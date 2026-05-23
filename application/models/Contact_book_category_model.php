<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contact_book_category_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_category()
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'contact_book_category');
        return $this->db->get()->result_array();
    }

    public function get($id)
    {
        $this->db->select('*');
        $this->db->where('id', $id);
        $this->db->from(db_prefix() . 'contact_book_category');
        return $this->db->get()->row_array();
    }


    public function insert($data)
    {
        $this->db->insert(db_prefix() . 'contact_book_category', $data);
        $id = $this->db->insert_id();
        if ($id) {
            log_activity('Contact Book New Category Created [ID: ' . $id . ']');
            return $id;
        }
        return false;
    }

    public function update($id, $data)
    {
        $table = db_prefix() . 'contact_book_category';
        $this->db->where('id', $id);
        $this->db->update($table, $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Contact Book Category Update [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'contact_book_category');
        if ($this->db->affected_rows() > 0) {
            log_activity('Contact Book Category Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }
}
