<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'name',
    '(SELECT name FROM ' . db_prefix() . 'freight_groups WHERE id = ' . db_prefix() . 'freight_sizes.group_id) as group_name',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'freight_sizes';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id', 'group_id']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Size Name
    $name = '<a href="#" onclick="edit_size(this,' . $aRow['id'] . '); return false;" data-name="' . $aRow['name'] . '" data-group-id="' . $aRow['group_id'] . '">' . $aRow['name'] . '</a>';
    $name .= '<div class="row-options">';
    $name .= '<a href="#" onclick="edit_size(this,' . $aRow['id'] . '); return false;" data-name="' . $aRow['name'] . '" data-group-id="' . $aRow['group_id'] . '">' . _l('edit') . '</a>';
    $name .= ' | <a href="' . admin_url('freight_master/delete_size/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    $name .= '</div>';
    
    $row[] = $name;

    // Group Name
    $row[] = $aRow['group_name'];

    // Options
    $options = icon_btn('#', 'pencil-square-o', 'btn-default', [
        'onclick' => 'edit_size(this,' . $aRow['id'] . '); return false;',
        'data-name' => $aRow['name'],
        'data-group-id' => $aRow['group_id'],
    ]);
    $options .= icon_btn('freight_master/delete_size/' . $aRow['id'], 'remove', 'btn-danger _delete');
    
    $row[]   = $options;

    $output['aaData'][] = $row;
}
