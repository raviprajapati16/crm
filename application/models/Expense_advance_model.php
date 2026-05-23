<?php

class Expense_advance_model extends App_Model
{

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'expense_advance')->row_array();
        } else {
            return $this->db->get(db_prefix() . 'expense_advance')->result_array();
        }
        return [];
    }

    public function add($data)
    {
        $data['status'] = "Pending";
        if (is_admin()) {
            $data['status'] = 'Approved';
            $data['status_changed_by'] = get_staff_user_id();
            $data['status_updated_at'] = date('Y-m-d H:i:s');
        }
        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['date'] = to_sql_date($data['date'], true);
        $this->db->insert(db_prefix() . 'expense_advance', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity("New Expense Advance Payment Record Created. ID [$insert_id]");
            return $insert_id;
        }
        return false;
    }

    public function update($id, $data, $status_change = false)
    {
        if (isset($data['date']) && !empty($data['date'])) {
            $data['date'] = to_sql_date($data['date'], true);
        }
        $this->db->where('id', $id);
        $check = $this->db->update(db_prefix() . 'expense_advance', $data);
        if ($check) {
            if ($status_change) {
                if ($data['status'] == 'Rejected') {
                    log_activity("Advance Payment Record Rejected. ID [$id]");
                } else {
                    log_activity("Advance Payment Record Approved. ID [$id]");
                }
            } else {
                log_activity("Advance Payment Record Updated. ID [$id]");
            }
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'expense_advance');
        if ($this->db->affected_rows() > 0) {
            log_activity("Advance Payment Record Deleted. ID [$id]");
            return true;
        }
        return false;
    }

    public function get_staff()
    {
        $this->db->select('staffid, firstname, lastname');
        $this->db->where('active', 1);
        $this->db->where('datedeleted IS NULL');
        return $this->db->get(db_prefix() . 'staff')->result_array();
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

    public function get_reports()
    {
        $has_permission = has_permission('expense_reports', '', 'view');
        $staff_id = get_staff_user_id();
        if (!$has_permission) {
            $this->db->where('created_by', $staff_id);
        }
        $this->db->order_by('id', 'desc');
        $this->db->where('status', "Draft");
        $query = $this->db->get(db_prefix() . 'expense_reports');
        return $query->result_array();
    }
}
