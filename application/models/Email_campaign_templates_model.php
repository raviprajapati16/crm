<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Email_campaign_templates_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_all_templates()
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'emailcampaign_templates');
        return $this->db->get()->result();
    }

    public function get_all_templates_except_demo()
    {
        $staffIds = get_manager_assigned_staff_ids();
        $permission = has_permission('email_campaigns', '', 'view');
        $this->db->select('*');
        $this->db->where('id !=', 1);
        // if (!$permission) {
        //     $this->db->where_in('created_by', $staffIds);
        // }
        $this->db->order_by('id', 'desc');
        $this->db->from(db_prefix() . 'emailcampaign_templates');
        return $this->db->get()->result();
    }

    public function get_template($id)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'emailcampaign_templates');
        $this->db->where('id', $id);
        return $this->db->get()->row();
    }

    public function save_template($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'emailcampaign_templates', $data);
        return $this->db->insert_id();
    }

    public function delete_template($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'emailcampaign_templates');
        return $this->db->affected_rows() > 0;
    }

    public function update_template($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = get_staff_user_id();
        $this->db->where('id', $id);
        return $this->db->update(db_prefix() . 'emailcampaign_templates', $data);
    }

    public function update_template_timestamp($id)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = get_staff_user_id();
        $this->db->where('id', $id);
        return $this->db->update(db_prefix() . 'emailcampaign_templates', $data);
    }
}
