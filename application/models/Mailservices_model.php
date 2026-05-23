<?php

class Mailservices_model extends App_Model
{
    public function get($id = '')
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'mail_services');
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            $data = $this->db->get()->row();
            return $data;
        }
        return $this->db->get()->result_array();
    }

    public function get_single($id)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'mail_services');
        $this->db->where('id', $id);
        $data = $this->db->get()->row();
        return $data;
    }

    public function add($data)
    {
        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'mail_services', $data);
        if ($this->db->affected_rows() > 0) {
            $insert_id = $this->db->insert_id();
            log_activity('Mail Service Created [ID: ' . $insert_id . ']');
            return $insert_id;
        }
        return false;
    }

    public function update($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'mail_services', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Mail Service Updated [ID: ' . $id . ']');
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'mail_services');
        if ($this->db->affected_rows() > 0) {
            log_activity('Mail Service Deleted [ID: ' . $id . ']');
            return true;
        }
        return false;
    }
}
