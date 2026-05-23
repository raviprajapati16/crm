<?php
defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
$aColumns = [
    'CONCAT(firstname," ", lastname)',
    'null as progress',
];

if (has_permission('goals', '', 'edit')) {
    array_push($aColumns, db_prefix() . 'goal_staff.active');
}
$getStaffIDArr = get_goal_staff_ids($goal_id, true);

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'goal_staff';

$join = ['LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'goal_staff.staff_id'];
$where = [];

array_push($where, 'AND ' . db_prefix() . 'goal_staff.goal_id = ' . $goal_id);
array_push($where, 'AND ' . db_prefix() . 'staff.active = 1');


if (!is_admin() && has_permission('goals', '', 'view_own')) {
    array_push($where, 'AND ' . db_prefix() . 'goal_staff.active = "true"');
    array_push($where, 'AND ' . db_prefix() . 'goal_staff.staff_id =' . get_staff_user_id());
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, ['id', db_prefix() . 'goal_staff.staff_id']);

$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    foreach ($aRow as $column => $value) {
        $_data = $value;

        if ($column == 'CONCAT(firstname," ", lastname)') {
            $_data = '<a href="javascript:;" onclick="openGoalModal(' . $goal_id . ',' . $aRow['staff_id'] . ')" data-id="' . $aRow['id'] . '">' . $aRow['CONCAT(firstname," ", lastname)'] . '</a>';
        } else if ($column == db_prefix() . 'goal_staff.active' && has_permission('goals', '', 'edit')) {
            $checked = "";
            if ($aRow[db_prefix() . 'goal_staff.active'] == 'true') {
                $checked = 'checked';
            }
            $_data = '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="' . admin_url() . 'goals/change_goal_staff_status/' . $aRow['id'] . '" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" ' . $checked . '>
                <label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
            </div>';
        } else if ($column == 'progress') {

            if (in_array($aRow['staff_id'], $getStaffIDArr)) {
                ob_start();
                $achievement = $CI->goals_model->calculate_goal_achievement_new($goal_id, $aRow['staff_id']);
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
            } else {
                $_data = "<div style='margin-left: 11px !important;'>N/A</div>";
            }
        }
        $row[] = $_data;
    }

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
