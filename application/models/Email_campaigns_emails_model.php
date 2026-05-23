<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Email_campaigns_emails_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'emailcampaign_emails')->row();
    }

    public function get_all()
    {
        return $this->db->get(db_prefix() . 'emailcampaign_emails')->result_array();
    }

    public function add($data)
    {
        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'emailcampaign_emails', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity("Email Campaign : New Custom Email Created. ID [$insert_id]");
            return $insert_id;
        }
        return false;
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $check = $this->db->update(db_prefix() . 'emailcampaign_emails', $data);
        if ($check) {
            log_activity("Email Campaign : Custom Email Updated. ID [$id]");
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        $this->db->where('id', $id);
        $check = $this->db->delete(db_prefix() . 'emailcampaign_emails');
        if ($check) {
            log_activity("Email Campaign : Custom Email Deleted. ID [$id]");
            return true;
        }
        return false;
    }
}
