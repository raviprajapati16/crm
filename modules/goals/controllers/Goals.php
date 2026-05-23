<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Goals extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('goals_model');
        $this->load->model('staff_model');
    }

    public function index()
    {
        if (!has_permission('goals', '', 'view') && !has_permission('goals', '', 'view_own')) {
            access_denied('goals');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('goals', 'table'));
        }
        $this->app_scripts->add('circle-progress-js', 'assets/plugins/jquery-circle-progress/circle-progress.min.js');
        $data['title']                 = _l('goals_tracking');
        $this->load->view('manage', $data);
    }

    public function goal($id = '')
    {
        if (!has_permission('goals', '', 'view') && !has_permission('goals', '', 'view_own')) {
            access_denied('goals');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $staff_ids = $data['staff_id'];
            unset($data['staff_id']);
            if ($id == '') {
                if (!has_permission('goals', '', 'create')) {
                    access_denied('goals');
                }
                $id = $this->goals_model->add($data);
                if ($id) {
                    update_goal_staff($staff_ids, $id);
                    set_alert('success', _l('added_successfully', _l('goal')));
                    redirect(admin_url('goals/goal/' . $id));
                }
            } else {
                if (!has_permission('goals', '', 'edit')) {
                    access_denied('goals');
                }
                $success = $this->goals_model->update($data, $id);
                update_goal_staff($staff_ids, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('goal')));
                }
                redirect(admin_url('goals/goal/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('goal_lowercase'));
        } else {
            $data['goal']        = $this->goals_model->get($id);
            $data['achievement'] = $this->goals_model->calculate_goal_achievement_new($id);

            $title = _l('edit', _l('goal_lowercase'));
        }

        $this->load->model('staff_model');
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        $this->load->model('contracts_model');
        $data['contract_types']        = $this->contracts_model->get_contract_types();
        $data['title']                 = $title;
        $this->app_scripts->add('circle-progress-js', 'assets/plugins/jquery-circle-progress/circle-progress.min.js');
        $this->load->view('goal', $data);
    }

    public function staff_table($goal_id)
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('goals', 'staff-table'), ["goal_id" => $goal_id]);
        }
    }

    public function change_status($id)
    {
        if (!has_permission('goals', '', 'edit')) {
            access_denied('goals');
        }
        $data = $this->input->post();
        $update['active'] = ($data['status'] == "0") ? "0" : "1";
        $success = $this->goals_model->update_direct($update, $id);
        $result['success'] = true;
        $result['message'] = ($data['status'] == "1") ? "Active Successfully." : "Deactive Successfully.";
        echo json_encode($result);
    }

    public function change_goal_staff_status($id)
    {
        if (!has_permission('goals', '', 'edit')) {
            access_denied('goals');
        }
        $data = $this->input->post();
        $update['active'] = ($data['status']) ? "true" : "false";
        $success = $this->goals_model->update_goal_staff($update, $id);
        $result['success'] = true;
        $result['message'] = ($data['status'] == "1") ? "Active Successfully." : "Deactive Successfully.";
        echo json_encode($result);
    }

    public function staff_goal_modal()
    {
        $data = $this->input->post();
        if (isset($data['staff_id']) && isset($data['goal_id'])) {
            $result['success'] = true;
            $data['goal'] = $this->goals_model->get($data['goal_id']);
            $data['staff'] = $this->staff_model->get($data['staff_id']);
            $html = $this->load->view('staff_goal_list_render', $data, true);
            $result['html'] = $html;
        } else {
            $result['success'] = false;
            $result['message'] = "Error : Invalid request.";
        }
        echo json_encode($result);
    }

    /* Delete announcement from database */
    public function delete($id)
    {
        if (!has_permission('goals', '', 'delete')) {
            access_denied('goals');
        }
        if (!$id) {
            redirect(admin_url('goals'));
        }
        $response = $this->goals_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('goal')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('goal_lowercase')));
        }
        redirect(admin_url('goals'));
    }

    public function notify()
    {
        if (!has_permission('goals', '', 'edit') && !has_permission('goals', '', 'create')) {
            access_denied('goals');
        }
        $data = $this->input->post();
        if (isset($data['staff_id']) && isset($data['goal_id']) && isset($data['status']) && isset($data['start_date']) && isset($data['end_date']) && isset($data['goal_duration_title'])) {
            $goal = $this->goals_model->get($data['goal_id']);
            $data['achievement'] =  $this->goals_model->calculate_goal_achievement_new($data['goal_id'], $data['staff_id'], $data['start_date'], $data['end_date']);
            $data['goal_duration_title'] = get_goal_duration_title_by_key($goal->goal_duration_type) . " - " . $data['goal_duration_title'];
            $check = $this->goals_model->notify_staff_members($data);
            if ($check) {
                log_activity("Manually notification sent to staff for goal ID [" . $data['goal_id'] . "] Staff ID [" . $data['staff_id'] . "] Goal Duration [" . $data['goal_duration_title'] . "]");
                $result['success'] = true;
                $result['message'] = "Notification successfully sent.";
            } else {
                $result['success'] = false;
                $result['message'] = "Error : Notification not send.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Error : Invalid request.";
        }
        echo json_encode($result);
    }

    public function staff_details_table($goal_id, $staff_id)
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('goals', 'staff-details-table'), ["goal_id" => $goal_id, "staff_id" => $staff_id]);
        }
    }
}
