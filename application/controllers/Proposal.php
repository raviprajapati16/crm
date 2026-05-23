<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Proposal extends ClientsController
{
    public function index($id, $hash)
    {
        set_time_limit(0);
        check_proposal_restrictions($id, $hash);
        $proposal = $this->proposals_model->get($id);

        if ($proposal->rel_type == 'customer' && !is_client_logged_in()) {
            load_client_language($proposal->rel_id);
        } else if ($proposal->rel_type == 'lead') {
            load_lead_language($proposal->rel_id);
        }

        $identity_confirmation_enabled = get_option('proposal_accept_identity_confirmation');
        if ($this->input->post()) {
            $action = $this->input->post('action');
            switch ($action) {
                case 'proposal_pdf':
                    set_time_limit(0);
                    $proposal_number = format_proposal_number($id);
                    $companyname     = get_option('invoice_company_name');
                    if ($companyname != '') {
                        $proposal_number .= '-' . mb_strtoupper(slug_it($companyname), 'UTF-8');
                    }

                    try {
                        $pdf = proposal_mpdf($proposal);
                    } catch (Exception $e) {
                        echo $e->getMessage();
                        die;
                    }

                    $pdf->Output($proposal_number . '.pdf', 'D');

                    break;
                case 'proposal_comment':
                    // comment is blank
                    if (!$this->input->post('content')) {
                        redirect($this->uri->uri_string());
                    }
                    $data               = $this->input->post();
                    $data['proposalid'] = $id;
                    $this->proposals_model->add_comment($data, true);
                    redirect($this->uri->uri_string() . '?tab=discussion');

                    break;
                case 'accept_proposal':
                    $success = $this->proposals_model->mark_action_status(3, $id, true);
                    if ($success) {
                        process_digital_signature_image($this->input->post('signature', false), PROPOSAL_ATTACHMENTS_FOLDER . $id);

                        $this->db->where('id', $id);
                        $this->db->update(db_prefix() . 'proposals', get_acceptance_info_array());
                        redirect($this->uri->uri_string(), 'refresh');
                    }

                    break;
                case 'decline_proposal':
                    $success = $this->proposals_model->mark_action_status(2, $id, true);
                    if ($success) {
                        redirect($this->uri->uri_string(), 'refresh');
                    }

                    break;
            }
        }

        $number_word_lang_rel_id = 'unknown';
        if ($proposal->rel_type == 'customer') {
            $number_word_lang_rel_id = $proposal->rel_id;
        }
        $this->load->library('app_number_to_word', [
            'clientid' => $number_word_lang_rel_id,
        ], 'numberword');

        $this->disableNavigation();
        $this->disableSubMenu();

        $data['title']     = $proposal->subject;
        $data['proposal']  = hooks()->apply_filters('proposal_html_pdf_data', $proposal);
        $data['bodyclass'] = 'proposal proposal-view';

        $data['identity_confirmation_enabled'] = $identity_confirmation_enabled;
        if ($identity_confirmation_enabled == '1') {
            $data['bodyclass'] .= ' identity-confirmation';
        }

        $this->app_scripts->theme('sticky-js', 'assets/plugins/sticky/sticky.js');

        $data['comments'] = $this->proposals_model->get_comments($id);

        $this->app_css->remove('reset-css', 'customers-area-default');
        $data                      = hooks()->apply_filters('proposal_customers_area_view_data', $data);
        no_index_customers_area();
        $this->load->model('Lead_inquiry_form_images_model');
        $data['background_slider_image'] = $this->Lead_inquiry_form_images_model->get_background_slider_images();

        if (!is_client_logged_in() &&  !is_staff_logged_in()) {
            if (!empty($data['proposal']->open_till)) {
                if (date('Y-m-d', strtotime($data['proposal']->open_till)) < date('Y-m-d')) {
                    return $this->load->view('admin/proposals/link_expired_page', $data);
                }
                add_views_tracking('proposal', $id);
                hooks()->do_action('proposal_html_viewed', $id);
            } else {
                return $this->load->view('admin/proposals/link_expired_page', $data);
            }
        }
        $pdf = proposal_mpdf($proposal);
        $tempPdfPath = 'uploads/temp_mail_attachments/proposal_temp_' . time() . '_' . uniqid() . '.pdf';
        $pdf->Output($tempPdfPath, \Mpdf\Output\Destination::FILE);
        $data['tmp_pdf_url'] = site_url($tempPdfPath);
        $this->data($data);
        $this->view('viewproposal');
        $this->pdf_page_layout();
    }

    public function download_request()
    {
        $CI = &get_instance();
        $data = $this->input->post();
        if (!empty($data['id'])) {
            $check =  $this->proposals_model->update_proposal(["download_request" => "2"], $data['id']);
            if ($check) {
                $notifiedUsers     = [];
                $members           = [];
                $notification_data = [];
                array_push($notification_data, format_proposal_number($data['id']));

                $notification_data = serialize($notification_data);
                $CI->db->select('addedfrom, assigned')
                    ->where('id', $data['id']);
                $rel = $CI->db->get(db_prefix() . 'proposals')->row();

                if (!empty($rel)) {
                    $CI->db->select('staffid')
                        ->where('staffid', $rel->addedfrom)
                        ->or_where('staffid', $rel->assigned);
                    $members = $CI->db->get(db_prefix() . 'staff')->result_array();
                    if (!empty($members)) {
                        foreach ($members as $member) {
                            $notification = [
                                'fromcompany'     => true,
                                'touserid'        => $member['staffid'],
                                'description'     => 'not_customer_requested_proposal_download',
                                'link'            => 'proposals/list_proposals/' . $data['id'],
                                'additional_data' => $notification_data,
                            ];
                            if (is_client_logged_in()) {
                                unset($notification['fromcompany']);
                            }
                            $notified = add_notification($notification);
                            if ($notified) {
                                array_push($notifiedUsers, $member['staffid']);
                            }
                        }
                    }
                    pusher_trigger_notification($notifiedUsers);
                }
                $result['success'] = true;
                $result['message'] = "Request has been sent for download.";
            } else {
                $result['success'] = false;
                $result['message'] = "Something went wrong.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Something went wrong.";
        }
        echo json_encode($result);
    }
}
