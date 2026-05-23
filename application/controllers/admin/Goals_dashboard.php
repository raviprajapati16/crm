<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Goals_dashboard extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('goals/goals_model');
        $this->load->model('staff_model');
        $this->load->model('goals_dashboard_model');
    }

    public function index()
    {
        if (!has_permission('goals_dashboard', '', 'view') && !has_permission('goals_dashboard', '', 'view_own')) {
            access_denied('goals dashboard');
        }
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $data['goals_list'] = $this->goals_dashboard_model->get_goals_list();
        $this->load->view('admin/goals_dashboard/index', $data);
    }

    public function main_render()
    {
        if (!has_permission('goals_dashboard', '', 'view') && !has_permission('goals_dashboard', '', 'view_own')) {
            access_denied('goals dashboard');
        }
        $post_data = $this->input->post();
        $data['staff'] = (isset($post_data['staff']) && !empty($post_data['staff'])) ? $post_data['staff'] : null;
        $data['goals_list'] = $this->goals_dashboard_model->get_goals_list();
        $html = $this->load->view('admin/goals_dashboard/render/main-render', $data, true);
        $result['success'] = true;
        $result['html'] = $html;
        echo json_encode($result);
    }

    public function goals_data()
    {
        if (!has_permission('goals_dashboard', '', 'view') && !has_permission('goals_dashboard', '', 'view_own')) {
            ajax_access_denied('goals dashboard');
        }
        $post_data = $this->input->post();
        $post_data['staff'] = (isset($post_data['staff']) && !empty($post_data['staff'])) ? $post_data['staff'] : null;
        $goal = $this->goals_model->get($post_data['goal_id']);
        if ($goal->goal_duration_type == 1) {
            $dateRange = explode(" - ", $post_data['date_range']);
            $start_date = to_sql_date($dateRange[0]);
            $end_date = to_sql_date($dateRange[1]);
        } else if (in_array($goal->goal_duration_type, [2, 3, 4, 5])) {
            $dateRange = explode(" - ", $post_data['date_range']);
            $start_date = $dateRange[0];
            $end_date = $dateRange[1];
        } else if ($goal->goal_duration_type == 6) {
            $start_date = $goal->start_date;
            $end_date = $goal->end_date;
        } else if ($goal->goal_duration_type == 7) {
            $start_date = date($post_data['date_range'] . "-01-01");
            $end_date = date($post_data['date_range'] . "-12-31");
        }

        $totalDays = countDaysInclusive($start_date, $end_date);
        $achievement = $this->goals_model->calculate_goal_achievement_new($post_data['goal_id'], $post_data['staff'], $start_date, $end_date);
        $staffIds = get_goal_staff_ids($post_data['goal_id'], true);
        $totalStaff = (isset($post_data['staff']) && !empty($post_data['staff'])) ? 1 : count($staffIds);

        $overalltarget = 0;
        $staffTarget = 0;
        if ($goal->goal_duration_type == 1) {
            $overalltarget = (int)($goal->achievement * $totalStaff) * $totalDays;
            $staffTarget = (int)$goal->achievement * $totalDays;
        } else if (in_array($goal->goal_duration_type, [2, 3, 4, 5, 6, 7])) {
            $overalltarget = (int)$goal->achievement * $totalStaff;
            $staffTarget = (int)$goal->achievement;
        }

        $result['success'] = true;
        $result['overalldata'] = ["total_staff" => $totalStaff, "total_target" => $overalltarget, "total_achievement" => $achievement['total']];
        $result['staffchartdata'] = [
            'staff_names' => [],
            'staff_achievements' => [],
            'total_target' => $staffTarget,
            'max_value' => 10,
        ];
        foreach ($staffIds as $staffId) {
            if (!empty($post_data['staff']) && $staffId != $post_data['staff']) {
                continue;
            }
            $staff = $this->staff_model->get($staffId);
            $staffAchievement = $this->goals_model->calculate_goal_achievement_new($post_data['goal_id'], $staffId, $start_date, $end_date);
            $result['staffchartdata']['staff_names'][] = $staff->firstname . ' ' . $staff->lastname;
            $result['staffchartdata']['staff_achievements'][] = $staffAchievement['total'];
        }
        if (count($result['staffchartdata']['staff_achievements']) > 0) {
            $max_value = max($result['staffchartdata']['staff_achievements']);
            if ($max_value > $staffTarget) {
                $result['staffchartdata']['max_value'] = $max_value + 10;
            } else {
                $result['staffchartdata']['max_value'] = $staffTarget;
            }
        }
        echo json_encode($result);
    }

    public function get_duration_list()
    {
        if (!has_permission('goals_dashboard', '', 'view') && !has_permission('goals_dashboard', '', 'view_own')) {
            ajax_access_denied('goals dashboard');
        }
        $post_data = $this->input->post();
        $goal = $this->goals_model->get($post_data['goal_id']);
        if ($goal->goal_duration_type == '2') {
            $weekList = get_week_list($post_data['year'], date("Y-m-d", strtotime($goal->created_at)));
            $result['data'] = $weekList;
        } else if ($goal->goal_duration_type == '3') {
            $monthList = array_reverse(get_month_list($post_data['year'], date("Y-m-d", strtotime($goal->created_at))));
            $result['data'] = $monthList;
        } else if ($goal->goal_duration_type == '4') {
            $monthList = array_reverse(get_quarter_list($post_data['year'], date("Y-m-d", strtotime($goal->created_at))));
            $result['data'] = $monthList;
        } else if ($goal->goal_duration_type == '5') {
            $monthList = array_reverse(get_half_yearly_list($post_data['year'], date("Y-m-d", strtotime($goal->created_at))));
            $result['data'] = $monthList;
        }
        $result['success'] = true;
        echo json_encode($result);
    }

    public function pdf()
    {
        if (!has_permission('goals_dashboard', '', 'report_generate')) {
            access_denied('goals dashboard');
        }
        try {
            $post_data = $this->input->post();
            $pdfData = [];
            $post_data['staff'] = (isset($post_data['staff']) && !empty($post_data['staff'])) ? $post_data['staff'] : null;
            $goalsData = $post_data['goals'];
            $goalCount = 0;
            foreach ($goalsData as $goalData) {
                $goal = $this->goals_model->get($goalData['id']);
                if ($goal->goal_duration_type == 1) {
                    $dateRange = explode(" - ", $goalData['date_range']);
                    $start_date = to_sql_date($dateRange[0]);
                    $end_date = to_sql_date($dateRange[1]);
                } else if (in_array($goal->goal_duration_type, [2, 3, 4, 5])) {
                    $dateRange = explode(" - ", $goalData['date_range']);
                    $start_date = $dateRange[0];
                    $end_date = $dateRange[1];
                } else if ($goal->goal_duration_type == 6) {
                    $start_date = $goal->start_date;
                    $end_date = $goal->end_date;
                } else if ($goal->goal_duration_type == 7) {
                    $start_date = date($goalData['year'] . "-01-01");
                    $end_date = date($goalData['year'] . "-12-31");
                }

                $totalDays = countDaysInclusive($start_date, $end_date);
                $achievement = $this->goals_model->calculate_goal_achievement_new($goalData['id'], $post_data['staff'], $start_date, $end_date);
                $staffIds = get_goal_staff_ids($goalData['id'], true);
                $totalStaff = (isset($post_data['staff']) && !empty($post_data['staff'])) ? 1 : count($staffIds);

                $overalltarget = 0;
                $staffTarget = 0;
                if ($goal->goal_duration_type == 1) {
                    $overalltarget = (int)($goal->achievement * $totalStaff) * $totalDays;
                    $staffTarget = (int)$goal->achievement * $totalDays;
                } else if (in_array($goal->goal_duration_type, [2, 3, 4, 5, 6, 7])) {
                    $overalltarget = (int)$goal->achievement * $totalStaff;
                    $staffTarget = (int)$goal->achievement;
                }
                $overallPercentage = 0;
                if ($overalltarget > 0) {
                    $overallPercentage = ($achievement['total'] / $overalltarget) * 100;
                    if ($overallPercentage >= 100) {
                        $overallPercentage = 100;
                    }
                }
                $pdfData[$goalCount] = [
                    "id" => $goal->id,
                    "subject" => $goal->subject,
                    "description" => $goal->description,
                    "goal_duration_type" => $goal->goal_duration_type,
                    "goal_type" => $goal->goal_type,
                    "start_date" => $start_date,
                    "end_date" =>  $end_date,
                    "total_staff" => $totalStaff,
                    "total_target" => $overalltarget,
                    "total_achievement" => $achievement['total'],
                    "total_percentage" => number_format($overallPercentage, 2) . '%',
                    "staff_selected" => ($post_data['staff']) ? true : false
                ];
                $count = 0;
                foreach ($staffIds as $staffId) {
                    if (!empty($post_data['staff']) && $staffId != $post_data['staff']) {
                        continue;
                    }
                    $staff = $this->staff_model->get($staffId);
                    $staffAchievement = $this->goals_model->calculate_goal_achievement_new($goalData['id'], $staffId, $start_date, $end_date);
                    $percentage = 0;
                    if ($staffTarget > 0) {
                        $percentage = ($staffAchievement['total'] / $staffTarget) * 100;
                        if ($percentage >= 100) {
                            $percentage = 100;
                        }
                    }
                    $pdfData[$goalCount]['staffData'][$count]['staff_name'] = $staff->firstname . ' ' . $staff->lastname;
                    $pdfData[$goalCount]['staffData'][$count]['staff_achievements'] = $staffAchievement['total'];
                    $pdfData[$goalCount]['staffData'][$count]['target'] = $staffTarget;
                    $pdfData[$goalCount]['staffData'][$count]['percentage'] = number_format($percentage, 2) . '%';
                    $count++;
                }
                $goalCount++;
            }
            $pdf = goals_dashboard_report_mpdf($pdfData);
            $tempPdfPath = 'uploads/temp_mail_attachments/goals_dashboard_' . time() . '_' . uniqid() . '.pdf';
            $pdf->Output($tempPdfPath, \Mpdf\Output\Destination::FILE);
            $response['success'] = true;
            $response['pdf_url'] = site_url($tempPdfPath);
            $response['view_url'] = site_url('download/file_download?path=' . $tempPdfPath);
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        echo json_encode($response);
    }
}
