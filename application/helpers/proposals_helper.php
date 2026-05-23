<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Check if proposal hash is equal
 * @param  mixed $id   proposal id
 * @param  string $hash proposal hash
 * @return void
 */
function check_proposal_restrictions($id, $hash)
{
    $CI = &get_instance();
    $CI->load->model('proposals_model');
    if (!$hash || !$id) {
        show_404();
    }
    $proposal = $CI->proposals_model->get($id);
    if (!$proposal || ($proposal->hash != $hash)) {
        show_404();
    }
}

/**
 * Check if proposal email template for expiry reminders is enabled
 * @return boolean
 */
function is_proposals_email_expiry_reminder_enabled()
{
    return total_rows(db_prefix() . 'emailtemplates', ['slug' => 'proposal-expiry-reminder', 'active' => 1]) > 0;
}

/**
 * Check if there are sources for sending proposal expiry reminders
 * Will be either email or SMS
 * @return boolean
 */
function is_proposals_expiry_reminders_enabled()
{
    return is_proposals_email_expiry_reminder_enabled() || is_sms_trigger_active(SMS_TRIGGER_PROPOSAL_EXP_REMINDER);
}

/**
 * Return proposal status color class based on twitter bootstrap
 * @param  mixed  $id
 * @param  boolean $replace_default_by_muted
 * @return string
 */
function proposal_status_color_class($id, $replace_default_by_muted = false)
{

    if ($id == 6) {
        $class = 'default';
    } elseif ($id == 4) {
        $class = 'info';
    } elseif ($id == 3) {
        $class = 'success';
    } elseif ($id == 1) {
        $class = 'warning';
    } elseif ($id == 5) {
        $class = 'success';
    } elseif ($id == 2) {
        $class = 'danger';
    }
    if ($class == 'default') {
        if ($replace_default_by_muted == true) {
            $class = 'muted';
        }
    }

    return $class;
}
/**
 * Format proposal status with label or not
 * @param  mixed  $status  proposal status id
 * @param  string  $classes additional label classes
 * @param  boolean $label   to include the label or return just translated text
 * @return string
 */
function format_proposal_status($status, $classes = '', $label = true)
{
    $id = $status;

    if ($status == 6) {
        $status      = _l('proposal_status_draft');
        $label_class = 'default';
    } elseif ($status == 4) {
        $status      = _l('proposal_status_sent');
        $label_class = 'info';
    } elseif ($status == 3) {
        $status      = _l('proposal_status_accepted');
        $label_class = 'success';
    } elseif ($status == 1) {
        $status      = _l('proposal_status_in_progress');
        $label_class = 'warning';
    } elseif ($status == 5) {
        $status      = _l('proposal_status_completed');
        $label_class = 'success';
    } elseif ($status == 2) {
        $status      = _l('proposal_status_rejected');
        $label_class = 'danger';
    }

    if ($label == true) {
        return '<span class="label label-' . $label_class . ' ' . $classes . ' s-status proposal-status-' . $id . '">' . $status . '</span>';
    }

    return $status;
}

/**
 * Function that format proposal number based on the prefix option and the proposal id
 * @param  mixed $id proposal id
 * @return string
 */

function proposal_number_prefix()
{
    $prefix = get_option('proposal_number_prefix');
    return replace_dynamic_prefix($prefix);
}


function format_proposal_number($id)
{
    $CI = &get_instance();
    $CI->db->where('id', $id);
    $proposal = $CI->db->get(db_prefix() . 'proposals')->row();
    if (!empty($proposal)) {
        return $proposal->proposal_number_prefix . $proposal->proposal_number;
    }
    return "N/A";
    //return get_option('proposal_number_prefix') . str_pad($id, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
}


/**
 * Function that return proposal item taxes based on passed item id
 * @param  mixed $itemid
 * @return array
 */
function get_proposal_item_taxes($itemid)
{
    $CI = &get_instance();
    $CI->db->where('itemid', $itemid);
    $CI->db->where('rel_type', 'proposal');
    $taxes = $CI->db->get(db_prefix() . 'item_tax')->result_array();
    $i     = 0;
    foreach ($taxes as $tax) {
        $taxes[$i]['taxname'] = $tax['taxname'] . '|' . $tax['taxrate'];
        $i++;
    }

    return $taxes;
}


