<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Contracts_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('contract_types_model');
    }

    /**
     * Get contract/s
     * @param  mixed  $id         contract id
     * @param  array   $where      perform where
     * @param  boolean $for_editor if for editor is false will replace the field if not will not replace
     * @return mixed
     */
    public function get($id = '', $where = [], $for_editor = false)
    {
        $this->db->select('*,' . db_prefix() . 'contracts_types.name as type_name,' . db_prefix() . 'contract_subtype.name as sub_type_name,' . db_prefix() . 'contracts.id as id, ' . db_prefix() . 'contracts.addedfrom, ' . db_prefix() . 'contracts.hash,' . db_prefix() . 'contracts.description');
        $this->db->where($where);
        $this->db->join(db_prefix() . 'contracts_types', '' . db_prefix() . 'contracts_types.id = ' . db_prefix() . 'contracts.contract_type', 'left');
        $this->db->join(db_prefix() . 'contract_subtype', '' . db_prefix() . 'contract_subtype.id = ' . db_prefix() . 'contracts.sub_type', 'left');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'contracts.client AND ' . db_prefix() . 'contracts.rel_type = "customer"', 'left');
        $this->db->join(db_prefix() . 'leads', db_prefix() . 'leads.id = ' . db_prefix() . 'contracts.client AND ' . db_prefix() . 'contracts.rel_type = "vendor"', 'left');
        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'contracts.id', $id);
            $contract = $this->db->get(db_prefix() . 'contracts')->row();
            if ($contract) {
                $contract->attachments = $this->get_contract_attachments('', $contract->id);
                if ($for_editor == false) {
                    $this->load->library('merge_fields/client_merge_fields');
                    $this->load->library('merge_fields/contract_merge_fields');
                    $this->load->library('merge_fields/other_merge_fields');
                    $this->load->library('merge_fields/vendors_merge_fields');
                    $this->load->library('merge_fields/contact_book_merge_fields');

                    $merge_fields = [];
                    $merge_fields = array_merge($merge_fields, $this->contract_merge_fields->format($id));
                    $merge_fields = array_merge($merge_fields, $this->client_merge_fields->format($contract->client));
                    $merge_fields = array_merge($merge_fields, $this->vendors_merge_fields->format($contract->client));
                    $merge_fields = array_merge($merge_fields, $this->contact_book_merge_fields->format($contract->client));
                    $merge_fields = array_merge($merge_fields, $this->other_merge_fields->format());
                    foreach ($merge_fields as $key => $val) {
                        if (stripos($contract->content, $key) !== false) {
                            $contract->content = str_ireplace($key, $val, $contract->content);
                        } else {
                            $contract->content = str_ireplace($key, '', $contract->content);
                        }
                    }
                }
                $contract->contacts = $this->get_contract_contacts($contract->id, $contract->rel_type);
            }

            return $contract;
        }
        $this->db->where(db_prefix() . 'clients.deleted_at IS NULL');
        //$this->db->where(db_prefix() . 'leads.isDeleted = "false"');
        $this->db->where(db_prefix() . 'contracts.deleted_at IS NULL');
        $this->db->order_by(db_prefix() . 'contracts.number', 'DESC');
        $contracts = $this->db->get(db_prefix() . 'contracts')->result_array();
        $i = 0;
        foreach ($contracts as $contract) {
            $contracts[$i]['attachments'] = $this->get_contract_attachments('', $contract['id']);
            $i++;
        }

        return $contracts;
    }

    /**
     * Select unique contracts years
     * @return array
     */
    public function get_contracts_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(datestart)) as year FROM ' . db_prefix() . 'contracts')->result_array();
    }

    /**
     * @param  integer ID
     * @return object
     * Retrieve contract attachments from database
     */
    public function get_contract_attachments($attachment_id = '', $id = '')
    {
        if (is_numeric($attachment_id)) {
            $this->db->where('id', $attachment_id);
            return $this->db->get(db_prefix() . 'files')->row();
        } else {
            //get contract data
            $this->db->where('id', $id);
            $contract = $this->db->get(db_prefix() . 'contracts')->row();
            $fileArr[] = get_attachments_by_type('contract', $id);
            //get contract linked customer files
            if ($contract->rel_type == "customer") {
                $fileArr[] = get_customer_linked_files($contract->client);
            } else if ($contract->rel_type == "vendor") {
                $fileArr[] = get_attachments_by_type('lead', $contract->client);
                $this->db->where('leadid', $contract->client);
                $client = $this->db->get(db_prefix() . 'clients')->row();
                if (!empty($client)) {
                    $fileArr[] = get_customer_linked_files($client->userid);
                }
            }
            $fileArr = array_values(attachement_unique_filter(array_merge(...$fileArr)));
            return $fileArr;
        }
    }

    /**
     * @param   array $_POST data
     * @return  integer Insert ID
     * Add new contract
     */
    public function add($data)
    {
        $data['dateadded'] = date('Y-m-d H:i:s');
        $data['addedfrom'] = get_staff_user_id();
        $data['contract_status'] = 'draft';

        $data['datestart'] = to_sql_date($data['datestart']);
        unset($data['attachment']);
        if ($data['dateend'] == '') {
            unset($data['dateend']);
        } else {
            $data['dateend'] = to_sql_date($data['dateend']);
        }
        $data['open_till'] = to_sql_date($data['open_till']);
        if (isset($data['trash']) && ($data['trash'] == 1 || $data['trash'] === 'on')) {
            $data['trash'] = 1;
        } else {
            $data['trash'] = 0;
        }

        if (isset($data['not_visible_to_client']) && ($data['not_visible_to_client'] == 1 || $data['not_visible_to_client'] === 'on')) {
            $data['not_visible_to_client'] = 1;
        } else {
            $data['not_visible_to_client'] = 0;
        }
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        $data['hash'] = app_generate_hash();

        $data = hooks()->apply_filters('before_contract_added', $data);

        $this->db->insert(db_prefix() . 'contracts', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            if (isset($custom_fields)) {
                handle_custom_fields_post($insert_id, $custom_fields);
            }
            hooks()->do_action('after_contract_added', $insert_id);
            log_activity('New Agreement Added [' . $data['subject'] . ']');

            return $insert_id;
        }

        return false;
    }

    public function update($data, $id, $isDelete = false)
    {
        $affectedRows = 0;

        if (!$isDelete) {
            $data['datestart'] = to_sql_date($data['datestart']);
            if ($data['dateend'] == '') {
                $data['dateend'] = null;
            } else {
                $data['dateend'] = to_sql_date($data['dateend']);
            }
            $data['open_till'] = to_sql_date($data['open_till']);
            if (isset($data['trash'])) {
                $data['trash'] = 1;
            } else {
                $data['trash'] = 0;
            }
            if (isset($data['not_visible_to_client'])) {
                $data['not_visible_to_client'] = 1;
            } else {
                $data['not_visible_to_client'] = 0;
            }

            $data = hooks()->apply_filters('before_contract_updated', $data, $id);

            if (isset($data['custom_fields'])) {
                $custom_fields = $data['custom_fields'];
                if (handle_custom_fields_post($id, $custom_fields)) {
                    $affectedRows++;
                }
                unset($data['custom_fields']);
            }

            $data['updated_by'] = get_staff_user_id();
            $data['updated_at'] = date('Y-m-d H:i:s');
        } else {
            $data['deleted_by'] = get_staff_full_name();
            $data['deleted_at'] = date('Y-m-d H:i:s');
            $data['prefix'] = null;
            $data['number'] = null;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'contracts', $data);

        if ($this->db->affected_rows() > 0) {
            hooks()->do_action('after_contract_updated', $id);
            if ($isDelete) {
                log_activity('Agreement Deleted [ ID : ' . $id . ']');
            } else {
                log_activity('Agreement Updated [ ID : ' . $id . ']');
            }
            return true;
        }

        return $affectedRows > 0;
    }

    public function clear_signature($contract_id, $sign_id = null, $is_return = true)
    {
        $this->db->select('*');
        if (!empty($sign_id)) {
            $this->db->where('id', $sign_id);
        } else {
            $this->db->where('contract_id', $contract_id);
        }
        $contract_signs = $this->db->get(db_prefix() . 'contracts_sign')->result();
        if (!empty($contract_signs)) {
            foreach ($contract_signs as $contract_sign) {
                $this->db->where('id', $contract_sign->id);
                $this->db->delete(db_prefix() . 'contracts_sign');
                if ($this->db->affected_rows() > 0) {
                    if (!empty($contract_sign->digital_signature)) {
                        unlink(get_upload_path_by_type('contract') . $contract_id . '/' . $contract_sign->digital_signature);
                    }
                    if (!empty($contract_sign->physical_signature)) {
                        unlink(get_upload_path_by_type('contract') . $contract_id . '/' . $contract_sign->physical_signature);
                    }
                    if (!empty($contract_sign->acceptance_selfie)) {
                        unlink(get_upload_path_by_type('contract') . $contract_id . '/' . $contract_sign->acceptance_selfie);
                    }
                    $this->check_and_update_signed_status($contract_id);
                    log_activity('Agreement Signature Clered [Agreement ID ' . $contract_id . ', Contact ID ' . $contract_sign->contact_id . ', Sign ID ' . $contract_sign->id . ']');
                }
            }
            if ($is_return) {
                return true;
            }
        }
        if ($is_return) {
            return false;
        }
    }

    /**
     * Add contract comment
     * @param mixed  $data   $_POST comment data
     * @param boolean $client is request coming from the client side
     */
    public function add_comment($data, $client = false)
    {
        if (is_staff_logged_in()) {
            $client = false;
        }

        if (isset($data['action'])) {
            unset($data['action']);
        }

        $data['dateadded'] = date('Y-m-d H:i:s');

        if ($client == false) {
            $data['rel_type'] = "staff";
            $data['staffid'] = get_staff_user_id();
        }

        $data['content'] = nl2br($data['content']);
        $this->db->insert(db_prefix() . 'contract_comments', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            $contract = $this->get($data['contract_id']);

            if (($contract->not_visible_to_client == '1' || $contract->trash == '1') && $client == false) {
                return true;
            }

            if ($client == true) {

                // Get creator
                $this->db->select('staffid, email, phonenumber');
                $this->db->where('staffid', $contract->addedfrom);
                $staff_contract = $this->db->get(db_prefix() . 'staff')->result_array();

                $notifiedUsers = [];

                foreach ($staff_contract as $member) {
                    $notified = add_notification([
                        'description' => 'not_contract_comment_from_client',
                        'touserid' => $member['staffid'],
                        'fromcompany' => 1,
                        'fromuserid' => null,
                        'link' => 'contracts/contract/' . $data['contract_id'],
                        'additional_data' => serialize([
                            $contract->subject,
                        ]),
                    ]);

                    if ($notified) {
                        array_push($notifiedUsers, $member['staffid']);
                    }

                    $template = mail_template('contract_comment_to_staff', $contract, $member);
                    $merge_fields = $template->get_merge_fields();
                    $template->send();

                    // Send email/sms to admin that client commented
                    $this->app_sms->trigger(SMS_TRIGGER_CONTRACT_NEW_COMMENT_TO_STAFF, $member['phonenumber'], $merge_fields);
                }
                pusher_trigger_notification($notifiedUsers);
            } else {
                if ($contract->rel_type == "vendor") {
                    $contacts = $this->leads_model->get('', [db_prefix() . "leads.id" => $contract->client]);
                } else if ($contract->rel_type == "customer") {
                    $contacts = $this->clients_model->get_contacts($contract->client, ['active' => 1, 'contract_emails' => 1]);
                } else if ($contract->rel_type == "contact_book") {
                    $this->load->model('contact_book_model');
                    $contacts[] = $this->contact_book_model->get($contract->client);
                }

                foreach ($contacts as $contact) {
                    if ($contract->rel_type == "vendor") {
                        $template = mail_template('contract_comment_to_vendor', $contract, $contact);
                    } else if ($contract->rel_type == "customer") {
                        $template = mail_template('contract_comment_to_customer', $contract, $contact);
                    } else if ($contract->rel_type == "contact_book") {
                        $template = mail_template('contract_comment_to_contact_book', $contract, $contact);
                    }
                    $merge_fields = $template->get_merge_fields();
                    $template->send();

                    $this->app_sms->trigger(SMS_TRIGGER_CONTRACT_NEW_COMMENT_TO_CUSTOMER, $contact['phonenumber'], $merge_fields);
                }
            }

            return true;
        }

        return false;
    }

    public function edit_comment($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'contract_comments', [
            'content' => nl2br($data['content']),
        ]);

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get contract comments
     * @param  mixed $id contract id
     * @return array
     */
    public function get_comments($id)
    {
        $this->db->where('contract_id', $id);
        $this->db->order_by('dateadded', 'ASC');

        return $this->db->get(db_prefix() . 'contract_comments')->result_array();
    }

    /**
     * Get contract single comment
     * @param  mixed $id  comment id
     * @return object
     */
    public function get_comment($id)
    {
        $this->db->where('id', $id);

        return $this->db->get(db_prefix() . 'contract_comments')->row();
    }

    /**
     * Remove contract comment
     * @param  mixed $id comment id
     * @return boolean
     */
    public function remove_comment($id)
    {
        $comment = $this->get_comment($id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'contract_comments');
        if ($this->db->affected_rows() > 0) {
            log_activity('Agreement Comment Removed [Agreement ID:' . $comment->contract_id . ', Comment Content: ' . $comment->content . ']');

            return true;
        }

        return false;
    }

    public function copy($id)
    {
        $contract = $this->get($id, [], true);
        $fields = $this->db->list_fields(db_prefix() . 'contracts');
        $newContactData = [];

        foreach ($fields as $field) {
            if (isset($contract->$field)) {
                $newContactData[$field] = $contract->$field;
            }
        }

        unset($newContactData['id']);

        $newContactData['trash'] = 0;
        $newContactData['isexpirynotified'] = 0;
        $newContactData['isexpirynotified'] = 0;
        $newContactData['signed'] = 0;
        $newContactData['signature'] = null;

        $newContactData = array_merge($newContactData, get_acceptance_info_array(true));

        if ($contract->dateend) {
            $dStart = new DateTime($contract->datestart);
            $dEnd = new DateTime($contract->dateend);
            $dDiff = $dStart->diff($dEnd);
            $newContactData['dateend'] = _d(date('Y-m-d', strtotime(date('Y-m-d', strtotime('+' . $dDiff->days . 'DAY')))));
        } else {
            $newContactData['dateend'] = '';
        }

        $newId = $this->add($newContactData);

        if ($newId) {
            $custom_fields = get_custom_fields('contracts');
            foreach ($custom_fields as $field) {
                $value = get_custom_field_value($id, $field['id'], 'contracts', false);
                if ($value != '') {
                    $this->db->insert(db_prefix() . 'customfieldsvalues', [
                        'relid' => $newId,
                        'fieldid' => $field['id'],
                        'fieldto' => 'contracts',
                        'value' => $value,
                    ]);
                }
            }
        }

        return $newId;
    }

    /**
     * @param  integer ID
     * @return boolean
     * Delete contract, also attachment will be removed if any found
     */
    public function delete($id)
    {
        hooks()->do_action('before_contract_deleted', $id);
        $contract = $this->get($id);
        $this->db->where('id', $id);
        //$this->db->delete(db_prefix() . 'contracts');
        $this->db->update(db_prefix() . 'contracts', ["deleted_at" => date('Y-m-d H:i:s'), "deleted_by" => get_staff_full_name(), "prefix" => null, "number" => null]);
        if ($this->db->affected_rows() > 0) {
            // $this->db->where('contract_id', $id);
            // $this->db->delete(db_prefix() . 'contract_comments');

            // Delete the custom field values
            // $this->db->where('relid', $id);
            // $this->db->where('fieldto', 'contracts');
            // $this->db->delete(db_prefix() . 'customfieldsvalues');

            // $this->db->where('rel_id', $id);
            // $this->db->where('rel_type', 'contract');
            // $attachments = $this->db->get(db_prefix() . 'files')->result_array();
            // foreach ($attachments as $attachment) {
            //     $this->delete_contract_attachment($attachment['id']);
            // }

            // $this->db->where('rel_id', $id);
            // $this->db->where('rel_type', 'contract');
            // $this->db->delete(db_prefix() . 'notes');

            // $this->db->where('contractid', $id);
            // $this->db->delete(db_prefix() . 'contract_renewals');
            // Get related tasks
            // $this->db->where('rel_type', 'contract');
            // $this->db->where('rel_id', $id);
            // $tasks = $this->db->get(db_prefix() . 'tasks')->result_array();
            // foreach ($tasks as $task) {
            //     $this->tasks_model->delete_task($task['id']);
            // }

            // delete_tracked_emails($id, 'contract');

            log_activity('Agreement Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Function that send contract to customer
     * @param  mixed  $id        contract id
     * @param  boolean $attachpdf to attach pdf or not
     * @param  string  $cc        Email CC
     * @return boolean
     */
    public function send_contract_to_client($id, $attachpdf = true, $cc = '')
    {
        set_time_limit(0);
        $contract = $this->get($id);

        if ($attachpdf) {
            set_mailing_constant();
            $pdf = contract_mpdf($contract);
            $attach = $pdf->Output(slug_it($contract->subject) . '.pdf', 'S');
        }

        $sent_to = $this->input->post('sent_to');
        $sent = false;

        if (is_array($sent_to)) {
            $i = 0;
            foreach ($sent_to as $contact_id) {
                if ($contact_id != '') {
                    if ($contract->rel_type == "customer") {
                        $contact = $this->clients_model->get_contact($contact_id);
                    } else if ($contract->rel_type == "vendor") {
                        $this->load->model('leads_model');
                        $contact = $this->leads_model->get($contact_id);
                    } else if ($contract->rel_type == "contact_book") {
                        $this->load->model('contact_book_model');
                        $contact = $this->contact_book_model->get($contract->client);
                    }

                    // Send cc only for the first contact
                    if (!empty($cc) && $i > 0) {
                        $cc = '';
                    }
                    $staffid = get_staff_user_id();
                    $staff = $this->staff_model->get($staffid, ['active' => 1]);
                    $userName = $staff->firstname . ' ' . $staff->lastname;
                    log_message('info', 'Reply-To: ' . $staff->email . ' - ' . $userName);

                    if ($contract->rel_type == "customer") {
                        $template = mail_template('contract_send_to_customer', $contract, $contact, $cc, $staff->email);
                    } else if ($contract->rel_type == "vendor") {
                        $template = mail_template('contract_send_to_vendor', $contract, $contact, $cc, $staff->email);
                    } else if ($contract->rel_type == "contact_book") {
                        $template = mail_template('contract_send_to_contact_book', $contract, (object) $contact, $cc, $staff->email);
                    }
                    if ($attachpdf) {
                        $template->add_attachment([
                            'attachment' => $attach,
                            'filename' => slug_it($contract->subject) . '.pdf',
                            'type' => 'application/pdf',
                        ]);
                    }
                    if ($template->send()) {
                        $this->db->where('id', $id);
                        $this->db->update(db_prefix() . 'contracts', [
                            'contract_status' => 'sent',
                            'open_till' => date('Y-m-d H:i:s', strtotime('+24 hours'))
                        ]);
                        $sent = true;
                    }
                }
                $i++;
            }
        } else {
            return false;
        }
        if ($sent) {
            return true;
        }

        return false;
    }

    public function send_verification_mail_to_client($id, $cc = '', $attachpdf = false)
    {
        set_time_limit(0);
        $contract = $this->get($id);

        if ($attachpdf) {
            set_mailing_constant();
            $pdf = contract_mpdf($contract);
            $attach = $pdf->Output(slug_it($contract->subject) . '.pdf', 'S');
        }

        $sent_to = $this->input->post('sent_to');
        $sent = false;
        if (is_array($sent_to)) {
            $i = 0;
            foreach ($sent_to as $contact_id) {
                if ($contact_id != '') {
                    if ($contract->rel_type == "customer") {
                        $contact = $this->clients_model->get_contact($contact_id);
                    } else if ($contract->rel_type == "vendor") {
                        $this->load->model('leads_model');
                        $contact = $this->leads_model->get($contact_id);
                    } else if ($contract->rel_type == "contact_book") {
                        $this->load->model('contact_book_model');
                        $contact = (object) $this->contact_book_model->get($contact_id);
                    }

                    if (!empty($cc) && $i > 0) {
                        $cc = '';
                    }
                    $staffid = get_staff_user_id();
                    $staff = $this->staff_model->get($staffid, ['active' => 1]);
                    $userName = $staff->firstname . ' ' . $staff->lastname;
                    log_message('info', 'Reply-To: ' . $staff->email . ' - ' . $userName);
                    if ($contract->rel_type == "customer") {
                        $template = mail_template('contract_send_verified_send_to_customer', $contract, $contact, $cc, $staff->email);
                    } else if ($contract->rel_type == "vendor") {
                        $template = mail_template('contract_send_verified_send_to_vendor', $contract, $contact, $cc, $staff->email);
                    } else if ($contract->rel_type == "contact_book") {
                        $template = mail_template('contract_send_verified_send_to_contact_book', $contract, $contact, $cc, $staff->email);
                    }
                    if ($attachpdf) {
                        $template->add_attachment([
                            'attachment' => $attach,
                            'filename' => slug_it($contract->subject) . '.pdf',
                            'type' => 'application/pdf',
                        ]);
                    }
                    if ($template->send()) {
                        $sent = true;
                    }
                }
                $i++;
            }
            if ($sent) {
                $this->db->where('id', $id);
                $this->db->update(db_prefix() . 'contracts', [
                    "is_verified" => "1",
                    "contract_status" => "verified",
                    "verified_timestamp" => date('Y-m-d H:i:s'),
                    "verified_by" => get_staff_user_id()
                ]);
            }
        } else {
            return false;
        }
        if ($sent) {
            return true;
        }

        return false;
    }

    /**
     * Delete contract attachment
     * @param  mixed $attachment_id
     * @return boolean
     */
    public function delete_contract_attachment($attachment_id)
    {
        $deleted = false;
        $attachment = $this->get_contract_attachments($attachment_id);

        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(get_upload_path_by_type('contract') . $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_activity('Agreement Attachment Deleted [ContractID: ' . $attachment->rel_id . ']');
            }

            if (is_dir(get_upload_path_by_type('contract') . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('contract') . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_by_type('contract') . $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    /**
     * Renew contract
     * @param  mixed $data All $_POST data
     * @return mixed
     */
    public function renew($data)
    {
        $clearSignature = isset($data['renew_clear_signature']);
        if ($clearSignature) {
            unset($data['renew_clear_signature']);
        }
       
        $data['new_start_date'] = to_sql_date($data['new_start_date']);
        $data['new_end_date'] = to_sql_date($data['new_end_date']);
        $data['date_renewed'] = date('Y-m-d H:i:s');
        $data['renewed_by'] = get_staff_full_name(get_staff_user_id());
        $data['renewed_by_staff_id'] = get_staff_user_id();
        if (!is_date($data['new_end_date'])) {
            unset($data['new_end_date']);
        }
        // get the original contract so we can check if is expiry notified on delete the expiry to revert
        $_contract = $this->get($data['contractid']);
        $data['is_on_old_expiry_notified'] = $_contract->isexpirynotified;
        $this->db->insert(db_prefix() . 'contract_renewals', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->db->where('id', $data['contractid']);
            $_data = [
                'datestart' => $data['new_start_date'],
                'contract_value' => $data['new_value'],
                'isexpirynotified' => 0,
            ];

            if (isset($data['new_end_date'])) {
                $_data['dateend'] = $data['new_end_date'];
            }

            $this->db->update(db_prefix() . 'contracts', $_data);
            if ($this->db->affected_rows() > 0) {
                if ($clearSignature) {
                    $this->clear_signature($data['contractid'], null, false);
                }
                log_activity('Agreement Renewed [ID: ' . $data['contractid'] . ']');
                return true;
            }
            // delete the previous entry
            $this->db->where('id', $insert_id);
            $this->db->delete(db_prefix() . 'contract_renewals');

            return false;
        }

        return false;
    }

    /**
     * Delete contract renewal
     * @param  mixed $id         renewal id
     * @param  mixed $contractid contract id
     * @return boolean
     */
    public function delete_renewal($id, $contractid)
    {
        // check if this renewal is last so we can revert back the old values, if is not last we wont do anything
        $this->db->select('id')->from(db_prefix() . 'contract_renewals')->where('contractid', $contractid)->order_by('id', 'desc')->limit(1);
        $query = $this->db->get();
        $last_contract_renewal = $query->row()->id;
        $is_last = false;
        if ($last_contract_renewal == $id) {
            $is_last = true;
            $this->db->where('id', $id);
            $original_renewal = $this->db->get(db_prefix() . 'contract_renewals')->row();
        }
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'contract_renewals');
        if ($this->db->affected_rows() > 0) {
            if ($is_last == true) {
                $this->db->where('id', $contractid);
                $data = [
                    'datestart' => $original_renewal->old_start_date,
                    'contract_value' => $original_renewal->old_value,
                    'isexpirynotified' => $original_renewal->is_on_old_expiry_notified,
                ];
                if ($original_renewal->old_end_date != '0000-00-00') {
                    $data['dateend'] = $original_renewal->old_end_date;
                }
                $this->db->update(db_prefix() . 'contracts', $data);
            }
            log_activity('Agreement Renewed [RenewalID: ' . $id . ', ContractID: ' . $contractid . ']');

            return true;
        }

        return false;
    }

    /**
     * Get contract renewals
     * @param  mixed $id contract id
     * @return array
     */
    public function get_contract_renewal_history($id)
    {
        $this->db->where('contractid', $id);
        $this->db->order_by('date_renewed', 'asc');

        return $this->db->get(db_prefix() . 'contract_renewals')->result_array();
    }

    /**
     * @param  integer ID (optional)
     * @return mixed
     * Get contract type object based on passed id if not passed id return array of all types
     */
    public function get_contract_types($id = '')
    {
        return $this->contract_types_model->get($id);
    }

    /**
     * @param  integer ID
     * @return mixed
     * Delete contract type from database, if used return array with key referenced
     */
    public function delete_contract_type($id)
    {
        return $this->contract_types_model->delete($id);
    }

    /**
     * Add new contract type
     * @param mixed $data All $_POST data
     */
    public function add_contract_type($data)
    {
        return $this->contract_types_model->add($data);
    }


    public function update_contract_type($data, $id)
    {
        return $this->contract_types_model->update($data, $id);
    }

    public function add_contract_sub_type($data)
    {
        return $this->contract_types_model->add_sub_type($data);
    }


    public function update_contract_sub_type($data, $id)
    {
        return $this->contract_types_model->update_sub_type($data, $id);
    }

    /**
     * Get contract types data for chart
     * @return array
     */
    public function get_contracts_types_chart_data()
    {
        return $this->contract_types_model->get_chart_data();
    }

    /**
     * Get contract types values for chart
     * @return array
     */
    public function get_contracts_types_values_chart_data()
    {
        return $this->contract_types_model->get_values_chart_data();
    }

    public function update_contract($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'contracts', $data);
        $affectedRows = $this->db->affected_rows();
        if ($affectedRows > 0) {
            log_activity('Agreement Updated [ ID : ' . $id . ']');
            return true;
        }
        return false;
    }

    public function check_and_update_signed_status($contract_id)
    {
        $contract = $this->get($contract_id);
        if ($contract->rel_type == "customer") {
            $this->db->select('COUNT(tblcontacts.id) as total_contacts');
            $this->db->from(db_prefix() . 'contacts');
            $this->db->join(db_prefix() . 'contracts', db_prefix() . 'contracts.client = ' . db_prefix() . 'contacts.userid');
            $this->db->where(db_prefix() . 'contracts.id', $contract_id);
            $this->db->where(db_prefix() . 'contacts.deleted_at IS NULL');
            $total_contacts_query = $this->db->get();
            $total_contacts = $total_contacts_query->row()->total_contacts;
        } else if ($contract->rel_type == "vendor") {
            $this->db->select('COUNT(tblleads.id) as total_contacts');
            $this->db->from(db_prefix() . 'leads');
            $this->db->join(db_prefix() . 'contracts', db_prefix() . 'contracts.client = ' . db_prefix() . 'leads.id');
            $this->db->where(db_prefix() . 'contracts.id', $contract_id);
            $this->db->where(db_prefix() . 'contracts.deleted_at IS NULL');
            $total_contacts_query = $this->db->get();
            $total_contacts = $total_contacts_query->row()->total_contacts;
        } else if ($contract->rel_type == "contact_book") {
            $this->db->select('COUNT(tblcontact_book.id) as total_contacts');
            $this->db->from(db_prefix() . 'contact_book');
            $this->db->join(db_prefix() . 'contracts', db_prefix() . 'contracts.client = ' . db_prefix() . 'contact_book.id');
            $this->db->where(db_prefix() . 'contracts.id', $contract_id);
            $this->db->where(db_prefix() . 'contracts.deleted_at IS NULL');
            $total_contacts_query = $this->db->get();
            $total_contacts = $total_contacts_query->row()->total_contacts;
        }

        $this->db->select('COUNT(tblcontracts_sign.id) as total_signed_contacts');
        $this->db->from(db_prefix() . 'contracts_sign');
        $this->db->where(db_prefix() . 'contracts_sign.contract_id', $contract_id);
        $signed_contacts_query = $this->db->get();
        $total_signed_contacts = $signed_contacts_query->row()->total_signed_contacts;

        if ($total_contacts != $total_signed_contacts) {
            $data = array(
                'signed' => 0,
                'is_verified' => '0',
                'verified_timestamp' => NULL,
                'verified_by' => NULL,
                'contract_status' => 'sent',
            );
            $this->db->where('id', $contract_id);
            $this->db->update(db_prefix() . 'contracts', $data);
            return false;
        }

        $this->db->select(db_prefix() . 'contracts_sign.signed');
        $this->db->from(db_prefix() . 'contracts_sign');
        $this->db->where(db_prefix() . 'contracts_sign.contract_id', $contract_id);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $signed_statuses = $query->result_array();
            foreach ($signed_statuses as $status) {
                if ($status['signed'] == 0) {
                    $data = array(
                        'signed' => 0,
                        'is_verified' => '0',
                        'verified_timestamp' => NULL,
                        'verified_by' => NULL,
                        'contract_status' => 'sent',
                    );
                    $this->db->where('id', $contract_id);
                    $this->db->update(db_prefix() . 'contracts', $data);
                    return false;
                }
            }
            $data = array(
                'signed' => 1,
                'contract_status' => 'in review',
            );
            $this->db->where('id', $contract_id);
            $this->db->update(db_prefix() . 'contracts', $data);
            return true;
        }

        $data = array(
            'signed' => 0,
            'contract_status' => 'sent'
        );
        $this->db->where('id', $contract_id);
        $this->db->update(db_prefix() . 'contracts', $data);
        return false;
    }


    public function check_email($contract_id, $email)
    {
        $contract = $this->get($contract_id);
        if ($contract->rel_type == "customer") {
            $this->db->select('tblcontacts.*, tblcontracts_sign.signed');
            $this->db->from(db_prefix() . 'contacts');
            $this->db->join(db_prefix() . 'contracts', db_prefix() . 'contracts.client = ' . db_prefix() . 'contacts.userid');
            $this->db->join(db_prefix() . 'contracts_sign', db_prefix() . 'contracts_sign.contact_id = ' . db_prefix() . 'contacts.id', 'left');
            $this->db->where(db_prefix() . 'contracts.id', $contract_id);
            $this->db->where(db_prefix() . 'contacts.email', $email);
            $this->db->where(db_prefix() . 'contacts.deleted_at IS NULL');
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                return $query->row_array();
            }
        } else if ($contract->rel_type == "vendor") {
            $this->db->select("leads.name, leads.id,leads.email,contracts_sign.signed");
            $this->db->from(db_prefix() . 'leads leads');
            $this->db->join(db_prefix() . 'contracts contracts', 'contracts.client = leads.id');
            $this->db->join(db_prefix() . 'contracts_sign contracts_sign', 'leads.id = contracts_sign.vendor_id', 'left');
            $this->db->where('contracts.id', $contract_id);
            $this->db->where('leads.email', $email);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                return $query->row_array();
            }
        } else if ($contract->rel_type == "contact_book") {
            $this->db->select("CONCAT(contact_book.firstname, ' ',contact_book.lastname) AS name,contracts_sign.signed, contact_book.*");
            $this->db->from(db_prefix() . 'contact_book contact_book');
            $this->db->join(db_prefix() . 'contracts contracts', 'contracts.client = contact_book.id');
            $this->db->join(db_prefix() . 'contracts_sign contracts_sign', 'contact_book.id = contracts_sign.contact_book_id', 'left');
            $this->db->where('contracts.id', $contract_id);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                return $query->row_array();
            }
        }
        return false;
    }

    public function get_contract_contacts($contract_id, $rel_type)
    {
        if ($rel_type == "customer") {
            $this->db->select("CONCAT(contacts.firstname, ' ', contacts.lastname) AS name, contracts_sign.*, , clients.company");
            $this->db->from(db_prefix() . 'contacts contacts');
            $this->db->join(db_prefix() . 'contracts contracts', 'contracts.client = contacts.userid');
            $this->db->join(db_prefix() . 'contracts_sign contracts_sign', 'contracts_sign.contact_id = contacts.id', 'left');
            $this->db->join(db_prefix() . 'clients clients', 'contacts.userid = clients.userid', 'left');
            $this->db->where('contacts.deleted_at IS NULL');
            $this->db->where('contracts.id', $contract_id);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                return $query->result_array();
            }
        } else if ($rel_type == "vendor") {
            $this->db->select("leads.name, contracts_sign.*, leads.company");
            $this->db->from(db_prefix() . 'leads leads');
            $this->db->join(db_prefix() . 'contracts contracts', 'contracts.client = leads.id');
            $this->db->join(db_prefix() . 'contracts_sign contracts_sign', 'leads.id = contracts_sign.vendor_id', 'left');
            $this->db->where('contracts.id', $contract_id);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                return $query->result_array();
            }
        } else if ($rel_type == "contact_book") {
            $this->db->select("CONCAT(contact_book.firstname, ' ',contact_book.lastname) AS name, contracts_sign.*, contact_book.company");
            $this->db->from(db_prefix() . 'contact_book contact_book');
            $this->db->join(db_prefix() . 'contracts contracts', 'contracts.client = contact_book.id');
            $this->db->join(db_prefix() . 'contracts_sign contracts_sign', 'contact_book.id = contracts_sign.contact_book_id', 'left');
            $this->db->where('contracts.id', $contract_id);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                return $query->result_array();
            }
        }
        return false;
    }

    public function insert_sign_data($data)
    {
        $this->db->insert(db_prefix() . 'contracts_sign', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('Agreement Signed : Agreement ID [' . $data['contract_id'] . '] Contact ID [' . $data['contact_id'] . ']');
            return $insert_id;
        }
        return false;
    }

    public function get_single_contract($id)
    {
        $this->db->where(db_prefix() . 'contracts.id', $id);
        $contract = $this->db->get(db_prefix() . 'contracts')->row();
        return $contract;
    }

    public function get_contract_sign_count($contract_id)
    {
        $contract_data = $this->get($contract_id);
        if ($contract_data->rel_type == "customer") {
            $this->db->select('COUNT(tblcontacts.id) as total_contacts');
            $this->db->from(db_prefix() . 'contacts');
            $this->db->join(db_prefix() . 'contracts', db_prefix() . 'contracts.client = ' . db_prefix() . 'contacts.userid');
            $this->db->where(db_prefix() . 'contracts.id', $contract_id);
            $this->db->where(db_prefix() . 'contacts.deleted_at IS NULL');
            $total_contacts_query = $this->db->get();
            $total_contacts = $total_contacts_query->row()->total_contacts;
        } else {
            $this->db->select('COUNT(tblleads.id) as total_contacts');
            $this->db->from(db_prefix() . 'leads');
            $this->db->join(db_prefix() . 'contracts', db_prefix() . 'contracts.client = ' . db_prefix() . 'leads.id');
            $this->db->where(db_prefix() . 'contracts.id', $contract_id);
            $this->db->where(db_prefix() . 'contracts.deleted_at IS NULL');
            $total_contacts_query = $this->db->get();
            $total_contacts = $total_contacts_query->row()->total_contacts;
        }

        $this->db->select('COUNT(tblcontracts_sign.id) as total_signed_contacts');
        $this->db->from(db_prefix() . 'contracts_sign');
        $this->db->where(db_prefix() . 'contracts_sign.contract_id', $contract_id);
        $signed_contacts_query = $this->db->get();
        $total_signed_contacts = $signed_contacts_query->row()->total_signed_contacts;
        $data = array(
            'total_partner' => $total_contacts,
            'signed_partner' => $total_signed_contacts,
        );
        return $data;
    }

    public function get_payment_terms($contract_id)
    {
        $this->db->where('contract_id', $contract_id);
        $this->db->where('deleted_at IS NULL');
        $this->db->order_by('id', 'ASC');
        return $this->db->get(db_prefix() . 'contract_payments')->result_array();
    }
    public function get_payment_term($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'contract_payments')->row_array();
    }

    public function insert_payment_term($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'contract_payments', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            update_payment_terms_status($data['contract_id']);
            log_activity('Agreement payment terms record created. Payment Terms Record ID [' . $insert_id . '] Agreement ID [' . $data['contract_id'] . '] ');
            return true;
        }
        return false;
    }

    public function update_payment_terms($data, $id, $is_delete = false)
    {
        $paymentData = $this->get_payment_term($id);
        if ($is_delete) {
            $data['deleted_at'] = date('Y-m-d H:i:s');
            $data['deleted_by'] = get_staff_full_name();
        } else {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['updated_by'] = get_staff_user_id();
        }
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'contract_payments', $data);
        if ($this->db->affected_rows() > 0) {
            update_payment_terms_status($data['contract_id']);
            if ($is_delete) {
                log_activity('Agreement payment terms record deleted. Payment Terms Record ID [' . $id . '] Agreement ID [' . $paymentData['contract_id'] . '] ');
            } else {
                log_activity('Agreement payment terms record updated. Payment Terms Record ID [' . $id . '] Agreement ID [' . $paymentData['contract_id'] . '] ');
            }
            return true;
        }
        return false;
    }

    public function send_contract_payment_term_reminder_email()
    {
        $current_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+3 days'));
        $this->db->select('cp.*, c.subject, c.client');
        $this->db->from(db_prefix() . 'contract_payments cp');
        $this->db->join(db_prefix() . 'contracts c', 'cp.contract_id = c.id');
        $this->db->where('cp.scheduled_payment_date >=', $current_date);
        $this->db->where('cp.scheduled_payment_date <=', $end_date);
        $this->db->where('cp.status !=', 'Received');
        $this->db->where('c.payment_reminder', '1');
        $this->db->where('c.contract_status', 'verified');
        $this->db->where('cp.deleted_at IS NULL');
        $this->db->where('c.deleted_at IS NULL');
        $query = $this->db->get();
        $paymentTermsData = $query->result_array();
        if (!empty($paymentTermsData)) {
            foreach ($paymentTermsData as $key => $termData) {
                $contract = $this->get($termData['id']);
                $this->db->where('id', get_primary_contact_user_id($termData['client']));
                $contact = $this->db->get(db_prefix() . 'contacts')->row();
                if (!empty($contact)) {
                    $check = mail_template('contract_payment_terms_reminder', $contract, $contact, $termData['id'])->send();
                    if ($check) {
                        log_activity("Agreement ID [" . $termData['contract_id'] . "] Payment Term ID [" . $termData['id'] . "] -  Payment Term Reminder Email Sucessfully Send.");
                    } else {
                        log_activity("Agreement ID [" . $termData['contract_id'] . "] Payment Term ID [" . $termData['id'] . "] - Payment Term Reminder Email Send Failed.");
                    }
                } else {
                    log_activity("Agreement ID [" . $termData['contract_id'] . "] Payment Term ID [" . $termData['id'] . "] - Payment Term Reminder Email Send Failed due to cutomer primary contact not found");
                }
            }
        }
    }

    public function send_contract_payment_over_due_notice_email()
    {
        $current_date = date('Y-m-d');
        $this->db->select('cp.*, c.subject, c.client');
        $this->db->from(db_prefix() . 'contract_payments cp');
        $this->db->join(db_prefix() . 'contracts c', 'cp.contract_id = c.id');
        $this->db->where('cp.scheduled_payment_date <', $current_date);
        $this->db->where('cp.status !=', 'Received');
        $this->db->where('c.payment_reminder', '1');
        $this->db->where('c.contract_status', 'verified');
        $this->db->where('cp.deleted_at IS NULL');
        $this->db->where('c.deleted_at IS NULL');

        $query = $this->db->get();
        $paymentTermsData = $query->result_array();

        if (!empty($paymentTermsData)) {
            foreach ($paymentTermsData as $key => $termData) {
                $contract = $this->get($termData['contract_id']);
                $this->db->where('id', get_primary_contact_user_id($termData['client']));
                $contact = $this->db->get(db_prefix() . 'contacts')->row();
                if (!empty($contact)) {
                    $check = mail_template('contract_payment_terms_overdue_notice', $contract, $contact, $termData['id'])->send();
                    if ($check) {
                        log_activity("Agreement ID [" . $termData['contract_id'] . "] Payment Term ID [" . $termData['id'] . "] - Payment Term Over Due Notice Email Successfully Sent.");
                    } else {
                        log_activity("Agreement ID [" . $termData['contract_id'] . "] Payment Term ID [" . $termData['id'] . "] - Payment Term Over Due Notice Email Send Failed.");
                    }
                } else {
                    log_activity("Agreement ID [" . $termData['contract_id'] . "] Payment Term ID [" . $termData['id'] . "] - Payment Term Over Due Notice Email Send Failed Due To Customer Primary Contact Not Found.");
                }
            }
        }
    }
}
