<?php

defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();

$aColumns = [
    'id',
    'title',
    'active',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'plant_visitor_types';

$join = [];
$additionalSelect = [];
$where = ["AND deleted_at IS NULL"];

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $key => $aRow) {
    $uid = time() . uniqid();
    $row = [];
    foreach ($aRow as $column => $value) {
        if ($column == "title") {
            $value = '<a class="id-col" href="javascript:;" onclick="openVisitorTypeModal(' . $aRow['id'] . ',this)">' . $aRow['title'] . '</a>
            <div class="row-options"><a href="javascript:;" data-id="' . $aRow['type'] . '" onclick="openVisitorTypeModal(' . $aRow['id'] . ',this)">Edit </a> | <a href=' . admin_url('leads_plant_visit_forms/delete_visitor_type/' . $aRow['id']) . ' class="text-danger _delete">Delete</a></div>';
        } else if ($column == "active") {
            $is_checked = ($aRow['active']) ? 'checked' : '';
            $value = '<div class="mleft5"><div class="onoffswitch">
                        <input type="checkbox" data-id="' . $aRow['id'] . '" name="onoffswitch" class="onoffswitch-checkbox formswitch-visior-type" id="form_switch_' . $uid . '" ' . $is_checked . '>
                        <label class="onoffswitch-label" for="form_switch_' . $uid . '"></label>
                    </div>
                </div>';
        }
        $row[] = $value;
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
