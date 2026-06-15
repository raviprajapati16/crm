<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Invoice_map_model
 *
 * Provides:
 *  - Aggregated invoice data at World / Country / State level
 *  - City-level invoice list
 *  - Dynamic GeoJSON fetching for ANY country in the world
 *    (no hardcoded files — uses GADM + fallback sources)
 *  - Geocoding cache for lat/lng scatter fallback
 */
class Invoice_map_model extends App_Model
{
    // ── GeoJSON CDN cascade (tried in order on cache miss) ────────────────────
    // %ISO2% = 2-letter ISO code (e.g. IN, US, GB)
    // Level 1 = country → states/provinces
    // Level 2 = country → districts/cities (admin level 2)
    private $geojson_sources = [
        // ── World: raw GeoJSON with name + ISO3166-1-Alpha-2 properties ──────────
        // Primary : geo-countries dataset (FeatureCollection, ~14 MB)
        // Fallback : jsdelivr CDN mirror
        'world' => [
            'https://raw.githubusercontent.com/datasets/geo-countries/master/data/countries.geojson',
            'https://cdn.jsdelivr.net/gh/datasets/geo-countries@master/data/countries.geojson',
        ],

        // ── Country → states/provinces ────────────────────────────────────────
        // Primary: Highcharts optimized TopoJSON (fast, ~50KB)
        // GADM 4.1 uses 3-letter ISO codes (IND, USA, GBR...) → %ISO3%
        // Fallback : geoBoundaries (uses ISO3, ADM1 = states)
        'country' => [
            'https://code.highcharts.com/mapdata/countries/%ISO2_LOWER%/%ISO2_LOWER%-all.topo.json',
            'https://geodata.ucdavis.edu/gadm/gadm4.1/json/gadm41_%ISO3%_1.json',
            'https://github.com/wmgeolab/geoBoundaries/raw/main/releaseData/gbOpen/%ISO3%/ADM1/geoBoundaries-%ISO3%-ADM1.geojson',
        ],

        // ── State → districts / cities ────────────────────────────────────────
        // GADM level-2 for districts; geoBoundaries ADM2; Nominatim polygon fallback
        'state' => [
            'https://geodata.ucdavis.edu/gadm/gadm4.1/json/gadm41_%ISO3%_2.json',
            'https://github.com/wmgeolab/geoBoundaries/raw/main/releaseData/gbOpen/%ISO3%/ADM2/geoBoundaries-%ISO3%-ADM2.geojson',
            'https://nominatim.openstreetmap.org/search?q=%STATE%,%ISO2%&format=geojson&polygon_geojson=1&limit=1&polygon_threshold=0.005',
        ],
    ];

    // ISO2 → ISO3 mapping is resolved live from tblcountries.
    // Cached in memory for the duration of the request.
    private $iso3_cache = [];


    // Local cache root (relative to FCPATH)
    private $cache_root = 'assets/geojson/';

    // How long to keep a cached file before re-fetching (seconds)
    private $cache_ttl = [
        'world'   => 2592000,  // 30 days
        'country' => 1209600,  // 14 days
        'state'   => 604800,   // 7 days
    ];

    /**
     * Per-country state rules when map labels / GADM NAME_1 disagree.
     * Keys use _normalize_geo_name_for_match() of the requested state name.
     */
    private $state_geo_rules = [
        'IN' => [
            'ladakh' => [
                'name_1_aliases' => ['Ladakh'],
                'districts'      => ['Kargil', 'Leh', 'Leh(Ladakh)'],
            ],
            'jammukashmir' => [
                'name_1_aliases' => ['JammuandKashmir', 'Jammu and Kashmir'],
                'exclude_districts' => ['Kargil', 'Leh', 'Leh(Ladakh)'],
            ],
        ],
    ];

    public function __construct()
    {
        parent::__construct();
    }

