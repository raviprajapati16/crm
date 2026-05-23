<?php

defined('BASEPATH') or exit('No direct script access allowed');

function check_contract_restrictions($id, $hash)
{
    $CI = &get_instance();
    $CI->load->model('contracts_model');

    if (!$hash || !$id) {
        show_404();
    }

    if (!is_client_logged_in() && !is_staff_logged_in()) {
        if (get_option('view_contract_only_logged_in') == 1) {
            redirect_after_login_to_current_url();
            redirect(site_url('authentication/login'));
        }
    }

    $contract = $CI->contracts_model->get($id);
    if (!$contract || ($contract->hash != $hash)) {
        show_404();
    }
    // Do one more check
    if (!is_staff_logged_in()) {
        if (get_option('view_contract_only_logged_in') == 1) {
            if ($contract->client != get_client_user_id()) {
                show_404();
            }
        }
    }
}

/**
 * Function that will search possible contracts templates in applicaion/views/admin/contracts/templates
 * Will return any found files and user will be able to add new template
 * @return array
 */
function get_contract_templates()
{
    $contract_templates = [];
    if (is_dir(VIEWPATH . 'admin/contracts/templates')) {
        foreach (list_files(VIEWPATH . 'admin/contracts/templates') as $template) {
            $contract_templates[] = $template;
        }
    }

    return $contract_templates;
}

function send_contract_signed_notification_to_staff($contract_id)
{
    $CI = &get_instance();
    $CI->db->where('id', $contract_id);
    $contract = $CI->db->get(db_prefix() . 'contracts')->row();

    if (!$contract) {
        return false;
    }

    // Get creator
    $CI->db->select('staffid, email');
    $CI->db->where('staffid', $contract->addedfrom);
    $staff_contract = $CI->db->get(db_prefix() . 'staff')->result_array();

    $notifiedUsers = [];
    foreach ($staff_contract as $member) {
        $notified = add_notification([
            'description'     => 'not_contract_signed',
            'touserid'        => $member['staffid'],
            'fromcompany'     => 1,
            'fromuserid'      => null,
            'link'            => 'contracts/contract/' . $contract->id,
            'additional_data' => serialize([
                '<b>' . $contract->subject . '</b>',
            ]),
        ]);

        if ($notified) {
            array_push($notifiedUsers, $member['staffid']);
        }

        send_mail_template('contract_signed_to_staff', $contract, $member);
    }

    pusher_trigger_notification($notifiedUsers);
}

function get_contract_status($id, $labelClass = "")
{
    $CI = &get_instance();
    $CI->load->model('contracts_model');

    $contract = $CI->contracts_model->get($id);
    if (!$contract) {
        return false;
    }
    $label = "";
    if ($contract->contract_status == "verified") {
        $label = "<span class='label label-success $labelClass'>Agreement Verified</span>";
    } else if ($contract->contract_status == "in review") {
        $label = "<span class='label label-warning $labelClass'>In Review</span>";
    } else if ($contract->contract_status == "sent") {
        $label = "<span class='label label-primary $labelClass'>Sent</span>";
    } else if ($contract->contract_status == "cancelled") {
        $label = "<span class='label label-danger $labelClass'>Cancelled</span>";
    } else {
        $label = "<span class='label label-info $labelClass'>Draft</span>";
    }
    return $label;
}

function get_contract_sign_count($id)
{
    $CI = &get_instance();
    $CI->load->model('contracts_model');
    return $CI->contracts_model->get_contract_sign_count($id);
}


function get_contract_linked_proposals($id)
{
    $CI = &get_instance();
    $CI->db->select('proposal_id');
    $CI->db->where('contract_id', $id);
    $CI->db->from(db_prefix() . 'contract_proposals');
    $query = $CI->db->get();
    if ($query->num_rows() > 0) {
        $result = $query->result_array();
        return array_column($result, 'proposal_id');
    }
    return [];
}

function update_contract_proposals($contract_id, $proposal_ids = [])
{
    $CI = &get_instance();
    $CI->db->select('id, proposal_id');
    $CI->db->from(db_prefix() . 'contract_proposals');
    $CI->db->where('contract_id', $contract_id);
    $query = $CI->db->get();
    $existing_proposals = $query->result_array();
    $existing_proposal_ids = array_column($existing_proposals, 'proposal_id', 'id');
    foreach ($proposal_ids as $proposal_id) {
        if (!in_array($proposal_id, $existing_proposal_ids)) {
            $CI->db->insert(db_prefix() . 'contract_proposals', [
                'contract_id' => $contract_id,
                'proposal_id' => $proposal_id,
            ]);
        } else {
            foreach ($existing_proposals as $key => $existing_proposal) {
                if ($existing_proposal['proposal_id'] == $proposal_id) {
                    unset($existing_proposals[$key]);
                    break;
                }
            }
        }
    }
    if (!empty($existing_proposals)) {
        $ids_to_delete = array_column($existing_proposals, 'id');
        $CI->db->where_in('id', $ids_to_delete);
        $CI->db->delete(db_prefix() . 'contract_proposals');
    }
}


function get_contract_payment_data($contract_id)
{
    $data['total_amount'] = 0;
    $data['remaining_amount'] = 0;
    $data['total_received_amount'] = 0;
    $proposal_ids = get_contract_linked_proposals($contract_id);
    if (!empty($proposal_ids)) {
        foreach ($proposal_ids as $id) {
            $payment_data = get_proposal_payment_data($id);
            $data['total_amount'] += $payment_data['total_amount'];
            $data['remaining_amount'] += $payment_data['remaining_amount'];
            $data['total_received_amount'] += $payment_data['total_received_amount'];
        }
    }
    $data['remaining_amount'] =  $data['total_amount'] - $data['total_received_amount'];
    return $data;
}


