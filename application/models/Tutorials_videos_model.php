<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Tutorials_videos_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_single($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get(db_prefix() . 'tutorial_videos');
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }

    public function add($data)
    {
        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'tutorial_videos', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity("New Tutorial Video Created. ID [$insert_id]");
            return $insert_id;
        }
        return false;
    }

    public function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'tutorial_videos', $data);
        $affectedRows = $this->db->affected_rows();
        if ($affectedRows > 0) {
            log_activity("Tutorial Video Updated. ID [$id]");
            return true;
        }
        return false;
    }

    public function delete($id)
    {
        $this->db->trans_start();
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'tutorial_videos');
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            return false;
        }
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'tutorial');
        $this->db->delete(db_prefix() . 'customer_media');
        log_activity("Tutorial Video Deleted. ID [$id]");
        return true;
    }

    public function get_tutorial_videos()
    {
        $query = $this->db->get(db_prefix() . 'tutorial_videos');
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return [];
    }

    public function get_single_link($id)
    {
        $this->db->where('id', $id);
        $query = $this->db->get(db_prefix() . 'tutorial_links');
        if ($query->num_rows() > 0) {
            return $query->row();
        }
        return false;
    }

    public function update_link($id, $data)
    {
        $data['updated_by'] = get_staff_user_id();
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'tutorial_links', $data);
        $affectedRows = $this->db->affected_rows();
        if ($affectedRows > 0) {
            log_activity("Tutorial Link Updated. ID [$id]");
            return true;
        }
        return false;
    }

    public function get()
    {
        $query = $this->db->get(db_prefix() . 'tutorial_videos');
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        return null;
    }
}
