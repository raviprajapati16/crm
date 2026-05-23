<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Expenses_merchants_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_merchant($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'expense_merchant')->row();
        }
        $this->db->order_by('name', 'asc');

        return $this->db->get(db_prefix() . 'expense_merchant')->result_array();
    }

    public function add_merchant($data)
    {
        $data['details'] = nl2br($data['details']);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'expense_merchant', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Expense Merchant Created [ID: ' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    public function update_merchant($data, $id)
    {
        $data['details'] = nl2br($data['details']);
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'expense_merchant', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Expense Merchant Updated [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    public function delete_merchant($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'expense_merchant');
        if ($this->db->affected_rows() > 0) {
            log_activity('Expense Merchant Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

}
