<?php

header('Content-Type: text/html; charset=utf-8');
defined('BASEPATH') or exit('No direct script access allowed');

class Leads extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('leads_model');
        $this->load->model('leadsnew_model');
        $this->load->model('vendors_model');
    }

    /* List all leads */
    public function index($id = '')
    {
        close_setup_menu();

        if (!is_staff_member()) {
            access_denied('Leads');
        }

        $data['switch_kanban'] = true;

        if ($this->session->userdata('leads_kanban_view') == 'true') {
            $data['switch_kanban'] = false;
            $data['bodyclass'] = 'kan-ban-body';
        }
        $data['module_type'] = "leads";
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

        $data['title'] = _l('leads');
        // in case accesed the url leads/index/ directly with id - used in search
        $data['leadid'] = $id;
        $this->load->view('admin/leads/manage_leads', $data);
    }

    public function table()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }
        $this->app->get_table_data('leads');
    }

    public function kanban()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }
        $data['statuses'] = $this->leads_model->get_status();
        echo $this->load->view('admin/leads/kan-ban', $data, true);
    }

    /* Add or update lead */
    public function lead($id = '')
    {
        if ($this->input->get('vendor')) {
            if (!is_staff_member() || ($id != '' && !$this->vendors_model->staff_can_access_lead($id))) {
                ajax_access_denied();
            }
        } else {
            if (!is_staff_member() || ($id != '' && !$this->leads_model->staff_can_access_lead($id))) {
                ajax_access_denied();
            }
        }

        if ($this->input->post()) {
            $post_data = $this->input->post();
            if ($id == '') {
                $id = $this->leads_model->add($this->input->post());
                if (isset($post_data['is_vendor']) && $post_data['is_vendor']) {
                    $entity = 'vendor';
                } else {
                    $entity = 'lead';
                }

                if ($this->session->userdata('duplicate_flash_message')) {
                    $message = $this->session->userdata('duplicate_flash_message');
                    $this->session->unset_userdata('duplicate_flash_message');
                } else {
                    $message = $id ? _l('added_successfully', _l($entity)) : '';
                }
                echo json_encode([
                    'success' => $id ? true : false,
                    'id' => $id,
                    'message' => $message,
                    'leadView' => $id ? $this->_get_lead_data($id) : [],
                ]);
            } else {
                $emailOriginal = $this->db->select('email')->where('id', $id)->get(db_prefix() . 'leads')->row()->email;
                $proposalWarning = false;
                $message = '';
                $success = $this->leads_model->update($this->input->post(), $id);

                if ($success) {
                    $emailNow = $this->db->select('email')->where('id', $id)->get(db_prefix() . 'leads')->row()->email;

                    $proposalWarning = (total_rows(db_prefix() . 'proposals', [
                        'rel_type' => 'lead',
                        'rel_id' => $id
                    ]) > 0 && ($emailOriginal != $emailNow) && $emailNow != '') ? true : false;

                    $message = _l('updated_successfully', _l('lead'));
                }
                $this->leads_model->log_lead_activity($id, " Updated Lead");
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                    'id' => $id,
                    'proposal_warning' => $proposalWarning,
                    'leadView' => $this->_get_lead_data($id, true),
                ]);
            }
            die;
        }

        echo json_encode([
            'leadView' => $this->_get_lead_data($id),
        ]);
    }

    private function _get_lead_data($id = '', $is_update = false)
    {
        $reminder_data = '';
        $data['lead_locked'] = false;
        $data['openEdit'] = $this->input->get('edit') ? true : false;
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);
        $data['status_id'] = $this->input->get('status_id') ? $this->input->get('status_id') : get_option('leads_default_status');
        if (is_numeric($id)) {
            if ($data['openEdit']) {
                $this->leads_model->log_lead_activity($id, " Edit Lead");
            } else {
                $this->leads_model->log_lead_activity($id, " View Lead");
            }

            $leadWhere = [];
            if (!$is_update) {
                if ($this->input->get('vendor')) {
                    if (!has_permission('vendors', '', 'view') && !has_permission('vendors', '', 'view_own')) {
                        $leadWhere = '(assigned = ' . get_staff_user_id() . ' OR addedfrom=' . get_staff_user_id() . ' OR is_public=1)';
                    }
                } else {
                    $leadWhere = ((has_permission('leads', '', 'view') || leads_permission_allow_to_manager($id)) ? [] : '(assigned = ' . get_staff_user_id() . ' OR addedfrom=' . get_staff_user_id() . ' OR is_public=1)');
                }
            }
            $lead = $this->leads_model->get($id, $leadWhere);

            if (!$lead && !$is_update) {
                header('HTTP/1.0 404 Not Found');
                echo _l('lead_not_found');
                die;
            }

            if (total_rows(db_prefix() . 'clients', ['leadid' => $id, 'deleted_at' => NULL]) > 0) {
                $data['lead_locked'] = ((!is_admin() && get_option('lead_lock_after_convert_to_customer') == 1) ? true : false);
            }

            $reminder_data = $this->load->view('admin/includes/modals/lead-reminder', [
                'id' => $lead->id,
                'name' => 'lead',
                'members' => $data['members'],
                'reminder_title' => "Action",
            ], true);

            $data['lead'] = $lead;
            $data['mail_activity'] = $this->leads_model->get_mail_activity($id);
            $data['notes'] = $this->misc_model->get_notes($id, 'lead');
            $data['activity_log'] = $this->leads_model->get_lead_activity_log($id);
            $data['call_logs'] = $this->leads_model->get_call_logs($id);
            if (is_gdpr() && get_option('gdpr_enable_consent_for_leads') == '1') {
                $this->load->model('gdpr_model');
                $data['purposes'] = $this->gdpr_model->get_consent_purposes($lead->id, 'lead');
                $data['consents'] = $this->gdpr_model->get_consents(['lead_id' => $lead->id]);
            }
        }
        $data['statuses'] = $this->leads_model->get_status();
        $data['sources'] = $this->leads_model->get_source();

        $data = hooks()->apply_filters('lead_view_data', $data);

        //Inquire Forms
        $data['main_group_data'] = $this->leadsnew_model->get_main_group();
        $data['sub_group_data'] = $this->leadsnew_model->get_sub_group();

        return [
            'data' => $this->load->view('admin/leads/lead', $data, true),
            'reminder_data' => $reminder_data,
        ];
    }

    public function leads_kanban_load_more()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }

        $status = $this->input->get('status');
        $page = $this->input->get('page');

        $this->db->where('id', $status);
        $status = $this->db->get(db_prefix() . 'leads_status')->row_array();

        $leads = $this->leads_model->do_kanban_query($status['id'], $this->input->get('search'), $page, [
            'sort_by' => $this->input->get('sort_by'),
            'sort' => $this->input->get('sort'),
        ]);

        foreach ($leads as $lead) {
            $this->load->view('admin/leads/_kan_ban_card', [
                'lead' => $lead,
                'status' => $status,
            ]);
        }
    }

    public function switch_kanban($set = 0)
    {
        if ($set == 1) {
            $set = 'true';
        } else {
            $set = 'false';
        }
        $this->session->set_userdata([
            'leads_kanban_view' => $set,
        ]);
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function export($id)
    {
        if (is_admin()) {
            $this->load->library('gdpr/gdpr_lead');
            $this->gdpr_lead->export($id);
        }
    }

    public function delete($id)
    {
        if (!has_permission('leads', '', 'delete')) {
            ajax_access_denied('Delete Lead');
        }

        $response = $this->leads_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            $result['success'] = false;
            $result['message'] =  _l('is_referenced', _l('lead_lowercase'));
        } elseif ($response === true) {
            $this->leads_model->log_lead_activity($id, " Deleted Lead");
            $result['success'] = true;
            $result['message'] =  _l('deleted', _l('lead'));
        } else {
            $result['success'] = false;
            $result['message'] = _l('problem_deleting', _l('lead_lowercase'));
        }
        echo json_encode($result);
    }

    public function hard_delete($id)
    {
        if (!has_permission('leads', '', 'delete')) {
            ajax_access_denied('Delete Lead');
        }

        $response = $this->leads_model->hard_delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            $result['success'] = false;
            $result['message'] =  _l('is_referenced', _l('lead_lowercase'));
        } elseif ($response === true) {
            $this->leads_model->log_lead_activity($id, " Deleted Lead");
            $result['success'] = true;
            $result['message'] =  _l('deleted', _l('lead'));
        } else {
            $result['success'] = false;
            $result['message'] = _l('problem_deleting', _l('lead_lowercase'));
        }
        echo json_encode($result);
    }

    public function restore($id)
    {
        if (!has_permission('leads', '', 'delete')) {
            ajax_access_denied('restore Lead');
        }
        $response = $this->leads_model->restore($id);
        if (is_array($response) && isset($response['referenced'])) {
            $result['success'] = false;
            $result['message'] =  _l('is_referenced', _l('lead_lowercase'));
        } elseif ($response === true) {
            $this->leads_model->log_lead_activity($id, " Restored Lead");
            $result['success'] = true;
            $result['message'] =  _l('restored', _l('lead'));
        } else {
            $result['success'] = false;
            $result['message'] = _l('problem_restoring', _l('lead_lowercase'));
        }
        echo json_encode($result);
    }

    public function mark_as_lost($id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            ajax_access_denied();
        }
        $message = '';
        $success = $this->leads_model->mark_as_lost($id);
        if ($success) {
            $message = _l('lead_marked_as_lost');
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'leadView' => $this->_get_lead_data($id),
            'id' => $id,
        ]);
    }

    public function unmark_as_lost($id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            ajax_access_denied();
        }
        $message = '';
        $success = $this->leads_model->unmark_as_lost($id);
        if ($success) {
            $message = _l('lead_unmarked_as_lost');
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'leadView' => $this->_get_lead_data($id),
            'id' => $id,
        ]);
    }

    public function mark_as_junk($id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            ajax_access_denied();
        }
        $message = '';
        $success = $this->leads_model->mark_as_junk($id);
        if ($success) {
            $message = _l('lead_marked_as_junk');
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'leadView' => $this->_get_lead_data($id),
            'id' => $id,
        ]);
    }

    public function unmark_as_junk($id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            ajax_access_denied();
        }
        $message = '';
        $success = $this->leads_model->unmark_as_junk($id);
        if ($success) {
            $message = _l('lead_unmarked_as_junk');
        }
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'leadView' => $this->_get_lead_data($id),
            'id' => $id,
        ]);
    }

    public function add_activity()
    {
        $leadid = $this->input->post('leadid');
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($leadid)) {
            ajax_access_denied();
        }
        if ($this->input->post()) {
            $message = $this->input->post('activity');
            $aId = $this->leads_model->log_lead_activity($leadid, $message);
            if ($aId) {
                $this->db->where('id', $aId);
                $this->db->update(db_prefix() . 'lead_activity_log', ['custom_activity' => 1]);
            }
            echo json_encode(['leadView' => $this->_get_lead_data($leadid), 'id' => $leadid]);
        }
    }

    public function get_convert_data($id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            ajax_access_denied();
        }
        if (is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1') {
            $this->load->model('gdpr_model');
            $data['purposes'] = $this->gdpr_model->get_consent_purposes($id, 'lead');
        }
        $data['lead'] = $this->leads_model->get($id);
        $customer_default_country = get_option('customer_default_country');
        $country_id               = ($data['lead']->country != 0 ? $data['lead']->country : $customer_default_country);
        $data['initial_states']   = [];
        $data['initial_cities']   = [];

        if ($country_id) {
            $country_code = get_country_short_name($country_id);
            if ($country_code) {
                $data['initial_states'] = $this->leadsnew_model->get_states(['country_code' => $country_code]);
                if (empty($data['initial_states'])) {
                    $country_name = get_country_name($country_id);
                    if ($country_name) {
                        $data['initial_states'] = $this->leadsnew_model->get_states(['country' => $country_name]);
                    }
                }
                if (!empty($data['lead']->state)) {
                    $data['initial_cities'] = $this->leadsnew_model->get_cities([
                        'country_code' => $country_code,
                        'state'        => $data['lead']->state,
                    ]);
                    if (empty($data['initial_cities'])) {
                        $country_name = get_country_name($country_id);
                        if ($country_name) {
                            $data['initial_cities'] = $this->leadsnew_model->get_cities([
                                'country' => $country_name,
                                'state'   => $data['lead']->state,
                            ]);
                        }
                    }
                }
            }
        }

        $this->load->view('admin/leads/convert_to_customer', $data);
    }

    /**
     * AJAX: states/cities for convert-to-customer location dropdowns.
     */
    public function get_state_city()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }

        $this->output->set_content_type('application/json');

        $result = ['success' => false, 'data' => []];

        if (!$this->input->post()) {
            echo json_encode($result);

            return;
        }

        $type         = $this->input->post('type');
        $country_id   = (int) $this->input->post('country_id');
        $country_code = get_country_short_name($country_id);

        if (!$country_code) {
            echo json_encode($result);

            return;
        }

        if ($type === 'state') {
            $result['data'] = $this->leadsnew_model->get_states(['country_code' => $country_code]);
            if (empty($result['data'])) {
                $country_name = get_country_name($country_id);
                if ($country_name) {
                    $result['data'] = $this->leadsnew_model->get_states(['country' => $country_name]);
                }
            }
            $result['success'] = true;
        } elseif ($type === 'city') {
            $state = $this->input->post('state');
            if ($state) {
                $result['data'] = $this->leadsnew_model->get_cities([
                    'country_code' => $country_code,
                    'state'        => $state,
                ]);
                if (empty($result['data'])) {
                    $country_name = get_country_name($country_id);
                    if ($country_name) {
                        $result['data'] = $this->leadsnew_model->get_cities([
                            'country' => $country_name,
                            'state'   => $state,
                        ]);
                    }
                }
                $result['success'] = true;
            }
        }

        echo json_encode($result);
    }

    /**
     * Convert lead to client
     * @since  version 1.0.1
     * @return mixed
     */
    public function convert_to_customer()
    {
        if (!is_staff_member()) {
            access_denied('Lead Convert to Customer');
        }

        if ($this->input->post()) {
            $default_country = get_option('customer_default_country');
            $data = $this->input->post();
            $data['password'] = $this->input->post('password', false);

            $original_lead_email = $data['original_lead_email'];
            unset($data['original_lead_email']);

            if (isset($data['transfer_notes'])) {
                $notes = $this->misc_model->get_notes($data['leadid'], 'lead');
                unset($data['transfer_notes']);
            }

            if (isset($data['transfer_consent'])) {
                $this->load->model('gdpr_model');
                $consents = $this->gdpr_model->get_consents(['lead_id' => $data['leadid']]);
                unset($data['transfer_consent']);
            }

            if (isset($data['merge_db_fields'])) {
                $merge_db_fields = $data['merge_db_fields'];
                unset($data['merge_db_fields']);
            }

            if (isset($data['merge_db_contact_fields'])) {
                $merge_db_contact_fields = $data['merge_db_contact_fields'];
                unset($data['merge_db_contact_fields']);
            }

            if (isset($data['include_leads_custom_fields'])) {
                $include_leads_custom_fields = $data['include_leads_custom_fields'];
                unset($data['include_leads_custom_fields']);
            }

            if ($data['country'] == '' && $default_country != '') {
                $data['country'] = $default_country;
            }

            $data['billing_street'] = $data['address'];
            $data['billing_city'] = $data['city'];
            $data['billing_state'] = $data['state'];
            $data['billing_zip'] = $data['zip'];
            $data['billing_country'] = $data['country'];

            $data['is_primary'] = 1;
            $id = $this->clients_model->add($data, true);
            if ($id) {
                $primary_contact_id = get_primary_contact_user_id($id);

                if (isset($notes)) {
                    foreach ($notes as $note) {
                        $this->db->insert(db_prefix() . 'notes', [
                            'rel_id' => $id,
                            'rel_type' => 'customer',
                            'dateadded' => $note['dateadded'],
                            'addedfrom' => $note['addedfrom'],
                            'description' => $note['description'],
                            'date_contacted' => $note['date_contacted'],
                        ]);
                    }
                }
                if (isset($consents)) {
                    foreach ($consents as $consent) {
                        unset($consent['id']);
                        unset($consent['purpose_name']);
                        $consent['lead_id'] = 0;
                        $consent['contact_id'] = $primary_contact_id;
                        $this->gdpr_model->add_consent($consent);
                    }
                }
                if (!has_permission('customers', '', 'view') && get_option('auto_assign_customer_admin_after_lead_convert') == 1) {
                    $this->db->insert(db_prefix() . 'customer_admins', [
                        'date_assigned' => date('Y-m-d H:i:s'),
                        'customer_id' => $id,
                        'staff_id' => get_staff_user_id(),
                        'created_by' => get_staff_user_id(),
                        'date_assigned' => date('Y-m-d H:i:s'),
                    ]);
                }
                $this->leads_model->log_lead_activity($data['leadid'], 'not_lead_activity_converted', false, serialize([
                    get_staff_full_name(),
                ]));
                $default_status = $this->leads_model->get_status('', [
                    'isdefault' => 1,
                ]);
                $this->db->where('id', $data['leadid']);
                $this->db->update(db_prefix() . 'leads', [
                    'date_converted' => date('Y-m-d H:i:s'),
                    'status' => $default_status[0]['id'],
                    'junk' => 0,
                    'lost' => 0,
                ]);
                // Check if lead email is different then client email
                $contact = $this->clients_model->get_contact(get_primary_contact_user_id($id));
                if ($contact->email != $original_lead_email) {
                    if ($original_lead_email != '') {
                        $this->leads_model->log_lead_activity($data['leadid'], 'not_lead_activity_converted_email', false, serialize([
                            $original_lead_email,
                            $contact->email,
                        ]));
                    }
                }
                if (isset($include_leads_custom_fields)) {
                    foreach ($include_leads_custom_fields as $fieldid => $value) {
                        // checked don't merge
                        if ($value == 5) {
                            continue;
                        }
                        // get the value of this leads custom fiel
                        $this->db->where('relid', $data['leadid']);
                        $this->db->where('fieldto', 'leads');
                        $this->db->where('fieldid', $fieldid);
                        $lead_custom_field_value = $this->db->get(db_prefix() . 'customfieldsvalues')->row()->value;
                        // Is custom field for contact ot customer
                        if ($value == 1 || $value == 4) {
                            if ($value == 4) {
                                $field_to = 'contacts';
                            } else {
                                $field_to = 'customers';
                            }
                            $this->db->where('id', $fieldid);
                            $field = $this->db->get(db_prefix() . 'customfields')->row();
                            // check if this field exists for custom fields
                            $this->db->where('fieldto', $field_to);
                            $this->db->where('name', $field->name);
                            $exists = $this->db->get(db_prefix() . 'customfields')->row();
                            $copy_custom_field_id = null;
                            if ($exists) {
                                $copy_custom_field_id = $exists->id;
                            } else {
                                // there is no name with the same custom field for leads at the custom side create the custom field now
                                $this->db->insert(db_prefix() . 'customfields', [
                                    'fieldto' => $field_to,
                                    'name' => $field->name,
                                    'required' => $field->required,
                                    'type' => $field->type,
                                    'options' => $field->options,
                                    'display_inline' => $field->display_inline,
                                    'field_order' => $field->field_order,
                                    'slug' => slug_it($field_to . '_' . $field->name, [
                                        'separator' => '_',
                                    ]),
                                    'active' => $field->active,
                                    'only_admin' => $field->only_admin,
                                    'show_on_table' => $field->show_on_table,
                                    'bs_column' => $field->bs_column,
                                ]);
                                $new_customer_field_id = $this->db->insert_id();
                                if ($new_customer_field_id) {
                                    $copy_custom_field_id = $new_customer_field_id;
                                }
                            }
                            if ($copy_custom_field_id != null) {
                                $insert_to_custom_field_id = $id;
                                if ($value == 4) {
                                    $insert_to_custom_field_id = get_primary_contact_user_id($id);
                                }
                                $this->db->insert(db_prefix() . 'customfieldsvalues', [
                                    'relid' => $insert_to_custom_field_id,
                                    'fieldid' => $copy_custom_field_id,
                                    'fieldto' => $field_to,
                                    'value' => $lead_custom_field_value,
                                ]);
                            }
                        } elseif ($value == 2) {
                            if (isset($merge_db_fields)) {
                                $db_field = $merge_db_fields[$fieldid];
                                // in case user don't select anything from the db fields
                                if ($db_field == '') {
                                    continue;
                                }
                                if ($db_field == 'country' || $db_field == 'shipping_country' || $db_field == 'billing_country') {
                                    $this->db->where('iso2', $lead_custom_field_value);
                                    $this->db->or_where('short_name', $lead_custom_field_value);
                                    $this->db->or_like('long_name', $lead_custom_field_value);
                                    $country = $this->db->get(db_prefix() . 'countries')->row();
                                    if ($country) {
                                        $lead_custom_field_value = $country->country_id;
                                    } else {
                                        $lead_custom_field_value = 0;
                                    }
                                }
                                $this->db->where('userid', $id);
                                $this->db->update(db_prefix() . 'clients', [
                                    $db_field => $lead_custom_field_value,
                                ]);
                            }
                        } elseif ($value == 3) {
                            if (isset($merge_db_contact_fields)) {
                                $db_field = $merge_db_contact_fields[$fieldid];
                                if ($db_field == '') {
                                    continue;
                                }
                                $this->db->where('id', $primary_contact_id);
                                $this->db->update(db_prefix() . 'contacts', [
                                    $db_field => $lead_custom_field_value,
                                ]);
                            }
                        }
                    }
                }
                // set the lead to status client in case is not status client
                $this->db->where('isdefault', 1);
                $status_client_id = $this->db->get(db_prefix() . 'leads_status')->row()->id;
                $this->db->where('id', $data['leadid']);
                $this->db->update(db_prefix() . 'leads', [
                    'status' => $status_client_id,
                ]);

                set_alert('success', _l('lead_to_client_base_converted_success'));

                if (is_gdpr() && get_option('gdpr_after_lead_converted_delete') == '1') {
                    $this->leads_model->delete($data['leadid']);

                    $this->db->where('userid', $id);
                    $this->db->update(db_prefix() . 'clients', ['leadid' => null]);
                }

                log_activity('Created Lead Client Profile [LeadID: ' . $data['leadid'] . ', ClientID: ' . $id . ']');
                hooks()->do_action('lead_converted_to_customer', ['lead_id' => $data['leadid'], 'customer_id' => $id]);
                redirect(admin_url('clients/client/' . $id));
            }
        }
    }

    /* Used in kanban when dragging and mark as */
    public function update_lead_status()
    {
        if ($this->input->post() && $this->input->is_ajax_request()) {
            $this->leads_model->update_lead_status($this->input->post());
        }
    }

    public function update_status_order()
    {
        if ($post_data = $this->input->post()) {
            $this->leads_model->update_status_order($post_data);
        }
    }

    public function add_lead_attachment()
    {
        $id = $this->input->post('id');
        $lastFile = $this->input->post('last_file');

        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($id)) {
            ajax_access_denied();
        }

        handle_lead_attachments($id);
        echo json_encode(['leadView' => $lastFile ? $this->_get_lead_data($id) : [], 'id' => $id]);
    }

    public function add_external_attachment()
    {
        if ($this->input->post()) {
            $this->leads_model->add_attachment_to_database(
                $this->input->post('lead_id'),
                $this->input->post('files'),
                $this->input->post('external')
            );
        }
    }

    public function delete_attachment($id, $lead_id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($lead_id)) {
            ajax_access_denied();
        }
        echo json_encode([
            'success' => $this->leads_model->delete_lead_attachment($id),
        ]);
    }

    public function delete_note($id, $lead_id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($lead_id)) {
            ajax_access_denied();
        }
        echo json_encode([
            'success' => $this->misc_model->delete_note($id),
        ]);
    }

    public function update_all_proposal_emails_linked_to_lead($id)
    {
        $success = false;
        $email = '';
        if ($this->input->post('update')) {
            $this->load->model('proposals_model');

            $this->db->select('email');
            $this->db->where('id', $id);
            $email = $this->db->get(db_prefix() . 'leads')->row()->email;

            $proposals = $this->proposals_model->get('', [
                'rel_type' => 'lead',
                'rel_id' => $id,
            ]);
            $affected_rows = 0;

            foreach ($proposals as $proposal) {
                $this->db->where('id', $proposal['id']);
                $this->db->update(db_prefix() . 'proposals', [
                    'email' => $email,
                ]);
                if ($this->db->affected_rows() > 0) {
                    $affected_rows++;
                }
            }

            if ($affected_rows > 0) {
                $success = true;
            }
        }

        echo json_encode([
            'success' => $success,
            'message' => _l('proposals_emails_updated', [
                _l('lead_lowercase'),
                $email,
            ]),
        ]);
    }

    public function save_form_data()
    {
        $data = $this->input->post();

        // form data should be always sent to the request and never should be empty
        // this code is added to prevent losing the old form in case any errors
        if (!isset($data['formData']) || isset($data['formData']) && !$data['formData']) {
            echo json_encode([
                'success' => false,
            ]);
            die;
        }

        // If user paste with styling eq from some editor word and the Codeigniter XSS feature remove and apply xss=remove, may break the json.
        $data['formData'] = preg_replace('/=\\\\/m', "=''", $data['formData']);

        $this->db->where('id', $data['id']);
        $this->db->update(db_prefix() . 'web_to_lead', [
            'form_data' => $data['formData'],
        ]);
        if ($this->db->affected_rows() > 0) {
            echo json_encode([
                'success' => true,
                'message' => _l('updated_successfully', _l('web_to_lead_form')),
            ]);
        } else {
            echo json_encode([
                'success' => false,
            ]);
        }
    }

    public function form($id = '')
    {
        if (!is_admin()) {
            access_denied('Web To Lead Access');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            if (isset($data['company_theme'])) {
                $data['company_theme'] = "1";
            } else {
                $data['company_theme'] = "0";
            }
            if (isset($data['send_brochure_email'])) {
                $data['send_brochure_email'] = "1";
            } else {
                $data['send_brochure_email'] = "0";
            }
            if ($id == '') {
                $id = $this->leads_model->add_form($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('web_to_lead_form')));
                    redirect(admin_url('leads/form/' . $id));
                }
            } else {
                $success = $this->leads_model->update_form($id, $data);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('web_to_lead_form')));
                }
                redirect(admin_url('leads/form/' . $id));
            }
        }

        $data['formData'] = [];
        $custom_fields = get_custom_fields('leads', 'type != "link"');

        $cfields = format_external_form_custom_fields($custom_fields);
        $data['title'] = _l('web_to_lead');

        if ($id != '') {
            $data['form'] = $this->leads_model->get_form([
                'id' => $id,
            ]);
            $data['title'] = $data['form']->name . ' - ' . _l('web_to_lead_form');
            $data['formData'] = $data['form']->form_data;
        }

        $this->load->model('roles_model');
        $data['roles'] = $this->roles_model->get();
        $data['sources'] = $this->leads_model->get_source();
        $data['statuses'] = $this->leads_model->get_status();

        $data['members'] = $this->staff_model->get('', [
            'active' => 1,
            'is_not_staff' => 0,
        ]);

        $data['languages'] = $this->app->get_available_languages();
        $data['cfields'] = $cfields;

        $db_fields = [];
        $fields = [
            'name',
            'title',
            'email',
            'phonenumber',
            'company',
            'address',
            'city',
            'state',
            'country',
            'zip',
            'description',
            'website',
        ];

        $fields = hooks()->apply_filters('lead_form_available_database_fields', $fields);

        $className = 'form-control';

        foreach ($fields as $f) {
            $_field_object = new stdClass();
            $type = 'text';
            $subtype = '';
            if ($f == 'email') {
                $subtype = 'email';
            } elseif ($f == 'description' || $f == 'address') {
                $type = 'textarea';
            } elseif ($f == 'country') {
                $type = 'select';
            }

            if ($f == 'name') {
                $label = _l('lead_add_edit_name');
            } elseif ($f == 'email') {
                $label = _l('lead_add_edit_email');
            } elseif ($f == 'phonenumber') {
                $label = _l('lead_add_edit_phonenumber');
            } else {
                $label = _l('lead_' . $f);
            }

            $field_array = [
                'subtype' => $subtype,
                'type' => $type,
                'label' => $label,
                'className' => $className,
                'name' => $f,
            ];

            if ($f == 'country') {
                $field_array['values'] = [];

                $field_array['values'][] = [
                    'label' => '',
                    'value' => '',
                    'selected' => false,
                ];

                $countries = get_all_countries();
                foreach ($countries as $country) {
                    $selected = false;
                    if (get_option('customer_default_country') == $country['country_id']) {
                        $selected = true;
                    }
                    array_push($field_array['values'], [
                        'label' => $country['short_name'],
                        'value' => (int) $country['country_id'],
                        'selected' => $selected,
                    ]);
                }
            }

            if ($f == 'name') {
                $field_array['required'] = true;
            }

            $_field_object->label = $label;
            $_field_object->name = $f;
            $_field_object->fields = [];
            $_field_object->fields[] = $field_array;
            $db_fields[] = $_field_object;
        }
        $data['bodyclass'] = 'web-to-lead-form';
        $data['db_fields'] = $db_fields;
        $this->load->view('admin/leads/formbuilder', $data);
    }

    public function forms($id = '')
    {
        if (!is_admin()) {
            access_denied('Web To Lead Access');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('web_to_lead');
        }

        $data['title'] = _l('web_to_lead');
        $this->load->view('admin/leads/forms', $data);
    }

    public function delete_form($id)
    {
        if (!is_admin()) {
            access_denied('Web To Lead Access');
        }

        $success = $this->leads_model->delete_form($id);
        if ($success) {
            set_alert('success', _l('deleted', _l('web_to_lead_form')));
        }

        redirect(admin_url('leads/forms'));
    }

    // Sources
    /* Manage leads sources */
    public function sources()
    {
        if (!is_admin()) {
            access_denied('Leads Sources');
        }
        $data['sources'] = $this->leads_model->get_source();
        $data['title'] = 'Leads sources';
        $this->load->view('admin/leads/manage_sources', $data);
    }

    /* Add or update leads sources */
    public function source()
    {
        if (!is_admin() && get_option('staff_members_create_inline_lead_source') == '0') {
            access_denied('Leads Sources');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            if (!$this->input->post('id')) {
                $inline = isset($data['inline']);
                if (isset($data['inline'])) {
                    unset($data['inline']);
                }

                $id = $this->leads_model->add_source($data);

                if (!$inline) {
                    if ($id) {
                        set_alert('success', _l('added_successfully', _l('lead_source')));
                    }
                } else {
                    echo json_encode(['success' => $id ? true : false, 'id' => $id]);
                }
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->leads_model->update_source($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('lead_source')));
                }
            }
        }
    }

    /* Delete leads source */
    public function delete_source($id)
    {
        if (!is_admin()) {
            access_denied('Delete Lead Source');
        }
        if (!$id) {
            redirect(admin_url('leads/sources'));
        }
        $response = $this->leads_model->delete_source($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lead_source_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lead_source')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lead_source_lowercase')));
        }
        redirect(admin_url('leads/sources'));
    }

    // Statuses
    /* View leads statuses */
    public function statuses()
    {
        if (!is_admin()) {
            access_denied('Leads Statuses');
        }
        $data['statuses'] = $this->leads_model->get_status();
        $data['title'] = 'Leads statuses';
        $this->load->view('admin/leads/manage_statuses', $data);
    }

    /* Add or update leads status */
    public function status()
    {
        if (!is_admin() && get_option('staff_members_create_inline_lead_status') == '0') {
            access_denied('Leads Statuses');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            if (!$this->input->post('id')) {
                $inline = isset($data['inline']);
                if (isset($data['inline'])) {
                    unset($data['inline']);
                }
                $id = $this->leads_model->add_status($data);
                if (!$inline) {
                    if ($id) {
                        set_alert('success', _l('added_successfully', _l('lead_status')));
                    }
                } else {
                    echo json_encode(['success' => $id ? true : false, 'id' => $id]);
                }
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->leads_model->update_status($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('lead_status')));
                }
            }
        }
    }

    /* Delete leads status from databae */
    public function delete_status($id)
    {
        if (!is_admin()) {
            access_denied('Leads Statuses');
        }
        if (!$id) {
            redirect(admin_url('leads/statuses'));
        }
        $response = $this->leads_model->delete_status($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lead_status_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lead_status')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lead_status_lowercase')));
        }
        redirect(admin_url('leads/statuses'));
    }

    /* Add new lead note */
    public function add_note($rel_id)
    {
        if (!is_staff_member() || !$this->leads_model->staff_can_access_lead($rel_id)) {
            ajax_access_denied();
        }

        if ($this->input->post()) {
            $data = $this->input->post();

            $notify_staff_id = '';
            if (isset($data['notify_staff_id'])) {
                $notify_staff_id = $data['notify_staff_id'];
                unset($data['notify_staff_id']);
            }

            if (isset($data['contacted_indicator']) && $data['contacted_indicator'] == 'yes') {
                $contacted_date = to_sql_date($data['custom_contact_date'], true);
                $data['date_contacted'] = $contacted_date;
            }

            if (isset($data['contacted_indicator'])) {
                unset($data['contacted_indicator']);
            }
            if (isset($data['custom_contact_date'])) {
                unset($data['custom_contact_date']);
            }

            // Causing issues with duplicate ID or if my prefixed file for lead.php is used
            $data['description'] = isset($data['lead_note_description']) ? $data['lead_note_description'] : $data['description'];

            if (isset($data['lead_note_description'])) {
                unset($data['lead_note_description']);
            }

            $note_id = $this->misc_model->add_note($data, 'lead', $rel_id);
            $whatsapp_link = '';

            if ($note_id) {
                if (isset($contacted_date)) {
                    $this->db->where('id', $rel_id);
                    $this->db->update(db_prefix() . 'leads', [
                        'lastcontact' => $contacted_date,
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        $this->leads_model->log_lead_activity($rel_id, 'not_lead_activity_contacted', false, serialize([
                            get_staff_full_name(get_staff_user_id()),
                            _dt($contacted_date),
                        ]));
                    }
                }

                if ($notify_staff_id != '') {
                    $staff = $this->staff_model->get($notify_staff_id);
                    $lead = $this->leads_model->get($rel_id);
                    if ($staff && $lead) {
                        $note_desc = strip_tags($data['description']);
                        $lead_url = admin_url('leads/index/' . $lead->id);
                        $message = "A new note has been added to Lead: " . $lead->name . "\n\nNote: " . $note_desc . "\n\nLead Link: " . $lead_url;

                        // Send In-App Notification
                        $notified = add_notification([
                            'description'     => 'New note added to lead: ' . $lead->name,
                            'touserid'        => $notify_staff_id,
                            'fromcompany'     => 1,
                            'fromuserid'      => get_staff_user_id(),
                            'link'            => '#leadid=' . $rel_id,
                        ]);
                        if ($notified) {
                            pusher_trigger_notification([$notify_staff_id]);
                        }

                        // Send Email Notification
                        $this->load->library('email');
                        $this->email->from(get_option('smtp_email'), get_option('companyname'));
                        $this->email->to($staff->email);
                        $this->email->subject("New Note Added to Lead: " . $lead->name);
                        $this->email->message(nl2br($message));
                        $this->email->send();

                        // WhatsApp link Generation
                        if (!empty($staff->phonenumber)) {
                            $whatsappMessage = urlencode($message);
                            $whatsapp_link = "https://api.whatsapp.com/send?phone=" . $staff->phonenumber . "&text=" . $whatsappMessage;
                        }
                    }
                }
            }
        }
        echo json_encode(['leadView' => $this->_get_lead_data($rel_id), 'id' => $rel_id, 'whatsapp_link' => $whatsapp_link]);
    }

    public function test_email_integration()
    {
        if (!is_admin()) {
            access_denied('Leads Test Email Integration');
        }

        app_check_imap_open_function(admin_url('leads/email_integration'));

        require_once(APPPATH . 'third_party/php-imap/Imap.php');

        $mail = $this->leads_model->get_email_integration();
        $ps = $mail->password;
        if (false == $this->encryption->decrypt($ps)) {
            set_alert('danger', _l('failed_to_decrypt_password'));
            redirect(admin_url('leads/email_integration'));
        }
        $mailbox = $mail->imap_server;
        $username = $mail->email;
        $password = $this->encryption->decrypt($ps);
        $encryption = $mail->encryption;
        // open connection
        $imap = new Imap($mailbox, $username, $password, $encryption);

        if ($imap->isConnected() === false) {
            set_alert('danger', _l('lead_email_connection_not_ok') . '<br /><b>' . $imap->getError() . '</b>');
        } else {
            set_alert('success', _l('lead_email_connection_ok'));
        }

        redirect(admin_url('leads/email_integration'));
    }

    public function email_integration()
    {
        if (!is_admin()) {
            access_denied('Leads Email Intregration');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $data['password'] = $this->input->post('password', false);

            if (isset($data['fakeusernameremembered'])) {
                unset($data['fakeusernameremembered']);
            }
            if (isset($data['fakepasswordremembered'])) {
                unset($data['fakepasswordremembered']);
            }

            $success = $this->leads_model->update_email_integration($data);
            if ($success) {
                set_alert('success', _l('leads_email_integration_updated'));
            }
            redirect(admin_url('leads/email_integration'));
        }
        $data['roles'] = $this->roles_model->get();
        $data['sources'] = $this->leads_model->get_source();
        $data['statuses'] = $this->leads_model->get_status();

        $data['members'] = $this->staff_model->get('', [
            'active' => 1,
            'is_not_staff' => 0,
        ]);

        $data['title'] = _l('leads_email_integration');
        $data['mail'] = $this->leads_model->get_email_integration();
        $data['bodyclass'] = 'leads-email-integration';
        $this->load->view('admin/leads/email_integration', $data);
    }

    public function change_status_color()
    {
        if ($this->input->post()) {
            $this->leads_model->change_status_color($this->input->post());
        }
    }

    public function import()
    {
        if (!is_admin() && get_option('allow_non_admin_members_to_import_leads') != '1') {
            access_denied('Leads Import');
        }

        $dbFields = $this->db->list_fields(db_prefix() . 'leads');
        array_push($dbFields, 'tags');

        $this->load->library('import/import_leads', [], 'import');
        // $this->import->setDatabaseFields($dbFields)
        //     ->setCustomFields(get_custom_fields('leads'));
        $this->import->setDatabaseFields($dbFields);

        if ($this->input->post('download_sample') === 'true') {
            $this->import->downloadSample();
        }

        if (
            $this->input->post()
            && isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != ''
        ) {
            $this->import->setSimulation($this->input->post('simulate'))
                ->setTemporaryFileLocation($_FILES['file_csv']['tmp_name'])
                ->setFilename($_FILES['file_csv']['name'])
                ->perform();

            $data['total_rows_post'] = $this->import->totalRows();

            if (!$this->import->isSimulation()) {
                set_alert('success', _l('import_total_imported', $this->import->totalImported()));
            }
        }

        $data['statuses'] = $this->leads_model->get_status();
        $data['sources'] = $this->leads_model->get_source();
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active' => 1]);

        $data['title'] = _l('import');
        $this->load->view('admin/leads/import', $data);
    }

    public function validate_unique_field()
    {
        if ($this->input->post()) {

            // First we need to check if the field is the same
            $lead_id = $this->input->post('lead_id');
            $field = $this->input->post('field');
            $value = $this->input->post($field);

            if ($lead_id != '') {
                $this->db->select($field);
                $this->db->where('id', $lead_id);
                $row = $this->db->get(db_prefix() . 'leads')->row();
                if ($row->{$field} == $value) {
                    echo json_encode(true);
                    die();
                }
            }

            echo total_rows(db_prefix() . 'leads', [$field => $value]) > 0 ? 'false' : 'true';
        }
    }

    public function bulk_action()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }
        hooks()->do_action('before_do_bulk_action_for_leads');
        $total_deleted = 0;

        if ($this->input->post()) {
            $ids = $this->input->post('ids');
            $status = $this->input->post('status');
            $source = $this->input->post('source');
            $assigned = $this->input->post('assigned');
            $visibility = $this->input->post('visibility');
            $tags = $this->input->post('tags');
            $last_contact = $this->input->post('last_contact');
            $lost = $this->input->post('lost');
            $has_permission_delete = has_permission('leads', '', 'delete');
            $city = $this->input->post('city');
            $state = $this->input->post('state');
            $country = $this->input->post('country');


            if (is_array($ids)) {
                foreach ($ids as $id) {
                    $oldleadData = $this->leads_model->get($id);

                    if ($this->input->post('mass_delete')) {
                        if ($has_permission_delete) {
                            if ($this->leads_model->hard_delete($id)) {
                                $this->leads_model->log_lead_activity($id, " Deleted Lead");
                                $total_deleted++;
                            }
                        }
                    } else {
                        if ($status || $source || $assigned || $last_contact || $visibility || $city || $state || $country) {
                            $update = [];

                            if ($status) {
                                // Update lead status
                                $this->leads_model->update_lead_status([
                                    'status' => $status,
                                    'leadid' => $id,
                                ]);
                            }
                            if ($source) {
                                $this->leads_model->update_lead_source([
                                    'source' => $source,
                                    'leadid' => $id,
                                ]);
                            }
                            if ($assigned) {
                                $update['assigned'] = $assigned;
                            }
                            if ($last_contact) {
                                $last_contact = to_sql_date($last_contact, true);
                                $update['lastcontact'] = $last_contact;
                            } else {
                                $update['lastcontact'] = date('Y-m-d H:i:s');
                            }

                            if ($visibility) {
                                $update['is_public'] = ($visibility == 'public') ? 1 : 0;
                            }

                            if (!empty($city)) {
                                $update['city'] = $city;
                            }

                            if (!empty($state)) {
                                $update['state'] = $state;
                            }

                            if (!empty($country)) {
                                $update['country'] = $country;
                            }

                            // if only assignee or city or state change then no need to update last contact at field.
                            if (($assigned || $city || $state || $country) && !$status && !$source && !$visibility && !$last_contact) {
                                if (isset($update['lastcontact'])) {
                                    unset($update['lastcontact']);
                                }
                            }

                            if (count($update) > 0) {
                                $this->db->where('id', $id);
                                $this->db->update(db_prefix() . 'leads', $update);
                                $this->leads_model->log_lead_activity($id, " Updated Lead");
                            }
                        }

                        if ($tags) {
                            handle_tags_save($tags, $id, 'lead');
                            if (!isset($update['lastcontact'])) {
                                leadLastContactAtUpdate($id);
                            }
                        }

                        if ($lost == 'true') {
                            $this->leads_model->mark_as_lost($id);
                            if (!isset($update['lastcontact'])) {
                                leadLastContactAtUpdate($id);
                            }
                        }
                    }

                    $newleadData = $this->leads_model->get($id);
                    if ($assigned) {
                        if ($oldleadData->assigned != $newleadData->assigned) {
                            $this->leads_model->lead_assigned_member_notification($id, $newleadData->assigned);
                        }
                    }
                }
            }
        }

        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('total_leads_deleted', $total_deleted));
        }
    }


    public function inquriry_form_render()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $getQuestions =  $this->leads_model->get_questions($data['mainGroupId'], $data['subGroupId']);
            if ($getQuestions) {
                $renderArr['questions_data'] = $getQuestions;
                $renderArr['main_group_data'] = $this->leads_model->get_main_group_by_id($data['mainGroupId']);
                if (!empty($data['subGroupId'])) {
                    $renderArr['sub_group_data'] = $this->leads_model->get_sub_group_by_id($data['subGroupId']);
                }
                $result['success'] = true;
                $result['html'] = $this->load->view('admin/leads/inquire-new-form-render', $renderArr, true);
            } else {
                $result['success'] = false;
                $result['message'] = "Questions not found";
            }
            echo json_encode($result);
        }
    }

    public function getInquiryFormLists()
    {
        if (!is_staff_member()) {
            ajax_access_denied();
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $getForms =  $this->leads_model->get_inquiry_forms($data['leadid']);
            $getData['leadData'] = $this->leads_model->get($data['leadid']);
            $getData['countryData'] = get_country($getData['leadData']->country);
            $getData['productname'] = implode(", ", get_tags_in($data['leadid'], 'lead'));
            if ($getForms) {
                $html = "";
                $formcount = 1;
                foreach ($getForms as $key => $form) {
                    $getFormsData =  $this->leads_model->get_inquiry_forms_data($form['id']);
                    if ($getFormsData) {
                        $renderArr = array();
                        $renderArr = $getData;
                        $renderArr['formCount'] = $formcount;
                        $renderArr['form_data'] = $form;
                        $renderArr['form_question_data'] = $getFormsData;
                        $renderArr['main_group_data'] = $this->leads_model->get_main_group_by_id($form['main_group_id']);
                        $renderArr['sub_group_data'] = $this->leads_model->get_sub_group_by_id($form['sub_group_id']);
                        $renderArr['questions_data'] = $getFormsData;
                        $html .= $this->load->view('admin/leads/inquire-new-form-render', $renderArr, true);
                        $formcount++;
                    }
                }
                $result['success'] = true;
                $result['html'] = $html;
            } else {
                $result['success'] = false;
                $result['message'] = "forms not found";
            }
            echo json_encode($result);
        }
    }

    public function save_inquiry_form()
    {
        $data = $this->input->post();
        if ($data['main_group_id']) {
            $getQuestions =  $this->leads_model->get_questions($data['main_group_id'], $data['sub_group_id']);
            if ($getQuestions) {
                if (!empty($data['id'])) {
                    $updateData = false;
                    $getFormsData =  $this->leads_model->get_inquiry_forms_data($data['id']);
                    if ($getFormsData) {
                        foreach ($getFormsData as $key => $item) {
                            $answer = (isset($data[$item['id']])) ? $data[$item['id']] : "";
                            if (!empty($answer)) {
                                $answer = is_array($answer) ? implode(",", $answer) : $answer;
                            }
                            $order_no = (isset($data['order'][$item['id']])) ? $data['order'][$item['id']] : $item['order_no'];
                            if ($item['type'] == "fileupload") {
                                if (isset($_FILES[$item['id']]) && $_FILES[$item['id']]['size']) {
                                    $upload_path = 'uploads/leads/' . $data['leadid'] . '/';
                                    $new_filename = $item['form_id'] . '_' . $item['id'] . '_' . unique_filename($upload_path, $_FILES[$item['id']]['name']);
                                    _maybe_create_upload_path($upload_path);
                                    //delete old file
                                    $old_path = $upload_path . $item['answer'];
                                    if (file_exists($old_path)) {
                                        unlink($old_path);
                                    }
                                    $new_path = $upload_path . '/' . $new_filename;
                                    if (move_uploaded_file($_FILES[$item['id']]['tmp_name'], $new_path)) {
                                        $answer = $new_filename;
                                    } else {
                                        die('Failed to upload file.');
                                    }
                                } else {
                                    $answer = $item['answer'];
                                }
                            }
                            $updateArr = [
                                "order_no" => $order_no,
                                "answer" => $answer,
                                "updated_at" => date('Y-m-d H:i:s'),
                                "updated_by" =>  get_staff_user_id(),
                            ];
                            $updateData =  $this->leads_model->update_inquiry_form_data($updateArr, $item['id']);
                        }
                    }
                    if ($updateData) {
                        $this->leads_model->log_lead_activity($data['leadid'], "Leads Inquiry form Updated [formID : " . $data['id'] . "]");
                        log_activity('Leads Inquiry form Updated [formID : ' . $data['id'] . ']');
                        $result['success'] = true;
                        $result['message'] = "Form updated successfully";
                    } else {
                        $result['success'] = false;
                        $result['message'] = "No Changes found in form data.";
                    }
                } else {
                    //Form Create
                    $formData = [
                        "formkey" => generateUniqueString(),
                        "lead_id" => $data['leadid'],
                        "main_group_id" => $data['main_group_id'],
                        "sub_group_id" => $data['sub_group_id'],
                        "created_at" => date('Y-m-d H:i:s'),
                        "created_by" =>  get_staff_user_id(),
                    ];
                    $formId = $this->leads_model->insert_inquiry_form($formData);
                    if ($formId) {
                        $insertArr = [];
                        foreach ($getQuestions as $key => $item) {
                            $answer = (isset($data[$item['id']])) ? $data[$item['id']] : "";
                            if (!empty($answer)) {
                                $answer = is_array($answer) ? implode(",", $answer) : $answer;
                            }
                            $order_no = (isset($data['order'][$item['id']])) ? $data['order'][$item['id']] : $item['order_no'];
                            $temp = [
                                "form_id" => $formId,
                                "order_no" => $order_no,
                                "is_required" => $item['is_required'],
                                "question_id" => $item['id'],
                                "question" => $item['question'],
                                "type" => $item['type'],
                                "type_options" => $item['type_options'],
                                "answer" => $answer,
                                "created_at" => date('Y-m-d H:i:s'),
                                "created_by" =>  get_staff_user_id(),
                            ];
                            $question_id = $this->leads_model->insert_inquiry_form_data($temp);
                            if ($question_id) {
                                if ($item['type'] == "fileupload") {
                                    if ($item['type'] == "fileupload" && isset($_FILES[$item['id']]) && $_FILES[$item['id']]['size']) {
                                        $upload_path = 'uploads/leads/' . $data['leadid'] . '/';
                                        $new_filename = $formId . '_' . $question_id . '_' . unique_filename($upload_path, $_FILES[$item['id']]['name']);
                                        _maybe_create_upload_path($upload_path);
                                        $new_path = $upload_path . '/' . $new_filename;
                                        if (move_uploaded_file($_FILES[$item['id']]['tmp_name'], $new_path)) {
                                            $this->leads_model->update_inquiry_form_data(["answer" => $new_filename], $question_id);
                                        } else {
                                            die('Failed to upload file.');
                                        }
                                    }
                                }
                            }
                        }
                        $result['success'] = true;
                        $result['message'] = "Form saved successfully";
                    } else {
                        $result['success'] = false;
                        $result['message'] = "Form not saved.";
                    }
                }
            } else {
                $result['success'] = false;
                $result['message'] = "Questions not found.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Main group not selected";
        }
        echo json_encode($result);
    }

    public function delete_inquiry_form()
    {
        $data = $this->input->post();
        if (isset($data['formId'])) {
            $deleteForm =  $this->leads_model->update_inquiry_form([], $data['formId'], true);
            if ($deleteForm) {
                $result['success'] = true;
                $result['message'] = "Form deleted successfully";
            } else {
                $result['success'] = false;
                $result['message'] = "Form not deleted.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Formid required";
        }
        echo json_encode($result);
    }

    public function send_inquiry_form()
    {
        $data = $this->input->post();
        if (isset($data['lead_id']) && isset($data['form_id']) && isset($data['type'])) {
            $getForm =  $this->leads_model->get_inquiry_form_single($data['form_id']);
            if ($getForm) {
                if ($data['type'] == "email") {
                    $lead = $this->leads_model->get($data['lead_id']);
                    $product_name = implode(", ", get_tags_in($data['lead_id'], 'lead'));
                    $product_desc = "I wanted to reach out regarding some inquiries I have";
                    if (!empty($product_name)) {
                        $product_desc = "I am sharing an inquiry form to collect essential details for the " . $product_name . " ";
                    }
                    $lead->product_description = $product_desc;
                    $lead->inquiry_form_link = site_url('forms/cif/') . $getForm["formkey"];
                    $checkEmail = send_lead_template_email_from_webmail('lead_inquiry_form_send', $lead);
                    $checkUpdate = $this->leads_model->update_inquiry_form(["is_email_send" => "1", "email_send_timestamp" => date('Y-m-d H:i:s')], $getForm['id']);
                    if ($checkEmail && $checkUpdate) {
                        $this->leads_model->log_lead_activity($data['lead_id'], "Lead Inquiry form send via Email. Form ID : " . $data['form_id']);
                        leadLastContactAtUpdate($data['lead_id']);
                        $result['success'] = true;
                        $result['message'] = "Email send successfully";
                    } else {
                        $result['success'] = false;
                        $result['message'] = "Email not send.";
                    }
                } else { // Whatsapp send
                    $checkUpdate = $this->leads_model->update_inquiry_form(["is_whatsapp_send" => "1", "whatsapp_send_timestamp" => date('Y-m-d H:i:s')], $getForm['id']);
                    if ($checkUpdate) {
                        $this->leads_model->log_lead_activity($data['lead_id'], "Lead Inquiry form share via Whatsapp. Form ID : " . $data['form_id']);
                        leadLastContactAtUpdate($data['lead_id']);
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

    public function customer_inquiry_form_status_change()
    {
        $data = $this->input->post();
        if (isset($data['formid']) && isset($data['status'])) {
            $formData = $this->leads_model->get_inquiry_form_single($data['formid']);
            $updateData =  $this->leads_model->update_inquiry_form(["is_active" => $data['status']], $data['formid']);
            $status = ($data['status'] == "1") ? "Active " : " In-Active";
            if ($updateData) {
                $this->leads_model->log_lead_activity($formData['lead_id'], "Leads Inquiry form status changed to $status [formID : " . $data['formid'] . "]");
                $result['success'] = true;
                $result['message'] = "Form $status successfully";
            } else {
                $result['success'] = false;
                $result['message'] = "Form $status failed.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid Request";
        }
        echo json_encode($result);
    }

    public function delete_inquiry_file()
    {
        if ($this->input->post()) {
            $post_data = $this->input->post();
            $getQuestion = $this->leads_model->get_inquiry_forms_question($post_data['id']);
            if ($getQuestion) {
                $getForm =  $this->leads_model->get_inquiry_form_single($post_data['formid']);
                $filename = $getQuestion['answer'];
                $upload_path = 'uploads/leads/' . $getForm['lead_id'] . '/';
                $old_path = $upload_path . $filename;
                if (file_exists($old_path)) {
                    if (unlink($old_path)) {
                        $this->leads_model->update_inquiry_form_data(["answer" => ""], $getQuestion['id'], true);
                        $this->leads_model->log_lead_activity($getForm['lead_id'], "Leads Inquiry form file deleted for [Question ID " . $getQuestion['id'] . " and Form ID to " . $getForm['id'] . "]");
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
                $result['message'] = "Question not exists.";
            }
            echo json_encode($result);
        }
    }

    public function lead_vendor_convert()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if (isset($data['type']) && isset($data['leadId'])) {
                $updateCheck = $this->leads_model->update_lead([
                    "is_vendor" => ($data['type'] == "vendor") ? '1' : '0'
                ], $data['leadId']);
                $dynamicMsg =  ($data['type'] == "vendor") ? 'Convert to vendor' : 'Convert to lead';
                if ($updateCheck) {
                    leadLastContactAtUpdate($data['leadId']);
                    $this->leads_model->log_lead_activity($data['leadId'], $dynamicMsg);
                    $result['success'] =  true;
                    $result['message'] =  $dynamicMsg . ' successfully';
                } else {
                    $result['success'] =  false;
                    $result['message'] =  $dynamicMsg . ' failed.';
                }
            } else {
                $result['success'] =  false;
                $result['message'] =  "Invalid request.";
            }
        } else {
            $result['success'] =  false;
            $result['message'] =  "Invalid request.";
        }
        echo json_encode($result);
    }

    public function inquiry_form_approval_status_change()
    {
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
                    $updateArr['customer_form_submitted'] = NULL;
                }
                $checkUpdate = $this->leads_model->update_inquiry_form($updateArr, $post_data['form_id']);
                if ($checkUpdate) {
                    $getForm =  $this->leads_model->get_inquiry_form_single($post_data['form_id']);
                    $this->leads_model->log_lead_activity($getForm['lead_id'], "Inquiry form status changed to " . strtoupper($getForm['form_status']) . " in Form ID : " . $post_data['form_id']);
                    $result['success'] = true;
                    $result['message'] = "Form status successfully changed to " . strtoupper($post_data['form_status']);
                } else {
                    $result['success'] = false;
                    $result['message'] = "Form not updated.";
                }
            } else {
                $result['success'] = false;
                $result['message'] = "Invalid Request.";
            }
            echo json_encode($result);
        }
    }

    public function send_inquiry_form_for_approve_not_approved_notify()
    {
        $data = $this->input->post();
        if (isset($data['formId']) && isset($data['type'])) {
            $getForm =  $this->leads_model->get_inquiry_form_single($data['formId']);
            if ($getForm) {
                if ($data['type'] == "email") {
                    $lead = $this->leads_model->get($getForm['lead_id']);
                    $lead->inquiry_form_link = site_url('forms/cif/') . $getForm["formkey"];
                    $lead->inquiry_form_not_approved_reason = $getForm['reject_note'];
                    $checkEmail = send_lead_template_email_from_webmail(($getForm['form_status'] == "approved") ? "lead_inquiry_form_approved" : "lead_inquiry_form_not_approved", $lead);
                    if ($checkEmail) {
                        $this->leads_model->log_lead_activity($getForm['lead_id'], "Inquiry form " . strtoupper($getForm['form_status']) . " email has been send. Form ID : " . $data['formId']);
                        leadLastContactAtUpdate($getForm['lead_id']);
                        $result['success'] = true;
                        $result['message'] = "Email send successfully";
                    } else {
                        $result['success'] = false;
                        $result['message'] = "Email not send.";
                    }
                } else {
                    $this->leads_model->log_lead_activity($getForm['lead_id'], "Inquiry form " . strtoupper($getForm['form_status']) . " shared via whatsapp. Form ID : " . $data['formId']);
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
    public function leadComposeNewEmail()
    {
        $this->load->model('emails_model');
        $data = $this->input->post();
        log_message('error', 'Data from lead: ' . json_encode($data));

        if (isset($data['leadId']) && isset($data['leadId'])) {
            $lead = $this->leads_model->get($data['leadId']);
            $signatureCode = "<br>Thanks & Regards,<br>" . get_webmail_signature();
            if (isset($data['selected_template']) && !empty($data['selected_template'])) {
                $curTemplate = $this->emails_model->get([
                    'emailtemplateid' => $data['selected_template'],
                ])[0];
            } else {
                $curTemplate = $this->emails_model->get([
                    'slug' => 'leads-follow-up-1',
                    'type' => 'leads-follow-up',
                    'language' => 'english',
                ])[0];
            }

            $templateNumber = "";
            $pattern = '/Template (\d+)/';
            if (preg_match($pattern, $curTemplate['name'], $match)) {
                $templateNumber = $match[1];
            } elseif (strpos($curTemplate['name'], 'Default') !== false) {
                $templateNumber = 'default';
            }
            if (empty($templateNumber)) {
                $result['success'] = false;
                $result['message'] = "something went wrong";
            } else {
                if (is_numeric($templateNumber)) {
                    $templateData = leadsEmailPreview('Lead_followup_template_' . $templateNumber, $lead);
                } else {
                    $templateData = leadsEmailPreview('Lead_followup_template_default', $lead);
                }
                if ($lead) {
                    $result['success'] = true;
                    $result['to'] = $lead->email;
                    $result['body'] = $templateData->message . $signatureCode;
                    $result['subject'] = $templateData->subject;
                    $templates = $this->emails_model->get([
                        'type' => 'leads-follow-up',
                        'language' => 'english',
                    ]);
                    if (!empty($templates)) {
                        $lastElement = array_pop($templates);
                        array_unshift($templates, $lastElement);
                    }
                    $result['templates'] = $templates;
                    $result['selected_template'] = (isset($data['selected_template'])) ? $data['selected_template'] : null;
                } else {
                    $result['success'] = false;
                    $result['message'] = "something went wrong";
                }
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request.";
        }
        echo json_encode($result);
    }
    public function leadSendCustomEmail()
    {
        $data = $this->input->post();
        if (isset($data['email_send_lead_id']) && isset($data['email_send_lead_id'])) {
            $lead = $this->leads_model->get($data['email_send_lead_id']);
            if ($lead) {
                $this->load->library('app_webmails');

                //inject mail tracking code
                $uid = app_generate_hash();
                $data['lead_email_body'] = custom_inject_mail_add_tracking($data['lead_email_body'], $uid);
                $sendData = array();
                $sendData['subject'] = $data['lead_email_subject'];
                $sendData['body'] = $data['lead_email_body'];
                if (isset($_FILES['email_attachments']) && !empty($_FILES['email_attachments']['name'][0])) {
                    $temp_folder = time() . uniqid();
                    for ($key = 0; $key <= count($_FILES['email_attachments']['name']) - 1; $key++) {
                        if ($_FILES['email_attachments']['size'][$key]) {
                            $upload_path = "uploads/temp_mail_attachments/$temp_folder";
                            $new_filename =  unique_filename($upload_path, $_FILES['email_attachments']['name'][$key]);
                            _maybe_create_upload_path($upload_path);
                            $new_path = $upload_path . '/' . $new_filename;
                            if (move_uploaded_file($_FILES['email_attachments']['tmp_name'][$key], $new_path)) {
                                $sendData['uploaded_files'][] = $new_path;
                            }
                        }
                    }
                }
                if (isset($data['to']) && !empty($data['to'])) {
                    $sendData['to'] = array_values(array_column(json_decode($data['to']), 'value'));
                }
                if (isset($data['cc']) && !empty($data['cc'])) {
                    $sendData['cc'] = array_values(array_column(json_decode($data['cc']), 'value'));
                }
                $mailSend = $this->app_webmails->send_email($sendData);
                if ($mailSend) {
                    leadLastContactAtUpdate($data['email_send_lead_id']);
                    $this->leads_model->log_lead_activity($data['email_send_lead_id'], 'Lead New Email Sent [ Lead ID : ' . $data['email_send_lead_id'] . ' ]');
                    // mail record for tracking
                    custom_mail_add_tracking([
                        "uid" => $uid,
                        "email" => $lead->email,
                        "subject" => $data['lead_email_subject'],
                        "message" => $sendData['body'],
                        "rel_type" => 'lead',
                        "rel_id" => $lead->id
                    ]);
                    $result['success'] = true;
                    $result['message'] = "Mail send successfully...";
                } else {
                    $result['success'] = false;
                    $result['message'] = "Error : Mail not send";
                }
            } else {
                $result['success'] = false;
                $result['message'] = "something went wrong";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request.";
        }
        echo json_encode($result);
    }

    public function leadComposeNewWhatsappMessage()
    {
        $this->load->model('emails_model');
        $data = $this->input->post();
        if (isset($data['leadId']) && isset($data['leadId']) && isset($data['phonenumber'])) {

            if (isset($data['selected_template']) && !empty($data['selected_template'])) {
                $curTemplate = $this->emails_model->get([
                    'emailtemplateid' => $data['selected_template'],
                ])[0];
            } else {
                $curTemplate = $this->emails_model->get([
                    'slug' => 'leads-follow-up-1',
                    'type' => 'leads-follow-up',
                    'language' => 'english',
                ])[0];
            }
            $templateNumber = "";
            $pattern = '/Template (\d+)/';
            if (preg_match($pattern, $curTemplate['name'], $match)) {
                $templateNumber = $match[1];
            } elseif (strpos($curTemplate['name'], 'Default') !== false) {
                $templateNumber = 'default';
            }

            if (empty($templateNumber)) {
                $result['success'] = false;
                $result['message'] = "something went wrong";
            } else {
                $lead = $this->leads_model->get($data['leadId']);
                if (is_numeric($templateNumber)) {
                    $templateData = leadsEmailPreview('Lead_followup_template_' . $templateNumber, $lead);
                } else {
                    $templateData = leadsEmailPreview('Lead_followup_template_default', $lead);
                }
                $message = message_html_to_text($templateData->message);
                if ($lead) {
                    $result['success'] = true;
                    $result['to'] = $lead->name . " < " . $data['phonenumber'] . " >";
                    $result['phonenumber'] = $data['phonenumber'];
                    $result['body'] = $message;
                    $templates = $this->emails_model->get([
                        'type' => 'leads-follow-up',
                        'language' => 'english',
                    ]);
                    if (!empty($templates)) {
                        $lastElement = array_pop($templates);
                        array_unshift($templates, $lastElement);
                    }
                    $result['templates'] = $templates;
                    $result['selected_template'] = (isset($data['selected_template'])) ? $data['selected_template'] : null;
                } else {
                    $result['success'] = false;
                    $result['message'] = "something went wrong";
                }
            }
        } else {
            $result['success'] = false;
            $result['message'] = "Invalid request.";
        }
        echo json_encode($result);
    }

    public function leadSendCustomWhatsapp()
    {
        $data = $this->input->post();
        if (isset($data['whatsapp_send_lead_id']) && isset($data['lead_whatsapp_body']) &&  isset($data['lead_whatsapp_number'])) {
            $content = $this->input->post('lead_whatsapp_body', false);
            $phonunumber = $data['lead_whatsapp_number'];
            $encodedMessage = urlencode($content);
            if ($data['submit'] == "web") {
                $whatsappLink = "https://web.whatsapp.com/send?phone={$phonunumber}&text={$encodedMessage}";
            } else {
                $whatsappLink = "https://api.whatsapp.com/send?phone={$phonunumber}&text={$encodedMessage}";
            }
            log_activity('Lead Whatsapp Message Sent [ Lead ID : ' . $data['whatsapp_send_lead_id'] . ' ]');
            leadLastContactAtUpdate($data['whatsapp_send_lead_id']);
            $this->leads_model->log_lead_activity($data['whatsapp_send_lead_id'], 'Lead Whatsapp Message Sent [ Lead ID : ' . $data['whatsapp_send_lead_id'] . ' ]');
            echo json_encode([
                'success' => true,
                'message' => "Whatsapp share succesfully",
                'link' => $whatsappLink,
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Invalid Request"
            ]);
        }
    }

    public function getEmailTrackingList()
    {
        $data = $this->input->post();
        $html = $this->load->view(
            'admin/includes/emails_tracking',
            array(
                'tracked_emails' =>
                get_tracked_emails($data['leadId'], 'lead')
            ),
            true
        );
        echo json_encode([
            'success' => true,
            'html' => $html,
        ]);
    }

    public function customer_assigned_leads_table($customer_id)
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('customer_assigned_leads', ['customer_id' => $customer_id]);
        }
    }

    public function remove_lead_from_customer()
    {
        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            $status = $this->leads_model->remove_customer_lead_assignee($data['lead_id']);
            if ($status) {
                echo json_encode([
                    'success' => true,
                    'message' => "lead successfully removed from this customer.",
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "Error : Lead not removed from this customer.",
                ]);
            }
        }
    }

    public function customer_lead_status_change()
    {
        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            $status = $this->leads_model->customer_lead_status_change($data);
            if ($status) {
                echo json_encode([
                    'success' => true,
                    'message' => "Status changed successfully.",
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "Error : Status not change.",
                ]);
            }
        }
    }

    public function get_lead_data()
    {
        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            $lead = $this->leads_model->get($data['lead_id']);
            if ($lead) {
                $lead->country = get_country_name($lead->country);
                echo json_encode([
                    'success' => true,
                    'lead_data' => $lead,
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => "Error : Something went wrong.",
                ]);
            }
        }
    }
}
