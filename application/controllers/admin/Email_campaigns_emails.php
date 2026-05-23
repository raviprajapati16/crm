<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Email_campaigns_emails extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('email_campaigns_emails_model');
        $this->load->model('email_campaigns_model');
        $this->load->model('mailservices_model');
    }

    public function index()
    {
        if (!has_permission('email_campaigns', '', 'view')) {
            access_denied('email_campaigns_emails');
        }
        $data['mail_services'] = $this->mailservices_model->get();
        if ($this->input->post()) {
            $this->app->get_table_data('email_campaigns_custom_emails');
        }
        $this->load->view('admin/email_campaigns_emails/index', $data);
    }

    public function get_data()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $emailData =  $this->email_campaigns_emails_model->get($data['id']);
            if ($emailData) {
                $result['success'] = true;
                $result['data'] =  $emailData;
            } else {
                $result['success'] = false;
                $result['message'] = "Invalid Data";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request";
        }
        echo json_encode($result);
    }

    public function save()
    {
        if (!has_permission('email_campaigns', '', 'created') || !has_permission('email_campaigns', '', 'edit')) {
            access_denied('email_campaigns_emails');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['id'] == '') {
                $id = $this->email_campaigns_emails_model->add($data);
                if ($id) {
                    set_alert('success', "Email successfully created.");
                } else {
                    set_alert('danger', "Error : Something went wrong.");
                }
            } else {
                $success = $this->email_campaigns_emails_model->update($data['id'], $data);
                set_alert('success', "Email successfully updated.");
            }
        }
        redirect(admin_url('email_campaigns_emails'));
    }

    public function delete($id)
    {
        if (!has_permission('email_campaigns', '', 'delete')) {
            access_denied('email_campaigns_emails');
        }
        if (!$id) {
            redirect(admin_url('email_campaigns_emails'));
        }

        $count = $this->email_campaigns_model->countRecords([
            "mail_send_from" => "custom_email",
            "mail_id" => $id
        ]);

        if ($count == 0) {
            $response = $this->email_campaigns_emails_model->delete($id);
            if ($response) {
                set_alert('success', "Email succesfully deleted.");
            } else {
                set_alert('danger', "Error : email not deleted");
            }
        } else {
            set_alert('warning', "Sorry ! This email is used in campaigns");
        }
        redirect(admin_url('email_campaigns_emails'));
    }
}
