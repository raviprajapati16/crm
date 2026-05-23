<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Payments_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('invoices_model');
    }

    /**
     * Get payment by ID
     * @param  mixed $id payment id
     * @return object
     */
    public function get($id)
    {
        $this->db->select('*,' . db_prefix() . 'invoicepaymentrecords.id as paymentid');
        $this->db->join(db_prefix() . 'payment_modes', db_prefix() . 'payment_modes.id = ' . db_prefix() . 'invoicepaymentrecords.paymentmode', 'left');
        $this->db->order_by(db_prefix() . 'invoicepaymentrecords.id', 'asc');
        $this->db->where(db_prefix() . 'invoicepaymentrecords.id', $id);
        $payment = $this->db->get(db_prefix() . 'invoicepaymentrecords')->row();
        if (!$payment) {
            return false;
        }
        // Since version 1.0.1
        $this->load->model('payment_modes_model');
        $payment_gateways = $this->payment_modes_model->get_payment_gateways(true);
        if (is_null($payment->id)) {
            foreach ($payment_gateways as $gateway) {
                if ($payment->paymentmode == $gateway['id']) {
                    $payment->name = $gateway['name'];
                }
            }
        }

        return $payment;
    }

    /**
     * Get all invoice payments
     * @param  mixed $invoiceid invoiceid
     * @return array
     */
    public function get_invoice_payments($invoiceid)
    {
        $this->db->select('*,' . db_prefix() . 'invoicepaymentrecords.id as paymentid');
        $this->db->join(db_prefix() . 'payment_modes', db_prefix() . 'payment_modes.id = ' . db_prefix() . 'invoicepaymentrecords.paymentmode', 'left');
        $this->db->order_by(db_prefix() . 'invoicepaymentrecords.id', 'asc');
        $this->db->where('invoiceid', $invoiceid);
        $this->db->where(db_prefix() . 'invoicepaymentrecords.deleted_at IS NULL');
        $payments = $this->db->get(db_prefix() . 'invoicepaymentrecords')->result_array();
        // Since version 1.0.1
        $this->load->model('payment_modes_model');
        $payment_gateways = $this->payment_modes_model->get_payment_gateways(true);
        $i                = 0;
        foreach ($payments as $payment) {
            if (is_null($payment['id'])) {
                foreach ($payment_gateways as $gateway) {
                    if ($payment['paymentmode'] == $gateway['id']) {
                        $payments[$i]['id']   = $gateway['id'];
                        $payments[$i]['name'] = $gateway['name'];
                    }
                }
            }
            $i++;
        }

        return $payments;
    }

    public function get_proposal_payments($proposal_id)
    {
        $this->db->select('*,' . db_prefix() . 'invoicepaymentrecords.id as paymentid');
        $this->db->join(db_prefix() . 'payment_modes', db_prefix() . 'payment_modes.id = ' . db_prefix() . 'invoicepaymentrecords.paymentmode', 'left');
        $this->db->order_by(db_prefix() . 'invoicepaymentrecords.id', 'asc');
        $this->db->where(db_prefix() . 'invoicepaymentrecords.deleted_at IS NULL');
        $this->db->where('proposal_id', $proposal_id);
        $payments = $this->db->get(db_prefix() . 'invoicepaymentrecords')->result_array();
        // Since version 1.0.1
        $this->load->model('payment_modes_model');
        $payment_gateways = $this->payment_modes_model->get_payment_gateways(true);
        $i                = 0;
        foreach ($payments as $payment) {
            if (is_null($payment['id'])) {
                foreach ($payment_gateways as $gateway) {
                    if ($payment['paymentmode'] == $gateway['id']) {
                        $payments[$i]['id']   = $gateway['id'];
                        $payments[$i]['name'] = $gateway['name'];
                    }
                }
            }
            $i++;
        }

        return $payments;
    }

    /**
     * Process invoice payment offline or online
     * @since  Version 1.0.1
     * @param  array $data $_POST data
     * @return boolean
     */
    public function process_payment($data, $invoiceid = '')
    {
        // Offline payment mode from the admin side
        if (is_numeric($data['paymentmode'])) {
            if (is_staff_logged_in()) {
                $id = $this->add($data);

                return $id;
            }

            return false;

            // Is online payment mode request by client or staff
        } elseif (!is_numeric($data['paymentmode']) && !empty($data['paymentmode'])) {
            // This request will come from admin area only
            // If admin clicked the button that dont want to pay the invoice from the getaways only want
            if (is_staff_logged_in() && has_permission('payments', '', 'create')) {
                if (isset($data['do_not_redirect'])) {
                    $id = $this->add($data);

                    return $id;
                }
            }

            if (!is_numeric($invoiceid)) {
                if (!isset($data['invoiceid'])) {
                    die('No invoice specified');
                }
                $invoiceid = $data['invoiceid'];
            }

            if (isset($data['do_not_send_email_template'])) {
                unset($data['do_not_send_email_template']);
                $this->session->set_userdata([
                    'do_not_send_email_template' => true,
                ]);
            }

            $invoice = $this->invoices_model->get($invoiceid);
            // Check if request coming from admin area and the user added note so we can insert the note also when the payment is recorded
            if (isset($data['note']) && $data['note'] != '') {
                $this->session->set_userdata([
                    'payment_admin_note' => $data['note'],
                ]);
            }

            if (get_option('allow_payment_amount_to_be_modified') == 0) {
                $data['amount'] = get_invoice_total_left_to_pay($invoiceid, $invoice->total);
            }

            $data['invoiceid'] = $invoiceid;
            $data['invoice']   = $invoice;
            $data              = hooks()->apply_filters('before_process_gateway_func', $data);

            $this->load->model('payment_modes_model');
            $gateway = $this->payment_modes_model->get($data['paymentmode']);

            $gateway->instance->process_payment($data);
        }

        return false;
    }

    /**
     * Record new payment
     * @param array $data payment data
     * @return boolean
     */
    public function add($data, $subscription = false)
    {
        set_time_limit(0);
        // Check if field do not redirect to payment processor is set so we can unset from the database
        if (isset($data['do_not_redirect'])) {
            unset($data['do_not_redirect']);
        }

        if ($subscription != false) {
            $after_success = get_option('after_subscription_payment_captured');

            if ($after_success == 'nothing' || $after_success == 'send_invoice') {
                $data['do_not_send_email_template'] = true;
            }
        }

        if (isset($data['do_not_send_email_template'])) {
            unset($data['do_not_send_email_template']);
            $do_not_send_email_template = true;
        } elseif ($this->session->has_userdata('do_not_send_email_template')) {
            $do_not_send_email_template = true;
            $this->session->unset_userdata('do_not_send_email_template');
        }

        if (is_staff_logged_in()) {
            if (isset($data['date'])) {
                $data['date'] = to_sql_date($data['date']);
            } else {
                $data['date'] = date('Y-m-d H:i:s');
            }
            if (isset($data['note'])) {
                $data['note'] = nl2br($data['note']);
            } elseif ($this->session->has_userdata('payment_admin_note')) {
                $data['note'] = nl2br($this->session->userdata('payment_admin_note'));
                $this->session->unset_userdata('payment_admin_note');
            }
        } else {
            $data['date'] = date('Y-m-d H:i:s');
        }

        if (is_staff_logged_in() || is_admin()) {
            $data['created_by'] = get_staff_user_id();
        }

        $data['daterecorded'] = date('Y-m-d H:i:s');
        $data                 = hooks()->apply_filters('before_payment_recorded', $data);

        $this->db->insert(db_prefix() . 'invoicepaymentrecords', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $invoice      = $this->invoices_model->get($data['invoiceid']);
            $force_update = false;

            if (!class_exists('Invoices_model', false)) {
                $this->load->model('invoices_model');
            }

            if ($invoice->status == Invoices_model::STATUS_DRAFT) {
                $force_update = true;
            }

            update_invoice_status($data['invoiceid'], $force_update);
            update_contract_terms($insert_id);

            $activity_lang_key = 'invoice_activity_payment_made_by_staff';
            if (!is_staff_logged_in()) {
                $activity_lang_key = 'invoice_activity_payment_made_by_client';
            }

            $this->invoices_model->log_invoice_activity($data['invoiceid'], $activity_lang_key, !is_staff_logged_in() ? true : false, serialize([
                app_format_money($data['amount'], $invoice->currency_name),
                '<a href="' . admin_url('payments/payment/' . $insert_id) . '" target="_blank">#' . $insert_id . '</a>',
            ]));

            log_activity('Payment Recorded [ID:' . $insert_id . ', Invoice Number: ' . format_invoice_number($invoice->id) . ', Total: ' . app_format_money($data['amount'], $invoice->currency_name) . ']');

            // Send email to the client that the payment is recorded
            $payment               = $this->get($insert_id);
            $payment->invoice_data = $this->invoices_model->get($payment->invoiceid);
            set_mailing_constant();
            $paymentpdf           = payment_mpdf($payment);
            $payment_pdf_filename = mb_strtoupper(slug_it(_l('payment') . '-' . $payment->paymentid), 'UTF-8') . '.pdf';
            $attach               = $paymentpdf->Output($payment_pdf_filename, 'S');

            if (
                !isset($do_not_send_email_template)
                || ($subscription != false && $after_success == 'send_invoice_and_receipt')
                || ($subscription != false && $after_success == 'send_invoice')
            ) {
                $template_name        = 'invoice_payment_recorded_to_customer';
                $pdfInvoiceAttachment = false;
                $attachPaymentReceipt = true;
                $emails_sent          = [];

                $where = ['active' => 1, 'invoice_emails' => 1];

                if ($subscription != false) {
                    $where['is_primary'] = 1;
                    $template_name       = 'subscription_payment_succeeded';

                    if ($after_success == 'send_invoice_and_receipt' || $after_success == 'send_invoice') {
                        $invoice_number = format_invoice_number($payment->invoiceid);
                        set_mailing_constant();
                        $payment->invoice_data->pdf_type = "tax-invoice";
                        $pdfInvoice           = invoice_mpdf($payment->invoice_data);
                        $pdfInvoiceAttachment = $pdfInvoice->Output($invoice_number . '.pdf', 'S');

                        if ($after_success == 'send_invoice') {
                            $attachPaymentReceipt = false;
                        }
                    }
                    // Is from settings: Send Payment Receipt
                }

                $contacts = $this->clients_model->get_contacts($invoice->clientid, $where);

                foreach ($contacts as $contact) {
                    $template = mail_template(
                        $template_name,
                        $contact,
                        $invoice,
                        $subscription,
                        $payment->paymentid
                    );

                    if ($attachPaymentReceipt) {
                        $template->add_attachment([
                            'attachment' => $attach,
                            'filename'   => $payment_pdf_filename,
                            'type'       => 'application/pdf',
                        ]);
                    }

                    if ($pdfInvoiceAttachment) {
                        $template->add_attachment([
                            'attachment' => $pdfInvoiceAttachment,
                            'filename'   => $invoice_number . '.pdf',
                            'type'       => 'application/pdf',
                        ]);
                    }
                    $merge_fields = $template->get_merge_fields();

                    if ($template->send()) {
                        array_push($emails_sent, $contact['email']);
                    }

                    $this->app_sms->trigger(SMS_TRIGGER_PAYMENT_RECORDED, $contact['phonenumber'], $merge_fields);
                }

                if (count($emails_sent) > 0) {
                    $additional_activity_data = serialize([
                        implode(', ', $emails_sent),
                    ]);
                    $activity_lang_key = 'invoice_activity_record_payment_email_to_customer';
                    if ($subscription != false) {
                        $activity_lang_key = 'invoice_activity_subscription_payment_succeeded';
                    }
                    $this->invoices_model->log_invoice_activity($invoice->id, $activity_lang_key, false, $additional_activity_data);
                }
            }

            $this->db->where('staffid', $invoice->addedfrom);
            $this->db->or_where('staffid', $invoice->sale_agent);
            $staff_invoice = $this->db->get(db_prefix() . 'staff')->result_array();

            $notifiedUsers = [];
            foreach ($staff_invoice as $member) {
                if (get_option('notification_when_customer_pay_invoice') == 1) {
                    if (is_staff_logged_in() && $member['staffid'] == get_staff_user_id()) {
                        continue;
                    }
                    // E.q. had permissions create not don't have, so we must re-check this
                    if (user_can_view_invoice($invoice->id, $member['staffid'])) {
                        $notified = add_notification([
                            'fromcompany'     => true,
                            'touserid'        => $member['staffid'],
                            'description'     => 'not_invoice_payment_recorded',
                            'link'            => 'invoices/list_invoices/' . $invoice->id,
                            'additional_data' => serialize([
                                format_invoice_number($invoice->id),
                            ]),
                        ]);
                        if ($notified) {
                            array_push($notifiedUsers, $member['staffid']);
                        }
                    }

                    send_mail_template(
                        'invoice_payment_recorded_to_staff',
                        $member['email'],
                        $member['staffid'],
                        $invoice,
                        $attach,
                        $payment->id
                    );
                }
            }

            pusher_trigger_notification($notifiedUsers);

            hooks()->do_action('after_payment_added', $insert_id);

            return $insert_id;
        }

        return false;
    }

    public function add_payment_without_invoice($data)
    {
        set_time_limit(0);
        $data['invoiceid'] = 0;
        $proposal_data = $this->proposals_model->get($data['proposal_id']);

        // Check if field do not redirect to payment processor is set so we can unset from the database
        if (isset($data['do_not_redirect'])) {
            unset($data['do_not_redirect']);
        }

        // Check if email template sending should be prevented
        if (isset($data['do_not_send_email_template'])) {
            unset($data['do_not_send_email_template']);
            $do_not_send_email_template = true;
        } elseif ($this->session->has_userdata('do_not_send_email_template')) {
            $do_not_send_email_template = true;
            $this->session->unset_userdata('do_not_send_email_template');
        }

        // Set the date and note fields if staff is logged in
        if (is_staff_logged_in()) {
            if (isset($data['date'])) {
                $data['date'] = to_sql_date($data['date']);
            } else {
                $data['date'] = date('Y-m-d H:i:s');
            }
            if (isset($data['note'])) {
                $data['note'] = nl2br($data['note']);
            } elseif ($this->session->has_userdata('payment_admin_note')) {
                $data['note'] = nl2br($this->session->userdata('payment_admin_note'));
                $this->session->unset_userdata('payment_admin_note');
            }
        } else {
            $data['date'] = date('Y-m-d H:i:s');
        }

        // Set the recorded date and apply any filters
        $data['daterecorded'] = date('Y-m-d H:i:s');
        $data = hooks()->apply_filters('before_payment_recorded', $data);

        if (is_staff_logged_in() || is_admin()) {
            $data['created_by'] = get_staff_user_id();
        }

        // Insert the payment record
        $this->db->insert(db_prefix() . 'invoicepaymentrecords', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            // Log payment activity
            log_activity('Payment Recorded [ID:' . $insert_id . ', Proposal [ID: ' . $data['proposal_id'] . '] ,Amount: ' . app_format_money($data['amount'], $proposal_data->currency) . ']');
            update_contract_terms($insert_id);
            // Generate payment receipt PDF
            $payment = $this->get($insert_id);
            set_mailing_constant();
            $paymentpdf = payment_mpdf($payment);
            $payment_pdf_filename = mb_strtoupper(slug_it(_l('payment') . '-' . $payment->paymentid), 'UTF-8') . '.pdf';
            $attach = $paymentpdf->Output($payment_pdf_filename, 'S');

            // Send email to the client with payment details
            if (!isset($do_not_send_email_template)) {

                $emails_sent = [];
                $this->load->model('proposals_model');
                $lead_id = "";
                if ($proposal_data->rel_type == "lead") {
                    $client_id = get_client_id_by_lead_id($proposal_data->rel_id);
                    $lead_id = $proposal_data->rel_id;
                } else {
                    $client_id = $proposal_data->rel_id;
                }

                $proposal_data->paymentid = $payment->paymentid;

                if (!empty($client_id) && $client_id != "0") {
                    $contacts = $this->clients_model->get_contacts($client_id, ['active' => 1]);
                    foreach ($contacts as $contact) {
                        $template = mail_template('proposal_advance_payment_recorded_to_customer', $contact['email'], $proposal_data);
                        $template->add_attachment([
                            'attachment' => $attach,
                            'filename' => $payment_pdf_filename,
                            'type' => 'application/pdf',
                        ]);

                        if ($template->send()) {
                            array_push($emails_sent, $contact['email']);
                        }

                        $merge_fields = $template->get_merge_fields();
                        $this->app_sms->trigger(SMS_TRIGGER_PAYMENT_RECORDED, $contact['phonenumber'], $merge_fields);
                    }
                } else {
                    $this->load->model('leads_model');
                    $lead_data = $this->leads_model->get($lead_id);
                    $template = mail_template('proposal_advance_payment_recorded_to_customer', $lead_data->email, $proposal_data);
                    $template->add_attachment([
                        'attachment' => $attach,
                        'filename' => $payment_pdf_filename,
                        'type' => 'application/pdf',
                    ]);

                    if ($template->send()) {
                        array_push($emails_sent, $lead_data->email);
                    }

                    $merge_fields = $template->get_merge_fields();
                    $this->app_sms->trigger(SMS_TRIGGER_PAYMENT_RECORDED, $lead_data->phonenumber, $merge_fields);
                }

                if (count($emails_sent) > 0) {
                    log_activity('Payment Email Sent to Client', false, serialize($emails_sent));
                }
            }

            hooks()->do_action('after_payment_added', $insert_id);

            return $insert_id;
        }

        return false;
    }


    /**
     * Update payment
     * @param  array $data payment data
     * @param  mixed $id   paymentid
     * @return boolean
     */
    public function update($data, $id)
    {
        $payment = $this->get($id);

        $data['date'] = to_sql_date($data['date']);
        $data['note'] = nl2br($data['note']);

        $data = hooks()->apply_filters('before_payment_updated', $data, $id);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'invoicepaymentrecords', $data);
        if ($this->db->affected_rows() > 0) {
            if ($data['amount'] != $payment->amount) {
                update_invoice_status($payment->invoiceid);
                update_contract_terms($id);
            }
            log_activity('Payment Updated [Number:' . $id . ']');

            return true;
        }

        return false;
    }

    public function advance_payment_move_to_invoice($payment_id, $invoice_id)
    {
        $this->db->where('id', $payment_id);
        $this->db->update(db_prefix() . 'invoicepaymentrecords', ["invoiceid" => $invoice_id]);
        if ($this->db->affected_rows() > 0) {
            update_invoice_status($invoice_id);
            log_activity('Payment Record [ID:' . $payment_id . '] Moved to Invoice [ID: ' . $invoice_id . ']');
            return true;
        }
        return false;
    }

    /**
     * Delete payment from database
     * @param  mixed $id paymentid
     * @return boolean
     */
    public function delete($id)
    {
        $current = $this->get($id);
        if (!empty($current->invoiceid) && $current->invoiceid != 0) {
            $current_invoice = $this->invoices_model->get($current->invoiceid);
            $invoiceid       = $current->invoiceid;
            hooks()->do_action('before_payment_deleted', [
                'paymentid' => $id,
                'invoiceid' => $invoiceid,
            ]);
        }
        $this->db->where('id', $id);
        //$this->db->delete(db_prefix() . 'invoicepaymentrecords');
        $this->db->update(db_prefix() . 'invoicepaymentrecords', ['deleted_at' => date('Y-m-d H:i:s'), 'deleted_by' => get_staff_full_name()]);
        if ($this->db->affected_rows() > 0) {
            if (!empty($current->invoiceid) && $current->invoiceid != 0) {
                update_invoice_status($invoiceid);
                update_contract_terms("", $invoiceid);
                $this->invoices_model->log_invoice_activity($invoiceid, 'invoice_activity_payment_deleted', false, serialize([
                    $current->paymentid,
                    app_format_money($current->amount, $current_invoice->currency_name),
                ]));
                log_activity('Payment Deleted [ID:' . $id . ', Invoice Number: ' . format_invoice_number($current->id) . ']');
            } else {
                update_contract_terms("", "", $current->proposal_id);
            }
            return true;
        }
        return false;
    }
}
