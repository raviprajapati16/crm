<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Webmails extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('app_webmails');
        $this->load->model('webmailsettings_model');
        $this->load->model('mailservices_model');
        $this->load->model('staff_model');
    }


    public function index()
    {
        $staff = get_staff(get_staff_user_id());
        $mailServiceData = $this->mailservices_model->get_single($staff->mail_service);
        if (!empty($mailServiceData)) {
            if ($mailServiceData->only_send == "0") {
                $data['auth_data'] = $this->app_webmails->authentication();
            }
            $data['mail_service_data'] = $mailServiceData;
            $data['staff_data'] = $staff;
            $this->load->view('admin/webmails/home', $data);
        } else {
            set_alert('danger', "Mail service not configured");
            redirect(admin_url());
        }
    }

    public function get_email_list()
    {
        $data = $this->input->post();
        $data['email_data'] = $this->app_webmails->fetch_emails($data);
        $html = $this->load->view('admin/webmails/email-list-render', $data, true);
        echo json_encode([
            'success' => true,
            'html' => $html,
        ]);
    }

    public function compose_new_email()
    {
        $data = $this->input->post();
        $html = $this->load->view('admin/webmails/compose-new-email-render', $data, true);
        echo json_encode([
            'success' => true,
            'html' => $html,
        ]);
    }

    public function view_email()
    {
        $data = $this->input->post();
        $result = $this->app_webmails->fetch_single_email($data);
        $result['folder'] =  $data['folder'];
        if ($result['success']) {
            $html = $this->load->view('admin/webmails/view-email-render', $result, true);
            $response['success'] = true;
            $response['html'] = $html;
        } else {
            $response = $result;
        }
        echo json_encode($response);
    }


    public function email_actions()
    {
        $data = $this->input->post();
        $get_attachments = false;
        if ($data['mode'] == "draft") {
            $get_attachments = true;
        }
        $result = $this->app_webmails->fetch_single_email($data, $get_attachments);
        if ($result['success']) {
            $result['mode'] = $data['mode'];
            $result['email_no'] = $data['email_no'];
            $html = $this->load->view('admin/webmails/compose-new-email-render', $result, true);
            $response['success'] = true;
            $response['html'] = $html;
        } else {
            $response = $result;
        }
        echo json_encode($response);
    }

    public function forward_selected()
    {
        $data = $this->input->post();
        $result = $this->app_webmails->create_eml_files($data);
        if ($result['success']) {
            $result['mode'] = $data['mode'];
            $result['email_no'] = $data['email_no'];
            $html = $this->load->view('admin/webmails/compose-new-email-render', $result, true);
            $response['success'] = true;
            $response['html'] = $html;
        } else {
            $response = $result;
        }
        echo json_encode($response);
    }

    public function delete_email()
    {
        $data = $this->input->post();
        $result = $this->app_webmails->delete_emails($data);
        echo json_encode($result);
    }

    public function move_to_inbox()
    {
        $data = $this->input->post();
        $result = $this->app_webmails->move_to_inbox($data);
        echo json_encode($result);
    }

    public function mark_as_read()
    {
        $data = $this->input->post();
        $result = $this->app_webmails->mark_as_read($data);
        echo json_encode($result);
    }

    public function temp_upload()
    {
        $result['success'] = false;
        $temp_folder = time() . uniqid();
        if (isset($_FILES['file'])) {
            if ($_FILES['file']['size']) {
                $upload_path = "uploads/temp_mail_attachments/$temp_folder";
                $new_filename =  unique_filename($upload_path, $_FILES['file']['name']);
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0755, true);
                }
                $new_path = $upload_path . '/' . $new_filename;
                if (move_uploaded_file($_FILES['file']['tmp_name'], $new_path)) {
                    $result['success'] = true;
                    $result['filename'] = $new_filename;
                    $result['file_path'] = $new_path;
                }
            }
        }
        echo json_encode($result);
    }

    public function delete_temp_file()
    {
        $data = $this->input->post();
        $result['success'] = false;
        if (isset($data['path'])) {
            if (file_exists($data['path'])) {
                unlink($data['path']);
            }
            $result['success'] = true;
            $result['message'] = "File successfully deleted.";
        }
        echo json_encode($result);
    }

    public function send_mail()
    {
        $data = $this->input->post();
        $data['body'] = $this->input->post('body', FALSE);
        if ($data) {
            if (isset($data['to']) && !empty($data['to'])) {
                $data['to'] = array_values(array_column(json_decode($data['to']), 'value'));
            }
            if (isset($data['cc']) && !empty($data['cc'])) {
                $data['cc'] = array_values(array_column(json_decode($data['cc']), 'value'));
            }
            if (isset($data['bcc']) && !empty($data['bcc'])) {
                $data['bcc'] = array_values(array_column(json_decode($data['bcc']), 'value'));
            }
            if (isset($data['replyto']) && !empty($data['replyto'])) {
                $data['replyto'] = array_values(array_column(json_decode($data['replyto']), 'value'));
            }

            $result = $this->app_webmails->send_email($data);
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request.";
        }

        echo json_encode($result);
    }

    public function save_draft()
    {
        $data = $this->input->post();
        $data['body'] = $this->input->post('body', false);
        if ($data) {
            if (isset($data['to']) && !empty($data['to'])) {
                $data['to'] = array_values(array_column(json_decode($data['to']), 'value'));
            }
            if (isset($data['cc']) && !empty($data['cc'])) {
                $data['cc'] = array_values(array_column(json_decode($data['cc']), 'value'));
            }
            if (isset($data['bcc']) && !empty($data['bcc'])) {
                $data['bcc'] = array_values(array_column(json_decode($data['bcc']), 'value'));
            }
            if (isset($data['replyto']) && !empty($data['replyto'])) {
                $data['replyto'] = array_values(array_column(json_decode($data['replyto']), 'value'));
            }
            $result = $this->app_webmails->saveDraftEmail($data);
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request.";
        }

        echo json_encode($result);
    }

    public function getInboxUnreadCount()
    {
        $data = $this->input->post();
        $staff = get_staff(get_staff_user_id());
        if (empty($staff->webmail_email) || empty($staff->webmail_password) || empty($staff->mail_service)) {
            echo json_encode([
                'success' => true,
                'inboxCount' => 0
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'inboxCount' => $this->app_webmails->getInboxUnreadCount()
            ]);
        }
    }

    public function email_suggestions()
    {
        $result = [];
        $data = $this->input->post();
        $type = ($this->input->post('type') ? $this->input->post('type') : null);
        $CI = &get_instance();

        //fetch leads emails
        $leads_emails = [];
        if ((empty($type) || $type == "leads") && !empty($data['query'])) {
            $CI->db->select('email');
            $CI->db->like('email', $data['query'], 'both');
            $CI->db->order_by('email', 'asc');
            $leads_emails = $CI->db->get(db_prefix() . 'leads')->result_array();
            $leads_emails = (!empty($leads_emails)) ? array_column($leads_emails, 'email') : [];
        }

        //staff emails
        $staff_emails = [];
        if ((empty($type) || $type == "staff") && !empty($data['query'])) {
            $CI->db->select('email');
            $CI->db->like('email', $data['query'], 'both');
            $CI->db->order_by('email', 'asc');
            $CI->db->where('datedeleted IS NULL');
            $CI->db->where('active', 1);
            $staff_emails = $CI->db->get(db_prefix() . 'staff')->result_array();
            $staff_emails = (!empty($staff_emails)) ? array_column($staff_emails, 'email') : [];
        }

        //contact emails
        $contact_emails = [];
        if ((empty($type) || $type == "customer") && !empty($data['query'])) {
            $CI->db->select('email');
            $CI->db->like('email', $data['query'], 'both');
            $CI->db->order_by('email', 'asc');
            $CI->db->where('deleted_at IS NULL');
            $contact_emails = $CI->db->get(db_prefix() . 'contacts')->result_array();
            $contact_emails = (!empty($contact_emails)) ? array_column($contact_emails, 'email') : [];
        }

        //merge all emails
        $all_emails = array_values(array_unique(array_filter(array_merge($leads_emails, $staff_emails, $contact_emails))));
        sort($all_emails);
        $result = array_values($all_emails);
        header('Content-Type: application/json');
        echo json_encode(array_values($result));
    }

    public function update_webmail_signature()
    {
        $data = $this->input->post();
        $success = false;
        if (!isset($data['user_id']) || empty($data['user_id'])) {
            $data['user_id'] = get_staff_user_id();
        }
        $check = $this->webmailsettings_model->update_webmail_signature($data);
        $this->staff_model->update_data(["email_signature" => get_webmail_signature($data['user_id'])], $data['user_id']);
        if ($check) {
            $success = true;
            $messsage = "Signature successfully updated.";
        } else {
            $messsage = "Error : Signature not updated.";
        }
        echo json_encode([
            'success' => $success,
            'message' => $messsage
        ]);
    }

    public function email_signature_preview()
    {
        $data = $this->input->post();
        $templatePath = "admin/webmails/email_signature_templates/" . $data['template'];
        if (!empty($data['template'])) {
            $templateContent = $this->load->view($templatePath, $data, true);
            echo json_encode([
                'success' => true,
                'html' => $templateContent
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'html' => ""
            ]);
        }
    }

    public function webmail_folder_save()
    {
        $data = $this->input->post();
        $result = $this->app_webmails->webmail_folder_save($data);
        echo json_encode($result);
    }

    public function webmail_move_to_folder()
    {
        $data = $this->input->post();
        $result = $this->app_webmails->webmail_move_to_folder($data);
        echo json_encode($result);
    }

    public function webmail_delete_folder()
    {
        $data = $this->input->post();
        $result = $this->app_webmails->webmail_delete_folder($data);
        echo json_encode($result);
    }

    public function template_check($template)
    {
        $this->load->view("admin/webmails/email_signature_templates/$template", []);
    }
}
