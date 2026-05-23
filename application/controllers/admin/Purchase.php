<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchase extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('purchase_model');
        $this->load->model('currencies_model');
        $this->load->model('payments_model');
        $this->load->model('invoices_model');
        $this->load->model('debit_notes_model');
    }

    public function index($purchase_id = '')
    {
        $this->list_purchase($purchase_id);
    }

    public function list_purchase($purchase_id = '')
    {
        close_setup_menu();

        if (!has_permission('purchase', '', 'view') && !has_permission('purchase', '', 'view_own')) {
            access_denied('purchase');
        }

        $data['purchase_id']           = $purchase_id;
        $data['switch_pipeline']       = true;
        $data['title']                 = _l('purchase');
        $data['statuses']              = $this->purchase_model->get_statuses();
        $data['purchase_sale_agents'] = $this->purchase_model->get_sale_agents();
        $data['years']                 = $this->purchase_model->get_purchase_years();
        $this->load->view('admin/purchase/manage', $data);
    }

    public function table()
    {
        if (
            !has_permission('purchase', '', 'view')
            && !has_permission('purchase', '', 'view_own')
        ) {
            ajax_access_denied();
        }
        $this->app->get_table_data('purchase');
    }

    public function purchase_orders_by_vendors($vendor_id)
    {
        $this->app->get_table_data('purchase', [
            'vendor_id'   => $vendor_id,
        ]);
    }

    public function delete_attachment($id)
    {
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo $this->purchase_model->delete_attachment($id);
        } else {
            ajax_access_denied();
        }
    }

    public function purchase($id = '')
    {
        if ($this->input->post()) {
            $purchase_data = $this->input->post();
            $dynamic_amount_fields = [];
            if (isset($purchase_data['dynamic_fields'])) {
                $dynamic_amount_fields = $purchase_data['dynamic_fields'];
                unset($purchase_data['dynamic_fields']);
            }
            $tax_id = null;
            if (isset($purchase_data['tax_id'])) {
                $tax_id = $purchase_data['tax_id'];
                unset($purchase_data['tax_id']);
            }

            if ($id == '') {
                if (!has_permission('purchase', '', 'create')) {
                    access_denied('purchase');
                }
                $purchase_number_prefix = purchase_number_prefix();
                if (total_rows(db_prefix() . 'purchase', ["purchase_number_prefix" => $purchase_number_prefix, "purchase_number" => (int) $purchase_data['purchase_number']]) > 0) {
                    set_alert('warning', "Purchase number " . $purchase_number_prefix . $purchase_data['purchase_number'] . " already used.");
                    redirect(admin_url('purchase/purchase'));
                }
                $purchase_data['purchase_number_prefix'] = $purchase_number_prefix;
                $id = $this->purchase_model->add($purchase_data);
                if ($id) {
                    if (!empty($tax_id)) {
                        $getTax = get_tax_by_id($tax_id);
                        if (!empty($getTax)) {
                            save_tax_by_relation($getTax->taxrate, $getTax->name, $id, "purchase");
                        }
                    }
                    save_dynamic_amount_fields("purchase", $id, $dynamic_amount_fields);
                    set_alert('success', _l('added_successfully', _l('purchase')));
                    redirect(admin_url('purchase/list_purchase/' . $id));
                }
            } else {
                if (!has_permission('purchase', '', 'edit')) {
                    access_denied('purchase');
                }

                $purchase = $this->purchase_model->get($id);
                if (total_rows(db_prefix() . 'purchase', ["purchase_number_prefix" => $purchase->purchase_number_prefix, "purchase_number" => (int) $purchase_data['purchase_number'], "id !=" => $id]) > 0) {
                    set_alert('warning', "Purchase number " . $purchase->purchase_number_prefix . $purchase_data['purchase_number'] . " already used.");
                    redirect(admin_url('purchase/purchase/' . $id));
                }
                $success = $this->purchase_model->update($purchase_data, $id);
                if ($success) {
                    if (!empty($tax_id)) {
                        $getTax = get_tax_by_id($tax_id);
                        if (!empty($getTax)) {
                            save_tax_by_relation($getTax->taxrate, $getTax->name, $id, "purchase");
                        }
                    }
                    save_dynamic_amount_fields("purchase", $id, $dynamic_amount_fields);
                    set_alert('success', _l('updated_successfully', _l('purchase')));
                }
                redirect(admin_url('purchase/list_purchase/' . $id));
            }
        }
        if ($id == '') {
            $title = "Create New Purchase";
        } else {
            $data['purchase'] = $this->purchase_model->get($id);
            $data['estimate']    = $data['purchase'];
            $data['is_purchase'] = true;
            $title  = "Edit Purchase";
        }

        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['statuses']      = $this->purchase_model->get_statuses();
        $data['staff']         = $this->staff_model->get('', ['active' => 1]);
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['title'] = $title;
        $this->load->view('admin/purchase/purchase', $data);
    }

    public function get_template()
    {
        $name = $this->input->get('name');
        echo $this->load->view('admin/purchase/templates/' . $name, [], true);
    }

    public function get_relation_data_values($vendor_id)
    {
        echo json_encode($this->purchase_model->get_relation_data_values($vendor_id));
    }

    public function pdf($id)
    {
        set_time_limit(0);
        if (!$id) {
            redirect(admin_url('purchase'));
        }

        if (!has_permission('purchase', '', 'view') && !has_permission('purchase', '', 'view_own') && $canView == false) {
            access_denied('purchase');
        }

        $purchase = $this->purchase_model->get($id);

        try {
            $pdf = purchase_mpdf($purchase);
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

        $purchase_number = format_purchase_number($id);
        $pdf->Output($purchase_number . '.pdf', $type);
    }

    public function get_purchase_data_ajax($id, $to_return = false)
    {
        if (!has_permission('purchase', '', 'view') && !has_permission('purchase', '', 'view_own')) {
            echo _l('access_denied');
            die;
        }

        $purchase = $this->purchase_model->get($id, [], true);
        $this->app_mail_template->set_rel_id($purchase->id);
        $data = prepare_mail_preview_data('purchase_send_to_vendor', $purchase->email);

        $merge_fields = [];

        $merge_fields = $this->app_merge_fields->get_flat('purchase');

        $data['purchase_statuses']     = $this->purchase_model->get_statuses();
        $data['members']               = $this->staff_model->get('', ['active' => 1]);
        $data['purchase_merge_fields'] = $merge_fields;
        $data['purchase']              = $purchase;

        $data['applied_debits'] = $this->debit_notes_model->get_applied_purchase_debits($id);
        if (debits_can_be_applied_to_purchase($purchase->status)) {
            $data['debits_available'] = $this->debit_notes_model->total_remaining_debits_by_vendor($purchase->vendor_id);

            if ($data['debits_available'] > 0) {
                $data['open_debits'] = $this->debit_notes_model->get_open_debits($purchase->vendor_id);
            }
            $data['purchase_currency'] = $this->currencies_model->get($purchase->currency);
        }

        $data['totalNotes']            = total_rows(db_prefix() . 'notes', ['rel_id' => $id, 'rel_type' => 'purchase']);
        if ($to_return == false) {
            $this->load->view('admin/purchase/purchase_preview_template', $data);
        } else {
            return $this->load->view('admin/purchase/purchase_preview_template', $data, true);
        }
    }

    public function preview()
    {
        $id = $this->input->post('purchase_id');
        $purchase = $this->purchase_model->get($id);
        $html = $this->load->view('themes/' . active_clients_theme() . '/mpdf/purchase/purchasepdf', ['purchase' => $purchase], true);
        echo json_encode(['success' => true, 'html' => $html]);
    }


    public function delete($id)
    {
        if (!has_permission('purchase', '', 'delete')) {
            access_denied('purchase');
        }
        $response = $this->purchase_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('purchase')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('purchase_lowercase')));
        }
        redirect(admin_url('purchase'));
    }


    public function save_purchase_data()
    {
        if (!has_permission('purchase', '', 'edit') && !has_permission('purchase', '', 'create')) {
            header('HTTP/1.0 400 Bad error');
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);
            die;
        }
        $success = false;
        $message = '';

        $this->db->where('id', $this->input->post('purchase_id'));
        $this->db->update(db_prefix() . 'purchase', [
            'content' => $this->input->post('content', false),
        ]);

        $success = $this->db->affected_rows() > 0;
        $message = _l('updated_successfully', _l('purchase'));

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
    }

    public function send_to_email($id)
    {
        set_time_limit(0);
        if (!has_permission('purchase', '', 'view') && !has_permission('purchase', '', 'view_own')) {
            access_denied('purchase');
        }

        if ($this->input->post()) {
            try {
                $success = $this->purchase_model->send_purchase_to_email(
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
                set_alert('success', "Email sent successfully");
            } else {
                set_alert('danger', "Email not sent");
            }

            redirect(admin_url('purchase/list_purchase/' . $id));
        }
    }

    public function add_note($rel_id)
    {
        if ($this->input->post()) {
            $this->misc_model->add_note($this->input->post(), 'purchase', $rel_id);
            echo $rel_id;
        }
    }

    public function get_notes($id)
    {
        $data['notes'] = $this->misc_model->get_notes($id, 'purchase');
        $this->load->view('admin/includes/sales_notes_template', $data);
    }

    public function apply_debits($purchase_id)
    {
        $total_debits_applied = 0;
        foreach ($this->input->post('amount') as $debit_id => $amount) {
            $success = $this->debit_notes_model->apply_debits($debit_id, [
                'purchase_id' => $purchase_id,
                'amount'     => $amount,
            ]);
            if ($success) {
                $total_debits_applied++;
            }
        }

        if ($total_debits_applied > 0) {
            set_alert('success', "Debits applied successfully");
        }
        redirect(admin_url('purchase/list_purchase/' . $purchase_id));
    }

    public function sync_data()
    {
        if (has_permission('purchase', '', 'create') || has_permission('purchase', '', 'edit')) {
            $has_permission_view = has_permission('purchase', '', 'view');
            $vendor_id = $this->input->post('vendor_id');
            $this->db->where('vendor_id', $vendor_id);
            if (!$has_permission_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }
            $address = trim($this->input->post('address'));
            $address = nl2br($address);
            $this->db->update(db_prefix() . 'purchase', [
                'phone'   => $this->input->post('phone'),
                'zip'     => $this->input->post('zip'),
                'country' => $this->input->post('country'),
                'state'   => $this->input->post('state'),
                'address' => $address,
                'city'    => $this->input->post('city'),
            ]);
            if ($this->db->affected_rows() > 0) {
                echo json_encode([
                    'message' => _l('all_data_synced_successfully'),
                ]);
            } else {
                echo json_encode([
                    'message' => "All purchase orders are up to date, nothing to sync",
                ]);
            }
        }
    }
}
