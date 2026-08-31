<?php

defined('BASEPATH') or exit('No direct script access allowed');

$project_id = $this->ci->input->post('project_id');

$aColumns = [
    'date',
    'CONCAT(prefix,number) as invoice_number',
    get_sql_select_client_company(),
    "state",
    db_prefix() . 'countries.short_name',
    'total',
    'duedate',
    db_prefix() . 'invoices.status',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'invoices';

$join = [
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'invoices.clientid',
    'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'invoices.currency',
];

array_push($join, 'LEFT JOIN ' . db_prefix() . 'countries ON ' . db_prefix() . 'countries.country_id = ' . db_prefix() . 'invoices.billing_country');

$custom_fields = get_table_custom_fields('invoice');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);

    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'invoices.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$where  = [];
$filter = [];

if ($this->ci->input->post('not_sent')) {
    array_push($filter, 'AND sent = 0 AND ' . db_prefix() . 'invoices.status NOT IN(' . Invoices_model::STATUS_PAID . ',' . Invoices_model::STATUS_CANCELLED . ')');
}
if ($this->ci->input->post('not_have_payment')) {
    array_push($filter, 'AND ' . db_prefix() . 'invoices.id NOT IN(SELECT invoiceid FROM ' . db_prefix() . 'invoicepaymentrecords) AND ' . db_prefix() . 'invoices.status != ' . Invoices_model::STATUS_CANCELLED);
}
if ($this->ci->input->post('recurring')) {
    array_push($filter, 'AND recurring > 0');
}

$statuses  = $this->ci->invoices_model->get_statuses();
$statusIds = [];
foreach ($statuses as $status) {
    if ($this->ci->input->post('invoices_' . $status)) {
        array_push($statusIds, $status);
    }
}
if (count($statusIds) > 0) {
    array_push($filter, 'AND ' . db_prefix() . 'invoices.status IN (' . implode(', ', $statusIds) . ')');
}

$agents    = $this->ci->invoices_model->get_sale_agents();
$agentsIds = [];
foreach ($agents as $agent) {
    if ($this->ci->input->post('sale_agent_' . $agent['sale_agent'])) {
        array_push($agentsIds, $agent['sale_agent']);
    }
}
if (count($agentsIds) > 0) {
    array_push($filter, 'AND sale_agent IN (' . implode(', ', $agentsIds) . ')');
}

$modesIds = [];
foreach ($data['payment_modes'] as $mode) {
    if ($this->ci->input->post('invoice_payments_by_' . $mode['id'])) {
        array_push($modesIds, $mode['id']);
    }
}
if (count($modesIds) > 0) {
    array_push($where, 'AND ' . db_prefix() . 'invoices.id IN (SELECT invoiceid FROM ' . db_prefix() . 'invoicepaymentrecords WHERE paymentmode IN ("' . implode('", "', $modesIds) . '"))');
}

$years     = $this->ci->invoices_model->get_invoices_years();
$yearArray = [];
foreach ($years as $year) {
    if ($this->ci->input->post('year_' . $year['year'])) {
        array_push($yearArray, $year['year']);
    }
}
if (count($yearArray) > 0) {
    array_push($where, 'AND YEAR(date) IN (' . implode(', ', $yearArray) . ')');
}

if (!has_explicit_financial_year_permission()) {
    array_push($where, 'AND YEAR(date) = ' . date('Y'));
}


// Branch / GST filter
$this->ci->load->model('proposals_model');
$branches_gst  = $this->ci->proposals_model->get_proposals_branches_gst();
$gstFilterVals = [];
foreach ($branches_gst as $branch) {
    $gst_key = md5($branch['gst_number']);
    if ($this->ci->input->post('branch_gst_' . $gst_key)) {
        array_push($gstFilterVals, $this->ci->db->escape($branch['gst_number']));
    }
}
if (count($gstFilterVals) > 0) {
    array_push($where, 'AND ' . db_prefix() . 'invoices.gst_number IN (' . implode(', ', $gstFilterVals) . ')');
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

if ($clientid != '') {
    array_push($where, 'AND ' . db_prefix() . 'invoices.clientid=' . $clientid);
}

if ($project_id) {
    array_push($where, 'AND project_id=' . $project_id);
}

if (!has_permission('invoices', '', 'view')) {
    $userWhere = 'AND ' . get_invoices_where_sql_for_staff(get_staff_user_id());
    array_push($where, $userWhere);
}

$aColumns = hooks()->apply_filters('invoices_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

array_push($where, 'AND ' . db_prefix() . 'invoices.deleted_at IS NULL');
array_push($where, 'AND ' . db_prefix() . 'clients.deleted_at IS NULL');
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    db_prefix() . 'invoices.id',
    db_prefix() . 'invoices.clientid',
    db_prefix() . 'currencies.name as currency_name',
    'project_id',
    'hash',
    'recurring',
    'deleted_customer_name',
]);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = _d($aRow['date']);

    $numberOutput = '';
   
  

    if (is_numeric($clientid) || $project_id) {
        $numberOutput = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '" target="_blank">' . format_invoice_number($aRow['id']) . '</a>';
    } else {
        $numberOutput = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '" onclick="init_invoice(' . $aRow['id'] . '); return false;">' . format_invoice_number($aRow['id']) . '</a>';
    }
    $numberOutput .= '<div class="row-options">';
    //$numberOutput .= '<a href="' . site_url('invoice/' . $aRow['id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';
    if (has_permission('invoices', '', 'edit')) {
        $numberOutput .= '<a href="' . admin_url('invoices/invoice/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }
    $numberOutput .= '</div>';

    if ($aRow['recurring'] > 0) {
        $numberOutput .= '<br /><span class="label label-primary inline-block mtop4"> ' . _l('invoice_recurring_indicator') . '</span>';
    }

    $row[] = $numberOutput;

    if (empty($aRow['deleted_customer_name'])) {
        $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';
    } else {
        $row[] = $aRow['deleted_customer_name'];
    }

    $row[] = $aRow['state'];

    $row[] = $aRow[db_prefix() . 'countries.short_name'];

    $row[] = app_format_money($aRow['total'], $aRow['currency_name']);

    $row[] = _d($aRow['duedate']);

    $row[] = format_invoice_status($aRow[db_prefix() . 'invoices.status']);

    $row[] = "";

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $rowClass = "";
    $valid_packge_list = is_invoice_packlist_completed($aRow['id']);
    if (!$valid_packge_list) {
        $rowClass = " bg-danger";
    }

    $row['DT_RowClass'] = "has-row-options $rowClass";

    $row = hooks()->apply_filters('invoices_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}
