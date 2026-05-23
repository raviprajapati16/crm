<?php

class Leadsdashboard_model extends App_Model
{
    public function get_received_leads_data($data)
    {
        $startDate = $data['startdate'];
        $endDate = $data['enddate'];
        $assignee = isset($data['assignee']) ? $data['assignee'] : null;

        $sql = "
            SELECT
                DATE_FORMAT(d.date, '%b %d, %Y') as date,
                COALESCE(COUNT(l.id), 0) as leads,
                s.name as source_name
            FROM
                (SELECT DISTINCT DATE(dateadded) as date
                FROM " . db_prefix() . "leads
                WHERE DATE(dateadded) BETWEEN ? AND ?) d
            CROSS JOIN
                " . db_prefix() . "leads_sources s
            LEFT JOIN
                " . db_prefix() . "leads l ON l.source = s.id
                AND DATE(l.dateadded) = d.date
                AND l.is_vendor = '0'
                AND l.isDeleted = 'false'
        ";

        $params = [$startDate, $endDate];
        if ($assignee !== null && $assignee !== '') {
            $sql .= " AND l.assigned IN $assignee";
        }

        $sql .= "
            GROUP BY
                s.name, d.date
            ORDER BY
                d.date
            ";

        $query = $this->db->query($sql, $params);
        return $query->result_array();
    }

    public function get_leads_view_data($data)
    {
        $startDate = $data['startdate'];
        $endDate = $data['enddate'];
        $assignee = isset($data['assignee']) ? $data['assignee'] : null;
        $viewType = ($data['viewType'] == "leads-updated") ? "updated" : "view";
        $desc = ($viewType == "updated") ? "Updated Lead" : "View Lead";

        $sql = "
            SELECT
                DATE_FORMAT(date, '%b %d, %Y') as date,
                COUNT(DISTINCT leadid) as leads
            FROM
                (
                    SELECT DISTINCT
                        lal.leadid,
                        DATE(lal.date) as date
                    FROM
                        " . db_prefix() . "lead_activity_log lal
                    JOIN
                        " . db_prefix() . "leads l
                    ON
                        l.id = lal.leadid
                    WHERE
                        DATE(lal.date) BETWEEN ? AND ?
                        AND l.is_vendor = '0'
                        AND l.isDeleted = 'false'
                        AND lal.description LIKE ?
                    " . ($assignee !== null && $assignee !== '' ? "AND lal.staffid IN $assignee" : "") . "
                ) subquery
            GROUP BY
                date
            ORDER BY
                date
        ";

        $params = [$startDate, $endDate, "%$desc%"];

        $query = $this->db->query($sql, $params);
        return $query->result_array();
    }

    public function get_leads_send_data($data)
    {
        $startDate = $data['startdate'];
        $endDate = $data['enddate'];
        $assignee = isset($data['assignee']) ? $data['assignee'] : null;
        if ($data['leadSendType'] == "email") {
            $sql = "
                SELECT
                    DATE_FORMAT(DATE(t1.date), '%b %d, %Y') as date,
                    SUM(CASE WHEN t1.opened = 1 THEN 1 ELSE 0 END) as emails_opened,
                    COUNT(t1.id) - SUM(CASE WHEN t1.opened = 1 THEN 1 ELSE 0 END) as emails_sent
                FROM
                    " . db_prefix() . "tracked_mails t1
                LEFT JOIN
                    " . db_prefix() . "leads t2
                ON
                    t1.rel_id = t2.id AND t1.rel_type = 'lead'
                WHERE
                    DATE(t1.date) BETWEEN ? AND ?
                    AND t1.rel_type = 'lead'
                    AND t2.is_vendor = '0'
                    AND t2.isDeleted = 'false'";

            $params = [$startDate, $endDate];
            if ($assignee !== null && $assignee !== '') {
                $sql .= " AND t1.staffid IN $assignee";
            }

            $sql .= "
            GROUP BY
                DATE(t1.date)
            ORDER BY
                DATE(t1.date)
            ";
        } else {
            $sql = "SELECT
            DATE_FORMAT(DATE(date), '%b %d, %Y') as date,
            COUNT(DISTINCT staffid, leadid, DATE(date)) as whatsapp_shared,
            staffid,
            leadid
        FROM
            `tbllead_activity_log`
        WHERE
            DATE(date) BETWEEN ? AND ?
            AND `description` LIKE '%whatsapp%'";

            $params = [$startDate, $endDate];

            if ($assignee !== null && $assignee !== '') {
                $sql .= " AND staffid IN $assignee";
            }

            $sql .= "
        GROUP BY
            DATE(date)
        ORDER BY
            date DESC";
        }
        $query = $this->db->query($sql, $params);
        return $query->result_array();
    }

