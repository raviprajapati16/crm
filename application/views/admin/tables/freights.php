<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'from_city',
    '(SELECT short_name FROM ' . db_prefix() . 'countries WHERE country_id = ' . db_prefix() . 'freights.to_country) as to_country_name',
    'to_city',
    'group_name' => '(SELECT name FROM ' . db_prefix() . 'freight_groups WHERE id = ' . db_prefix() . 'freights.group_id)',
    'size_name' => '(SELECT name FROM ' . db_prefix() . 'freight_sizes WHERE id = ' . db_prefix() . 'freights.size_id)',
    'carrier',
    'freight_cost',
    'transit_time',
    'updated_at',
];

// Fix for subqueries format for Codeigniter DataTables
$aColumns = [
    'id',
    'from_city',
    '(SELECT short_name FROM ' . db_prefix() . 'countries WHERE country_id = ' . db_prefix() . 'freights.to_country) as to_country_name',
    'to_city',
    '(SELECT name FROM ' . db_prefix() . 'freight_groups WHERE id = ' . db_prefix() . 'freights.group_id) as group_name',
    '(SELECT name FROM ' . db_prefix() . 'freight_sizes WHERE id = ' . db_prefix() . 'freights.size_id) as size_name',
    'carrier',
    'freight_cost',
    'transit_time',
    'updated_at',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'freights';

$where = [];
if (!has_permission('freights', '', 'view')) {
    array_push($where, 'AND ' . db_prefix() . 'freights.created_by = ' . get_staff_user_id());
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, ['id', 'from_country', 'to_country', 'group_id', 'size_id', 'from_city', 'to_city']);

$output  = $result['output'];
$rResult = $result['rResult'];

$ci = &get_instance();
$start = intval($ci->input->post('start'));
$sr_no = $start + 1;

foreach ($rResult as $aRow) {
    $row = [];

    // From Location
    // In perfex, we'd ideally load the city/state names too, but for datatable performance, just showing country or doing more joins is possible. 
    // Let's just show ID and From/To country for simplicity here, or we can fetch city names if needed.

    // ID + Options
    $row[] = $sr_no++;

    $row[] = $aRow['from_city'];
    $row[] = $aRow['to_country_name'];
    $row[] = $aRow['to_city'];
    $row[] = $aRow['group_name'];
    $row[] = $aRow['size_name'];
    $row[] = $aRow['carrier'];
    $row[] = $aRow['freight_cost'];
    $row[] = $aRow['transit_time'];
    $row[] = ($aRow['updated_at'] ? _dt($aRow['updated_at']) : '-');

    $options = '';
    $options = '<div style="white-space: nowrap;">';
    if (has_permission('freights', '', 'view') || has_permission('freights', '', 'view_own')) {
        $options .= '<a href="#" onclick="view_freight(' . $aRow['id'] . '); return false;" class="btn btn-info btn-icon"><i class="fa fa-eye"></i></a>';
    }
    if (has_permission('freights', '', 'edit')) {
        $options .= '<a href="' . admin_url('freights/freight/' . $aRow['id']) . '" class="btn btn-default btn-icon"><i class="fa fa-pencil-square-o"></i></a>';
    }
    if (has_permission('freights', '', 'delete')) {
        $options .= '<a href="' . admin_url('freights/delete/' . $aRow['id']) . '" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>';
    }
    $options .= '</div>';
    $row[] = $options;

    $output['aaData'][] = $row;
}