/**
 * Calculate proposal percent by status
 * @param  mixed $status          proposal status
 * @param  mixed $total_estimates in case the total is calculated in other place
 * @return array
 */
function get_proposals_percent_by_status($status, $total_proposals = '')
{
    $has_permission_view                 = has_permission('proposals', '', 'view');
    $has_permission_view_own             = has_permission('proposals', '', 'view_own');
    $allow_staff_view_proposals_assigned = get_option('allow_staff_view_proposals_assigned');
    $staffId                             = get_staff_user_id();

    $whereUser = db_prefix() . 'proposals.deleted_at IS NULL';
    if (!$has_permission_view) {
        if ($has_permission_view_own) {
            $whereUser = '(addedfrom=' . $staffId;
            if ($allow_staff_view_proposals_assigned == 1) {
                $whereUser .= ' OR assigned=' . $staffId;
            }
            $whereUser .= ')';
        } else {
            $whereUser .= 'assigned=' . $staffId;
        }
    }

    if (!is_numeric($total_proposals)) {
        $total_proposals = total_rows(db_prefix() . 'proposals', $whereUser);
    }

    $data            = [];
    $total_by_status = 0;
    $where = db_prefix() . 'proposals.deleted_at IS NULL AND status=' . $status;

    if (!$has_permission_view) {
        $where .= ' AND (' . $whereUser . ')';
    }

    $total_by_status = total_rows(db_prefix() . 'proposals', $where);
    $percent         = ($total_proposals > 0 ? number_format(($total_by_status * 100) / $total_proposals, 2) : 0);

    $data['total_by_status'] = $total_by_status;
    $data['percent']         = $percent;
    $data['total']           = $total_proposals;

    return $data;
}

/**
 * Function that will search possible proposal templates in applicaion/views/admin/proposal/templates
 * Will return any found files and user will be able to add new template
 * @return array
 */
function get_proposal_templates()
{
    $proposal_templates = [];
    if (is_dir(VIEWPATH . 'admin/proposals/templates')) {
        foreach (list_files(VIEWPATH . 'admin/proposals/templates') as $template) {
            $proposal_templates[] = $template;
        }
    }

    return $proposal_templates;
}
/**
 * Check if staff member can view proposal
 * @param  mixed $id proposal id
 * @param  mixed $staff_id
 * @return boolean
 */
function user_can_view_proposal($id, $staff_id = false)
{
    $CI = &get_instance();

    $staff_id = $staff_id ? $staff_id : get_staff_user_id();

    if (has_permission('proposals', $staff_id, 'view')) {
        return true;
    }

    $CI->db->select('id, addedfrom, assigned');
    $CI->db->from(db_prefix() . 'proposals');
    $CI->db->where('id', $id);
    $proposal = $CI->db->get()->row();

    if ((has_permission('proposals', $staff_id, 'view_own') && ($proposal->addedfrom == $staff_id)
        || ($proposal->assigned == $staff_id || proposal_manager_permission_check($id)) && get_option('allow_staff_view_proposals_assigned') == 1)) {
        return true;
    }

    return false;
}
function parse_proposal_content_merge_fields($proposal)
{
    $id           = is_array($proposal) ? $proposal['id'] : $proposal->id;
    $CI = &get_instance();

    $CI->load->library('merge_fields/proposals_merge_fields');
    $CI->load->library('merge_fields/other_merge_fields');

    $merge_fields = [];
    $merge_fields = array_merge($merge_fields, $CI->proposals_merge_fields->format($id));
    $merge_fields = array_merge($merge_fields, $CI->other_merge_fields->format());
    foreach ($merge_fields as $key => $val) {
        $content = is_array($proposal) ? $proposal['content'] : $proposal->content;

        if (stripos($content, $key) !== false) {
            if (is_array($proposal)) {
                $proposal['content'] = str_ireplace($key, $val, $content);
            } else {
                $proposal->content = str_ireplace($key, $val, $content);
            }
        } else {
            if (is_array($proposal)) {
                $proposal['content'] = str_ireplace($key, '', $content);
            } else {
                $proposal->content = str_ireplace($key, '', $content);
            }
        }
    }

    return $proposal;
}

