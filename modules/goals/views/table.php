<?php
$CI = &get_instance();
defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    'subject',
    'achievement',
    'goal_duration_type',
    'goal_type',
    'created_at',
];
if (has_permission('goals', '', 'edit')) {
    array_push($aColumns, db_prefix() . 'goals.active');
}

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'goals';


$join = [];
$where = [];
if (!is_admin() && has_permission('goals', '', 'view_own')) {
    $join[] = 'JOIN ' . db_prefix() . 'goal_staff ON ' . db_prefix() . 'goal_staff.goal_id = ' . db_prefix() . 'goals.id AND ' . db_prefix() . 'goal_staff.staff_id = ' . get_staff_user_id();
    $where[] = 'AND ' . db_prefix() . 'goal_staff.active = "true" AND ' . db_prefix() . 'goals.active = "1"';
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix() . 'goals.id', 'start_date', 'end_date']);
$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'subject') {
            $_data = '<a href="' . admin_url('goals/goal/' . $aRow['id']) . '">' . $_data . '</a>';
            $_data .= '<div class="row-options">';
            $_data .= '<a href="' . admin_url('goals/goal/' . $aRow['id']) . '">' . _l('view') . '</a>';

            if (has_permission('goals', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('goals/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }
            $_data .= '</div>';
        } elseif ($aColumns[$i] == 'start_date' || $aColumns[$i] == 'end_date') {
            $_data = _d($_data);
        } elseif ($aColumns[$i] == 'goal_type') {
            $_data = format_goal_type($_data);
        } elseif ($aColumns[$i] == db_prefix() . 'goals.active' && has_permission('goals', '', 'edit')) {
            $checked = "";
            if ($aRow[db_prefix() . 'goals.active'] == '1') {
                $checked = 'checked';
            }
            $_data = '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="' . admin_url() . 'goals/change_status/' . $aRow['id'] . '" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" ' . $checked . '>
                <label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
            </div>';
        } elseif ($aColumns[$i] == "goal_duration_type") {
            $_data = get_goal_duration_title_by_key($aRow['goal_duration_type']);
            if ($aRow['goal_duration_type'] == "6") {
                $_data .= " (" . _d($aRow['start_date']) . ' - ' . _d($aRow['end_date']) . ")";
            }
        } elseif ($aColumns[$i] == 'created_at') {
            ob_start();
            $achievement = $CI->goals_model->calculate_goal_achievement_new($aRow['id']);
            $percent = $achievement['percent'];
            $progress_bar_percent = $achievement['progress_bar_percent']; ?>
            <input type="hidden" value="<?php
                                        echo $progress_bar_percent; ?>" name="percent">
            <div class="goal-progress" data-reverse="true">
                <strong class="goal-percent"><?php
                                                echo $percent; ?>%</strong>
            </div>
<?php
            $progress = ob_get_contents();
            ob_end_clean();
            $_data  = $progress;
        }
        $row[] = $_data;
    }

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
