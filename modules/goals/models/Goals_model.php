<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Goals_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param  integer (optional)
     * @return object
     * Get single goal
     */
    public function get($id = '', $exclude_notified = false)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'goals')->row();
        }
        if ($exclude_notified == true) {
            $this->db->where('notified', 0);
        }
        $this->db->where('active', '1');
        return $this->db->get(db_prefix() . 'goals')->result_array();
    }

    public function get_staff_goals($staff_id, $exclude_notified = true)
    {
        $this->db->select(db_prefix() . 'goals.*');
        $this->db->from(db_prefix() . 'goals');
        $this->db->join(db_prefix() . 'goal_staff', db_prefix() . 'goal_staff.goal_id = ' . db_prefix() . 'goals.id');
        $this->db->where(db_prefix() . 'goal_staff.staff_id', $staff_id);
        $this->db->where(db_prefix() . 'goals.active', '1');
        $this->db->where(db_prefix() . 'goal_staff.active', 'true');
        if ($exclude_notified) {
            $this->db->where(db_prefix() . 'goals.notified', 0);
        }
        $this->db->order_by(db_prefix() . 'goals.id', 'desc');
        $goals = $this->db->get()->result_array();
        foreach ($goals as $key => $val) {
            $goals[$key]['achievements']    = $this->calculate_goal_achievement_new($val['id'], $staff_id);
            $goals[$key]['goal_type_name'] = format_goal_type($val['goal_type']);
        }
        return $goals;
    }


    /**
     * Add new goal
     * @param mixed $data All $_POST dat
     * @return mixed
     */
    public function add($data)
    {
        $data['notify_when_fail']    = isset($data['notify_when_fail']) ? 1 : 0;
        $data['notify_when_achieve'] = isset($data['notify_when_achieve']) ? 1 : 0;
        $data['email_when_achieve'] = isset($data['email_when_achieve']) ? 1 : 0;
        $data['email_when_fail'] = isset($data['email_when_fail']) ? 1 : 0;
        $data['email_when_assign'] = isset($data['email_when_assign']) ? 1 : 0;
        $data['contract_type'] = $data['contract_type'] == '' ? 0 : $data['contract_type'];
        $data['staff_id']      = $data['staff_id'] == '' ? 0 : $data['staff_id'];
        if ($data['goal_duration_type'] == "6") {
            $data['start_date'] = to_sql_date($data['start_date']);
            $data['end_date'] = to_sql_date($data['end_date']);
        } else {
            $data['start_date'] = NULL;
            $data['end_date'] = NULL;
        }
        $data['created_at']  = date('Y-m-d H:i:s');
        $data['created_by']  = get_staff_user_id();
        $this->db->insert(db_prefix() . 'goals', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Goal Added [ID:' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update goal
     * @param  mixed $data All $_POST data
     * @param  mixed $id   goal id
     * @return boolean
     */
    public function update($data, $id)
    {
        $data['notify_when_fail']    = isset($data['notify_when_fail']) ? 1 : 0;
        $data['notify_when_achieve'] = isset($data['notify_when_achieve']) ? 1 : 0;
        $data['email_when_achieve'] = isset($data['email_when_achieve']) ? 1 : 0;
        $data['email_when_fail'] = isset($data['email_when_fail']) ? 1 : 0;
        $data['email_when_assign'] = isset($data['email_when_assign']) ? 1 : 0;
        $data['contract_type'] = $data['contract_type'] == '' ? 0 : $data['contract_type'];
        //        $data['staff_id']      = $data['staff_id'] == '' ? 0 : $data['staff_id'];
        if ($data['goal_duration_type'] == "6") {
            $data['start_date'] = to_sql_date($data['start_date']);
            $data['end_date'] = to_sql_date($data['end_date']);
        } else {
            $data['start_date'] = NULL;
            $data['end_date'] = NULL;
        }

        $goal = $this->get($id);

        if ($goal->notified == 1 && date('Y-m-d') < $data['end_date']) {
            // After goal finished, user changed/extended date? If yes, set this goal to be notified
            $data['notified'] = 0;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'goals', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Goal Updated [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    public function update_direct($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'goals', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Goal Updated [ID:' . $id . ']');
            return true;
        }

        return false;
    }

    public function update_goal_staff($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'goal_staff', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Goal Staff Status Updated [ID:' . $id . ']');
            return true;
        }
        return false;
    }

    /**
     * Delete goal
     * @param  mixed $id goal id
     * @return boolean
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'goals');
        if ($this->db->affected_rows() > 0) {
            log_activity('Goal Deleted [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Notify staff members about goal result
     * @param  mixed $id          goal id
     * @param  string $notify_type is success or failed
     * @param  mixed $achievement total achievent (Option)
     * @return boolean
     */
    public function notify_staff_members($data)
    {
        $goal = $this->get($data['goal_id']);
        $goal_desc = "";
        if ($data['status'] == 'success') {
            $goal_desc = 'not_goal_message_success';
        } else {
            $goal_desc = 'not_goal_message_failed';
        }
        $notified = add_notification([
            'fromcompany'     => 1,
            'touserid'        => $data['staff_id'],
            'description'     => $goal_desc,
            'additional_data' => serialize([
                format_goal_type($goal->goal_type),
                $goal->achievement,
                $data['achievement']['total'],
                $data['goal_duration_title'],
            ]),
        ]);
        if ($notified) {
            pusher_trigger_notification([$data['staff_id']]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Calculate goal achievement
     * @param  mixed $id goal id
     * @return array
     */
    public function calculate_goal_achievement($id)
    {
        $goal = $this->get($id);
        $type = $goal->goal_type;
        $goal_duration_type = $goal->goal_duration_type;
        $total = 0;
        $percent = 0;

        switch ($goal_duration_type) {
            case 1: // Daily
                $start_date = date('Y-m-d');
                $end_date = date('Y-m-d 23:59:59');
                break;
            case 2: // Weekly
                $start_date = date('Y-m-d', strtotime('monday this week'));
                $end_date = date('Y-m-d 23:59:59', strtotime('sunday this week'));
                break;
            case 3: // Monthly
                $start_date = date('Y-m-01');
                $end_date = date('Y-m-t 23:59:59');
                break;
            case 4: // Quarterly
                $current_month = date('n');
                $current_quarter = ceil($current_month / 3);
                $start_date = date('Y-m-d', strtotime('first day of ' . (($current_quarter - 1) * 3 + 1) . ' month this year'));
                $end_date = date('Y-m-d 23:59:59', strtotime('last day of ' . ($current_quarter * 3) . ' month this year'));
                break;
            case 5: // Half Yearly
                $current_month = date('n');
                if ($current_month <= 6) {
                    $start_date = date('Y-01-01');
                    $end_date = date('Y-06-30 23:59:59');
                } else {
                    $start_date = date('Y-07-01');
                    $end_date = date('Y-12-31 23:59:59');
                }
                break;
            case 6: // Custom (use values from database)
                $start_date = $goal->start_date;
                $end_date = $goal->end_date;
                break;
            case 7: // Yearly
                $start_date = date('Y-01-01');
                $end_date = date('Y-12-31 23:59:59');
                break;
            default:
                $start_date = date('Y-m-d');
                $end_date = date('Y-m-d 23:59:59');
                break;
        }

        if ($type == 1) {
            $sql = 'SELECT SUM(amount) as total FROM ' . db_prefix() . 'invoicepaymentrecords';
            if ($goal->staff_id != 0) {
                $sql .= ' JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid';
            }
            $sql .= ' WHERE ' . db_prefix() . "invoicepaymentrecords.date BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
            if ($goal->staff_id != 0) {
                $sql .= ' AND (' . db_prefix() . 'invoices.addedfrom=' . $goal->staff_id . ' OR sale_agent=' . $goal->staff_id . ')';
            }
        } elseif ($type == 2) {
            $sql = 'SELECT COUNT(' . db_prefix() . 'leads.id) as total FROM ' . db_prefix() . "leads WHERE DATE(date_converted) BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND status = 1 AND " . db_prefix() . 'leads.id IN (SELECT leadid FROM ' . db_prefix() . 'clients WHERE leadid=' . db_prefix() . 'leads.id)';
            if ($goal->staff_id != 0) {
                $sql .= ' AND CASE WHEN assigned=0 THEN addedfrom=' . $goal->staff_id . ' ELSE assigned=' . $goal->staff_id . ' END';
            }
        } elseif ($type == 3) {
            $sql = 'SELECT COUNT(' . db_prefix() . 'clients.userid) as total FROM ' . db_prefix() . "clients WHERE DATE(datecreated) BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND deleted_at IS NULL AND leadid IS NULL";
            if ($goal->staff_id != 0) {
                $sql .= ' AND addedfrom=' . $goal->staff_id;
            }
        } elseif ($type == 4) {
            $sql = 'SELECT COUNT(' . db_prefix() . 'clients.userid) as total FROM ' . db_prefix() . "clients WHERE DATE(datecreated) BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND deleted_at IS NULL";
            if ($goal->staff_id != 0) {
                $sql .= ' AND addedfrom=' . $goal->staff_id;
            }
        } elseif ($type == 5 || $type == 7) {
            $column = 'dateadded';
            if ($type == 7) {
                $column = 'datestart';
            }
            $sql = 'SELECT count(id) as total FROM ' . db_prefix() . 'contracts WHERE ' . $column . " BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND contract_type = " . $goal->contract_type . ' AND trash = 0';
            if ($goal->staff_id != 0) {
                $sql .= ' AND addedfrom=' . $goal->staff_id;
            }
        } elseif ($type == 6) {
            $sql = 'SELECT count(id) as total FROM ' . db_prefix() . "estimates WHERE DATE(invoiced_date) BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND invoiceid IS NOT NULL AND invoiceid NOT IN (SELECT id FROM " . db_prefix() . 'invoices WHERE status=5)';
            if ($goal->staff_id != 0) {
                $sql .= ' AND (addedfrom=' . $goal->staff_id . ' OR sale_agent=' . $goal->staff_id . ')';
            }
        } else {
            $sql = hooks()->apply_filters('calculate_goal_achievement_sql', '', $goal);
            if ($sql === '') {
                return;
            }
        }

        $total = floatval($this->db->query($sql)->row()->total);

        if ($total >= floatval($goal->achievement)) {
            $percent = 100;
        } else {
            if ($total !== 0) {
                $percent = number_format(($total * 100) / $goal->achievement, 2);
            }
        }

        $progress_bar_percent = $percent / 100;

        return [
            'total'                => $total,
            'percent'              => $percent,
            'progress_bar_percent' => $progress_bar_percent,
        ];
    }

    public function calculate_goal_achievement_new($id, $staff_ids = "", $start_date = "", $end_date = "")
    {
        if (empty($staff_ids)) {
            $staff_ids = get_goal_staff_ids($id, true);
            if (!is_admin() && has_permission('goals', '', 'view_own')) {
                $staff_ids = get_staff_user_id();
            }
        }
        if (is_object($id)) {
            $goal = $id;
        } else {
            $goal = $this->get($id);
        }
        $type = $goal->goal_type;
        $goal_duration_type = $goal->goal_duration_type;
        $total = 0;
        $percent = 0;

        if ($start_date == "" && $end_date == "") {
            switch ($goal_duration_type) {
                case 1: // Daily
                    $start_date = date('Y-m-d');
                    $end_date = date('Y-m-d 23:59:59');
                    break;
                case 2: // Weekly
                    $start_date = date('Y-m-d', strtotime('monday this week'));
                    $end_date = date('Y-m-d 23:59:59', strtotime('sunday this week'));
                    break;
                case 3: // Monthly
                    $start_date = date('Y-m-01');
                    $end_date = date('Y-m-d 23:59:59');
                    break;
                case 4: // Quarterly
                    $current_month = date('n');
                    $current_quarter = ceil($current_month / 3);
                    $start_month = ($current_quarter - 1) * 3 + 1;
                    $end_month = $current_quarter * 3;
                    $current_year = date('Y');
                    $start_date = date('Y-m-d', strtotime("$current_year-$start_month-01"));
                    $end_date = date('Y-m-t 23:59:59', strtotime("$current_year-$end_month-01"));
                    break;
                case 5: // Half Yearly
                    $current_month = date('n');
                    if ($current_month <= 6) {
                        $start_date = date('Y-01-01');
                        $end_date = date('Y-06-30 23:59:59');
                    } else {
                        $start_date = date('Y-07-01');
                        $end_date = date('Y-12-31 23:59:59');
                    }
                    break;
                case 6: // Custom (use values from database)
                    $start_date = $goal->start_date;
                    $end_date = $goal->end_date;
                    break;
                case 7: // Yearly
                    $start_date = date('Y-01-01');
                    $end_date = date('Y-12-31 23:59:59');
                    break;
                default:
                    $start_date = date('Y-m-d');
                    $end_date = date('Y-m-d 23:59:59');
                    break;
            }
        }

        if (!empty($staff_ids)) {
            if (is_array($staff_ids)) {
                $staff_condition = ' IN (' . implode(',', $staff_ids) . ')';
                $goal->achievement = $goal->achievement * count($staff_ids);
            } else {
                $staff_condition = ' = ' . $staff_ids;
            }
        }

        if ($type == 1) {
            $sql = 'SELECT SUM(amount) as total
                FROM ' . db_prefix() . 'invoicepaymentrecords
                JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid
                WHERE ' . db_prefix() . "invoicepaymentrecords.date BETWEEN '" . $start_date . "' AND '" . $end_date . "'
                AND " . db_prefix() . "invoicepaymentrecords.deleted_at IS NULL
                AND " . db_prefix() . "invoices.deleted_at IS NULL";
            if (!empty($staff_condition)) {
                $sql .= ' AND (' . db_prefix() . 'invoices.addedfrom ' . $staff_condition . ' OR ' . db_prefix() . 'invoices.sale_agent ' . $staff_condition . ')';
            }
        } elseif ($type == 2) {
            $sql = 'SELECT COUNT(' . db_prefix() . 'leads.id) as total
                FROM ' . db_prefix() . "leads
                WHERE DATE(date_converted) BETWEEN '" . $start_date . "' AND '" . $end_date . "'
                AND status = 1
                AND " . db_prefix() . 'leads.id IN (
                    SELECT leadid
                    FROM ' . db_prefix() . 'clients
                    WHERE leadid = ' . db_prefix() . 'leads.id
                )
                AND ' . db_prefix() . 'leads.isDeleted = "false"';
            if (!empty($staff_condition)) {
                $sql .= ' AND CASE WHEN assigned=0 THEN addedfrom' . $staff_condition . ' ELSE assigned' . $staff_condition . ' END';
            }
        } elseif ($type == 3) {
            $sql = 'SELECT COUNT(' . db_prefix() . 'clients.userid) as total
                FROM ' . db_prefix() . "clients
                WHERE DATE(datecreated) BETWEEN '" . $start_date . "' AND '" . $end_date . "'
                AND deleted_at IS NULL
                AND leadid IS NULL
                AND " . db_prefix() . 'clients.deleted_at IS NULL';
            if (!empty($staff_condition)) {
                $sql .= ' AND addedfrom' . $staff_condition;
            }
        } elseif ($type == 4) {
            $sql = 'SELECT COUNT(' . db_prefix() . 'clients.userid) as total FROM ' . db_prefix() . "clients WHERE DATE(datecreated) BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND deleted_at IS NULL";
            if (!empty($staff_condition)) {
                $sql .= ' AND addedfrom' . $staff_condition;
            }
        } elseif ($type == 5 || $type == 7) {
            $column = ($type == 7) ? 'datestart' : 'dateadded';
            $sql = 'SELECT COUNT(id) as total
                FROM ' . db_prefix() . 'contracts
                WHERE ' . $column . " BETWEEN '" . $start_date . "' AND '" . $end_date . "'
                AND contract_type = " . $goal->contract_type . '
                AND trash = 0
                AND deleted_at IS NULL';
            if (!empty($staff_condition)) {
                $sql .= ' AND addedfrom' . $staff_condition;
            }
        } elseif ($type == 6) {
            $sql = 'SELECT COUNT(id) as total
                FROM ' . db_prefix() . "estimates
                WHERE DATE(invoiced_date) BETWEEN '" . $start_date . "' AND '" . $end_date . "'
                AND invoiceid IS NOT NULL
                AND invoiceid NOT IN (
                    SELECT id
                    FROM " . db_prefix() . 'invoices
                    WHERE status=5
                    AND deleted_at IS NULL
                )
                AND deleted_at IS NULL';
            if (!empty($staff_condition)) {
                $sql .= ' AND (addedfrom' . $staff_condition . ' OR sale_agent' . $staff_condition . ')';
            }
        } elseif ($type == 8 || $type == 9 || $type == 10 || $type == 11) {
            if ($type == 8) {
                $reminder_action = "Call";
                $status = "Attend";
            } else if ($type == 9) {
                $reminder_action = "Online Meeting";
                $status = "Attend";
            } else if ($type == 10) {
                $reminder_action = "Face To Face";
                $status = "Present";
            } else if ($type == 11) {
                $reminder_action = "Plant Visit";
                $status = "Visited";
            }
            $sql = 'SELECT COUNT(id) as total
                    FROM ' . db_prefix() . "reminders
                    WHERE DATE(action_date) BETWEEN '" . $start_date . "' AND '" . $end_date . "'
                    AND rel_type = 'lead' AND reminder_action = '" . $reminder_action . "' AND status = '" . $status . "'
                    AND deleted_at IS NULL";

            if (!empty($staff_condition)) {
                $sql .= ' AND staff ' . $staff_condition;
            }
        } elseif ($type == 12) {
            $sql = 'SELECT COUNT(id) as total
                    FROM ' . db_prefix() . "lead_inquiry_forms
                    WHERE DATE(created_at) BETWEEN '" . $start_date . "' AND '" . $end_date . "'
                    AND form_status = 'approved'
                    AND deleteddate IS NULL";

            if (!empty($staff_condition)) {
                $sql .= ' AND created_by ' . $staff_condition;
            }
        } elseif ($type == 13) {
            // NO SQL : query beacuse of lots of complex query structure so create helper function calculate_volume_of_business which is applied below.
        } else {
            $sql = hooks()->apply_filters('calculate_goal_achievement_sql', '', $goal);
            if ($sql === '') {
                return;
            }
        }

        if (!empty($staff_ids)) {
            if($type == 13) {
                $total = calculate_volume_of_business($goal->id, $staff_ids, $start_date, $end_date)['total_amount'];
            }else{
                $total = floatval($this->db->query($sql)->row()->total);
            }
            if ($total >= floatval($goal->achievement)) {
                $percent = 100;
            } else {
                if ($total !== 0) {
                    $percent = number_format(($total * 100) / $goal->achievement, 2);
                }
            }
            $progress_bar_percent = $percent / 100;
        } else {
            $total = 0;
            $percent = 0;
            $progress_bar_percent = 0;
        }
        return [
            'total'                => $total,
            'percent'              => $percent,
            'progress_bar_percent' => $progress_bar_percent,
        ];
    }
}