    public function get_assignee_data($data)
    {
        $startDate = $data['startdate'];
        $endDate = $data['enddate'];
        $assignee = isset($data['assignee']) ? $data['assignee'] : null;

        $sql = "
            SELECT
                CONCAT(st.firstname, ' ', st.lastname) as assignee_name,
                COALESCE(COUNT(l.id), 0) as leads
            FROM
                " . db_prefix() . "leads l
            LEFT JOIN
                " . db_prefix() . "staff st ON l.assigned = st.staffid
            WHERE
                DATE(l.dateadded) BETWEEN ? AND ?
                AND l.is_vendor = '0'
                AND l.isDeleted = 'false'
                AND l.assigned != 0
        ";

        $params = [$startDate, $endDate];
        if ($assignee !== null && $assignee !== '') {
            $sql .= " AND l.assigned IN $assignee";
        }

        $sql .= "
            GROUP BY
                assignee_name
            ORDER BY
                assignee_name
        ";

        $query = $this->db->query($sql, $params);
        return $query->result_array();
    }

    public function get_customer_conversion_data($data)
    {
        $startDate = $data['startdate'];
        $endDate = $data['enddate'];
        $assignee = isset($data['assignee']) ? $data['assignee'] : null;

        $sql = "
            SELECT
                DATE_FORMAT(c.datecreated, '%b %d, %Y') as date,
                COALESCE(COUNT(c.userid), 0) as clients
            FROM
                " . db_prefix() . "clients c
            JOIN
                " . db_prefix() . "leads l ON c.leadid = l.id
            WHERE
                DATE(c.datecreated) BETWEEN ? AND ?
                AND l.is_vendor = '0'
                AND l.isDeleted = 'false'
            ";

        $params = [$startDate, $endDate];
        if ($assignee !== null && $assignee !== '') {
            $sql .= " AND l.assigned IN $assignee";
        }

        $sql .= "
            GROUP BY
                DATE(c.datecreated)
            ORDER BY
                DATE(c.datecreated)
            ";

        $query = $this->db->query($sql, $params);
        return $query->result_array();
    }

