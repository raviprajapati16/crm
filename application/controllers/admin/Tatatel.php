<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Tatatel extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Tatatel_model');
    }

    public function CallPhoneNumber()
    {
        $phoneNumber = $this->input->get('phoneNumber');

        if (!$phoneNumber) {
            show_error('Phone number not provided', 400);
            return;
        }

        $phone_number = $this->Tatatel_model->tatatel_curl_request($phoneNumber);
        $response = ['status' => 200, 'message' => 'You called on ' . $phone_number];
        header('Content-Type: application/json');
        echo json_encode($response);
        return true;
    }
}
