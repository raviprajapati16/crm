<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Goals
Description: Default module for defining goals
Version: 2.3.0
Requires at least: 2.3.*
*/

define('GOALS_MODULE_NAME', 'goals');

//hooks()->add_action('after_cron_run', 'goals_notification');
hooks()->add_action('admin_init', 'goals_module_init_menu_items');
hooks()->add_action('staff_member_deleted', 'goals_staff_member_deleted');
hooks()->add_action('admin_init', 'goals_permissions');
hooks()->add_action('app_admin_footer', 'goals_init_checkView');

hooks()->add_filter('migration_tables_to_replace_old_links', 'goals_migration_tables_to_replace_old_links');
hooks()->add_filter('global_search_result_query', 'goals_global_search_result_query', 10, 3);
hooks()->add_filter('global_search_result_output', 'goals_global_search_result_output', 10, 2);
hooks()->add_filter('get_dashboard_widgets', 'goals_add_dashboard_widget');



function goals_init_checkView()
{
    $CI = &get_instance();
    $CI->load->model('goals/goals_model');
    echo $CI->load->view('goals/initViewCheck', [], true);
}

function goals_add_dashboard_widget($widgets)
{
    $widgets[] = [
        'path'      => 'goals/widget',
        'container' => 'right-4',
    ];

    return $widgets;
}

function goals_staff_member_deleted($data)
{
    $CI = &get_instance();
    $CI->db->where('staff_id', $data['id']);
    $CI->db->update(db_prefix() . 'goals', [
        'staff_id' => $data['transfer_data_to'],
    ]);
}

function goals_global_search_result_output($output, $data)
{
    if ($data['type'] == 'goals') {
        $output = '<a href="' . admin_url('goals/goal/' . $data['result']['id']) . '">' . $data['result']['subject'] . '</a>';
    }

    return $output;
}

function goals_global_search_result_query($result, $q, $limit)
{
    $CI = &get_instance();
    if (has_permission('goals', '', 'view')) {
        // Goals
        $CI->db->select()->from(db_prefix() . 'goals')->like('description', $q)->or_like('subject', $q)->limit($limit);

        $CI->db->order_by('subject', 'ASC');

        $result[] = [
            'result'         => $CI->db->get()->result_array(),
            'type'           => 'goals',
            'search_heading' => _l('goals'),
        ];
    }

    return $result;
}

function goals_migration_tables_to_replace_old_links($tables)
{
    $tables[] = [
        'table' => db_prefix() . 'goals',
        'field' => 'description',
    ];

    return $tables;
}

function goals_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view_own'   => _l('permission_view_own'),
        'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('goals', $capabilities, _l('goals'));
}

function goals_notification()
{
    set_time_limit(0);
    $CI = &get_instance();
    $CI->load->model('goals/goals_model');
    $goals = $CI->goals_model->get('');
    if (!empty($goals)) {
        foreach ($goals as $goal) {
            if ($goal['notify_when_fail'] || $goal['notify_when_achieve']) {
                $notificationSendCount = 0;
                $durationData = get_goal_last_duration_by_type($goal['goal_duration_type'], $goal['start_date'], $goal['end_date']);
                if ($durationData['status']) {
                    $staff_ids = get_goal_staff_ids($goal['id'], true);
                    if (!empty($staff_ids)) {
                        foreach ($staff_ids as $staff_id) {
                            $staffData = get_staff($staff_id);
                            if (!empty($staffData)) {
                                if ($staffData->active == 0) {
                                    continue;
                                }
                            } else {
                                continue;
                            }
                            $acheivement = $CI->goals_model->calculate_goal_achievement_new($goal['id'], $staff_id, $durationData['start_date'], $durationData['end_date']);
                            $status = "";
                            if ($acheivement['total'] >= $goal['achievement'] && $goal['notify_when_achieve']) {
                                $status = "success";
                            } else if ($acheivement['total'] != $goal['achievement'] && $goal['notify_when_fail']) {
                                $status = "failed";
                            }
                            if (!empty($status)) {
                                $holidayArr = getHolidayDatesArr($staff_id);
                                $notificationData = [];
                                if ($goal['goal_duration_type'] == 1) {
                                    if (!in_array(date('Y-m-d', strtotime('-1 day')), $holidayArr)) {
                                        $notificationData = array(
                                            "goal_id" => $goal['id'],
                                            "staff_id" => $staff_id,
                                            "start_date" => $durationData['start_date'],
                                            "end_date" => $durationData['end_date'],
                                            "goal_duration_title" => $durationData['title'],
                                            "status" => $status,
                                            "achievement" => $acheivement,
                                        );
                                    }
                                } else {
                                    $notificationData = array(
                                        "goal_id" => $goal['id'],
                                        "staff_id" => $staff_id,
                                        "start_date" => $durationData['start_date'],
                                        "end_date" => $durationData['end_date'],
                                        "goal_duration_title" => $durationData['title'],
                                        "status" => $status,
                                        "achievement" => $acheivement,
                                    );
                                }
                                if (!empty($notificationData)) {
                                    $notificationSendCheck =  $CI->goals_model->notify_staff_members($notificationData);
                                    if ($notificationSendCheck) {
                                        $notificationSendCount++;
                                    }
                                }
                            }
                        }
                    }
                }
                if ($notificationSendCount > 0) {
                    log_activity($notificationSendCount . " notification sent to staff users for goal ID [" . $goal['id'] . "] [Goal Duration Data " . (json_encode($durationData)) . "] using CRON JOB");
                }
            }
        }
    }
}

