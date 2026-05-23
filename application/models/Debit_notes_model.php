<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Debit_notes_model extends App_Model
{
    private $shipping_fields = ['shipping_street', 'shipping_city', 'shipping_city', 'shipping_state', 'shipping_zip', 'shipping_country'];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('leads_model');
    }

    public function get_statuses()
    {
        return hooks()->apply_filters('before_get_debit_notes_statuses', [
            [
                'id'             => 1,
                'color'          => '#03a9f4',
                'name'           => "Open",
                'order'          => 1,
                'filter_default' => true,
            ],
            [
                'id'             => 2,
                'color'          => '#84c529',
                'name'           => "Closed",
                'order'          => 2,
                'filter_default' => true,
            ],
            [
                'id'             => 3,
                'color'          => '#777',
                'name'           => "Void",
                'order'          => 3,
                'filter_default' => false,
            ],
        ]);
    }

    public function get_available_debitable_purchase($debit_note_id)
    {
        $has_permission_view = has_permission('purchase', '', 'view');

        $this->db->select('vendorid');
        $this->db->where('id', $debit_note_id);
        $debit_note = $this->db->get(db_prefix() . 'debitnotes')->row();

        $this->db->select('' . db_prefix() . 'purchase.id as id, status, total, date, ' . db_prefix() . 'currencies.name as currency_name');
        $this->db->where('vendor_id', $debit_note->vendorid);
        $this->db->where('status NOT IN (2)');
        if (!$has_permission_view) {
            $this->db->where('addedfrom', get_staff_user_id());
        }
        $this->db->where(db_prefix() . 'purchase.deleted_at IS NULL');
        $this->db->join(db_prefix() . 'currencies', '' . db_prefix() . 'currencies.id = ' . db_prefix() . 'purchase.currency');
        $purchases = $this->db->get(db_prefix() . 'purchase')->result_array();
        return $purchases;
    }

    public function send_debit_note_to_vendor($id, $attachpdf = true, $cc = '', $manually = false)
    {
        set_time_limit(0);
        $debit_note = $this->get($id);
        $number      = format_debit_note_number($debit_note->id);

        $sent    = false;
        $sent_to = $this->input->post('sent_to');

        if ($manually === true) {
            $sent_to  = [];
            $vendors[] = (array) $this->leads_model->get($debit_note->vendorid);
            foreach ($vendors as $vendor) {
                array_push($sent_to, $vendor['id']);
            }
        }

        if (is_array($sent_to) && count($sent_to) > 0) {
            if ($attachpdf) {
                set_mailing_constant();
                $pdf    = debit_note_mpdf($debit_note);
                $attach = $pdf->Output($number . '.pdf', 'S');
            }
            $i = 0;
            foreach ($sent_to as $vendor_id) {
                if ($vendor_id != '') {
                    if (!empty($cc) && $i > 0) {
                        $cc = '';
                    }

                    $vendor = $this->leads_model->get($vendor_id);
                    $staffid = get_staff_user_id();
                    $staff = $this->staff_model->get($staffid, ['active' => 1]);
                    $template = mail_template('debit_note_send_to_vendor', $debit_note, $vendor, $cc, $staff->email);

                    if ($attachpdf) {
                        $template->add_attachment([
                            'attachment' => $attach,
                            'filename'   => str_replace('/', '-', $number . '.pdf'),
                            'type'       => 'application/pdf',
                        ]);
                    }
                    if ($template->send()) {
                        $sent = true;
                    }
                }
                $i++;
            }
        } else {
            return false;
        }

        if ($sent) {
            hooks()->do_action('debit_note_sent', $id);

            return true;
        }

        return false;
    }

    /**
     * Get debit note/s
     * @param  mixed $id    debit note id
     * @param  array  $where perform where
     * @return mixed
     */
    public function get($id = '', $where = [])
    {
        $this->db->select('*,' . db_prefix() . 'currencies.id as currencyid, ' . db_prefix() . 'debitnotes.id as id, ' . db_prefix() . 'currencies.name as currency_name');
        $this->db->from(db_prefix() . 'debitnotes');
        $this->db->join(db_prefix() . 'currencies', '' . db_prefix() . 'currencies.id = ' . db_prefix() . 'debitnotes.currency', 'left');
        $this->db->where($where);

        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'debitnotes.id', $id);
            $debit_note = $this->db->get()->row();
            if ($debit_note) {
                $debit_note->refunds       = $this->get_refunds($id);
                $debit_note->total_refunds = $this->total_refunds_by_debit_note($id);

                $debit_note->applied_debits   = $this->get_applied_debits($id);
                $debit_note->remaining_debits = $this->total_remaining_debits_by_debit_note($id);
                $debit_note->debits_used      = $this->total_debits_used_by_debit_note($id);

                $debit_note->items  = get_items_by_type('debit_note', $id);
                $debit_note->vendor = $this->leads_model->get($debit_note->vendorid);

                if (!$debit_note->vendor) {
                    $debit_note->vendor          = new stdClass();
                    $debit_note->vendor->name = $debit_note->deleted_customer_name;
                }
                $debit_note->attachments = $this->get_attachments($id);
            }

            return $debit_note;
        }
        $this->db->where('deleted_at IS NULL');
        $this->db->order_by('number,YEAR(date)', 'desc');

        return $this->db->get()->result_array();
    }

    public function add($data)
    {
        $save_and_send = isset($data['save_and_send']);

        $data['prefix']        = get_option('debit_note_prefix');
        $data['number_format'] = get_option('debit_note_number_format');
        $data['datecreated']   = date('Y-m-d H:i:s');
        $data['addedfrom']     = get_staff_user_id();

        $items = [];
        if (isset($data['newitems'])) {
            $items = $data['newitems'];
            unset($data['newitems']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        $data = $this->map_shipping_columns($data);

        $hook = hooks()->apply_filters('before_create_debit_note', ['data' => $data, 'items' => $items]);

        $data  = $hook['data'];
        $items = $hook['items'];

        if (isset($data['item_select'])) {
            unset($data['item_select']);
        }

        if (isset($data['description'])) {
            unset($data['description']);
        }

        if (isset($data['long_description'])) {
            unset($data['long_description']);
        }

        if (isset($data['quantity'])) {
            unset($data['quantity']);
        }

        if (isset($data['unit'])) {
            unset($data['unit']);
        }

        if (isset($data['rate'])) {
            unset($data['rate']);
        }

        if (isset($data['taxname'])) {
            unset($data['taxname']);
        }

        $this->db->insert(db_prefix() . 'debitnotes', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {

            // Update next debit note number in settings
            $this->db->where('name', 'next_debit_note_number');
            $this->db->set('value', 'value+1', false);
            $this->db->update(db_prefix() . 'options');

            if (isset($custom_fields)) {
                handle_custom_fields_post($insert_id, $custom_fields);
            }

            foreach ($items as $key => $item) {
                if ($itemid = add_new_sales_item_post($item, $insert_id, 'debit_note')) {
                    _maybe_insert_post_item_tax($itemid, $item, $insert_id, 'debit_note');
                }
            }

            update_sales_total_tax_column($insert_id, 'debit_note', db_prefix() . 'debitnotes');

            log_activity('Debit Note Created [ID: ' . $insert_id . ']');

            hooks()->do_action('after_create_debit_note', $insert_id);

            if ($save_and_send === true) {
                $this->send_debit_note_to_vendor($insert_id, true, '', true);
            }

            return $insert_id;
        }

        return false;
    }

    /**
     * Update proposal
     * @param  mixed $data $_POST data
     * @param  mixed $id   proposal id
     * @return boolean
     */
    public function update($data, $id)
    {
        $affectedRows  = 0;
        $save_and_send = isset($data['save_and_send']);

        $items = [];
        if (isset($data['items'])) {
            $items = $data['items'];
            unset($data['items']);
        }

        $newitems = [];
        if (isset($data['newitems'])) {
            $newitems = $data['newitems'];
            unset($data['newitems']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        $data = $this->map_shipping_columns($data);

        $hook = hooks()->apply_filters('before_update_debit_note', [
            'data'          => $data,
            'items'         => $items,
            'newitems'      => $newitems,
            'removed_items' => isset($data['removed_items']) ? $data['removed_items'] : [],
        ], $id);

        $data                  = $hook['data'];
        $items                 = $hook['items'];
        $newitems              = $hook['newitems'];
        $data['removed_items'] = $hook['removed_items'];

        // Delete items checked to be removed from database
        foreach ($data['removed_items'] as $remove_item_id) {
            if (handle_removed_sales_item_post($remove_item_id, 'debit_note')) {
                $affectedRows++;
            }
        }
        unset($data['removed_items']);

        if (isset($data['item_select'])) {
            unset($data['item_select']);
        }

        if (isset($data['description'])) {
            unset($data['description']);
        }

        if (isset($data['long_description'])) {
            unset($data['long_description']);
        }

        if (isset($data['quantity'])) {
            unset($data['quantity']);
        }

        if (isset($data['unit'])) {
            unset($data['unit']);
        }

        if (isset($data['rate'])) {
            unset($data['rate']);
        }

        if (isset($data['taxname'])) {
            unset($data['taxname']);
        }

        if (isset($data['isedit'])) {
            unset($data['isedit']);
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'debitnotes', $data);

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }

        foreach ($items as $key => $item) {
            if (update_sales_item_post($item['itemid'], $item)) {
                $affectedRows++;
            }

            if (isset($item['custom_fields'])) {
                if (handle_custom_fields_post($item['itemid'], $item['custom_fields'])) {
                    $affectedRows++;
                }
            }

            if (!isset($item['taxname']) || (isset($item['taxname']) && count($item['taxname']) == 0)) {
                if (delete_taxes_from_item($item['itemid'], 'debit_note')) {
                    $affectedRows++;
                }
            } else {
                $item_taxes        = get_debit_note_item_taxes($item['itemid']);
                $_item_taxes_names = [];
                foreach ($item_taxes as $_item_tax) {
                    array_push($_item_taxes_names, $_item_tax['taxname']);
                }

                $i = 0;
                foreach ($_item_taxes_names as $_item_tax) {
                    if (!in_array($_item_tax, $item['taxname'])) {
                        $this->db->where('id', $item_taxes[$i]['id'])
                            ->delete(db_prefix() . 'item_tax');
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                    $i++;
                }
                if (_maybe_insert_post_item_tax($item['itemid'], $item, $id, 'debit_note')) {
                    $affectedRows++;
                }
            }
        }

        foreach ($newitems as $key => $item) {
            if ($new_item_added = add_new_sales_item_post($item, $id, 'debit_note')) {
                _maybe_insert_post_item_tax($new_item_added, $item, $id, 'debit_note');
                $affectedRows++;
            }
        }

        if ($save_and_send === true) {
            $this->send_debit_note_to_vendor($id, true, '', true);
        }

        if ($affectedRows > 0) {
            $this->update_debit_note_status($id);
            update_sales_total_tax_column($id, 'debit_note', db_prefix() . 'debitnotes');
        }

        if ($affectedRows > 0) {
            log_activity('Debit Note Updated [ID:' . $id . ']');
            hooks()->do_action('after_update_debit_note', $id);

            return true;
        }

        return false;
    }

    /**
     *  Delete debit note attachment
     * @param   mixed $id  attachmentid
     * @return  boolean
     */
    public function delete_attachment($id)
    {
        $attachment = $this->misc_model->get_file($id);

        $deleted = false;
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(get_upload_path_by_type('debit_note') . $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_activity('Debit Note Attachment Deleted [debite Note: ' . format_debit_note_number($attachment->rel_id) . ']');
            }
            if (is_dir(get_upload_path_by_type('debit_note') . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('debit_note') . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_by_type('debit_note') . $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    public function get_attachments($debit_note_id)
    {
        //get debit note
        $this->db->where('id', $debit_note_id);
        $debitnote = $this->db->get(db_prefix() . 'debitnotes')->row();
        //get customer linked files including debit note
        $fileArr = get_attachments_by_type('debit_note', $debitnote->id);
        return $fileArr;
    }

    /**
     * Delete debit note
     * @param  mixed $id debit note id
     * @return boolean
     */
    public function delete($id, $simpleDelete = false)
    {
        hooks()->do_action('before_debit_note_deleted', $id);
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'debitnotes', ["deleted_at" => date('Y-m-d H:i:s'), "deleted_by" => get_staff_full_name()]);
        //$this->db->delete(db_prefix() . 'debitnotes');
        if ($this->db->affected_rows() > 0) {
            $current_debit_note_number = get_option('next_debit_note_number');

            if ($current_debit_note_number > 1 && $simpleDelete == false && is_last_debit_note($id)) {
                // Decrement next debit note number
                $this->db->where('name', 'next_debit_note_number');
                $this->db->set('value', 'value-1', false);
                $this->db->update(db_prefix() . 'options');
            }

            //delete_tracked_emails($id, 'debit_note');

            // Delete the custom field values
            // $this->db->where('relid IN (SELECT id from ' . db_prefix() . 'itemable WHERE rel_type="debit_note" AND rel_id="' . $id . '")');
            // $this->db->where('fieldto', 'items');
            // $this->db->delete(db_prefix() . 'customfieldsvalues');

            // $this->db->where('relid', $id);
            // $this->db->where('fieldto', 'debit_note');
            // $this->db->delete(db_prefix() . 'customfieldsvalues');

            // $this->db->where('debit_id', $id);
            // $this->db->delete(db_prefix() . 'debits');

            // $this->db->where('debit_note_id', $id);
            // $this->db->delete(db_prefix() . 'debitnote_refunds');

            // $this->db->where('rel_id', $id);
            // $this->db->where('rel_type', 'debit_note');
            // $this->db->delete(db_prefix() . 'itemable');

            // $this->db->where('rel_id', $id);
            // $this->db->where('rel_type', 'debit_note');
            // $this->db->delete(db_prefix() . 'item_tax');

            // $attachments = $this->get_attachments($id);
            // foreach ($attachments as $attachment) {
            //     $this->delete_attachment($attachment['id']);
            // }

            // $this->db->where('rel_type', 'debit_note');
            // $this->db->where('rel_id', $id);
            // $this->db->delete(db_prefix() . 'reminders');

            hooks()->do_action('after_debit_note_deleted', $id);

            return true;
        }

        return false;
    }

    public function mark($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'debitnotes', ['status' => $status]);

        return $this->db->affected_rows() > 0 ? true : false;
    }

    public function total_remaining_debits_by_vendor($vendor_id)
    {
        $has_permission_view = has_permission('debit_notes', '', 'view');
        $this->db->select('total,id');
        $this->db->where('vendorid', $vendor_id);
        $this->db->where('deleted_at IS NULL');
        $this->db->where('status', 1);
        if (!$has_permission_view) {
            $this->db->where('addedfrom', get_staff_user_id());
        }
        $debits = $this->db->get(db_prefix() . 'debitnotes')->result_array();

        $total = $this->calc_remaining_debits($debits);

        return $total;
    }

    public function total_remaining_debits_by_debit_note($debit_note_id)
    {
        $this->db->select('total,id');
        $this->db->where('id', $debit_note_id);
        $debits = $this->db->get(db_prefix() . 'debitnotes')->result_array();

        $total = $this->calc_remaining_debits($debits);

        return $total;
    }

    private function calc_remaining_debits($debits)
    {
        $total       = 0;
        $debits_ids = [];

        $bcadd = function_exists('bcadd');
        foreach ($debits as $debit) {
            if ($bcadd) {
                $total = bcadd($total, $debit['total'], get_decimal_places());
            } else {
                $total += $debit['total'];
            }
            array_push($debits_ids, $debit['id']);
        }

        if (count($debits_ids) > 0) {
            $this->db->where('debit_id IN (' . implode(', ', $debits_ids) . ')');
            $this->db->where('deleted_at IS NULL');
            $applied_debits = $this->db->get(db_prefix() . 'debits')->result_array();
            $bcsub           = function_exists('bcsub');
            foreach ($applied_debits as $debit) {
                if ($bcsub) {
                    $total = bcsub($total, $debit['amount'], get_decimal_places());
                } else {
                    $total -= $debit['amount'];
                }
            }

            foreach ($debits_ids as $debit_note_id) {
                $total_refunds_by_debit_note = $this->total_refunds_by_debit_note($debit_note_id);
                if ($bcsub) {
                    $total = bcsub($total, $total_refunds_by_debit_note, get_decimal_places());
                } else {
                    $total -= $total_refunds_by_debit_note;
                }
            }
        }

        return $total;
    }

    public function delete_applied_debit($id, $debit_id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'debits', ["deleted_at" => date('Y-m-d H:i:s'), "deleted_by" => get_staff_full_name()]);
        if ($this->db->affected_rows() > 0) {
            log_activity('Debit Note Applied Debit Deleted [Debit Note ID: '. $debit_id. ',  Debit ID : '. $id. ']');
            $this->update_debit_note_status($debit_id);
        }
    }

    public function create_refund($id, $data)
    {
        if ($data['amount'] == 0) {
            return false;
        }

        $data['note'] = trim($data['note']);

        $this->db->insert(db_prefix() . 'debitnote_refunds', [
            'created_at'     => date('Y-m-d H:i:s'),
            'debit_note_id' => $id,
            'staff_id'       => $data['staff_id'],
            'refunded_on'    => $data['refunded_on'],
            'payment_mode'   => $data['payment_mode'],
            'amount'         => $data['amount'],
            'note'           => nl2br($data['note']),
        ]);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            log_activity('Debit Note Refund Created [Debit Note ID : ' . $id . ' ] [ID :' . $insert_id . ']');
            $this->update_debit_note_status($id);
            hooks()->do_action('debit_note_refund_created', ['data' => $data, 'debit_note_id' => $id]);
        }

        return $insert_id;
    }

    public function edit_refund($id, $data)
    {
        if ($data['amount'] == 0) {
            return false;
        }

        $refund = $this->get_refund($id);

        $data['note'] = trim($data['note']);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'debitnote_refunds', [
            'refunded_on'  => $data['refunded_on'],
            'payment_mode' => $data['payment_mode'],
            'amount'       => $data['amount'],
            'note'         => nl2br($data['note']),
        ]);

        $insert_id = $this->db->insert_id();

        if ($this->db->affected_rows() > 0) {
            $this->update_debit_note_status($refund->debit_note_id);
            log_activity('Debit Note Refund Updated [Debit Note ID : ' . $refund->debit_note_id . ' ] [ID :' . $id . ']');
            hooks()->do_action('debit_note_refund_updated', ['data' => $data, 'refund_id' => $refund->debit_note_id]);
        }

        return $insert_id;
    }

    public function get_refund($id)
    {
        $this->db->where('id', $id);

        return $this->db->get(db_prefix() . 'debitnote_refunds')->row();
    }

    public function get_refunds($debit_note_id)
    {
        $this->db->select(prefixed_table_fields_array(db_prefix() . 'debitnote_refunds', true) . ',' . db_prefix() . 'payment_modes.id as payment_mode_id, ' . db_prefix() . 'payment_modes.name as payment_mode_name');
        $this->db->where('debit_note_id', $debit_note_id);

        $this->db->join(db_prefix() . 'payment_modes', db_prefix() . 'payment_modes.id = ' . db_prefix() . 'debitnote_refunds.payment_mode', 'left');
        $this->db->where(db_prefix() . 'debitnote_refunds.deleted_at IS NULL');
        $this->db->order_by('refunded_on', 'desc');

        $refunds = $this->db->get(db_prefix() . 'debitnote_refunds')->result_array();

        $this->load->model('payment_modes_model');
        $payment_gateways = $this->payment_modes_model->get_payment_gateways(true);
        $i                = 0;

        foreach ($refunds as $refund) {
            if (is_null($refund['payment_mode_id'])) {
                foreach ($payment_gateways as $gateway) {
                    if ($refund['payment_mode'] == $gateway['id']) {
                        $refunds[$i]['payment_mode_id']   = $gateway['id'];
                        $refunds[$i]['payment_mode_name'] = $gateway['name'];
                    }
                }
            }
            $i++;
        }

        return $refunds;
    }

    public function delete_refund($refund_id, $debit_note_id)
    {
        $this->db->where('id', $refund_id);
        //$this->db->delete(db_prefix() . 'debitnote_refunds');
        $this->db->update(db_prefix() . 'debitnote_refunds', ["deleted_at" => date('Y-m-d H:i:s'), "deleted_by" => get_staff_full_name()]);
        if ($this->db->affected_rows() > 0) {
            $this->update_debit_note_status($debit_note_id);
            log_activity('Debit Note Refund Deleted [Debit Note ID : ' . $debit_note_id . '] [ID :' . $refund_id . ']');
            hooks()->do_action('debit_note_refund_deleted', ['refund_id' => $refund_id, 'debit_note_id' => $debit_note_id]);
            return true;
        }

        return false;
    }

    private function total_refunds_by_debit_note($id)
    {
        return sum_from_table(db_prefix() . 'debitnote_refunds', [
            'field' => 'amount',
            'where' => ['debit_note_id' => $id, 'deleted_at' => NULL],
        ]);
    }

    public function apply_debits($id, $data)
    {
        if ($data['amount'] == 0) {
            return false;
        }

        $this->db->insert(db_prefix() . 'debits', [
            'purchase_id'   => $data['purchase_id'],
            'debit_id'    => $id,
            'staff_id'     => get_staff_user_id(),
            'date'         => date('Y-m-d'),
            'date_applied' => date('Y-m-d H:i:s'),
            'amount'       => $data['amount'],
        ]);

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            $this->update_debit_note_status($id);
            hooks()->do_action('debits_applied', ['data' => $data, 'debit_note_id' => $id]);
            log_activity('Debit Applied to Purchase [ Purchase ID : ' . $data['purchase_id'] . ', Debit Note ID : ' . $id . ' ]');
        }

        return $insert_id;
    }

    private function total_debits_used_by_debit_note($id)
    {
        return sum_from_table(db_prefix() . 'debits', [
            'field' => 'amount',
            'where' => ['debit_id' => $id, 'deleted_at' => NULL],
        ]);
    }

    public function update_debit_note_status($id)
    {
        $total_refunds_by_debit_note = $this->total_refunds_by_debit_note($id);
        $total_debits_used           = $this->total_debits_used_by_debit_note($id);

        $status = 1;

        // sum from table returns null if nothing found
        if ($total_debits_used || $total_refunds_by_debit_note) {
            $compare = $total_debits_used + $total_refunds_by_debit_note;

            $this->db->select('total');
            $this->db->where('id', $id);
            $debit = $this->db->get(db_prefix() . 'debitnotes')->row();

            if ($debit) {
                if (function_exists('bccomp')) {
                    if (bccomp($debit->total, $compare, get_decimal_places()) === 0) {
                        $status = 2;
                    }
                } else {
                    if ($debit->total == $compare) {
                        $status = 2;
                    }
                }
            }
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'debitnotes', ['status' => $status]);

        return $this->db->affected_rows() > 0 ? true : false;
    }

    public function get_open_debits($vendor_id)
    {
        $has_permission_view = has_permission('debit_notes', '', 'view');
        $this->db->where('status', 1);
        $this->db->where('vendorid', $vendor_id);
        $this->db->where('deleted_at IS NULL');
        if (!$has_permission_view) {
            $this->db->where('addedfrom', get_staff_user_id());
        }
        $debits = $this->db->get(db_prefix() . 'debitnotes')->result_array();

        foreach ($debits as $key => $debit) {
            $debits[$key]['available_debits'] = $this->calculate_available_debits($debit['id'], $debit['total']);
        }

        return $debits;
    }

    public function get_applied_purchase_debits($purchase_id)
    {
        $this->db->order_by('date', 'desc');
        $this->db->where('purchase_id', $purchase_id);
        $this->db->where('deleted_at IS NULL');
        return $this->db->get(db_prefix() . 'debits')->result_array();
    }

    public function get_applied_debits($debit_id)
    {
        $this->db->where('debit_id', $debit_id);
        $this->db->order_by('date', 'desc');
        $this->db->where('deleted_at IS NULL');
        return $this->db->get(db_prefix() . 'debits')->result_array();
    }

    private function calculate_available_debits($debit_id, $debit_amount = false)
    {
        if ($debit_amount === false) {
            $this->db->select('total')
                ->from(db_prefix() . 'debitnotes')
                ->where('id', $debit_id);

            $debit_amount = $this->db->get()->row()->total;
        }

        $available_total = $debit_amount;

        $bcsub           = function_exists('bcsub');
        $applied_debits = $this->get_applied_debits($debit_id);


        foreach ($applied_debits as $debit) {
            if ($bcsub) {
                $available_total = bcsub($available_total, $debit['amount'], get_decimal_places());
            } else {
                $available_total -= $debit['amount'];
            }
        }

        $total_refunds = $this->total_refunds_by_debit_note($debit_id);

        if ($total_refunds) {
            if ($bcsub) {
                $available_total = bcsub($available_total, $total_refunds, get_decimal_places());
            } else {
                $available_total -= $total_refunds;
            }
        }

        return $available_total;
    }

    public function get_debits_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM ' . db_prefix() . 'debitnotes ORDER BY year DESC')->result_array();
    }

    private function map_shipping_columns($data)
    {
        if (!isset($data['include_shipping'])) {
            foreach ($this->shipping_fields as $_s_field) {
                if (isset($data[$_s_field])) {
                    $data[$_s_field] = null;
                }
            }
            $data['show_shipping_on_debit_note'] = 1;
            $data['include_shipping']             = 0;
        } else {
            $data['include_shipping'] = 1;
            // set by default for the next time to be checked
            if (isset($data['show_shipping_on_debit_note']) && ($data['show_shipping_on_debit_note'] == 1 || $data['show_shipping_on_debit_note'] == 'on')) {
                $data['show_shipping_on_debit_note'] = 1;
            } else {
                $data['show_shipping_on_debit_note'] = 0;
            }
        }

        return $data;
    }
}