function update_payment_terms_status($contract_id)
{
    $CI = &get_instance();
    $CI->load->model('proposals_model');
    $proposal_ids = get_contract_linked_proposals($contract_id);
    if (!empty($proposal_ids)) {
        $total_received = 0;

        // Get total received amounts
        foreach ($proposal_ids as $proposal_id) {
            $paymentsData = $CI->proposals_model->get_proposal_payments($proposal_id);
            if (!empty($paymentsData)) {
                foreach ($paymentsData as $payment) {
                    $total_received += $payment['amount'] ?: 0;
                }
            }
        }

        // Update payment terms
        $CI->db->where('contract_id', $contract_id);
        $CI->db->where('deleted_at IS NULL');
        $CI->db->order_by('id', 'ASC');
        $payment_terms = $CI->db->get(db_prefix() . 'contract_payments')->result();

        if (!empty($payment_terms)) {
            foreach ($payment_terms as $term) {
                $status = "";
                if ($total_received == 0) {
                    $status = 'Pending';
                } elseif ($total_received >= $term->amount) {
                    $status = 'Received';
                    $total_received -= $term->amount;
                } elseif ($total_received > 0 && $total_received < $term->amount) {
                    $status = 'Partially Received';
                    $total_received = 0;
                } else {
                    $status = 'Pending';
                }

                $CI->db->where('id', $term->id);
                $CI->db->update(db_prefix() . 'contract_payments', ['status' => $status]);
            }
        }
    }
}

function get_contract_currency($contract_id)
{
    $CI = &get_instance();
    $CI->load->model('contracts_model');
    $proposal_ids = get_contract_linked_proposals($contract_id);
    if (!empty($proposal_ids)) {
        $CI->db->select('currency');
        $CI->db->where('id', $proposal_ids[0]);
        $CI->db->from(db_prefix() . 'proposals');
        $query = $CI->db->get();
        if ($query->num_rows() > 0) {
            return $query->row()->currency;
        }
    } else {
        $contract =  $CI->contracts_model->get($contract_id);
        $clientData = get_client($contract->client);
        if (!empty($clientData) && $clientData->default_currency != 0) {
            return $clientData->default_currency;
        }
    }
    return get_base_currency()->id;
}

function get_contract_term_payment_data($term_id)
{
    $result['total_amount'] = 0;
    $result['received_for_term'] = 0;
    $result['remaining_amount'] = 0;
    $CI = &get_instance();
    $CI->load->model('contracts_model');
    $paymentTermData = $CI->contracts_model->get_payment_term($term_id);
    $received_for_term = 0;
    if (!empty($paymentTermData)) {
        $total_received = get_contract_payment_data($paymentTermData['contract_id'])['total_received_amount'];
        $allPaymentTerms = $CI->contracts_model->get_payment_terms($paymentTermData['contract_id']);
        if (!empty($allPaymentTerms)) {
            foreach ($allPaymentTerms as $item) {
                $received_for_term = 0;
                if ($total_received == 0) {
                    $received_for_term = 0;
                } elseif ($total_received >= $item['amount']) {
                    $received_for_term = $item['amount'];
                    $total_received -= $item['amount'];
                } elseif ($total_received > 0 && $total_received < $item['amount']) {
                    $received_for_term = $total_received;
                    $total_received = 0;
                }
                if ($item['id'] == $term_id) {
                    $result['total_amount'] = $item['amount'];
                    $result['received_for_term'] = $received_for_term;
                    $result['remaining_amount'] = $result['total_amount'] - $result['received_for_term'];
                }
            }
        }
    }
    return $result;
}

function contract_number_prefix()
{
    $prefix = get_option('contract_number_prefix');
    return replace_dynamic_prefix($prefix);
}

function format_contract_number($id)
{
    $CI = &get_instance();
    $CI->db->where('id', $id);
    $proposal = $CI->db->get(db_prefix() . 'contracts')->row();
    if (!empty($proposal)) {
        return $proposal->prefix . $proposal->number;
    }
    return "N/A";
}

function format_contract_customer_info($rel_type, $rel_id)
{
    $CI = &get_instance();
    $content = "";
    if ($rel_type == "customer") {
        $CI->db->where('userid', $rel_id);
        $customer = $CI->db->get(db_prefix() . 'clients')->row();
        if (!empty($customer)) {
            $content = "<strong>" . $customer->company . "</strong>";
            $content .= "<br>" . $customer->address;
            $content .= "<br>" . $customer->city . ', ' . $customer->state;
            $content .= "<br>" . get_country_name($customer->country) . ', ' . $customer->zip;
            $content .= "<br>Ph. No. :" . $customer->phonenumber;
        }
    } else { // vendor
        $CI->db->where('id', $rel_id);
        $vendor = $CI->db->get(db_prefix() . 'leads')->row();
        if (!empty($vendor)) {
            $content = "<strong>" . $vendor->name . "</strong>";
            $content .= "<br>" . $vendor->address;
            $content .= "<br>" . $vendor->city . ', ' . $vendor->state;
            $content .= "<br>" . get_country_name($vendor->country) . ', ' . $vendor->zip;
            $content .= "<br>Ph. No. :" . $vendor->phonenumber;
        }
    }
    return $content;
}
