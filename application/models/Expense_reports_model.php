<?php

class Expense_reports_model extends App_Model
{

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'expense_reports')->row_array();
        } else {
            return $this->db->get(db_prefix() . 'expense_reports')->result_array();
        }
        return [];
    }

    public function add($data)
    {
        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['start_date'] = to_sql_date($data['start_date'], true);
        $data['end_date'] = to_sql_date($data['end_date'], true);
        $data['status'] = "Draft";
        $data['status'] = "Draft";

        $this->db->insert(db_prefix() . 'expense_reports', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity("New Expense Report Created. ID [$insert_id]");
            return $insert_id;
        }
        return false;
    }

    public function update($id, $data, $update_log = true)
    {
        if (isset($data['start_date']) && !empty($data['start_date'])) {
            $data['start_date'] = to_sql_date($data['start_date'], true);
        }
        if (isset($data['end_date']) && !empty($data['end_date'])) {
            $data['end_date'] = to_sql_date($data['end_date'], true);
        }
        $this->db->where('id', $id);
        $check = $this->db->update(db_prefix() . 'expense_reports', $data);
        if ($check) {
            if ($update_log) {
                log_activity("Expense Report Updated. ID [$id]");
            }
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'expense_reports');
        if ($this->db->affected_rows() > 0) {

            $this->db->where('report_id', $id);
            $this->db->update(db_prefix() . 'expenses', ['report_id' => null]);

            $this->db->where('report_id', $id);
            $this->db->update(db_prefix() . 'expense_advance', ['report_id' => null]);

            log_activity("Expense Report Deleted. ID [$id]");
            return true;
        }
        return false;
    }

    public function get_trips()
    {
        $has_permission = has_permission('expense_trip', '', 'view');
        $staff_id = get_staff_user_id();
        if (!$has_permission) {
            $this->db->where('created_by', $staff_id);
        }
        $this->db->order_by('id', 'desc');
        $query = $this->db->get(db_prefix() . 'expense_trip');
        return $query->result_array();
    }

    public function get_expense_trips_by_report($id)
    {
        if (empty($id)) {
            return [];
        }
        $this->db->select('expense_trip.*, countries.short_name as country_name');
        $this->db->from(db_prefix() . 'expense_trip as expense_trip');
        $this->db->join(db_prefix() . 'countries as countries', 'countries.country_id = expense_trip.country', 'left');
        $this->db->where('expense_trip.id', $id);
        $query = $this->db->get();
        return $query->row_array();
    }


    public function get_expense_unreported_advances()
    {
        $has_permission = has_permission('expense_advance', '', 'view');
        $staff_id = get_staff_user_id();
        $this->db->group_start();
        $this->db->where('report_id', NULL);
        $this->db->or_where('report_id', '');
        $this->db->or_where('report_id', 0);
        $this->db->group_end();
        $this->db->where('status', 'Approved');
        if (!$has_permission) {
            $this->db->where('created_by', $staff_id);
        }
        $query = $this->db->get(db_prefix() . 'expense_advance');
        return $query->result_array();
    }

    public function get_expense_unreported_expenses()
    {
        $has_permission = has_permission('expenses', '', 'view');
        $staff_id = get_staff_user_id();
        $this->db->group_start();
        $this->db->where('report_id', NULL);
        $this->db->or_where('report_id', '');
        $this->db->or_where('report_id', 0);
        $this->db->group_end();
        if (!$has_permission) {
            $this->db->where('created_by', $staff_id);
        }
        $query = $this->db->get(db_prefix() . 'expenses');
        return $query->result_array();
    }

    public function get_expense_advances_by_report($id)
    {
        $this->db->where('report_id', $id);
        $this->db->where('status', "Approved");
        $query = $this->db->get(db_prefix() . 'expense_advance');
        return $query->result_array();
    }

    public function get_expenses_by_report($id)
    {
        $this->db->where('report_id', $id);
        $query = $this->db->get(db_prefix() . 'expenses');
        return $query->result_array();
    }

    public function get_expenses_attachment($id)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'expense');
        $file = $this->db->get(db_prefix() . 'files')->row();
        if ($file) {
            return $file;
        }
        return [];
    }
}