function goals_emails()
{
    set_time_limit(0);
    $CI = &get_instance();
    $CI->load->model('goals/goals_model');
    $goals = $CI->goals_model->get('');
    if (!empty($goals)) {
        foreach ($goals as $goal) {
            if ($goal['email_when_achieve'] || $goal['email_when_fail']) {
                $durationData = get_goal_last_duration_by_type($goal['goal_duration_type'], $goal['start_date'], $goal['end_date']);
                if ($durationData['status']) {
                    $staff_ids = get_goal_staff_ids($goal['id'], true);
                    if (!empty($staff_ids)) {
                        foreach ($staff_ids as $staff_id) {
                            $staffData = get_staff($staff_id);
                            if (!empty($staffData)) {
                                if ($staffData->active == 0) {
                                    continue;
                                }
                            } else {
                                continue;
                            }
                            $acheivement = $CI->goals_model->calculate_goal_achievement_new($goal['id'], $staff_id, $durationData['start_date'], $durationData['end_date']);
                            $status = "";
                            if ($acheivement['total'] >= $goal['achievement'] && $goal['email_when_achieve'] == 1) {
                                $status = "success";
                            } else if ($acheivement['total'] != $goal['achievement'] && $goal['email_when_fail'] == 1) {
                                $status = "failed";
                            }
                            $acheivement['duration_data'] = $durationData;
                            $needToSend = true;
                            if (!empty($status)) {
                                $holidayArr = getHolidayDatesArr($staff_id);
                                if ($goal['goal_duration_type'] == 1) {
                                    if (in_array(date('Y-m-d', strtotime('-1 day')), $holidayArr)) {
                                        $needToSend = false;
                                    }
                                }
                                if ($needToSend) {
                                    $checkMail = false;
                                    $goalData = $CI->goals_model->get($goal['id']);
                                    if ($status == "success") {
                                        $checkMail = send_mail_template('goals_achieve', $goalData, $staff_id,);;
                                    } else {
                                        $checkMail = send_mail_template('goals_failed_to_achieve', $goalData, $staff_id);;
                                    }
                                    if ($checkMail) {
                                        log_activity(" Goal achieve $status email has been sent to staff ID [" . $staff_id . "] for goal ID [" . $goal['id'] . "] for Goal Period [" . (json_encode($durationData)) . "] via CRON JOB");
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

/**
 * Register activation module hook
 */
register_activation_hook(GOALS_MODULE_NAME, 'goals_module_activation_hook');

function goals_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(GOALS_MODULE_NAME, [GOALS_MODULE_NAME]);

/**
 * Init goals module menu items in setup in admin_init hook
 * @return null
 */
function goals_module_init_menu_items()
{
    $CI = &get_instance();

    $CI->app->add_quick_actions_link([
        'name'       => _l('goal'),
        'url'        => 'goals/goal',
        'permission' => 'goals',
        'position'   => 56,
    ]);

    if (has_permission('goals', '', 'view') || has_permission('goals', '', 'view_own')) {
        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug'     => 'goals-tracking',
            'name'     => _l('goals'),
            'href'     => admin_url('goals'),
            'position' => 24,
        ]);
    }
}


/**
 * Get goal types for the goals feature
 * @return array
 */
function get_goal_types()
{
    $types = [
        [
            'key'      => 1,
            'lang_key' => 'goal_type_total_income',
            'subtext'  => 'goal_type_income_subtext',
        ],
        [
            'key'      => 2,
            'lang_key' => 'goal_type_convert_leads',
        ],
        [
            'key'      => 3,
            'lang_key' => 'goal_type_increase_customers_without_leads_conversions',
            'subtext'  => 'goal_type_increase_customers_without_leads_conversions_subtext',
        ],
        [
            'key'      => 4,
            'lang_key' => 'goal_type_increase_customers_with_leads_conversions',
            'subtext'  => 'goal_type_increase_customers_with_leads_conversions_subtext',
        ],
        [
            'key'      => 5,
            'lang_key' => 'goal_type_make_contracts_by_type_calc_database',
            'subtext'  => 'goal_type_make_contracts_by_type_calc_database_subtext',
        ],
        [
            'key'      => 7,
            'lang_key' => 'goal_type_make_contracts_by_type_calc_date',
            'subtext'  => 'goal_type_make_contracts_by_type_calc_date_subtext',
        ],
        [
            'key'      => 6,
            'lang_key' => 'goal_type_total_estimates_converted',
            'subtext'  => 'goal_type_total_estimates_converted_subtext',
        ],
        [
            'key'      => 8,
            'lang_key' => 'goal_type_total_calls',
            'subtext'  => 'goal_type_total_calls_subtext',
        ],
        [
            'key'      => 9,
            'lang_key' => 'goal_type_online_meetings',
            'subtext'  => 'goal_type_online_meetings_subtext',
        ],
        [
            'key'      => 10,
            'lang_key' => 'goal_type_f2f_meetings',
            'subtext'  => 'goal_type_f2f_meetings_subtext',
        ],
        [
            'key'      => 11,
            'lang_key' => 'goal_type_plant_visit',
            'subtext'  => 'goal_type_plant_visit_subtext',
        ],
        [
            'key'      => 12,
            'lang_key' => 'goal_type_inquiry_form',
            'subtext'  => 'goal_type_inquiry_form_subtext',
        ],
        [
            'key'      => 13,
            'lang_key' => 'goal_type_volume_of_business',
        ],
    ];

    return hooks()->apply_filters('get_goal_types', $types);
}

function get_goal_duration_types()
{
    $types = [
        [
            'key'   => 1,
            'title' => 'Daily',
        ],
        [
            'key'   => 2,
            'title' => 'Weekly',
        ],
        [
            'key'   => 3,
            'title' => 'Monthly',
        ],
        [
            'key'   => 4,
            'title' => 'Quarterly',
        ],
        [
            'key'   => 5,
            'title' => 'Half Yearly',
        ],
        [
            'key'   => 7,
            'title' => 'Yearly',
        ],
        [
            'key'   => 6,
            'title' => 'Custom',
        ],
    ];

    return hooks()->apply_filters('get_goal_duration_types', $types);
}

function get_goal_duration_by_key($key)
{

    // Get the current date
    $current_date = date('d-m-Y');
    $current_year = date('Y');

    // Define the format based on the key
    switch ($key) {
        case 1: // Daily
            $type = 'Daily Goal';
            $title = $current_date;
            break;
        case 2: // Weekly
            $type = 'Weekly goal';
            $start_of_week = date('d-m-Y', strtotime('monday this week'));
            $end_of_week = date('d-m-Y', strtotime('sunday this week'));
            $title = "$start_of_week to $end_of_week";
            break;
        case 3: // Monthly
            $type = 'Monthly Goal';
            $month = date('F'); // Full month name (e.g., "January")
            $title = "$month $current_year";
            break;
        case 4: // Quarterly
            $type = 'Quarterly goal';
            $current_month = date('m');
            if ($current_month <= 3) {
                $quarter = 1;
            } elseif ($current_month <= 6) {
                $quarter = 2;
            } elseif ($current_month <= 9) {
                $quarter = 3;
            } else {
                $quarter = 4;
            }
            $title = "Quarter $quarter $current_year";
            break;
        case 5: // Half Yearly
            $type = 'Half Yearly Goal';
            if (date('m') <= 6) {
                $title = "First half of $current_year";
            } else {
                $title = "Second half of $current_year";
            }
            break;
        case 6: // Custom
            $type = 'Custom Duration';
            $title = '';
            break;
        case 7: // Yearly
            $type = 'Yearly goal';
            $title = $current_year;
            break;
        default:
            return null;
    }

    return [
        'type'  => $type,
        'title' => $title,
    ];
}


function get_goal_duration_title_by_key($key)
{
    $types = get_goal_duration_types();

    foreach ($types as $type) {
        if ($type['key'] == $key) {
            return $type['title'];
        }
    }

    return null;
}

/**
 * Translate goal type based on passed key
 * @param  mixed $key
 * @return string
 */
function format_goal_type($key)
{
    foreach (get_goal_types() as $type) {
        if ($type['key'] == $key) {
            return _l($type['lang_key']);
        }
    }

    return $type;
}

function get_goal_staff_ids($goal_id, $get_active = false)
{
    $CI = &get_instance();
    $CI->db->select('gs.staff_id');
    $CI->db->from(db_prefix() . 'goal_staff as gs');
    $CI->db->join(db_prefix() . 'staff as s', 's.staffid = gs.staff_id');
    $CI->db->where('gs.goal_id', $goal_id);
    $CI->db->where('s.active', 1);
    if ($get_active) {
        $CI->db->where('gs.active', 'true');
    }

    $result = $CI->db->get()->result_array();

    if (!empty($result)) {
        return array_values(array_filter(array_column($result, "staff_id")));
    }

    return [];
}

function update_goal_staff($staff_ids, $goal_id)
{
    $CI = &get_instance();
    $CI->load->database();

    $CI->load->model('goals/goals_model');
    $goal = $CI->goals_model->get($goal_id);
    if (empty($staff_ids)) {
        $CI->db->where('goal_id', $goal_id);
        $CI->db->delete(db_prefix() . 'goal_staff');
        return true;
    }

    $CI->db->select('staff_id');
    $CI->db->from(db_prefix() . 'goal_staff');
    $CI->db->where('goal_id', $goal_id);
    $existing_staff = $CI->db->get()->result_array();

    $existing_staff_ids = array_column($existing_staff, 'staff_id');

    $staff_to_add = array_diff($staff_ids, $existing_staff_ids);
    $staff_to_remove = array_diff($existing_staff_ids, $staff_ids);

    if (!empty($staff_to_add)) {
        $data_to_insert = [];
        foreach ($staff_to_add as $staff_id) {
            $data_to_insert = [
                'goal_id'  => $goal_id,
                'staff_id' => $staff_id
            ];
            $CI->db->insert(db_prefix() . 'goal_staff', $data_to_insert);
            $insert_id = $CI->db->insert_id();
            if ($insert_id && $goal->email_when_assign == 1) {
                send_mail_template('goals_new_assigned', $goal, $staff_id);
            }
        }
    }

    if (!empty($staff_to_remove)) {
        $CI->db->where('goal_id', $goal_id);
        $CI->db->where_in('staff_id', $staff_to_remove);
        $CI->db->delete(db_prefix() . 'goal_staff');
    }

    return true;
}

function get_goal_last_duration_by_type($key, $custom_start_date = null, $custom_end_date = null)
{
    $today = date('Y-m-d');
    $status = false;
    $start_date = $end_date = $title = '';

    switch ($key) {
        case 1: // Daily
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $start_date = $yesterday;
            $end_date = $yesterday;
            $title = 'Daily : ' . date('d M, Y', strtotime($yesterday));
            $status = true;  // Always true for daily
            break;
        case 2: // Weekly
            $day_of_week = date('N'); // 1 (Monday) to 7 (Sunday)
            if ($day_of_week == 1) {  // If today is Monday
                $start_of_last_week = date('Y-m-d', strtotime('last monday -7 days'));
                $end_of_last_week = date('Y-m-d', strtotime('last sunday'));
                $week_number = date('W', strtotime($start_of_last_week));
                $start_date = $start_of_last_week;
                $end_date = $end_of_last_week;
                $title = 'Weekly : Week ' . $week_number . ' - ' . date('d M, Y', strtotime($start_of_last_week)) . ' to ' . date('d M, Y', strtotime($end_of_last_week));
                $status = true;
            }
            break;
        case 3: // Monthly
            $day_of_month = date('j'); // Day of the month (1-31)
            if ($day_of_month == 1) {  // If today is the 1st of the month
                $start_of_last_month = date('Y-m-01', strtotime('first day of last month'));
                $end_of_last_month = date('Y-m-t', strtotime('last day of last month'));
                $start_date = $start_of_last_month;
                $end_date = $end_of_last_month;
                $title = 'Monthly : ' . date('M Y', strtotime($start_of_last_month));
                $status = true;
            }
            break;
        case 4: // Quarterly
            $current_month = date('n'); // Month number (1-12)
            $current_year = date('Y');
            $current_quarter = ceil($current_month / 3);

            // If today is the start of a new quarter (Jan, Apr, Jul, Oct)
            if (in_array($current_month, [1, 4, 7, 10]) && date('j') == 1) {
                $previous_quarter = $current_quarter - 1;
                if ($previous_quarter == 0) {
                    $previous_quarter = 4;
                    $current_year = $current_year - 1; // Set year to the previous year
                }

                // Get the start and end months of the previous quarter
                $previous_quarter_month_start = ($previous_quarter - 1) * 3 + 1;
                $start_of_last_quarter = date('Y-m-01', mktime(0, 0, 0, $previous_quarter_month_start, 1, $current_year));
                $end_of_last_quarter = date('Y-m-t', mktime(0, 0, 0, $previous_quarter_month_start + 2, 1, $current_year));

                $start_date = $start_of_last_quarter;
                $end_date = $end_of_last_quarter;
                $title = 'Quarterly - Quarter ' . $previous_quarter . ' of ' . $current_year;
                $status = true;
            }
            break;

        case 5: // Half Yearly
            $current_month = date('n');
            // If today is the 1st of January or July
            if (($current_month == 1 || $current_month == 7) && date('j') == 1) {
                if ($current_month == 1) { // Start of January -> return last half year (July-Dec)
                    $start_of_last_half_year = date('Y-07-01', strtotime('last year'));
                    $end_of_last_half_year = date('Y-12-31', strtotime('last year'));
                    $title = 'Half Yearly - Second half of ' . date('Y', strtotime('last year'));
                } else { // Start of July -> return first half of the current year
                    $start_of_last_half_year = date('Y-01-01');
                    $end_of_last_half_year = date('Y-06-30');
                    $title = 'Half Yearly - First half of ' . date('Y');
                }
                $start_date = $start_of_last_half_year;
                $end_date = $end_of_last_half_year;
                $status = true;
            }
            break;
        case 7: // Yearly
            $day_of_year = date('z'); // Day of the year (0-365)
            if ($day_of_year == 0) {  // If today is January 1st
                $start_of_last_year = date('Y-01-01', strtotime('last year'));
                $end_of_last_year = date('Y-12-31', strtotime('last year'));
                $start_date = $start_of_last_year;
                $end_date = $end_of_last_year;
                $title = 'Yearly - Year ' . date('Y', strtotime($start_of_last_year));
                $status = true;
            }
            break;

        case 6: // Custom
            if ($custom_start_date && $custom_end_date) {
                $yesterday = date('Y-m-d', strtotime('-1 day'));
                $custom_end_date_parsed = date('Y-m-d', strtotime($custom_end_date));
                // Only return if the custom end date is yesterday
                if ($custom_end_date_parsed == $yesterday) {
                    $start_date = date('Y-m-d', strtotime($custom_start_date));
                    $end_date = $custom_end_date_parsed;
                    $title = 'Custom - ' . date('d M, Y', strtotime($start_date)) . ' to ' . date('d M, Y', strtotime($end_date));
                    $status = true;
                }
            }
            break;
    }

    return [
        'title'     => $title,
        'start_date' => $start_date,
        'end_date'   => $end_date,
        'status'    => $status,
    ];
}

function get_goals_progress_bars($goal_id)
{
    $CI = &get_instance();
    $result = array();
    $holidayDatesArr = getHolidayDatesArr(get_staff_user_id());
    $CI->load->model('goals/goals_model');
    $goal = $CI->goals_model->get($goal_id);

    if (!empty($goal)) {
        $mainTarget = $goal->achievement;
        $dailyTarget = 0;
        $weeklyTarget = 0;
        $monthlyTarget = 0;
        $quarterlyTarget = 0;
        $halfYearlyTarget = 0;
        $yearlyTarget = 0;
        $customTarget = 0;

        $curQuarterDates = get_current_quarter_dates();
        $curWeekDates = get_current_week_dates();
        $curHalfYearDates = get_current_year_half_dates();

        $workingDaysInCurMonth = count_valid_days(date('Y-m-01'), date('Y-m-t'), $holidayDatesArr);
        $workingDaysInCurWeek = count_valid_days($curWeekDates['start_date'], $curWeekDates['end_date'], $holidayDatesArr);
        $workingDaysInCurQuarter = count_valid_days($curQuarterDates['start_date'], $curQuarterDates['end_date'], $holidayDatesArr);
        $workingDaysInHalfYear = count_valid_days($curHalfYearDates['start_date'], $curHalfYearDates['end_date'], $holidayDatesArr);
        $workingDaysInYear = count_valid_days(date('Y-01-01'), date('Y-12-31'), $holidayDatesArr);
        $workingDaysInCustom = count_valid_days($goal->start_date, $goal->end_date, $holidayDatesArr);


        $dailyShow = true;
        $weeklyShow = true;
        $monthlyShow = true;
        $quarterlyShow = true;
        $halfYearlyShow = true;
        $yearlyShow = true;
        $customShow = true;

        if ($goal->goal_duration_type == 1) { // daily
            $dailyTarget = $mainTarget;
            $monthlyTarget = $mainTarget * $workingDaysInCurMonth;
            $weeklyTarget = $mainTarget * $workingDaysInCurWeek;
            $quarterlyTarget = $mainTarget * $workingDaysInCurQuarter;
            $yearlyTarget = $mainTarget * $workingDaysInYear;
            $halfYearlyShow = false;
            $customShow = false;
        } else if ($goal->goal_duration_type == 2) { // weekly
            $dailyTarget = $mainTarget / $workingDaysInCurWeek;
            $weeklyTarget = $mainTarget;
            $monthlyShow = false;
            $quarterlyShow = false;
            $halfYearlyShow = false;
            $yearlyShow = false;
            $customShow = false;
        } else if ($goal->goal_duration_type == 3) { // monthly
            $dailyTarget = $mainTarget / $workingDaysInCurMonth;
            $monthlyTarget = $mainTarget;
            $quarterlyTarget = $mainTarget * 3;
            $yearlyTarget = $mainTarget * 12;
            $halfYearlyTarget = $mainTarget * 6;
            $weeklyShow = false;
            $customShow = false;
        } else if ($goal->goal_duration_type == 4) { // quarterly
            $quarterlyTarget = $mainTarget;
            $monthlyTarget = $quarterlyTarget / 3;
            $dailyTarget = $quarterlyTarget / $workingDaysInCurQuarter;
            $halfYearlyTarget = $quarterlyTarget * 2;
            $yearlyTarget = $halfYearlyTarget * 2;
            $weeklyShow = false;
            $customShow = false;
        } else if ($goal->goal_duration_type == 5) { // half yearly
            $halfYearlyTarget = $mainTarget;
            $quarterlyTarget = $halfYearlyTarget / 2;
            $monthlyTarget = $quarterlyTarget / 3;
            $dailyTarget = $halfYearlyTarget / $workingDaysInHalfYear;
            $yearlyTarget = $halfYearlyTarget * 2;
            $weeklyShow = false;
            $customShow = false;
        } else if ($goal->goal_duration_type == 6) { // custom
            $customTarget = $mainTarget;
            $dailyTarget = $customTarget / $workingDaysInCustom;
            $weeklyShow = false;
            $monthlyShow = false;
            $quarterlyShow = false;
            $halfYearlyShow = false;
            $yearlyShow = false;
        } else if ($goal->goal_duration_type == 7) { // yearly
            $yearlyTarget = $mainTarget;
            $halfYearlyTarget = $yearlyTarget / 2;
            $quarterlyTarget = $halfYearlyTarget / 2;
            $monthlyTarget = $quarterlyTarget / 3;
            $dailyTarget = $yearlyTarget / $workingDaysInCurMonth;
            $customShow = false;
            $weeklyShow = false;
        }

        // Daily Progress
        if ($dailyShow) {
            $goal->achievement = decimalFormat($dailyTarget);
            $achievements = $CI->goals_model->calculate_goal_achievement_new($goal, get_staff_user_id(), date('Y-m-d'), date('Y-m-d'));
            $result[] = array(
                "title" => "Today",
                "achievements" => $achievements,
                "target" => decimalFormat($dailyTarget)
            );
        }

        // Weekly Progress
        if ($weeklyShow) {
            $goal->achievement = decimalFormat($weeklyTarget);
            $achievement = $CI->goals_model->calculate_goal_achievement_new($goal, get_staff_user_id(), $curWeekDates['start_date'], $curWeekDates['end_date']);
            $result[] = array(
                "title" => "This Week",
                "achievements" => $achievement,
                "target" => decimalFormat($weeklyTarget)
            );
        }

        // Monthly Progress
        if ($monthlyShow) {
            $goal->achievement = decimalFormat($monthlyTarget);
            $achievement = $CI->goals_model->calculate_goal_achievement_new($goal, get_staff_user_id(), date('Y-m-01'), date('Y-m-t'));
            $result[] = array(
                "title" => "This Month",
                "achievements" => $achievement,
                "target" => decimalFormat($monthlyTarget)
            );
        }

        // Quarter Progress
        if ($quarterlyShow) {
            $goal->achievement = decimalFormat($quarterlyTarget);
            $achievement = $CI->goals_model->calculate_goal_achievement_new($goal, get_staff_user_id(), $curQuarterDates['start_date'], $curQuarterDates['end_date']);
            $result[] = array(
                "title" => "This Quarter",
                "achievements" => $achievement,
                "target" => decimalFormat($quarterlyTarget)
            );
        }

        // Half Yearly Progress
        if ($halfYearlyShow) {
            $goal->achievement = decimalFormat($halfYearlyTarget);
            $achievement = $CI->goals_model->calculate_goal_achievement_new($goal, get_staff_user_id(), $curHalfYearDates['start_date'], $curHalfYearDates['end_date']);
            $result[] = array(
                "title" => $curHalfYearDates['title'],
                "achievements" => $achievement,
                "target" => decimalFormat($halfYearlyTarget)
            );
        }

        // Custom Dates Progress
        if ($customShow) {
            $goal->achievement = decimalFormat($customTarget);
            $achievement = $CI->goals_model->calculate_goal_achievement_new($goal, get_staff_user_id(), $goal->start_date, $goal->end_date);
            $result[] = array(
                "title" => "Overall",
                "achievements" => $achievement,
                "target" => decimalFormat($customTarget)
            );
        }

        // Yearly Progress
        if ($yearlyShow) {
            $goal->achievement = decimalFormat($yearlyTarget);
            $achievement = $CI->goals_model->calculate_goal_achievement_new($goal, get_staff_user_id(), date('Y-01-01'), date('Y-12-31'));
            $result[] = array(
                "title" => "This Year",
                "achievements" => $achievement,
                "target" => decimalFormat($yearlyTarget)
            );
        }
    }
    return $result;
}

// Helper function to count weekdays excluding Sundays and holidays
function count_valid_days($start_date, $end_date, $holidayDatesArr)
{
    $validDaysCount = 0;

    $startDate = new DateTime($start_date);
    $endDate = new DateTime($end_date);
    $interval = new DateInterval('P1D');
    $dateRange = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

    foreach ($dateRange as $date) {
        // Check if it's not a Sunday and not a holiday
        if ($date->format('N') < 7 && !in_array($date->format('Y-m-d'), $holidayDatesArr)) {
            $validDaysCount++;
        }
    }

    return $validDaysCount;
}
