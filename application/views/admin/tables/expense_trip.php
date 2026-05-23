<?php
$CI = &get_instance();
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'name',
    'type',
    'business_purpose',
    'created_by',
    'created_at',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'expense_trip';
$additionalSelect = ['country', 'visa_required', 'id', 'CONCAT(TRIM(firstname), " ", TRIM(lastname))',];

$join = [
    'LEFT JOIN ' . db_prefix() . 'staff tstaff ON tstaff.staffid = ' . db_prefix() . 'expense_trip.created_by',
];
$where = [];

if (!has_permission('expense_advance', '', 'view')) {
    array_push($where, 'AND created_by = ' . get_staff_user_id());
}

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

$no = 0;
foreach ($rResult as $key => $aRow) {
    $row = [];
    foreach ($aRow as $column => $value) {
        $_data = $value;
        if ($column == 'id') {
            $_data = expenseTripIdFormat($aRow['id']);
            $_data .= '<div class="row-options">';
            if (has_permission('expense_trip', '', 'edit') && total_rows(db_prefix().'expense_reports',["trip_id" => $aRow['id']]) == 0 && total_rows(db_prefix().'expense_advance',["trip" => $aRow['id']]) == 0) {
                $_data .= '<a href="javascript:;" class="text-info edit-trip" data-id="' . $aRow['id'] . '">' . _l('Edit') . '</a>';
            }
            if (has_permission('expense_trip', '', 'delete') && total_rows(db_prefix().'expense_reports',["trip_id" => $aRow['id']]) == 0 && total_rows(db_prefix().'expense_advance',["trip" => $aRow['id']]) == 0) {
                $_data .= ' | <a href="' . admin_url('expense_trip/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('Delete') . '</a>';
            }
            $_data .= '</div>';
            $row[] = $_data;
        } else if ($column == 'name') {
            $row[] = $_data;
        } else if ($column == 'type') {
            if ($value == 'domestic') {
                $value = _l('Domestic');
            } else if ($value == 'international') {
                $value = _l('International');
                $country = $aRow['country'] ? get_country_name($aRow['country']) : '';
                $value .= "<br><b>Country</b> : " . $country;
                if ($aRow['visa_required']) {
                    $value .= "<br><b> Visa </b> : Visa Required</br>";
                } else {
                    $value .= "<br><b> Visa </b> : Visa Not Required</br>";
                }
            }
            $row[] = $value;
        } else if ($column == 'business_purpose') {
            $row[] = $value;
        } else if ($column == 'created_by') {
            $row[] = $aRow['CONCAT(TRIM(firstname), " ", TRIM(lastname))'] ? $aRow['CONCAT(TRIM(firstname), " ", TRIM(lastname))'] : _l('Unknown');
        } else if ($column == 'created_at') {
            $row[] = _dt($value);
        }
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
