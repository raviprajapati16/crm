<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'draft_title',
    'null as contracts_count',
    'null as action',
];
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'contract_draft';

$where = [];
array_push($where, " AND main_type = $main_type");
array_push($where, " AND sub_type = $sub_type");
array_push($where, " AND deleted_at IS NULL");

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, ['id', 'main_type', 'sub_type']);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $key => $aRow) {
    $row = [];
    foreach ($aRow as $column => $value) {
        if ($column == 'draft_title') {
            $value = '<a data-id="' . $aRow['id'] . '" href="' . admin_url('contracts/draft/' . $aRow['main_type'] . '/' . $aRow['sub_type'] . '/' . $aRow['id']) . '">' . htmlspecialchars($aRow['draft_title']) . '</a>';
        }
        if ($column == 'contracts_count') {
            $value = total_rows(db_prefix() . 'contracts', ['draft_id' => $aRow['id'], 'deleted_at' => null]);
        }
        if ($column == 'action') {
            $value = '<a class="btn btn-xs btn-info" data-toggle="tooltip" data-title="Edit" href="' . admin_url('contracts/draft/' . $aRow['main_type'] . '/' . $aRow['sub_type'] . '/' . $aRow['id']) . '"><i class="fa fa-pencil-square-o"></i></a>';
            $value .= "&nbsp;<a data-toggle='tooltip' data-title='Copy' class='btn btn-xs btn-success _copy' href='#'><i class='fa fa-copy'></i></a> ";
            $value .= '&nbsp;' . icon_btn('contracts/delete_contract_draft/' . $aRow['id'], 'trash', 'btn-danger _delete', [
                'data-toggle' => 'tooltip',
                'data-title' => "Delete",
            ]);
        }
        $row[] = $value;
    }
    $output['aaData'][] = $row;
}
