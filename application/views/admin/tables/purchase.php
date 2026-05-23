<?php

defined('BASEPATH') or exit('No direct script access allowed');
$baseCurrency = get_base_currency();

$aColumns = [
    'date',
    'CONCAT(' . db_prefix() . 'purchase.purchase_number_prefix, ' . db_prefix() . 'purchase.purchase_number) as purchase_number',
    'subject',
    'purchase_to',
    'total',
    db_prefix() . 'countries.short_name',
    'state',
    'status',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'purchase';

$where  = [];
$filter = [];

$statuses  = $this->ci->purchase_model->get_statuses();
$statusIds = [];

foreach ($statuses as $status) {
    if ($this->ci->input->post('purchase_' . $status['id'])) {
        array_push($statusIds, $status['id']);
    }
}
if (count($statusIds) > 0) {
    array_push($filter, 'AND status IN (' . implode(', ', $statusIds) . ')');
}

$agents    = $this->ci->purchase_model->get_sale_agents();
$agentsIds = [];
foreach ($agents as $agent) {
    if ($this->ci->input->post('sale_agent_' . $agent['sale_agent'])) {
        array_push($agentsIds, $agent['sale_agent']);
    }
}
if (count($agentsIds) > 0) {
    array_push($filter, 'AND assigned IN (' . implode(', ', $agentsIds) . ')');
}

$years      = $this->ci->purchase_model->get_purchase_years();
$yearsArray = [];
foreach ($years as $year) {
    if ($this->ci->input->post('year_' . $year['year'])) {
        array_push($yearsArray, $year['year']);
    }
}
if (count($yearsArray) > 0) {
    array_push($filter, 'AND YEAR(date) IN (' . implode(', ', $yearsArray) . ')');
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

if (!has_permission('purchase', '', 'view')) {
    $staff_id = get_staff_user_id();
    array_push($where, 'AND (' . db_prefix() . 'purchase.assigned = ' . $staff_id . ' OR ' . db_prefix() . 'purchase.addedfrom = ' . $staff_id . ')');
}

if(isset($vendor_id) && !empty($vendor_id)){
    array_push($where, 'AND '. db_prefix(). 'purchase.vendor_id = '. $vendor_id);
}

array_push($where, 'AND deleted_at IS NULL');
$join          = [];

array_push($join, 'LEFT JOIN ' . db_prefix() . 'countries ON ' . db_prefix() . 'countries.country_id = ' . db_prefix() . 'purchase.country');

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'id',
    'currency',
    'hash',
    'vendor_id',
    db_prefix() . 'purchase.id',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = _d($aRow['date']);

    $numberOutput = '<a href="' . admin_url('purchase/list_purchase/' . $aRow['id']) . '" onclick="init_purchase(' . $aRow['id'] . '); return false;">' . format_purchase_number($aRow['id']) . '</a>';
    if (has_permission('purchase', '', 'edit')) {
        $numberOutput .= '<div class="row-options">';
        $numberOutput .= '<a href="' . admin_url('purchase/purchase/' . $aRow['id']) . '">' . _l('edit') . '</a>';
        $numberOutput .= '</div>';
    }
    $row[] = $numberOutput;

    $row[] =  '<a href="#" onclick="init_lead(' . $aRow['vendor_id'] . ');return false;" target="_blank" data-toggle="tooltip" data-title="Vendor">' . $aRow['purchase_to'] . '</a>';

    $row[] = $aRow['state'];

    $row[] = $aRow[db_prefix() . 'countries.short_name'];

    $row[] = app_format_money($aRow['total'], ($aRow['currency'] != 0 ? get_currency($aRow['currency']) : $baseCurrency));
    
    $row[] = format_purchase_status($aRow['status']);

    $row['DT_RowClass'] = 'has-row-options';

    $row = hooks()->apply_filters('purchase_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}
