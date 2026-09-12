<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'name',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'freight_groups';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Group Name
    $name = '<a href="#" onclick="edit_group(this,' . $aRow['id'] . '); return false;" data-name="' . $aRow['name'] . '">' . $aRow['name'] . '</a>';
    $name .= '<div class="row-options">';
    $name .= '<a href="#" onclick="edit_group(this,' . $aRow['id'] . '); return false;" data-name="' . $aRow['name'] . '">' . _l('edit') . '</a>';
    $name .= ' | <a href="' . admin_url('freight_master/delete_group/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    $name .= '</div>';
    
    $row[] = $name;

    // Options
    $options = icon_btn('#', 'pencil-square-o', 'btn-default', [
        'onclick' => 'edit_group(this,' . $aRow['id'] . '); return false;',
        'data-name' => $aRow['name'],
    ]);
    $options .= icon_btn('freight_master/delete_group/' . $aRow['id'], 'remove', 'btn-danger _delete');
    
    $row[]   = $options;

    $output['aaData'][] = $row;
}
