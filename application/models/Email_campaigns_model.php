<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Email_campaigns_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function countRecords($where)
    {
        $this->db->where($where);
        return $this->db->count_all_results(db_prefix() . 'emailcampaign');
    }

    public function add($data)
    {
        $data['created_by'] = get_staff_user_id();
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'emailcampaign', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity("Email Campaign Created. ID [$insert_id]");
            return $insert_id;
        }
        return false;
    }

    public function queue_add($data)
    {
        if (!empty($data)) {
            $this->db->insert_batch(db_prefix() . 'emailcampaign_queue', $data);
            $affected_rows = $this->db->affected_rows();
            if ($affected_rows > 0) {
                return true;
            }
        }
        return false;
    }

    public function updateCampaign($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'emailcampaign', $data);
        return true;
    }

    public function updateQueue($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'emailcampaign_queue', $data);
        return true;
    }

    public function updateQueueByCampaignId($where, $data)
    {
        $this->db->where($where);
        $this->db->update(db_prefix() . 'emailcampaign_queue', $data);
        return true;
    }

    public function delete_campaign($id)
    {
        $this->db->trans_start();
        $this->db->where('campaign_id', $id);
        $this->db->delete('tblemailcampaign_queue');
        $this->db->where('id', $id);
        $this->db->delete('tblemailcampaign');
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            return false;
        }
        log_activity("Email Campaign Deleted. ID [$id]");
        return true;
    }

    public function is_limit_reached($campaign_id, $queue_id)
    {
        $campaign = $this->db->select('*')
            ->from(db_prefix() . 'emailcampaign')
            ->where('id', $campaign_id)
            ->get()->row();

        $queue = $this->db->select('*')
            ->from(db_prefix() . 'emailcampaign_queue')
            ->where('id', $queue_id)
            ->get()->row();

        $maximum_limit_per_day = $campaign->max_send_limit;

        $campaign_paused = true;
        $this->db->select('mail_send_from_id, send_from, campaign_id');
        $this->db->from(db_prefix() . 'emailcampaign_queue');
        $this->db->where('campaign_id', $campaign_id);
        $this->db->group_by(['mail_send_from_id', 'send_from']);
        $query = $this->db->get();
        $senderData = $query->result();
        if (!empty($senderData)) {
            foreach ($senderData as $sender) {
                $this->db->select('*');
                $this->db->select('mail_send_from_id, send_from, campaign_id');
                $this->db->from(db_prefix() . 'emailcampaign_queue');
                $this->db->where('campaign_id', $campaign_id);
                $this->db->where('send_from',  $sender->send_from);
                $this->db->where('mail_send_from_id', $sender->mail_send_from_id);
                $this->db->where('DATE(email_sent_at)', date('Y-m-d'));
                $countResult = $this->db->count_all_results();
                if ($countResult < $maximum_limit_per_day) {
                    $campaign_paused = false;
                }
            }
        }

        if ($campaign_paused) {
            return ['campaign_paused' => true];
        }

        //Sender Limit
        $sender_limit_reached = true;
        $this->db->select('COUNT(*) AS emails_sent_today');
        $this->db->from(db_prefix() . 'emailcampaign_queue');
        $this->db->where('DATE(email_sent_at)', date('Y-m-d'));
        $this->db->where('campaign_id', $campaign_id);
        $this->db->where('send_from', $queue->send_from);
        $this->db->where('mail_send_from_id', $queue->mail_send_from_id);
        $query = $this->db->get();
        $getCountData = $query->row_array();
        if (!empty($getCountData)) {
            if ($getCountData['emails_sent_today'] < $maximum_limit_per_day) {
                $sender_limit_reached = false;
            }
        } else {
            $sender_limit_reached = false;
        }

        if ($sender_limit_reached) {
            return ['limit_reached' => true];
        }
    }

    public function is_limit_reached_campaign($campaign_id)
    {
        $campaign = $this->db->select('*')
            ->from(db_prefix() . 'emailcampaign')
            ->where('id', $campaign_id)
            ->get()->row();
        $maximum_limit_per_day = $campaign->max_send_limit;
        $this->db->select('mail_send_from_id, send_from, campaign_id, COUNT(*) AS emails_sent_today');
        $this->db->from(db_prefix() . 'emailcampaign_queue');
        $this->db->where('DATE(email_sent_at)', date('Y-m-d'));
        $this->db->where('campaign_id', $campaign_id);
        $this->db->group_by(['mail_send_from_id', 'send_from', 'campaign_id']);
        $query = $this->db->get();
        $getCountData = $query->result_array();
        if (!empty($getCountData)) {
            foreach ($getCountData as $key => $data) {
                if ($data['emails_sent_today'] < $maximum_limit_per_day) {
                    return false;
                }
            }
        } else {
            return false;
        }
        return true;
    }

    public function search_relation_data($data)
    {
        $q = $data['q'];
        $leadAssignedWhere = "";
        $customerAssignedWhere = "";
        $data['assignee'] = (isset($data['assignee']) && !empty($data['assignee'])) ? array_filter($data['assignee']) : [];
        if (!empty($data['assignee'])) {
            $data['assignee'] = implode(",", $data['assignee']);
            $leadAssignedWhere = "assigned IN (" . $data['assignee'] . ")";
            $customerAssignedWhere = "userid IN (SELECT customer_id FROM " . db_prefix() . "customer_admins WHERE staff_id IN (" . $data['assignee'] . "))";
        } else if (!has_permission('email_campaigns', '', 'view')) {
            if (manager_employee_data_access_permission_check("email_campaigns")) {
                $leadAssignedWhere = "assigned IN (" . get_manager_assigned_staff_ids("", true) . ")";
            } else {
                $leadAssignedWhere = "assigned = " . get_staff_user_id();
            }
            $customerAssignedWhere = "userid IN (SELECT customer_id FROM " . db_prefix() . "customer_admins WHERE staff_id IN (" . implode(",", get_assigned_customers_ids_by_staff()) . "))";
        }

        $result = [
            'result'         => [],
            'type'           => 'leads',
            'search_heading' => _l('leads'),
        ];

        if ($data['type'] == "leads") {
            $this->db->select();
            $this->db->from(db_prefix() . 'leads');
            if (!empty($leadAssignedWhere)) {
                $this->db->where($leadAssignedWhere, null, false);
            }
            $this->db->where('(name LIKE "%' . $this->db->escape_like_str($q) . '%"
                        OR title LIKE "%' . $this->db->escape_like_str($q) . '%"
                        OR company LIKE "%' . $this->db->escape_like_str($q) . '%"
                        OR zip LIKE "%' . $this->db->escape_like_str($q) . '%"
                        OR city LIKE "%' . $this->db->escape_like_str($q) . '%"
                        OR state LIKE "%' . $this->db->escape_like_str($q) . '%"
                        OR address LIKE "%' . $this->db->escape_like_str($q) . '%"
                        OR email LIKE "%' . $this->db->escape_like_str($q) . '%"
                        OR phonenumber LIKE "%' . $this->db->escape_like_str($q) . '%"
                        )', null, false);
            $this->db->where('isDeleted', 'false');
            $this->db->where('lost', '0');
            $this->db->where('is_vendor', '0');
            $this->db->order_by('name', 'ASC');
            $query = $this->db->get();
            $result['result'] = $query->result_array();
        } else if ($data['type'] == "customer") {
            $this->db->select('company as name, userid as id');
            $this->db->from(db_prefix() . 'clients');
            if (!empty($customerAssignedWhere)) {
                $this->db->where($customerAssignedWhere, null, false);
            }
            $this->db->where('(company LIKE "%' . $this->db->escape_like_str($q) . '%"
                        OR zip LIKE "%' . $this->db->escape_like_str($q) . '%"
                        OR city LIKE "%' . $this->db->escape_like_str($q) . '%"
                        OR state LIKE "%' . $this->db->escape_like_str($q) . '%"
                        OR address LIKE "%' . $this->db->escape_like_str($q) . '%"
                        OR phonenumber LIKE "%' . $this->db->escape_like_str($q) . '%"
                        )', null, false);
            $this->db->where('deleted_at IS NULL', null, false);
            $this->db->where('active', '1');
            $query = $this->db->get();
            $result['result'] = $query->result_array();
        }
        return $result['result'];
    }

    public function get_email_campaign_stats($campaignId = "")
    {
        $where = "";
        if (!has_permission('email_campaigns', '', 'view')) {
            if (manager_employee_data_access_permission_check("email_campaigns")) {
                $where = "tc.created_by IN (" . get_manager_assigned_staff_ids("", true) . ")";
            } else {
                $where = "tc.created_by = " . get_staff_user_id();
            }
        }

        $this->db->select('COUNT(ecq.id) as total_emails');
        $this->db->from(db_prefix() . 'emailcampaign_queue ecq');
        $this->db->join(db_prefix() . 'emailcampaign tc', 'ecq.campaign_id = tc.id');
        if (!empty($where)) {
            $this->db->where($where);
        }
        if (!empty($campaignId)) {
            $this->db->where('ecq.campaign_id', $campaignId);
        }
        $query = $this->db->get();
        $total_emails = $query->row()->total_emails ?? 0;


        $statuses = ['queue', 'sent', 'failed'];
        $counts = [];
        foreach ($statuses as $status) {
            $this->db->select("COUNT(ecq.id) as count");
            $this->db->from(db_prefix() . 'emailcampaign_queue ecq');
            $this->db->join(db_prefix() . 'emailcampaign tc', 'ecq.campaign_id = tc.id');
            $this->db->where('ecq.status', $status);
            if (!empty($where)) {
                $this->db->where($where);
            }
            if (!empty($campaignId)) {
                $this->db->where('ecq.campaign_id', $campaignId);
            }
            if ($status == "queue") {
                $this->db->where('tc.status !=', 'Stopped');
            }
            $query = $this->db->get();
            $counts[$status] = $query->row()->count ?? 0;
        }

        $this->db->select('COUNT(ecq.id) as opened_count');
        $this->db->from(db_prefix() . 'emailcampaign_queue ecq');
        $this->db->join(db_prefix() . 'emailcampaign tc', 'ecq.campaign_id = tc.id');
        $this->db->where('ecq.status', 'sent');
        $this->db->where('ecq.email_open_at IS NOT NULL');
        if (!empty($campaignId)) {
            $this->db->where('ecq.campaign_id', $campaignId);
        }
        if (!empty($where)) {
            $this->db->where($where);
        }
        $query = $this->db->get();
        $opened_count = $query->row()->opened_count ?? 0;
        $not_opened_count = $counts['sent'] - $opened_count;

        $percentages = [
            'queue_percentage' => ($total_emails > 0) ? ($counts['queue'] / $total_emails) * 100 : 0,
            'sent_percentage' => ($total_emails > 0) ? ($counts['sent'] / $total_emails) * 100 : 0,
            'failed_percentage' => ($total_emails > 0) ? ($counts['failed'] / $total_emails) * 100 : 0,
            'opened_percentage' => ($total_emails > 0) ? ($opened_count / $total_emails) * 100 : 0,
            'not_opened_percentage' => ($total_emails > 0) ? ($not_opened_count / $total_emails) * 100 : 0
        ];

        return [
            'total_emails' => $total_emails,
            'queue_count' => $counts['queue'],
            'queue_percentage' => round($percentages['queue_percentage'], 2),
            'sent_count' => $counts['sent'],
            'sent_percentage' => round($percentages['sent_percentage'], 2),
            'failed_count' => $counts['failed'],
            'failed_percentage' => round($percentages['failed_percentage'], 2),
            'opened_count' => $opened_count,
            'opened_percentage' => round($percentages['opened_percentage'], 2),
            'not_opened_count' => $not_opened_count,
            'not_opened_percentage' => round($percentages['not_opened_percentage'], 2)
        ];
    }

    public function get_email_campaign_stats_by_date($campaignId)
    {
        $startDateArr = [];
        $endDateArr = [];
        $this->db->select('MIN(email_sent_at) as start_date, MAX(email_sent_at) as end_date');
        $this->db->where('campaign_id', $campaignId);
        $this->db->where('status', 'sent');
        $query = $this->db->get(db_prefix() . 'emailcampaign_queue');
        $result = $query->row();
        if (!empty($result)) {
            if (!empty($result->start_date)) {
                array_push($startDateArr, $result->start_date);
            }
            if (!empty($result->end_date)) {
                array_push($endDateArr, $result->end_date);
            }
        }

        $this->db->select('MIN(email_open_at) as start_date, MAX(email_open_at) as end_date');
        $this->db->where('campaign_id', $campaignId);
        $this->db->where('status', 'sent');
        $query = $this->db->get(db_prefix() . 'emailcampaign_queue');
        $result = $query->row();
        if (!empty($result)) {
            if (!empty($result->start_date)) {
                array_push($startDateArr, $result->start_date);
            }
            if (!empty($result->end_date)) {
                array_push($endDateArr, $result->end_date);
            }
        }

        if (!empty($startDateArr) && !empty($endDateArr)) {
            $start_date = min($startDateArr);
            $end_date = max($endDateArr);
            $this->db->select('
                DATE(email_sent_at) as sent_date,
                COUNT(*) as total_sent_emails,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent_count,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_count
            ');
            $this->db->where('email_sent_at >=', $start_date);
            $this->db->where('email_sent_at <=', $end_date);
            $this->db->where('campaign_id', $campaignId);
            $this->db->where('status', 'sent');
            $this->db->group_by('DATE(email_sent_at)');
            $this->db->order_by('sent_date', 'ASC');
            $query_sent = $this->db->get(db_prefix() . 'emailcampaign_queue');
            $sent_data = [];
            foreach ($query_sent->result() as $row) {
                $sent_data[$row->sent_date] = [
                    'sent_count' => $row->sent_count,
                    'failed_count' => $row->failed_count,
                    'total_sent_emails' => $row->total_sent_emails
                ];
            }

            $this->db->select('
                DATE(email_open_at) as open_date,
                COUNT(*) as total_opened_emails,
                SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent_count,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_count
            ');
            $this->db->where('email_open_at >=', $start_date);
            $this->db->where('email_open_at <=', $end_date);
            $this->db->where('campaign_id', $campaignId);
            $this->db->where('status', 'sent');
            $this->db->group_by('DATE(email_open_at)');
            $this->db->order_by('open_date', 'ASC');

            $query_open = $this->db->get(db_prefix() . 'emailcampaign_queue');
            $open_data = [];
            foreach ($query_open->result() as $row) {
                $open_data[$row->open_date] = [
                    'opened_count' => $row->total_opened_emails,
                    'sent_count' => $row->sent_count,
                    'failed_count' => $row->failed_count
                ];
            }

            $data = [];
            $all_dates = array_merge(array_keys($sent_data), array_keys($open_data));
            $all_dates = array_unique($all_dates);

            foreach ($all_dates as $date) {
                $sent_count = isset($sent_data[$date]) ? $sent_data[$date] : ['sent_count' => 0, 'failed_count' => 0, 'total_sent_emails' => 0];
                $opened_count = isset($open_data[$date]) ? $open_data[$date] : ['opened_count' => 0, 'sent_count' => 0, 'failed_count' => 0];

                $sent_percentage = ($sent_count['total_sent_emails'] > 0) ? ($sent_count['sent_count'] / $sent_count['total_sent_emails']) * 100 : 0;
                $failed_percentage = ($sent_count['total_sent_emails'] > 0) ? ($sent_count['failed_count'] / $sent_count['total_sent_emails']) * 100 : 0;
                $opened_percentage = ($sent_count['total_sent_emails'] > 0) ? ($opened_count['opened_count'] / $sent_count['total_sent_emails']) * 100 : 0;

                $data[] = [
                    'date' => $date,
                    'total_sent_emails' => $sent_count['total_sent_emails'],
                    'sent_count' => $sent_count['sent_count'],
                    'sent_percentage' => round($sent_percentage, 2),
                    'failed_count' => $sent_count['failed_count'],
                    'failed_percentage' => round($failed_percentage, 2),
                    'opened_count' => $opened_count['opened_count'],
                    'opened_percentage' => round($opened_percentage, 2),
                    'not_opened_count' => $sent_count['total_sent_emails'] - $opened_count['opened_count'],
                    'not_opened_percentage' => round(100 - $opened_percentage, 2)
                ];
            }

            return $data;
        } else {
            return [];
        }
    }

    public function get_email_campaign_status_count($campaignId)
    {
        $this->db->select('
            COUNT(*) as total_emails,
            SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) as sent_count,
            SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_count,
            ROUND(SUM(CASE WHEN status = "sent" THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as sent_percentage,
            ROUND(SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as failed_percentage
        ');
        $this->db->where('campaign_id', $campaignId);
        $query = $this->db->get('tblemailcampaign_queue');

        $result = $query->row();

        if ($result) {
            return [
                'sent_count' => $result->sent_count,
                'failed_count' => $result->failed_count,
                'sent_percentage' => $result->sent_percentage,
                'failed_percentage' => $result->failed_percentage
            ];
        }
        return null;
    }

    public function get_email_campaign_open_status_count($campaignId)
    {
        $this->db->select('
            COUNT(*) as total_emails,
            SUM(CASE WHEN email_open_at IS NOT NULL THEN 1 ELSE 0 END) as opened_count,
            SUM(CASE WHEN email_open_at IS NULL THEN 1 ELSE 0 END) as not_opened_count,
            ROUND(SUM(CASE WHEN email_open_at IS NOT NULL THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as opened_percentage,
            ROUND(SUM(CASE WHEN email_open_at IS NULL THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as not_opened_percentage
        ');
        $this->db->where('status', 'sent');
        $this->db->where('campaign_id', $campaignId);
        $query = $this->db->get('tblemailcampaign_queue');

        $result = $query->row();

        if ($result) {
            return [
                'total_emails' => $result->total_emails,
                'opened_count' => $result->opened_count,
                'not_opened_count' => $result->not_opened_count,
                'opened_percentage' => $result->opened_percentage,
                'not_opened_percentage' => $result->not_opened_percentage
            ];
        }

        return null;
    }

    public function get_email_campaign_stats_by_sender($campaignId)
    {
        $sql = "
            SELECT
                SUM(CASE WHEN q.status = 'failed' THEN 1 ELSE 0 END) AS failed_count,
                SUM(CASE WHEN q.status = 'sent' THEN 1 ELSE 0 END) AS sent_count,
                CASE
                    WHEN q.send_from = 'staff' THEN s.webmail_email
                    ELSE e.email
                END AS email
            FROM
                " . db_prefix() . "emailcampaign_queue q
            LEFT JOIN
                " . db_prefix() . "staff s ON q.mail_send_from_id = s.staffid AND q.send_from = 'staff'
            LEFT JOIN
                " . db_prefix() . "emailcampaign_emails e ON q.mail_send_from_id = e.id AND q.send_from != 'staff'
            WHERE campaign_id = " . $campaignId . "
            GROUP BY
                q.mail_send_from_id, q.send_from
        ";

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function get_email_all_campaign_stats()
    {
        $where = "";
        if (!has_permission('email_campaigns', '', 'view')) {
            if (manager_employee_data_access_permission_check("email_campaigns")) {
                $where = "created_by IN (" . get_manager_assigned_staff_ids("", true) . ")";
            } else {
                $where = "created_by = " . get_staff_user_id();
            }
        }

        $statuses = [
            'Paused' => 0,
            'Stopped' => 0,
            'Completed' => 0,
            'In Queue' => 0,
            'In Progress' => 0,
            'Scheduled' => 0,
        ];
        $this->db->select('status, COUNT(*) as count');
        $this->db->from(db_prefix() . 'emailcampaign');
        $this->db->where_in('status', array_keys($statuses));
        if (!empty($where)) {
            $this->db->where($where);
        }
        $this->db->group_by('status');

        $query = $this->db->get();
        $result = $query->result_array();

        foreach ($result as $row) {
            $statuses[$row['status']] = (int) $row['count'];
        }

        $statuses['Total'] = array_sum($statuses);

        return $statuses;
    }

    public function get_email_campaign_send_from($campaign_id)
    {
        $this->db->distinct();
        $this->db->select('send_from, mail_send_from_id');
        $this->db->from(db_prefix() . 'emailcampaign_queue');
        $this->db->where('campaign_id', $campaign_id);
        $query = $this->db->get();
        return $query->result_array();
    }
}
