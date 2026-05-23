<?php
$CI = &get_instance();
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'null as checkbox',
    'null as name',
    'null as email',
    'null as contact_no',
    'null as subject',
    'null as message',
    'is_imported',
    'date_created'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'indiamart_leads';
$additionalSelect = ['id', 'lead_data', 'is_imported'];

$join = [];
$where = [];

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

$no = $CI->input->post('start');

foreach ($rResult as $key => $aRow) {
    $lead_data = json_decode($aRow['lead_data'], true);
    $row = [];
    foreach ($aRow as $column => $value) {
        if ($column == 'checkbox') {
            if ($aRow['is_imported'] == '0') {
                $row[] = "<input type='checkbox' name='lead_ids[]' class='import_id' value='{$aRow['id']}'>";
            } else {
                $row[] = "-";
            }
        } else if ($column == 'name') {
            $senderName = "";
            if (isset($lead_data['SENDERNAME'])) {
                $senderName = (isset($lead_data['SENDERNAME'])) ? $lead_data['SENDERNAME'] : "";
            } else if (isset($lead_data['SENDER_NAME'])) {
                $senderName = (isset($lead_data['SENDER_NAME'])) ? $lead_data['SENDER_NAME'] : "";
            }
            $row[] = $senderName;
        } else if ($column == 'email') {
            if (isset($lead_data['SENDEREMAIL'])) {
                $row[] = (isset($lead_data['SENDEREMAIL'])) ? $lead_data['SENDEREMAIL'] : "";
            } else if (isset($lead_data['SENDER_EMAIL'])) {
                $row[] = (isset($lead_data['SENDER_EMAIL'])) ? $lead_data['SENDER_EMAIL'] : "";
            } else {
                $row[] = "";
            }
        } else if ($column == 'contact_no') {
            if (isset($lead_data['MOB'])) {
                $row[] = (isset($lead_data['MOB'])) ? $lead_data['MOB'] : "";
            } else if (isset($lead_data['SENDER_MOBILE'])) {
                $row[] = (isset($lead_data['SENDER_MOBILE'])) ? $lead_data['SENDER_MOBILE'] : "";
            } else {
                $row[] = "";
            }
        } else if ($column == 'subject') {
            if (isset($lead_data['SUBJECT'])) {
                $row[] = (isset($lead_data['SUBJECT'])) ? $lead_data['SUBJECT'] : "";
            } else {
                $row[] = "";
            }
        } else if ($column == 'message') {
            if (isset($lead_data['ENQ_MESSAGE'])) {
                $row[] = (isset($lead_data['ENQ_MESSAGE'])) ? $lead_data['ENQ_MESSAGE'] : "";
            } else if (isset($lead_data['QUERY_MESSAGE'])) {
                $row[] = (isset($lead_data['QUERY_MESSAGE'])) ? $lead_data['QUERY_MESSAGE'] : "";
            } else {
                $row[] = "";
            }
        } else if ($column == 'date_created') {
            $row[] = _dt($value);
        } else if ($column == 'is_imported') {
            if ($value == '0') {
                $row[] = "<span class='label label-danger'>Not Imported</span>";
            } else {
                $row[] = "<span class='label label-success'>Imported</span>";
            }
        }
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
