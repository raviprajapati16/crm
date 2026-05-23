<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Lead_inquiry_form_images_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_image_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'lead_inquiry_forms_images')->row_array();
    }

    public function add_image($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'lead_inquiry_forms_images', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('Lead Inquiry form Image Added [Record ID: ' . $insert_id . ']');
        }
        return $insert_id;
    }

    public function update_image($data, $id, $log_add = true)
    {
        $this->db->where('id', $id);
        if ($log_add) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['updated_by'] = get_staff_user_id();
        }
        $this->db->update(db_prefix() . 'lead_inquiry_forms_images', $data);
        if ($this->db->affected_rows() > 0) {
            if ($log_add) {
                log_activity('Lead Inquiry form Image Updated [Record ID: ' . $id . ']');
            }
            return true;
        }
        return false;
    }

    public function delete_image($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'lead_inquiry_forms_images');
        if ($this->db->affected_rows() > 0) {
            log_activity('Lead Inquiry form Image Deleted [Record ID: ' . $id . ']');

            return true;
        }
        return false;
    }

    public function deactive_pop_images()
    {
        $data['is_active'] = '0';
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = get_staff_user_id();
        $this->db->where('type', 'popup-image');
        $this->db->update(db_prefix() . 'lead_inquiry_forms_images', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Lead Inquiry form Popup All Images deactivated.');
            return true;
        }
        return false;
    }

    public function get_active_popup_image()
    {
        $this->db->where('is_active', '1');
        $this->db->where('type', 'popup-image');
        $this->db->limit(1);
        return $this->db->get(db_prefix() . 'lead_inquiry_forms_images')->row_array();
    }

    public function get_background_slider_images()
    {
        $this->db->where('is_active', '1');
        $this->db->where('type', 'background-image-slider');
        return $this->db->get(db_prefix() . 'lead_inquiry_forms_images')->result_array();
    }
}
