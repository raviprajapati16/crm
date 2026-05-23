<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'expenses.id as id',
    db_prefix() . 'expenses.created_by',
    db_prefix() . 'expenses.date as expense_date',
    db_prefix() . 'expenses_categories.name as category_name',
    db_prefix() . 'expense_merchant.name as merchant_name',
    'amount',
    db_prefix() . 'expense_reports.report_name as report_name',
    db_prefix() . 'expense_reports.status as expense_status',
];
$join = [
    'JOIN ' . db_prefix() . 'expenses_categories ON ' . db_prefix() . 'expenses_categories.id = ' . db_prefix() . 'expenses.category',
    'LEFT JOIN ' . db_prefix() . 'expense_merchant ON ' . db_prefix() . 'expense_merchant.id = ' . db_prefix() . 'expenses.merchant_id',
    'LEFT JOIN ' . db_prefix() . 'expense_reports ON ' . db_prefix() . 'expense_reports.id = ' . db_prefix() . 'expenses.report_id',
    'LEFT JOIN ' . db_prefix() . 'staff t_submitter ON t_submitter.staffid = ' . db_prefix() . 'expenses.created_by',
];


$where  = [];
$filter = [];

if (!has_permission('expenses', '', 'view')) {
    array_push($where, 'AND ' . db_prefix() . 'expenses.created_by=' . get_staff_user_id());
}

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'expenses';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'category',
    'report_id',
    'CONCAT(t_submitter.firstname, " ", t_submitter.lastname) as submitter_name',
]);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $value = '<a href="javascript:;" class="text-info view-expense" data-id="' . $aRow['id'] . '">' . expenseIdFormat($aRow['id']) . '</a>';
    $value .= '<div class="row-options">';
    if (empty($aRow['expense_status']) || $aRow['expense_status'] == "Draft" || $aRow['expense_status'] == "Rejected") {
        if (has_permission('expenses', '', 'edit')) {
            $value .= '<a href="' . admin_url('expenses/expense/' . $aRow['id']) . '">' . _l('edit') . '</a>';
        }
        if (has_permission('expenses', '', 'delete') && empty($aRow['report_id'])) {
            $value .= ' | <a href="' . admin_url('expenses/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
        }
    }

    $value .= '</div>';

    $row[] = $value;

    $row[] = $aRow['submitter_name'];

    $row[] = _d($aRow['expense_date']);

    $row[] = $aRow['category_name'];

    $row[] = $aRow['merchant_name'];

    $row[] = app_format_money($aRow['amount'], get_base_currency());

    $report = "-";
    if (!empty($aRow['report_id'])) {
        $reportName =  expenseReportIdFormat($aRow['report_id']) . " - " . $aRow['report_name'];
        $report = '<a href="' . admin_url('expense_reports/report/' . $aRow['report_id']) . '" target="_blank">' . $reportName . '</a>';
    }
    $row[] = $report;

    if (empty($aRow['expense_status'])) {
        $row[] = '<span class="label label-default">Unreported</span>';
    } else if ($aRow['expense_status'] == "Draft") {
        $row[] = '<span class="label label-default">Unsubmitted</span>';
    } else  if ($aRow['expense_status'] == "Awaiting Approval") {
        $row[] = '<span class="label label-warning">' . $aRow['expense_status'] . '</span>';
    } else if ($aRow['expense_status'] == "Approved" || $aRow['expense_status'] == "Reimbursed") {
        $row[] = '<span class="label label-success">' . $aRow['expense_status'] . '</span>';
    } else if ($aRow['expense_status'] == "Rejected") {
        $row[] = '<span class="label label-danger">' . $aRow['expense_status'] . '</span>';
    }

    $row['DT_RowClass'] = 'has-row-options';

    $row = hooks()->apply_filters('expenses_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}
