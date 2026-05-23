<?php
$CI = &get_instance();
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'null as sr_no',
    'type',
    'button_text',
    'active',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'tutorial_links';
$additionalSelect = ['id'];

$join = [];
$where = [];

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

$no = $CI->input->post('start');
foreach ($rResult as $key => $aRow) {
    $row = [];
    foreach ($aRow as $column => $value) {
        $_data = $value;
        if ($column == 'type') {
            $_data .= '<div class="row-options">';
            if (has_permission('tutorials_links', '', 'edit')) {
                $_data .= '<a href="javascript:;" onclick="openTutorialModal(' . $aRow['id'] . ')">' . _l('Edit') . '</a>';
            }
            $_data .= '</div>';
            $row[] = $_data;
        } else if ($column == 'active') {
            if ($value == "1") {
                $_data = '<div class="label label-success">Active</div>';
            } else {
                $_data = '<div class="label label-danger">In-active</div>';
            }
            $row[] = $_data;
        } else if ($column == 'sr_no') {
            $row[] = $no += 1;
        } else {
            $row[] = $_data;
        }
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
