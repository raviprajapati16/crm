<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Geojson_dataset
 *
 * Local-first administrative boundary storage using geoBoundaries (gbOpen).
 * Downloads run only from refresh jobs or on cache miss — never from arbitrary CDNs at runtime.
 */
class Geojson_dataset
{
    /** geoBoundaries API base (current gbOpen release). */
    const API_BASE = 'https://www.geoboundaries.org/api/current/gbOpen/';

    /** Bundled world countries outline (refresh-time only). */
    const WORLD_SOURCE = 'https://raw.githubusercontent.com/datasets/geo-countries/master/data/countries.geojson';

    private $cache_root = 'assets/geojson/';

    private $ttl = [
        'world'   => 2592000,
        'country' => 1209600,
        'state'   => 604800,
    ];

    /** @var array<string, array> */
    private $adm1_cache = [];

    /** @var array<string, array<string, string>> */
    private $adm1_index_cache = [];

    /** Common suffixes stripped for cross-source name matching. */
    private $match_suffixes = [
        'province', 'state', 'region', 'department', 'prefecture',
        'oblast', 'territory', 'county', 'district', 'municipality',
    ];

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    // ── Manifest ─────────────────────────────────────────────────────────────

    public function get_manifest()
    {
        $path = $this->manifest_path();
        if (!file_exists($path)) {
            return $this->default_manifest();
        }

        $data = json_decode(file_get_contents($path), true);
        if (!is_array($data)) {
            return $this->default_manifest();
        }

        return array_merge($this->default_manifest(), $data);
    }

    public function save_manifest(array $patch)
    {
        $manifest = array_merge($this->get_manifest(), $patch);
        $manifest['refreshed_at'] = date('c');

        $dir = dirname($this->manifest_path());
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        file_put_contents($this->manifest_path(), json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $manifest;
    }

    public function get_version_token()
    {
        $manifest = $this->get_manifest();
        $build    = $manifest['build_date'] ?? '1';
        $world    = file_exists(FCPATH . $this->local_path('world')) ? filemtime(FCPATH . $this->local_path('world')) : 0;

        return substr(md5($build . '_' . $world), 0, 12);
    }

    public function default_manifest()
    {
        return [
            'source'     => 'geoboundaries-gbOpen',
            'api'        => 'current',
            'build_date' => null,
            'refreshed_at' => null,
        ];
    }

    private function manifest_path()
    {
        return FCPATH . rtrim($this->cache_root, '/') . '/manifest.json';
    }

    // ── Paths ────────────────────────────────────────────────────────────────

    public function local_path($level, $iso2 = null, $state = null)
    {
        $root = rtrim($this->cache_root, '/') . '/';

        switch ($level) {
            case 'world':
                return $root . 'world.json';
            case 'country':
                return $root . 'countries/' . strtoupper($iso2) . '.json';
            case 'country_adm2':
                return $root . 'countries/' . strtoupper($iso2) . '_adm2.json';
            case 'state':
                $safe = preg_replace('/[^a-zA-Z0-9\-_]/', '_', (string) $state);
                return $root . 'states/' . strtoupper($iso2) . '/' . $safe . '.json';
        }

        return $root . 'misc_' . md5($level . $iso2 . $state) . '.json';
    }

    public function is_stale($rel_path, $level)
    {
        $abs = FCPATH . ltrim($rel_path, '/');
        if (!file_exists($abs)) {
            return true;
        }

        if ($this->is_legacy_format($rel_path)) {
            return true;
        }

        $ttl = $this->ttl[$level] ?? 604800;

        return (time() - filemtime($abs)) >= $ttl;
    }

    /**
     * Detect pre-migration cache files (Highcharts / GADM) that must be replaced.
     */
    public function is_legacy_format($rel_path)
    {
        $abs = FCPATH . ltrim($rel_path, '/');
        if (!file_exists($abs)) {
            return false;
        }

        $head = @file_get_contents($abs, false, null, 0, 4096);
        if ($head === false) {
            return false;
        }

        if (strpos($head, '"hc-key"') !== false || strpos($head, '"hc-group"') !== false) {
            return true;
        }

        if (stripos($head, 'gadm41_') !== false) {
            return true;
        }

        return false;
    }

    // ── Refresh orchestration ────────────────────────────────────────────────

    /**
     * @param array $options world(bool), countries(array ISO2|'*'|active), force(bool)
     * @return array summary
     */
    public function refresh(array $options = [])
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        $summary = ['ok' => [], 'failed' => [], 'skipped' => []];
        $force   = !empty($options['force']);

        if (!empty($options['world'])) {
            $result = $this->refresh_world($force);
            $summary[$result ? 'ok' : 'failed'][] = 'world';
        }

        $countries = $options['countries'] ?? [];
        if ($countries === 'active' || (is_array($countries) && in_array('active', $countries, true))) {
            $countries = $this->get_active_iso2_codes();
        }
        if ($countries === '*' || (is_array($countries) && in_array('*', $countries, true))) {
            $countries = $this->get_all_iso2_codes();
        }

        foreach ((array) $countries as $iso2) {
            $iso2 = strtoupper(preg_replace('/[^A-Z]/', '', (string) $iso2));
            if (strlen($iso2) !== 2) {
                continue;
            }

            if ($this->refresh_country($iso2, $force)) {
                $summary['ok'][] = $iso2;
            } else {
                $summary['failed'][] = $iso2;
            }

            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }

        $this->save_manifest(['build_date' => date('Y-m-d')]);

        return $summary;
    }

