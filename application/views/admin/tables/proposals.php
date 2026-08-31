<?php

defined('BASEPATH') or exit('No direct script access allowed');

$baseCurrency = get_base_currency();

$aColumns = [
    'date',
    'CONCAT(' . db_prefix() . 'proposals.proposal_number_prefix, ' . db_prefix() . 'proposals.proposal_number) as proposal_number',
    'subject',
    'proposal_to',
    'total',
    'open_till',
    db_prefix() . 'countries.short_name',
    'state',
    'status',
    'addedfrom',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'proposals';

$where = [];
$filter = [];

if ($this->ci->input->post('leads_related')) {
    array_push($filter, 'OR rel_type="lead"');
}
if ($this->ci->input->post('customers_related')) {
    array_push($filter, 'OR rel_type="customer"');
}
if ($this->ci->input->post('expired')) {
    array_push($filter, 'OR open_till IS NOT NULL AND open_till <"' . date('Y-m-d') . '" AND status NOT IN(2,3)');
}

$statuses = $this->ci->proposals_model->get_statuses();
$statusIds = [];

foreach ($statuses as $status) {
    if ($this->ci->input->post('proposals_' . $status)) {
        array_push($statusIds, $status);
    }
}
if (count($statusIds) > 0) {
    array_push($filter, 'AND status IN (' . implode(', ', $statusIds) . ')');
}

$agents = $this->ci->proposals_model->get_sale_agents();
$agentsIds = [];
foreach ($agents as $agent) {
    if ($this->ci->input->post('sale_agent_' . $agent['sale_agent'])) {
        array_push($agentsIds, $agent['sale_agent']);
    }
}
if (count($agentsIds) > 0) {
    array_push($filter, 'AND assigned IN (' . implode(', ', $agentsIds) . ')');
}

$years = $this->ci->proposals_model->get_proposals_years();
$yearsArray = [];
foreach ($years as $year) {
    if ($this->ci->input->post('year_' . $year['year'])) {
        array_push($yearsArray, $year['year']);
    }
}
if (count($yearsArray) > 0) {
    array_push($filter, 'AND YEAR(date) IN (' . implode(', ', $yearsArray) . ')');
}

if (!has_explicit_financial_year_permission()) {
    array_push($filter, 'AND YEAR(date) = ' . date('Y'));
}

// Branch / GST filter
$branches_gst  = $this->ci->proposals_model->get_proposals_branches_gst();
$gstFilterVals = [];
foreach ($branches_gst as $branch) {
    $gst_key = md5($branch['gst_number']);
    if ($this->ci->input->post('branch_gst_' . $gst_key)) {
        array_push($gstFilterVals, $this->ci->db->escape($branch['gst_number']));
    }
}
if (count($gstFilterVals) > 0) {
    array_push($filter, 'AND proposal_gst_number IN (' . implode(', ', $gstFilterVals) . ')');
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

if (!has_permission('proposals', '', 'view')) {
    array_push($where, 'AND ' . get_proposals_sql_where_staff(get_staff_user_id()));
}

array_push($where, 'AND deleted_at IS NULL');
$join = [];
$custom_fields = get_table_custom_fields('proposal');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);

    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'proposals.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$aColumns = hooks()->apply_filters('proposals_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

array_push($join, 'LEFT JOIN ' . db_prefix() . 'countries ON ' . db_prefix() . 'countries.country_id = ' . db_prefix() . 'proposals.country');

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'currency',
    'rel_id',
    'rel_type',
    'invoice_id',
    'hash',
    'download_request',
    db_prefix() . 'proposals.id',
]);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = _d($aRow['date']);

    $numberOutput = '<a href="' . admin_url('proposals/list_proposals/' . $aRow['id']) . '" onclick="init_proposal(' . $aRow['id'] . '); return false;">' . format_proposal_number($aRow['id']) . '</a>';

    $numberOutput .= '<div class="row-options">';

    $numberOutput .= '<a href="' . site_url('proposal/' . $aRow['id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';
 if (has_permission('proposals', '', 'edit')) {
    if ($aRow['status'] == 6 || $aRow['status'] == 4) {
        $numberOutput .= ' | <a href="' . admin_url('proposals/proposal/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }
}

    $numberOutput .= '</div>';

    $row[] = $numberOutput;

    if ($aRow['rel_type'] == 'lead') {
        $toOutput = '<a href="#" onclick="init_lead(' . $aRow['rel_id'] . ');return false;" target="_blank" data-toggle="tooltip" data-title="' . _l('lead') . '">' . $aRow['proposal_to'] . '</a>';
    } elseif ($aRow['rel_type'] == 'customer') {
        $toOutput = '<a href="' . admin_url('clients/client/' . $aRow['rel_id']) . '" target="_blank" data-toggle="tooltip" data-title="' . _l('client') . '">' . $aRow['proposal_to'] . '</a>';
    }
    $row[] = $toOutput;

    $row[] = $aRow['state'];

    $row[] = $aRow[db_prefix() . 'countries.short_name'];

    $amount = app_format_money($aRow['total'], ($aRow['currency'] != 0 ? get_currency($aRow['currency']) : $baseCurrency));
    if ($aRow['invoice_id']) {
        $amount .= '<br /> <span class="hide"> - </span><span class="text-success">' . _l('estimate_invoiced') . '</span>';
    }
    $row[] = $amount;

    $row[] = _d($aRow['open_till']);

    $row[] = format_proposal_status($aRow['status']);
     $row[] = $aRow['addedfrom'];

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $row['DT_RowClass'] = ($aRow['download_request'] == "2") ? ' has-row-options alert-warning' : 'has-row-options';

    $row = hooks()->apply_filters('proposals_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}