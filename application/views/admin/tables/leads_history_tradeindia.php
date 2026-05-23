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
$sTable = db_prefix() . 'tradeindia_leads';
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
            if (isset($lead_data['sender_name'])) {
                $senderName = (isset($lead_data['sender_name'])) ? $lead_data['sender_name'] : "";
            }
            $row[] = $senderName;
        } else if ($column == 'email') {
            if (isset($lead_data['sender_email'])) {
                $row[] = (isset($lead_data['sender_email'])) ? $lead_data['sender_email'] : "";
            } else {
                $row[] = "";
            }
        } else if ($column == 'contact_no') {
            if (isset($lead_data['sender_mobile'])) {
                $row[] = (isset($lead_data['sender_mobile'])) ? $lead_data['sender_mobile'] : "";
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
            if (isset($lead_data['message'])) {
                $row[] = (isset($lead_data['message'])) ? $lead_data['message'] : "";
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
