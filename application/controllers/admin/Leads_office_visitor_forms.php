<?php

class Leads_office_visitor_forms extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Leadsnew_model');
        $this->load->model('leads_model');
        $this->load->model('office_visitor_form_model');
    }

    public function getOfficeVisitFormSection()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['lead_data'] = $this->leads_model->get($data['leadid']);
            if ($data['create_form'] == 'true') {
                $this->office_visitor_form_model->add_new_form([
                    "name" =>  $data['lead_data']->name,
                    "organization" =>  $data['lead_data']->company,
                    "email" => $data['lead_data']->email,
                    "phone" =>  $data['lead_data']->phonenumber,
                    "lead_id" => $data['leadid'],
                    "hash" => app_generate_hash(),
                    "terms_and_conditions" => get_option('office_visit_form_terms_and_conditions')
                ]);
            }
            $data['forms'] = $this->office_visitor_form_model->get(array("lead_id" => $data['leadid']));
            $data['countryData'] = get_country($data['lead_data']->country);
            $html = $this->load->view('admin/leads-office-visitor-form/main', $data, true);
            $result['success'] = true;
            $result['html'] = $html;
        } else {
            $result['success'] = false;
            $result['html'] = "No data available";
        }
        echo json_encode($result);
    }

    public function otp_verification()
    {
        $data = $this->input->post();
        $otp = rand(1000, 9999);
        if ($data['sendvia'] == "email") {
            if (isset($data['email']) && !empty($data['email'])) {
                $otp_slug = "lead_" . $data['leadid'] . "_";
                if ($data['type'] == "send-otp") {
                    $this->session->set_userdata($otp_slug . 'otp', $otp);
                    $this->session->set_userdata($otp_slug . 'otp_timestamp', time());
                    $lead = $this->leads_model->get($data['leadid']);
                    $emailSend = mail_template('lead_customer_office_visit_form_otp_send', $lead, $otp)->send();
                    if ($emailSend) {
                        echo json_encode(['success' => true, 'message' => 'OTP successfully send to your email.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error : OTP not send. try again later']);
                    }
                } else if ($data['type'] == "verify-otp") {
                    if (isset($data['otp']) && !empty($data['otp'])) {
                        $session_otp = $this->session->userdata($otp_slug . 'otp');
                        $otp_timestamp = $this->session->userdata($otp_slug . 'otp_timestamp');
                        $current_time = time();
                        if (($current_time - $otp_timestamp) > 300) {
                            echo json_encode(['success' => false, 'message' => 'OTP has expired. please click on resend to get new OTP.']);
                        } else if ($data['otp'] == $session_otp) {
                            echo json_encode(['success' => true, 'message' => 'New form successfully created.']);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Please enter OTP code.']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error : Something went wrong.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error : Invalid email.']);
            }
        } else if ($data['sendvia'] == "mobile") {
            if (isset($data['phoneNumber']) && !empty($data['phoneNumber'])) {
                if ($data['type'] == "send-otp") {
                    $check = $this->app_sms->send_otp($data['phoneNumber'], $otp);
                    if (isset($check->Status)) {
                        if ($check->Status == "Success") {
                            echo json_encode(['success' => true, 'message' => 'OTP Successfully send to mobile number.']);
                        } else {
                            echo json_encode(['success' => false, 'message' => $check->Details]);
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error : Something went wrong.']);
                    }
                } else if ($data['type'] == "verify-otp") {
                    $check = $this->app_sms->verify_otp($data['phoneNumber'], $data['otp']);
                    if (isset($check->Status)) {
                        if ($check->Status == "Success") {
                            echo json_encode(['success' => true, 'message' => 'OTP Successfully verified.']);
                        } else {
                            echo json_encode(['success' => false, 'message' => $check->Details]);
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error : Something went wrong.']);
                    }
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error : Invalid email.']);
            }
        }
    }

    public function form_status_change()
    {
        $data = $this->input->post();
        if (isset($data['id']) && isset($data['status'])) {
            $updateData =  $this->office_visitor_form_model->update_form(["active" => $data['status']], $data['id']);
            $status = ($data['status'] == "1") ? "Active " : " In-Active";
            $getForm =  $this->office_visitor_form_model->get_single(array("id" => $data['id']));
            $this->leads_model->log_lead_activity($getForm['lead_id'], "office visit form [ID " . $data['id'] . "] status changed to " . $status);
            if ($updateData) {
                $result['success'] = true;
                $result['message'] = "Form $status successfully";
            } else {
                $result['success'] = false;
                $result['message'] = "Form $status failed.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid Request";
        }
        echo json_encode($result);
    }

    public function send_form()
    {
        $data = $this->input->post();
        if (isset($data['lead_id']) && isset($data['id']) && isset($data['type'])) {
            $getForm =  $this->office_visitor_form_model->get_single(array("id" => $data['id']));
            if ($getForm) {
                if ($data['type'] == "email") {
                    $lead = $this->leads_model->get($data['lead_id']);
                    $lead->visitor_form_link = site_url('forms/covf/') . $getForm["hash"];
                    $checkEmail = send_lead_template_email_from_webmail('lead_customer_office_visitor_form_send', $lead);
                    if ($checkEmail) {
                        leadLastContactAtUpdate($data['lead_id']);
                        $this->office_visitor_form_model->update_form(["email_send_timestamp" => date('Y-m-d H:i:s')], $getForm['id']);
                        $this->leads_model->log_lead_activity($data['lead_id'], "Office visit form send via Email. Form ID : " . $data['id']);
                        $result['success'] = true;
                        $result['message'] = "Email send successfully";
                    } else {
                        $result['success'] = false;
                        $result['message'] = "Email not send.";
                    }
                } else { // Whatsapp send
                    $checkUpdate = $this->office_visitor_form_model->update_form(["whatsapp_send_timestamp" => date('Y-m-d H:i:s')], $getForm['id']);
                    if ($checkUpdate) {
                        leadLastContactAtUpdate($data['lead_id']);
                        $this->leads_model->log_lead_activity($data['lead_id'], "Office visit form share via Whatsapp. Form ID : " . $data['id']);
                        $result['success'] = true;
                        $result['message'] = "Whatsapp shared successfully";
                    } else {
                        $result['success'] = false;
                        $result['message'] = "Whatsapp not shared.";
                    }
                }
            } else {
                $result['success'] = false;
                $result['message'] = "Form not found.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request.";
        }
        echo json_encode($result);
    }

    public function delete_form()
    {
        $data = $this->input->post();
        if (isset($data['id'])) {
            $deleteForm =  $this->office_visitor_form_model->update_form([], $data['id'], true);
            if ($deleteForm) {
                $result['success'] = true;
                $result['message'] = "Form deleted successfully";
            } else {
                $result['success'] = false;
                $result['message'] = "Form not deleted.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request";
        }
        echo json_encode($result);
    }

    public function terms_and_conditions()
    {
        if ($this->input->post()) {
            $term_and_conditions = $this->input->post('ovf_terms_and_conditions', FALSE);
            $check = update_option('office_visit_form_terms_and_conditions', $term_and_conditions);
            if ($check) {
                log_activity("Office visit terms and condtions has been updated.");
                set_alert('success', "Terms and conditions successfully updated.");
            } else {
                set_alert('danger', "Terms and conditions not updated.");
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->load->view('admin/leads-office-visitor-form/ovf-terms-conditions');
    }
}