    public function get_leads_followup_data($data)
    {
        $startDate = $data['startdate'];
        $endDate = $data['enddate'];
        $assignee = isset($data['assignee']) ? $data['assignee'] : null;

        $selectSql = "";
        if ($data['followupType'] == "Call") {
            $selectSql .= " SUM(CASE WHEN r.status = 'Attend' THEN 1 ELSE 0 END) as 'Attend',
                SUM(CASE WHEN r.status = 'Not Attend' THEN 1 ELSE 0 END) as 'Not Attend',
                SUM(CASE WHEN r.status = 'Declined' THEN 1 ELSE 0 END) as 'Declined',
                SUM(CASE WHEN r.status = 'Busy' THEN 1 ELSE 0 END) as 'Busy',
                SUM(CASE WHEN r.status = 'Not Reachable' THEN 1 ELSE 0 END) as 'Not Reachable' ";
        } else if ($data['followupType'] == "Online Meeting") {
            $selectSql .= " SUM(CASE WHEN r.status = 'Attend' THEN 1 ELSE 0 END) as 'Attend',
                SUM(CASE WHEN r.status = 'Not Attend' THEN 1 ELSE 0 END) as 'Not Attend' ";
        } else if ($data['followupType'] == "Plant Visit") {
            $selectSql .= " SUM(CASE WHEN r.status = 'Visited' THEN 1 ELSE 0 END) as 'Visited',
                SUM(CASE WHEN r.status = 'Not Visited' THEN 1 ELSE 0 END) as 'Not Visited' ";
        } else if ($data['followupType'] == "Face To Face") {
            $selectSql .= " SUM(CASE WHEN r.status = 'Present' THEN 1 ELSE 0 END) as 'Present',
                SUM(CASE WHEN r.status = 'Absent' THEN 1 ELSE 0 END) as 'Absent' ";
        }

        $sql = "
        SELECT
           DATE_FORMAT(DATE(r.action_date), '%b %d, %Y') as date,
           $selectSql
        FROM
            " . db_prefix() . "reminders r
        JOIN
                " . db_prefix() . "leads l
        ON l.id = r.rel_id AND r.rel_type = 'lead'
        WHERE
            DATE(r.action_date) BETWEEN ? AND ?
            AND r.rel_type = 'lead'
            AND l.is_vendor = '0'
            AND l.isDeleted = 'false'
        ";

        $sql .= " AND r.reminder_action = '" . $data['followupType'] . "' ";

        $params = [$startDate, $endDate];
        if ($assignee !== null && $assignee !== '') {
            $sql .= " AND r.staff IN $assignee";
        }

        $sql .= "
        GROUP BY
            DATE(r.action_date)
        ORDER BY
            DATE(r.action_date)
        ";

        $query = $this->db->query($sql, $params);
        return $query->result_array();
    }

    public function get_vendor_conversion_data($data)
    {
        $startDate = $data['startdate'];
        $endDate = $data['enddate'];
        $assignee = isset($data['assignee']) ? $data['assignee'] : null;
        $sql = "
            SELECT
                DATE_FORMAT(DATE(l.date), '%b %d, %Y') as date,
                COUNT(DISTINCT ld.id) as vendors
            FROM
                " . db_prefix() . "leads ld
            LEFT JOIN
                " . db_prefix() . "lead_activity_log l
            ON
                ld.id = l.leadid
                AND (l.description LIKE '%Convert to vendor%' OR l.description LIKE '%not_vendor_activity_created%')
            WHERE
                ld.isDeleted = 'false'
                AND ld.is_vendor = '1'
                AND DATE(l.date) BETWEEN ? AND ?
        ";
        $params = [$startDate, $endDate];
        if ($assignee !== null && $assignee !== '') {
            $sql .= " AND l.staffid IN $assignee";
        }
        $sql .= "
            GROUP BY
                DATE(l.date)
            ORDER BY
                DATE(l.date)
        ";
        $query = $this->db->query($sql, $params);
        return $query->result_array();
    }

    public function get_inquiry_form_data($data)
    {
        $startDate = $data['startdate'];
        $endDate = $data['enddate'];
        $assignee = isset($data['assignee']) ? $data['assignee'] : null;

        $sql = "
            SELECT
                DATE_FORMAT(DATE(f.created_at), '%b %d, %Y') as date,
                CASE
                    WHEN (f.form_status IS NULL OR f.form_status = '') AND (f.is_whatsapp_send = '0' AND f.is_email_send = '0') THEN 'draft'
                    WHEN (f.form_status IS NULL OR f.form_status = '') AND (f.is_whatsapp_send = '1' OR f.is_email_send = '1') THEN 'sent'
                    WHEN f.form_status IS NOT NULL AND f.form_status != '' THEN f.form_status
                    ELSE 'draft'
                END as form_status,
                COUNT(*) as count
            FROM
                " . db_prefix() . "lead_inquiry_forms f
            JOIN
                " . db_prefix() . "leads l
            ON
                l.id = f.lead_id
            WHERE
                DATE(f.created_at) BETWEEN ? AND ?
                AND f.deleteddate IS NULL
                AND l.is_vendor = '0'
                AND l.isDeleted = 'false'
        ";

        $params = [$startDate, $endDate];
        if ($assignee !== null && $assignee !== '') {
            $sql .= " AND f.created_by IN $assignee";
        }
        $sql .= "
            GROUP BY
                DATE(f.created_at),
                CASE
                    WHEN (f.form_status IS NULL OR f.form_status = '') AND (f.is_whatsapp_send = '0' AND f.is_email_send = '0') THEN 'draft'
                    WHEN (f.form_status IS NULL OR f.form_status = '') AND (f.is_whatsapp_send = '1' OR f.is_email_send = '1') THEN 'sent'
                    WHEN f.form_status IS NOT NULL AND f.form_status != '' THEN f.form_status
                    ELSE 'draft'
                END
            ORDER BY
                DATE(f.created_at)
        ";

        $query = $this->db->query($sql, $params);
        return $query->result_array();
    }

