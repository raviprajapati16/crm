<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$post_data = $CI->input->post();
$getGoalData = $CI->goals_model->get($goal_id);
$holidayArr = getHolidayDatesArr($staff_id);
$durationData = [];

$dateRange = explode(" - ", $post_data['date']);
switch ($getGoalData->goal_duration_type) {
    case 1: // Daily
        $durationData = getDatesBetween($dateRange[0], $dateRange[1], $holidayArr);
        break;
    case 2: // Weekly
        $durationData = get_week_list($post_data['year'], date("Y-m-d", strtotime($getGoalData->created_at)));
        break;
    case 3: // Monthly
        $durationData = get_month_list($post_data['year'], date("Y-m-d", strtotime($getGoalData->created_at)));
        break;
    case 4: // Quarterly
        $durationData = get_quarter_list($post_data['year'], date("Y-m-d", strtotime($getGoalData->created_at)));
        break;
    case 5: // Half Yearly
        $durationData = get_half_yearly_list($post_data['year'], date("Y-m-d", strtotime($getGoalData->created_at)));
        break;
    case 6: // Custom
        $currentDate = new DateTime();
        $currentDateStartOfDay = (clone $currentDate)->setTime(0, 0, 0);
        $currentDateEndOfDay = (clone $currentDate)->setTime(23, 59, 59);
        $startDate = new DateTime($getGoalData->start_date);
        $endDate = new DateTime($getGoalData->end_date);
        $status = 'future';
        if ($endDate < $currentDateStartOfDay) {
            $status = 'past';
        } elseif ($startDate >= $currentDateStartOfDay && $startDate <= $currentDateEndOfDay) {
            $status = 'current';
        }
        $durationData[] = array(
            "title" => date('d M, Y', strtotime($getGoalData->start_date)) . " - " . date('d M, Y', strtotime($getGoalData->end_date)),
            "start_date" => $getGoalData->start_date,
            "end_date" => $getGoalData->end_date,
            "status" => $status
        );
        break;
    case 7:
        $durationData = get_year_list(date("Y-m-d", strtotime($getGoalData->created_at)));
        break;
    default:
        $durationData = [];
        break;
}

if (!is_array($durationData)) {
    $durationData = [$durationData];
}

$start = intval($post_data['start']);
$length = intval($post_data['length']);
$search = $post_data['search']['value'];
$totalRecords = count($durationData);
$paginatedData = array_slice($durationData, $start, $length);

$output = [
    "draw" => intval($post_data['draw']),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecords,
    "aaData" => []
];


foreach ($paginatedData as $key => $record) {
    $start_date = date("Y-m-d", strtotime($record['start_date']));
    $end_date = date("Y-m-d", strtotime($record['end_date']));
    $goalAchivedData = $CI->goals_model->calculate_goal_achievement_new($goal_id, $staff_id, $start_date, $end_date);
    $target = $getGoalData->achievement;
    $achieved = $goalAchivedData['total'];

    $status = "";
    $action = "";
    if ($achieved == "0" && ($record['status'] == "current" || $record['status'] == "future")) {
        $status = "<span class='label label-warning'>Pending</span>";
    } else if ($achieved >= $target) {
        $status = "<span class='label label-success'>Achieved</span>";
        $action = "<button type='button' class='btn btn-success btn-xs notify-btn' data-duration-title='".$record['title']."' data-start-date='".$start_date."' data-end-date='".$end_date."' data-status='success'>Notifiy <i class='fa fa-bell-o' aria-hidden='true'></i></button>";
    } else if ($achieved != $target && $record['status'] == "past") {
        $status = "<span class='label label-danger'>Failed</span>";
        $action = "<button type='button' class='btn btn-danger btn-xs notify-btn' data-duration-title='".$record['title']."' data-start-date='".$start_date."' data-end-date='".$end_date."' data-status='failed'>Notifiy <i class='fa fa-bell-o' aria-hidden='true'></i></button>";
    } else if ($achieved > 0 && ($record['status'] == "current" || $record['status'] == "future")) {
        $status = "<span class='label label-info'>In Progress</span>";
    }

    ob_start();
    $percent = $goalAchivedData['percent'];
    $progress_bar_percent = $goalAchivedData['progress_bar_percent']; ?>
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



    if (has_permission('goals', '', 'edit')) {
        $output['aaData'][] = array(
            $record['title'],
            $achieved . " / " . $target,
            $progress,
            $status,
            $action
        );
    } else {
        $output['aaData'][] = array(
            $record['title'],
            $achieved . " / " . $target,
            $progress,
            $status
        );
    }
}

echo json_encode($output);
exit;
