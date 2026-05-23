<?php
$CI = &get_instance();
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'name',
];

$join = [];

$additionalSelect = ['id'];
$where = [];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'contact_book_category';
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output       = $result['output'];
$rResult      = $result['rResult'];
$no = $CI->input->post('start');
foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'name') {
            $_data .= '<div class="row-options">';
            $_data .= '<a href="javascript:;" data-id="' . $aRow['id'] . '" data-name="' . $aRow['name'] . '" onclick="openCategoryModal(' . $aRow['id'] . ')">' . _l('Edit') . '</a>';
            $_data .= ' | <a href="' . admin_url('contact_book_category/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('Delete') . '</a>';
            $_data .= '</div>';
        } elseif ($aColumns[$i] == 'id') {
            $_data = $no += 1;
        }
        $row[] = $_data;
    }
    $output['aaData'][] = $row;
}
