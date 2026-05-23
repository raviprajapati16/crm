<?php
$CI = &get_instance();
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'null as checkbox',
    db_prefix() . 'expense_advance.id',
    'staff_id',
    'date',
    'trip',
    'report_id',
    'amount',
    db_prefix() . 'expense_advance.status',
    'status_changed_by',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'expense_advance';

$additionalSelect = [
    db_prefix() . 'expense_advance.status_updated_at',
    db_prefix() . 'expense_advance.id as advance_id',
    'CONCAT(t_submitter.firstname, " ", t_submitter.lastname) as submitter_name',
    'CONCAT(t_approver.firstname, " ", t_approver.lastname) as status_changed_by_name',
    'et.name as trip_name',
    'er.report_name'
];


$join = [
    'LEFT JOIN ' . db_prefix() . 'staff t_submitter ON t_submitter.staffid = ' . db_prefix() . 'expense_advance.staff_id',
    'LEFT JOIN ' . db_prefix() . 'staff t_approver ON t_approver.staffid = ' . db_prefix() . 'expense_advance.status_changed_by	',
    'LEFT JOIN ' . db_prefix() . 'expense_trip et ON et.id = ' . db_prefix() . 'expense_advance.trip',
    'LEFT JOIN ' . db_prefix() . 'expense_reports er ON er.id = ' . db_prefix() . 'expense_advance.report_id',
];

$where = [];
if (!has_permission('expense_advance', '', 'view')) {
    $where[] = 'AND ' . db_prefix() . 'expense_advance.staff_id = ' . get_staff_user_id();
}

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    if (has_permission('expense_advance', '', 'approve_reject_payment')) {
        if ($aRow[ db_prefix() . 'expense_advance.status'] == "Pending") {
            $row[] = '<div class="checkbox"><input type="checkbox" class="adv-checkbox" value="' . $aRow[db_prefix() . 'expense_advance.id'] . '"><label></label></div>';
        } else {
            $row[] = '-';
        }
    }

    $_data = '<a href="javascript:;" class="text-info view-advance" data-id="' . $aRow[db_prefix() . 'expense_advance.id'] . '">' . expenseAdvancePaymentIdFormat($aRow[db_prefix() . 'expense_advance.id']) . '</a>';
    $_data .= '<div class="row-options">';
    if (has_permission('expense_advance', '', 'edit') && (in_array($aRow[ db_prefix() . 'expense_advance.status'],["Pending","Rejected"]))) {
        $_data .= '<a href="javascript:;" class="text-info edit-advance" data-id="' . $aRow[db_prefix() . 'expense_advance.id'] . '">' . _l('Edit') . '</a>';
    }
    if (has_permission('expense_advance', '', 'delete') &&  (in_array($aRow[ db_prefix() . 'expense_advance.status'],["Pending","Rejected"]))) {
        $_data .= ' | <a href="' . admin_url('expense_advance/delete/' . $aRow[db_prefix() . 'expense_advance.id']) . '" class="text-danger _delete">' . _l('Delete') . '</a>';
    }
    $_data .= '</div>';
    $row[] = $_data;


    $row[] = $aRow['submitter_name'];
    $row[] = _d($aRow['date']);
    $row[] = $aRow['trip'] ? "#".expenseTripIdFormat($aRow['trip']) . ' - ' . $aRow['trip_name'] : '';

    $report = "-";
    if (!empty($aRow['report_id'])) {
        $reportName =  expenseReportIdFormat($aRow['report_id']) . " - " . $aRow['report_name'];
        $report = '<a href="' . admin_url('expense_reports/report/' . $aRow['report_id']) . '" target="_blank">' . $reportName . '</a>';
    }
    $row[] = $report;

    $row[] = app_format_money($aRow['amount'], get_base_currency());

    if ($aRow[db_prefix() . 'expense_advance.status'] == "Pending") {
        $row[] = '<span class="label label-warning">' . _l('Pending') . '</span>';
    } else if ($aRow[db_prefix() . 'expense_advance.status'] == "Rejected") {
        $row[] = '<span class="label label-danger">' . _l('Rejected') . '</span>';
    } else if ($aRow[db_prefix() . 'expense_advance.status'] == "Approved") {
        $row[] = '<span class="label label-success">' . _l('Approved') . '</span>';
    }

    if ($aRow['status_changed_by_name']) {
        $row[] = $aRow['status_changed_by_name'] . " <br> At " . _dt($aRow['status_updated_at']);
    } else {
        $row[] = '';
    }


    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
