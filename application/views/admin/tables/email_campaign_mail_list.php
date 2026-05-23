<?php
$CI = &get_instance();
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'null as sr_no',
    'title',
    'CONCAT(firstname, " ", lastname) AS staffname',
    'created_at'
];

$join = [
    'JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'emailcampaign_mail_list.created_by',
];

$additionalSelect = ['id'];
$where = [];

if (!has_permission('email_campaigns', '', 'view')) {
    if (manager_employee_data_access_permission_check("email_campaigns")) {
        $staffIds = get_manager_assigned_staff_ids(null, true);
        array_push($where, "AND created_by IN ($staffIds) ");
    } else {
        array_push($where, "AND created_by = " . get_staff_user_id());
    }
}

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'emailcampaign_mail_list';
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output       = $result['output'];
$rResult      = $result['rResult'];
$no = $CI->input->post('start');
foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'title') {
            $_data .= '<div class="row-options">';
            if (has_permission('email_campaigns', '', 'view') || has_permission('email_campaigns', '', 'view_own')) {
                $_data .= '<a href="' . admin_url('email_campaign_mail_list/mail_list_view/' . $aRow['id']) . '">View Emails</a>';
            }

            if (has_permission('email_campaigns', '', 'edit')) {
                $_data .= ' | <a href="javascript:;" data-id="' . $aRow['id'] . '" data-title="' . $aRow['title'] . '" onclick="openMailListModal(' . $aRow['id'] . ')">' . _l('Edit') . '</a>';
            }

            if (has_permission('email_campaigns', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('email_campaign_mail_list/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('Delete') . '</a>';
            }
            $_data .= '</div>';
        } elseif ($aColumns[$i] == 'created_at') {
            $_data = _dt($_data);
        } elseif ($aColumns[$i] == 'null as sr_no') {
            $_data = $no += 1;
        } elseif ($aColumns[$i] == 'CONCAT(firstname, " ", lastname) AS staffname') {
            $_data = $aRow['staffname'];
        }
        $row[] = $_data;
    }
    $output['aaData'][] = $row;
}
