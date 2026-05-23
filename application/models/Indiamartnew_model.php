<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Indiamartnew_model extends App_Model {
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
	//Get Unassigned leads to auto assign
	public function getLeads()
	{
		$table = db_prefix()."indiamart_leads";
		$where = ['is_imported'=>0];
		return $this->db->get_where($table,$where)->result();
	}

	public function indiamart_curl_request($params=[]){
		$indiamart = $this->get_settings();

		if($indiamart)
		{
			if($indiamart->indiamart_number != '' && $indiamart->indiamart_key != '')
			{
				$fetch_url = sprintf(INDIAMART_FETCH_URL,$indiamart->indiamart_key);
				if(!empty($params))
				{
					if($params['start_date'] && $params['end_date'])
					{
						$stdt = strtotime($params['start_date']);
						$strtdate = date("d-M-Y", $stdt);
						$endt = strtotime($params['end_date']);
						$enddate =  date("d-M-Y", $endt);
						$fetch_url .= '&start_time=' . $strtdate . '&end_time=' . $enddate;
					}
				}
				else
				{
					$date = date("d-M-Y");
					$fetch_url .= '&start_time=' . $date . '&end_time=' . $date;
				}
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $fetch_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				$headers = array();
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				$api_result = (curl_exec($ch));
				$response = ['status'=>true,'result'=>($api_result)];
				if (curl_errno($ch)) {
					$response['status'] = false;
					$response['result'] = 'Error:' . curl_error($ch);
				}
				curl_close($ch);
			}
			else
			{
				$response = ['status'=>false,'result'=>'Please Configure '._l('indiamart_settings').' and try again!'];
			}
			return $response;
		}
    }

}

/* End of file Indiamart_model.php */