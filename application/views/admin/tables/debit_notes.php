<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'number',
    'date',
    db_prefix() . 'leads.name as vendor_name',
    db_prefix() . 'debitnotes.status as status',
    'reference_no',
    'total',
    '(SELECT ' . db_prefix() . 'debitnotes.total - (
      (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'debits WHERE ' . db_prefix() . 'debits.debit_id=' . db_prefix() . 'debitnotes.id AND ' . db_prefix() . 'debits.deleted_at IS NULL)
      +
      (SELECT COALESCE(SUM(amount),0) FROM ' . db_prefix() . 'debitnote_refunds WHERE ' . db_prefix() . 'debitnote_refunds.debit_note_id=' . db_prefix() . 'debitnotes.id AND ' . db_prefix() . 'debitnote_refunds.deleted_at IS NULL)
      )
    ) as remaining_amount',
];

$join = [
    'LEFT JOIN ' . db_prefix() . 'leads ON ' . db_prefix() . 'leads.id = ' . db_prefix() . 'debitnotes.vendorid AND ' . db_prefix() . 'leads.isDeleted = "false"',
    'LEFT JOIN ' . db_prefix() . 'currencies ON ' . db_prefix() . 'currencies.id = ' . db_prefix() . 'debitnotes.currency',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'debitnotes';

$custom_fields = get_table_custom_fields('debit_note');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'debitnotes.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$where = [];
$filter = [];

if (!has_permission('debit_notes', '', 'view')) {
    array_push($where, 'AND ' . db_prefix() . 'debitnotes.addedfrom=' . get_staff_user_id());
}

$statuses = $this->ci->debit_notes_model->get_statuses();
$statusIds = [];

foreach ($statuses as $status) {
    if ($this->ci->input->post('debit_notes_status_' . $status['id'])) {
        array_push($statusIds, $status['id']);
    }
}

array_push($where, 'AND ' . db_prefix() . 'debitnotes.deleted_at IS NULL');

if (count($statusIds) > 0) {
    array_push($filter, 'AND ' . db_prefix() . 'debitnotes.status IN (' . implode(', ', $statusIds) . ')');
}

$years = $this->ci->debit_notes_model->get_debits_years();
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

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    db_prefix() . 'debitnotes.id',
    db_prefix() . 'debitnotes.vendorid',
    db_prefix() . 'currencies.name as currency_name',
    'deleted_vendor_name',
]);


$output = $result['output'];
$rResult = $result['rResult'];
$i = 1;
foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $i;
    $numberOutput = '';
    $numberOutput = '<a href="' . admin_url('debit_notes/list_debit_notes/' . $aRow['id']) . '" onclick="init_debit_note(' . $aRow['id'] . '); return false;">' . format_debit_note_number($aRow['id']) . '</a>';

    $numberOutput .= '<div class="row-options">';

    if (has_permission('debit_notes', '', 'edit')) {
        $numberOutput .= '<a href="' . admin_url('debit_notes/debit_note/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;

    $row[] = _d($aRow['date']);

    if (empty($aRow['vendor_name'])) {
        $row[] = $aRow['deleted_vendor_name'];
    } else {
        $row[] = '<a href="javascript:;" onclick="init_lead(' . $aRow['vendorid'] . ')">' . $aRow['vendor_name'] . '</a>';
    }

    $row[] = format_debit_note_status($aRow['status']);

    $row[] = $aRow['reference_no'];

    $row[] = app_format_money($aRow['total'], $aRow['currency_name']);

    $row[] = app_format_money($aRow['remaining_amount'], $aRow['currency_name']);

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $output['aaData'][] = $row;
    $i++;
}

echo json_encode($output);
die();