/**
 * Check if staff member have assigned proposals / added as sale agent
 * @param  mixed $staff_id staff id to check
 * @return boolean
 */
function staff_has_assigned_proposals($staff_id = '')
{
    $CI         = &get_instance();
    $staff_id = is_numeric($staff_id) ? $staff_id : get_staff_user_id();
    $cache    = $CI->app_object_cache->get('staff-total-assigned-proposals-' . $staff_id);
    if (is_numeric($cache)) {
        $result = $cache;
    } else {
        $result = total_rows(db_prefix() . 'proposals', ['assigned' => $staff_id]);
        $CI->app_object_cache->add('staff-total-assigned-proposals-' . $staff_id, $result);
    }

    return $result > 0 ? true : false;
}

function get_proposals_sql_where_staff($staff_id)
{
    $has_permission_view_own            = has_permission('proposals', '', 'view_own');
    $allow_staff_view_invoices_assigned = get_option('allow_staff_view_proposals_assigned');
    $whereUser = '';
    if ($has_permission_view_own) {
        if (manager_employee_data_access_permission_check("proposals")) {
            $whereUser = '( addedfrom IN (' . get_manager_assigned_staff_ids('', true) . ')';
            if ($allow_staff_view_invoices_assigned == 1) {
                $whereUser .= ' OR assigned IN (' . get_manager_assigned_staff_ids('', true) . ')';
            }
            $whereUser .= ')';
        } else {
            $whereUser = '( addedfrom=' . $staff_id;
            // $whereUser = '((' . db_prefix() . 'proposals.addedfrom=' . $staff_id . ' AND ' . db_prefix() . 'proposals.addedfrom IN (SELECT staff_id FROM ' . db_prefix() . 'staff_permissions WHERE feature = "proposals" AND capability="view_own"))';
            if ($allow_staff_view_invoices_assigned == 1) {
                $whereUser .= ' OR assigned=' . $staff_id;
            }
            $whereUser .= ')';
        }
    } else {
        $whereUser .= 'assigned=' . $staff_id;
    }

    return $whereUser;
}

function get_proposal_invoice_id($proposal_id)
{
    $invoice_id = "";
    $CI = &get_instance();
    $CI->load->model('proposals_model');
    $proposalData = $CI->proposals_model->get($proposal_id);
    if (!empty($proposalData->invoice_id)) {
        $invoice_id = $proposalData->invoice_id;
    }
    // else if (!empty($proposalData->estimate_id)) {
    //     $CI->db->where('id', $proposalData->estimate_id);
    //     $estimate = $CI->db->get(db_prefix() . 'estimates')->row();
    //     if (!empty($estimate) && !empty($estimate->invoiceid) && $estimate->invoiceid != "0") {
    //         $invoice_id = $estimate->invoiceid;
    //     }
    // }
    if (!empty($invoice_id)) {
        return $invoice_id;
    }
    return false;
}

function get_proposal_id_by_invoice($invoice_id)
{
    $proposal_id = "";
    $CI = &get_instance();
    $CI->load->model('proposals_model');
    $CI->db->where('invoice_id', $invoice_id);
    $proposalData = $CI->db->get(db_prefix() . 'proposals')->row();
    if (!empty($proposalData)) {
        $proposal_id = $proposalData->id;
    }
    // else {
    //     $CI->db->where('invoiceid', $invoice_id);
    //     $estimate = $CI->db->get(db_prefix() . 'estimates')->row();
    //     if (!empty($estimate)) {
    //         $CI->db->where('estimate_id', $estimate->id);
    //         $proposalData = $CI->db->get(db_prefix() . 'proposals')->row();
    //         if (!empty($proposalData)) {
    //             $proposal_id = $proposalData->id;
    //         }
    //     }
    // }
    if (!empty($proposal_id)) {
        return $proposal_id;
    }
    return null;
}