    public function ensure_world($force = false)
    {
        $rel = $this->local_path('world');
        if (!$force && file_exists(FCPATH . $rel) && !$this->is_stale($rel, 'world')) {
            return true;
        }

        return $this->refresh_world($force);
    }

    public function ensure_country($iso2, $force = false)
    {
        $iso2 = strtoupper($iso2);
        $rel  = $this->local_path('country', $iso2);
        if (!$force && file_exists(FCPATH . $rel) && !$this->is_stale($rel, 'country')) {
            return true;
        }

        return $this->refresh_country($iso2, $force);
    }

    public function ensure_country_adm2($iso2, $force = false)
    {
        $iso2 = strtoupper($iso2);
        $rel  = $this->local_path('country_adm2', $iso2);
        if (!$force && file_exists(FCPATH . $rel) && !$this->is_stale($rel, 'country')) {
            $this->ensure_adm2_parents($iso2);

            return true;
        }

        $ok = $this->refresh_country_adm2($iso2, $force);
        if ($ok) {
            $this->ensure_adm2_parents($iso2);
        }

        return $ok;
    }

    public function refresh_world($force = false)
    {
        $rel = $this->local_path('world');
        if (!$force && file_exists(FCPATH . $rel) && !$this->is_stale($rel, 'world')) {
            return true;
        }

        $body = $this->http_get(self::WORLD_SOURCE);
        if ($body === false) {
            log_message('error', 'Geojson_dataset: failed to download world boundaries');

            return false;
        }

        $decoded = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($decoded['features'])) {
            return false;
        }

        $normalized = $this->normalize_collection($decoded, 'world');

