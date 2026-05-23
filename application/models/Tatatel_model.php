<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tatatel_model extends App_Model {
	public $table;
	public function __construct()
    {
        parent::__construct();
        $this->load->model('staff_model');
    }

	public function get_settings()
	{
		$tatatel_email = get_option('tatatel_email');
		$tatatel_password = get_option('tatatel_password');
		$tatatel_url = get_option('tatatel_url');
		$tatatel_access_token = get_option('tatatel_access_token');

		$data = array(
			'tatatel_email' => $tatatel_email,
			'tatatel_password' => $tatatel_password,
			'tatatel_url' => $tatatel_url,
			'tatatel_access_token' => $tatatel_access_token

		);

		return $data;
	}

    public function tatatel_curl_request($params = [])
    {
        $tatatel = $this->get_settings();
        $curl = curl_init();
        $fetch_url = $tatatel['tatatel_url'].'click_to_call';
        $token = $tatatel['tatatel_access_token'];
        $staff = $this->staff_model->get(get_staff_user_id());

            curl_setopt_array($curl, [
            CURLOPT_URL =>  $fetch_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'agent_number' => $staff->tata_tele_agentid,
                'destination_number' => $params,
                'caller_id' => $staff->tata_tele_phone_number,
            ]),
            CURLOPT_HTTPHEADER => [
                "Authorization: $token",
                "accept: application/json",
                "content-type: application/json"
            ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
            echo "cURL Error #:" . $err;
            } else {
            echo $response;
            }
    }

    public function tatatel_get_request($params = [])
    {
        $tatatel = $this->get_settings();
        $curl = curl_init();
        $staff = $this->staff_model->get(get_staff_user_id());
        $staff_linkedid = $staff->linkedin;
        $fetch_url = $tatatel['tatatel_url'] . 'call-details?linkedid=' . $staff_linkedid;
        $token = $tatatel['tatatel_access_token'];

        curl_setopt_array($curl, [
            CURLOPT_URL =>  $fetch_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Authorization:  $token",
                "accept: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
        echo "cURL Error #:" . $err;
        } else {
        echo $response;
        }
    }

    public function tatatel_delete_request($params = [])
    {
        $tatatel = $this->get_settings();
        $curl = curl_init();
        $staff = $this->staff_model->get(get_staff_user_id());
        $staff_staffid = $staff->staffid;
        $fetch_url = $tatatel['tatatel_url'] . "/call/note/" . $staff_staffid;
        $token = $tatatel['tatatel_access_token'];

        curl_setopt_array($curl, [
            CURLOPT_URL =>  $fetch_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_HTTPHEADER => [
              "Authorization: $token",
              "accept: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
        echo "cURL Error #:" . $err;
        } else {
        echo $response;
        }
    }

    public function tatatel_put_request($params = [])
    {
        $tatatel = $this->get_settings();
        $curl = curl_init();
        $staff = $this->staff_model->get(get_staff_user_id());
        $staff_staffid = $staff->staffid;
        $fetch_url = $tatatel['tatatel_url'] . "/call/note/" . $staff_staffid;
        $token = $tatatel['tatatel_access_token'];

        curl_setopt_array($curl, [
            CURLOPT_URL =>  $fetch_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => json_encode([
                'message' => 'test'
              ]),
            CURLOPT_HTTPHEADER => [
                "Authorization: $token",
                "accept: application/json",
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
        echo "cURL Error #:" . $err;
        } else {
        echo $response;
        }
    }

    public function insert_calldata($data = array()){
       return  $this->db->insert('tbltatatele_call_details', $data);
    }

}
/* End of file Tatatel_model.php */