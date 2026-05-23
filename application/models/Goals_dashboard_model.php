<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Goals_dashboard_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_goals_list()
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'goals');
        $this->db->where('active', '1');
        $this->db->order_by('id', 'ASC');
        return $this->db->get()->result_array();
    }
}
