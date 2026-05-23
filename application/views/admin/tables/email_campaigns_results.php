<?php
$CI = &get_instance();
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'null as sr_no',
    'name',
    'email',
    'status',
    'mail_send_from_id',
    'email_sent_at',
    'email_open_at'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'emailcampaign_queue';
$additionalSelect = ['rel_id', 'status_message', 'rel_type', 'send_from'];

$join = [];
$where = [];

array_push($where, 'AND campaign_id = ' . $id);

$status = $CI->input->post('status');
if (!empty($status)) {
    array_push($where, "AND status = '" . $status . "'");
}

$rel_type = $CI->input->post('rel_type');
if (!empty($rel_type)) {
    array_push($where, "AND rel_type = '" . $rel_type . "'");
}

$email_status = $CI->input->post('email_status');
if (!empty($email_status)) {
    if ($email_status == "open") {
        array_push($where, "AND email_open_at IS NOT NULL");
    } else {
        array_push($where, "AND email_open_at IS NULL");
    }
}

$mail_id = $CI->input->post('sent_from');
$mail_send_from = $CI->input->post('sent_from_type');
if (!empty($mail_send_from) && !empty($mail_id)) {
    array_push($where, "AND mail_send_from_id = '" . $mail_id . "'");
    array_push($where, "AND send_from = '" . $mail_send_from . "'");
}

$date_range = $CI->input->post('date_range');
if (!empty($date_range)) {
    $dateRange = explode(" - ", $date_range);
    $startdate = to_sql_date($dateRange[0]);
    $enddate = to_sql_date($dateRange[1]);
    array_push($where, "AND DATE(email_sent_at) >= '" . $startdate . "'");
    array_push($where, "AND DATE(email_sent_at) <= '" . $enddate . "'");
}

$join = [];

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

$no = $CI->input->post('start');
foreach ($rResult as $key => $aRow) {
    $row = [];
    foreach ($aRow as $column => $value) {
        $_data = $value;
        if ($column == 'status') {
            if ($value == 'queue') {
                $_data = "<span data-toggle='tooltip' data-title='In Queue' class='label label-warning'>In Queue</span>";
            } else if ($value == 'sent') {
                $_data = "<span data-toggle='tooltip' data-title='" . $aRow['status_message'] . "' class='label label-success'>Sent</span>";
            } else {
                $_data = "<span data-toggle='tooltip' data-title='" . $aRow['status_message'] . "' class='label label-danger'>Failed</span>";
            }
            $row[] = $_data;
        } else if ($column == 'email_sent_at' || $column == 'email_open_at') {
            $_data = _dt($value);
            $row[] = $_data;
        } else if ($column == 'mail_send_from_id') {
            $_data = "";
            if ($aRow['send_from'] == "custom_email") {
                $mailData = get_custom_email_data($aRow['mail_send_from_id']);
                $_data = ($mailData) ?  $mailData->email : "";
                $_data .= " (Custom)";
            } else {
                $staff = get_staff($aRow['mail_send_from_id']);
                $_data = ($staff) ?  $staff->webmail_email : "";
                $_data .= " (Staff)";
            }

            $row[] = $_data;
        } else if ($column == 'name') {
            $_data = $value;
            $hrefAttr = "javascript:;";
            if ($aRow['rel_type'] == "lead") {
                $hrefAttr = 'href="javascript:;" onclick="init_lead(' . $aRow['rel_id'] . ');return false;"';
                $_data .= " (Lead)";
            } else if ($aRow['rel_type'] == "client_contact") {
                $clientid = get_client_id_by_contact_id($aRow['rel_id']);
                $hrefAttr = 'href="' . admin_url('clients/client/' . $clientid . '?group=contacts') . '"';
                $_data .= " (Client)";
            } else if ($aRow['rel_type'] == "staff") {
                $hrefAttr = 'href="' . admin_url('hrm/member/' . $aRow['rel_id']) . '"';
                $_data .= " (Staff)";
            } else if ($aRow['rel_type'] == "email_list") {
                $CI->db->select('list_id');
                $CI->db->from(db_prefix() . 'emailcampaign_mail_list_items');
                $CI->db->where('id', $aRow['rel_id']);
                $query = $CI->db->get();
                $listItem = $query->row();
                $list_id = "";
                if (!empty($listItem)) {
                    $list_id = $listItem->list_id;
                    $hrefAttr = 'href="' . admin_url('email_campaign_mail_list/mail_list_view/' . $list_id) . '"';
                }
                $_data .= " (Email List)";
            }
            $row[]    = '<a ' . $hrefAttr . '>' .  $_data . '</a>';
        } else if ($column == 'sr_no') {
            $row[] = $no += 1;
        } else {
            $row[] = $_data;
        }
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
