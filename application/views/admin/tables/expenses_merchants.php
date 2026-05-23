<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'name',
    'details',
];
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'expense_merchant';
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], [
    'id',
]);
$output  = $result['output'];
$rResult = $result['rResult'];
$no = 0;
foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'id') {
            $_data = ++$no;
        }
        if ($aColumns[$i] == 'name') {
            $_data = '<a href="#" onclick="edit_merchant(this,' . $aRow['id'] . '); return false;" data-name="' . $aRow['name'] . '" data-details="' . clear_textarea_breaks($aRow['details']) . '">' . $_data . '</a>';
        }

        $row[] = $_data;
    }
    $options = icon_btn('#', 'pencil-square-o', 'btn-default', [
        'onclick'          => 'edit_merchant(this,' . $aRow['id'] . '); return false;',
        'data-name'        => $aRow['name'],
        'data-details' => clear_textarea_breaks($aRow['details']),
    ]);
    $row[]              = $options .= icon_btn('expenses_merchants/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    $output['aaData'][] = $row;
}
