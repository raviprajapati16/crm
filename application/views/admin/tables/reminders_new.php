<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'description',
    'date',
    'staff',
    'isnotified',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'reminders';
$where        = [
    'AND rel_id=' . $id . ' AND rel_type="' . $rel_type . '" AND deleted_at IS NULL',
];
$join = [
    'JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'reminders.staff',
];
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'firstname',
    'lastname',
    'id',
    'creator',
    'rel_type',
]);
$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    foreach ($aRow as $column => $value) {
        $_data = $value;
        if ($column == 'staff') {
            $_data = '<a href="' . admin_url('staff/profile/' . $aRow['staff']) . '">' . staff_profile_image($aRow['staff'], [
                'staff-profile-image-small',
            ]) . ' ' . $aRow['firstname'] . ' ' . $aRow['lastname'] . '</a>';
        } elseif ($column == 'description') {
            if ($aRow['creator'] == get_staff_user_id() || is_admin()) {
                $_data .= '<div class="row-options">';
                if ($aRow['isnotified'] == 0 &&  $rel_type != "lead") {
                    $_data .= '<a href="#" onclick="edit_reminder(' . $aRow['id'] . ',this); return false;" class="edit-reminder">' . _l('edit') . '</a> | ';
                } else if ($rel_type == "lead") {
                    $_data .= "<a href='#' data-action='" . $aRow['reminder_action'] . "' data-id='" . $aRow['id'] . "' class='btn-reminder-modal'>" . _l('edit') . "</a> | ";
                }
                $_data .= '<a href="' . admin_url('misc/delete_reminder/' . $id . '/' . $aRow['id'] . '/' . $aRow['rel_type']) . '" class="text-danger delete-reminder">' . _l('delete') . '</a>';
                $_data .= '</div>';
            }
        } elseif ($column == 'isnotified') {
            if (!empty($aRow['date'])) {
                if ($_data == 1) {
                    $_data = _l('reminder_is_notified_boolean_yes');
                } else {
                    $_data = _l('reminder_is_notified_boolean_no');
                }
            } else {
                $_data = "-";
            }
        } elseif ($column == 'status') {
            $class = "";
            if ($value == "Pending") {
                $class  = "label-warning";
            } else if ($value == "Attend" || $value == "Visited" || $value == "Present") {
                $class  = "label-success";
            } else if ($value == "Declined" || $value == "Not Attend" || $value == "Not Visited" || $value == "Absent") {
                $class  = "label-danger";
            } else {
                $class  = "label-info";
            }
            $_data = "<span class='label " . $class . "'>" . $value . "</span>";
        } elseif ($column == 'reminder_action' && !empty($aRow['status'])) {
            $_data = "<button type='button' data-action='" . $aRow['reminder_action'] . "' data-id='" . $aRow['id'] . "' class='btn btn-info btn-xs btn-reminder-modal'>" . ucfirst($value) . "</button>";
        } elseif ($column == 'date') {
            $_data = _dt($value);
            if (empty($aRow['date'])) {
                $_data = "-";
            }
        } elseif ($column == 'created_at') {
            $_data = _dt($value);
            if (empty($aRow['date'])) {
                $_data = "-";
            }
        }
        $row[] = $_data;
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
