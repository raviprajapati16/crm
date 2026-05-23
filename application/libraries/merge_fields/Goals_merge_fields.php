<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Goals_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Goal Subject',
                'key'       => '{goal_subject}',
                'available' => [
                    'goals',
                ],
            ],
            [
                'name'      => 'Goal Type',
                'key'       => '{goal_type}',
                'available' => [
                    'goals',
                ],
            ],
            [
                'name'      => 'Goal Target',
                'key'       => '{goal_target}',
                'available' => [
                    'goals',
                ],
            ],
            [
                'name'      => 'Goal Duration Type',
                'key'       => '{goal_duration_type}',
                'available' => [
                    'goals',
                ],
            ],
            [
                'name'      => 'Goal Description',
                'key'       => '{goal_description}',
                'available' => [
                    'goals',
                ],
            ],
            [
                'name'      => 'Goal Duration Period',
                'key'       => '{goal_duration_period}',
                'available' => [],
                'templates' => [
                    'goals-achieve',
                    'goals-failed-to-achieve',
                ],
            ],
            [
                'name'      => 'Goal Achievement',
                'key'       => '{goal_achievement}',
                'available' => [],
                'templates' => [
                    'goals-achieve',
                    'goals-failed-to-achieve',
                ],
            ],
            // [
            //     'name'      => 'Goal Progress Graph',
            //     'key'       => '{goal_progress_graph}',
            //     'available' => [],
            //     'templates' => [
            //         'goals-achieve',
            //         'goals-failed-to-achieve',
            //     ],
            // ],
        ];
    }

    public function format($id, $staff_id = "")
    {
        $fields = [];
        $fields['{goal_subject}'] = '';
        $fields['{goal_type}'] = '';
        $fields['{goal_target}'] = '';
        $fields['{goal_duration_type}'] = '';
        $fields['{goal_description}'] = '';
        $fields['{goal_duration_period}'] = '';
        $fields['{goal_achievement}'] = '';
        $fields['{goal_progress_graph}'] = '';

        if (is_numeric($id)) {
            $this->ci->load->model('goals/goals_model');
            $goal = $this->ci->goals_model->get($id);
        } else {
            $goal = $id;
        }
        if (!$goal) {
            return $fields;
        }
        $fields['{goal_subject}'] = $goal->subject;
        $fields['{goal_type}'] = format_goal_type($goal->goal_type);
        $fields['{goal_target}'] = $goal->achievement;
        $fields['{goal_duration_type}'] = get_goal_duration_title_by_key($goal->goal_duration_type);
        if ($goal->goal_duration_type == 6) {
            $fields['{goal_duration_type}'] = get_goal_duration_title_by_key($goal->goal_duration_type) . " - " . date('d M, Y', strtotime($goal->start_date)) . " to " . date('d M, Y', strtotime($goal->end_date));
        }
        $fields['{goal_description}'] = $goal->description;
        $durationData = get_goal_last_duration_by_type($goal->goal_duration_type, $goal->start_date, $goal->end_date);
        if ($durationData['status']) {
            $acheivement = $this->ci->goals_model->calculate_goal_achievement_new($goal->id, $staff_id, $durationData['start_date'], $durationData['end_date']);
            $fields['{goal_achievement}'] = $acheivement['total'];
            $fields['{goal_duration_period}'] = $durationData['title'];
            //$fields['{goal_progress_graph}'] = $this->ci->load->view('goals/goal_progress_circular', ['percentage' => $acheivement['percent']], true);
        }
        return hooks()->apply_filters('goals_merge_fields', $fields, ['id' => $goal->id, 'goal' => $goal]);
    }
}
