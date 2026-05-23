<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contract extends ClientsController
{
    public function index($id, $hash)
    {
        set_time_limit(0);
        check_contract_restrictions($id, $hash);
        $contract = $this->contracts_model->get($id);
        $success = $this->input->get('success', TRUE);

        if (!$contract) {
            show_404();
        }

        if ($success) {
            return $this->load->view('admin/contracts/link_expired_page', ['success' => true]);
        }

        if (!is_client_logged_in()) {
            load_client_language($contract->client);
        }

        if ($this->input->post()) {
            $action = $this->input->post('action');

            switch ($action) {
                case 'contract_pdf':
                    $pdf = contract_mpdf($contract);
                    $pdf->Output(slug_it($contract->subject . '-' . get_option('companyname')) . '.pdf', 'D');
                    break;
                case 'sign_contract':
                    $data = $this->input->post();
                    if (isset($data['confirm'])) {
                        unset($data['confirm']);
                    }
                    if (isset($data['acceptance_email']) && !empty($data['acceptance_email'])) {
                        $checkContact = $this->contracts_model->check_email($id, $data['acceptance_email']);
                        if (!empty($checkContact)) {
                            if ($checkContact['signed'] != '1') {
                                //Digital Signature
                                $data['digital_signature'] = null;
                                process_digital_signature_image($this->input->post('signature', false), CONTRACTS_UPLOADS_FOLDER . $id);
                                if (isset($GLOBALS['processed_digital_signature'])) {
                                    $data['digital_signature'] = $GLOBALS['processed_digital_signature'];
                                    unset($GLOBALS['processed_digital_signature']);
                                    unset($data['signature']);
                                }

                                //selfie
                                $data['acceptance_selfie'] = null;
                                process_selfie_image($this->input->post('selfie', false), CONTRACTS_UPLOADS_FOLDER . $id);
                                if (isset($GLOBALS['processed_selfie_image'])) {
                                    $data['acceptance_selfie'] = $GLOBALS['processed_selfie_image'];
                                    unset($GLOBALS['processed_selfie_image']);
                                    unset($data['selfie']);
                                }

                                //Physical Signature
                                if (isset($_FILES['physical_signature']) && $_FILES['physical_signature']['size']) {
                                    $upload_path = 'uploads/contracts/' . $id . '/';
                                    _maybe_create_upload_path($upload_path);
                                    $extension = pathinfo($_FILES['physical_signature']['name'], PATHINFO_EXTENSION);
                                    $new_filename = unique_filename($upload_path, 'physical_signature.' . $extension);
                                    $new_path = $upload_path . '/' . $new_filename;
                                    if (move_uploaded_file($_FILES['physical_signature']['tmp_name'], $new_path)) {
                                        $data['physical_signature'] = $new_filename;
                                    }
                                }

                                //Default Signature
                                $data['default_signature'] = null;
                                if (!empty($data['physical_signature']) || !empty($data['digital_signature'])) {
                                    $data['default_signature'] = (!empty($data['physical_signature'])) ? 'physical' : 'digital';
                                }

                                $insertArr = $data;
                                $insertArr['acceptance_date'] = date('Y-m-d H:i:s');
                                $insertArr['acceptance_ip'] = $this->input->ip_address();
                                if ($contract->rel_type == "customer") {
                                    $insertArr['contact_id'] = $checkContact['id'];
                                } else if ($contract->rel_type == "vendor") {
                                    $insertArr['vendor_id'] = $checkContact['id'];
                                } else if ($contract->rel_type == "contact_book") {
                                    $insertArr['contact_book_id'] = $checkContact['id'];
                                }
                                $insertArr['contract_id'] = $id;
                                $insertArr['signed'] = '1';
                                unset($insertArr['action']);

                                $signData = $this->contracts_model->insert_sign_data($insertArr);
                                if ($signData) {
                                    $contractData = $this->contracts_model->get_single_contract($id);
                                    if ($contractData->rel_type == "customer") {
                                        $contactData = $this->clients_model->get_contact($checkContact['id']);
                                        mail_template('contract_signed_to_partner', $contractData, $contactData)->send();
                                    } else if ($contractData->rel_type == "vendor") {
                                        $this->load->model('leads_model');
                                        $contactData = $this->leads_model->get($checkContact['id']);
                                        mail_template('contract_signed_to_vendor', $contractData, $contactData)->send();
                                    } else if ($contractData->rel_type == "contact_book") {
                                        $this->load->model('contact_book_model');
                                        $contactData = (object)$this->contact_book_model->get($checkContact['id']);
                                        mail_template('contract_signed_to_contact_book', $contractData, $contactData)->send();
                                    }
                                    $allSigned = $this->contracts_model->check_and_update_signed_status($id);
                                    if ($allSigned) {
                                        send_contract_signed_notification_to_staff($id);
                                    }
                                    $this->session->unset_userdata('contract_' . $id . '_customer_authenticated');
                                    $this->session->unset_userdata('contract_' . $id . '_customer_auth_email');
                                    $this->session->unset_userdata('contract_' . $id . '_user_type');
                                    $this->session->unset_userdata('contract_' . $id . '_user_id');
                                    set_alert('success', _l('document_signed_successfully'));
                                    redirect(site_url("contract/$id/$hash?success=true"));
                                } else {
                                    set_alert('danger', "Error : Something went wrong. contract not signed.");
                                    redirect($_SERVER['HTTP_REFERER']);
                                }
                            } else {
                                set_alert('danger', "This contract is already signed by you.");
                                redirect($_SERVER['HTTP_REFERER']);
                            }
                        } else {
                            set_alert('danger', "Entered Email is unauthorized to sign this contract.");
                            redirect($_SERVER['HTTP_REFERER']);
                        }
                    } else {
                        set_alert('danger', "Please enter email address.");
                        redirect($_SERVER['HTTP_REFERER']);
                    }
                    break;
                case 'contract_comment':
                    // comment is blank
                    if (!$this->input->post('content')) {
                        redirect($this->uri->uri_string());
                    }
                    $data = $this->input->post();
                    $data['rel_type'] = $this->session->userdata('contract_' . $id . '_user_type');
                    $data['staffid'] = $this->session->userdata('contract_' . $id . '_user_id');
                    $data['contract_id'] = $id;
                    $this->contracts_model->add_comment($data, true);
                    redirect($this->uri->uri_string() . '?tab=discussion');
                    break;
            }
        }

        $this->disableNavigation();
        $this->disableSubMenu();
        $data['title'] = $contract->subject;
        $data['contract']  = hooks()->apply_filters('contract_html_pdf_data', $contract);
        $data['contract_contacts']  = $this->contracts_model->get_contract_contacts($data['contract']->id, $data['contract']->rel_type);
        $data['bodyclass'] = 'contract contract-view';
        $data['identity_confirmation_enabled'] = true;
        $data['bodyclass'] .= ' identity-confirmation';
        $this->app_scripts->theme('sticky-js', 'assets/plugins/sticky/sticky.js');
        $data['comments'] = $this->contracts_model->get_comments($id);
        hooks()->do_action('contract_html_viewed', $id);
        $this->app_css->remove('reset-css', 'customers-area-default');
        $data = hooks()->apply_filters('contract_customers_area_view_data', $data);
        if (!is_admin() &&  !is_staff_logged_in()) {
            if (!empty($data['contract']->open_till)) {
                if (strtotime($data['contract']->open_till) <= time()) {
                    $this->data($data);
                    $this->load->view('admin/contracts/link_expired_page', $data);
                } else {
                    if ($this->session->userdata('contract_' . $data['contract']->id . '_customer_authenticated')) {
                        $data['loggedin_contact'] = $this->contracts_model->check_email($data['contract']->id, $this->session->userdata('contract_' . $data['contract']->id . '_customer_auth_email'));
                        if ($data['contract']->rel_type == "vendor") {
                            $name = explode(" ", $data['loggedin_contact']['name']);
                            $data['loggedin_contact']['firstname'] = (isset($name[0])) ? $name[0] : "";
                            $data['loggedin_contact']['lastname'] = (isset($name[1])) ? $name[1] : "";
                        }
                        $pdf = contract_mpdf($contract);
                        $tempPdfPath = 'uploads/temp_mail_attachments/contract_temp_' . time() . '_' . uniqid() . '.pdf';
                        $pdf->Output($tempPdfPath, \Mpdf\Output\Destination::FILE);
                        $data['tmp_pdf_url'] = site_url($tempPdfPath);
                        $this->data($data);
                        no_index_customers_area();
                        $this->view('contracthtml');
                        $this->pdf_page_layout();
                    } else {
                        $this->data($data);
                        $this->view('contract_customer_login');
                        $this->pdf_page_layout();
                    }
                }
            } else {
                $this->data($data);
                $this->load->view('admin/contracts/link_expired_page', $data);
            }
        } else if (is_staff_logged_in() || is_admin()) {
            $pdf = contract_mpdf($contract);
            $tempPdfPath = 'uploads/temp_mail_attachments/contract_temp_' . time() . '_' . uniqid() . '.pdf';
            $pdf->Output($tempPdfPath, \Mpdf\Output\Destination::FILE);
            $data['tmp_pdf_url'] = site_url($tempPdfPath);
            $this->data($data);
            no_index_customers_area();
            $this->view('contracthtml');
            $this->pdf_page_layout();
        }
    }

    public function otp_verification()
    {
        $data = $this->input->post();
        if (isset($data['email']) && !empty($data['email'])) {
            $getContractData = $this->contracts_model->get($data['contract_id']);
            $checkContact = $this->contracts_model->check_email($data['contract_id'], $data['email']);
            if (!empty($checkContact)) {
                $otp_slug = "contract_" . $data['contract_id'] . "_" . $checkContact['id'] . "_";
                if ($checkContact['signed'] != '1') {
                    if ($data['type'] == "send-otp") {
                        $otp = rand(100000, 999999);
                        $this->session->set_userdata($otp_slug . 'otp', $otp);
                        $this->session->set_userdata($otp_slug . 'otp_timestamp', time());
                        $contract = $this->contracts_model->get_single_contract($data['contract_id']);
                        if ($getContractData->rel_type == "customer") {
                            $contact = $this->clients_model->get_contact($checkContact['id']);
                        } else if ($getContractData->rel_type == "vendor") {
                            $this->load->model('leads_model');
                            $contact = $this->leads_model->get($getContractData->client);
                        } else if ($getContractData->rel_type == "contact_book") {
                            $this->load->model('contact_book_model');
                            $contact = (object)$this->contact_book_model->get($getContractData->client);
                        }
              

                        $emailSend = false;
                        if ($data['mode'] == "contract_authenticate") {
                            $emailSend = mail_template('contract_auth_otp_send', $contract, $contact, $otp)->send();
                         
                        } else {
                            $emailSend = mail_template('contract_signed_otp_send', $contract, $contact, $otp)->send();
                            
                        }
                        if ($emailSend) {
                            echo json_encode(['success' => true, 'message' => 'OTP successfully send to your email.']);
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Error : Email not send']);
                        }
                    } else if ($data['type'] == "verify-otp") {
                        if (isset($data['otp']) && !empty($data['otp'])) {
                            $session_otp = $this->session->userdata($otp_slug . 'otp');
                            $otp_timestamp = $this->session->userdata($otp_slug . 'otp_timestamp');
                            $current_time = time();
                            if (($current_time - $otp_timestamp) > 300) {
                                echo json_encode(['success' => false, 'message' => 'OTP has expired. please click on resend to get new OTP.']);
                            } else if ($data['otp'] == $session_otp) {
                                if ($data['mode'] == "contract_authenticate") {
                                    $this->session->set_userdata('contract_' . $data['contract_id'] . '_customer_authenticated', true);
                                    $this->session->set_userdata('contract_' . $data['contract_id'] . '_customer_auth_email', $data['email']);
                                    $this->session->set_userdata('contract_' . $data['contract_id'] . '_user_type', $getContractData->rel_type);
                                    $this->session->set_userdata('contract_' . $data['contract_id'] . '_user_id', $checkContact['id']);
                                }
                                echo json_encode(['success' => true, 'message' => 'Email successfully verified.']);
                            } else {
                                echo json_encode(['success' => false, 'message' => 'Please enter correct OTP.']);
                            }
                        } else {
                            echo json_encode(['success' => false, 'message' => 'Please enter OTP code.']);
                        }
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error : Something went wrong.']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'You already signed the contract. The contract is currently under review. <br> Once the verification is completed, you will receive a confirmation email <br> on your registered email ID.<br>
']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Sorry ! Unauthorized email. Please enter valid email.']);
            }
        }
    }

    public function session_logout()
    {
        $data = $this->input->post();
        $this->session->unset_userdata('contract_' . $data['contract_id'] . '_customer_authenticated');
        $this->session->unset_userdata('contract_' . $data['contract_id'] . '_customer_auth_email');
        $this->session->unset_userdata('contract_' . $data['contract_id'] . '_user_type');
        $this->session->unset_userdata('contract_' . $data['contract_id'] . '_user_id');
        echo json_encode(['success' => true, 'message' => 'Logout successfully.']);
    }
}
