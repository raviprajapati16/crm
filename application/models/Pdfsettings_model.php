<?php

class Pdfsettings_model extends App_Model
{
    public function get($id = '')
    {
        $this->db->select('pdf_settings.*, files.file_name, files.filetype');
        $this->db->from(db_prefix() . 'pdf_settings pdf_settings');
        $this->db->join(db_prefix() . 'files files', 'files.rel_id = pdf_settings.id AND files.rel_type = "pdf_settings"', 'left');
        if (is_numeric($id)) {
            $this->db->where('pdf_settings.id', $id);
            $data = $this->db->get()->row();
            return $data;
        }
        return $this->db->get()->result_array();
    }

    public function update($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'pdf_settings', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('PDF Settings Updated [ID: ' . $id . ']');
            return true;
        }
        return false;
    }
}
