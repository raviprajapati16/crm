<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_management_model extends App_Model
{
    public $table;
	public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . "products";
    }

    public function get_products()
    {
        //$table = db_prefix() . "products";
        // $q_where = ['id'=>$id];
        // if(!empty($where))
        // {
        // 	$q_where = array_merge($q_where,$where);
        // }
        //return $this->db->get_where($table,$q_where)->result();
        $this->db->order_by("order");
        return $this->db->get($this->table)->result();
    }
    public function get_product($id)
    {
        //$table = db_prefix() . "products";
        $q_where = ['productid'=>$id];
        if(!empty($where))
        {
        	$q_where = array_merge($q_where,$where);
        }
        return $this->db->get_where($this->table,$q_where)->result();
        //return $this->db->get($table)->result();
    }
    public function save_product()
    {
        $data = array();
        $data['order']         = $this->input->post('order', true);
        $data['name']     = $this->input->post('name', true);
        $data['searchterms']     = $this->input->post('searchterms', true);
        $id = $this->input->post('productid',true);
        if($id == "")
        {
            return $this->db->insert($this->table, $data);
        }   
        else
        {
            $where = ['productid' => $id];
            $this->db->where($where);
            return $this->db->update($this->table, $data, $where);
        }     
    }
}
