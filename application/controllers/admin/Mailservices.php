<?php
class Mailservices extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('mailservices_model');
    }

    public function index($id = '')
    {
        if (!is_admin()) {
            access_denied('mail_services');
        }
        $data['settings'] = $this->mailservices_model->get();
        return $this->load->view('admin/mail_services/index', $data);
    }

    public function service($id = '')
    {
        if (!is_admin()) {
            access_denied('mail_services');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['only_send'] = isset($data['only_send']) ? '1' : '0';
            if ($id == '') {
                $id = $this->mailservices_model->add($data);
                if ($id) {
                    set_alert('success', "Mail service successfully created.");
                } else {
                    set_alert('warning', "Error : Mail service not created.");
                }
                redirect(admin_url('mailservices'));
            } else {
                $success = $this->mailservices_model->update($data, $id);
                set_alert('success', "Mail service successfully updated.");
                redirect(admin_url('mailservices'));
            }
        }
        if ($id == '') {
            $data['title'] = "Add New Mail Service";
        } else {
            $data['service']  = $this->mailservices_model->get($id);
            $data['title'] = "Edit Mail Service";
        }
        $this->load->view('admin/mail_services/form', $data);
    }

    public function delete($id)
    {
        if (!is_admin()) {
            access_denied('mail_services');
        }
        if (!$id) {
            redirect(admin_url('mailservices'));
        }
        if (total_rows(db_prefix() . "staff", ["mail_service" => $id]) > 0) {
            set_alert('danger', _l('Sorry ! this service is already used by some staff members.'));
            redirect(admin_url('mailservices'));
        }
        $response = $this->mailservices_model->delete($id);
        if ($response) {
            set_alert('success', "Mail service successfully deleted.");
        } else {
            set_alert('warning', "Error : Mail service not deleted.");
        }
        redirect(admin_url('mailservices'));
    }
}
