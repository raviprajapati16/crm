<?php
$CI = &get_instance();
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'name',
    'company',
    'phonenumber',
    'email',
    'address',
    'assigned_customer_status',
    'assigned_customer_last_updated_at',
];

$join = [];

$additionalSelect = [
    'id',
    'name',
    'company',
    'phonenumber',
    'email',
    'address',
    'city',
    'state',
    'country',
    'zip',
    'assigned_customer_status',
    'assigned_customer_last_updated_at',
    'assigned_customer_last_updated_by'
];

$where = [];

array_push($where, 'AND ' . db_prefix() . 'leads.assigned_customer_id = ' . $customer_id);

$sIndexColumn = 'id';
$sTable = db_prefix() . 'leads';
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output = $result['output'];
$rResult = $result['rResult'];

$status_arr = get_lead_customer_assign_status();
foreach ($rResult as $aRow) {

   
    $row = [];
    $row[] = $aRow['id'];
    //name column
    $_data = '<a href="' . admin_url('leads/index/' . $aRow['id']) . '" onclick="view_lead_data(' . $aRow['id'] . ');return false;">' . $aRow['name'] . '</a>';
    $_data .= '<div class="row-options">';
    $_data .= '<a href="javascript:;" onclick="view_lead_data(' . $aRow['id'] . ')">' . _l('View') . '</a>';
    $_data .= ' | <a href="javascript:;" onclick="remove_lead_from_customer(' . $aRow['id'] . ')" class="text-danger">' . _l('Remove') . '</a>';
    $_data .= '</div>';
    $row[] = $_data;

    $row[] = !empty($aRow['company']) ? $aRow['company'] : '-';
    $row[] = !empty($aRow['phonenumber']) ? $aRow['phonenumber'] : '-';
    $row[] = !empty($aRow['email']) ? $aRow['email'] : '-';
    $row[] = $aRow['address']."<br>".$aRow['city'].", ".$aRow['state'].", ".get_country_name($aRow['country'])." - ".$aRow['zip'];

    $selectedStatus = (!empty($aRow['assigned_customer_status'])) ? $status_arr[$aRow['assigned_customer_status']] : $status_arr[0];

    $outputStatus = '<span class="inline-block label label-' . (empty($selectedStatus['color']) ? 'default' : '') . '" style="color:' . $selectedStatus['color'] . ';border:1px solid ' . $selectedStatus['color'] . '">' . $selectedStatus['name'];
    $outputStatus .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
    $outputStatus .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsStatus-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
    $outputStatus .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';
    $outputStatus .= '</a>';

    $outputStatus .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' . $aRow['id'] . '">';
    foreach ($status_arr as $key => $status) {
        if ($aRow['assigned_customer_status'] != $key) {
            $outputStatus .= '<li>
                  <a href="#" onclick="customer_lead_change_status(' . $key . ',' . $aRow['id'] . '); return false;">
                     ' . $status['name'] . '
                  </a>
               </li>';
        }
    }
    $outputStatus .= '</ul>';
    $outputStatus .= '</div>';

    $outputStatus .= '</span>';
    $row[] = $outputStatus;

    $row[] = $aRow['assigned_customer_last_updated_by'] . " At " . date('d-m-Y h:i A');
    $output['aaData'][] = $row;
}
