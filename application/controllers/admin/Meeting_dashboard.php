<?php

class Meeting_dashboard extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('meeting_dashboard_model');
        $this->load->model('staff_model');
    }

    public function index()
    {
        if (!has_permission('meeting_dashboard', '', 'view')) {
            access_denied('Meeting Dashboard');
        }
        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            $this->app->get_table_data('meeting_dashboard', ["where" => $data]);
        }
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('admin/meeting_dashboard/dashboard', $data);
    }

    public function get_calendar_data()
    {
        $data = $this->input->post();
        if (isset($data['month']) && isset($data['year'])) {
            $resultData =  $this->meeting_dashboard_model->get_meeting_data($data);
            if (!empty($resultData)) {
                foreach ($resultData as $key => $item) {
                    $resultData[$key]['date'] = date('Y-m-d', strtotime($item['date_time']));
                    if ($item['table_type'] == "plant_visit_form") {
                        $resultData[$key]['title'] = leadFormIdRender("#PVF", $item['lead_id'], $item['id']);
                    } else {
                        $resultData[$key]['title'] = "#LEAD-" . $item['lead_id'];
                    }
                    $resultData[$key]['date_time'] = _d($item['date_time']);
                    $resultData[$key]['tooltip'] = _d($item['date_time']) . " - " . $item['status'];
                }
            }
            $result['success'] = true;
            $result['data'] = $resultData;
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request";
        }
        echo json_encode($result);
    }
}
