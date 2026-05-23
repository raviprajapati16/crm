<?php

class Expense_trip_model extends App_Model
{

    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'expense_trip')->row_array();
        } else {
            $this->db->order_by('id', 'desc');
            return $this->db->get(db_prefix() . 'expense_trip')->result_array();
        }
        return [];
    }

    public function add($data)
    {
        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'expense_trip', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity("New Expense Trip Created. ID [$insert_id]");
            return $insert_id;
        }
        return false;
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $check = $this->db->update(db_prefix() . 'expense_trip', $data);
        if ($check) {
            log_activity("Expense Trip Updated. ID [$id]");
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'expense_trip');
        if ($this->db->affected_rows() > 0) {
            log_activity("Expense Trip Deleted. ID [$id]");
            return true;
        }
        return false;
    }
}
