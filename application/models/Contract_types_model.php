<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contract_types_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }


    public function add($data)
    {
        $this->db->insert(db_prefix() . 'contracts_types', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Agreement Type Added [' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    public function add_sub_type($data)
    {
        $this->db->insert(db_prefix() . 'contract_subtype', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Agreement Sub Type Added [' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    public function add_draft($data)
    {
        $this->db->insert(db_prefix() . 'contract_draft', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Agreement Draft Added [' . $data['draft_title'] . ']');

            return $insert_id;
        }

        return false;
    }


    public function update($data, $id, $isDelete = false)
    {
        if ($isDelete) {
            if (is_reference_in_table('contract_type', db_prefix() . 'contracts', $id, 'deleted_at', NULL)) {
                return [
                    'referenced' => true,
                    'message' => "Unable to delete. contracts exists with this type."
                ];
            }
            if (is_reference_in_table('main_type', db_prefix() . 'contract_subtype', $id, 'deleted_at', NULL)) {
                return [
                    'referenced' => true,
                    'message' => "Unable to delete. due to sub type exists."
                ];
            }
            $data['deleted_by'] = get_staff_full_name();
            $data['deleted_at'] = date('Y-m-d H:i:s');
        } else {
            $data['updated_by'] = get_staff_user_id();
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'contracts_types', $data);
        if ($this->db->affected_rows() > 0) {
            if ($isDelete) {
                log_activity('Agreement Type Deleted [ID:' . $id . ']');
            } else {
                log_activity('Agreement Type Updated [' . $data['name'] . ', ID:' . $id . ']');
            }
            return true;
        }
        return false;
    }

    public function update_sub_type($data, $id, $isDelete = false)
    {
        if ($isDelete) {
            if (is_reference_in_table('sub_type', db_prefix() . 'contracts', $id, 'deleted_at', NULL)) {
                return [
                    'referenced' => true,
                    'message' => "Unable to delete. contracts exists with this sub type."
                ];
            }
            if (is_reference_in_table('sub_type', db_prefix() . 'contract_draft', $id, 'deleted_at', NULL)) {
                return [
                    'referenced' => true,
                    'message' => "Unable to delete. due to draft(s) exists with sub type."
                ];
            }
            $data['deleted_by'] = get_staff_full_name();
            $data['deleted_at'] = date('Y-m-d H:i:s');
        } else {
            $data['updated_by'] = get_staff_user_id();
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'contract_subtype', $data);
        if ($this->db->affected_rows() > 0) {
            if ($isDelete) {
                log_activity('Agreement Sub Type Deleted [ID:' . $id . ']');
            } else {
                log_activity('Agreement Sub Type Updated [' . $data['name'] . ', ID:' . $id . ']');
            }
            return true;
        }
        return false;
    }

    public function update_draft($data, $id, $isDelete = false)
    {
        if ($isDelete) {
            if (is_reference_in_table('draft_id', db_prefix() . 'contracts', $id, 'deleted_at', NULL)) {
                return [
                    'referenced' => true,
                    'message' => "Unable to delete draft. contracts exists with this draft."
                ];
            }
            $data['deleted_by'] = get_staff_full_name();
            $data['deleted_at'] = date('Y-m-d H:i:s');
        } else {
            $data['updated_by'] = get_staff_user_id();
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'contract_draft', $data);
        if ($this->db->affected_rows() > 0) {
            if ($isDelete) {
                log_activity('Agreement Draft Deleted [ID:' . $id . ']');
            } else {
                log_activity('Agreement Draft Updated [' . $data['draft_title'] . ', ID:' . $id . ']');
            }
            return true;
        }
        return false;
    }

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'contracts_types')->row();
        }

        $types = $this->app_object_cache->get('contract-types');

        if (!$types && !is_array($types)) {
            $this->db->where('deleted_at IS NULL');
            $types = $this->db->get(db_prefix() . 'contracts_types')->result_array();
            $this->app_object_cache->add('contract-types', $types);
        }

        return $types;
    }

    public function get_sub_type_single($id = '')
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'contract_subtype')->row();
    }

    public function get_sub_types($id = '')
    {
        $this->db->where('main_type', $id);
        return $this->db->get(db_prefix() . 'contracts_types')->result_array();
    }

    public function get_draft_single($id = '')
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'contract_draft')->row();
    }

    public function delete($id)
    {
        if (is_reference_in_table('contract_type', db_prefix() . 'contracts', $id)) {
            return [
                'referenced' => true,
                'message' => "Unable to delete. contracts exists with this type."
            ];
        }
        if (is_reference_in_table('main_type', db_prefix() . 'contract_subtype', $id)) {
            return [
                'referenced' => true,
                'message' => "Unable to delete. due to sub type exists."
            ];
        }
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'contracts_types');
        if ($this->db->affected_rows() > 0) {
            log_activity('Agreement Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    public function get_chart_data()
    {
        $labels = [];
        $totals = [];
        $types  = $this->get();
        foreach ($types as $type) {
            $total_rows_where = 'contract_type = ' . $type['id'] . ' AND trash = 0 AND deleted_at IS NULL';
            if (is_client_logged_in()) {
                $total_rows_where .= ' AND not_visible_to_client = 0 AND client = ' . get_client_user_id();
            } else {
                if (!has_permission('contracts', '', 'view')) {
                    if (manager_employee_data_access_permission_check("contracts")) {
                        $total_rows_where .= ' AND addedfrom IN (' . get_manager_assigned_staff_ids('', true) . ')';
                    } else {
                        $total_rows_where .= ' AND addedfrom = ' . get_staff_user_id();
                    }
                }
            }
            $total_rows = total_rows(db_prefix() . 'contracts', $total_rows_where);
            if ($total_rows == 0 && is_client_logged_in()) {
                continue;
            }
            array_push($labels, $type['name']);
            array_push($totals, $total_rows);
        }
        $chart = [
            'labels'   => $labels,
            'datasets' => [
                [
                    'label'           => _l('contract_summary_by_type'),
                    'backgroundColor' => 'rgba(3,169,244,0.2)',
                    'borderColor'     => '#03a9f4',
                    'borderWidth'     => 1,
                    'data'            => $totals,
                ],
            ],
        ];

        return $chart;
    }

    public function get_values_chart_data()
    {
        $labels = [];
        $totals = [];
        $types  = $this->get();
        foreach ($types as $type) {
            array_push($labels, $type['name']);

            $where = [
                'where' => ['contract_type = ' . $type['id'] . ' AND trash = 0 AND deleted_at IS NULL'],
                'field' => 'contract_value',
            ];

            if (!has_permission('contracts', '', 'view')) {
                if (manager_employee_data_access_permission_check("contracts")) {
                    array_push($where['where'], 'addedfrom IN (' . get_manager_assigned_staff_ids('', true) . ')');
                } else {
                    array_push($where['where'], 'addedfrom = ' . get_staff_user_id());
                }
            }
            $total = sum_from_table(db_prefix() . 'contracts', $where);
            if ($total == null) {
                $total = 0;
            }
            array_push($totals, $total);
        }
        $chart = [
            'labels'   => $labels,
            'datasets' => [
                [
                    'label'           => _l('contract_summary_by_type_value'),
                    'backgroundColor' => 'rgba(37,155,35,0.2)',
                    'borderColor'     => '#84c529',
                    'tension'         => false,
                    'borderWidth'     => 1,
                    'data'            => $totals,
                ],
            ],
        ];

        return $chart;
    }

    function get_all_main_types()
    {
        $this->db->where('deleted_at IS NULL');
        return $this->db->get(db_prefix() . 'contracts_types')->result_array();
    }

    function get_all_sub_types($main_type)
    {
        $this->db->where('main_type', $main_type);
        $this->db->where('deleted_at IS NULL');
        return $this->db->get(db_prefix() . 'contract_subtype')->result_array();
    }

    function get_single_sub_type($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'contract_subtype')->row_array();
    }

    function get_all_drafts($main_type, $sub_type)
    {
        $this->db->where('main_type', $main_type);
        $this->db->where('sub_type', $sub_type);
        $this->db->where('deleted_at IS NULL');
        return $this->db->get(db_prefix() . 'contract_draft')->result_array();
    }

    function get_single_draft($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'contract_draft')->row_array();
    }

    function insert_data($table, $data)
    {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    function check_data($table, $where)
    {
        $this->db->where($where);
        $this->db->where('deleted_at IS NULL');
        return $this->db->get($table)->result_array();
    }
}
