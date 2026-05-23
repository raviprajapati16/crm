<?php
class Vendors extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('vendors_model');
        $this->load->model('leads_model');
    }

    public function index($id = '')
    {
        if (!has_permission('vendors', '', 'view') && !has_permission('vendors', '', 'view_own')) {
            access_denied('Vendors');
        }
        $data['module_type'] = "vendors";
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $data['id'] = get_staff_user_id();
        if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
            $this->load->model('gdpr_model');
            $data['consent_purposes'] = $this->gdpr_model->get_consent_purposes();
        }
        $data['summary'] = get_leads_summary();
        $data['statuses'] = $this->leads_model->get_status();
        $data['sources'] = $this->leads_model->get_source();
        $data['lead_countries'] = $this->leads_model->get_lead_countries();
        $data['lead_states'] = $this->leads_model->get_lead_states();
        $data['lead_cities'] = $this->leads_model->get_lead_cities();
        $data['products'] = get_tags_unique();
        $data['title'] = _l('vendors');
        $data['leadid'] = $id;
        $this->load->view('admin/vendors/manage_vendors', $data);
    }

    public function get_vendors()
    {
        $post_data = $this->input->post();
        $leads = $this->vendors_model->get_vendors($post_data);
        $totalLeadsCount = $this->vendors_model->get_vendors($post_data, true);

        usort($leads, function ($a, $b) {
            return $b->lapselead <=> $a->lapselead;
        });

        foreach ($leads as &$lead) {
            if ($lead->assigned != 0) {
                $id = $lead->assigned;
                $full_name = $lead->assigned_firstname . ' ' . $lead->assigned_lastname;
                $lead->assigned_output = '<a data-toggle="tooltip" data-title="' . $full_name . '" href="' . admin_url('profile/' . $lead->assigned) . '">' . staff_profile_image($lead->assigned, ['staff-profile-image-small']) . '</a>';
                $lead->assigned_output .= '<span class="hide">' . $full_name . '</span>';
                $lead->assigned_output .= '<span class="hide">' . $id . '</span>';
            } else {
                $lead->assigned_output = '';
            }

            if ($lead->short_name) {
                $lead->country_name = $lead->short_name;
            } else {
                $lead->country_name = '';
            }
            if ($lead->dateadded) {
                $lead->dateadded = '<span data-toggle="tooltip" data-title="' . _dt($lead->dateadded) . '" class="text-has-action is-date">' . time_ago($lead->dateadded) . '</span>';
            } else {
                $lead->dateadded = '';
            }
            if ($lead->lastcontact) {
                $lead->lastcontact  = '<span data-toggle="tooltip" data-title="' . _dt($lead->lastcontact) . '" class="text-has-action is-date">' . time_ago($lead->lastcontact) . '</span>';
            } else {
                $lead->lastcontact = '';
            }

            $locked = false;
            $lockAfterConvert = get_option('lead_lock_after_convert_to_customer');

            if ($lead->is_converted > 0) {
                $locked = ((!is_admin() && $lockAfterConvert == 1) ? true : false);
            }

            // Pass the locked status to the view
            $lead->edit_locked = $locked;
        }
        $response = array(
            "draw" => $post_data['draw'],
            "recordsTotal" => $totalLeadsCount,
            "recordsFiltered" => $totalLeadsCount,
            "data" => $leads
        );
        header('Content-Type: application/json');
        echo json_encode($response);
    }


    public function delete($id)
    {
        if (!has_permission('vendors', '', 'delete')) {
            ajax_access_denied('Delete vendor');
        }

        $response = $this->leads_model->delete($id);
        if ($response) {
            $this->leads_model->log_lead_activity($id, " Deleted Vendor");
            $result['success'] = true;
            $result['message'] =  "Vendor successfully deleted.";
        } else {
            $result['success'] = false;
            $result['message'] = "Vendor not deleted.";
        }
        echo json_encode($result);
    }

    public function restore($id)
    {
        if (!has_permission('vendors', '', 'delete')) {
            ajax_access_denied('Restore vendor');
        }
        $response = $this->leads_model->restore($id);
        if ($response) {
            $this->leads_model->log_lead_activity($id, " Restored Vendor");
            $result['success'] = true;
            $result['message'] =  "Vendor successfully restored.";
        } else {
            $result['success'] = false;
            $result['message'] = "Vendor not restore.";
        }
        echo json_encode($result);
    }

    public function save_quotation_form()
    {
        if (!has_permission('vendors', '', 'view') && !has_permission('vendors', '', 'view_own')) {
            access_denied('Vendors');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            if (isset($data['vendor_quoation_forms_id']) && !empty($data['vendor_quoation_forms_id'])) {
                $id = $data['vendor_quoation_forms_id'];
                unset($data['vendor_quoation_forms_id']);
                if (!empty($data['quotation_date'])) {
                    $data['quotation_date'] = date('Y-m-d', strtotime($data['quotation_date']));
                } else {
                    $data['quotation_date'] = NULL;
                }
                $getFormData = $this->vendors_model->get_quotation_forms_by_id($id);
                if ($getFormData) {
                    if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
                        $upload_path = 'uploads/leads/' . $getFormData['lead_id'] . '/';
                        $new_filename = 'vendor_' . $getFormData['id'] . '_' . unique_filename($upload_path, $_FILES['file']['name']);
                        _maybe_create_upload_path($upload_path);
                        $new_path = $upload_path . '/' . $new_filename;
                        if (move_uploaded_file($_FILES['file']['tmp_name'], $new_path)) {
                            $data['file'] = $new_filename;
                            //delete old file
                            $old_path = $upload_path . $getFormData['file'];
                            if (file_exists($old_path)) {
                                unlink($old_path);
                            }
                        }
                    }
                    $updateCheck = $this->vendors_model->update_quotation_form($data, $id);
                    if ($updateCheck) {
                        $this->leads_model->log_lead_activity($getFormData['lead_id'], "Vendor quotation form updated. Form ID : " . $getFormData['id']);
                        $result['success'] = true;
                        $result['id'] = $id;
                        $result['message'] = "Quotation form successfully updated.";
                    } else {
                        $result['success'] = false;
                        $result['message'] = "Quotation form not updated.";
                    }
                } else {
                    $result['success'] = false;
                    $result['message'] = "Quotation form not updated.";
                }
            } else {
                $data['formkey'] =  generateUniqueString();
                $data['quotation_date'] = date('Y-m-d');
                $getId = $this->vendors_model->create_quotation_form($data);
                if ($getId) {
                    $this->leads_model->log_lead_activity($data['lead_id'], "New Vendor quotation form created. Form ID : " . $getId);
                    $result['success'] = true;
                    $result['id'] = $getId;
                    $result['message'] = "Quotation form successfully created.";
                } else {
                    $result['success'] = false;
                    $result['message'] = "Quotation form not created.";
                }
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Something went wrong.";
        }
        echo json_encode($result);
    }

    public function save_quotation_form_items()
    {
        if (!has_permission('vendors', '', 'view') && !has_permission('vendors', '', 'view_own')) {
            access_denied('Vendors');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['id'] == "") {
                $getId = $this->vendors_model->create_quotation_form_items($data);
                if ($getId) {
                    $getFormData = $this->vendors_model->get_quotation_forms_by_id($data['vendor_quotation_form_id']);
                    $this->leads_model->log_lead_activity($getFormData['lead_id'], "Vendor quotation form item created. Form ID : " . $getFormData['id'] . " Item ID " . $getId);
                    $result['success'] = true;
                    $result['data'] = $this->vendors_model->get_quotation_forms_items_by_form_id($data['vendor_quotation_form_id']);
                    $result['message'] = "Quotation Item successfully created.";
                } else {
                    $result['success'] = false;
                    $result['message'] = "Quotation item not created...";
                }
            } else {
                $getId = $this->vendors_model->update_quotation_form_items($data, $data['id']);
                if ($getId) {
                    $getFormData = $this->vendors_model->get_quotation_forms_by_id($data['vendor_quotation_form_id']);
                    $this->leads_model->log_lead_activity($getFormData['lead_id'], "Vendor quotation form item updated. Form ID : " . $getFormData['id'] . " Item ID " . $data['id']);
                    $result['success'] = true;
                    $result['data'] = $this->vendors_model->get_quotation_forms_items_by_form_id($data['vendor_quotation_form_id']);
                    $result['message'] = "Quotation Item successfully updated.";
                } else {
                    $result['success'] = false;
                    $result['message'] = "Quotation item not updated...";
                }
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Something went wrong.";
        }
        echo json_encode($result);
    }

    public function get_vendor_quotation_form_list()
    {
        if (!has_permission('vendors', '', 'view') && !has_permission('vendors', '', 'view_own')) {
            ajax_access_denied();
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $getForms =  $this->vendors_model->get_quotation_forms_by_lead_id($data['lead_id']);
            $getData['leadData'] = $this->leads_model->get($data['lead_id']);
            $getData['countryData'] = get_country($getData['lead_id']->country);
            if ($getForms) {
                $html = "";
                $formcount = 1;
                foreach ($getForms as $key => $form) {
                    $renderArr = array();
                    $renderArr = $getData;
                    $renderArr['formCount'] = $formcount;
                    $renderArr['form_data'] = $form;
                    $renderArr['form_items_data'] = $this->vendors_model->get_quotation_forms_items_by_form_id($form['id']);
                    $html .= $this->load->view('admin/vendors/vendor-quotation-form-render', $renderArr, true);
                    $formcount++;
                }
                $result['success'] = true;
                $result['html'] = $html;
            } else {
                $result['success'] = false;
                $result['message'] = "quotation forms not available";
            }
            echo json_encode($result);
        }
    }

    public function vendor_quotation_form_status_change()
    {
        if (!has_permission('vendors', '', 'view') && !has_permission('vendors', '', 'view_own')) {
            ajax_access_denied();
        }
        $data = $this->input->post();
        if (isset($data['id']) && isset($data['status'])) {
            $getFormData = $this->vendors_model->get_quotation_forms_by_id($data['id']);
            $updateData =  $this->vendors_model->update_quotation_form(["is_active" => $data['status']], $data['id']);
            $status = ($data['status'] == "1") ? "Active " : " In-Active";
            if ($updateData) {
                $this->leads_model->log_lead_activity($getFormData['lead_id'], "Vendor quotation form status changed to " . $status . ". Form ID : " . $getFormData['id']);
                $result['success'] = true;
                $result['message'] = "Quotation Form $status successfully";
            } else {
                $result['success'] = false;
                $result['message'] = "Quotation Form $status failed.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid Request";
        }
        echo json_encode($result);
    }

    public function delete_quotation_form()
    {
        if (!has_permission('vendors', '', 'delete')) {
            ajax_access_denied();
        }
        $data = $this->input->post();
        if (isset($data['id'])) {
            $getFormData = $this->vendors_model->get_quotation_forms_by_id($data['id']);
            $deleteForm =  $this->vendors_model->update_quotation_form([], $data['id'], true);
            if ($deleteForm) {
                $this->leads_model->log_lead_activity($getFormData['lead_id'], "Vendor quotation form deleted. Form ID : " . $getFormData['id']);
                $result['success'] = true;
                $result['message'] = "Quotation Form deleted successfully";
            } else {
                $result['success'] = false;
                $result['message'] = "Quotation Form not deleted.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Formid required";
        }
        echo json_encode($result);
    }

    public function delete_quotation_item()
    {
        if (!has_permission('vendors', '', 'delete')) {
            ajax_access_denied();
        }
        $data = $this->input->post();
        if (isset($data['id'])) {
            $getFormItemData = $this->vendors_model->get_quotation_forms_items_by_id($data['id']);
            $getFormData = $this->vendors_model->get_quotation_forms_by_id($getFormItemData['vendor_quotation_form_id']);
            $deleteForm =  $this->vendors_model->delete_quotation_form_items($data['id']);
            if ($deleteForm) {
                $this->leads_model->log_lead_activity($getFormData['lead_id'], "Vendor quotation item deleted. Form ID : " . $getFormItemData['vendor_quotation_form_id'] . " Item ID : " . $getFormItemData['id']);
                $result['success'] = true;
                $result['message'] = "Quotation item deleted successfully";
            } else {
                $result['success'] = false;
                $result['message'] = "Quotation item not deleted.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request";
        }
        echo json_encode($result);
    }

    public function delete_quotation_file()
    {
        if (!has_permission('vendors', '', 'delete')) {
            ajax_access_denied();
        }
        if ($this->input->post()) {
            $post_data = $this->input->post();
            $getFormData = $this->vendors_model->get_quotation_forms_by_id($post_data['id']);
            if ($getFormData) {
                $filename = $getFormData['file'];
                $upload_path = 'uploads/leads/' . $getFormData['lead_id'] . '/';
                $file_path = $upload_path . $filename;
                if (file_exists($file_path)) {
                    if (unlink($file_path)) {
                        $this->vendors_model->update_quotation_form(["file" => ""], $getFormData['id']);
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

    public function send_quotation_form()
    {
        if (!has_permission('vendors', '', 'view') && !has_permission('vendors', '', 'view_own')) {
            ajax_access_denied();
        }
        $data = $this->input->post();
        if (isset($data['lead_id']) && isset($data['form_id']) && isset($data['type'])) {
            $getForm =  $this->vendors_model->get_quotation_forms_by_id($data['form_id']);
            if ($getForm) {
                if ($data['type'] == "email") {
                    $vendor = $this->leads_model->get($data['lead_id']);
                    $vendor->vendor_name = $vendor->name;
                    $vendor->quotation_form_link = site_url('forms/vqf/') . $getForm["formkey"];
                    $checkEmail = send_lead_template_email_from_webmail('vendor_quotation_form_send', $vendor);
                    $checkUpdate = $this->vendors_model->update_quotation_form(["is_email_send" => "1", "email_send_at" => date('Y-m-d H:i:s')], $getForm['id']);
                    if ($checkEmail && $checkUpdate) {
                        leadLastContactAtUpdate($data['lead_id']);
                        $this->leads_model->log_lead_activity($data['lead_id'], "Vendor quotation form send via Email. Form ID : " . $data['form_id']);
                        $result['success'] = true;
                        $result['message'] = "Email send successfully";
                    } else {
                        $result['success'] = false;
                        $result['message'] = "Email not send.";
                    }
                } else { // Whatsapp send
                    $checkUpdate = $this->vendors_model->update_quotation_form(["is_whatsapp_send" => "1", "whatsapp_send_at" => date('Y-m-d H:i:s')], $getForm['id']);
                    if ($checkUpdate) {
                        leadLastContactAtUpdate($data['lead_id']);
                        $this->leads_model->log_lead_activity($data['lead_id'], "Vendor quotation form share via Whatsapp. Form ID : " . $data['form_id']);
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

    public function quotation_approval_status_change()
    {
        if (!has_permission('vendors', '', 'view') && !has_permission('vendors', '', 'view_own')) {
            ajax_access_denied();
        }
        if ($this->input->post()) {
            $post_data = $this->input->post();
            if (isset($post_data['form_status']) && isset($post_data['form_id'])) {
                if ($post_data['form_status'] == "approved") {
                    $post_data['reject_note'] = "";
                }
                $updateArr = [
                    "form_status" => $post_data['form_status'],
                    "reject_note" => $post_data['reject_note'],
                    "status_changed_by" => get_staff_user_id(),
                    "status_updated_at" => date('Y-m-d H:i:s')
                ];
                if ($post_data['form_status'] == "not-approved") {
                    $updateArr['is_active'] = '1';
                    $updateArr['vendor_form_submitted'] = NULL;
                }
                $checkUpdate = $this->vendors_model->update_quotation_form($updateArr, $post_data['form_id']);
                if ($checkUpdate) {
                    $getForm =  $this->vendors_model->get_quotation_forms_by_id($post_data['form_id']);
                    $this->leads_model->log_lead_activity($getForm['lead_id'], "Vendor Quotation form status changed to " . strtoupper($getForm['form_status']) . " Form ID : " . $post_data['form_id']);
                    $result['success'] = true;
                    $result['message'] = "Quotation status successfully changed to " . strtoupper($post_data['form_status']);
                } else {
                    $result['success'] = false;
                    $result['message'] = "Quotation Form not updated.";
                }
            } else {
                $result['success'] = false;
                $result['message'] = "Invalid Request.";
            }
            echo json_encode($result);
        }
    }

    public function send_quotation_form_approve_not_approved_notify()
    {
        if (!has_permission('vendors', '', 'view') && !has_permission('vendors', '', 'view_own')) {
            ajax_access_denied();
        }
        $data = $this->input->post();
        if (isset($data['formId']) && isset($data['type'])) {
            $getForm =  $this->vendors_model->get_quotation_forms_by_id($data['formId']);
            if ($getForm) {
                if ($data['type'] == "email") {
                    $vendor = $this->leads_model->get($getForm['lead_id']);
                    $vendor->quotation_form_link = site_url('forms/vqf/') . $getForm["formkey"];
                    $vendor->reject_note = $getForm['reject_note'];
                    $checkEmail = send_lead_template_email_from_webmail(($getForm['form_status'] == "approved") ? "vendor_quotation_form_approved" : "vendor_quotation_form_not_approved", $vendor);
                    if ($checkEmail) {
                        leadLastContactAtUpdate($getForm['lead_id']);
                        $this->leads_model->log_lead_activity($getForm['lead_id'], "Vendor Quotation form " . strtoupper($getForm['form_status']) . " email has been send. Form ID : " . $data['formId']);
                        $result['success'] = true;
                        $result['message'] = "Email send successfully";
                    } else {
                        $result['success'] = false;
                        $result['message'] = "Email not send.";
                    }
                } else {
                    $this->leads_model->log_lead_activity($getForm['lead_id'], "Vendor Quotation form " . strtoupper($getForm['form_status']) . " shared via whatsapp. Form ID : " . $data['formId']);
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

    public function pdf($formkey)
    {
        set_time_limit(0);
        if (!has_permission('vendors', '', 'view') && !has_permission('vendors', '', 'view_own')) {
            ajax_access_denied();
        }
        $pdf_data['form_data'] = $this->vendors_model->get_quotation_forms_by_key($formkey);
        if ($pdf_data['form_data']) {
            $pdf_data['item_data'] = $this->vendors_model->get_quotation_forms_items_by_form_id($pdf_data['form_data']['id']);
        }
        try {
            $pdf = vendor_quotation_mpdf($pdf_data);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $pdf->Output(mb_strtoupper(slug_it($formkey)) . '.pdf', $type);
    }
}