    // =========================================================================
    // LEVEL 0 — World: invoice count + amount per country
    // =========================================================================
    public function get_world_data($filters = [])
    {
        $this->db->select('
            c.iso2                           AS iso_code,
            c.long_name                      AS name,
            COUNT(inv.id)                    AS value,
            COALESCE(SUM(inv.total), 0)      AS total_amount,
            MAX(cur.name)                    AS currency_name
        ');
        $this->db->from(db_prefix() . 'invoices inv');
        $this->db->join(db_prefix() . 'currencies cur', 'cur.id = inv.currency', 'left');
        $this->db->join(
            db_prefix() . 'countries c',
            'c.country_id = inv.billing_country',
            'left'
        );
        $this->db->where('inv.deleted_at IS NULL');
        $this->db->where('inv.status !=', 5);
        $this->_apply_filters($filters, 'inv');
        $this->db->group_by('c.iso2, c.long_name');
        $this->db->having('value >', 0);

        $rows = $this->db->get()->result_array();

        // Ensure numeric types
        foreach ($rows as &$r) {
            $r['value']        = (int)   $r['value'];
            $r['total_amount'] = (float)  $r['total_amount'];
        }
        return $rows;
    }

    // =========================================================================
    // LEVEL 1 — Country: state-level aggregation for a given ISO2 code
    // =========================================================================
    public function get_country_data($iso2, $filters = [])
    {
        $this->db->select('
            TRIM(inv.billing_state)          AS name,
            COUNT(inv.id)                    AS value,
            COALESCE(SUM(inv.total), 0)      AS total_amount,
            MAX(cur.name)                    AS currency_name
        ');
        $this->db->from(db_prefix() . 'invoices inv');
        $this->db->join(db_prefix() . 'currencies cur', 'cur.id = inv.currency', 'left');
        $this->db->join(
            db_prefix() . 'countries c',
            'c.country_id = inv.billing_country',
            'left'
        );
        $this->db->where('inv.deleted_at IS NULL');
        $this->db->where('inv.status !=', 5);
        $this->db->where('c.iso2',           strtoupper($iso2));
        $this->db->where('inv.billing_state !=', '');
        $this->db->where('inv.billing_state IS NOT NULL');
        $this->_apply_filters($filters, 'inv');
        $this->db->group_by('TRIM(inv.billing_state)');
        $this->db->having('value >', 0);
        $this->db->order_by('value', 'DESC');

        $rows = $this->db->get()->result_array();
        foreach ($rows as &$r) {
            $r['value']        = (int)   $r['value'];
            $r['total_amount'] = (float)  $r['total_amount'];
        }
        
        return $rows;
    }

    // =========================================================================
    // LEVEL 2 — State: city-level aggregation with lat/lng from geocache
    // =========================================================================
    public function get_state_data($iso2, $state, $filters = [])
    {
        $this->db->select('
            TRIM(inv.billing_city)           AS name,
            COUNT(inv.id)                    AS value,
            COALESCE(SUM(inv.total), 0)      AS total_amount,
            MAX(cur.name)                    AS currency_name,
            geo.latitude,
            geo.longitude
        ');
        $this->db->from(db_prefix() . 'invoices inv');
        $this->db->join(db_prefix() . 'currencies cur', 'cur.id = inv.currency', 'left');
        $this->db->join(
            db_prefix() . 'countries c',
            'c.country_id = inv.billing_country',
            'left'
        );
        // Join geocache for scatter-map coordinates
        $this->db->join(
            db_prefix() . 'invoice_city_geocache geo',
            'geo.city = TRIM(inv.billing_city)
             AND geo.state = TRIM(inv.billing_state)
             AND geo.country_iso2 = c.iso2',
            'left'
        );
        $this->db->where('inv.deleted_at IS NULL');
        $this->db->where('inv.status !=', 5);
        $this->db->where('c.iso2',            strtoupper($iso2));
        $this->db->where('TRIM(inv.billing_state)', trim($state));
        $this->db->where('inv.billing_city !=',  '');
        $this->db->where('inv.billing_city IS NOT NULL');
        $this->_apply_filters($filters, 'inv');
        $this->db->group_by('TRIM(inv.billing_city), geo.latitude, geo.longitude');
        $this->db->having('value >', 0);
        $this->db->order_by('value', 'DESC');

        $rows = $this->db->get()->result_array();

        // Kick off background geocoding for cities without coords
        foreach ($rows as &$r) {
            $r['value']        = (int)   $r['value'];
            $r['total_amount'] = (float)  $r['total_amount'];
            $r['latitude']     = $r['latitude']  ? (float) $r['latitude']  : null;
            $r['longitude']    = $r['longitude'] ? (float) $r['longitude'] : null;

            // Auto-geocode if missing (non-blocking: save to cache on next request)
            if ($r['latitude'] === null && $r['name'] !== '') {
                $this->_queue_geocode($r['name'], $state, $iso2);
            }
        }
        return $rows;
    }

    // =========================================================================
    // LEVEL 3 — City: invoice list (paginated, 100/page)
    // =========================================================================
    public function get_city_invoices($iso2, $state, $city, $filters = [], $page = 0)
    {
        $per_page = 100;
        $offset   = $page * $per_page;

        // ── Totals ────────────────────────────────────────────────────────────
        $this->db->select('COUNT(inv.id) AS cnt, COALESCE(SUM(inv.total), 0) AS total_amount');
        $this->db->from(db_prefix() . 'invoices inv');
        $this->db->join(db_prefix() . 'countries c', 'c.country_id = inv.billing_country', 'left');
        $this->_city_where($iso2, $state, $city);
        $this->_apply_filters($filters, 'inv');
        $summary = $this->db->get()->row_array();

        // ── Detail list ────────────────────────────────────────────────────────
        $this->db->select('
            inv.id,
            CONCAT(COALESCE(inv.prefix,""), inv.number) AS number,
            COALESCE(cl.company, inv.deleted_customer_name, "—") AS client,
            inv.date,
            inv.total,
            inv.status,
            cur.name AS currency_name,
            c.long_name AS country,
            inv.billing_state AS state,
            inv.billing_city AS city
        ');
        $this->db->from(db_prefix() . 'invoices inv');
        $this->db->join(db_prefix() . 'clients cl',   'cl.userid = inv.clientid', 'left');
        $this->db->join(db_prefix() . 'countries c',  'c.country_id = inv.billing_country', 'left');
        $this->db->join(db_prefix() . 'currencies cur', 'cur.id = inv.currency', 'left');
        $this->_city_where($iso2, $state, $city);
        $this->_apply_filters($filters, 'inv');
        $this->db->order_by('inv.date', 'DESC');
        $this->db->limit($per_page, $offset);
        $invoices = $this->db->get()->result_array();

        return [
            'count'        => (int)   $summary['cnt'],
            'total_amount' => (float)  $summary['total_amount'],
            'invoices'     => $invoices,
        ];
    }

    // =========================================================================
    // EXPORT: Raw invoice data for CSV based on current level & filters
    // =========================================================================
    public function get_export_invoices($level, $iso2, $state, $city, $filters = [])
    {
        $this->db->select('
            inv.id,
            CONCAT(COALESCE(inv.prefix,""), inv.number) AS number,
            COALESCE(cl.company, inv.deleted_customer_name, "—") AS client,
            inv.date,
            inv.duedate,
            inv.total,
            inv.status,
            cur.name AS currency_name,
            c.long_name AS country,
            inv.billing_state AS state,
            inv.billing_city AS city
        ');
        $this->db->from(db_prefix() . 'invoices inv');
        $this->db->join(db_prefix() . 'clients cl',   'cl.userid = inv.clientid', 'left');
        $this->db->join(db_prefix() . 'countries c',  'c.country_id = inv.billing_country', 'left');
        $this->db->join(db_prefix() . 'currencies cur', 'cur.id = inv.currency', 'left');
        
        $this->db->where('inv.deleted_at IS NULL');
        $this->db->where('inv.status !=', 5);

        if ($level === 'country' || $level === 'state' || $level === 'city') {
            if ($iso2) $this->db->where('c.iso2', strtoupper($iso2));
        }
        if (($level === 'state' || $level === 'city') && $state) {
            $this->db->where('TRIM(inv.billing_state)', trim($state));
        }
        if ($level === 'city' && $city) {
            $this->db->where('TRIM(inv.billing_city)', trim($city));
        }

        $this->_apply_filters($filters, 'inv');
        $this->db->order_by('inv.date', 'DESC');
        
        return $this->db->get()->result_array();
    }

    // =========================================================================
    // GEOJSON — Dynamic fetch for any country / state in the world
    // =========================================================================

    /**
     * Return GeoJSON string for the requested level.
     *
     * Resolution order for every request:
     *   1. Serve from local file if it exists and is not stale
     *   2. Serve from DB cache record (file_path pointer)
     *   3. Try each CDN in $this->geojson_sources[level]
     *   4. Save to local file + update DB cache
     *   5. Return false if all sources fail
     *
     * @param string      $level  world | country | state
     * @param string|null $iso2   2-letter ISO country code
     * @param string|null $state  State / province name
     * @return string|false
     */
    public function get_geojson($level, $iso2 = null, $state = null)
    {
        try {
            $iso2  = $iso2  ? strtoupper($iso2)  : null;
            $state = $this->_canonicalize_state_name($state);

            $cache_key = $this->_cache_key($level, $iso2, $state);
            $ttl       = $this->cache_ttl[$level] ?? 604800;

            // 1. Check local file cache
            $local = $this->_local_path($level, $iso2, $state);
            if (file_exists(FCPATH . $local)) {
                $mtime = filemtime(FCPATH . $local);
                if ((time() - $mtime) < $ttl) {
                    return $this->_finalize_state_geojson(file_get_contents(FCPATH . $local), $level, $iso2, $state, $local);
                }
            }

            // 2. Check DB cache (may have a valid file_path even if local check failed)
            if ($this->db->table_exists(db_prefix() . 'invoice_geojson_cache')) {
                $this->db->where('cache_key', $cache_key);
                $cached = $this->db->get(db_prefix() . 'invoice_geojson_cache')->row();

                if ($cached && $cached->file_path && file_exists(FCPATH . $cached->file_path)) {
                    $mtime = filemtime(FCPATH . $cached->file_path);
                    if ((time() - $mtime) < $ttl) {
                        return $this->_finalize_state_geojson(
                            file_get_contents(FCPATH . $cached->file_path),
                            $level,
                            $iso2,
                            $state,
                            $cached->file_path
                        );
                    }
                }
            }

            ini_set('memory_limit', '512M');

            // 3. Fetch — state level filters from shared country ADM2 cache when possible
            if ($level === 'state' && $iso2 && $state) {
                $geojson = $this->_fetch_state_geojson_filtered($iso2, $state);
            } else {
                $geojson = $this->_fetch_from_sources($level, $iso2, $state);
            }

            if ($geojson === false) {
                return false;
            }

            // 4. Save & cache (filtered for state level)
            $geojson = $this->_finalize_state_geojson($geojson, $level, $iso2, $state, null);
            $this->_save_and_cache($cache_key, $geojson, $local, $level, $iso2, $state);

            return $geojson;
        } catch (Throwable $e) {
            log_message('error', 'Invoice_map_model::get_geojson — ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Filter a state/province from a once-downloaded country ADM2 file (avoids
     * repeated 40MB+ downloads and memory spikes per state click).
     */
    private function _fetch_state_geojson_filtered($iso2, $state)
    {
        $adm2 = $this->_load_country_adm2_decoded($iso2);
        if ($adm2) {
            $filtered = $this->_filter_geojson_by_state($adm2, $state, $iso2);
            $decoded  = json_decode($filtered, true);
            if (!empty($decoded['features'])) {
                return $filtered;
            }
        }

        return $this->_fetch_from_sources('state', $iso2, $state);
    }

    /**
     * Load (or download once) the full ADM2 district file for a country.
     */
    private function _load_country_adm2_decoded($iso2)
    {
        $rel  = $this->_country_adm2_path($iso2);
        $path = FCPATH . $rel;
        $ttl  = $this->cache_ttl['country'];

        if (file_exists($path)) {
            $mtime = filemtime($path);
            if ((time() - $mtime) < $ttl) {
                $decoded = json_decode(file_get_contents($path), true);
                if (!empty($decoded['features'])) {
                    return $decoded;
                }
            }
        }

        $iso3 = $this->_get_iso3($iso2);
        if (!$iso3) {
            return null;
        }

        @set_time_limit(180);
        @ini_set('memory_limit', '512M');

        $url  = 'https://geodata.ucdavis.edu/gadm/gadm4.1/json/gadm41_' . $iso3 . '_2.json';
        $body = $this->_curl_get($url);
        if ($body === false) {
            return null;
        }

        $decoded = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($decoded['features'])) {
            return null;
        }

        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($path, $body);

        return $decoded;
    }

    private function _country_adm2_path($iso2)
    {
        return rtrim($this->cache_root, '/') . '/countries/' . strtoupper($iso2) . '_adm2.json';
    }

    /**
     * For state-level GeoJSON, always filter to the requested province and
     * rewrite bloated cache files (e.g. whole-country district dumps).
     */
    private function _finalize_state_geojson($body, $level, $iso2, $state, $local_rel = null)
    {
        if ($level !== 'state' || !$state) {
            return $body;
        }

        $decoded = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($decoded['features'])) {
            return $body;
        }

        $beforeCount = count($decoded['features']);
        $filtered    = $this->_filter_geojson_by_state($decoded, $state, $iso2);
        $after       = json_decode($filtered, true);
        $afterCount  = !empty($after['features']) ? count($after['features']) : 0;

        // Rewrite cache when an old unfiltered country-wide file was stored
        if ($local_rel && $afterCount > 0 && $beforeCount > $afterCount * 2) {
            $full_path = FCPATH . ltrim($local_rel, '/');
            $dir       = dirname($full_path);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            @file_put_contents($full_path, $filtered);
        }

        return $filtered;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GeoJSON: try each source URL in order, validate, and return raw string
    // ─────────────────────────────────────────────────────────────────────────
    private function _fetch_from_sources($level, $iso2, $state)
    {
        $sources = $this->geojson_sources[$level] ?? [];

        foreach ($sources as $url_template) {
            $url = $this->_resolve_url($url_template, $iso2, $state);
            if (!$url) continue;

            $body = $this->_curl_get($url);
            if ($body === false) continue;

            $decoded = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) continue;

            // ── Handle TopoJSON (world-atlas etc.) ────────────────────────────
            if (isset($decoded['type']) && $decoded['type'] === 'Topology') {
                $body    = $this->_topojson_to_geojson($decoded, $level, $iso2);
                if ($body === false) continue;
                $decoded = json_decode($body, true);
            }

            // ── GADM responses wrap features in a named key for state level ──
            // e.g. { "type":"FeatureCollection", "features":[...] }  ← normal
            // Some GADM files need state-name filtering at level-2
            if ($level === 'state' && $state && !empty($decoded['features'])) {
                $body = $this->_filter_geojson_by_state($decoded, $state, $iso2);
                $decoded = json_decode($body, true);
            }

            // ── Final validation: must have at least one feature ──────────────
            if (empty($decoded['features'])) continue;

            return $body;
        }

        return false;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // For state-level GeoJSON (GADM _2.json = whole country districts):
    // filter features that belong to the requested state/province.
    // GADM level-2 features have NAME_1 = state name, NAME_2 = district name.
    // ─────────────────────────────────────────────────────────────────────────
    private function _filter_geojson_by_state($decoded, $state, $iso2 = null)
    {
        $filtered   = [];
        $exclusions = $this->_state_district_exclusions($iso2, $state);

        foreach ($decoded['features'] as $feature) {
            $props = $feature['properties'] ?? [];
            // GADM: NAME_1 = state, NAME_2 = district; geoBoundaries uses shapeName
            $featureState = $props['NAME_1'] ?? $props['name'] ?? $props['shapeName'] ?? '';
            $district     = $props['NAME_2'] ?? $props['name'] ?? $props['shapeName'] ?? '';

            if ($this->_district_is_excluded($district, $exclusions)) {
                continue;
            }

            if ($this->_feature_matches_state($featureState, $district, $state, $iso2)) {
                $filtered[] = $feature;
            }
        }

        $out = [
            'type'     => 'FeatureCollection',
            'features' => $filtered,
        ];
        return json_encode($out);
    }

    private function _feature_matches_state($featureState, $district, $state, $iso2)
    {
        foreach ($this->_state_district_aliases($iso2, $state) as $dAlias) {
            if ($this->_geo_names_match($district, $dAlias)) {
                return true;
            }
        }

        $stateNorm = $this->_normalize_geo_name_for_match($state);
        $distNorm  = $this->_normalize_geo_name_for_match($district);
        if ($stateNorm !== '' && strlen($stateNorm) >= 4 && strpos($distNorm, $stateNorm) !== false) {
            return true;
        }

        if ($this->_geo_names_match($featureState, $state)) {
            return true;
        }

        foreach ($this->_state_name_aliases($iso2, $state) as $alias) {
            if ($this->_geo_names_match($featureState, $alias)) {
                return true;
            }
        }

        return false;
    }

    private function _state_rules_key($state)
    {
        return $this->_normalize_geo_name_for_match($state);
    }

    private function _state_geo_rule($iso2, $state)
    {
        $iso2 = strtoupper((string) $iso2);
        $key  = $this->_state_rules_key($state);

        return $this->state_geo_rules[$iso2][$key] ?? [];
    }

    private function _state_name_aliases($iso2, $state)
    {
        $rule = $this->_state_geo_rule($iso2, $state);

        return $rule['name_1_aliases'] ?? [];
    }

    private function _state_district_aliases($iso2, $state)
    {
        $rule = $this->_state_geo_rule($iso2, $state);

        return $rule['districts'] ?? [];
    }

    private function _state_district_exclusions($iso2, $state)
    {
        $rule = $this->_state_geo_rule($iso2, $state);

        return $rule['exclude_districts'] ?? [];
    }

    private function _district_is_excluded($district, $exclusions)
    {
        foreach ($exclusions as $excluded) {
            if ($this->_geo_names_match($district, $excluded)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize geographic names for comparison (GADM omits spaces: "JammuandKashmir").
     */
    private function _normalize_geo_name($name)
    {
        $n = strtolower(trim((string) $name));
        $n = preg_replace('/[^a-z0-9]+/', '', $n);
        return $n;
    }

    private function _geo_names_match($a, $b)
    {
        $normA = $this->_normalize_geo_name_for_match($a);
        $normB = $this->_normalize_geo_name_for_match($b);
        if ($normA === '' || $normB === '') {
            return false;
        }
        if ($normA === $normB) {
            return true;
        }
        return (strpos($normA, $normB) !== false || strpos($normB, $normA) !== false);
    }

    /**
     * Match key: ignore spaces/punctuation and optional "and"
     * (e.g. "Jammu & Kashmir" ↔ GADM "JammuandKashmir").
     */
    private function _normalize_geo_name_for_match($name)
    {
        return str_replace('and', '', $this->_normalize_geo_name($name));
    }

    /**
     * Normalize a state/province name from map labels or user input.
     */
    private function _canonicalize_state_name($state)
    {
        if ($state === null || $state === '') {
            return null;
        }

        $state = html_entity_decode((string) $state, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $state = strip_tags($state);
        $state = preg_replace('/\s*&\s*/', ' and ', $state);
        $state = preg_replace('/_+/', ' ', $state);
        $state = preg_replace('/\s+/', ' ', trim($state));

        return $state !== '' ? substr($state, 0, 100) : null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Replace placeholders in URL template
    // Supports: %ISO2%, %ISO2_LOWER%, %ISO3%, %STATE%
    // ─────────────────────────────────────────────────────────────────────────
    private function _resolve_url($template, $iso2, $state)
    {
        $url = $template;
        $url = str_replace('%ISO2%', strtoupper($iso2 ?? ''), $url);
        $url = str_replace('%ISO2_LOWER%', strtolower($iso2 ?? ''), $url);
        $url = str_replace('%STATE%', rawurlencode($state ?? ''), $url);

        if (strpos($url, '%ISO3%') !== false) {
            $iso3 = $iso2 ? $this->_get_iso3($iso2) : '';
            if (!$iso3) return false;
            $url = str_replace('%ISO3%', $iso3, $url);
        }

        if (preg_match('/%[A-Z_]+%/', $url)) return false;
        return $url;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Look up the 3-letter ISO code for a given ISO2 code from tblcountries.
    // ─────────────────────────────────────────────────────────────────────────
    private function _get_iso3($iso2)
    {
        $iso2 = strtoupper($iso2);
        if (isset($this->iso3_cache[$iso2])) {
            return $this->iso3_cache[$iso2];
        }

        $this->db->select('iso3')->where('iso2', $iso2)->limit(1);
        $row = $this->db->get(db_prefix() . 'countries')->row();
        $iso3 = $row ? strtoupper($row->iso3) : '';
        $this->iso3_cache[$iso2] = $iso3;
        return $iso3;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TopoJSON → GeoJSON converter (pure PHP, no external tools needed)
    // ─────────────────────────────────────────────────────────────────────────
    private function _topojson_to_geojson($topo, $level, $iso2 = null)
    {
        $objects = $topo['objects'] ?? [];
        if (empty($objects)) return false;
        
        $obj = $objects['default'] ?? reset($objects);
        if (!isset($obj['geometries'])) return false;

        $scale     = $topo['transform']['scale']     ?? [1, 1];
        $translate = $topo['transform']['translate'] ?? [0, 0];
        $arcs      = $topo['arcs'] ?? [];

        $decodedArcs = [];
        foreach ($arcs as $arc) {
            $coords = [];
            $x = 0; $y = 0;
            foreach ($arc as $pt) {
                $x += $pt[0]; $y += $pt[1];
                $coords[] = [
                    $x * $scale[0] + $translate[0],
                    $y * $scale[1] + $translate[1]
                ];
            }
            $decodedArcs[] = $coords;
        }

        $features = [];
        foreach ($obj['geometries'] as $geom) {
            $geoGeom = $this->_decode_topo_geometry($geom, $decodedArcs);
            if (!$geoGeom) continue;
            $features[] = [
                'type'       => 'Feature',
                'id'         => $geom['id'] ?? null,
                'properties' => $geom['properties'] ?? new stdClass(),
                'geometry'   => $geoGeom,
            ];
        }

        if (empty($features)) return false;
        return json_encode(['type' => 'FeatureCollection', 'features' => $features]);
    }

    private function _decode_topo_geometry($geom, $decodedArcs)
    {
        $type = $geom['type'] ?? '';
        switch ($type) {
            case 'Polygon':
                return ['type' => 'Polygon', 'coordinates' => $this->_decode_polygon_rings($geom['arcs'], $decodedArcs)];
            case 'MultiPolygon':
                $multi = [];
                foreach ($geom['arcs'] as $poly) {
                    $multi[] = $this->_decode_polygon_rings($poly, $decodedArcs);
                }
                return ['type' => 'MultiPolygon', 'coordinates' => $multi];
            default:
                return null;
        }
    }

    private function _decode_polygon_rings($ringsArcs, $decodedArcs)
    {
        $rings = [];
        foreach ($ringsArcs as $ringArcs) {
            $ringCoords = [];
            foreach ($ringArcs as $arcIdx) {
                $reversed = false;
                if ($arcIdx < 0) { $arcIdx = ~$arcIdx; $reversed = true; }
                $arcPts = $decodedArcs[$arcIdx] ?? [];
                if ($reversed) {
                    $arcPts = array_reverse($arcPts);
                }
                if (!empty($ringCoords) && !empty($arcPts)) {
                    array_shift($arcPts);
                }
                $ringCoords = array_merge($ringCoords, $arcPts);
            }
            $rings[] = $ringCoords;
        }
        return $rings;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Save GeoJSON to local file + update DB cache record
    // ─────────────────────────────────────────────────────────────────────────
    private function _save_and_cache($cache_key, $geojson, $local_rel, $level, $iso2, $state)
    {
        $decoded = json_decode($geojson, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($decoded['features'])) {
            return;
        }

        $abs = FCPATH . $local_rel;
        $dir = dirname($abs);

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        @file_put_contents($abs, $geojson);

        if (!$this->db->table_exists(db_prefix() . 'invoice_geojson_cache')) {
            return;
        }

        $urls = [];
        foreach (($this->geojson_sources[$level] ?? []) as $t) {
            $urls[] = $this->_resolve_url($t, $iso2, $state);
        }

        $this->db->replace(db_prefix() . 'invoice_geojson_cache', [
            'cache_key'  => $cache_key,
            'source_url' => implode(' | ', array_filter($urls)),
            'file_path'  => $local_rel,
            'cached_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Determine local file path from level + params
    // ─────────────────────────────────────────────────────────────────────────
    private function _local_path($level, $iso2, $state)
    {
        $root = rtrim($this->cache_root, '/') . '/';

        switch ($level) {
            case 'world':
                return $root . 'world.json';

            case 'country':
                // e.g. assets/geojson/countries/IN.json
                return $root . 'countries/' . strtoupper($iso2) . '.json';

            case 'state':
                // e.g. assets/geojson/states/IN/Gujarat.json
                $safe_state = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $state);
                return $root . 'states/' . strtoupper($iso2) . '/' . $safe_state . '.json';
        }

        return $root . 'misc_' . md5($level . $iso2 . $state) . '.json';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Stable cache key for DB record
    // ─────────────────────────────────────────────────────────────────────────
    private function _cache_key($level, $iso2, $state)
    {
        return substr($level . '_' . $iso2 . '_' . md5((string)$state), 0, 80);
    }

    // =========================================================================
    // GEOCODING — on-demand lat/lng for city scatter layer
    // =========================================================================

    /**
     * Lookup or geocode a single city; returns ['latitude','longitude'] or false.
     */
    public function geocode_city($city, $state, $iso2)
    {
        $iso2 = strtoupper($iso2);

        // 1. Check our geocache table
        $this->db->where('city',         trim($city));
        $this->db->where('state',        trim($state));
        $this->db->where('country_iso2', $iso2);
        $row = $this->db->get(db_prefix() . 'invoice_city_geocache')->row_array();

        if ($row && !$row['failed']) {
            return ['latitude' => $row['latitude'], 'longitude' => $row['longitude']];
        }

        // 2. Also check tblcities (existing geo database)
        $this->db->select('city_latitude, city_longitude');
        $this->db->where('city',    trim($city));
        $this->db->where('state',   trim($state));
        $this->db->where('country', $iso2);
        $city_row = $this->db->get(db_prefix() . 'cities')->row_array();

        if ($city_row && $city_row['city_latitude']) {
            $this->_upsert_geocache($city, $state, $iso2,
                $city_row['city_latitude'], $city_row['city_longitude']);
            return [
                'latitude'  => $city_row['city_latitude'],
                'longitude' => $city_row['city_longitude'],
            ];
        }

        // 3. Nominatim fallback (respectful rate-limit: 1 req/s)
        return $this->_nominatim_geocode($city, $state, $iso2);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Queue geocoding asynchronously (store as failed=1 initially, geocode later)
    // ─────────────────────────────────────────────────────────────────────────
    private function _queue_geocode($city, $state, $iso2)
    {
        // Only insert if not already in cache
        $this->db->where('city',         trim($city));
        $this->db->where('state',        trim($state));
        $this->db->where('country_iso2', strtoupper($iso2));
        $exists = $this->db->count_all_results(db_prefix() . 'invoice_city_geocache');

        if ($exists === 0) {
            $this->db->insert(db_prefix() . 'invoice_city_geocache', [
                'city'         => trim($city),
                'state'        => trim($state),
                'country_iso2' => strtoupper($iso2),
                'failed'       => 1,
                'geocoded_at'  => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function _nominatim_geocode($city, $state, $iso2)
    {
        $q   = urlencode(trim($city) . ', ' . trim($state) . ', ' . $iso2);
        $url = "https://nominatim.openstreetmap.org/search?q={$q}&format=json&limit=1&countrycodes=" . strtolower($iso2);

        $body = $this->_curl_get($url, ['User-Agent: CRM-InvoiceMap/1.0 (contact@example.com)']);
        if ($body === false) {
            $this->_upsert_geocache($city, $state, $iso2, null, null, true);
            return false;
        }

        $results = json_decode($body, true);
        if (empty($results[0])) {
            $this->_upsert_geocache($city, $state, $iso2, null, null, true);
            return false;
        }

        $lat = $results[0]['lat'];
        $lng = $results[0]['lon'];
        $this->_upsert_geocache($city, $state, $iso2, $lat, $lng);

        return ['latitude' => $lat, 'longitude' => $lng];
    }

    private function _upsert_geocache($city, $state, $iso2, $lat, $lng, $failed = false)
    {
        $this->db->replace(db_prefix() . 'invoice_city_geocache', [
            'city'         => trim($city),
            'state'        => trim($state),
            'country_iso2' => strtoupper($iso2),
            'latitude'     => $lat,
            'longitude'    => $lng,
            'failed'       => $failed ? 1 : 0,
            'geocoded_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function _apply_filters($filters, $alias = 'inv')
    {
        if (!empty($filters['date_from'])) {
            $this->db->where("{$alias}.date >=", to_sql_date($filters['date_from']));
        }
        if (!empty($filters['date_to'])) {
            $this->db->where("{$alias}.date <=", to_sql_date($filters['date_to']));
        }
        if (!empty($filters['status']) && is_array($filters['status'])) {
            $statuses = array_filter(array_map('intval', $filters['status']));
            if ($statuses) {
                $this->db->where_in("{$alias}.status", $statuses);
            }
        }
        if (!empty($filters['currency'])) {
            $this->db->where("{$alias}.currency", (int)$filters['currency']);
        }
        if (!empty($filters['gst_numbers']) && is_array($filters['gst_numbers'])) {
            $gst_vals = array_filter(array_map('trim', $filters['gst_numbers']));
            if ($gst_vals) {
                $this->db->where_in("{$alias}.gst_number", $gst_vals);
            }
        }
    }

    private function _city_where($iso2, $state, $city)
    {
        $this->db->where('inv.deleted_at IS NULL');
        $this->db->where('inv.status !=', 5);
        $this->db->where('c.iso2',                 strtoupper($iso2));
        $this->db->where('TRIM(inv.billing_state)', trim($state));
        $this->db->where('TRIM(inv.billing_city)',  trim($city));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // cURL GET with timeout and follow-redirects
    // ─────────────────────────────────────────────────────────────────────────
    private function _curl_get($url, $extra_headers = [])
    {
        if (!function_exists('curl_init')) {
            // Fallback to file_get_contents if cURL not available
            $ctx = stream_context_create([
                'http' => [
                    'timeout'     => 20,
                    'user_agent'  => 'CRM-InvoiceMap/1.0',
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
            CURLOPT_TIMEOUT        => 90,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_USERAGENT      => 'CRM-InvoiceMap/1.0',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING       => 'gzip, deflate',
        ]);

        if ($extra_headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $extra_headers);
        }

        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 200 && $code < 300 && $body !== false && strlen($body) > 10) {
            return $body;
        }

        return false;
    }
}
