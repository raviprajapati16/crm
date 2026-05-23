<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Crm_utility_model extends App_Model {
	public function __construct()
    {
        parent::__construct();
    }
	public function getTags($rel_with='',$rel_id=null){
		$tags = db_prefix()."tags";
		$taggables = db_prefix()."taggables";
		$this->db->select("DISTINCT({$tags}.name),{$tags}.id");
		$this->db->from($tags);
		$this->db->join($taggables, "{$taggables}.tag_id={$tags}.id");
		if($rel_with != ''){
			$this->db->where('rel_type', $rel_with);
		}
		if($rel_id != ''){
			$this->db->where('rel_id', $rel_with);
		}
		return $this->db->get()->result_array();
	}

}

/* End of file Indiamart_model.php */