    public function get_vendor_form_data($data)
    {
        $startDate = $data['startdate'];
        $endDate = $data['enddate'];
        $assignee = isset($data['assignee']) ? $data['assignee'] : null;

        $sql = "
            SELECT
                DATE_FORMAT(DATE(f.created_at), '%b %d, %Y') as date,
                CASE
                    WHEN (f.form_status IS NULL OR f.form_status = '') AND (f.is_whatsapp_send = '0' AND f.is_email_send = '0') THEN 'draft'
                    WHEN (f.form_status IS NULL OR f.form_status = '') AND (f.is_whatsapp_send = '1' OR f.is_email_send = '1') THEN 'sent'
                    WHEN f.form_status IS NOT NULL AND f.form_status != '' THEN f.form_status
                    ELSE 'draft'
                END as form_status,
                COUNT(*) as count
            FROM
                " . db_prefix() . "vendor_quoation_forms f
            JOIN
                " . db_prefix() . "leads l
            ON
                l.id = f.lead_id
            WHERE
                DATE(f.created_at) BETWEEN ? AND ?
                AND l.is_vendor = '1'
                AND l.isDeleted = 'false'
                AND f.deleted_at IS NULL
        ";

        $params = [$startDate, $endDate];
        if ($assignee !== null && $assignee !== '') {
            $sql .= " AND f.created_by IN $assignee";
        }
        $sql .= "
            GROUP BY
                DATE(f.created_at),
                CASE
                    WHEN (f.form_status IS NULL OR f.form_status = '') AND (f.is_whatsapp_send = '0' AND f.is_email_send = '0') THEN 'draft'
                    WHEN (f.form_status IS NULL OR f.form_status = '') AND (f.is_whatsapp_send = '1' OR f.is_email_send = '1') THEN 'sent'
                    WHEN f.form_status IS NOT NULL AND f.form_status != '' THEN f.form_status
                    ELSE 'draft'
                END
            ORDER BY
                DATE(f.created_at)
        ";

        $query = $this->db->query($sql, $params);
        return $query->result_array();
    }

    public function get_proposal_data($data)
    {
        $startDate = $data['startdate'];
        $endDate = $data['enddate'];
        $assignee = isset($data['assignee']) ? $data['assignee'] : null;

        $sql = "
            SELECT
                DATE_FORMAT(DATE(p.datecreated), '%b %d, %Y') as date,
                COALESCE(SUM(p.status = 1), 0) as open,
                COALESCE(SUM(p.status = 2), 0) as declined,
                COALESCE(SUM(p.status = 3), 0) as accepted,
                COALESCE(SUM(p.status = 4), 0) as sent,
                COALESCE(SUM(p.status = 5), 0) as revised,
                COALESCE(SUM(p.status = 6), 0) as draft
            FROM
                " . db_prefix() . "proposals p
            WHERE
                DATE(p.datecreated) BETWEEN ? AND ?
                AND deleted_at IS NULL
        ";

        $params = [$startDate, $endDate];
        if ($assignee !== null && $assignee !== '') {
            $sql .= " AND p.assigned IN $assignee";
        }

        $sql .= "
            GROUP BY
                DATE(p.datecreated)
            ORDER BY
                DATE(p.datecreated)
        ";

        $query = $this->db->query($sql, $params);
        return $query->result_array();
    }

