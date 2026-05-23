<?php

class Brochure_model extends App_Model
{

    public function add($data)
    {
        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'brochure', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity("Brochure Created. ID [$insert_id]");
            return $insert_id;
        }
        return false;
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $check = $this->db->update(db_prefix() . 'brochure', $data);
        if ($check) {
            log_activity("Brochure Updated. ID [$id]");
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        $this->db->trans_start();
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'brochure');
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            return false;
        }
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'brochure');
        $this->db->delete(db_prefix() . 'customer_media');
        log_activity("Brochure Deleted. ID [$id]");
        return true;
    }

    public function get_single($id)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('hash', $id);
        }
        $query = $this->db->get(db_prefix() . 'brochure');
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return null;
    }

    // public function get()
    // {
    //     $query = $this->db->get(db_prefix() . 'brochure');
    //     if ($query->num_rows() > 0) {
    //         return $query->result_array();
    //     }
    //     return null;
    // }
    public function get()
{
    $this->db->order_by('title', 'ASC'); // Order by title from A to Z
    $query = $this->db->get(db_prefix() . 'brochure');

    if ($query->num_rows() > 0) {
        return $query->result_array();
    }
    return null;
}

}
