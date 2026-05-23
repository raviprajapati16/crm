<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Google_sheets_model extends App_Model
{
    public function add_sheet($data)
    {
        $this->db->insert(db_prefix() . 'google_sheets', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Google Sheet Added [ID: ' . $insert_id . ', Title: ' . $data['sheet_title'] . ']');
            return $insert_id;
        }

        return false;
    }

    public function add_sheet_record($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'google_sheets_data', $data);
        return $this->db->insert_id();
    }

    public function get_sheets($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'google_sheets')->row();
        }
        return $this->db->get(db_prefix() . 'google_sheets')->result_array();
    }

    public function get_sheet_records($sheet_id, $imported = null)
    {
        $this->db->where('sheet_id', $sheet_id);
        if ($imported !== null) {
            $this->db->where('is_imported', $imported ? 1 : 0);
        }
        return $this->db->get(db_prefix() . 'google_sheets_data')->result_array();
    }

    public function delete_sheet($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'google_sheets');
        return $this->db->affected_rows() > 0;
    }

    public function delete_sheet_records($sheet_id)
    {
        $this->db->where('sheet_id', $sheet_id);
        $this->db->delete(db_prefix() . 'google_sheets_data');
        return true;
    }

    public function update_sheet($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'google_sheets', $data);

        return $this->db->affected_rows() > 0 || $this->db->affected_rows() === 0;
    }

    public function update_sheet_records($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'google_sheets_data', $data);

        return $this->db->affected_rows() > 0 || $this->db->affected_rows() === 0;
    }

    public function get_sample_record($sheet_id)
    {
        $this->db->where('sheet_id', $sheet_id);
        $this->db->limit(1);
        return $this->db->get(db_prefix() . 'google_sheets_data')->row();
    }
}
