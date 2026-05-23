<?php
/* Author : Asif Thebepotra */
defined('BASEPATH') OR exit('No direct script access allowed');
class Indiamart extends AdminController {
	public $indiamart_setting;
	public function __construct()
	{
		parent::__construct();
		$this->load->model(['indiamart_model','leads_model']);
		$this->indiamart_setting = $this->indiamart_model->get_settings();
        if(!$this->indiamart_setting)
        {
        	set_alert('error',_l('missing', _l('indiamart_settings')));
			redirect(admin_url(INDIAMART_MODULE_NAME."/settings"));
		}
	}
	public function index(){
		redirect(admin_url(INDIAMART_MODULE_NAME."/fetch_leads"));
	}
	public function settings(){
        $data['title'] = _l('indiamart_settings');
        $data['indiamart'] = $this->indiamart_setting;
        if ($this->input->post())
        {
        	if($this->indiamart_model->save_settings())
        	{
        		set_alert('success',_l('updated_successfully', _l('indiamart_settings')));
        	}
        	redirect(admin_url(INDIAMART_MODULE_NAME."/settings"));
        }
        $this->load->view('indiamart_settings', $data);
	}
	public function fetch_leads(){
		$data['title'] = _l('indiamart_fetch_leads');
    	$data['fetch_url'] = admin_url(INDIAMART_MODULE_NAME."/get_leads");
    	$data['import_url'] = admin_url(INDIAMART_MODULE_NAME."/import_leads");
    	$data['statuses'] = $this->leads_model->get_status();
    	$data['sources']  = $this->leads_model->get_source();
    	$this->load->view('fetch_leads', $data);
	}
	public function leads_history(){
		$data['title'] = _l('indiamart_leads_history');
		$data['fetch_url'] = admin_url(INDIAMART_MODULE_NAME."/get_leads");
    	$data['import_url'] = admin_url(INDIAMART_MODULE_NAME."/import_leads");
    	$data['statuses'] = $this->leads_model->get_status();
    	$data['sources']  = $this->leads_model->get_source();
		$data['leads'] = $this->indiamart_model->getLeads();
		$this->load->view('leads_history', $data);
	}
	public function get_leads(){
		$post = $this->input->post(null,TRUE);
		$dateResponse = $this->checkDates($post['start_date'],$post['end_date']);
		if(!$dateResponse['status'])
		{
			echo json_encode($dateResponse);die;
		}
		$response = $this->indiamart_curl_request($dateResponse);
		if(!empty($response['result'])) {
			$response['head_title'] = "Latest Leads";
			if(isset($dateResponse['has_dates'])) {
				$response['head_title'] = "Leads Between {$dateResponse['start_date']} and {$dateResponse['end_date']}";
			}
			$result_arr = json_decode($response['result'],true);
			if(count($result_arr) <= 5)
			{
				for($i=0;$i<count($result_arr);$i++) {
					if(array_key_exists('Error_Message', $result_arr[$i])) {
						$response['result'] = $result_arr[$i]['Error_Message'];
						$response['status'] = false;
					}
				}
			}
			$table = ['recordsTotal'=>0,'recordsFiltered'=>0,'data'=>[]];
			$table_rows = '';
			if($response['status'])
			{
				for($i=0;$i<count($result_arr);$i++)
				{
					$query_id = $result_arr[$i]['QUERY_ID'];
					$lead_data = $result_arr[$i];
					$leadRes = $this->indiamart_model->add_lead($query_id,$lead_data);
					$id = $leadRes['id'];
					$lead_data['lead_id'] = $id;
					$leadRow = false;
					if($leadRes['exists']) {
						$leadRow = $leadRes['exists'];
					}
					$table_rows .= $this->getRowHTML($lead_data,$leadRow);
				}
			}
			$response['table_rows'] = $table_rows;
			if(!$response['status'])
            {
				$response['message'] = $response['result'];
			}
		}
		echo json_encode($response);
	}
	public function import_leads(){
		$post = $this->input->post(null,TRUE);
		$response = ['status'=>FALSE,'message'=>'Something Went Wrong,Please try again!','message_type'=>'danger'];
		$leads = $post['lead_ids'];
		$this->load->model('leads_model');
		$status = $post['status'];
		$source = $post['source'];
		if(is_array($leads) && !empty($leads))
		{
			$imported_leads = [];
			foreach ($leads as $l_k=>$lead_id) {
				$leadRow = $this->indiamart_model->get_lead($lead_id);
				if($leadRow)
				{
					$leadData = json_decode($leadRow->lead_data,true);
					$email = isset($leadData['SENDEREMAIL']) ? $leadData['SENDEREMAIL'] : '';
					$phone = $leadData['MOB'];
					if(strlen($phone)  == 14)
					{
						$phone = substr($phone,4);
					}
					$qproduct = str_replace('Requirement for ', '' ,$leadData['SUBJECT']);
					$prod = $this->leads_model->getleadproducts($qproduct);

					if($prod > 0 ){
						$assigned =  $this->leads_model->getstaffassignment_byproduct($prod);
					}
					else{
						$assigned = get_staff_user_id();
					}

					$insert_data = [
		                'name' => isset($leadData['SENDERNAME']) ? $leadData['SENDERNAME'] : '',
		                'source' => $source,
		                'status' => $status,
		                'email' => isset($leadData['SENDEREMAIL']) ? $leadData['SENDEREMAIL'] : '',
		                'phonenumber' => $phone,
		                'title' => '',
		                'tags' => $leadData['SUBJECT'],
		                'company' => isset($leadData['GLUSR_USR_COMPANYNAME']) ? $leadData['GLUSR_USR_COMPANYNAME'] : '',
		                'website' => '',
		                'address' => isset($leadData['ENQ_ADDRESS']) ? $leadData['ENQ_ADDRESS'] : '',
		                'city' => isset($leadData['ENQ_CITY']) ? $leadData['ENQ_CITY'] : '',
		                'zip' => '',
		                'state' => isset($leadData['ENQ_STATE']) ? $leadData['ENQ_STATE'] : '',
		                'description' => $leadData['ENQ_MESSAGE'],
		                'assigned' => $assigned
		                /*'custom_contact_date' => $this->Api_model->value($this->input->post('custom_contact_date', TRUE)),
		                'is_public' => $this->Api_model->value($this->input->post('is_public', TRUE)),*/
	                ];
					$output = $this->leads_model->add($insert_data);
					if($output)
					{
						$this->indiamart_model->update_lead($lead_id,['is_imported'=>1]);
						$imported_leads[] = $lead_id;
					}
				}
			} /* Leads Loop */
			$response['status'] = TRUE;
			$response['imported_leads'] = $imported_leads;
			$response['message'] = count($imported_leads)." Lead(s) Imported Successfully";
			$response['message_type'] = "success";
		}
		echo json_encode($response);
	}
	public function indiamart_curl_request($params=[]){
		$indiamart = $this->indiamart_setting;
		if($indiamart)
		{
			if($indiamart->indiamart_number != '' && $indiamart->indiamart_key != '')
			{
				$fetch_url = sprintf(INDIAMART_FETCH_URL,$indiamart->indiamart_number,$indiamart->indiamart_key);
				if(!empty($params))
				{
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
    private function checkDates($start_date,$end_date){
    	$response = ['status'=>TRUE,'message'=>''];
    	if($start_date != '' && $end_date != '')
    	{
    		if($end_date < $start_date)
    		{
    			$response['status'] = FALSE;
    			$response['message'] = "Invalid End Date";
    		}
    		$response['start_date'] = date('Y-m-d',strtotime($start_date));
    		$response['end_date'] = date('Y-m-d',strtotime($end_date));
    		$response['has_dates'] = TRUE;
    	}
    	else
    	{
    		if($start_date != '' || $end_date != '')
    		{
    			$response['status'] = FALSE;
    			$response['message'] = "Please Provide Both Dates";
    		}
    	}
    	return $response;
    }
    private function getRowHTML($data,$lead_row){
    	extract($data);
    	$imported_span = '';
    	$checkbox = "<input type='checkbox' name='lead_ids[]' class='import_id' value='{$lead_id}'>";
    	if($lead_row)
    	{
	    	if($lead_row->is_imported == 1)
	    	{
	    		$imported_span = "<span class='text-info'>Imported</span>";
	    		$checkbox = '';
	    	}
    	}
    	$row_html = '';
    	$row_html .= "<tr id='lead_{$lead_id}'>";
	    $row_html .= "<td>{$checkbox}</td>";
        $row_html .= "<td>{$SENDERNAME} {$imported_span}</td>";
        $row_html .= "<td>{$SENDEREMAIL}</td>";
        $row_html .= "<td>{$MOB}</td>";
        $row_html .= "<td>{$SUBJECT}</td>";
        $row_html .= "<td>{$ENQ_MESSAGE}</td>";
	    $row_html .= "</tr>";
	    return $row_html;
    }
}
/* End of file Indiamart.php */