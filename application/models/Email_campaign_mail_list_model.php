<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Email_campaign_mail_list_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_lists()
    {
        $permission = has_permission('email_campaigns', '', 'view');
        $isManager = is_manager();
        $staffIds = get_manager_assigned_staff_ids();
        $loggedinStaff = get_staff_user_id();
        $this->db->select('*');
        if (!$permission) {
            if ($isManager) {
                $this->db->where_in('created_by', $staffIds);
            } else {
                $this->db->where('created_by', $loggedinStaff);
            }
        }
        $this->db->from(db_prefix() . 'emailcampaign_mail_list');

        return $this->db->get()->result_array();
    }

    public function get_list_items($list_ids = [])
    {
        $permission = has_permission('email_campaigns', '', 'view');
        $isManager = is_manager();
        $staffIds = get_manager_assigned_staff_ids();
        $loggedinStaff = get_staff_user_id();

        $this->db->select('items.*');
        $this->db->from(db_prefix() . 'emailcampaign_mail_list_items as items');
        $this->db->join(db_prefix() . 'emailcampaign_mail_list as lists', 'lists.id = items.list_id');

        if (!empty($list_ids)) {
            $this->db->where_in('items.list_id', $list_ids);
        }

        if (!$permission) {
            if ($isManager) {
                $this->db->where_in('lists.created_by', $staffIds);
            } else {
                $this->db->where('lists.created_by', $loggedinStaff);
            }
        }

        return $this->db->get()->result_array();
    }


    public function insert_list($data)
    {
        $this->db->insert(db_prefix() . 'emailcampaign_mail_list', $data);
        return $this->db->insert_id();
    }

    public function update_list($id, $data)
    {
        $table = db_prefix() . 'emailcampaign_mail_list';
        $this->db->where('id', $id);
        $this->db->update($table, $data);
        return $this->db->affected_rows() > 0;
    }

    public function insert_email_item($data)
    {
        $this->db->insert(db_prefix() . 'emailcampaign_mail_list_items', $data);
        return $this->db->insert_id();
    }

    public function update_email_item($id, $data)
    {
        $table = db_prefix() . 'emailcampaign_mail_list_items';
        $this->db->where('id', $id);
        $this->db->update($table, $data);
        return $this->db->affected_rows() > 0;
    }

    public function is_duplicate_email($email, $list_id, $record_id = null)
    {
        $this->db->select('id');
        $this->db->from(db_prefix() . 'emailcampaign_mail_list_items');
        $this->db->where('email', $email);
        $this->db->where('list_id', $list_id);
        if ($list_id !== null) {
            $this->db->where('id !=', $record_id);
        }
        $query = $this->db->get();
        return $query->num_rows() > 0;
    }


    public function insert_emails_batch($emails)
    {
        return $this->db->insert_batch(db_prefix() . 'emailcampaign_mail_list_items', $emails);
    }

    public function delete_mail_list($id)
    {
        $affectedRows = 0;
        $this->db->where('list_id', $id);
        $this->db->delete(db_prefix() . 'emailcampaign_mail_list_items');


        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'emailcampaign_mail_list');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            log_activity('Email Campaign Mail List Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    public function delete_mail_list_item($id)
    {
        $affectedRows = 0;
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'emailcampaign_mail_list_items');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            log_activity('Email Campaign Mail List Item Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }
}
