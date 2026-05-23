<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'name',
    'null as contracts_count',
    'null as sub_category_counts',
    'null as action',
];
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'contracts_types';

$where = ['AND deleted_at IS NULL'];


$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, ['id']);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $key => $aRow) {
    $row = [];
    foreach ($aRow as $column => $value) {
        if ($column == 'name') {
            $value = '<a href="#" onclick="edit_type(this,' . $aRow['id'] . '); return false;" data-id="'.$aRow['id'].'" data-name="' . htmlspecialchars($aRow['name']) . '">' . htmlspecialchars($aRow['name']) . '</a>';
        }
        if ($column == 'contracts_count') {
            $value = total_rows(db_prefix() . 'contracts', ['contract_type' => $aRow['id'], 'deleted_at' => null]);
        }
        if ($column == 'sub_category_counts') {
            $value = total_rows(db_prefix() . 'contract_subtype', ['main_type' => $aRow['id'], 'deleted_at' => NULL]);
        }
        if ($column == 'action') {
            $value .= "<a class='btn btn-xs btn-primary' data-toggle='tooltip' data-title='Manage Sub Types' href='" . admin_url('contracts/sub_types/' . $aRow['id']) . "'>Sub Types</a> ";
            $value .= "<a data-toggle='tooltip' data-title='Copy' class='btn btn-xs btn-success _copy' href='#'><i class='fa fa-copy'></i></a> ";
            $value .= icon_btn('#', 'pencil-square-o', 'btn-info', [
                'onclick' => 'edit_type(this,' . $aRow['id'] . '); return false;',
                'data-toggle' => 'tooltip',
                'data-title' => "Edit",
                'data-name' => htmlspecialchars($aRow['name'])
            ]);
            $value .= icon_btn('contracts/delete_contract_type/' . $aRow['id'], 'trash', 'btn-danger _delete', [
                'data-toggle' => 'tooltip',
                'data-title' => "Delete",
            ]);
        }
        $row[] = $value;
    }
    $output['aaData'][] = $row;
}