function get_proposal_payment_data($proposal_id)
{
    $CI = &get_instance();
    $CI->load->model('proposals_model');
    $CI->load->model('invoices_model');
    $CI->load->model('credit_notes_model');
    $data['total_amount'] = 0;
    $data['remaining_amount'] = 0;
    $data['total_received_amount'] = 0;
    $data['applied_credits'] = 0;
    $invoice_id = get_proposal_invoice_id($proposal_id);
    if ($invoice_id) {
        $invoice_data = $CI->invoices_model->get($invoice_id);
        $data['total_amount'] = (isset($invoice_data)) ? $invoice_data->total : 0;
        $invoice_data = $CI->invoices_model->get($invoice_id);
        $applied_credits_data = $CI->credit_notes_model->get_applied_invoice_credits($invoice_id);
        if (!empty($applied_credits_data)) {
            foreach ($applied_credits_data as $key => $item) {
                $data['applied_credits'] += $item['amount'];
            }
        }
    } else {
        $proposal_data = $CI->proposals_model->get($proposal_id);
        $data['total_amount'] = (isset($proposal_data)) ? $proposal_data->total : 0;
    }
    $payments = $CI->proposals_model->get_proposal_payments($proposal_id);
    if (!empty($payments)) {
        $data['total_received_amount'] = array_sum(array_column($payments, 'amount'));
    }
    $data['remaining_amount'] =  $data['total_amount'] - $data['applied_credits'] - $data['total_received_amount'];
    return $data;
}


function get_proposal_client_id($proposal_id)
{
    $CI = &get_instance();
    $CI->load->model('proposals_model');
    $proposal_data = $CI->proposals_model->get($proposal_id);
    if (!empty($proposal_data)) {
        if ($proposal_data->rel_type == "lead") {
            return get_client_id_by_lead_id($proposal_data->rel_id);
        } else {
            return $proposal_data->rel_id;
        }
    }
}

function get_proposal_customer_data($proposal_id, $for = '')
{
    $CI = &get_instance();
    $CI->load->model('proposals_model');
    $proposal = $CI->proposals_model->get($proposal_id);

    $countryCode = '';
    $countryName = '';
    $country     = get_country($proposal->country);

    if ($country) {
        $countryCode = $country->iso2;
        $countryName = $country->short_name;
    }

    // Get proposal-related details
    $proposalTo = $proposal->proposal_to;
    $phone      = $proposal->phone;
    $email      = $proposal->email;

    $rel_data = get_relation_data($proposal->rel_type, $proposal->rel_id);
    $gst_in = ($proposal->rel_type == "lead") ? $rel_data->gst_in : $rel_data->vat;

    // Prepare data in an array instead of formatting for HTML
    $data = [
        'proposal_to' => $proposalTo,
        'phone'       => $phone,
        'email'       => $email,
        'address'     => $proposal->address,
        'city'        => $proposal->city,
        'state'       => $proposal->state,
        'country_code' => $countryCode,
        'country_name' => $countryName,
        'zip_code'    => $proposal->zip,
        'gst_in'      => $gst_in,
        'rel_type'    => $proposal->rel_type,
        'rel_id'      => $proposal->rel_id
    ];

    // Add custom fields to the array
    $whereCF = [];
    if (is_custom_fields_for_customers_portal()) {
        $whereCF['show_on_client_portal'] = 1;
    }
    $customFieldsProposals = get_custom_fields('proposal', $whereCF);

    foreach ($customFieldsProposals as $field) {
        $value = get_custom_field_value($proposal->id, $field['id'], 'proposal');
        $data['custom_fields'][$field['name']] = $value;
    }

    // Apply filters if needed
    return hooks()->apply_filters('proposal_info_array', $data, ['proposal' => $proposal, 'for' => $for]);
}


function get_proposal_payments_record($proposal_id)
{
    $CI = &get_instance();
    $CI->load->model('proposals_model');
    return $CI->proposals_model->get_proposal_payments($proposal_id);
}


function proposal_manager_permission_check($proposal_id)
{
    $CI = &get_instance();
    $CI->load->model('proposals_model');
    $managerStaffIds = get_manager_assigned_staff_ids();
    if (manager_employee_data_access_permission_check("proposals")) {
        $proposal = $CI->proposals_model->get($proposal_id);
        if (in_array($proposal->assigned, $managerStaffIds) || in_array($proposal->addedfrom, $managerStaffIds) || $proposal->assigned == get_staff_user_id() || $proposal->addedfrom == get_staff_user_id()) {
            return true;
        }
    }
    return false;
}
