<?php
$CI = &get_instance();
defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    'sheet_record_id',
    db_prefix() . 'google_sheets_data.record_data',
    'is_imported',
    'lead_id',
    db_prefix() . 'google_sheets.sheet_title',
    db_prefix() . 'google_sheets.sheet_url'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'google_sheets_data';
$additionalSelect = [
    db_prefix() . 'google_sheets_data.id',
    db_prefix() . 'google_sheets.column_mapping',
    db_prefix() . 'google_sheets.created_at'
];

$join = [
    'JOIN ' . db_prefix() . 'google_sheets ON ' . db_prefix() . 'google_sheets.id = ' . db_prefix() . 'google_sheets_data.sheet_id',
];

$where = [];
array_push($where, "AND " . db_prefix() . "google_sheets_data.sheet_id = $sheet_id");

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $aRow['sheet_record_id'];
    $recordData = json_decode($aRow[db_prefix() . 'google_sheets_data.record_data'], true);
    if (!empty($recordData)) {
        $columnMapping = json_decode($aRow['column_mapping'], true);
        $importantFields = ['name', 'email', 'phonenumber'];
        foreach ($importantFields as $field) {
            if (isset($columnMapping[$field]) && !empty($columnMapping[$field])) {
                $text = $recordData[$columnMapping[$field]];
                $row[] = str_replace("p:","",$text);
            } else {
                $row[] = "-";
            }
        }
    } else {
        $row[] = "-";
        $row[] = "-";
        $row[] = "-";
    }

    $importStatus = '';
    if ($aRow['is_imported'] == 1) {
        $importStatus = '<span class="label label-success">Imported</span>';
    } else {
        $importStatus = '<span class="label label-warning">Pending</span>';
    }
    $row[] = $importStatus;



    $actions = '<div>';
    $actions .= '<button type="button" class="btn btn-xs btn-info view-record" data-record="' . htmlspecialchars(json_encode($aRow[db_prefix() . 'google_sheets_data.record_data']), ENT_QUOTES, 'UTF-8') . '" data-id="' . $aRow['id'] . '"><i class="fa fa-eye" aria-hidden="true"></i></button>';    $actions .= '</div>';
    $row[] = $actions;

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
