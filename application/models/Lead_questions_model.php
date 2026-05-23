<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Lead_questions_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'lead_questions')->row();
    }

    public function add_question($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'lead_questions', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Leads Question Added [QuestionID: ' . $insert_id . ', Question: ' . $data['question'] . ']');
        }

        return $insert_id;
    }
    public function update_question($data, $id, $isDelete = false)
    {
        $this->db->where('id', $id);
        if ($isDelete) {
            $data['datedeleted'] = date('Y-m-d H:i:s');
            $data['deleted_by'] = get_staff_full_name();
        } else {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['updated_by'] = get_staff_user_id();
        }
        $this->db->update(db_prefix() . 'lead_questions', $data);
        if ($this->db->affected_rows() > 0) {
            if ($isDelete) {
                log_activity('Lead Question Deleted [QuestionID: ' . $id . ']');
            } else {
                log_activity('Leads Question Updated [QuestionID: ' . $id . ', Question: ' . $data['question'] . ']');
            }
            return true;
        }
        return false;
    }

    public function change_custom_field_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'lead_questions', [
            'is_active' => $status,
        ]);
        log_activity('Leads Question Field Status Changed [FieldID: ' . $id . ' - Active: ' . $status . ']');
    }

    public function get_main_group()
    {
        $this->db->where('deleted_at IS NULL');
        return $this->db->get(db_prefix() . 'items_groups')->result_array();
    }

    public function get_sub_group()
    {
        $this->db->where('deleted_at IS NULL');
        return $this->db->get(db_prefix() . 'sub_groups')->result_array();
    }

    public function get_main_group_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'items_groups')->row_array();
    }

    public function get_sub_group_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'sub_groups')->row_array();
    }

    public function get_last_question($main_group_id,$sub_group_id)
    {
        $this->db->where('main_group_id', $main_group_id);
        $this->db->where('sub_group_id', $sub_group_id);
        $this->db->order_by('order_no', 'DESC');
        $this->db->limit(1);
        return $this->db->get(db_prefix() . 'lead_questions')->row_array();
    }

    function get_questions_by_group($main_group_id, $sub_group_id)
    {
        $this->db->where('main_group_id', $main_group_id);
        $this->db->where('sub_group_id', $sub_group_id);
        $this->db->where('datedeleted IS NULL');
        return $this->db->get(db_prefix() . 'lead_questions')->result_array();
    }

    function get_questions_by_id($idArr)
    {
        $this->db->where_in('id', $idArr);
        $this->db->where('datedeleted IS NULL');
        return $this->db->get(db_prefix() . 'lead_questions')->result_array();
    }

    function check_question($question, $main_group_id, $sub_group_id, $id = "")
    {
        $this->db->where('main_group_id', $main_group_id);
        $this->db->where('sub_group_id', $sub_group_id);
        if(!empty($id)){
            $this->db->where('id !=', $id);
        }
        $this->db->where('question', $question);
        $this->db->where('datedeleted IS NULL');
        $query = $this->db->get(db_prefix() . 'lead_questions');
        return $query->num_rows();
    }

    public function get_questions_suggestions($text)
    {
        $this->db->select('question');
        $this->db->from(db_prefix() . 'lead_questions');
        $this->db->like('question', $text, 'both');
        return $this->db->get()->result_array();
    }
}
