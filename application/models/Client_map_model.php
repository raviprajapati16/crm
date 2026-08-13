<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Client_map_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // =========================================================================
    // LEVEL 0 — World: customer count per country
    // =========================================================================
    public function get_world_data($filters = [])
    {
        $has_permission_view = has_permission('customers', '', 'view');

        $this->db->select('
            c.iso2                           AS iso_code,
            c.short_name                      AS name,
            COUNT(cl.userid)                 AS value
        ');
        $this->db->from(db_prefix() . 'clients cl');
        $this->db->join(
            db_prefix() . 'countries c',
            'c.country_id = cl.country',
            'left'
        );
        $this->_apply_filters($filters, 'cl');
        if (!$has_permission_view) {
            $this->db->where('cl.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')');
        }
        $this->db->group_by('c.iso2, c.short_name');
        $this->db->having('value >', 0);

        $rows = $this->db->get()->result_array();

        foreach ($rows as &$r) {
            $r['value'] = (int) $r['value'];
        }
        return $rows;
    }

    // =========================================================================
    // LEVEL 1 — Country: state-level aggregation for a given ISO2 code
    // =========================================================================
    public function get_country_data($iso2, $filters = [])
    {
        $has_permission_view = has_permission('customers', '', 'view');

        $this->db->select('
            TRIM(cl.state)                   AS name,
            COUNT(cl.userid)                 AS value
        ');
        $this->db->from(db_prefix() . 'clients cl');
        $this->db->join(
            db_prefix() . 'countries c',
            'c.country_id = cl.country',
            'left'
        );
        $this->db->where('c.iso2', strtoupper($iso2));
        $this->db->where('cl.state !=', '');
        $this->db->where('cl.state IS NOT NULL');
        $this->_apply_filters($filters, 'cl');
        if (!$has_permission_view) {
            $this->db->where('cl.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')');
        }
        $this->db->group_by('TRIM(cl.state)');
        $this->db->having('value >', 0);
        $this->db->order_by('value', 'DESC');

        $rows = $this->db->get()->result_array();
        foreach ($rows as &$r) {
            $r['value'] = (int) $r['value'];
        }
        return $rows;
    }

    // =========================================================================
    // LEVEL 2 — State: city-level aggregation with lat/lng from geocache
    // =========================================================================
    public function get_state_data($iso2, $state, $filters = [])
    {
        $has_permission_view = has_permission('customers', '', 'view');

        $this->db->select('
            TRIM(cl.city) AS name,
            COUNT(cl.userid) AS value,
            geo.latitude,
            geo.longitude
        ');
        $this->db->from(db_prefix() . 'clients cl');
        $this->db->join(
            db_prefix() . 'countries c',
            'c.country_id = cl.country',
            'left'
        );
        // Use existing invoice geocache table for coordinates
        $this->db->join(
            db_prefix() . 'invoice_city_geocache geo',
            'geo.city = TRIM(cl.city)
             AND geo.state = TRIM(cl.state)
             AND geo.country_iso2 = c.iso2',
            'left'
        );
        $this->db->where('c.iso2', strtoupper($iso2));
        $this->db->where('TRIM(cl.state)', trim($state));
        $this->db->where('cl.city !=', '');
        $this->db->where('cl.city IS NOT NULL');
        $this->_apply_filters($filters, 'cl');
        if (!$has_permission_view) {
            $this->db->where('cl.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')');
        }
        $this->db->group_by('TRIM(cl.city), geo.latitude, geo.longitude');
        $this->db->having('value >', 0);
        $this->db->order_by('value', 'DESC');

        $rows = $this->db->get()->result_array();

        $this->load->model('invoice_map_model');

        foreach ($rows as &$r) {
            $r['value']     = (int) $r['value'];
            $r['latitude']  = $r['latitude']  ? (float) $r['latitude']  : null;
            $r['longitude'] = $r['longitude'] ? (float) $r['longitude'] : null;

            if ($r['latitude'] === null && $r['name'] !== '') {
                $this->invoice_map_model->queue_city_geocode($r['name'], $state, $iso2);
            }
        }
        return $rows;
    }

    // =========================================================================
    // LEVEL 3 — City: client list (paginated, 100/page)
    // =========================================================================
    public function get_city_clients($iso2, $state, $city, $filters = [], $page = 0)
    {
        $has_permission_view = has_permission('customers', '', 'view');

        $per_page = 100;
        $offset   = $page * $per_page;

        $this->db->select('COUNT(cl.userid) AS cnt');
        $this->db->from(db_prefix() . 'clients cl');
        $this->db->join(db_prefix() . 'countries c', 'c.country_id = cl.country', 'left');
        $this->_city_where($iso2, $state, $city);
        $this->_apply_filters($filters, 'cl');
        if (!$has_permission_view) {
            $this->db->where('cl.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')');
        }
        $summary = $this->db->get()->row_array();

        $this->db->select('
            cl.userid,
            cl.company,
            cl.phonenumber,
            cl.active,
            cl.datecreated,
            (SELECT GROUP_CONCAT(name SEPARATOR ", ") 
             FROM ' . db_prefix() . 'customers_groups cg 
             JOIN ' . db_prefix() . 'customer_groups csg ON csg.groupid = cg.id 
             WHERE csg.customer_id = cl.userid) as groups
        ');
        $this->db->from(db_prefix() . 'clients cl');
        $this->db->join(db_prefix() . 'countries c', 'c.country_id = cl.country', 'left');
        $this->_city_where($iso2, $state, $city);
        $this->_apply_filters($filters, 'cl');
        if (!$has_permission_view) {
            $this->db->where('cl.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')');
        }
        $this->db->order_by('cl.company', 'ASC');
        $this->db->limit($per_page, $offset);
        $clients = $this->db->get()->result_array();

        return [
            'count'   => (int) $summary['cnt'],
            'clients' => $clients,
        ];
    }

    // =========================================================================
    // EXPORT: Raw client data for CSV based on current level & filters
    // =========================================================================
    public function get_export_clients($level, $iso2, $state, $city, $filters = [])
    {
        $has_permission_view = has_permission('customers', '', 'view');

        $this->db->select('
            cl.userid,
            cl.company,
            cl.phonenumber,
            cl.active,
            c.short_name AS country,
            cl.state,
            cl.city,
            cl.datecreated,
            (SELECT GROUP_CONCAT(name SEPARATOR ", ") 
             FROM ' . db_prefix() . 'customers_groups cg 
             JOIN ' . db_prefix() . 'customer_groups csg ON csg.groupid = cg.id 
             WHERE csg.customer_id = cl.userid) as groups
        ');
        $this->db->from(db_prefix() . 'clients cl');
        $this->db->join(db_prefix() . 'countries c', 'c.country_id = cl.country', 'left');

        if ($level === 'country' || $level === 'state' || $level === 'city') {
            if ($iso2) $this->db->where('c.iso2', strtoupper($iso2));
        }
        if (($level === 'state' || $level === 'city') && $state) {
            $this->db->where('TRIM(cl.state)', trim($state));
        }
        if ($level === 'city' && $city) {
            $this->db->where('TRIM(cl.city)', trim($city));
        }

        $this->_apply_filters($filters, 'cl');

        if (!$has_permission_view) {
            $this->db->where('cl.userid IN (SELECT customer_id FROM ' . db_prefix() . 'customer_admins WHERE staff_id=' . get_staff_user_id() . ')');
        }
        $this->db->order_by('cl.company', 'ASC');

        return $this->db->get()->result_array();
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function _apply_filters($filters, $alias = 'cl')
    {
        $this->db->where("{$alias}.deleted_at IS NULL");

        if (isset($filters['exclude_inactive']) && $filters['exclude_inactive'] == '1') {
            $this->db->where("{$alias}.active", 1);
        }

        if (!empty($filters['groups']) && is_array($filters['groups'])) {
            $groups = array_filter(array_map('intval', $filters['groups']));
            if ($groups) {
                $this->db->where("{$alias}.userid IN (SELECT customer_id FROM " . db_prefix() . "customer_groups WHERE groupid IN (" . implode(',', $groups) . "))");
            }
        }
    }

    private function _city_where($iso2, $state, $city)
    {
        $this->db->where('c.iso2', strtoupper($iso2));
        if ($state !== null && $state !== '') {
            $this->db->where('TRIM(cl.state)', trim($state));
        }
        if ($city !== null && $city !== '') {
            $this->db->where('TRIM(cl.city)', trim($city));
        }
    }
}
