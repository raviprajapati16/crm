<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Contracts extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('contracts_model');
        $this->load->model('contract_types_model');
        $this->load->model('proposals_model');
        $this->load->model('clients_model');
        $this->load->model('payment_modes_model');
        $this->load->model('leads_model');
        $this->load->model('contact_book_model');
    }

    /* List all contracts */
    public function index()
    {
        close_setup_menu();

        if (!has_permission('contracts', '', 'view') && !has_permission('contracts', '', 'view_own')) {
            access_denied('Agreements');
        }

        $data['chart_types'] = json_encode($this->contracts_model->get_contracts_types_chart_data());
        $data['chart_types_values'] = json_encode($this->contracts_model->get_contracts_types_values_chart_data());
        $data['contract_types'] = $this->contracts_model->get_contract_types();
        $data['years'] = $this->contracts_model->get_contracts_years();
        $this->load->model('currencies_model');
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['title'] = _l('contracts');
        $this->load->view('admin/contracts/manage', $data);
    }

    public function table($clientid = '')
    {
        if (!has_permission('contracts', '', 'view') && !has_permission('contracts', '', 'view_own')) {
            ajax_access_denied();
        }

        $this->app->get_table_data('contracts', [
            'clientid' => $clientid,
        ]);
    }

    public function contract($id = '')
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if (isset($data['content']) && !empty($data['content'])) {
                $data['content'] = $this->input->post('content', false);
            }
            $proposal_ids = [];
            if (isset($data['proposal_id']) && !empty($data['proposal_id'])) {
                $proposal_ids = array_filter($data['proposal_id']);
                unset($data['proposal_id']);
            }
            if ($id == '') {
                if (!has_permission('contracts', '', 'create')) {
                    access_denied('Agreements');
                }

                if (isset($data['draft_id']) && !empty($data['draft_id'])) {
                    $draftData = $this->contract_types_model->get_single_draft($data['draft_id']);
                    if (!empty($draftData)) {
                        $data['content'] = $draftData['content'];
                    }
                }
                $contract_number_prefix = contract_number_prefix();
                if (total_rows(db_prefix() . 'contracts', ["prefix" => $contract_number_prefix, "number" => (int) $data['number']]) > 0) {
                    set_alert('warning', "Agreement number " . $contract_number_prefix . $data['number'] . " already used.");
                    redirect(admin_url('contracts/contract'));
                }
                $data['prefix'] = $contract_number_prefix;
                $id = $this->contracts_model->add($data);
                if ($id) {
                    if (!empty($proposal_ids)) {
                        update_contract_proposals($id, $proposal_ids);
                    }
                    set_alert('success', "Agreement created successfully.");
                    redirect(admin_url('contracts/contract/' . $id));
                }
            } else {
                if (!has_permission('contracts', '', 'edit')) {
                    access_denied('contracts');
                }

                $contract = $this->contracts_model->get($id);
                if (total_rows(db_prefix() . 'contracts', ["prefix" => $contract->prefix, "number" => (int) $data['number'], "id !=" => $id]) > 0) {
                    set_alert('warning', "Agreement number " . $contract->prefix . $data['number'] . " already used.");
                    redirect(admin_url('contracts/contract/' . $id));
                }

                $success = $this->contracts_model->update($data, $id);
                if ($success) {
                    if (!empty($proposal_ids)) {
                        update_contract_proposals($id, $proposal_ids);
                    }
                    set_alert('success', "Agreement updated successfully.");
                }
                redirect(admin_url('contracts/contract/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('contract_lowercase'));
        } else {
            $data['contract'] = $this->contracts_model->get($id, [], true);
            $data['contract_renewal_history'] = $this->contracts_model->get_contract_renewal_history($id);
            $data['totalNotes'] = total_rows(db_prefix() . 'notes', ['rel_id' => $id, 'rel_type' => 'contract']);
            if (!$data['contract'] || (!has_permission('contracts', '', 'view') && !manager_employee_data_access_permission_check("contracts") && $data['contract']->addedfrom != get_staff_user_id())) {
                blank_page(_l('contract_not_found'));
            }

            $data['selected_contract_type'] = $this->contract_types_model->get($data['contract']->contract_type);
            $data['selected_sub_contract_type'] = $this->contract_types_model->get_single_sub_type($data['contract']->sub_type);
            $data['selected_contract_draft'] = $this->contract_types_model->get_single_draft($data['contract']->draft_id);

            $data['contract_merge_fields'] = $this->app_merge_fields->get_flat('contract', ['other', 'client'], '{email_signature}');

            $title = $data['contract']->subject;
            if ($data['contract']->rel_type == "customer") {
                $data['verification_template'] = prepare_mail_preview_data('contract_send_verified_send_to_customer', $data['contract']->client);
                $data = array_merge($data, prepare_mail_preview_data('contract_send_to_customer', $data['contract']->client));
            } else if ($data['contract']->rel_type == "vendor") {
                $data['verification_template'] = prepare_mail_preview_data('contract_send_verified_send_to_vendor', $data['contract']->client);
                $data = array_merge($data, prepare_mail_preview_data('contract_send_to_vendor', $data['contract']->client));
            } else if ($data['contract']->rel_type == "contact_book") {
                $data['verification_template'] = prepare_mail_preview_data('contract_send_verified_send_to_contact_book', $data['contract']->client);
                $data = array_merge($data, prepare_mail_preview_data('contract_send_to_contact_book', $data['contract']->client));
            }

            $data['contract_contacts'] = $this->contracts_model->get_contract_contacts($id, $data['contract']->rel_type);
        }
        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }
        $this->load->model('currencies_model');
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['types'] = $this->contracts_model->get_contract_types();
        $data['payments_terms'] = $this->contracts_model->get_payment_terms($id);
        $data['payments_received_data'] = array();
        $proposal_ids = get_contract_linked_proposals($data['contract']->id);
        if (!empty($proposal_ids)) {
            $data['proposals'] = $this->proposals_model->get_multiple_proposals($proposal_ids);
            foreach ($data['proposals'] as $key => $proposal) {
                $payment_data = $this->proposals_model->get_proposal_payments($proposal['id']);
                if (!empty($payment_data)) {
                    foreach ($payment_data as $key2 => $payments) {
                        $payment_data[$key2]['proposal_id'] = $proposal['id'];
                    }
                    $data['payments_received_data'] = array_merge($payment_data, $data['payments_received_data']);
                    usort($data['payments_received_data'], function ($a, $b) {
                        return $a['paymentid'] <=> $b['paymentid'];
                    });
                    $data['payments_received_data'] = array_values($data['payments_received_data']);
                }
            }
        }
        $data['locked_amount'] = array_sum(array_column($this->contracts_model->get_payment_terms($id), 'amount'));
        $data['pending_amount'] = $data['contract']->contract_value - $data['locked_amount'];
        $data['title'] = $title;
        $data['bodyclass'] = 'contract';
        $this->load->view('admin/contracts/contract', $data);
    }

    public function get_template()
    {
        $name = $this->input->get('name');
        echo $this->load->view('admin/contracts/templates/' . $name, [], true);
    }

    public function pdf($id)
    {
        set_time_limit(0);
        if (!has_permission('contracts', '', 'view') && !has_permission('contracts', '', 'view_own')) {
            access_denied('contracts');
        }

        if (!$id) {
            redirect(admin_url('contracts'));
        }

        $contract = $this->contracts_model->get($id);
        try {
            $pdf = contract_mpdf($contract);
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }
        $type = 'D';
        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }
        if ($this->input->get('print')) {
            $type = 'I';
        }

        $pdf->Output(slug_it($contract->subject) . '.pdf', $type);
    }

    public function send_to_email($id)
    {
        set_time_limit(0);
        if (!has_permission('contracts', '', 'view') && !has_permission('contracts', '', 'view_own')) {
            access_denied('Agreements');
        }
        $success = $this->contracts_model->send_contract_to_client($id, $this->input->post('attach_pdf'), $this->input->post('cc'));
        if ($success) {
            set_alert('success', _l('contract_sent_to_client_success'));
        } else {
            set_alert('danger', _l('contract_sent_to_client_fail'));
        }
        redirect(admin_url('contracts/contract/' . $id));
    }

    public function contract_verificaton($id)
    {
        set_time_limit(0);
        if (!has_permission('contracts', '', 'view') && !has_permission('contracts', '', 'view_own')) {
            access_denied('contracts');
        }
        $success = $this->contracts_model->send_verification_mail_to_client($id, $this->input->post('cc'), $this->input->post('attach_pdf'));
        if ($success) {
            set_alert('success', "Agreement successfully verified");
        } else {
            set_alert('danger', "Error : Agreement not verified.");
        }
        redirect(admin_url('contracts/contract/' . $id));
    }

    public function add_note($rel_id)
    {
        if ($this->input->post() && (has_permission('contracts', '', 'view') || has_permission('contracts', '', 'view_own'))) {
            $this->misc_model->add_note($this->input->post(), 'contract', $rel_id);
            echo $rel_id;
        }
    }

    public function get_notes($id)
    {
        if ((has_permission('contracts', '', 'view') || has_permission('contracts', '', 'view_own'))) {
            $data['notes'] = $this->misc_model->get_notes($id, 'contract');
            $this->load->view('admin/includes/sales_notes_template', $data);
        }
    }

    public function clear_signature($contract_id, $sign_id)
    {
        if (has_permission('contracts', '', 'delete')) {
            $check = $this->contracts_model->clear_signature($contract_id, $sign_id);
            if ($check) {
                set_alert('success', "Signature data cleared successfully.");
            } else {
                set_alert('danger', "Error : Signature data not cleared.");
            }
        }
        redirect(admin_url('contracts/contract/' . $contract_id));
    }

    public function save_contract_data()
    {
        if (!has_permission('contracts', '', 'edit') && !has_permission('contracts', '', 'create')) {
            header('HTTP/1.0 400 Bad error');
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);
            die;
        }

        $success = false;
        $message = '';

        $this->db->where('id', $this->input->post('contract_id'));
        $this->db->update(db_prefix() . 'contracts', [
            'content' => $this->input->post('content', false),
            'other_content' => $this->input->post('other_content', false),
        ]);

        $success = $this->db->affected_rows() > 0;
        $message = "Agreement data saved successfully";

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
    }

    public function add_comment()
    {
        if ($this->input->post()) {
            echo json_encode([
                'success' => $this->contracts_model->add_comment($this->input->post()),
            ]);
        }
    }

    public function edit_comment($id)
    {
        if ($this->input->post()) {
            echo json_encode([
                'success' => $this->contracts_model->edit_comment($this->input->post(), $id),
                'message' => _l('comment_updated_successfully'),
            ]);
        }
    }

    public function get_comments($id)
    {
        $data['comments'] = $this->contracts_model->get_comments($id);
        $this->load->view('admin/contracts/comments_template', $data);
    }

    public function remove_comment($id)
    {
        $this->db->where('id', $id);
        $comment = $this->db->get(db_prefix() . 'contract_comments')->row();
        if ($comment) {
            if ($comment->staffid != get_staff_user_id() && !is_admin()) {
                echo json_encode([
                    'success' => false,
                ]);
                die;
            }
            echo json_encode([
                'success' => $this->contracts_model->remove_comment($id),
            ]);
        } else {
            echo json_encode([
                'success' => false,
            ]);
        }
    }

    public function renew()
    {
        if (!has_permission('contracts', '', 'create') && !has_permission('contracts', '', 'edit')) {
            access_denied('contracts');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $success = $this->contracts_model->renew($data);
            if ($success) {
                set_alert('success', _l('contract_renewed_successfully'));
            } else {
                set_alert('warning', _l('contract_renewed_fail'));
            }
            redirect(admin_url('contracts/contract/' . $data['contractid'] . '?tab=renewals'));
        }
    }

    public function delete_renewal($renewal_id, $contractid)
    {
        $success = $this->contracts_model->delete_renewal($renewal_id, $contractid);
        if ($success) {
            set_alert('success', _l('contract_renewal_deleted'));
        } else {
            set_alert('warning', _l('contract_renewal_delete_fail'));
        }
        redirect(admin_url('contracts/contract/' . $contractid . '?tab=renewals'));
    }

    public function copy($id)
    {
        if (!has_permission('contracts', '', 'create')) {
            access_denied('Agreements');
        }
        if (!$id) {
            redirect(admin_url('contracts'));
        }
        $newId = $this->contracts_model->copy($id);
        if ($newId) {
            set_alert('success', "Agreement copied successfully");
        } else {
            set_alert('warning', "Error : Agreement not copied.");
        }
        redirect(admin_url('contracts/contract/' . $newId));
    }

    public function delete($id)
    {
        if (!has_permission('contracts', '', 'delete')) {
            access_denied('Agreements');
        }
        if (!$id) {
            redirect(admin_url('contracts'));
        }
        $response = $this->contracts_model->update([], $id, true);
        if ($response == true) {
            set_alert('success', "Agreement deleted successfully");
        } else {
            set_alert('warning', "Error : Agreement not deleted.");
        }
        if (strpos($_SERVER['HTTP_REFERER'], 'clients/') !== false) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('contracts'));
        }
    }

    public function type($id = '')
    {
        if (!is_admin() && get_option('staff_members_create_inline_contract_types') == '0') {
            access_denied('Agreements');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            if (!$this->input->post('id')) {
                $data['created_by'] = get_staff_user_id();
                $data['created_at'] = date('Y-m-d H:i:s');
                $id = $this->contracts_model->add_contract_type($data);
                if ($id) {
                    $success = true;
                    $message = "Agreement main type successfully created";
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'id' => $id,
                    'name' => $this->input->post('name'),
                ]);
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->contracts_model->update_contract_type($data, $id);
                $message = '';
                if ($success) {
                    $message = "Agreement main type successfully updated";
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
        }
    }

    public function types()
    {
        if (!is_admin()) {
            access_denied('Agreements');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('contract_types');
        }
        $data['main_type_list'] = $this->contract_types_model->get_all_main_types();
        $data['title'] = _l('contract_types');
        $this->load->view('admin/contracts/manage_types', $data);
    }

    public function sub_types($main_id)
    {
        if (!is_admin()) {
            access_denied('sub agreements types');
        }
        $data['main_type'] = $this->contract_types_model->get($main_id);
        if (empty($data['main_type'])) {
            redirect(admin_url('contracts/types'));
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('sub_contract_types', ["main_type" => $main_id]);
        }
        $data['main_type_list'] = $this->contract_types_model->get_all_main_types();
        $data['title'] = "Sub Agreement Types";
        $this->load->view('admin/contracts/manage_sub_types', $data);
    }

    public function drafts($main_id, $sub_id)
    {
        if (!is_admin()) {
            access_denied('Agreements drafts');
        }
        $data['main_type'] = $this->contract_types_model->get($main_id);
        if (empty($data['main_type'])) {
            redirect(admin_url('contracts/types'));
        }

        $data['sub_type'] = $this->contract_types_model->get_sub_type_single($sub_id);
        if (empty($data['sub_type'])) {
            redirect(admin_url('contracts/types'));
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('contract_drafts', ["main_type" => $main_id, "sub_type" => $sub_id]);
        }
        $data['main_type_list'] = $this->contract_types_model->get();
        // print_r($data['main_type_list']);
        // exit;
        $data['title'] = "Agreement Drafts";
        $this->load->view('admin/contracts/manage_contract_drafts', $data);
    }

    public function get_sub_types_list()
    {
        $list = [];
        $success = false;
        $data = $this->input->post();
        if ($data) {
            $list = $this->contract_types_model->get_all_sub_types($data['main_id']);
            $success = true;
        }
        echo json_encode([
            'success' => $success,
            'sub_types' => $list,
        ]);
    }

    public function get_drafts_list()
    {
        $list = [];
        $success = false;
        $data = $this->input->post();
        if ($data) {
            $list = $this->contract_types_model->get_all_drafts($data['main_id'], $data['sub_id']);
            $success = true;
        }
        echo json_encode([
            'success' => $success,
            'sub_types' => $list,
        ]);
    }

    public function get_draft()
    {
        $success = false;
        $draftData = [];
        $data = $this->input->post();
        if ($data) {
            $draftData = $this->contract_types_model->get_single_draft($data['id']);
            $success = true;
        }
        echo json_encode([
            'success' => $success,
            'draft_data' => $draftData,
        ]);
    }

    public function sub_type()
    {
        if (!is_admin() && get_option('staff_members_create_inline_contract_types') == '0') {
            access_denied('sub Agreements types');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            if (!$this->input->post('id')) {
                $data['created_by'] = get_staff_user_id();
                $data['created_at'] = date('Y-m-d H:i:s');
                $id = $this->contracts_model->add_contract_sub_type($data);
                if ($id) {
                    $success = true;
                    $message = $message = "Agreement sub type successfully created";
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'id' => $id,
                    'name' => $this->input->post('name'),
                ]);
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->contracts_model->update_contract_sub_type($data, $id);
                $message = '';
                if ($success) {
                    $message = "Agreement sub type successfully updated";
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
        }
    }

    public function delete_sub_contract_type($id)
    {
        if (!$id) {
            redirect(admin_url('contracts/types'));
        }
        if (!is_admin()) {
            access_denied('Agreements');
        }
        $response = $this->contract_types_model->update_sub_type([], $id, true);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', $response['message']);
        } elseif ($response == true) {
            set_alert('success', "Agreement sub type successfully deleted");
        } else {
            set_alert('warning', "Error : Not deleted");
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function delete_contract_type($id)
    {
        if (!$id) {
            redirect(admin_url('contracts/types'));
        }
        if (!is_admin()) {
            access_denied('Agreements');
        }
        $response = $this->contract_types_model->update([], $id, true);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', $response['message']);
        } elseif ($response == true) {
            set_alert('success', "Agreement main type successfully deleted");
        } else {
            set_alert('warning', "Error : Not deleted");
        }
        redirect(admin_url('contracts/types'));
    }

    public function draft($main_id, $sub_id, $id = "")
    {
        if (!is_admin() && get_option('staff_members_create_inline_contract_types') == '0') {
            access_denied('Agreement draft');
        }
        $data = array(
            "main_id" => $main_id,
            "sub_id" => $sub_id,
            "available_merge_fields" => $this->app_merge_fields->all()
        );

        if (!empty($id)) {
            $data['draft'] = $this->contract_types_model->get_draft_single($id);
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['content'] = $this->input->post('content', false);
            if (!$this->input->post('id')) {
                $data['created_by'] = get_staff_user_id();
                $data['created_at'] = date('Y-m-d H:i:s');
                $id = $this->contract_types_model->add_draft($data);
                if ($id) {
                    set_alert('success', "Agreement draft successfully created");
                } else {
                    set_alert('warning', "Error : Agreement Draft not created.");
                }
                redirect(admin_url('contracts/drafts/' . $data['main_type'] . '/' . $data['sub_type']));
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->contract_types_model->update_draft($data, $id);
                if ($success) {
                    set_alert('success', "Agreement draft updated successfully");
                } else {
                    set_alert('warning', "Error : Agreement Draft not updated.");
                }
                redirect(admin_url('contracts/drafts/' . $data['main_type'] . '/' . $data['sub_type']));
            }
        }
        // echo "<pre>";
        // print_r($data["available_merge_fields"]);
        // exit;
        $this->load->view('admin/contracts/draft', $data);
    }

    public function delete_contract_draft($id)
    {
        if (!$id) {
            redirect(admin_url('contracts/types'));
        }
        if (!is_admin()) {
            access_denied('Agreements drafts');
        }
        $response = $this->contract_types_model->update_draft([], $id, true);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', $response['message']);
        } elseif ($response == true) {
            set_alert('success', "Agreement draft successfully deleted");
        } else {
            set_alert('warning', "Error : Draft not deleted");
        }
        redirect($_SERVER['HTTP_REFERER']);
    }


    public function add_contract_attachment($id)
    {
        handle_contract_attachment($id);
    }

    public function add_external_attachment()
    {
        if ($this->input->post()) {
            $this->misc_model->add_attachment_to_database(
                $this->input->post('contract_id'),
                'contract',
                $this->input->post('files'),
                $this->input->post('external')
            );
        }
    }

    public function delete_contract_attachment($attachment_id)
    {
        $file = $this->misc_model->get_file($attachment_id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo json_encode([
                'success' => $this->contracts_model->delete_contract_attachment($attachment_id),
            ]);
        }
    }

    public function copy_contract_type_data()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['type'] == "main_type_copy") {
                $getAllFromSubTypes = $this->contract_types_model->get_all_sub_types($data['from_main_type']);
                $getAllToSubTypes = $this->contract_types_model->get_all_sub_types($data['to_main_type']);

                if (!empty($getAllToSubTypes)) {
                    $getAllToSubTypes = array_map('trim', array_column($getAllToSubTypes, 'name'));
                    $getAllToSubTypes = array_filter($getAllToSubTypes, function ($value) {
                        return !empty($value);
                    });
                    $getAllToSubTypes = array_values($getAllToSubTypes);
                }
                $totalSubType = count($getAllFromSubTypes);
                $insertSubType = 0;
                if (!empty($getAllFromSubTypes)) {
                    // sub type copy
                    foreach ($getAllFromSubTypes as $key => $item) {
                        if (!empty(trim($item['name'])) && !in_array(trim($item['name']), $getAllToSubTypes)) {
                            $insertArr = array(
                                "main_type" => $data['to_main_type'],
                                "name" => trim($item['name']),
                                "created_by" => get_staff_user_id(),
                                "created_at" => date('Y-m-d H:i:s'),
                            );
                            $subTypeInsertId = $this->contract_types_model->insert_data(db_prefix() . 'contract_subtype', $insertArr);
                            if ($subTypeInsertId) {
                                //draft copy
                                $getAllDrafts = $this->contract_types_model->get_all_drafts($data['from_main_type'], $item['id']);
                                if (!empty($getAllDrafts)) {
                                    foreach ($getAllDrafts as $key => $item) {
                                        if (!empty(trim($item['draft_title']))) {
                                            $insertArr = array(
                                                "main_type" => $data['to_main_type'],
                                                "sub_type" => $subTypeInsertId,
                                                "draft_title" => trim($item['draft_title']),
                                                "content" => $item['content'],
                                                "created_by" => get_staff_user_id(),
                                                "created_at" => date('Y-m-d H:i:s'),
                                            );
                                            $this->contract_types_model->insert_data(db_prefix() . 'contract_draft', $insertArr);
                                        }
                                    }
                                }
                                $insertSubType++;
                            }
                        }
                    }
                }

                if ($insertSubType == 0) {
                    set_alert('warning', "Sorry! data not copy due to all sub types founds duplicate.");
                } else if ($totalSubType != $insertSubType) {
                    set_alert('success', $insertSubType . " out of " . $totalSubType . " sub types and it's drafts copied Successfully");
                } else if ($totalSubType == $insertSubType) {
                    set_alert('success', "All sub types and its draft copied successfully.");
                }
            } else if ($data['type'] == "sub_type_copy") {
                $is_copied = false;
                $subType = $this->contract_types_model->get_single_sub_type($data['from_sub_type']);
                if (!empty($subType)) {
                    $checkData = $this->contract_types_model->check_data(
                        db_prefix() . 'contract_subtype',
                        [
                            'name' => trim($subType['name']),
                            'main_type' => $data['to_main_type'],
                        ]
                    );
                    if (empty($checkData)) {
                        $insertArr = array(
                            "main_type" => $data['to_main_type'],
                            "name" => trim($subType['name']),
                            "created_by" => get_staff_user_id(),
                            "created_at" => date('Y-m-d H:i:s'),
                        );
                        $subTypeInsertId = $this->contract_types_model->insert_data(db_prefix() . 'contract_subtype', $insertArr);
                        if ($subTypeInsertId) {
                            //draft copy
                            $getAllDrafts = $this->contract_types_model->get_all_drafts($data['from_main_type'], $data['from_sub_type']);
                            if (!empty($getAllDrafts)) {
                                foreach ($getAllDrafts as $key => $item) {
                                    if (!empty(trim($item['draft_title']))) {
                                        $insertArr = array(
                                            "main_type" => $data['to_main_type'],
                                            "sub_type" => $subTypeInsertId,
                                            "draft_title" => trim($item['draft_title']),
                                            "content" => $item['content'],
                                            "created_by" => get_staff_user_id(),
                                            "created_at" => date('Y-m-d H:i:s'),
                                        );
                                        $is_copied = $this->contract_types_model->insert_data(db_prefix() . 'contract_draft', $insertArr);
                                        if ($is_copied) {
                                            set_alert('success', "Sub types and its draft copied successfully.");
                                        } else {
                                            set_alert('danger', "Error : copy failed");
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        set_alert('danger', "Sub type already exists.");
                    }
                }
            } else if ($data['type'] == "draft_copy") {
                $draftData = $this->contract_types_model->get_single_draft($data['selected_draft']);
                if (!empty($draftData)) {
                    $checkData = $this->contract_types_model->check_data(
                        db_prefix() . 'contract_draft',
                        [
                            'draft_title' => trim($draftData['draft_title']),
                            'main_type' => $data['to_main_type'],
                            'sub_type' => $data['to_sub_type'],
                        ]
                    );
                    if (empty($checkData)) {
                        $insertArr = array(
                            "main_type" => $data['to_main_type'],
                            "sub_type" => $data['to_sub_type'],
                            "draft_title" => trim($draftData['draft_title']),
                            "content" => trim($draftData['content']),
                            "created_by" => get_staff_user_id(),
                            "created_at" => date('Y-m-d H:i:s'),
                        );
                        $is_copied = $this->contract_types_model->insert_data(db_prefix() . 'contract_draft', $insertArr);
                        if ($is_copied) {
                            set_alert('success', "Draft copied successfully.");
                        } else {
                            set_alert('danger', "Error : copy failed");
                        }
                    } else {
                        set_alert('danger', "Draft already exists.");
                    }
                }
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function unique_check()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['type'] == "main_type") {
                $table = db_prefix() . "contracts_types";
                $column = "name";
            } else if ($data['type'] == "sub_type") {
                $table = db_prefix() . "contract_subtype";
                $column = "name";
            } else if ($data['type'] == "draft") {
                $table = db_prefix() . "contract_draft";
                $column = "draft_title";
            }
            $where[$column] = trim($data['value']);
            if (!empty($data['id'])) {
                $where['id !='] = $data['id'];
            }

            if (!empty($data['main_id'])) {
                $where['main_type'] = $data['main_id'];
            }

            if (!empty($data['sub_id'])) {
                $where['sub_type'] = $data['sub_id'];
            }

            $checkData = $this->contract_types_model->check_data($table, $where);
            if (!empty($checkData)) {
                echo json_encode([
                    'success' => true,
                    'message' => "Already Exists",
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "",
                ]);
            }
        }
    }

    public function change_default_signature()
    {
        $data = $this->input->post();

        $this->db->where('id', $data['id']);
        $success = $this->db->update(db_prefix() . 'contracts_sign', $data);
        if ($success) {
            log_activity("Agreement default sign updated To " . $data['default_signature'] . "  [Agreement ID : " . $data['contract_id'] . " Contact ID : " . $data['id'] . "]");
            echo json_encode([
                'success' => true,
                'message' => "Default Signature updated successfully",
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Default Signature not updated",
            ]);
        }
    }

    public function get_proposal_list()
    {
        $list = [];
        $success = false;
        $data = $this->input->post();
        if ($data) {
            $list = $this->proposals_model->get_proposal_by_customer_id($data['customer_id']);
            if (!empty($list)) {
                foreach ($list as $key => $item) {
                    if (empty($item['deleted_at'])) {
                        $list[$key]['proposal_formatted_id'] = format_proposal_number($item['id']);
                    } else {
                        unset($list[$key]);
                    }
                }
                $list = array_values($list);
            }
            $success = true;
        }
        echo json_encode([
            'success' => $success,
            'proposals' => $list,
        ]);
    }

    public function whatsapp_share()
    {
        $data = $this->input->post();
        if (isset($data['contract_id']) && isset($data['contact_id']) && isset($data['content'])) {
            $content = $this->input->post('content', false);
            $contractData = $this->contracts_model->get($data['contract_id']);
            $type = ($contractData->rel_type == "customer") ? "Customer" : "Vendor";
            if ($contractData->rel_type == "customer") {
                $contactData = $this->clients_model->get_contact($data['contact_id']);
                $content = str_replace("{contact_firstname}", $contactData->firstname, $content);
                $content = str_replace("{contact_lastname}", $contactData->lastname, $content);
            } else if ($contractData->rel_type == "vendor") {
                $contactData = $this->leads_model->get($data['contact_id']);
                $content = str_replace("{vendor_name}", $contactData->name, $content);
            } else if ($contractData->rel_type == "contact_book") {
                $contactData = (object) $this->contact_book_model->get($data['contact_id']);
                $content = str_replace("{contact_book_firstname}", $contactData->firstname, $content);
                $content = str_replace("{contact_book_lastname}", $contactData->lastname, $content);
            }
            $staffData = get_staff(get_staff_user_id());
            $signatureCode = get_whatsapp_signature();
            $link = site_url('contract/' . $contractData->id . '/' . $contractData->hash);

            $content = str_replace("{agreement_link}", $link, $content);
            $content = str_replace("{email_signature}", $signatureCode, $content);
            $this->contracts_model->update_contract([
                "contract_status" => "sent",
                "whatsapp_send_timestamp" => date('Y-m-d H:i:s'),
                "open_till" => date('Y-m-d H:i:s', strtotime('+24 hours'))
            ], $contractData->id);
            log_activity('Agreement Share Via Whatsapp [ Agreement ID : ' . $contractData->id . ' and ' . $type . ' ID : ' . $contactData->id . ']');
            $countryData = get_country($contractData->country);
            $phonunumber = convert_phonenumer_by_country($contactData->phonenumber, $countryData->iso2);
            $encodedMessage = urlencode($content);
            $whatsappLink = "https://wa.me/{$phonunumber}?text={$encodedMessage}";
            echo json_encode([
                'success' => true,
                'message' => "Whatsapp share succesfully",
                'link' => $whatsappLink,
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => "something went wrong"
            ]);
        }
    }

    public function save_contract_draft_data()
    {
        $success = false;
        $message = '';

        $this->db->where('id', $this->input->post('id'));
        $this->db->update(db_prefix() . 'contract_draft', [
            'content' => $this->input->post('content', false),
        ]);

        $success = $this->db->affected_rows() > 0;
        $message = _l('updated_successfully');

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
    }

    public function get_payment_terms_modal()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if (!empty($data['id'])) {
                $data['payment'] = $this->contracts_model->get_payment_term($data['id']);
                $data['title'] = ($data['payment'] == "Pending") ? "Update Payment Term" : "Payment Term Details";
            } else {
                $data['title'] = "Create Payment Terms";
            }
            $data['total_amount'] = $data['contract_value'];
            $data['locked_amount'] = array_sum(array_column($this->contracts_model->get_payment_terms($data['contract_id']), 'amount'));
            $data['pending_amount'] = $data['total_amount'] - $data['locked_amount'];
            $data['pending_percentage'] = ($data['pending_amount'] / $data['total_amount']) * 100;
            $data['payment_modes'] = $this->payment_modes_model->get('', [
                'expenses_only !=' => 1,
            ]);
            $html = $this->load->view('admin/contracts/payments_terms_modal', $data, true);
            echo json_encode([
                'success' => true,
                'html' => $html,
            ]);
        }
    }

    public function save_payment_terms()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if (!empty($data['id'])) {
                if (isset($data['scheduled_payment_date']) && !empty($data['scheduled_payment_date'])) {
                    $data['scheduled_payment_date'] = to_sql_date($data['scheduled_payment_date']);
                }
                $check = $this->contracts_model->update_payment_terms($data, $data['id']);
            } else {
                $check = true;
                if (isset($data['percentage']) && !empty($data['percentage'])) {
                    foreach ($data['percentage'] as $key => $pr) {
                        $insertArray = array(
                            "contract_id" => $data['contract_id'],
                            "percentage" => floatval($pr),
                            "amount" => floatval($data['amount'][$key]),
                            "scheduled_payment_date" => to_sql_date($data['scheduled_payment_date'][$key]),
                            "note" => $data['note'][$key],
                            "status" => "Pending",
                            "created_at" => date('Y-m-d H:i:s'),
                            "created_by" => get_staff_user_id()
                        );
                        $insert = $this->contracts_model->insert_payment_term($insertArray);
                        if (!$insert) {
                            $check = false;
                        }
                    }
                }
            }
            if ($check) {
                set_alert('success', "Payment successfully saved.");
            } else {
                set_alert('danger', "Error : Payment not saved.");
            }
            redirect(admin_url('contracts/contract/' . $data['contract_id']) . "?tab=payments_terms");
        }
    }

    public function delete_payment_term($contract_id, $id)
    {
        if ($id) {
            $check = $this->contracts_model->update_payment_terms([], $id, true);
            if ($check) {
                set_alert('success', "Payment successfully deleted.");
            } else {
                set_alert('danger', "Error : Payment not deleted.");
            }
        } else {
            set_alert('danger', "Error : Payment not deleted.");
        }
        redirect(admin_url('contracts/contract/' . $contract_id) . "?tab=payments_terms");
    }

    public function payment_reminder($contract_id, $status)
    {
        if (!has_permission('contracts', '', 'edit')) {
            access_denied('Agreements');
        }
        $success = $this->contracts_model->update_contract(["payment_reminder" => $status], $contract_id);
        if ($success) {
            log_activity(($status == "1") ? "Payment Reminder Resume For Agreement ID [$contract_id]." : "Payment Reminder Stopped For Agreement ID [$contract_id].");
            set_alert('success', ($status == "1") ? "Payment Reminder Resume Successfully." : "Payment Reminder Stop Successfully.");
        } else {
            set_alert('danger', ($status == "1") ? "Error : Payment Not Resume." : "Error :  Payment Reminder Not Stop.");
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function status($id, $status)
    {
        if (!has_permission('contracts', '', 'edit')) {
            access_denied('Agreements');
        }
        $this->contracts_model->update_contract([
            "contract_status" => $status,
        ], $id);
        log_activity('Agreement Status Updated [ Agreement ID : ' . $id . ' - Status : ' . $status . ']');
        set_alert('success', "Agreement status updated successfully changed to " . $status);
        redirect($_SERVER['HTTP_REFERER']);
    }
}
