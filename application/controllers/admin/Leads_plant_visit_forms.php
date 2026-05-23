<?php

class Leads_plant_visit_forms extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Leadsnew_model');
        $this->load->model('leads_model');
        $this->load->model('plant_visit_form_model');
    }

    public function getPlantVisitFormSection()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['lead_data'] = $this->leads_model->get($data['leadid']);
            if ($data['create_form'] == 'true') {
                $this->plant_visit_form_model->add_new_form([
                    "name" =>  $data['lead_data']->name,
                    "organization" =>  $data['lead_data']->company,
                    "email" => $data['lead_data']->email,
                    "phone" =>  $data['lead_data']->phonenumber,
                    "lead_id" => $data['leadid'],
                    "hash" => app_generate_hash(),
                    "tax_rate" => 18.00,
                    "tax_name" => "GST",
                    "terms_and_conditions" => get_option('plant_visit_form_terms_and_conditions')
                ]);
            }
            $data['forms'] = $this->plant_visit_form_model->get(array("lead_id" => $data['leadid']));
            $data['countryData'] = get_country($data['lead_data']->country);
            $html = $this->load->view('admin/leads-plant-visit-form/main', $data, true);
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
                $otp_slug = "lead_pvf_" . $data['leadid'] . "_";
                if ($data['type'] == "send-otp") {
                    $this->session->set_userdata($otp_slug . 'otp', $otp);
                    $this->session->set_userdata($otp_slug . 'otp_timestamp', time());
                    $lead = $this->leads_model->get($data['leadid']);
                    $emailSend = mail_template('lead_plant_visit_form_otp_send', $lead, $otp)->send();
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
            $updateData =  $this->plant_visit_form_model->update_form(["active" => $data['status']], $data['id']);
            $status = ($data['status'] == "1") ? "Active " : " In-Active";
            $getForm =  $this->plant_visit_form_model->get_single(array("id" => $data['id']));
            $this->leads_model->log_lead_activity($getForm['lead_id'], "Plant visit form [ID " . $data['id'] . "] status changed to " . $status);
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
            $getForm =  $this->plant_visit_form_model->get_single(array("id" => $data['id']));
            if ($getForm) {
                if ($data['type'] == "email") {
                    $lead = $this->leads_model->get($data['lead_id']);
                    $lead->visitor_form_link = site_url('forms/pvf/') . $getForm["hash"];
                    $checkEmail =  send_lead_template_email_from_webmail('lead_plant_visit_form_send', $lead);
                    if ($checkEmail) {
                        leadLastContactAtUpdate($data['lead_id']);
                        $this->plant_visit_form_model->update_form(["email_send_timestamp" => date('Y-m-d H:i:s')], $getForm['id']);
                        $this->leads_model->log_lead_activity($data['lead_id'], "Plant visit form send via Email. Form ID : " . $data['id']);
                        $result['success'] = true;
                        $result['message'] = "Email send successfully";
                    } else {
                        $result['success'] = false;
                        $result['message'] = "Email not send.";
                    }
                } else { // Whatsapp send
                    $checkUpdate = $this->plant_visit_form_model->update_form(["whatsapp_send_timestamp" => date('Y-m-d H:i:s')], $getForm['id']);
                    if ($checkUpdate) {
                        leadLastContactAtUpdate($data['lead_id']);
                        $this->leads_model->log_lead_activity($data['lead_id'], "Plant visit form share via Whatsapp. Form ID : " . $data['id']);
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
            $deleteForm =  $this->plant_visit_form_model->update_form([], $data['id'], true);
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

    public function form_approval_status_change()
    {
        if ($this->input->post()) {
            $post_data = $this->input->post();
            if (isset($post_data['form_status']) && isset($post_data['form_id'])) {
                $oldGetForm =  $this->plant_visit_form_model->get_by_id($post_data['form_id']);
                $updateArr = [
                    "approval_staus" => $post_data['form_status'],
                    "reject_note" => $post_data['reject_note'],
                    "status_changed_by" => get_staff_user_id(),
                    "status_updated_at" => date('Y-m-d H:i:s')
                ];
                $dateChangeText = "";
                if ($post_data['form_status'] == "Approved") {
                    $post_data['reject_note'] = "";
                    $updateArr['visit_date_time'] = $oldGetForm['visit_date_time'];
                    if (!empty($oldGetForm['visit_date_time']) && $oldGetForm['visit_date_time'] != to_sql_date($post_data['visit_date_time'], true)) {
                        $updateArr['visit_date_time'] = to_sql_date($post_data['visit_date_time'], true);
                        $dateChangeText = "and visit date and time changed from " . date("d-m-Y H:i", strtotime($oldGetForm['visit_date_time'])) . " to " . $post_data['visit_date_time'];
                    }
                    if (get_plant_visitform_check_free_visit(["visit_date_time" => $updateArr['visit_date_time'], "is_free_visit" => $oldGetForm['is_free_visit'], "free_visit_day" => $oldGetForm['free_visit_day']])) {
                        $updateArr['total_amount'] = 0;
                        $updateArr['tax_amount'] = 0;
                        $updateArr['total_pay_amount'] = 0;
                    } else {
                        $updateArr['total_amount'] = 0;
                        if ($oldGetForm['charge_type'] == "fixed") {
                            $updateArr['total_amount'] = $oldGetForm['visit_amount'];
                        } else {
                            $updateArr['total_amount'] = ($oldGetForm['visit_amount'] * $oldGetForm['total_members']);
                        }
                        $updateArr['tax_amount'] = ($updateArr['total_amount'] * $oldGetForm['tax_rate']) / 100;
                        $updateArr['total_pay_amount'] = $updateArr['total_amount'] + $updateArr['tax_amount'];
                    }
                }

                if ($post_data['form_status'] == "Not Approved") {
                    $updateArr['active'] = '1';
                    $updateArr['customer_submitted'] = NULL;
                }
                $checkUpdate = $this->plant_visit_form_model->update_form($updateArr, $post_data['form_id'], false, false);
                if ($checkUpdate) {
                    $getForm =  $this->plant_visit_form_model->get_by_id($post_data['form_id']);
                    $this->leads_model->log_lead_activity($getForm['lead_id'], "Plant Visit form status changed to " . strtoupper($getForm['approval_staus']) . " $dateChangeText in Form ID : " . $post_data['form_id']);
                    $result['success'] = true;
                    $result['message'] = "Form status successfully changed to " . strtoupper($post_data['form_status']);
                } else {
                    $result['success'] = false;
                    $result['message'] = "Form not updated.";
                }
            } else {
                $result['success'] = false;
                $result['message'] = "Invalid Request.";
            }
            echo json_encode($result);
        }
    }

    public function send_approve_not_approved_notify()
    {
        $data = $this->input->post();
        if (isset($data['formId']) && isset($data['type'])) {
            $getForm =  $this->plant_visit_form_model->get_by_id($data['formId']);
            if ($getForm) {
                if ($data['type'] == "email") {
                    $lead = $this->leads_model->get($getForm['lead_id']);
                    $lead->plant_visit_form_link = site_url('forms/pvf/') . $getForm["hash"];
                    $lead->plant_visit_form_not_approved_reason = $getForm['reject_note'];
                    $lead->plant_visit_payable_amount = $getForm['total_pay_amount'];
                    $lead->plant_visit_total_members = $getForm['total_members'];
                    $lead->plant_visit_date_time = $getForm['visit_date_time'];
                    $checkEmail =  send_lead_template_email_from_webmail(($getForm['approval_staus'] == "Approved") ? "lead_plant_visit_form_approved" : "lead_plant_visit_form_not_approved", $lead);
                    if ($checkEmail) {
                        $this->leads_model->log_lead_activity($getForm['lead_id'], "Plant Visit form " . strtoupper($getForm['approval_staus']) . " email has been send. Form ID : " . $data['formId']);
                        leadLastContactAtUpdate($getForm['lead_id']);
                        $result['success'] = true;
                        $result['message'] = "Email send successfully";
                    } else {
                        $result['success'] = false;
                        $result['message'] = "Email not send.";
                    }
                } else {
                    $this->leads_model->log_lead_activity($getForm['lead_id'], "Plant Visit form " . strtoupper($getForm['approval_staus']) . " shared via whatsapp. Form ID : " . $data['formId']);
                    leadLastContactAtUpdate($getForm['lead_id']);
                    $result['success'] = true;
                    $result['message'] = "Whatsapp shared successfully";
                }
            } else {
                $result['success'] = false;
                $result['message'] = "Form not send.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request.";
        }
        echo json_encode($result);
    }

    public function plant_visit_settings()
    {
        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            if (isset($data['pvf-visitor-types'])) {
                $this->app->get_table_data('plant_visitor_types');
            }
            if (isset($data['pvf-relation-types'])) {
                $this->app->get_table_data('plant_visit_relation_types');
            }
        }
        $this->load->view('admin/leads-plant-visit-form/plant_visit_settings');
    }

    public function terms_and_conditions_update()
    {
        if ($this->input->post()) {
            $term_and_conditions = $this->input->post('pvf_terms_and_conditions', FALSE);
            $check = update_option('plant_visit_form_terms_and_conditions', $term_and_conditions);
            if ($check) {
                log_activity("Plant visit terms and condtions has been updated.");
                set_alert('success', "Terms and conditions successfully updated.");
            } else {
                set_alert('danger', "Terms and conditions not updated.");
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function get_visitor_type()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if (isset($data['id'])) {
                $getData =  $this->plant_visit_form_model->get_plant_visitor_type_by_id($data['id']);
                if ($getData) {
                    $result['success'] = true;
                    $result['data'] = $getData;
                } else {
                    $result['success'] = false;
                    $result['message'] = "Invalid Request";
                }
            } else {
                $result['success'] = false;
                $result['message'] = "Invalid Request";
            }
            echo json_encode($result);
        }
    }

    public function save_visitor_type_data()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['free_visit_allowed'] == "0") {
                $data['free_visit_day'] = NULL;
            }
            if (empty($data['id'])) {
                $data['active'] = '1';
                $check =  $this->plant_visit_form_model->add_plant_visitor_type($data);
            } else {
                $check =  $this->plant_visit_form_model->update_plant_visitor_type($data, $data['id']);
            }
            if ($check) {
                set_alert('success', "Visitor type successfully saved.");
            } else {
                set_alert('danger', "Error : Visitor type not saved.");
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function visitor_type_active_inactive_status_change()
    {
        $data = $this->input->post();
        if (isset($data['id']) && isset($data['status'])) {
            $updateData =  $this->plant_visit_form_model->update_plant_visitor_type(["active" => $data['status']], $data['id']);
            $status = ($data['status'] == "1") ? "Active " : " In-Active";
            if ($updateData) {
                $result['success'] = true;
                $result['message'] = "$status successfully";
            } else {
                $result['success'] = false;
                $result['message'] = "$status failed.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid Request";
        }
        echo json_encode($result);
    }

    public function get_relation_type()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if (isset($data['id'])) {
                $getData =  $this->plant_visit_form_model->get_relation_type_by_id($data['id']);
                if ($getData) {
                    $result['success'] = true;
                    $result['data'] = $getData;
                } else {
                    $result['success'] = false;
                    $result['message'] = "Invalid Request";
                }
            } else {
                $result['success'] = false;
                $result['message'] = "Invalid Request";
            }
            echo json_encode($result);
        }
    }

    public function delete_visitor_type($id)
    {
        $count = $this->plant_visit_form_model->count_plant_visit_form_by_visitor_type_id($id);
        if ($count > 0) {
            set_alert('warning', "Sorry! This visitor type is used in plant visit form.");
        } else {
            $check = $this->plant_visit_form_model->update_plant_visitor_type([], $id, true);
            if ($check) {
                set_alert('success', "Visitor type successfully deleted");
            } else {
                set_alert('success', "Visitor type not deleted");
            }
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function save_relation_type_data()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['free_visit_allowed'] == "0") {
                $data['free_visit_day'] = NULL;
            }
            if (empty($data['id'])) {
                $data['active'] = '1';
                $check =  $this->plant_visit_form_model->add_relation_type($data);
            } else {
                $check =  $this->plant_visit_form_model->update_relation_type($data, $data['id']);
            }
            if ($check) {
                set_alert('success', "Relation type successfully saved.");
            } else {
                set_alert('danger', "Error : Relation type not saved.");
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function relation_type_active_inactive_status_change()
    {
        $data = $this->input->post();
        if (isset($data['id']) && isset($data['status'])) {
            $updateData =  $this->plant_visit_form_model->update_relation_type(["active" => $data['status']], $data['id']);
            $status = ($data['status'] == "1") ? "Active " : " In-Active";
            if ($updateData) {
                $result['success'] = true;
                $result['message'] = "$status successfully";
            } else {
                $result['success'] = false;
                $result['message'] = "$status failed.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid Request";
        }
        echo json_encode($result);
    }

    public function delete_relation_type($id)
    {
        $check = $this->plant_visit_form_model->update_relation_type([], $id, true);
        if ($check) {
            set_alert('success', "Visitor type successfully deleted");
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            set_alert('success', "Visitor type not deleted");
            redirect($_SERVER['HTTP_REFERER']);
        }
    }
}
