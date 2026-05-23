<?php

use Omnipay\Common\Issuer;

defined('BASEPATH') or exit('No direct script access allowed');

class Proposals extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('proposals_model');
        $this->load->model('currencies_model');
        $this->load->model('payments_model');
        $this->load->model('invoices_model');
    }

    public function index($proposal_id = '')
    {
        $this->list_proposals($proposal_id);
    }

    public function list_proposals($proposal_id = '')
    {
        close_setup_menu();

        if (!has_permission('proposals', '', 'view') && !has_permission('proposals', '', 'view_own') && !proposal_manager_permission_check($proposal_id) && get_option('allow_staff_view_proposals_assigned') == 0) {
            access_denied('proposals');
        }
        // 🔹 Handle pipeline toggle requests (Perfex uses ?switch_pipeline=1)
        if ($this->input->get('switch_pipeline')) {
            $set = $this->input->get('switch_pipeline') == 'true' ? 'true' : 'false';
            $this->session->set_userdata('proposals_pipeline', $set);
            redirect(admin_url('proposals'));
        }

        // 🔹 Default to pipeline view if not set yet
        if ($this->session->userdata('proposals_pipeline') === null) {
            $this->session->set_userdata('proposals_pipeline', 'true');
        }

        $isPipeline = $this->session->userdata('proposals_pipeline') == 'true';
        // $isPipeline = $this->session->userdata('proposals_pipeline') == 'true';
        // $isPipeline = true;

        if ($isPipeline && !$this->input->get('status')) {
            $data['title'] = _l('proposals_pipeline');
            $data['bodyclass'] = 'proposals-pipeline';
            $data['switch_pipeline'] = false;
            // Direct access
            if (is_numeric($proposal_id)) {
                $data['proposalid'] = $proposal_id;
            } else {
                $data['proposalid'] = $this->session->flashdata('proposalid');
            }

            $this->load->view('admin/proposals/pipeline/manage', $data);
        } else {

            // Pipeline was initiated but user click from home page and need to show table only to filter
            if ($this->input->get('status') && $isPipeline) {
                $this->pipeline(0, true);
            }

            $data['proposal_id'] = $proposal_id;
            $data['switch_pipeline'] = true;
            $data['title'] = _l('proposals');
            $data['statuses'] = $this->proposals_model->get_statuses();
            $data['proposals_sale_agents'] = $this->proposals_model->get_sale_agents();
            $data['years'] = $this->proposals_model->get_proposals_years();
            $this->load->view('admin/proposals/manage', $data);
        }
    }

    public function table()
    {
        if (
            !has_permission('proposals', '', 'view')
            && !has_permission('proposals', '', 'view_own')
            && get_option('allow_staff_view_proposals_assigned') == 0
        ) {
            ajax_access_denied();
        }
        $data['proposaltemplates'] = $this->proposals_model->get_proposals_templates();
        $this->app->get_table_data('proposals');
    }

    public function proposal_relations($rel_id, $rel_type)
    {
        $this->app->get_table_data('proposals_relations', [
            'rel_id' => $rel_id,
            'rel_type' => $rel_type,
        ]);
    }

    public function delete_attachment($id)
    {
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo $this->proposals_model->delete_attachment($id);
        } else {
            ajax_access_denied();
        }
    }

    public function clear_signature($id)
    {
        if (has_permission('proposals', '', 'delete')) {
            $this->proposals_model->clear_signature($id);
        }

        redirect(admin_url('proposals/list_proposals/' . $id));
    }

    public function sync_data()
    {
        if (has_permission('proposals', '', 'create') || has_permission('proposals', '', 'edit')) {
            $has_permission_view = has_permission('proposals', '', 'view');
            $rel_id = $this->input->post('rel_id');
            $rel_type = $this->input->post('rel_type');
            $this->db->where('rel_id', $rel_id);
            $this->db->where('rel_type', $rel_type);
            if (!$has_permission_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }

            $address = trim($this->input->post('address'));
            $address = nl2br($address);
            $this->db->update(db_prefix() . 'proposals', [
                'phone' => $this->input->post('phone'),
                'zip' => $this->input->post('zip'),
                'country' => $this->input->post('country'),
                'state' => $this->input->post('state'),
                'address' => $address,
                'city' => $this->input->post('city'),
            ]);

            if ($this->db->affected_rows() > 0) {
                if ($rel_type == "lead") {
                    leadLastContactAtUpdate($rel_id);
                }
                echo json_encode([
                    'message' => _l('all_data_synced_successfully'),
                ]);
            } else {
                echo json_encode([
                    'message' => _l('sync_proposals_up_to_date'),
                ]);
            }
        }
    }

    public function proposal($id = '')
    {
        if ($this->input->post()) {
            $proposal_data = $this->input->post();
            $dynamic_amount_fields = [];
            if (isset($proposal_data['dynamic_fields'])) {
                $dynamic_amount_fields = $proposal_data['dynamic_fields'];
                unset($proposal_data['dynamic_fields']);
            }
            $tax_id = null;
            if (isset($proposal_data['tax_id'])) {
                $tax_id = $proposal_data['tax_id'];
                unset($proposal_data['tax_id']);
            }

            if (isset($proposal_data['download_request']) && $proposal_data['download_request'] == "1") {
                $proposal_data['download_allow_till'] = date('Y-m-d', strtotime('+1 days'));
            } else {
                $proposal_data['download_allow_till'] = NULL;
            }
            if ($id == '') {
                if (!has_permission('proposals', '', 'create')) {
                    access_denied('proposals');
                }
                $proposal_number_prefix = proposal_number_prefix();
                if (total_rows(db_prefix() . 'proposals', ["proposal_number_prefix" => $proposal_number_prefix, "proposal_number" => (int) $proposal_data['proposal_number']]) > 0) {
                    set_alert('warning', "Proposal number " . $proposal_number_prefix . $proposal_data['proposal_number'] . " already used.");
                    redirect(admin_url('proposals/proposal'));
                }
                $proposal_data['proposal_number_prefix'] = $proposal_number_prefix;
                $id = $this->proposals_model->add($proposal_data);
                if ($id) {
                    if (!empty($tax_id)) {
                        $getTax = get_tax_by_id($tax_id);
                        if (!empty($getTax)) {
                            save_tax_by_relation($getTax->taxrate, $getTax->name, $id, "proposal");
                        }
                    }
                    save_dynamic_amount_fields("proposal", $id, $dynamic_amount_fields);
                    $rel_id = $this->input->post('rel_id');
                    $rel_type = $this->input->post('rel_type');
                    if ($rel_type == "lead") {
                        leadLastContactAtUpdate($rel_id);
                    }
                    set_alert('success', _l('added_successfully', _l('proposal')));
                    if ($this->set_proposal_pipeline_autoload($id)) {
                        redirect(admin_url('proposals'));
                    } else {
                        redirect(admin_url('proposals/list_proposals/' . $id));
                    }
                }
            } else {
                if (!has_permission('proposals', '', 'edit')) {
                    access_denied('proposals');
                }

                $proposal = $this->proposals_model->get($id);
                if (total_rows(db_prefix() . 'proposals', ["proposal_number_prefix" => $proposal->proposal_number_prefix, "proposal_number" => (int) $proposal_data['proposal_number'], "id !=" => $id]) > 0) {
                    set_alert('warning', "Proposal number " . $proposal->proposal_number_prefix . $proposal_data['proposal_number'] . " already used.");
                    redirect(admin_url('proposals/proposal/' . $id));
                }

                $rel_id = $this->input->post('rel_id');
                $rel_type = $this->input->post('rel_type');
                if ($rel_type == "lead") {
                    leadLastContactAtUpdate($rel_id);
                }
                $success = $this->proposals_model->update($proposal_data, $id);
                if ($success) {
                    if (!empty($tax_id)) {
                        $getTax = get_tax_by_id($tax_id);
                        if (!empty($getTax)) {
                            save_tax_by_relation($getTax->taxrate, $getTax->name, $id, "proposal");
                        }
                    }
                    save_dynamic_amount_fields("proposal", $id, $dynamic_amount_fields);
                    set_alert('success', _l('updated_successfully', _l('proposal')));
                }
                if ($this->set_proposal_pipeline_autoload($id)) {
                    redirect(admin_url('proposals'));
                } else {
                    redirect(admin_url('proposals/list_proposals/' . $id));
                }
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('proposal_lowercase'));
        } else {
            $data['proposal'] = $this->proposals_model->get($id);

            if (!$data['proposal'] || !user_can_view_proposal($id)) {
                blank_page(_l('proposal_not_found'));
            }

            $data['estimate'] = $data['proposal'];
            $data['is_proposal'] = true;
            $title = _l('edit', _l('proposal_lowercase'));
        }

        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items'] = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['statuses'] = $this->proposals_model->get_statuses();
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $data['currencies'] = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['title'] = $title;
        $this->load->view('admin/proposals/proposal', $data);
    }

    public function get_template()
    {
        $name = $this->input->get('name');
        echo $this->load->view('admin/proposals/templates/' . $name, [], true);
    }

    public function send_expiry_reminder($id)
    {
        $canView = user_can_view_proposal($id);
        if (!$canView) {
            access_denied('proposals');
        } else {
            if (!has_permission('proposals', '', 'view') && !has_permission('proposals', '', 'view_own') && $canView == false) {
                access_denied('proposals');
            }
        }

        $success = $this->proposals_model->send_expiry_reminder($id);
        if ($success) {
            set_alert('success', _l('sent_expiry_reminder_success'));
        } else {
            set_alert('danger', _l('sent_expiry_reminder_fail'));
        }
        if ($this->set_proposal_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('proposals/list_proposals/' . $id));
        }
    }

    public function clear_acceptance_info($id)
    {
        if (is_admin()) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'proposals', get_acceptance_info_array(true));
        }

        redirect(admin_url('proposals/list_proposals/' . $id));
    }

    public function pdf($id)
    {
        set_time_limit(0);
        if (!$id) {
            redirect(admin_url('proposals'));
        }

        $canView = user_can_view_proposal($id);
        if (!$canView) {
            access_denied('proposals');
        } else {
            if (!has_permission('proposals', '', 'view') && !has_permission('proposals', '', 'view_own') && $canView == false) {
                access_denied('proposals');
            }
        }

        $proposal = $this->proposals_model->get($id);

        try {
            $pdf = proposal_mpdf($proposal);
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

        $proposal_number = format_proposal_number($id);
        $pdf->Output($proposal_number . '.pdf', $type);
    }

    public function get_proposal_data_ajax($id, $to_return = false)
    {
        if (!has_permission('proposals', '', 'view') && !has_permission('proposals', '', 'view_own') && get_option('allow_staff_view_proposals_assigned') == 0) {
            echo _l('access_denied');
            die;
        }

        $proposal = $this->proposals_model->get($id, [], true);

        if (!$proposal || !user_can_view_proposal($id)) {
            echo _l('proposal_not_found');
            die;
        }

        $this->app_mail_template->set_rel_id($proposal->id);
        $data = prepare_mail_preview_data('proposal_send_to_customer', $proposal->email);

        $merge_fields = [];

        $merge_fields = $this->app_merge_fields->get_flat('proposals');

        $data['proposal_statuses'] = $this->proposals_model->get_statuses();
        $data['members'] = $this->staff_model->get('', ['active' => 1]);
        $data['proposal_merge_fields'] = $merge_fields;
        $data['proposal'] = $proposal;
        $data['payments'] = $this->proposals_model->get_proposal_payments($proposal->id);
        if (!empty($data['payments'])) {
            foreach ($data['payments'] as $key => $payments) {
                $data['payments'][$key]['proposal_id'] = $proposal->id;
            }
            usort($data['payments'], function ($a, $b) {
                return $a['paymentid'] <=> $b['paymentid'];
            });
            $data['payments'] = array_values($data['payments']);
        }

        $data['totalNotes'] = total_rows(db_prefix() . 'notes', ['rel_id' => $id, 'rel_type' => 'proposal']);
        if ($to_return == false) {
            $this->load->view('admin/proposals/proposals_preview_template', $data);
        } else {
            return $this->load->view('admin/proposals/proposals_preview_template', $data, true);
        }
    }

    public function preview()
    {
        $id = $this->input->post('proposal_id');
        $proposal = $this->proposals_model->get($id);
        $html = $this->load->view('themes/' . active_clients_theme() . '/mpdf/proposal/proposalpdf', ['proposal' => $proposal], true);
        echo json_encode(['success' => true, 'html' => $html]);
    }

    public function add_note($rel_id)
    {
        if ($this->input->post() && user_can_view_proposal($rel_id)) {
            $this->misc_model->add_note($this->input->post(), 'proposal', $rel_id);
            echo $rel_id;
        }
    }

    public function get_notes($id)
    {
        if (user_can_view_proposal($id)) {
            $data['notes'] = $this->misc_model->get_notes($id, 'proposal');
            $this->load->view('admin/includes/sales_notes_template', $data);
        }
    }

    public function convert_to_estimate($id)
    {
        if (!has_permission('estimates', '', 'create')) {
            access_denied('estimates');
        }
        if ($this->input->post()) {
            $this->load->model('estimates_model');
            $data = $this->input->post();
            $estimate_id = $this->estimates_model->add($data);
            if ($estimate_id) {
                set_alert('success', _l('proposal_converted_to_estimate_success'));
                $this->db->where('id', $id);
                $this->db->update(db_prefix() . 'proposals', [
                    'estimate_id' => $estimate_id,
                    'status' => 3,
                ]);
                log_activity('Proposal Converted to Estimate [EstimateID: ' . $estimate_id . ', ProposalID: ' . $id . ']');

                hooks()->do_action('proposal_converted_to_estimate', ['proposal_id' => $id, 'estimate_id' => $estimate_id]);

                redirect(admin_url('estimates/estimate/' . $estimate_id));
            } else {
                set_alert('danger', _l('proposal_converted_to_estimate_fail'));
            }
            if ($this->set_proposal_pipeline_autoload($id)) {
                redirect(admin_url('proposals'));
            } else {
                redirect(admin_url('proposals/list_proposals/' . $id));
            }
        }
    }

    public function convert_to_invoice($id)
    {
        if (!has_permission('invoices', '', 'create')) {
            access_denied('invoices');
        }
        if ($this->input->post()) {
            $post_data = $this->input->post();
            $tax_id = null;
            if (isset($post_data['tax_id'])) {
                $tax_id = $post_data['tax_id'];
                unset($post_data['tax_id']);
            }
            $dynamic_amount_fields = [];
            if (isset($post_data['dynamic_fields'])) {
                $dynamic_amount_fields = $post_data['dynamic_fields'];
                unset($post_data['dynamic_fields']);
            }
            $invoice_number_prefix = invoice_number_prefix();
            if (total_rows(db_prefix() . 'invoices', ["prefix" => $invoice_number_prefix, "number" => (int) $post_data['number']]) > 0) {
                set_alert('warning', "Invoice number " . $invoice_number_prefix . $post_data['number'] . " already used.");
                redirect(admin_url('proposals#' . $id));
            }
            $post_data['prefix'] = $invoice_number_prefix;
            $invoice_id = $this->invoices_model->add($post_data);
            if ($invoice_id) {
                if (!empty($tax_id)) {
                    $getTax = get_tax_by_id($tax_id);
                    if (!empty($getTax)) {
                        save_tax_by_relation($getTax->taxrate, $getTax->name, $invoice_id, "invoice");
                    }
                }
                save_dynamic_amount_fields("invoice", $invoice_id, $dynamic_amount_fields);

                set_alert('success', _l('proposal_converted_to_invoice_success'));
                $this->db->where('id', $id);
                $this->db->update(db_prefix() . 'proposals', [
                    'invoice_id' => $invoice_id,
                    'status' => 5,
                ]);
                // Update all advance payments to invoice ID.
                $advance_payments = $this->payments_model->get_proposal_payments($id);
                if (!empty($advance_payments)) {
                    foreach ($advance_payments as $paymentData) {
                        $this->payments_model->advance_payment_move_to_invoice($paymentData['paymentid'], $invoice_id);
                    }
                }
                log_activity('Proposal Converted to Invoice [InvoiceID: ' . $invoice_id . ', ProposalID: ' . $id . ']');
                hooks()->do_action('proposal_converted_to_invoice', ['proposal_id' => $id, 'invoice_id' => $invoice_id]);
                redirect(admin_url('invoices/invoice/' . $invoice_id));
            } else {
                set_alert('danger', _l('proposal_converted_to_invoice_fail'));
            }
            if ($this->set_proposal_pipeline_autoload($id)) {
                redirect(admin_url('proposals'));
            } else {
                redirect(admin_url('proposals/list_proposals/' . $id));
            }
        }
    }

    public function get_invoice_convert_data($id)
    {
        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $data['currencies'] = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items'] = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $data['proposal'] = $this->proposals_model->get($id);
        $data['invoice'] = $data['proposal'];
        $data['invoice']->terms = get_option('invoice_terms_and_condition');
        $data['invoice']->status = 6;
        $data['convert_invoice'] = true;
        $data['is_invoice'] = true;
        $data['billable_tasks'] = [];
        $data['add_items'] = $this->_parse_items($data['proposal']);

        if ($data['proposal']->rel_type == 'lead') {
            $this->db->where('leadid', $data['proposal']->rel_id);
            $this->db->where('deleted_at IS NULL');
            $data['customer_id'] = $this->db->get(db_prefix() . 'clients')->row()->userid;
        } else {
            $data['customer_id'] = $data['proposal']->rel_id;
        }
        $data['custom_fields_rel_transfer'] = [
            'belongs_to' => 'proposal',
            'rel_id' => $id,
        ];
        $this->load->view('admin/proposals/invoice_convert_template', $data);
    }

    public function get_estimate_convert_data($id)
    {
        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $data['currencies'] = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items'] = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $data['proposal'] = $this->proposals_model->get($id);
        $data['add_items'] = $this->_parse_items($data['proposal']);

        $this->load->model('estimates_model');
        $data['estimate_statuses'] = $this->estimates_model->get_statuses();
        if ($data['proposal']->rel_type == 'lead') {
            $this->db->where('leadid', $data['proposal']->rel_id);
            $data['customer_id'] = $this->db->get(db_prefix() . 'clients')->row()->userid;
        } else {
            $data['customer_id'] = $data['proposal']->rel_id;
        }

        $data['custom_fields_rel_transfer'] = [
            'belongs_to' => 'proposal',
            'rel_id' => $id,
        ];

        $this->load->view('admin/proposals/estimate_convert_template', $data);
    }

    private function _parse_items($proposal)
    {
        $items = [];
        foreach ($proposal->items as $item) {
            $getItem = get_item_by_id($item['item_id']);

            $taxnames = [];
            $taxes = get_proposal_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                array_push($taxnames, $tax['taxname']);
            }
            $item['taxname'] = $taxnames;
            $item['parent_item_id'] = $item['id'];
            $item['id'] = 0;
            $item['gross_weight'] = (isset($getItem['gross_weight']) ? $getItem['gross_weight'] : 0);
            $item['net_weight'] = (isset($getItem['net_weight']) ? $getItem['net_weight'] : 0);
            $items[] = $item;
        }

        return $items;
    }

    /* Send proposal to email */
    public function send_to_email($id)
    {
        set_time_limit(0);
        $canView = user_can_view_proposal($id);
        if (!$canView) {
            access_denied('proposals');
        } else {
            if (!has_permission('proposals', '', 'view') && !has_permission('proposals', '', 'view_own') && $canView == false) {
                access_denied('proposals');
            }
        }

        if ($this->input->post()) {
            try {
                $success = $this->proposals_model->send_proposal_to_email(
                    $id,
                    $this->input->post('attach_pdf'),
                    $this->input->post('cc')
                );
            } catch (Exception $e) {
                $message = $e->getMessage();
                echo $message;
                if (strpos($message, 'Unable to get the size of the image') !== false) {
                    show_pdf_unable_to_get_image_size_error();
                }
                die;
            }

            if ($success) {
                set_alert('success', _l('proposal_sent_to_email_success'));
            } else {
                set_alert('danger', _l('proposal_sent_to_email_fail'));
            }

            if ($this->set_proposal_pipeline_autoload($id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('proposals/list_proposals/' . $id));
            }
        }
    }

    public function copy($id)
    {
        if (!has_permission('proposals', '', 'create')) {
            access_denied('proposals');
        }
        $new_id = $this->proposals_model->copy($id);
        if ($new_id) {
            set_alert('success', _l('proposal_copy_success'));
            $this->set_proposal_pipeline_autoload($new_id);
            redirect(admin_url('proposals/proposal/' . $new_id));
        } else {
            set_alert('success', _l('proposal_copy_fail'));
        }
        if ($this->set_proposal_pipeline_autoload($id)) {
            redirect(admin_url('proposals'));
        } else {
            redirect(admin_url('proposals/list_proposals/' . $id));
        }
    }

    public function mark_action_status($status, $id)
    {
        if (!has_permission('proposals', '', 'edit')) {
            access_denied('proposals');
        }
        $success = $this->proposals_model->mark_action_status($status, $id);
        if ($success) {
            set_alert('success', _l('proposal_status_changed_success'));
        } else {
            set_alert('danger', _l('proposal_status_changed_fail'));
        }
        if ($this->set_proposal_pipeline_autoload($id)) {
            redirect(admin_url('proposals'));
        } else {
            redirect(admin_url('proposals/list_proposals/' . $id));
        }
    }

    public function delete($id)
    {
        if (!has_permission('proposals', '', 'delete')) {
            access_denied('proposals');
        }
        $response = $this->proposals_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('proposal')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('proposal_lowercase')));
        }
        redirect(admin_url('proposals'));
    }

    public function get_relation_data_values($rel_id, $rel_type)
    {
        echo json_encode($this->proposals_model->get_relation_data_values($rel_id, $rel_type));
    }

    public function add_proposal_comment()
    {
        if ($this->input->post()) {
            echo json_encode([
                'success' => $this->proposals_model->add_comment($this->input->post()),
            ]);
        }
    }

    public function edit_comment($id)
    {
        if ($this->input->post()) {
            echo json_encode([
                'success' => $this->proposals_model->edit_comment($this->input->post(), $id),
                'message' => _l('comment_updated_successfully'),
            ]);
        }
    }

    public function get_proposal_comments($id)
    {
        $data['comments'] = $this->proposals_model->get_comments($id);
        $this->load->view('admin/proposals/comments_template', $data);
    }

    public function remove_comment($id)
    {
        $this->db->where('id', $id);
        $comment = $this->db->get(db_prefix() . 'proposal_comments')->row();
        if ($comment) {
            if ($comment->staffid != get_staff_user_id() && !is_admin()) {
                echo json_encode([
                    'success' => false,
                ]);
                die;
            }
            echo json_encode([
                'success' => $this->proposals_model->remove_comment($id),
            ]);
        } else {
            echo json_encode([
                'success' => false,
            ]);
        }
    }

    public function save_proposal_data()
    {
        if (!has_permission('proposals', '', 'edit') && !has_permission('proposals', '', 'create')) {
            header('HTTP/1.0 400 Bad error');
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);
            die;
        }
        $success = false;
        $message = '';

        $this->db->where('id', $this->input->post('proposal_id'));
        $this->db->update(db_prefix() . 'proposals', [
            'content' => $this->input->post('content', false),
        ]);

        $success = $this->db->affected_rows() > 0;
        $message = _l('updated_successfully', _l('proposal'));

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
    }

    // Pipeline
    public function pipeline($set = 0, $manual = false)
    {
        if ($set == 1) {
            $set = 'true';
        } else {
            $set = 'false';
        }
        $this->session->set_userdata([
            'proposals_pipeline' => $set,
        ]);
        if ($manual == false) {
            redirect(admin_url('proposals'));
        }
    }

    public function pipeline_open($id)
    {
        if (has_permission('proposals', '', 'view') || has_permission('proposals', '', 'view_own') || get_option('allow_staff_view_proposals_assigned') == 1) {
            $data['proposal'] = $this->get_proposal_data_ajax($id, true);
            $data['proposal_data'] = $this->proposals_model->get($id);
            $this->load->view('admin/proposals/pipeline/proposal', $data);
        }
    }

    public function update_pipeline()
    {
        if (has_permission('proposals', '', 'edit')) {
            $this->proposals_model->update_pipeline($this->input->post());
        }
    }

    public function get_pipeline()
    {
        log_message('error', 'get client id: ');

        if (has_permission('proposals', '', 'view') || has_permission('proposals', '', 'view_own') || get_option('allow_staff_view_proposals_assigned') == 1) {
            $data['statuses'] = $this->proposals_model->get_statuses();
            $this->load->view('admin/proposals/pipeline/pipeline', $data);
        }
    }

    public function pipeline_load_more()
    {

        $status = $this->input->get('status');
        $page = $this->input->get('page');

        $proposals = $this->proposals_model->do_kanban_query($status, $this->input->get('search'), $page, [
            'sort_by' => $this->input->get('sort_by'),
            'sort' => $this->input->get('sort'),
        ]);

        foreach ($proposals as $proposal) {
            $this->load->view('admin/proposals/pipeline/_kanban_card', [
                'proposal' => $proposal,
                'status' => $status,
            ]);
        }
    }

    public function set_proposal_pipeline_autoload($id)
    {
        if ($id == '') {
            return false;
        }

        if ($this->session->has_userdata('proposals_pipeline') && $this->session->userdata('proposals_pipeline') == 'true') {
            $this->session->set_flashdata('proposalid', $id);

            return true;
        }

        return false;
    }

    public function get_due_date()
    {
        if ($this->input->post()) {
            $date = $this->input->post('date');
            $duedate = '';
            if (get_option('proposal_due_after') != 0) {
                $date = to_sql_date($date);
                $d = date('Y-m-d', strtotime('+' . get_option('proposal_due_after') . ' DAY', strtotime($date)));
                $duedate = _d($d);
                echo $duedate;
            }
        }
    }

    /* Delete invoice payment*/
    public function delete_payment($id, $proposal_id, $contract_id = "")
    {
        if (!has_permission('payments', '', 'delete')) {
            access_denied('payments');
        }
        if (!$id) {
            redirect(admin_url('payments'));
        }
        $response = $this->payments_model->delete($id);
        if ($response == true) {
            if (!empty($contract_id)) {
                log_activity('Agreement ID [' . $contract_id . '] Payment Deleted [ID:' . $id . ']');
            } else {
                log_activity('Proposal ID [' . $proposal_id . '] Payment Deleted [ID:' . $id . ']');
            }
            set_alert('success', _l('deleted', _l('payment')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('payment_lowercase')));
        }
        if (empty($contract_id)) {
            redirect(admin_url("proposals#$proposal_id"));
        } else {
            redirect(admin_url("contracts/contract/$contract_id"));
        }
    }

    public function get_payment_modal()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if (!isset($data['contract_id'])) {
                $data['invoice_id'] = get_proposal_invoice_id($data['proposal_id']);
                $data['payment_data'] = get_proposal_payment_data($data['proposal_id']);
            } else {
                $proposal_ids = get_contract_linked_proposals($data['contract_id']);
                if (!empty($proposal_ids)) {
                    $data['proposals'] = $this->proposals_model->get_multiple_proposals($proposal_ids);
                    if (!empty($data['proposals'])) {
                        foreach ($data['proposals'] as $key => $proposal) {
                            $data['proposals'][$key]['payment_data'] = get_proposal_payment_data($proposal['id']);
                            $data['proposals'][$key]['invoice_id'] = get_proposal_invoice_id($proposal['id']);
                        }
                    }
                }
            }
            if (!empty($data['id'])) {
                $data['payment'] = $this->payments_model->get($data['id']);
                $data['title'] = "Update Payment";
            } else {
                $data['title'] = "Create Payment";
            }
            $this->load->model('payment_modes_model');
            $data['payment_modes'] = $this->payment_modes_model->get('', [], true, true);
            $i = 0;
            foreach ($data['payment_modes'] as $mode) {
                if ($mode['active'] == 0 && $data['payment']->paymentmode != $mode['id']) {
                    unset($data['payment_modes'][$i]);
                }
                $i++;
            }
            $html = $this->load->view('admin/proposals/payments_modal', $data, true);
            echo json_encode([
                'success' => true,
                'html' => $html,
            ]);
        }
    }

    public function save_payment()
    {
        $data = $this->input->post();
        $contract_id = "";
        if (isset($data['contract_id'])) {
            $contract_id = $data['contract_id'];
            unset($data['contract_id']);
        }
        if ($data) {
            if (!empty($data['id'])) {
                $success = $this->payments_model->update($data, $data['id']);
                if ($success) {
                    if (!empty($contract_id)) {
                        log_activity('Agreement ID [' . $contract_id . '] Payment Updated [ID:' . $id . ']');
                    } else {
                        log_activity('Proposal ID [' . $data['proposal_id'] . '] Payment Updated [ID:' . $id . ']');
                    }
                    set_alert('success', "Payment successfully updated.");
                } else {
                    set_alert('danger', "Error : Payment not updated.");
                }
            } else {
                if (!has_permission('payments', '', 'create')) {
                    access_denied('Record Payment');
                }

                if (isset($data['invoiceid']) && !empty($data['invoiceid']) && $data['invoiceid'] != "0") {
                    $id = $this->payments_model->process_payment($data, $data['invoiceid'], $contract_id);
                } else {
                    $id = $this->payments_model->add_payment_without_invoice($data, $contract_id);
                }
                if ($id) {
                    if (!empty($contract_id)) {
                        log_activity('Agreement ID [' . $contract_id . '] Payment Recorded [ID:' . $id . ']');
                    } else {
                        log_activity('Proposal ID [' . $data['proposal_id'] . '] Payment Recorded [ID:' . $id . ']');
                    }
                    set_alert('success', "Payment successfully created.");
                } else {
                    set_alert('danger', "Error : Payment not created.");
                }
            }
        }
        if (!empty($contract_id)) {
            redirect(admin_url('contracts/contract/' . $contract_id));
        } else {
            redirect(admin_url('proposals#' . $data['proposal_id']));
        }
    }

    public function get_bank_details()
    {
        $type = $this->input->post('type');
        if ($type == "0") {
            $data['bank_ac_name'] = get_option('proposal_domestic_account_name');
            $data['bank_ac_no'] = get_option('proposal_domestic_account_no');
            $data['bank_name'] = get_option('proposal_domestic_bank_name');
            $data['bank_ifsc_code'] = get_option('proposal_domestic_ifsc_code');
            $data['bank_swift_code'] = get_option('proposal_domestic_swift_code');
            $data['bank_address'] = get_option('proposal_domestic_address');
        } else {
            $data['bank_ac_name'] = get_option('proposal_international_account_name');
            $data['bank_ac_no'] = get_option('proposal_international_account_no');
            $data['bank_name'] = get_option('proposal_international_bank_name');
            $data['bank_ifsc_code'] = get_option('proposal_international_ifsc_code');
            $data['bank_swift_code'] = get_option('proposal_international_swift_code');
            $data['bank_address'] = get_option('proposal_international_address');
        }
        echo json_encode(["success" => true, "data" => $data]);
        exit;
    }
}