    public function get_contract_data($data)
    {
        $startDate = $data['startdate'];
        $endDate = $data['enddate'];
        $assignee = isset($data['assignee']) ? $data['assignee'] : null;

        $sql = "
            SELECT
                DATE_FORMAT(DATE(c.dateadded), '%b %d, %Y') as 'date',
                SUM(CASE WHEN c.contract_status = 'verified' THEN 1 ELSE 0 END) as 'verified',
                SUM(CASE WHEN c.contract_status = 'in review' THEN 1 ELSE 0 END) as 'in review',
                SUM(CASE WHEN c.contract_status = 'sent' THEN 1 ELSE 0 END) as 'sent',
                SUM(CASE WHEN c.contract_status = 'draft' THEN 1 ELSE 0 END) as 'draft'
            FROM
                " . db_prefix() . "contracts c
            WHERE
                DATE(c.dateadded) BETWEEN ? AND ?
                AND c.deleted_at IS NULL
        ";

        $params = [$startDate, $endDate];
        if ($assignee !== null && $assignee !== '') {
            $sql .= " AND c.addedfrom IN $assignee";
        }

        $sql .= "
            GROUP BY
                DATE(c.dateadded)
            ORDER BY
                DATE(c.dateadded)
        ";

        $query = $this->db->query($sql, $params);
        return $query->result_array();
    }

