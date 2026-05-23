<?php
header('Content-Type: text/html; charset=utf-8');
defined('BASEPATH') or exit('No direct script access allowed');

class Leads_map extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('leadsnew_model');
    }

    public function index($id = '')
    {
        if (!has_permission('leads_map', '', 'view')) {
            access_denied('leads map');
        }
        $data['country_data'] = $this->leadsnew_model->get_countries();
        $this->load->view('admin/leads_map/leads_map', $data);
    }

    public function get_state_city()
    {
        $result['success'] = false;
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['type'] == "state") {
                $result['success'] = true;
                $result['data'] = $this->leadsnew_model->get_states(["country" => $data['country']]);
            } else if ($data['type'] == "city") {
                $result['success'] = true;
                $result['data'] = $this->leadsnew_model->get_cities(["country" => $data['country'], "state" => $data['state']]);
            }
        }
        echo json_encode($result);
    }

    public function get_map_lead_data()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $checkData = [];
            if (empty($data['country']) && empty($data['state']) && empty($data['city'])) { // leads count all country wise
                $checkData = $this->leadsnew_model->get_lead_count_for_map(["type" => "all_country", "product" => $data['product']]);
            } else if (!empty($data['country']) && empty($data['state']) && empty($data['city'])) { // leads count all state wise
                $checkData = $this->leadsnew_model->get_lead_count_for_map(["type" => "all_state", "country" => $data['country'], "product" => $data['product']]);
            } else if (!empty($data['country']) && !empty($data['state']) && empty($data['city'])) { // leads count all city wise
                $checkData = $this->leadsnew_model->get_lead_count_for_map(["type" => "all_city", "country" => $data['country'], "state" => $data['state'], "product" => $data['product']]);
            } else if (!empty($data['country']) && !empty($data['state']) && !empty($data['city'])) { // leads count single city wise
                $checkData = $this->leadsnew_model->get_lead_count_for_map(["type" => "single_city", "country" => $data['country'], "state" => $data['state'], "city" => $data['city'], "product" => $data['product']]);
            }
            if (!empty($checkData)) {
                $result['success'] = true;
                $result['lead_data'] = $checkData;
            } else {
                $result['success'] = false;
                $result['message'] = "No data Available";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid Request";
        }
        echo json_encode($result);
    }
}
