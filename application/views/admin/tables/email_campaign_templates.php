<?php
$CI = &get_instance();
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'null as sr_no',
    'title',
    'subject',
    'CONCAT(firstname, " ", lastname) AS staffname',
    'created_at',
    'null as action',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'emailcampaign_templates';
$additionalSelect = ['id'];

$join = [
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'emailcampaign_templates.created_by',
];
$where = [];

// if (!has_permission('email_campaigns', '', 'view')) {
//     if (manager_employee_data_access_permission_check("email_campaigns")) {
//         $staffIds = get_manager_assigned_staff_ids(null, true);
//         array_push($where, "AND created_by IN ($staffIds) OR id = 1");
//     } else {
//         array_push($where, "AND created_by = " . get_staff_user_id() . " OR id = 1");
//     }
// }

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

$no = $CI->input->post('start');
foreach ($rResult as $key => $aRow) {
    $row = [];
    foreach ($aRow as $column => $value) {
        $_data = $value;
        if ($column == 'title') {
            $_data .= '<div class="row-options">';

            if (has_permission('email_campaigns', '', 'view') || has_permission('email_campaigns', '', 'view_own')) {
                $_data .= '<a href="javascript:;" onclick="openPreviewModal(' . $aRow['id'] . ')">Preview</a>';
            }

            if (has_permission('email_campaigns', '', 'edit')) {
                if ($aRow['id'] == 1) {
                    if (has_permission('email_campaigns', '', 'view')) {
                        $_data .= ' | <a href="' . admin_url('email_campaign_templates/edit/' . $aRow['id']) . '" target="_blank">Template Edit</a>';
                    }
                } else {
                    $_data .= ' | <a href="' . admin_url('email_campaign_templates/edit/' . $aRow['id']) . '" target="_blank">Template Edit</a>';
                }

                if ($aRow['id'] != 1) {
                    $_data .= ' | <a href="javascript:;" onclick="openTemplateModel(' . $aRow['id'] . ')">' . _l('Edit') . '</a>';
                }
            }

            if (has_permission('email_campaigns', '', 'create')) {
                $_data .= ' | <a href="' . admin_url('email_campaign_templates/duplicate/' . $aRow['id']) . '">' . _l('Duplicate') . '</a>';
            }

            if ($aRow['id'] != 1 && has_permission('email_campaigns', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('email_campaign_templates/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('Delete') . '</a>';
            }
            $_data .= '</div>';
            $row[] = $_data;
        } else if ($column == 'sr_no') {
            $row[] = $no += 1;
        } else if ($column == 'created_at') {
            $row[] = _d($_data);
        } else {
            $row[] = $_data;
        }
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