    public function get_leads_transfer_to_other_data($data)
    {
        $startDate = $data['startdate'];
        $endDate = $data['enddate'];
        if (isset($data['assignee']) && !empty($data['assignee'])) {
            $sql = "
                SELECT
                    COUNT(*) as leads,
                    SUBSTRING_INDEX(SUBSTRING_INDEX(al.additional_data, '>', -2), '<', 1) as name,
                    IFNULL(
                        NULLIF(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(al.additional_data, 'profile/', -1), '\"', 1), '\"', 1), ''),
                        '0'
                    ) as profile_id
                FROM
                    " . db_prefix() . "lead_activity_log al
                WHERE
                    DATE(al.date) BETWEEN ? AND ?
                    AND al.staffid IN " . $data['assignee'] . " AND al.staffid != 0
                    AND al.description LIKE '%not_lead_activity_assigned_to%'
                GROUP BY
                    name
                HAVING
                    profile_id NOT IN " . $data['assignee'] . "
                ORDER BY
                    name
            ";
            $params = [$startDate, $endDate];
            $query = $this->db->query($sql, $params);
            return $query->result_array();
        }
    }

    public function get_leads_transfer_to_self_data($data)
    {
        $startDate = $data['startdate'];
        $endDate = $data['enddate'];
        if (isset($data['assignee']) && !empty($data['assignee'])) {
            $sql = "
                SELECT
                    COUNT(*) as leads,
                    IFNULL(
                        NULLIF(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(additional_data, 'profile/', -1), '\"', 1), '\"', 1), ''),
                        '0'
                    ) as profile_id,
                    full_name as name
                FROM
                    " . db_prefix() . "lead_activity_log
                WHERE
                    DATE(date) BETWEEN ? AND ?
                    AND description LIKE '%not_lead_activity_assigned_to%'
                    AND staffid NOT IN " . $data['assignee'] . " AND staffid != 0
                GROUP BY
                    full_name, profile_id
                HAVING
                    profile_id IN  " . $data['assignee'] . "
                ORDER BY
                    name
                ";
            $params = [$startDate, $endDate];
            $query = $this->db->query($sql, $params);
            return $query->result_array();
        }
    }

    public function get_today_lead_followup_data($data)
    {
        $currentDate = date('Y-m-d');
        $assignee = isset($data['assignee']) ? $data['assignee'] : null;

        $selectSql = "
            CONCAT(s.firstname, ' ', s.lastname) as assignee_name,
            SUM(CASE WHEN r.status != 'Pending' AND r.status IS NOT NULL THEN 1 ELSE 0 END) as 'attend_count',
            SUM(CASE WHEN r.status = 'Pending' OR r.status IS NULL THEN 1 ELSE 0 END) as 'pending_count'
        ";

        $sql = "
            SELECT
                $selectSql
            FROM
                " . db_prefix() . "leads l
            LEFT JOIN
                " . db_prefix() . "reminders r ON r.id = (
                    SELECT r1.id
                    FROM " . db_prefix() . "reminders r1
                    WHERE r1.rel_type = 'lead'
                    AND r1.rel_id = l.id
                    AND DATE(r1.action_date) = ?
                    ORDER BY r1.id ASC
                    LIMIT 1
                )
            LEFT JOIN
                " . db_prefix() . "staff s
                    ON (r.staff IS NOT NULL AND r.staff = s.staffid)
                    OR (r.staff IS NULL AND l.assigned = s.staffid)
            WHERE
                l.is_vendor = '0'
                AND l.isDeleted = 'false'
                AND DATE(l.dateadded) = ?
        ";

        $params = [$currentDate, $currentDate];

        if ($assignee !== null && $assignee !== '') {
            $sql .= " AND (r.staff IN $assignee OR l.assigned IN $assignee)";
        }

        $sql .= "
            GROUP BY
                assignee_name
            ORDER BY
                DATE(l.dateadded)
        ";

        $query = $this->db->query($sql, $params);
        return $query->result_array();
    }

    public function get_today_lead_data($data)
    {
        $assignee = isset($data['assignee']) ? $data['assignee'] : null;
        $desc = ($data['todayLeadsType'] == "leads-updated") ? "Updated Lead" : "View Lead";
        $today = date('Y-m-d');
        $sql = "
            SELECT
                CONCAT(s.firstname, ' ', s.lastname) as assignee_name,
                COUNT(DISTINCT subquery.leadid) as leads
            FROM
                (
                    SELECT DISTINCT
                        lal.leadid,
                        lal.staffid
                    FROM
                        " . db_prefix() . "lead_activity_log lal
                    JOIN
                        " . db_prefix() . "leads l ON lal.leadid = l.id
                    WHERE
                        DATE(l.dateadded) = ?
                        AND DATE(lal.date) = ?
                        AND lal.description LIKE ?
                    " . ($assignee !== null && $assignee !== '' ? "AND lal.staffid IN $assignee" : "") . "
                ) subquery
            JOIN
                " . db_prefix() . "staff s ON subquery.staffid = s.staffid
            GROUP BY
                assignee_name
            ORDER BY
                leads
        ";
        $params = [$today, $today, "%$desc%"];
        $query = $this->db->query($sql, $params);
        return $query->result_array();
    }


    public function get_lead_attend_data($data)
    {
        $startDate = $data['startdate'];
        $endDate = $data['enddate'];
        $assignee = isset($data['assignee']) ? $data['assignee'] : null;
        $sql = "SELECT
                leads.id,
                leads.assigned,
                CONCAT(staff.firstname, ' ', staff.lastname) as assignee_name,
                COALESCE(
                    TIMESTAMPDIFF(
                        SECOND,
                        assign_log.max_assign_date,
                        view_log.min_view_date
                    ),
                    0
                ) AS attend_time
            FROM
                " . db_prefix() . "leads AS leads
            LEFT JOIN
                " . db_prefix() . "staff AS staff ON staff.staffid = leads.assigned
            LEFT JOIN (
                SELECT
                    leadid,
                    MAX(date) as max_assign_date
                FROM
                    " . db_prefix() . "lead_activity_log
                WHERE
                    description = 'not_lead_activity_assigned_to'
                GROUP BY
                    leadid
            ) AS assign_log ON assign_log.leadid = leads.id
            LEFT JOIN (
                SELECT
                    leadid,
                    staffid,
                    MIN(date) as min_view_date
                FROM
                    " . db_prefix() . "lead_activity_log
                WHERE
                    description LIKE '%View Lead%'
                    OR description LIKE '%Edit Lead%'
                    OR description LIKE '%Updated Lead%'
                GROUP BY
                    leadid, staffid
            ) AS view_log ON view_log.leadid = leads.id AND view_log.staffid = leads.assigned
            WHERE
                DATE(leads.dateadded) >= ?
                AND DATE(leads.dateadded) <= ?
                AND leads.is_vendor = '0'
                AND leads.isDeleted = 'false'
                AND assign_log.max_assign_date < view_log.min_view_date ";
        $params = [$startDate, $endDate];
        if (!empty($assignee)) {
            $sql .= " AND leads.assigned IN $assignee";
        }
        $query = $this->db->query($sql, $params);
        return $query->result_array();
    }

    public function get_lead_followup_duration_data($data)
    {
        $startDate = $data['startdate'];
        $endDate = $data['enddate'];
        $assignee = isset($data['assignee']) ? $data['assignee'] : null;

        $sql = "SELECT
            CONCAT(staff.firstname, ' ', staff.lastname) as assignee_name,
            SUM(CASE WHEN reminders.reminder_action = 'Call' THEN TIME_TO_SEC(reminders.duration) ELSE 0 END) AS call_duration,
            SUM(CASE WHEN reminders.reminder_action = 'Online Meeting' THEN TIME_TO_SEC(reminders.duration) ELSE 0 END) AS meeting_duration
        FROM
            " . db_prefix() . "reminders AS reminders
        LEFT JOIN
            " . db_prefix() . "staff AS staff ON staff.staffid = reminders.staff
        WHERE
            reminders.reminder_action IN ('Call', 'Online Meeting')
            AND reminders.status = 'Attend'
            AND DATE(reminders.date) >= ?
            AND DATE(reminders.date) <= ?";

        $params = [$startDate, $endDate];
        if (!empty($assignee)) {
            $sql .= " AND reminders.staff IN $assignee";
        }

        $sql .= " GROUP BY assignee_name";

        $query = $this->db->query($sql, $params);
        return $query->result_array();
    }

    public function get_lapslead_data($data)
    {
        $startDate = $data['startdate'];
        $endDate = $data['enddate'];
        $assignee = isset($data['assignee']) ? $data['assignee'] : null;

        $sql = "SELECT
                CONCAT(staff.firstname, ' ', staff.lastname) as assignee_name,
                count(leads.id) as count
            FROM
                " . db_prefix() . "leads AS leads
            JOIN (
                SELECT * FROM " . db_prefix() . "reminders AS r1
                WHERE r1.rel_type = 'lead'
                AND r1.date = (
                    SELECT MAX(r2.date)
                    FROM " . db_prefix() . "reminders AS r2
                    WHERE
                    r2.rel_id = r1.rel_id
                    AND r2.rel_type = 'lead'
                )
            ) AS latest_reminders ON leads.id = latest_reminders.rel_id
            JOIN
                " . db_prefix() . "staff AS staff ON staff.staffid = leads.assigned
            WHERE
                leads.isDeleted = 'false'
                AND leads.is_vendor = '0'
                AND leads.lost = 0
                AND leads.junk = 0
                AND leads.status NOT IN (1,10,42)
                AND latest_reminders.date < NOW()
                AND DATE(leads.dateadded) >= ?
                AND DATE(leads.dateadded) <= ?";

        $params = [$startDate, $endDate];
        if (!empty($assignee)) {
            $sql .= " AND leads.assigned IN $assignee";
        }
        $sql .= " GROUP BY assignee_name";
        $query = $this->db->query($sql, $params);
        return $query->result_array();
    }

    public function get_avg_value_data($data) {}
}
