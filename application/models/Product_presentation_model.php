<?php

class Product_presentation_model extends App_Model
{

    public function add($data)
    {
        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'product_presentation', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity("Product Presentation Created. ID [$insert_id]");
            return $insert_id;
        }
        return false;
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $check = $this->db->update(db_prefix() . 'product_presentation', $data);
        if ($check) {
            log_activity("Update Presentation. ID [$id]");
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        $this->db->trans_start();
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'product_presentation');
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            return false;
        }
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'product_presentation');
        $this->db->delete(db_prefix() . 'customer_media');
        log_activity("Product Presentation Deleted. ID [$id]");
        return true;
    }

    public function get_single($id)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('hash', $id);
        }
        $query = $this->db->get(db_prefix() . 'product_presentation');
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return null;
    }

public function get()
{
    $this->db->order_by('title', 'ASC'); // A to Z order
    $query = $this->db->get(db_prefix() . 'product_presentation');

    if ($query->num_rows() > 0) {
        return $query->result_array();
    }
    return null;
}

    // public function get()
    // {
    //     $query = $this->db->get(db_prefix() . 'product_presentation');
    //     if ($query->num_rows() > 0) {
    //         return $query->result_array();
    //     }
    //     return null;
    // }
}
