<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'null as checkbox',
    'order_no',
    'question',
    'type',
    'is_active',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'lead_questions';
$additionalSelect = ['id'];

$join = [];
$where = [];
array_push($where, " AND main_group_id = " . $main_group_id);
if ($sub_group_id) {
    array_push($where, " AND sub_group_id = " . $sub_group_id);
}
array_push($where, 'AND datedeleted IS NULL');

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

// echo "<pre>";
// print_r($rResult);
// exit;
foreach ($rResult as $key => $aRow) {
    $row = [];
    foreach ($aRow as $column => $value) {
        $_data = $value;
        if ($column == 'question' || $column == 'id') {
            $_data = '<a class="id-col" data-order-no="' . $aRow['order_no'] . '" data-id="' . $aRow['id'] . '" href="javascript:;" onclick="openQuestionModal(' . $aRow['id'] . ')">' . $_data . '</a>';
            if ($column == 'question') {
                $_data .= '<div class="row-options">';
                $_data .= '<a href="javascript:;" onclick="openQuestionModal(' . $aRow['id'] . ')">' . _l('edit') . '</a>';
                $_data .= ' | <a href="' . admin_url('leads_questionnaire_group/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
                $_data .= '</div>';
            }
            $row[] = $_data;
        } elseif ($column == 'is_active') {
            $checked = '';
            if ($aRow['is_active'] == 1) {
                $checked = 'checked';
            }
            $_data = '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="' . admin_url() . 'leads_questionnaire_group/change_question_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" ' . $checked . '>
                <label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
            </div>';
            // For exporting
            $_data .= '<span class="hide">' . ($checked == 'checked' ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';
            $row[] = $_data;
        } else if ($column == 'type') {
            $row[] = ucfirst($_data);
        } else if ($column == 'checkbox') {
            $row[] = '<div class="dragger todo-dragger"></div><div class="checkbox mleft15"><input type="checkbox" name="question_id[]" class="single-checkbox" form="copyForm" value="'.$aRow['id'].'"/><label></label></div>';
        } else {
            $row[] = $_data;
        }
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
