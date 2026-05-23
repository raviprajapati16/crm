<?php
$CI = &get_instance();
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'expense_reports.id',
    db_prefix() . 'expense_reports.report_name',
    'start_date',
    'status',
    db_prefix() . 'expense_reports.created_by',
    'status_updated_by',
    'null as total',
    'null as to_be_reimbursed',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'expense_reports';

$additionalSelect = [
    'start_date',
    'end_date',
    'status_updated_at',
    'CONCAT(t_submitter.firstname, " ", t_submitter.lastname) as submitter_name',
    'CONCAT(t_status_updated.firstname, " ", t_status_updated.lastname) as status_change_by',
    'et.name as trip_name',
    '(SELECT SUM(amount) FROM ' . db_prefix() . 'expenses WHERE report_id = ' . db_prefix() . 'expense_reports.id) as total',
    '(
    IFNULL((
        SELECT SUM(amount)
        FROM ' . db_prefix() . 'expenses
        WHERE report_id = ' . db_prefix() . 'expense_reports.id
        AND reimbursement = 1
    ), 0)
    -
    IFNULL((
        SELECT SUM(amount)
        FROM ' . db_prefix() . 'expense_advance
        WHERE report_id = ' . db_prefix() . 'expense_reports.id
        AND status = "Approved"
    ), 0)
) AS to_be_reimbursed',

];



$join = [
    'LEFT JOIN ' . db_prefix() . 'staff t_submitter ON t_submitter.staffid = ' . db_prefix() . 'expense_reports.created_by',
    'LEFT JOIN ' . db_prefix() . 'staff t_status_updated ON t_status_updated.staffid = ' . db_prefix() . 'expense_reports.status_updated_by',
    'LEFT JOIN ' . db_prefix() . 'expense_trip et ON et.id = ' . db_prefix() . 'expense_reports.trip_id',
];

$where = [];
if (!has_permission('expense_reports', '', 'view')) {
    $where[] = 'AND ' . db_prefix() . 'expense_reports.created_by = ' . get_staff_user_id();
}

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $_data = '<a href="' . admin_url('expense_reports/report/' . $aRow[db_prefix() . 'expense_reports.id']) . '" class="text-info edit-report">' . expenseReportIdFormat($aRow[db_prefix() . 'expense_reports.id']) . '</a>';

    $_data .= '<div class="row-options">';
    if (has_permission('expense_reports', '', 'edit') && ($aRow['status'] == "Draft" || $aRow['status'] == "Rejected")) {
        $_data .= '<a href="javascript:;" class="text-info edit-report" data-id="' . $aRow[db_prefix() . 'expense_reports.id'] . '">' . _l('Edit') . '</a>';
    }
    if (has_permission('expense_reports', '', 'delete') && ($aRow['status'] == "Draft" || $aRow['status'] == "Rejected")) {
        $_data .= ' | <a href="' . admin_url('expense_reports/delete/' . $aRow[db_prefix() . 'expense_reports.id']) . '" class="text-danger _delete">' . _l('Delete') . '</a>';
    }
    $_data .= '</div>';
    $row[] = $_data;

    $row[] = $aRow[db_prefix() . 'expense_reports.report_name'];

    $row[] = _d($aRow['start_date']) . ' - ' . _d($aRow['end_date']);

    if ($aRow['status'] == "Draft") {
        $row[] = '<span class="label label-default">Draft</span>';
    } else  if ($aRow['status'] == "Awaiting Approval") {
        $row[] = '<span class="label label-warning">'.$aRow['status'].'</span>';
    } else if ($aRow['status'] == "Approved" || $aRow['status'] == "Reimbursed") {
        $row[] = '<span class="label label-success">'.$aRow['status'].'</span>';
    } else if ($aRow['status'] == "Rejected") {
        $row[] = '<span class="label label-danger">'.$aRow['status'].'</span>';
    }

    $row[] = $aRow['submitter_name'];
    if ($aRow['status_change_by']) {
        $row[] = $aRow['status_change_by'] . " <br> At " . _dt($aRow['status_updated_at']);
    } else {
        $row[] = '-';
    }


    $row[] = app_format_money($aRow['total'], get_base_currency());
    $row[] = app_format_money($aRow['to_be_reimbursed'], get_base_currency());

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
