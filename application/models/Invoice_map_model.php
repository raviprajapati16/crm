<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Invoice_map_model
 *
 * Provides:
 *  - Aggregated invoice data at World / Country / State level
 *  - City-level invoice list
 *  - Local-first GeoJSON (geoBoundaries gbOpen via Geojson_dataset)
 *  - Geocoding cache for lat/lng scatter fallback
 */
class Invoice_map_model extends App_Model
{
    /** @var array<string, array> */
    private $mapping_cache = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->library('geojson_dataset');
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
    // GEOJSON — Local-first boundaries (geoBoundaries via Geojson_dataset)
    // =========================================================================

    /**
     * Return GeoJSON string for world | country | state.
     * Downloads boundaries only on cache miss via Geojson_dataset (geoBoundaries API).
     */
    public function get_geojson($level, $iso2 = null, $state = null, $stateIso = null)
    {
        try {
            $iso2  = $iso2 ? strtoupper($iso2) : null;
            $state = $this->_canonicalize_state_name($state);

            if ($level === 'state' && $iso2) {
                $lookup = $stateIso ?: $state;
                if ($lookup) {
                    $resolved = $this->geojson_dataset->resolve_adm1_name($iso2, $lookup);
                    if ($resolved) {
                        $state = $resolved;
                    }
                }
            }

            @ini_set('memory_limit', '1536M');
            @set_time_limit(120);

            $this->_ensure_geojson_files($level, $iso2);

            $stateKey = ($level === 'state' && $iso2 && $state)
                ? $this->geojson_dataset->state_cache_slug($iso2, $state)
                : $state;

            $local = $this->_local_path($level, $iso2, $stateKey);

            if ($level === 'state' && $iso2 && $state && !file_exists(FCPATH . $local)) {
                if (!$this->_build_state_file_from_adm2($iso2, $state, $local)) {
                    $this->_build_state_file_from_adm1_fallback($iso2, $state, $local);
                }
            }

            if (!file_exists(FCPATH . $local)) {
                return false;
            }

            return $this->_finalize_state_geojson(
                file_get_contents(FCPATH . $local),
                $level,
                $iso2,
                $state,
                $local
            );
        } catch (Throwable $e) {
            log_message('error', 'Invoice_map_model::get_geojson — ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Refresh boundary files (world + countries). Used by cron / admin.
     *
     * @return array{ok: array, failed: array, skipped: array}
     */
    public function refresh_geojson_dataset(array $options = [])
    {
        $defaults = [
            'world'     => true,
            'countries' => 'active',
            'force'     => false,
        ];

        return $this->geojson_dataset->refresh(array_merge($defaults, $options));
    }

    public function get_geojson_version_token()
    {
        return $this->geojson_dataset->get_version_token();
    }

    private function _ensure_geojson_files($level, $iso2)
    {
        if ($level === 'world') {
            $this->geojson_dataset->ensure_world();

            return;
        }

        if (!$iso2) {
            return;
        }

        $this->geojson_dataset->ensure_country($iso2);

        if ($level === 'state') {
            $this->geojson_dataset->ensure_country_adm2($iso2);
        }
    }

    private function _build_state_file_from_adm2($iso2, $state, $local_rel)
    {
        $adm2_rel = $this->geojson_dataset->local_path('country_adm2', $iso2);
        $adm2_abs = FCPATH . $adm2_rel;

        if (!file_exists($adm2_abs)) {
            return false;
        }

        $decoded = json_decode(file_get_contents($adm2_abs), true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($decoded['features'])) {
            return false;
        }

        $features = $this->geojson_dataset->filter_adm2_features($decoded['features'], $iso2, $state, false);
        if (empty($features)) {
            $features = $this->geojson_dataset->filter_adm2_features($decoded['features'], $iso2, $state, true);
        }

        if (empty($features)) {
            return false;
        }

        $filtered = json_encode([
            'type'     => 'FeatureCollection',
            'features' => $features,
        ]);
        $after = json_decode($filtered, true);

        if (empty($after['features'])) {
            return false;
        }

        $abs = FCPATH . ltrim($local_rel, '/');
        $dir = dirname($abs);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        return file_put_contents($abs, $filtered) !== false;
    }

    /**
     * When geoBoundaries has no ADM2 subdivisions for a state (federal cities, etc.),
     * use the ADM1 boundary as a single district map.
     */
    private function _build_state_file_from_adm1_fallback($iso2, $state, $local_rel)
    {
        $collection = $this->geojson_dataset->build_state_collection_from_adm1($iso2, $state);
        if (empty($collection['features'])) {
            return false;
        }

        $abs = FCPATH . ltrim($local_rel, '/');
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

        if ($afterCount === 0 && $beforeCount > 0) {
            $firstProps = $decoded['features'][0]['properties'] ?? [];
            if (!empty($firstProps['_from_adm1_fallback'])) {
                return $body;
            }
        }

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
    // Filter ADM2 districts for the selected state / province
    // ─────────────────────────────────────────────────────────────────────────
    private function _filter_geojson_by_state($decoded, $state, $iso2 = null)
    {
        $exclusions = $this->_state_district_exclusions($iso2, $state);
        $matched    = [];
        $seen       = [];

        $add = function (array $feature) use (&$matched, &$seen, $exclusions) {
            $props    = $feature['properties'] ?? [];
            $district = $props['shapeName'] ?? $props['NAME_2'] ?? $props['name'] ?? '';
            $id       = (string) ($props['shapeID'] ?? $district);

            if ($this->_district_is_excluded($district, $exclusions) || isset($seen[$id])) {
                return;
            }

            $seen[$id]  = true;
            $matched[] = $feature;
        };

        foreach ($this->geojson_dataset->filter_adm2_features($decoded['features'], $iso2, $state) as $feature) {
            $add($feature);
        }

        foreach ($decoded['features'] as $feature) {
            $props        = $feature['properties'] ?? [];
            $featureState = $props['NAME_1'] ?? $props['parent_name'] ?? $props['shapeGroup'] ?? $props['name'] ?? '';
            $district     = $props['shapeName'] ?? $props['NAME_2'] ?? $props['name'] ?? '';

            if ($this->_district_is_excluded($district, $exclusions)) {
                continue;
            }

            if ($this->_feature_matches_state($featureState, $district, $state, $iso2)) {
                $add($feature);
            }
        }

        return json_encode([
            'type'     => 'FeatureCollection',
            'features' => $matched,
        ]);
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
        $map  = $this->_load_country_mapping($iso2);
        $rules = $map['state_rules'] ?? [];

        return $rules[$key] ?? [];
    }

    private function _state_name_aliases($iso2, $state)
    {
        $rule = $this->_state_geo_rule($iso2, $state);

        return $rule['name_1_aliases'] ?? [];
    }

    private function _state_district_aliases($iso2, $state)
    {
        $rule    = $this->_state_geo_rule($iso2, $state);
        $aliases = $rule['districts'] ?? [];

        $map = $this->_load_country_mapping($iso2);
        $crm = $map['crm_state_aliases'] ?? [];
        $lower = strtolower(trim((string) $state));

        if (!empty($crm[$lower])) {
            $aliases = array_merge($aliases, $crm[$lower]);
        }

        $normKey = $this->_normalize_geo_name_for_match($state);
        foreach ($crm as $aliasKey => $districts) {
            if ($this->_normalize_geo_name_for_match($aliasKey) === $normKey) {
                $aliases = array_merge($aliases, $districts);
            }
        }

        return array_values(array_unique($aliases));
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

    private function _load_country_mapping($iso2)
    {
        $iso2 = strtoupper((string) $iso2);
        if (isset($this->mapping_cache[$iso2])) {
            return $this->mapping_cache[$iso2];
        }

        $path = FCPATH . 'assets/geojson/mappings/' . $iso2 . '.json';
        if (!file_exists($path)) {
            $this->mapping_cache[$iso2] = [];

            return [];
        }

        $data = json_decode(file_get_contents($path), true);
        $this->mapping_cache[$iso2] = is_array($data) ? $data : [];

        return $this->mapping_cache[$iso2];
    }

    /**
     * Normalize geographic names for comparison.
     */
    private function _normalize_geo_name($name)
    {
        return $this->geojson_dataset->match_key($name);
    }

    private function _geo_names_match($a, $b)
    {
        return $this->geojson_dataset->geo_names_match($a, $b);
    }

    private function _normalize_geo_name_for_match($name)
    {
        return $this->geojson_dataset->match_key($name);
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

    private function _local_path($level, $iso2, $state)
    {
        if ($level === 'state') {
            return $this->geojson_dataset->local_path('state', $iso2, $state);
        }

        return $this->geojson_dataset->local_path($level, $iso2, $state);
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
        $this->db->where('city',         trim($city));
        $this->db->where('state',        trim($state));
        $this->db->where('country_code', $iso2);
        $city_row = $this->db->get(db_prefix() . 'cities')->row_array();

        if ($city_row && $city_row['city_latitude']) {
            $this->_upsert_geocache($city, $state, $iso2,
                $city_row['city_latitude'], $city_row['city_longitude']);
            return [
                'latitude'  => $city_row['city_latitude'],
                'longitude' => $city_row['city_longitude'],
            ];
        }

        // 3. OpenCage via App_geocoder (server-side)
        return $this->_external_geocode($city, $state, $iso2);
    }

    /**
     * Queue a city for background geocoding (non-blocking; safe during map AJAX).
     */
    public function queue_city_geocode($city, $state, $iso2)
    {
        $this->_queue_geocode($city, $state, $iso2);
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

    private function _external_geocode($city, $state, $iso2)
    {
        $this->load->library('app_geocoder');
        $query  = trim($city) . ', ' . trim($state) . ', ' . strtoupper($iso2);
        $coords = $this->app_geocoder->get_coordinate($query);

        if (!$coords) {
            $this->_upsert_geocache($city, $state, $iso2, null, null, true);

            return false;
        }

        $this->_upsert_geocache($city, $state, $iso2, $coords['latitude'], $coords['longitude']);

        return [
            'latitude'  => $coords['latitude'],
            'longitude' => $coords['longitude'],
        ];
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

    private function _parse_filter_date($date)
    {
        if ($date === '' || $date === null) {
            return null;
        }

        $from_format = get_current_date_format(true);
        $dt          = date_create_from_format($from_format, trim($date));

        if ($dt === false) {
            return null;
        }

        return $dt->format('Y-m-d');
    }

    private function _apply_filters($filters, $alias = 'inv')
    {
        $this->db->where("{$alias}.deleted_at IS NULL");

        if (!empty($filters['date_from'])) {
            $sqlDateFrom = $this->_parse_filter_date($filters['date_from']);
            if ($sqlDateFrom) {
                $this->db->where("{$alias}.date >=", $sqlDateFrom);
            }
        }
        if (!empty($filters['date_to'])) {
            $sqlDateTo = $this->_parse_filter_date($filters['date_to']);
            if ($sqlDateTo) {
                $this->db->where("{$alias}.date <=", $sqlDateTo);
            }
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
        $this->db->where('inv.status !=', 5);
        $this->db->where('c.iso2', strtoupper($iso2));
        if ($state !== null && $state !== '') {
            $this->db->where('TRIM(inv.billing_state)', trim($state));
        }
        if ($city !== null && $city !== '') {
            $this->db->where('TRIM(inv.billing_city)', trim($city));
        }
    }
}