        return $this->write_json($rel, $normalized);
    }

    public function refresh_country($iso2, $force = false)
    {
        $iso2 = strtoupper($iso2);
        $iso3 = $this->get_iso3($iso2);
        if (!$iso3) {
            return false;
        }

        $rel = $this->local_path('country', $iso2);
        if (!$force && file_exists(FCPATH . $rel) && !$this->is_stale($rel, 'country')) {
            return true;
        }

        $meta = $this->fetch_api_metadata($iso3, 'ADM1');
        $url  = $this->resolve_download_url($meta);
        if (!$url) {
            log_message('error', 'Geojson_dataset: no ADM1 metadata for ' . $iso3);

            return false;
        }

        $body = $this->http_get($url);
        if ($body === false) {
            return false;
        }

        $decoded = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($decoded['features'])) {
            return false;
        }

        $normalized = $this->normalize_collection($decoded, 'country', $iso2);

        return $this->write_json($rel, $normalized);
    }

    public function refresh_country_adm2($iso2, $force = false)
    {
        $iso2 = strtoupper($iso2);
        $iso3 = $this->get_iso3($iso2);
        if (!$iso3) {
            return false;
        }

        $rel = $this->local_path('country_adm2', $iso2);
        if (!$force && file_exists(FCPATH . $rel) && !$this->is_stale($rel, 'country')) {
            return true;
        }

        $metaAdm2 = $this->fetch_api_metadata($iso3, 'ADM2');
        $url      = $this->resolve_download_url($metaAdm2);
        if (!$url) {
            log_message('error', 'Geojson_dataset: no ADM2 metadata for ' . $iso3);

            return false;
        }

        $body = $this->http_get($url);
        if ($body === false) {
            return false;
        }

        $adm2 = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($adm2['features'])) {
            return false;
        }

        // Enrich ADM2 with parent province from ADM1 (geoBoundaries ADM2 lacks hierarchy).
        $adm1Path = FCPATH . $this->local_path('country', $iso2);
        if (!file_exists($adm1Path)) {
            $this->refresh_country($iso2, true);
        }
        if (file_exists($adm1Path)) {
            $adm1 = json_decode(file_get_contents($adm1Path), true);
            if (!empty($adm1['features'])) {
                $adm2 = $this->enrich_adm2_with_parents($adm2, $adm1);
            }
        }

        $normalized = $this->normalize_collection($adm2, 'country_adm2', $iso2);

        return $this->write_json($rel, $normalized);
    }

    // ── geoBoundaries API ────────────────────────────────────────────────────

    public function fetch_api_metadata($iso3, $admLevel)
    {
        $url  = self::API_BASE . rawurlencode($iso3) . '/' . rawurlencode($admLevel) . '/';
        $body = $this->http_get($url, 60);
        if ($body === false) {
            return null;
        }

        $data = json_decode($body, true);

        return is_array($data) ? $data : null;
    }

    /**
     * Prefer simplified GeoJSON (smaller, map-friendly) over full resolution.
     */
    private function resolve_download_url($meta)
    {
        if (!is_array($meta)) {
            return null;
        }

        if (!empty($meta['simplifiedGeometryGeoJSON'])) {
            return $meta['simplifiedGeometryGeoJSON'];
        }

        return $meta['gjDownloadURL'] ?? null;
    }

    // ── ADM2 parent enrichment ───────────────────────────────────────────────

    public function enrich_adm2_with_parents(array $adm2, array $adm1)
    {
        $parents = [];
        $adm1Keys = [];

        foreach ($adm1['features'] as $feature) {
            $name = $this->feature_label($feature, 'country');
            if ($name === '') {
                continue;
            }

            $shapeIso = strtoupper(trim((string) (($feature['properties'] ?? [])['shapeISO'] ?? '')));
            $parents[] = [
                'name'     => $name,
                'shapeIso' => $shapeIso,
                'geometry' => $feature['geometry'] ?? null,
            ];

            foreach ($this->match_key_variants($name) as $key) {
                $adm1Keys[$key] = true;
            }
            if ($shapeIso !== '') {
                $adm1Keys[strtoupper($shapeIso)] = true;
                $suffix = $shapeIso;
                if (strpos($suffix, '-') !== false) {
                    $adm1Keys[substr($suffix, strrpos($suffix, '-') + 1)] = true;
                }
            }
        }

        foreach ($adm2['features'] as &$child) {
            $props         = $child['properties'] ?? [];
            $existing      = trim((string) ($props['parent_name'] ?? $props['NAME_1'] ?? ''));
            $existingValid = $existing !== '' && isset($adm1Keys[$this->match_key($existing)]);

            if ($existingValid) {
                continue;
            }

            $point = $this->feature_sample_point($child);
            if (!$point) {
                continue;
            }

            foreach ($parents as $parent) {
                if ($this->point_in_geometry($point, $parent['geometry'])) {
                    $child['properties']['NAME_1']      = $parent['name'];
                    $child['properties']['parent_name'] = $parent['name'];
                    if ($parent['shapeIso'] !== '') {
                        $child['properties']['parent_iso'] = $parent['shapeIso'];
                    }
                    break;
                }
            }
        }
        unset($child);

        return $adm2;
    }

    /**
     * Re-enrich ADM2 parent fields when cache predates improved enrichment.
     */
    public function ensure_adm2_parents($iso2)
    {
        $iso2 = strtoupper($iso2);
        $rel  = $this->local_path('country_adm2', $iso2);
        $abs  = FCPATH . $rel;
        if (!file_exists($abs)) {
            return false;
        }

        $adm2 = json_decode(file_get_contents($abs), true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($adm2['features'])) {
            return false;
        }

        $adm1 = $this->load_adm1_collection($iso2);
        if (empty($adm1['features'])) {
            return false;
        }

        $missing = 0;
        $invalid = 0;
        $adm1Keys = array_fill_keys(array_keys($this->adm1_name_index($iso2)), true);

        foreach ($adm2['features'] as $feature) {
            $props  = $feature['properties'] ?? [];
            $parent = trim((string) ($props['parent_name'] ?? $props['NAME_1'] ?? ''));
            if ($parent === '') {
                $missing++;
                continue;
            }
            if (!isset($adm1Keys[$this->match_key($parent)])) {
                $invalid++;
            }
        }

        if ($missing === 0 && $invalid === 0) {
            return true;
        }

        $adm2 = $this->enrich_adm2_with_parents($adm2, $adm1);

        return $this->write_json($rel, $adm2);
    }

    // ── Normalization ────────────────────────────────────────────────────────

    /**
     * Strip diacritics so geoBoundaries labels (e.g. Gujarāt) match CRM names (Gujarat).
     */
    public function ascii_fold($text)
    {
        $text = (string) $text;
        if ($text === '') {
            return '';
        }

        if (class_exists('Normalizer')) {
            $text = Normalizer::normalize($text, Normalizer::FORM_D);
            $text = preg_replace('/\p{M}/u', '', $text);
        } elseif (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        }

        return $text;
    }

    public function normalize_collection(array $collection, $level, $iso2 = null)
    {
        foreach ($collection['features'] as &$feature) {
            if (!isset($feature['properties']) || !is_array($feature['properties'])) {
                $feature['properties'] = [];
            }

            $label = $this->feature_label($feature, $level);
            if ($label !== '') {
                $feature['properties']['shapeName'] = $label;
                $feature['properties']['name']      = $this->ascii_fold($label);
            }

            if ($level === 'world') {
                $iso = $feature['properties']['ISO3166-1-Alpha-2']
                    ?? $feature['properties']['iso_a2']
                    ?? $feature['properties']['ISO_A2']
                    ?? '';
                if ($iso) {
                    $feature['properties']['ISO3166-1-Alpha-2'] = strtoupper($iso);
                }
            }
        }
        unset($feature);

        $collection['type'] = 'FeatureCollection';

        return $collection;
    }

    public function feature_label(array $feature, $level)
    {
        $p = $feature['properties'] ?? [];

        if ($level === 'country_adm2' || $level === 'state') {
            return $p['shapeName'] ?? $p['NAME_2'] ?? $p['name'] ?? $p['NAME'] ?? '';
        }

        if ($level === 'country') {
            return $p['shapeName'] ?? $p['NAME_1'] ?? $p['name'] ?? $p['NAME'] ?? $p['ADMIN'] ?? '';
        }

        return $p['name'] ?? $p['ADMIN'] ?? $p['shapeName'] ?? $p['NAME_0'] ?? '';
    }

    // ── Name resolution (CRM ↔ geoBoundaries) ────────────────────────────────

    /**
     * Normalized match key: diacritics, punctuation, suffixes, and "and" removed.
     */
    public function match_key($name)
    {
        $n = strtolower(trim($this->ascii_fold((string) $name)));
        $n = preg_replace('/[^a-z0-9]+/', '', $n);
        $n = str_replace('and', '', $n);

        foreach ($this->match_suffixes as $suffix) {
            if (strlen($n) > strlen($suffix) + 2 && substr($n, -strlen($suffix)) === $suffix) {
                $n = substr($n, 0, -strlen($suffix));
            }
        }

        return $n;
    }

    /**
     * @return string[]
     */
    public function match_key_variants($name)
    {
        $keys = [];
        $base = $this->match_key($name);
        if ($base !== '') {
            $keys[] = $base;
        }

        $ascii = strtolower(trim($this->ascii_fold((string) $name)));
        $ascii = preg_replace('/[^a-z0-9]+/', '', $ascii);
        if ($ascii !== '' && $ascii !== $base) {
            $keys[] = $ascii;
        }

        return array_values(array_unique($keys));
    }

    public function geo_names_match($a, $b)
    {
        $normA = $this->match_key($a);
        $normB = $this->match_key($b);
        if ($normA === '' || $normB === '') {
            return false;
        }
        if ($normA === $normB) {
            return true;
        }

        return (strpos($normA, $normB) !== false || strpos($normB, $normA) !== false);
    }

    public function load_adm1_collection($iso2)
    {
        $iso2 = strtoupper($iso2);
        if (isset($this->adm1_cache[$iso2])) {
            return $this->adm1_cache[$iso2];
        }

        $path = FCPATH . $this->local_path('country', $iso2);
        if (!file_exists($path)) {
            $this->adm1_cache[$iso2] = ['type' => 'FeatureCollection', 'features' => []];

            return $this->adm1_cache[$iso2];
        }

        $decoded = json_decode(file_get_contents($path), true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($decoded['features'])) {
            $this->adm1_cache[$iso2] = ['type' => 'FeatureCollection', 'features' => []];

            return $this->adm1_cache[$iso2];
        }

        $this->adm1_cache[$iso2] = $decoded;

        return $this->adm1_cache[$iso2];
    }

    /**
     * @return array<string, string> lookup key → canonical ADM1 shapeName
     */
    public function adm1_name_index($iso2)
    {
        $iso2 = strtoupper($iso2);
        if (isset($this->adm1_index_cache[$iso2])) {
            return $this->adm1_index_cache[$iso2];
        }

        $index  = [];
        $adm1   = $this->load_adm1_collection($iso2);
        foreach ($adm1['features'] as $feature) {
            $props     = $feature['properties'] ?? [];
            $shapeName = $props['shapeName'] ?? $props['name'] ?? $props['NAME_1'] ?? '';
            if ($shapeName === '') {
                continue;
            }

            foreach ($this->match_key_variants($shapeName) as $key) {
                $index[$key] = $shapeName;
            }

            $shapeIso = strtoupper(trim((string) ($props['shapeISO'] ?? '')));
            if ($shapeIso !== '') {
                $index[$shapeIso] = $shapeName;
                if (strpos($shapeIso, '-') !== false) {
                    $index[substr($shapeIso, strrpos($shapeIso, '-') + 1)] = $shapeName;
                }
            }
        }

        $this->adm1_index_cache[$iso2] = $index;

        return $index;
    }

    /**
     * Resolve any CRM / map label to canonical geoBoundaries ADM1 shapeName.
     */
    public function resolve_adm1_name($iso2, $input)
    {
        $input = trim((string) $input);
        if ($input === '') {
            return null;
        }

        $index  = $this->adm1_name_index($iso2);
        $inputUp = strtoupper($input);

        if (isset($index[$inputUp])) {
            return $index[$inputUp];
        }

        foreach ($this->match_key_variants($input) as $key) {
            if (isset($index[$key])) {
                return $index[$key];
            }
        }

        foreach ($index as $key => $shapeName) {
            if ($this->geo_names_match($input, $shapeName)) {
                return $shapeName;
            }
        }

        return null;
    }

    public function find_adm1_feature($iso2, $stateInput)
    {
        $canonical = $this->resolve_adm1_name($iso2, $stateInput) ?: trim((string) $stateInput);
        $adm1      = $this->load_adm1_collection($iso2);

        foreach ($adm1['features'] as $feature) {
            $props     = $feature['properties'] ?? [];
            $shapeName = $props['shapeName'] ?? $props['name'] ?? $props['NAME_1'] ?? '';
            if ($shapeName === '') {
                continue;
            }
            if ($this->geo_names_match($shapeName, $canonical)) {
                return $feature;
            }

            $shapeIso = strtoupper(trim((string) ($props['shapeISO'] ?? '')));
            if ($shapeIso !== '' && strtoupper($stateInput) === $shapeIso) {
                return $feature;
            }
        }

        return null;
    }

    /**
     * Stable ASCII / ISO slug for state-level cache filenames.
     */
    public function state_cache_slug($iso2, $stateInput)
    {
        $canonical = $this->resolve_adm1_name($iso2, $stateInput) ?: trim((string) $stateInput);
        if ($canonical === '') {
            return '';
        }

        $feature = $this->find_adm1_feature($iso2, $canonical);
        if ($feature) {
            $shapeIso = trim((string) (($feature['properties'] ?? [])['shapeISO'] ?? ''));
            if ($shapeIso !== '') {
                return $shapeIso;
            }
        }

        return $this->ascii_fold($canonical);
    }

    /**
     * Build a single-feature state map from ADM1 when no ADM2 districts exist
     * (e.g. Russian federal cities: Saint Petersburg, Sevastopol).
     */
    public function build_state_collection_from_adm1($iso2, $stateInput)
    {
        $feature = $this->find_adm1_feature($iso2, $stateInput);
        if (!$feature || empty($feature['geometry'])) {
            return null;
        }

        $props    = $feature['properties'] ?? [];
        $name     = $props['shapeName'] ?? $props['name'] ?? trim((string) $stateInput);
        $shapeIso = trim((string) ($props['shapeISO'] ?? ''));

        return [
            'type'     => 'FeatureCollection',
            'features' => [
                [
                    'type'       => 'Feature',
                    'properties' => [
                        'shapeName'           => $name,
                        'name'                => $this->ascii_fold($name),
                        'parent_name'         => $name,
                        'parent_iso'          => $shapeIso,
                        'shapeISO'            => $shapeIso,
                        'shapeType'           => 'ADM2',
                        '_from_adm1_fallback' => true,
                    ],
                    'geometry' => $feature['geometry'],
                ],
            ],
        ];
    }

    /**
     * Filter ADM2 districts for a state using names, ISO codes, then geometry.
     *
     * @return array<int, array>
     */
    public function filter_adm2_features(array $features, $iso2, $stateInput, $useGeometry = true)
    {
        $iso2      = strtoupper($iso2);
        $canonical = $this->resolve_adm1_name($iso2, $stateInput) ?: trim((string) $stateInput);
        $adm1      = $this->find_adm1_feature($iso2, $canonical);
        $stateUp   = strtoupper(trim((string) $stateInput));
        $stateKeys = $this->match_key_variants($canonical);
        if ($stateUp !== '') {
            $stateKeys[] = $stateUp;
        }

        $filtered   = [];
        $needGeo    = [];

        foreach ($features as $feature) {
            if ($this->adm2_feature_in_state($feature, $iso2, $canonical, $stateKeys, $stateUp, $adm1, false)) {
                $filtered[] = $feature;
            } elseif ($useGeometry && $adm1) {
                $needGeo[] = $feature;
            }
        }

        if (!empty($filtered) || !$useGeometry || !$adm1) {
            return $filtered;
        }

        foreach ($needGeo as $feature) {
            if ($this->feature_in_adm1($feature, $adm1)) {
                $filtered[] = $feature;
            }
        }

        return $filtered;
    }

    private function adm2_feature_in_state(array $feature, $iso2, $canonical, array $stateKeys, $stateUp, $adm1Feature, $useGeometry = true)
    {
        $props        = $feature['properties'] ?? [];
        $featureState = trim((string) ($props['parent_name'] ?? $props['NAME_1'] ?? ''));
        $parentIso    = strtoupper(trim((string) ($props['parent_iso'] ?? '')));

        if ($featureState !== '') {
            foreach ($stateKeys as $key) {
                if ($this->match_key($featureState) === $key) {
                    return true;
                }
            }
            if ($this->geo_names_match($featureState, $canonical)) {
                return true;
            }
        }

        if ($parentIso !== '' && $stateUp !== '') {
            if ($parentIso === $stateUp) {
                return true;
            }
            if (strpos($parentIso, '-') !== false && substr($parentIso, strrpos($parentIso, '-') + 1) === $stateUp) {
                return true;
            }
        }

        if ($parentIso !== '' && $adm1Feature) {
            $adm1Iso = strtoupper(trim((string) (($adm1Feature['properties'] ?? [])['shapeISO'] ?? '')));
            if ($adm1Iso !== '' && $parentIso === $adm1Iso) {
                return true;
            }
        }

        if ($useGeometry && $adm1Feature && $this->feature_in_adm1($feature, $adm1Feature)) {
            return true;
        }

        return false;
    }

    public function feature_in_adm1(array $adm2Feature, array $adm1Feature)
    {
        $geometry = $adm1Feature['geometry'] ?? null;
        if (!$geometry) {
            return false;
        }

        foreach ($this->feature_sample_points($adm2Feature) as $point) {
            if ($this->point_in_geometry($point, $geometry)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array{0: float, 1: float}>
     */
    public function feature_sample_points(array $feature)
    {
        $points = [];
        $centroid = $this->feature_centroid($feature);
        if ($centroid) {
            $points[] = $centroid;
        }

        $bboxCenter = $this->feature_bbox_center($feature);
        if ($bboxCenter) {
            $points[] = $bboxCenter;
        }

        $first = $this->feature_first_coordinate($feature);
        if ($first) {
            $points[] = $first;
        }

        $unique = [];
        foreach ($points as $pt) {
            $k = round($pt[0], 5) . ',' . round($pt[1], 5);
            if (!isset($unique[$k])) {
                $unique[$k] = $pt;
            }
        }

        return array_values($unique);
    }

    public function feature_sample_point(array $feature)
    {
        $points = $this->feature_sample_points($feature);

        return $points[0] ?? null;
    }

    public function feature_bbox_center(array $feature)
    {
        $geometry = $feature['geometry'] ?? null;
        if (!$geometry || empty($geometry['coordinates'])) {
            return null;
        }

        $points = [];
        $this->collect_coordinates($geometry, $points);
        if (empty($points)) {
            return null;
        }

        $minLon = $maxLon = $points[0][0];
        $minLat = $maxLat = $points[0][1];
        foreach ($points as $pt) {
            $minLon = min($minLon, $pt[0]);
            $maxLon = max($maxLon, $pt[0]);
            $minLat = min($minLat, $pt[1]);
            $maxLat = max($maxLat, $pt[1]);
        }

        return [($minLon + $maxLon) / 2, ($minLat + $maxLat) / 2];
    }

    public function feature_first_coordinate(array $feature)
    {
        $geometry = $feature['geometry'] ?? null;
        if (!$geometry || empty($geometry['coordinates'])) {
            return null;
        }

        $coords = $geometry['coordinates'];
        while (is_array($coords) && isset($coords[0]) && is_array($coords[0]) && !isset($coords[0][0])) {
            $coords = $coords[0];
        }

        if (is_array($coords) && count($coords) >= 2 && is_numeric($coords[0]) && is_numeric($coords[1])) {
            return [(float) $coords[0], (float) $coords[1]];
        }

        return null;
    }

    // ── Geometry helpers ─────────────────────────────────────────────────────

    public function feature_centroid(array $feature)
    {
        $geometry = $feature['geometry'] ?? null;
        if (!$geometry || empty($geometry['coordinates'])) {
            return null;
        }

        $points = [];
        $this->collect_coordinates($geometry, $points);
        if (empty($points)) {
            return null;
        }

        $lon = 0;
        $lat = 0;
        $n   = count($points);
        foreach ($points as $pt) {
            $lon += $pt[0];
            $lat += $pt[1];
        }

        return [$lon / $n, $lat / $n];
    }

    private function collect_coordinates(array $geometry, array &$points)
    {
        $type = $geometry['type'] ?? '';
        $coords = $geometry['coordinates'] ?? [];

        switch ($type) {
            case 'Point':
                $points[] = $coords;
                break;
            case 'MultiPoint':
            case 'LineString':
                foreach ($coords as $c) {
                    $points[] = $c;
                }
                break;
            case 'MultiLineString':
            case 'Polygon':
                foreach ($coords as $ring) {
                    foreach ($ring as $c) {
                        $points[] = $c;
                    }
                }
                break;
            case 'MultiPolygon':
                foreach ($coords as $poly) {
                    foreach ($poly as $ring) {
                        foreach ($ring as $c) {
                            $points[] = $c;
                        }
                    }
                }
                break;
        }
    }

    public function point_in_geometry(array $point, $geometry)
    {
        if (!$geometry || empty($geometry['type'])) {
            return false;
        }

        $type = $geometry['type'];
        if ($type === 'Polygon') {
            return $this->point_in_polygon($point, $geometry['coordinates'][0] ?? []);
        }
        if ($type === 'MultiPolygon') {
            foreach ($geometry['coordinates'] as $poly) {
                if ($this->point_in_polygon($point, $poly[0] ?? [])) {
                    return true;
                }
            }
        }

        return false;
    }

    private function point_in_polygon(array $point, array $ring)
    {
        if (count($ring) < 3) {
            return false;
        }

        $x = $point[0];
        $y = $point[1];
        $inside = false;
        $n = count($ring);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $ring[$i][0];
            $yi = $ring[$i][1];
            $xj = $ring[$j][0];
            $yj = $ring[$j][1];

            $intersect = (($yi > $y) !== ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 1e-12) + $xi);

            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    // ── ISO helpers ──────────────────────────────────────────────────────────

    public function get_iso3($iso2)
    {
        $iso2 = strtoupper($iso2);
        $this->ci->db->select('iso3')->where('iso2', $iso2)->limit(1);
        $row = $this->ci->db->get(db_prefix() . 'countries')->row();

        return $row ? strtoupper($row->iso3) : '';
    }

    public function get_active_iso2_codes()
    {
        $prefix = db_prefix();
        $sql    = "SELECT DISTINCT c.iso2
            FROM {$prefix}countries c
            WHERE c.iso2 IS NOT NULL AND c.iso2 != ''
              AND (
                c.country_id IN (SELECT billing_country FROM {$prefix}invoices WHERE deleted_at IS NULL)
                OR c.country_id IN (SELECT country FROM {$prefix}clients)
              )
            ORDER BY c.iso2";

        $rows = $this->ci->db->query($sql)->result_array();

        return array_values(array_filter(array_map(function ($r) {
            return strtoupper($r['iso2']);
        }, $rows)));
    }

    public function get_all_iso2_codes()
    {
        $this->ci->db->select('iso2');
        $this->ci->db->where('iso2 IS NOT NULL');
        $this->ci->db->where('iso2 !=', '');
        $rows = $this->ci->db->get(db_prefix() . 'countries')->result_array();

        return array_values(array_filter(array_map(function ($r) {
            return strtoupper($r['iso2']);
        }, $rows)));
    }

    // ── IO ───────────────────────────────────────────────────────────────────

    private function write_json($rel, array $collection)
    {
        if (empty($collection['features'])) {
            return false;
        }

        $abs = FCPATH . ltrim($rel, '/');
        $dir = dirname($abs);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $json = json_encode($collection);
        if ($json === false) {
            return false;
        }

        return file_put_contents($abs, $json) !== false;
    }

    public function http_get($url, $timeout = 120)
    {
        if (!function_exists('curl_init')) {
            $ctx = stream_context_create([
                'http' => [
                    'timeout'       => $timeout,
                    'user_agent'    => 'CRM-GeoJSON-Refresh/1.0',
                    'ignore_errors' => true,
                ],
                'ssl' => ['verify_peer' => false],
            ]);
            $body = @file_get_contents($url, false, $ctx);

            return ($body !== false && strlen($body) > 10) ? $body : false;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_USERAGENT      => 'CRM-GeoJSON-Refresh/1.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING       => 'gzip, deflate',
        ]);

        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 200 && $code < 300 && $body !== false && strlen($body) > 10) {
            return $body;
        }

        log_message('error', 'Geojson_dataset::http_get failed ' . $code . ' for ' . $url);

        return false;
    }
}
