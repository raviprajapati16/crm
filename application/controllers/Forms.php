<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Forms extends ClientsController
{
    public function index()
    {
        show_404();
    }

    /**
     * Web to lead form
     * User no need to see anything like LEAD in the url, this is the reason the method is named wtl
     * @param  string $key web to lead form key identifier
     * @return mixed
     */

    public function inquiryForm()
    {
        redirect(site_url('forms/wtl/ubkjdzi2txg33jnp4r5rrue2eyhvha'));
    }

    public function wtl($key)
    {
        $post_data = $this->input->post();
        $this->load->model('leads_model');
        $form = $this->leads_model->get_form([
            'form_key' => $key,
        ]);

        if (!$form) {
            show_404();
        }

        $data['form_fields'] = json_decode($form->form_data);
        if (!$data['form_fields']) {
            $data['form_fields'] = [];
        }
        if ($this->input->post('key')) {
            if ($this->input->post('key') == $key) {
                $post_data = $this->input->post();
                $required = [];
                //get Country Data
                $countryData = [];;
                if (isset($post_data['country'])) {
                    if (is_numeric($post_data['country'])) {
                        $countryData =  get_country($post_data['country']);
                    } else {
                        $this->db->where('iso2', $post_data['country']);
                        $this->db->or_where('short_name', $post_data['country']);
                        $this->db->or_where('long_name', $post_data['country']);
                        $countryQuery = $this->db->get(db_prefix() . 'countries')->row();
                        if ($countryQuery) {
                            $countryData = $countryQuery;
                        }
                    }
                }

                foreach ($data['form_fields'] as $field) {
                    if (isset($field->required)) {
                        $required[] = $field->name;
                    }
                }
                if (is_gdpr() && get_option('gdpr_enable_terms_and_conditions_lead_form') == 1) {
                    $required[] = 'accept_terms_and_conditions';
                }

                foreach ($required as $field) {
                    if (!isset($post_data[$field]) || isset($post_data[$field]) && empty($post_data[$field])) {
                        $this->output->set_status_header(422);
                        die;
                    }
                }

                if (get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != '' && $form->recaptcha == 1) {
                    if (!do_recaptcha_validation($post_data['g-recaptcha-response'])) {
                        echo json_encode([
                            'success' => false,
                            'message' => _l('recaptcha_error'),
                        ]);
                        die;
                    }
                }

                if (isset($post_data['g-recaptcha-response'])) {
                    unset($post_data['g-recaptcha-response']);
                }

                unset($post_data['key']);

                $regular_fields = [];
                $custom_fields = [];
                foreach ($post_data as $name => $val) {
                    //phonenumber formating
                    if ($name == 'phonenumber') {
                        if (isset($countryData->iso2)) {
                            $val = "+" . convert_phonenumer_by_country($val, $countryData->iso2);
                        }
                    }
                    if (strpos($name, 'form-cf-') !== false) {
                        array_push($custom_fields, [
                            'name' => $name,
                            'value' => $val,
                        ]);
                    } else {
                        if ($this->db->field_exists($name, db_prefix() . 'leads')) {
                            if ($name == 'country') {
                                if (!empty($countryData)) {
                                    $val = $countryData->country_id;
                                } else {
                                    $val = 0;
                                }
                            } elseif ($name == 'address') {
                                $val = trim($val);
                                $val = nl2br($val);
                            }
                            $regular_fields[$name] = $val;
                        }
                    }
                }



                $success = false;
                $insert_to_db = true;

                if ($form->allow_duplicate == 0) {
                    $where1 = [];
                    $where2 = [];
                    $duplicateLead = false;
                    if (!empty($form->track_duplicate_field) && isset($regular_fields[$form->track_duplicate_field])) {
                        $where1['column'] = $form->track_duplicate_field;
                        $where1['value'] = $regular_fields[$form->track_duplicate_field];
                    }
                    if (!empty($form->track_duplicate_field_and) && isset($regular_fields[$form->track_duplicate_field_and])) {
                        $where2['column'] = $form->track_duplicate_field_and;
                        $where2['value'] = $regular_fields[$form->track_duplicate_field_and];
                    }

                    $email = (isset($regular_fields['email'])) ? $regular_fields['email'] : null;
                    $phonenumber = (isset($regular_fields['phonenumber'])) ? $regular_fields['phonenumber'] : null;
                    if (!empty($email) || !empty($phonenumber)) {
                        $duplicateLead = duplicateLeadData($email, $phonenumber);
                    } else if (!empty($where1) || !empty($where2)) {
                        $duplicateLead = $this->leads_model->checkDuplicateLead($where1, $where2);
                    }

                    if ($duplicateLead) {
                        // Success set to true for the response.
                        $success = true;
                        $insert_to_db = false;
                        if ($form->create_reminder_on_duplicate == 1) {
                            $leadEmail = "";
                            foreach ($duplicateLead as $row) {
                                $leadid = $row->id;
                                $assigned = $row->assigned;
                                $description = 'Duplicate Lead - Description: ' . $post_data['description'];
                                $leadEmail = $row->email;
                                break;
                            }
                            $reminderData['notify_by_email'] = "";
                            $reminderData['date'] = date("Y-m-d H:i:s");
                            $reminderData['description'] = $description;
                            $reminderData['rel_id'] = $leadid;
                            $reminderData['rel_type'] = "lead";
                            $reminderData['staff'] = $assigned;
                            $reminderData['creator'] = 1;
                            $reminderData['reminder_action'] = "Call";
                            $reminderData['status'] = "Pending";
                            $reminderData['created_at'] = date("Y-m-d H:i:s");
                            $this->db->insert(db_prefix() . 'reminders', $reminderData);
                            $reminder_id = $this->db->insert_id();
                            if (!empty($leadEmail)) {
                                send_mail_template('lead_web_form_submitted', $duplicateLead);
                            }
                        }
                    }
                }

                if ($insert_to_db == true) {
                    $regular_fields['status'] = $form->lead_status;
                    if ((isset($regular_fields['name']) && empty($regular_fields['name'])) || !isset($regular_fields['name'])) {
                        $regular_fields['name'] = 'Unknown';
                    }
                    $regular_fields['source'] = $form->lead_source;
                    $regular_fields['addedfrom'] = 0;
                    $regular_fields['lastcontact'] = null;
                    $regular_fields['assigned'] = $form->responsible;
                    $regular_fields['dateadded'] = date('Y-m-d H:i:s');
                    $regular_fields['from_form_id'] = $form->id;
                    $regular_fields['is_public'] = $form->mark_public;
                    $this->db->insert(db_prefix() . 'leads', $regular_fields);
                    $lead_id = $this->db->insert_id();
                    //save product name
                    handle_tags_save($post_data['product'], $lead_id, 'lead');
                    hooks()->do_action('lead_created', [
                        'lead_id' => $lead_id,
                        'web_to_lead_form' => true,
                    ]);

                    $success = false;
                    if ($lead_id) {
                        $success = true;

                        $this->leads_model->log_lead_activity($lead_id, 'not_lead_imported_from_form', true, serialize([
                            $form->name,
                        ]));
                        // /handle_custom_fields_post
                        $custom_fields_build['leads'] = [];
                        foreach ($custom_fields as $cf) {
                            $cf_id = strafter($cf['name'], 'form-cf-');
                            $custom_fields_build['leads'][$cf_id] = $cf['value'];
                        }

                        $this->leads_model->lead_assigned_member_notification($lead_id, $form->responsible, true);
                        handle_custom_fields_post($lead_id, $custom_fields_build);
                        handle_lead_attachments($lead_id, 'file-input', $form->name);

                        if ($form->notify_lead_imported != 0) {
                            if ($form->notify_type == 'assigned') {
                                $to_responsible = true;
                            } else {
                                $ids = @unserialize($form->notify_ids);
                                $to_responsible = false;
                                if ($form->notify_type == 'specific_staff') {
                                    $field = 'staffid';
                                } elseif ($form->notify_type == 'roles') {
                                    $field = 'role';
                                }
                            }

                            if ($to_responsible == false && is_array($ids) && count($ids) > 0) {
                                $this->db->where('active', 1);
                                $this->db->where_in($field, $ids);
                                $staff = $this->db->get(db_prefix() . 'staff')->result_array();
                            } else {
                                $staff = [
                                    [
                                        'staffid' => $form->responsible,
                                    ],
                                ];
                            }
                            $notifiedUsers = [];
                            foreach ($staff as $member) {
                                if ($member['staffid'] != 0) {
                                    $notified = add_notification([
                                        'description' => 'not_lead_imported_from_form',
                                        'touserid' => $member['staffid'],
                                        'fromcompany' => 1,
                                        'fromuserid' => null,
                                        'additional_data' => serialize([
                                            $form->name,
                                        ]),
                                        'link' => '#leadid=' . $lead_id,
                                    ]);
                                    if ($notified) {
                                        array_push($notifiedUsers, $member['staffid']);
                                    }
                                }
                            }
                            pusher_trigger_notification($notifiedUsers);
                        }
                        if (isset($regular_fields['email']) && $regular_fields['email'] != '') {
                            $lead = $this->leads_model->get($lead_id);
                            send_mail_template('lead_web_form_submitted', $lead);
                        }
                    }
                } // end insert_to_db
                if ($success == true) {
                    if (!isset($lead_id)) {
                        $lead_id = 0;
                    }
                    if (!isset($reminder_id)) {
                        $reminder_id = 0;
                    }
                    hooks()->do_action('web_to_lead_form_submitted', [
                        'lead_id' => $lead_id,
                        'form_id' => $form->id,
                        'reminder_id' => $reminder_id,
                    ]);
                }
                if ($form->send_brochure_email == "1") {
                    if (isset($regular_fields['email']) && $regular_fields['email'] != '') {
                        $email = $regular_fields['email'];
                        send_mail_template('leads_form_brochure_download', $email);
                    }
                }
                hooks()->do_action('form_submitted', $post_data);
                echo json_encode([
                    'success' => $success,
                    'message' => $form->success_submit_msg,
                ]);
                die;
            }
        }
        $data['form'] = $form;
        if ($form->company_theme == "1") {
            $this->load->view('forms/web_to_lead_theme', $data);
        } else {
            $this->load->view('forms/web_to_lead', $data);
        }
    }

    /**
     * Web to lead form
     * User no need to see anything like LEAD in the url, this is the reason the method is named eq lead
     * @param  string $hash lead unique identifier
     * @return mixed
     */
    public function l($hash)
    {
        if (get_option('gdpr_enable_lead_public_form') == '0') {
            show_404();
        }
        $this->load->model('leads_model');
        $this->load->model('gdpr_model');
        $lead = $this->leads_model->get('', ['hash' => $hash]);

        if (!$lead || count($lead) > 1) {
            show_404();
        }

        $lead = array_to_object($lead[0]);
        load_lead_language($lead->id);

        if ($this->input->post('update')) {
            $data = $this->input->post();
            unset($data['update']);
            $this->leads_model->update($data, $lead->id);
            redirect($_SERVER['HTTP_REFERER']);
        } elseif ($this->input->post('export') && get_option('gdpr_data_portability_leads') == '1') {
            $this->load->library('gdpr/gdpr_lead');
            $this->gdpr_lead->export($lead->id);
        } elseif ($this->input->post('removal_request')) {
            $success = $this->gdpr_model->add_removal_request([
                'description' => nl2br($this->input->post('removal_description')),
                'request_from' => $lead->name,
                'lead_id' => $lead->id,
            ]);
            if ($success) {
                send_gdpr_email_template('gdpr_removal_request_by_lead', $lead->id);
                set_alert('success', _l('data_removal_request_sent'));
            }
            redirect($_SERVER['HTTP_REFERER']);
        }

        $lead->attachments = $this->leads_model->get_lead_attachments($lead->id);
        $this->disableNavigation();
        $this->disableSubMenu();
        $data['title'] = $lead->name;
        $data['lead'] = $lead;
        $this->view('forms/lead');
        $this->data($data);
        $this->layout(true);
    }

    public function ticket()
    {
        $form = new stdClass();
        $form->language = get_option('active_language');
        $form->recaptcha = 1;

        $this->lang->load($form->language . '_lang', $form->language);
        if (file_exists(APPPATH . 'language/' . $form->language . '/custom_lang.php')) {
            $this->lang->load('custom_lang', $form->language);
        }

        $form->success_submit_msg = _l('success_submit_msg');

        $form = hooks()->apply_filters('ticket_form_settings', $form);

        if ($this->input->post() && $this->input->is_ajax_request()) {
            $post_data = $this->input->post();

            $required = ['subject', 'department', 'email', 'name', 'message', 'priority'];

            if (is_gdpr() && get_option('gdpr_enable_terms_and_conditions_ticket_form') == 1) {
                $required[] = 'accept_terms_and_conditions';
            }

            foreach ($required as $field) {
                if (!isset($post_data[$field]) || isset($post_data[$field]) && empty($post_data[$field])) {
                    $this->output->set_status_header(422);
                    die;
                }
            }

            if (get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != '' && $form->recaptcha == 1) {
                if (!do_recaptcha_validation($post_data['g-recaptcha-response'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => _l('recaptcha_error'),
                    ]);
                    die;
                }
            }

            $post_data = [
                'email' => $post_data['email'],
                'name' => $post_data['name'],
                'subject' => $post_data['subject'],
                'department' => $post_data['department'],
                'priority' => $post_data['priority'],
                'service' => isset($post_data['service']) && is_numeric($post_data['service'])
                    ? $post_data['service']
                    : null,
                'custom_fields' => isset($post_data['custom_fields']) && is_array($post_data['custom_fields'])
                    ? $post_data['custom_fields']
                    : [],
                'message' => $post_data['message'],
            ];

            $success = false;

            $this->db->where('email', $post_data['email']);
            $this->db->where('deleted_at IS NULL');
            $result = $this->db->get(db_prefix() . 'contacts')->row();

            if ($result) {
                $post_data['userid'] = $result->userid;
                $post_data['contactid'] = $result->id;
                unset($post_data['email']);
                unset($post_data['name']);
            }

            $this->load->model('tickets_model');

            $post_data = hooks()->apply_filters('ticket_external_form_insert_data', $post_data);
            $ticket_id = $this->tickets_model->add($post_data);

            if ($ticket_id) {
                $success = true;
            }

            if ($success == true) {
                hooks()->do_action('ticket_form_submitted', [
                    'ticket_id' => $ticket_id,
                ]);
            }

            echo json_encode([
                'success' => $success,
                'message' => $form->success_submit_msg,
            ]);

            die;
        }

        $this->load->model('tickets_model');
        $this->load->model('departments_model');
        $data['departments'] = $this->departments_model->get();
        $data['priorities'] = $this->tickets_model->get_priority();

        $data['priorities']['callback_translate'] = 'ticket_priority_translate';
        $data['services'] = $this->tickets_model->get_service();

        $data['form'] = $form;
        $this->load->view('forms/ticket', $data);
    }

    //Lead customer inquiry form
    public function cif($formkey)
    {

        $this->load->model('leads_model');
        $this->load->model('Lead_inquiry_form_images_model');
        $data['form'] = $this->leads_model->get_inquiry_forms_by_formkey($formkey);
        if (!$data['form']) {
            show_404();
        }
        $data['background_slider_image'] = $this->Lead_inquiry_form_images_model->get_background_slider_images();
        $data['popup_image'] = $this->Lead_inquiry_form_images_model->get_active_popup_image();
        $data['form_questions'] = $this->leads_model->get_inquiry_forms_data($data['form']['id']);
        if ($this->input->post()) {
            $post_data = $this->input->post();
            $formId = $data['form']['id'];
            if ($data['form_questions']) {
                $updateAnswerCheck = false;
                foreach ($data['form_questions'] as $key => $item) {
                    $answer = (isset($post_data[$item['id']])) ? $post_data[$item['id']] : "";
                    if (!empty($answer)) {
                        $answer = is_array($answer) ? implode(",", $answer) : $answer;
                    }
                    if ($item['type'] == "fileupload") {
                        if (isset($_FILES[$item['id']]) && $_FILES[$item['id']]['size']) {
                            $upload_path = 'uploads/leads/' . $data['form']['lead_id'] . '/';
                            $new_filename = $item['form_id'] . '_' . $item['id'] . '_' . unique_filename($upload_path, $_FILES[$item['id']]['name']);
                            _maybe_create_upload_path($upload_path);
                            //delete old file
                            $old_path = $upload_path . $item['answer'];
                            if (file_exists($old_path)) {
                                unlink($old_path);
                            }
                            $new_path = $upload_path . '/' . $new_filename;
                            if (move_uploaded_file($_FILES[$item['id']]['tmp_name'], $new_path)) {
                                $answer = $new_filename;
                            } else {
                                die('Failed to upload file.');
                            }
                        } else {
                            $answer = $item['answer'];
                        }
                    }
                    $updateAnswerCheck =  $this->leads_model->update_inquiry_form_data(["answer" => $answer], $item['id'], true);
                }
                if ($updateAnswerCheck) {
                    $updateArr = [
                        "form_status" => 'pending',
                        "is_active" => '0',
                        "customer_form_submitted" => date('Y-m-d H:i:s')
                    ];
                    $updateAnswerCheck =  $this->leads_model->update_inquiry_form($updateArr, $formId, false, true);
                    if ($updateAnswerCheck) {
                        $this->leads_model->log_lead_activity($data['form']['lead_id'], "Leads Inquiry form submitted by customer [formID : $formId ]");
                        log_activity('Leads Inquiry form submitted by customer [formID : ' . $formId . ']');
                        $notified = add_notification([
                            'description' => 'lead_inquiry_from_submited',
                            'touserid' => $data['form']['created_by'],
                            'fromcompany' => 1,
                            'fromuserid' => null,
                            'additional_data' => serialize([
                                $data['form']['lead_id'],
                            ]),
                            'link' => '#leadid=' . $data['form']['lead_id'],
                        ]);
                        if ($notified) {
                            pusher_trigger_notification([$data['form']['created_by']]);
                        }
                    }
                }
                redirect(site_url('/forms/cif/' . $formkey));
            }
        } else {
            $this->load->view('forms/lead_customer_inquiry_form', $data);
        }
    }

    public function delete_inquiry_file()
    {
        $this->load->model('leads_model');
        if ($this->input->post()) {
            $post_data = $this->input->post();
            $formData = $this->leads_model->get_inquiry_forms_by_formkey($post_data['formkey']);
            if ($formData) {
                $getQuestion = $this->leads_model->get_inquiry_forms_question($post_data['id']);
                if ($getQuestion) {
                    $filename = $getQuestion['answer'];
                    $upload_path = 'uploads/leads/' . $formData['lead_id'] . '/';
                    $old_path = $upload_path . $filename;
                    if (file_exists($old_path)) {
                        if (unlink($old_path)) {
                            $this->leads_model->update_inquiry_form_data(["answer" => ""], $getQuestion['id'], true);
                            $this->leads_model->log_lead_activity($formData['lead_id'], "Leads Inquiry form file deleted by customer [Question ID " . $getQuestion['id'] . " and Form ID to " . $formData['id'] . "]");
                            $result['success'] = true;
                            $result['message'] = "File successfully deleted.";
                        } else {
                            $result['success'] = false;
                            $result['message'] = "File not deleted.";
                        }
                    } else {
                        $result['success'] = false;
                        $result['message'] = "File not exists.";
                    }
                } else {
                    $result['success'] = false;
                    $result['message'] = "Question not exists.";
                }
            } else {
                $result['success'] = false;
                $result['message'] = "Form not exists.";
            }
            echo json_encode($result);
        }
    }

    public function inquiry_analysis_log()
    {
        if ($this->input->post()) {
            $this->load->model('leads_model');
            $result['status'] = false;
            $data = $this->input->post();
            $formData = $this->leads_model->get_inquiry_forms_by_formkey($data['formkey']);
            if ($formData) {
                $inserArr = array(
                    "form_id" => $formData['id'],
                    "ip_address" => $data['ip_address'],
                    "browser_agent" => $data['browser_agent'],
                    "os_info" => $data['os_info'],
                    "device_type" => $data['device_type'],
                    "timestamp" => date('Y-m-d H:i:s'),
                );
                $check = $this->leads_model->insert_inquiry_form_analysis($inserArr);
                if ($check) {
                    $result['status'] = true;
                } else {
                    $result['status'] = false;
                }
            }
            echo json_encode($result);
        }
    }

    public function vqf($formkey, $action = "", $id = "")
    {
        $this->load->model('vendors_model');
        $redirectUrl = site_url('/forms/vqf/' . $formkey);
        $data['form'] = $this->vendors_model->get_quotation_forms_by_key($formkey);
        if (!$data['form']) {
            show_404();
        }
        $data['form_items_data'] = $this->vendors_model->get_quotation_forms_items_by_form_id($data['form']['id']);
        if ($action == "delete_item" && !empty($id)) {
            $delete =  $this->vendors_model->delete_quotation_form_items($id, true);
            if ($delete) {
                $result['success'] = true;
                $result['message'] = "Quotation item deleted successfully";
            } else {
                $result['success'] = false;
                $result['message'] = "Quotation item not deleted.";
            }
            redirect($redirectUrl);
        } else if ($action == "add_update_item") {
            $post_data = $this->input->post();
            if (!empty($post_data['id'])) {
                $this->vendors_model->update_quotation_form_items($post_data, $post_data['id'], true);
            } else {
                $post_data['vendor_quotation_form_id'] = $data['form']['id'];
                $this->vendors_model->create_quotation_form_items($post_data, true);
            }
            redirect($redirectUrl);
        } else if ($action == "main_form_submit") {
            $post_data = $this->input->post();
            if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
                $upload_path = 'uploads/leads/' . $data['form']['lead_id'] . '/';
                $new_filename = 'vendor_' . $data['form']['id'] . '_' . unique_filename($upload_path, $_FILES['file']['name']);
                _maybe_create_upload_path($upload_path);
                $new_path = $upload_path . '/' . $new_filename;
                if (move_uploaded_file($_FILES['file']['tmp_name'], $new_path)) {
                    $post_data['file'] = $new_filename;
                    //delete old file
                    $old_path = $upload_path . $data['form']['file'];
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
            }
            $post_data['is_active'] = 0;
            $post_data['form_status'] = 'pending';
            $post_data['vendor_form_submitted'] = date('Y-m-d H:i:s');
            $this->vendors_model->update_quotation_form($post_data, $post_data['id'], false, true);
            redirect($redirectUrl);
        } else {
            $this->load->view('forms/vendor_quotation_form', $data);
        }
    }

    public function delete_quotation_file()
    {
        $this->load->model('vendors_model');
        if ($this->input->post()) {
            $post_data = $this->input->post();
            $getFormData = $this->vendors_model->get_quotation_forms_by_id($post_data['id']);
            if ($getFormData) {
                $filename = $getFormData['file'];
                $upload_path = 'uploads/leads/' . $getFormData['lead_id'] . '/';
                $file_path = $upload_path . $filename;
                if (file_exists($file_path)) {
                    if (unlink($file_path)) {
                        $this->vendors_model->update_quotation_form(["file" => ""], $getFormData['id'], false, true);
                        $result['success'] = true;
                        $result['message'] = "File successfully deleted.";
                    } else {
                        $result['success'] = false;
                        $result['message'] = "File not deleted.";
                    }
                } else {
                    $result['success'] = false;
                    $result['message'] = "File not exists.";
                }
            } else {
                $result['success'] = false;
                $result['message'] = "File not exists.";
            }
            echo json_encode($result);
        }
    }

    //customer office visit form
    public function covf($formkey)
    {
        $this->load->model('leads_model');
        $this->load->model('leadsnew_model');
        $this->load->model('office_visitor_form_model');
        $this->load->model('plant_visit_form_model');
        $data['form'] = $this->office_visitor_form_model->get_single(array("hash" => $formkey));
        if (!$data['form']) {
            show_404();
        }
        $data['lead_data'] = $this->leads_model->get($data['form']['lead_id']);
        $data['main_group_data'] = $this->leadsnew_model->get_main_group();
        $data['sub_group_data'] = $this->leadsnew_model->get_sub_group();
        $data['member_data'] = $this->office_visitor_form_model->get_members_data($data['form']['id']);
        $data['relation_types_data'] = $this->plant_visit_form_model->get_relation_types();
        $redirectUrl = site_url('/forms/covf/' . $formkey);
        if ($this->input->post()) {
            $post_data = $this->input->post();
            //lead inquiry question data
            $lead_inquiry_data = [];
            if (!empty($post_data)) {
                foreach ($post_data as $key => $val) {
                    if (is_numeric($key)) {
                        if (!empty($val)) {
                            $lead_inquiry_data[$key] = $val;
                        }
                        unset($post_data[$key]);
                    }
                }
            }

            if (!empty($post_data['id'])) {
                $updateMainArr = array(
                    "name" => $post_data['name'],
                    "professional_field" => $post_data['professional_field'],
                    "occupation" => $post_data['occupation'],
                    "organization" => $post_data['organization'],
                    "email" => $post_data['email'],
                    "phone" => $post_data['phone'],
                    "age" => $post_data['age'],
                    "aadhar_no" => $post_data['aadhar_no'],
                    "pan_no" => $post_data['pan_no'],
                    "visit_purpose" => $post_data['visit_purpose'],
                    "special_request" => $post_data['special_request'],
                    "additional_info" => $post_data['additional_info'],
                );

                if (isset($post_data['terms_and_conditions']) && !empty($post_data['terms_and_conditions'])) {
                    $updateMainArr['terms_and_conditions'] = $this->input->post('terms_and_conditions', false);
                }

                $updateMainArr['main_group_id'] = NULL;
                $updateMainArr['sub_group_id'] = NULL;
                $updateMainArr['service_type'] = NULL;
                $updateMainArr['other_service_type'] = NULL;
                if (in_array($post_data['visit_purpose'], ["1", "2", "3"])) {
                    $updateMainArr['main_group_id'] = $post_data['main_group_id'];
                    $updateMainArr['sub_group_id'] = $post_data['sub_group_id'];
                } else if ($post_data['visit_purpose'] == "4") {
                    $updateMainArr['service_type'] = $post_data['service_type'];
                    if ($post_data['service_type'] == "9") {
                        $updateMainArr['other_service_type'] = $post_data['other_service_type'];
                    }
                }

                $customerUpdate = false;
                if (is_staff_logged_in() || is_admin()) {
                    $customerUpdate = false;
                } else {
                    $customerUpdate = true;
                    $updateMainArr['customer_submitted'] = date('Y-m-d H:i:s');
                    $updateMainArr['active'] = "0";
                }
                $updateMain = $this->office_visitor_form_model->update_form($updateMainArr, $post_data['id'], false, $customerUpdate);
                if ($updateMain) {
                    if (isset($post_data['dynamic_data']) && !empty($post_data['dynamic_data'])) {
                        $this->office_visitor_form_model->update_member_data($post_data['dynamic_data'], $post_data['id']);
                    } else {
                        $this->office_visitor_form_model->delete_member_data($post_data['id']);
                    }

                    //lead inquiry questions update
                    $updateAnswerCheck = false;
                    if (isset($post_data['lead_inquriy_form_id'])) {
                        $form_questions = $this->leads_model->get_inquiry_forms_data($post_data['lead_inquriy_form_id']);
                        if ($form_questions) {
                            foreach ($form_questions as $key => $item) {
                                $answer = (isset($lead_inquiry_data[$item['id']])) ? $lead_inquiry_data[$item['id']] : "";
                                if (!empty($answer)) {
                                    $answer = is_array($answer) ? implode(",", $answer) : $answer;
                                }
                                if ($item['type'] == "fileupload") {
                                    if (isset($_FILES[$item['id']]) && $_FILES[$item['id']]['size']) {
                                        $upload_path = 'uploads/leads/' . $data['form']['lead_id'] . '/';
                                        $new_filename = $item['form_id'] . '_' . $item['id'] . '_' . unique_filename($upload_path, $_FILES[$item['id']]['name']);
                                        _maybe_create_upload_path($upload_path);
                                        //delete old file
                                        $old_path = $upload_path . $item['answer'];
                                        if (file_exists($old_path)) {
                                            unlink($old_path);
                                        }
                                        $new_path = $upload_path . '/' . $new_filename;
                                        if (move_uploaded_file($_FILES[$item['id']]['tmp_name'], $new_path)) {
                                            $answer = $new_filename;
                                        }
                                    } else {
                                        $answer = $item['answer'];
                                    }
                                }
                                $updateAnswerCheck =  $this->leads_model->update_inquiry_form_data(["answer" => $answer], $item['id'], true);
                                if ($updateAnswerCheck) {
                                    //lead inquiry is temp = 0 update
                                    $this->leads_model->update_inquiry_form(["is_temp" => '0',], $post_data['lead_inquriy_form_id'], false, $customerUpdate);
                                }
                            }
                        }
                    } else {
                        $this->office_visitor_form_model->ovf_delete_inquiry_form(["officeFormId" => $data['form']['id']]);
                    }

                    if ($customerUpdate) {
                        //lead inquiry status update
                        if ($updateAnswerCheck) {
                            $updateArr = [
                                "form_status" => 'pending',
                                "is_active" => '0',
                                "customer_form_submitted" => date('Y-m-d H:i:s')
                            ];
                            $this->leads_model->update_inquiry_form($updateArr, $post_data['lead_inquriy_form_id'], false, $customerUpdate);
                        }

                        $notified = add_notification([
                            'description' => 'lead_office_visit_from_submited',
                            'touserid' => $data['form']['created_by'],
                            'fromcompany' => 1,
                            'fromuserid' => null,
                            'additional_data' => serialize([
                                $data['form']['lead_id'],
                            ]),
                            'link' => '#leadid=' . $data['form']['lead_id'],
                        ]);
                        if ($notified) {
                            pusher_trigger_notification([$data['form']['created_by']]);
                        }
                    }
                }
            }
            redirect($redirectUrl);
        } else {
            $this->load->view('forms/lead_customer_office_visit_form', $data);
        }
    }

    public function covf_render_lead_inquiry_form()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            $this->load->model('leads_model');
            $this->load->model('leadsnew_model');
            $this->load->model('office_visitor_form_model');
            $this->load->model('Lead_inquiry_form_images_model');

            $old_data =  $this->office_visitor_form_model->get_by_id($data['officeFormId']);
            $this->office_visitor_form_model->update_ovf_form([
                "main_group_id" => $data['mainGroupId'],
                "sub_group_id" => $data['subGroupId'],
                "visit_purpose" => $data['visit_purpose'],
            ], $data['officeFormId']);

            $getQuestions =  $this->leads_model->get_questions($data['mainGroupId'], $data['subGroupId']);
            if (!empty($getQuestions)) {
                $inquiryFormId =  $this->office_visitor_form_model->check_ovf_linked_inquiry_form($data);
                if (!empty($inquiryFormId)) {
                    if ($old_data['main_group_id'] != $data['mainGroupId'] || $old_data['sub_group_id'] != $data['subGroupId']) {
                        $this->office_visitor_form_model->ovf_delete_question_data($inquiryFormId);
                        foreach ($getQuestions as $item) {
                            $temp = array(
                                "form_id" => $inquiryFormId,
                                "order_no" => $item['order_no'],
                                "question_id" => $item['id'],
                                "is_required" => $item['is_required'],
                                "question" => $item['question'],
                                "type" => $item['type'],
                                "type_options" => $item['type_options'],
                                "answer" => NULL,
                                "created_at" => date("Y-m-d H:i:s"),
                                "created_by" => "auto-by-office-visit-form",
                            );
                            $this->leads_model->insert_inquiry_form_data($temp);
                        }
                    }
                    $form_data['form'] = $this->leads_model->get_inquiry_form_single($inquiryFormId);
                    $form_data['form_questions'] = $this->leads_model->get_inquiry_forms_data($inquiryFormId);
                    $result['html'] = $this->load->view('forms/lead_customer_office_visit_form_inquiry_form_render', $form_data, true);
                    $result['success'] = true;
                }
            } else {
                $this->office_visitor_form_model->ovf_delete_inquiry_form($data);
                $result['success'] = false;
                $result['message'] = "Questions not found";
            }
            echo json_encode($result);
        }
    }

    //plant office visit form
    public function pvf($formkey)
    {
        $this->load->model('leads_model');
        $this->load->model('leadsnew_model');
        $this->load->model('plant_visit_form_model');
        $this->load->model('taxes_model');
        $data['form'] = $this->plant_visit_form_model->get_single(array("hash" => $formkey));
        if (!$data['form']) {
            show_404();
        }
        $upload_path = 'uploads/leads/' . $data['form']['lead_id'] . '/';
        _maybe_create_upload_path($upload_path);
        $data['lead_data'] = $this->leads_model->get($data['form']['lead_id']);
        $data['member_data'] = $this->plant_visit_form_model->get_members_data($data['form']['id']);
        $data['product_type'] = $this->leadsnew_model->get_main_group();
        $data['relation_types_data'] = $this->plant_visit_form_model->get_relation_types();
        $data['visitor_types_data'] = $this->plant_visit_form_model->get_plant_visitor_types();
        $data['taxes_data'] = $this->taxes_model->get();
        if (!empty($data['member_data'])) {
            foreach ($data['member_data'] as $key => $item) {
                if (!empty($item['photo'])) {
                    $file_path = get_upload_path_by_type('lead') . $data['form']['lead_id'] . '/' . $item['photo'];
                    if (file_exists($file_path)) {
                        $protected_path = protected_file_url_by_path(get_upload_path_by_type('lead') . $data['form']['lead_id'] . '/' . $item['photo']);
                        $data['member_data'][$key]['photo_preview'] = site_url('download/file_download?path=' . $protected_path);
                    }
                }
                if (!empty($item['aadhar_card'])) {
                    $file_path = get_upload_path_by_type('lead') . $data['form']['lead_id'] . '/' . $item['aadhar_card'];
                    if (file_exists($file_path)) {
                        $protected_path = protected_file_url_by_path(get_upload_path_by_type('lead') . $data['form']['lead_id'] . '/' . $item['aadhar_card']);
                        $data['member_data'][$key]['aadhar_card_preview'] = site_url('download/file_download?path=' . $protected_path);
                    }
                }
                if (!empty($item['pan_card'])) {
                    $file_path = get_upload_path_by_type('lead') . $data['form']['lead_id'] . '/' . $item['pan_card'];
                    if (file_exists($file_path)) {
                        $protected_path = protected_file_url_by_path(get_upload_path_by_type('lead') . $data['form']['lead_id'] . '/' . $item['pan_card']);
                        $data['member_data'][$key]['pan_card_preview'] = site_url('download/file_download?path=' . $protected_path);
                    }
                }
                if (!empty($item['signature'])) {
                    $file_path = get_upload_path_by_type('lead') . $data['form']['lead_id'] . '/' . $item['signature'];
                    if (file_exists($file_path)) {
                        $protected_path = protected_file_url_by_path(get_upload_path_by_type('lead') . $data['form']['lead_id'] . '/' . $item['signature']);
                        $data['member_data'][$key]['signature_preview'] = site_url('download/file_download?path=' . $protected_path);
                    }
                }
            }
        }

        $redirectUrl = site_url('/forms/pvf/' . $formkey);
        if ($this->input->post()) {
            $post_data = $this->input->post();
            if (!empty($post_data['id'])) {
                $updateMainArr = array(
                    "name" => $post_data['name'],
                    "professional_field" => $post_data['professional_field'],
                    "occupation" => $post_data['occupation'],
                    "organization" => $post_data['organization'],
                    "email" => $post_data['email'],
                    "phone" => $post_data['phone'],
                    "age" => $post_data['age'],
                    "aadhar_no" => $post_data['aadhar_no'],
                    "pan_no" => $post_data['pan_no'],
                    "visit_purpose" => $post_data['visit_purpose'],
                    "special_request" => $post_data['special_request'],
                    "additional_info" => $post_data['additional_info'],
                    "visitor_type" => $post_data['visitor_type'],
                    "plant_visit" => $post_data['plant_visit'],
                    "tax_rate" => $post_data['tax_rate'],
                    "tax_name" => $post_data['tax_name'],
                    "tax_amount" => $post_data['tax_amount'],
                    "charge_type" => $post_data['charge_type'],
                    "visit_amount" => $post_data['visit_amount'],
                    "total_amount" => $post_data['total_amount'],
                    "total_pay_amount" => $post_data['total_pay_amount'],
                    "max_allowed_members" => $post_data['max_allowed_members'],
                    "total_members" => $post_data['total_members'],
                    "is_free_visit" => $post_data['is_free_visit'],
                    "free_visit_day" => $post_data['free_visit_day'],
                );
                if (isset($post_data['terms_and_conditions']) && !empty($post_data['terms_and_conditions'])) {
                    $updateMainArr['terms_and_conditions'] = $this->input->post('terms_and_conditions', false);
                }

                if (isset($post_data['visit_date_time']) && !empty($post_data['visit_date_time'])) {
                    $updateMainArr['visit_date_time'] = to_sql_date($post_data['visit_date_time'], true);
                }

                if (isset($post_data['digital_signature']) && !empty($post_data['digital_signature'])) {
                    process_digital_signature_image($this->input->post('digital_signature', false), $upload_path);
                    if (isset($GLOBALS['processed_digital_signature'])) {
                        $updateMainArr['digital_signature'] = $GLOBALS['processed_digital_signature'];
                        unset($GLOBALS['processed_digital_signature']);
                        unset($post_data['digital_signature']);
                    }
                }

                $dynamicFiles = [];
                if (isset($_FILES)) {
                    foreach ($_FILES as $key => $file) {
                        if ($key != "dynamic_data") {
                            if (!empty($file['name'])) {
                                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                                $new_filename = unique_filename($upload_path, 'pvf_' . $data['form']['id'] . '_' . time() . '_' . uniqid() . '.' . $ext);
                                $new_path = $upload_path . '/' . $new_filename;
                                if (move_uploaded_file($file['tmp_name'], $new_path)) {
                                    $updateMainArr[$key] = $new_filename;
                                    //delete old file
                                    $old_path = $upload_path . $data['form'][$key];
                                    if (file_exists($old_path)) {
                                        unlink($old_path);
                                    }
                                }
                            }
                        } else {
                            foreach ($file['name'] as $key => $item) {
                                foreach ($item as $key2 => $item2) {
                                    $dynamicFiles[$key][] = [
                                        'name' => $file['name'][$key][$key2],
                                        'type' => $file['type'][$key][$key2],
                                        'tmp_name' => $file['tmp_name'][$key][$key2],
                                        'error' => $file['error'][$key][$key2],
                                        'size' => $file['size'][$key][$key2],
                                    ];
                                }
                            }
                        }
                    }
                }

                if (!empty($post_data['dynamic_data'])) {
                    foreach ($post_data['dynamic_data'] as $key => $item) {
                        foreach ($dynamicFiles as $key2 => $item2) {
                            if (isset($dynamicFiles[$key2][$key])) {
                                if (!empty($dynamicFiles[$key2][$key]['name'])) {
                                    $file = $dynamicFiles[$key2][$key];
                                    _maybe_create_upload_path($upload_path);
                                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                                    $new_filename = unique_filename($upload_path, 'pvf_' . $data['form']['id'] . '_' . time() . '_' . uniqid() . '.' . $ext);
                                    $new_path = $upload_path . '/' . $new_filename;
                                    if (move_uploaded_file($file['tmp_name'], $new_path)) {
                                        $post_data['dynamic_data'][$key][$key2] = $new_filename;
                                        //delete old file
                                        $old_path = $upload_path . $data['form'][$key];
                                        if (file_exists($old_path)) {
                                            unlink($old_path);
                                        }
                                    }
                                    //$post_data['dynamic_data'][$key][$key2] = $dynamicFiles[$key2][$key]['name'];
                                }
                            }
                        }
                    }
                }

                $customerUpdate = false;
                if (is_staff_logged_in() || is_admin()) {
                    $customerUpdate = false;
                } else {
                    $customerUpdate = true;
                    $updateMainArr['customer_submitted'] = date('Y-m-d H:i:s');
                    $updateMainArr['active'] = "0";
                    $updateMainArr['approval_staus'] = "Pending";
                }

                $updateMain = $this->plant_visit_form_model->update_form($updateMainArr, $post_data['id'], false, $customerUpdate);
                if ($updateMain) {
                    if (isset($post_data['dynamic_data']) && !empty($post_data['dynamic_data'])) {
                        $this->plant_visit_form_model->update_member_data($post_data['dynamic_data'], $post_data['id']);
                    } else {
                        $this->plant_visit_form_model->delete_member_data($post_data['id']);
                    }
                    if ($customerUpdate) {
                        $notified = add_notification([
                            'description' => 'lead_plant_visit_from_submited',
                            'touserid' => $data['form']['created_by'],
                            'fromcompany' => 1,
                            'fromuserid' => null,
                            'additional_data' => serialize([
                                $data['form']['lead_id'],
                            ]),
                            'link' => '#leadid=' . $data['form']['lead_id'],
                        ]);
                        if ($notified) {
                            pusher_trigger_notification([$data['form']['created_by']]);
                        }
                    }
                }
            }
            redirect($redirectUrl);
        } else {
            $this->load->view('forms/lead_plant_visit_form', $data);
        }
    }

    public function delete_pvf_file()
    {
        $this->load->model('plant_visit_form_model');
        if ($this->input->post()) {
            $post_data = $this->input->post();
            $check = false;
            $getFormData = $this->plant_visit_form_model->get_by_id($post_data['id']);
            $upload_path = 'uploads/leads/' . $getFormData['lead_id'] . '/';
            if (isset($post_data['member_id']) && !empty($post_data['member_id'])) {
                $getFormMemberData = $this->plant_visit_form_model->get_member_data_by_id($post_data['member_id']);
                $file_path = $upload_path . $getFormMemberData[$post_data['key']];
                if ($getFormMemberData) {
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                    $customerSubmit = (!is_staff_logged_in() && !is_admin()) ? true : false;
                    $this->plant_visit_form_model->update_member_data_single([$post_data['key'] => NULL], $getFormMemberData['id']);
                    $check = true;
                }
            } else {
                if ($getFormData) {
                    $file_path = $upload_path . $getFormData[$post_data['key']];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                    $customerSubmit = (!is_staff_logged_in() && !is_admin()) ? true : false;
                    $this->plant_visit_form_model->update_form([$post_data['key'] => NULL], $getFormData['id'], false, $customerSubmit);
                    $check = true;
                }
            }
            if ($check) {
                $result['success'] = true;
                $result['message'] = "File successfully deleted.";
            } else {
                $result['success'] = false;
                $result['message'] = "File not exists.";
            }
            echo json_encode($result);
        }
    }
}
