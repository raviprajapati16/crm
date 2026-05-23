<?php
$CI = &get_instance();
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'sheet_title',
    'created_at',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'google_sheets';
$additionalSelect = ['id'];

$join = [];
$where = [];

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

$no = $CI->input->post('start');
$i = 0;
foreach ($rResult as $key => $aRow) {
    $row = [];
    foreach ($aRow as $column => $value) {
        if ($column == 'id') {
            $row[] = $i += 1;
        }else if ($column == "sheet_title") {
            $html = $value;
            $html .= '<div class="row-options">';
            $html .= '<a href="' . admin_url('google_sheets/view_sheets_records/' . $aRow['id']) . '">View Records</a>';
            $html .= ' | <a href="' . admin_url('google_sheets/edit/' . $aRow['id']) . '">Edit</a>';
            $html .= ' | <a href="' . admin_url('google_sheets/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('Delete') . '</a>';
            $html .= '</div>';
            $row[] = $html;
        } else if ($column == 'created_at') {
            $row[] = _dt($value);
        } else {
            $row[] = $value;
        }
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
