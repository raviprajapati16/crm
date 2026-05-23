<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TradeIndia_model extends App_Model
{
	public $table;
	public function __construct()
	{
		parent::__construct();
	}

	public function get_settings()
	{
		$tradeindia_api_link = get_option('tradeindia_api_link');               //log_activity($tradeindia_api_link);
		$tradeindia_userid = get_option('tradeindia_userid');               //log_activity($tradeindia_userid);
		$tradeindia_profile_id = get_option('tradeindia_profile_id');               //log_activity($tradeindia_profile_id);
		$tradeindia_key = get_option('tradeindia_key');
		$tradeindia_imported_on = get_option('tradeindia_imported_on');

		// $data['tradeindia_api_link'] = $tradeindia_api_link;
		// $data['tradeindia_userid'] = $tradeindia_userid;
		// $data['tradeindia_profile_id'] = $tradeindia_profile_id;
		// $data['tradeindia_key'] = $tradeindia_key;
		// $data['tradeindia_imported_on'] = $tradeindia_imported_on;

		$data = array(
			'tradeindia_api_link' => $tradeindia_api_link,
			'tradeindia_userid' => $tradeindia_userid,
			'tradeindia_profile_id' => $tradeindia_profile_id,
			'tradeindia_key' => $tradeindia_key,
			'tradeindia_imported_on' => $tradeindia_imported_on
		);

		return $data;
	}

	public function update_setting()
	{
		$fields = [
			'tradeindia_api_link',
			'tradeindia_userid',
			'tradeindia_profile_id',
			'tradeindia_key',
			'tradeindia_imported_on'
		];
		$updated = false;
		foreach ($fields as $field) {
			if ($this->input->post($field,true) !== null) {
				$value = $this->input->post($field);

				$this->db->where('name', $field);
				if ($this->db->update(db_prefix() . 'options', ['value' => $value])) {
					$updated = true;
				}
			}
		}
		return $updated;
	}

	public function add_lead($query_id, $lead_data)
	{
		$table = db_prefix() . "tradeindia_leads";
		$where = ['QUERY_ID' => $query_id];
		$isLeadExists = $this->db->get_where($table, $where)->row();
		if ($isLeadExists) {
			return ['exists' => $isLeadExists, 'id' => $isLeadExists->id];
		} else {
			$data = ['lead_data' => json_encode($lead_data), 'QUERY_ID' => $query_id];
			$this->db->insert($table, $data);
			return ['exists' => false, 'id' => $this->db->insert_id()];
		}
	}

	public function get_lead($id = 0, $where = [])
	{
		$table = db_prefix() . "tradeindia_leads";
		$q_where = ['id' => $id];
		if (!empty($where)) {
			$q_where = array_merge($q_where, $where);
		}
		return $this->db->get_where($table, $q_where)->row();
	}

	public function update_lead($id = 0, $data = [])
	{
		$table = db_prefix() . "tradeindia_leads";
		if ($id > 0) {
			$where = ['id' => $id];
			return $this->db->update($table, $data, $where);
		}
		return true;
	}
	//Get Unassigned leads to auto assign
	public function getLeads()
	{
		$table = db_prefix() . "tradeindia_leads";
		$where = ['is_imported' => 0];
		return $this->db->get_where($table, $where)->result();
	}

	public function tradeindia_curl_request($params = [])
	{
		$tradeindia = $this->get_settings();
		if ($tradeindia) {
			if ($tradeindia['tradeindia_api_link'] != '' && $tradeindia['tradeindia_userid'] != ''  && $tradeindia['tradeindia_profile_id'] != ''   && $tradeindia['tradeindia_key'] != '') {
				$fetch_url = $tradeindia['tradeindia_api_link'] . '?userid=' .  $tradeindia['tradeindia_userid'] . '&profile_id=' . $tradeindia['tradeindia_profile_id'] . '&key=' . $tradeindia['tradeindia_key'];
				if (!empty($params)) {
					if ($params['start_date'] && $params['end_date']) {
						$stdt = strtotime($params['start_date']);
						$strtdate = date("Y-m-d", $stdt);
						$endt = strtotime($params['end_date']);
						$enddate =  date("Y-m-d", $endt);
						$fetch_url .= '&from_date=' . $strtdate . '&to_date=' . $enddate;
					}
				} else {
					$date = date("Y-m-d");
					$fetch_url .= '&from_date=' . $date . '&to_date=' . $date;
				}
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $fetch_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				$headers = array();
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
				$api_result = (curl_exec($ch));
				$response = ['result' => ($api_result)];
				if (curl_errno($ch)) {
					$response['status'] = false;
					$response['result'] = 'Error:' . curl_error($ch);
				}
				curl_close($ch);
			} else {
				$response = ['status' => false, 'result' => 'Please Configure TradeIndia Settings and try again!'];
			}
			return $response;
		}
	}
}

/* End of file TradeIndia_model.php */