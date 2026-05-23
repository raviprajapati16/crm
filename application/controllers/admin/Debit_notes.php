<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Debit_notes extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('debit_notes_model');
        $this->load->model('leads_model');
    }

    public function index($id = '')
    {
        $this->list_debit_notes($id);
    }

    public function list_debit_notes($id = '')
    {
        if (!has_permission('debit_notes', '', 'view') && !has_permission('debit_notes', '', 'view_own')) {
            access_denied('debit_notes');
        }

        close_setup_menu();

        $data['years']          = $this->debit_notes_model->get_debits_years();
        $data['statuses']       = $this->debit_notes_model->get_statuses();
        $data['debit_note_id'] = $id;
        $data['title']          = _l('debit_notes');
        $this->load->view('admin/debit_notes/manage', $data);
    }

    public function table()
    {
        if (!has_permission('debit_notes', '', 'view') && !has_permission('debit_notes', '', 'view_own')) {
            ajax_access_denied();
        }

        $this->app->get_table_data('debit_notes');
    }

    public function update_number_settings($id)
    {
        $response = [
            'success' => false,
            'message' => '',
        ];
        if (has_permission('debit_notes', '', 'edit')) {
            if ($this->input->post('prefix')) {
                $affected_rows = 0;

                $this->db->where('id', $id);
                $this->db->update(db_prefix() . 'debitnotes', [
                    'prefix' => $this->input->post('prefix'),
                ]);
                if ($this->db->affected_rows() > 0) {
                    $affected_rows++;
                }

                if ($affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = "Debit note updated successfully";
                }
            }
        }
        echo json_encode($response);
        die;
    }

    public function validate_number()
    {
        $isedit          = $this->input->post('isedit');
        $number          = $this->input->post('number');
        $original_number = $this->input->post('original_number');
        $number          = trim($number);
        $number          = ltrim($number, '0');
        if ($isedit == 'true') {
            if ($number == $original_number) {
                echo json_encode(true);
                die;
            }
        }
        if (total_rows(db_prefix() . 'debitnotes', [
            'number' => $number,
        ]) > 0) {
            echo 'false';
        } else {
            echo 'true';
        }
    }

    public function debit_note($id = '')
    {
        if (!has_permission('debit_notes', '', 'view') && !has_permission('debit_notes', '', 'view_own')) {
            access_denied('debit_notes');
        }
        if ($this->input->post()) {
            $debit_note_data = $this->input->post();
            if (isset($debit_note_data['date'])) {
                $debit_note_data['date'] = to_sql_date($debit_note_data['date']);
            }

            if ($id == '') {
                if (!has_permission('debit_notes', '', 'create')) {
                    access_denied('debit_notes');
                }
                $id = $this->debit_notes_model->add($debit_note_data);
                if ($id) {
                    set_alert('success', "Debit note added successfully");
                    redirect(admin_url('debit_notes/list_debit_notes/' . $id));
                }
            } else {
                if (!has_permission('debit_notes', '', 'edit')) {
                    access_denied('debit_notes');
                }
                $success = $this->debit_notes_model->update($debit_note_data, $id);
                if ($success) {
                    set_alert('success', "Debit note updated successfully");
                }
                redirect(admin_url('debit_notes/list_debit_notes/' . $id));
            }
        }
        if ($id == '') {
            $title = "Add new debit note";
        } else {
            $debit_note = $this->debit_notes_model->get($id);

            if (!$debit_note || (!has_permission('debit_notes', '', 'view') && $debit_note->addedfrom != get_staff_user_id())) {
                blank_page("Debit note not found.", 'danger');
            }

            $data['debit_note'] = $debit_note;
            $data['edit']        = true;
            $title  = "Edit Debit Note " . ' - ' . format_debit_note_number($debit_note->id);
        }

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
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

        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['title']     = $title;
        $data['bodyclass'] = 'debit-note';
        $this->load->view('admin/debit_notes/debit_note', $data);
    }

    public function apply_debits_to_purchase($debit_note_id)
    {
        $debitApplied = false;
        if ($this->input->post()) {
            foreach ($this->input->post('amount') as $purchase_id => $amount) {
                if ($this->debit_notes_model->apply_debits($debit_note_id, ['amount' => $amount, 'purchase_id' => $purchase_id])) {
                    $debitsApplied = true;
                }
            }
        }
        if ($debitsApplied) {
            set_alert('success', "Debits applied successfully");
        }
        redirect(admin_url('debit_notes/list_debit_notes/' . $debit_note_id));
    }

    public function refund($id, $refund_id = null)
    {
        if (has_permission('debit_notes', '', 'edit')) {
            $this->load->model('payment_modes_model');
            if (!$refund_id) {
                $data['payment_modes'] = $this->payment_modes_model->get('', [
                    'expenses_only !=' => 1,
                ]);
            } else {
                $data['refund']        = $this->debit_notes_model->get_refund($refund_id);
                $data['payment_modes'] = $this->payment_modes_model->get('', [], true, true);
                $i                     = 0;
                foreach ($data['payment_modes'] as $mode) {
                    if ($mode['active'] == 0 && $data['refund']->payment_mode != $mode['id']) {
                        unset($data['payment_modes'][$i]);
                    }
                    $i++;
                }
            }

            $data['debit_note'] = $this->debit_notes_model->get($id);
            $this->load->view('admin/debit_notes/refund', $data);
        }
    }

    public function create_refund($debit_note_id)
    {
        if (has_permission('debit_notes', '', 'edit')) {
            $data                = $this->input->post();
            $data['refunded_on'] = to_sql_date($data['refunded_on']);
            $data['staff_id']    = get_staff_user_id();
            $success             = $this->debit_notes_model->create_refund($debit_note_id, $data);

            if ($success) {
                set_alert('success', "Refund added successfully");
            }
        }

        redirect(admin_url('debit_notes/list_debit_notes/' . $debit_note_id));
    }

    public function edit_refund($refund_id, $cerdit_note_id)
    {
        if (has_permission('debit_notes', '', 'edit')) {
            $data                = $this->input->post();
            $data['refunded_on'] = to_sql_date($data['refunded_on']);
            $success             = $this->debit_notes_model->edit_refund($refund_id, $data);

            if ($success) {
                set_alert('success', "Refund updated successfully");
            }
        }

        redirect(admin_url('debit_notes/list_debit_notes/' . $cerdit_note_id));
    }

    public function delete_refund($refund_id, $debit_note_id)
    {
        if (has_permission('debit_notes', '', 'delete')) {
            $success = $this->debit_notes_model->delete_refund($refund_id, $debit_note_id);
            if ($success) {
                set_alert('success', "Refund deleted successfully");
            }
        }
        redirect(admin_url('debit_notes/list_debit_notes/' . $debit_note_id));
    }

    public function get_debit_note_data_ajax($id)
    {
        if (!has_permission('debit_notes', '', 'view') && !has_permission('debit_notes', '', 'view_own')) {
            echo _l('access_denied');
            die;
        }

        if (!$id) {
            die("Debit Note Not Found");
        }

        $debit_note = $this->debit_notes_model->get($id);

        if (!$debit_note || (!has_permission('debit_notes', '', 'view') && $debit_note->addedfrom != get_staff_user_id())) {
            echo "Debit Note Not Found";
            die;
        }

        $data = prepare_mail_preview_data('debit_note_send_to_vendor', $debit_note->vendorid);

        $data['debit_note']                   = $debit_note;
        $data['members']                       = $this->staff_model->get('', ['active' => 1]);
        $data['available_debitable_purchase'] = [];
        $data['available_debitable_purchase'] = $this->debit_notes_model->get_available_debitable_purchase($id);

        $this->load->view('admin/debit_notes/debit_note_preview_template', $data);
    }

    public function mark_open($id)
    {
        if (total_rows(db_prefix() . 'debitnotes', ['status' => 3, 'id' => $id]) > 0 && has_permission('debit_notes', '', 'edit')) {
            $this->debit_notes_model->mark($id, 1);
            set_alert('success', "Debit note marked as open");
        }

        redirect(admin_url('debit_notes/list_debit_notes/' . $id));
    }

    public function delete_attachment($id)
    {
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo $this->debit_notes_model->delete_attachment($id);
        } else {
            ajax_access_denied();
        }
    }

    public function mark_void($id)
    {
        $debit_note = $this->debit_notes_model->get($id);
        if ($debit_note->status != 2 && !$debit_note->debits_used && has_permission('debit_notes', '', 'edit')) {
            $this->debit_notes_model->mark($id, 3);
            set_alert('success', "Debit note marked as void");
        } else {
            set_alert('danger', "You don't have permission");
        }
        redirect(admin_url('debit_notes'));
    }

    /* Send debit note to email */
    public function send_to_email($id)
    {
        if (!has_permission('debit_notes', '', 'view') && !has_permission('debit_notes', '', 'view_own')) {
            access_denied('debit_notes');
        }
        $success = $this->debit_notes_model->send_debit_note_to_vendor($id, $this->input->post('attach_pdf'), $this->input->post('cc'));
        // In case client use another language
        load_admin_language();
        if ($success) {
            set_alert('success', "Debit note sent to vendor successfully");
        } else {
            set_alert('danger', "Debit note can not be sent to vendor");
        }
        redirect(admin_url('debit_notes/list_debit_notes/' . $id));
    }

    public function delete_purchase_applied_credit($id, $debit_id, $purchase_id)
    {
        if (has_permission('debit_notes', '', 'delete')) {
            $this->debit_notes_model->delete_applied_debit($id, $debit_id, $purchase_id);
            set_alert('success', "Debit deleted successfully");
        } else {
            set_alert('danger', "You don't have permission to delete");
        }
        redirect(admin_url('purchase/list_purchase/' . $purchase_id));
    }

    public function delete_debit_note_applied_debit($id, $debit_id, $purchase_id)
    {
        if (has_permission('debit_notes', '', 'delete')) {
            $this->debit_notes_model->delete_applied_debit($id, $debit_id, $purchase_id);
        }
        redirect(admin_url('debit_notes/list_debit_notes/' . $debit_id));
    }

    /* Delete debit note */
    public function delete($id)
    {
        if (!has_permission('debit_notes', '', 'delete')) {
            access_denied('debit_notes');
        }

        if (!$id) {
            redirect(admin_url('debit_notes'));
        }

        $debit_note = $this->debit_notes_model->get($id);

        if ($debit_note->debits_used || $debit_note->status == 2) {
            $success = false;
        } else {
            $success = $this->debit_notes_model->delete($id);
        }

        if ($success) {
            set_alert('success', "Debit note deleted successfully");
        } else {
            set_alert('warning', "Debit note can not be deleted");
        }

        redirect(admin_url('debit_notes'));
    }

    public function pdf($id)
    {
        set_time_limit(0);
        if (!has_permission('debit_notes', '', 'view') && !has_permission('debit_notes', '', 'view_own')) {
            access_denied('debit_notes');
        }
        if (!$id) {
            redirect(admin_url('debit_notes/list_debit_notes'));
        }
        $debit_note        = $this->debit_notes_model->get($id);
        $debit_note_number = format_debit_note_number($debit_note->id);

        try {
            $pdf = debit_note_mpdf($debit_note);
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

        $pdf->Output(mb_strtoupper(slug_it($debit_note_number)) . '.pdf', $type);
    }
}
