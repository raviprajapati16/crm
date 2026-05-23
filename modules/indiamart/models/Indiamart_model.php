<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Indiamart_model extends App_Model {
	public $table;
	public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix()."indiamart_settings";
    }
	public function save_settings()
	{
		$data = array();
		$data['indiamart_key'] 		= $this->input->post('indiamart_key',true);
		$data['indiamart_number'] 	= $this->input->post('indiamart_number',true);

		$where = ['id'=>1];

		$this->db->where($where);
		return $this->db->update($this->table,$data,$where);
	}

	public function get_settings()
	{
		$where = ['id'=>1];
		return $this->db->get_where($this->table,$where)->row();
	}

	public function add_lead($query_id,$lead_data)
	{
		$table = db_prefix()."indiamart_leads";
		$where = ['QUERY_ID'=>$query_id];
		$isLeadExists = $this->db->get_where($table,$where)->row();
		if($isLeadExists)
		{
			return ['exists'=>$isLeadExists,'id'=>$isLeadExists->id];
		}
		else
		{
			$data = ['lead_data'=>json_encode($lead_data),'QUERY_ID'=>$query_id];
			$this->db->insert($table,$data);
			return ['exists'=>false,'id'=>$this->db->insert_id()];
		}
	}

	public function get_lead($id=0,$where=[])
	{
		$table = db_prefix()."indiamart_leads";
		$q_where = ['id'=>$id];
		if(!empty($where))
		{
			$q_where = array_merge($q_where,$where);
		}
		return $this->db->get_where($table,$q_where)->row();
	}
	public function update_lead($id=0,$data=[])
	{
		$table = db_prefix()."indiamart_leads";
		if($id > 0)
		{
			$where = ['id'=>$id];
			return $this->db->update($table,$data,$where);
		}
		return true;
	}

	public function getLeads()
	{
		$table = db_prefix()."indiamart_leads";
		return $this->db->get($table)->result();  //-- Commented to reduce results in Indiamart results 
		//$where = ['is_imported'=>0];
		//return $this->db->get_where($table,$where)->result();
	}

}

/* End of file Indiamart_model.php */