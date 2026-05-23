<?php
$CI = &get_instance();
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'title',
    'start_date',
    'status',
    'null as days_count',
    'CONCAT(firstname, " ", lastname) AS staffname',
    'created_at',
    'null as action'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'emailcampaign';
$additionalSelect = ['id', 'status_message', 'max_send_limit'];

$join = [
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'emailcampaign.created_by',
];
$where = [];
if (!has_permission('email_campaigns', '', 'view')) {
    if (manager_employee_data_access_permission_check("email_campaigns")) {
        $staffIds = get_manager_assigned_staff_ids(null, true);
        array_push($where, "AND created_by IN ($staffIds) ");
    } else {
        array_push($where, "AND created_by = " . get_staff_user_id());
    }
}
$status = $CI->input->post('status');
if (!empty($status)) {
    array_push($where, "AND status = '" . $status . "'");
}

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
                $_data .= '<a href="' . admin_url('email_campaigns/results/' . $aRow['id']) . '" class="text-info">' . _l('Results') . '</a>';
            }
            if (has_permission('email_campaigns', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('email_campaigns/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('Delete') . '</a>';
            }
            $_data .= '</div>';
            $row[] = $_data;
        } else if ($column == 'start_date') {
            $row[] = _d($value);
        } else if ($column == 'status') {
            if ($value == 'Completed') {
                $_data = "<span data-toggle='tooltip' data-title='" . $aRow['status_message'] . "' class='label label-success'>Completed</span>";
            } else if ($value == 'In Progress' || $value == 'In Queue') {
                $_data = "<span data-toggle='tooltip' data-title='" . $aRow['status_message'] . "' class='label label-warning'>" . $value . "</span>";
            } else if ($value == 'Paused' || $value == 'Error' || $value == 'Stopped') {
                $_data = "<span data-toggle='tooltip' data-title='" . $aRow['status_message'] . "' class='label label-danger'>" . $value . "</span>";
            } else if ($value == 'Scheduled') {
                $_data = "<span data-toggle='tooltip' data-title='" . $value . "' class='label label-info'>" . $value . "</span>";
            } else {
                $_data = $value;
            }
            $row[] = $_data;
        } else if ($column == 'action') {
            if (has_permission('email_campaigns', '', 'edit')) {
                if ($aRow['status'] == 'Completed') {
                    if (total_rows(db_prefix() . 'emailcampaign_queue', ["status" => "failed", "campaign_id" => $aRow['id']]) > 0) {
                        $_data .= "<a class='btn btn-xs btn-primary' onclick=\"campaignStatusUpdate({$aRow['id']}, 'requeue')\" data-toggle='tooltip' data-title='Requeue Failed Email(s)' href='javascript:;'><i class='fa fa-refresh' aria-hidden='true'></i></a> ";
                    }
                } else if ($aRow['status'] == 'In Progress' || $aRow['status'] == 'Scheduled' || $aRow['status'] == 'In Queue' || $aRow['status'] == 'Paused') {
                    $_data .= "<a class='btn btn-xs btn-danger' onclick=\"campaignStatusUpdate({$aRow['id']}, 'stop')\" data-toggle='tooltip' data-title='Stop The Campaign' href='javascript:;'><i class='fa fa-stop' aria-hidden='true'></i></a> ";
                } else if ($aRow['status'] == 'Error' || $aRow['status'] == 'Stopped') {
                    $_data .= "<a class='btn btn-xs btn-primary' onclick=\"campaignStatusUpdate({$aRow['id']}, 'resume')\" data-toggle='tooltip' data-title='Resume The Campaign' href='javascript:;'><i class='fa fa-play' aria-hidden='true'></i></a> ";
                }
                $row[] = $_data;
            } else {
                $row[] = "-";
            }
        } else if ($column == 'created_at') {
            $row[] = _d($_data);
        } else if ($column == 'id') {
            $row[] = $no += 1;
        } else if ($column == 'days_count') {
            $maxLimitPerDay = $aRow['max_send_limit'];

            $CI->db->select('*');
            $CI->db->from(db_prefix() . 'emailcampaign_queue');
            $CI->db->group_by('mail_send_from_id');
            $CI->db->group_by('send_from');
            $CI->db->where('campaign_id', $aRow['id']);
            $query = $CI->db->get();
            $totalSender = $query->num_rows();

            $CI->db->select('*');
            $CI->db->from(db_prefix() . 'emailcampaign_queue');
            $CI->db->where('campaign_id', $aRow['id']);
            $query = $CI->db->get();
            $totalEmails = $query->num_rows();

            if ($totalSender > 0 && $maxLimitPerDay > 0) {
                $totalDays = ceil($totalEmails / ($totalSender * $maxLimitPerDay));
            } else {
                $totalDays = 0;
            }

            $CI->db->select('*');
            $CI->db->from(db_prefix() . 'emailcampaign_queue');
            $CI->db->where('campaign_id', $aRow['id']);
            $CI->db->where('status', 'sent');
            $query = $CI->db->get();
            $completedEmails = $query->num_rows();

            if ($totalSender > 0 && $maxLimitPerDay > 0) {
                $completedDays = ceil($completedEmails / ($totalSender * $maxLimitPerDay));
            } else {
                $completedDays = 0;
            }

            $row[] = $completedDays . "/" . $totalDays;
        } else {
            $row[] = $_data;
        }
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
