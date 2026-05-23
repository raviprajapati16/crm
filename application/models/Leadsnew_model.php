<?php

class Leadsnew_model extends App_Model
{
    public function get_leads($where, $isCountCheck = false)
    {
        $is_manager = manager_employee_data_access_permission_check("leads");
        $view_permission = has_permission('leads', '', 'view');
        $permissionCheck = ($view_permission || $is_manager);
        $staff_user_id = get_staff_user_id();
        $managerStaffIds = get_manager_assigned_staff_ids();
        $this->db->select('*, CalculateLeadPriority(lastcontact,reminderdate,status) AS lapselead');
        $this->db->from(db_prefix() . 'leadsview');
        $filter = $where['customFilter'];
        if (!empty($filter)) {
            if ($filter == 'lost') {
                $this->db->where("lost", 1);
            } elseif ($filter == 'junk') {
                $this->db->where("junk", 1);
            } elseif ($filter == 'not_assigned') {
                $this->db->where("assigned", 0);
            } elseif ($filter == 'contacted_today') {
                $this->db->like("lastcontact", date('Y-m-d'), 'both');
            } elseif ($filter == 'created_today') {
                $this->db->like("dateadded", date('Y-m-d'), 'both');
            } elseif ($filter == 'public') {
                $this->db->where("is_public", 1);
            } elseif ($filter == 'today_leads') {
                $this->db->like("reminderdate", date('Y-m-d'), 'both');
            } elseif ($filter == "lapsed_lead") {
                $this->db->where("(reminderdate < NOW())");
            } elseif ($filter == "deleted") {
                $this->db->where("isDeleted", 'true');
            }
            if ($filter != 'lost' && $filter != 'junk') {
                $this->db->where("lost", 0);
                $this->db->where("junk", 0);
            }
        } else {
            $this->db->where("lost", 0);
            $this->db->where("junk", 0);
        }
        // if (empty($filter) || $filter != "lapsed_lead") {
        //    $this->db->where("reminderdate >= DATE_SUB(NOW(), INTERVAL 30 DAY) OR reminderdate IS NULL");
        // }
        if (empty($filter) && empty($where['followUpdateFilter'])) {
            // $this->db->where("(reminderdate >= DATE_SUB(NOW(), INTERVAL 30 DAY) OR reminderdate IS NULL)");
        }
        if (empty($filter) || ($filter != 'lost' && $filter != 'junk')) {
            $this->db->where("lost", 0);
            $this->db->where("junk", 0);
        }

        if ($permissionCheck) {
            if (isset($where['assigneFilter']) && !empty($where['assigneFilter'])) {
                $this->db->where("assigned", $where['assigneFilter']);
            } else if (!$view_permission && $is_manager) {
                $this->db->where_in("assigned", $managerStaffIds);
            }
        }

        if (isset($where['statusFilter']) && !empty($where['statusFilter']) && ($filter != 'lost' && $filter != 'junk')) {
            $this->db->where_in("status_name", $where['statusFilter']);
            if (count($where['statusFilter']) == 1 && in_array("NOT INTRESTED", $where['statusFilter']) && !empty($where['fromStatusToNotInterestedFilter'])) {
                $this->db->join(
                    "(SELECT leadid, additional_data, date
                      FROM (
                          SELECT leadid, additional_data, date,
                                 ROW_NUMBER() OVER (PARTITION BY leadid ORDER BY date DESC) AS rn
                          FROM " . db_prefix() . "lead_activity_log
                          WHERE description = 'not_lead_activity_status_updated'
                      ) AS logs
                      WHERE rn = 1
                    ) AS latest_logs",
                    "leadsview.id = latest_logs.leadid"
                );
                $this->db->like("latest_logs.additional_data", $where['fromStatusToNotInterestedFilter'], 'both');
            }
        }
        if (isset($where['countryFilter']) && !empty($where['countryFilter'])) {
            $this->db->where_in("short_name", $where['countryFilter']);
        }
        if (isset($where['stateFilter']) && !empty($where['stateFilter'])) {
            $this->db->where_in("state", $where['stateFilter']);
        }
        if (isset($where['cityFilter']) && !empty($where['cityFilter'])) {
            $this->db->where_in("city", $where['cityFilter']);
        }
        if (isset($where['sourceFilter']) && !empty($where['sourceFilter'])) {
            $this->db->where("source_name", $where['sourceFilter']);
        }
        if (!$permissionCheck) {
            $this->db->group_start();
            $this->db->where('assigned', $staff_user_id);
            // $this->db->or_where('addedfrom', $staff_user_id);
            $this->db->or_where('is_public', 1);
            $this->db->group_end();
        }
        if (isset($where['followUpdateFilter']) && !empty($where['followUpdateFilter'])) {
            $followupdate = date('Y-m-d', strtotime(implode('-', array_reverse(explode('-', $where['followUpdateFilter'])))));
            $this->db->where("DATE(reminderdate)", $followupdate);
        }
        if (isset($where['productFilter']) && !empty($where['productFilter'])) {
            $productFilter = $where['productFilter'];
            if ($productFilter == "No Product") {
                $this->db->where("(tags = '' OR tags IS NULL)");
            } else {
                $productFilter = str_replace("Requirement for", "", $productFilter);;
                $this->db->like("tags", "$productFilter", 'both');
            }
        }
        if (isset($where['dateTypeFilter']) && !empty($where['dateTypeFilter']) && isset($where['fromDate']) && !empty($where['fromDate'])) {
            $fromDate = date('Y-m-d 00:00:00', strtotime(implode('-', array_reverse(explode('-', $where['fromDate'])))));
            $toDate = "";
            if (isset($where['to_date']) && !empty($where['to_date'])) {
                $toDate = date('Y-m-d 23:59:59', strtotime(implode('-', array_reverse(explode('-', $where['to_date'])))));
            } else {
                $toDate = date('Y-m-d 23:59:59');
            }

            if ($where['dateTypeFilter'] == "dateadded") {
                $this->db->where("dateadded >=", $fromDate);
                $this->db->where("dateadded <=", $toDate);
            } else if ($where['dateTypeFilter'] == "lastcontact") {
                $this->db->where("lastcontact >=", $fromDate);
                $this->db->where("lastcontact <=", $toDate);
            } else if (in_array($where['dateTypeFilter'], ["Call", "Face To Face", "Online Meeting", "Plant Visit"])) {
                $this->db->join(
                    "(SELECT rel_id, MAX(action_date) as latest_action_date
                                  FROM " . db_prefix() . "reminders
                                  WHERE rel_type = 'lead' AND reminder_action = '" . $where['dateTypeFilter'] . "'
                                  AND status != 'Pending' AND deleted_at IS NULL
                                  GROUP BY rel_id) as latest_calls",
                    "leadsview.id = latest_calls.rel_id",
                    "left"
                );
                $this->db->where("latest_calls.latest_action_date >=", $fromDate);
                $this->db->where("latest_calls.latest_action_date <=", $toDate);
            } else if ($where['dateTypeFilter'] == "Inquiry Forms") {
                $this->db->join(
                    "(SELECT lead_id, MAX(created_at) as form_created,
                              CASE
                                  WHEN form_status IS NULL AND is_whatsapp_send = '1' AND is_email_send = '1' THEN 'send'
                                  WHEN form_status IS NULL AND (is_whatsapp_send = '1' OR is_email_send = '1') THEN 'send'
                                  ELSE form_status
                              END as form_status
                     FROM " . db_prefix() . "lead_inquiry_forms
                     WHERE deleteddate IS NULL
                     GROUP BY lead_id) as inquiry_forms",
                    "leadsview.id = inquiry_forms.lead_id"
                );
                $this->db->where("inquiry_forms.form_created >=", $fromDate);
                $this->db->where("inquiry_forms.form_created <=", $toDate);
            }
        }

        //search
        $search = trim($where['search']['value']);
        if (!empty($search)) {
            $this->db->where("(id LIKE '%$search%' OR
                              name LIKE '%$search%' OR
                              company LIKE '%$search%' OR
                              email LIKE '%$search%' OR
                              phonenumber LIKE '%$search%' OR
                              city LIKE '%$search%' OR
                              state LIKE '%$search%' OR
                              tags LIKE '%$search%' OR
                              assigned_firstname LIKE '%$search%' OR
                              short_name LIKE '%$search%' OR
                              status_name LIKE '%$search%' OR
                              source_name LIKE '%$search%' OR
                              lastcontact LIKE '%$search%' OR
                              dateadded LIKE '%$search%' OR
                              assigned_lastname LIKE '%$search%' OR
                              zip LIKE '%$search%')");
        }
        if (!$isCountCheck && $where['length'] != "-1") {
            $this->db->limit($where['length'], $where['start']);
        }

        if (isset($where['dateTypeFilter']) && !empty($where['dateTypeFilter']) && $where['dateTypeFilter'] == "Inquiry Forms") {
            $this->db->order_by("CASE WHEN form_status IS NULL THEN 1 ELSE 0 END", 'ASC', false);
            $this->db->order_by("FIELD(form_status, 'approved', 'pending', 'not-approved', 'send')", '', false);
        } else {
            $this->db->order_by('ISNULL(reminderdate), reminderdate ASC');
            $this->db->order_by('lapselead', 'DESC');
            $this->db->order_by('dateadded', 'DESC');
        }
        if ($filter != "deleted") {
            $this->db->where('isDeleted', 'false');
        }
        $this->db->where('is_vendor', '0');
        if ($isCountCheck) {
            return $this->db->count_all_results();
        } else {
            $query = $this->db->get();
            return $query->result();
        }
    }

    public function get_tags($id = false)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get(db_prefix() . 'tags')->row();
        }
        $this->db->order_by('name', 'asc');
        return $this->db->get(db_prefix() . 'tags')->result_array();
    }

    public function get_all_leads()
    {
        $this->db->select('id as #, name as Name, company as Company, email as Email, phonenumber as `Phone Number`, tags as Products, DATE_FORMAT(reminderdate, "%d/%m/%Y %h:%i %p") AS `Follow up date`, CONCAT(assigned_firstname, " ", assigned_lastname) AS Assigned, status_name as Status, source_name as Source, DATE_FORMAT(lastcontact, "%d/%m/%Y %h:%i %p") AS `Last Contact`, DATE_FORMAT(dateadded, "%d/%m/%Y %h:%i %p") AS Created, short_name as Country, state as State, city as City, DATE_FORMAT(datedeleted, "%d/%m/%Y %h:%i %p") AS `Deleted Date`, deletedBy AS `Deleted By`');
        $this->db->from('tblfilteredleads');
        $this->db->where('is_vendor', '0');
        $this->db->order_by('lapselead', 'DESC');
        $this->db->order_by('reminderdate', 'ASC');
        $this->db->order_by('dateadded', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_main_group()
    {
        return $this->db->get(db_prefix() . 'items_groups')->result_array();
    }

    public function get_sub_group()
    {
        $this->db->where('deleted_at IS NULL');
        return $this->db->get(db_prefix() . 'sub_groups')->result_array();
    }
    public function get_countries()
    {
        $this->db->select('country');
        $this->db->distinct();
        $this->db->from(db_prefix() . 'cities');
        $this->db->order_by('country', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }
    public function get_states($where)
    {
        $this->db->select('state');
        $this->db->distinct();
        $this->db->from(db_prefix() . 'cities');
        $this->db->where($where);
        $this->db->order_by('state', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }
    public function get_cities($where)
    {
        $this->db->select('city');
        $this->db->distinct();
        $this->db->from(db_prefix() . 'cities');
        $this->db->where($where);
        $this->db->order_by('city', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }
    public function get_lead_count_for_map($where)
    {
        $this->db->reset_query();
        if ($where['type'] == "all_country") {
            $this->db->select('cities.country AS name, COUNT(leads.id) AS count, cities.country_latitude as latitude, cities.country_longitude as longitude');
            $this->db->from(db_prefix() . 'leads leads');
            $this->db->join(db_prefix() . 'countries countries', 'leads.country = countries.country_id');
            $this->db->join('(SELECT country, country_longitude, country_latitude FROM ' . db_prefix() . 'cities GROUP BY country, country_longitude, country_latitude) cities', 'countries.short_name = cities.country');
            $this->db->group_by(array('cities.country', 'cities.country_latitude', 'cities.country_longitude'));
        } else if ($where['type'] == "all_state") {
            $this->db->select('cities.state AS name, COUNT(leads.id) AS count, cities.state_latitude as latitude, cities.state_longitude as longitude');
            $this->db->from(db_prefix() . 'leads leads');
            $this->db->join(db_prefix() . 'countries countries', 'leads.country = countries.country_id');
            $this->db->join('(SELECT state, country, state_longitude, state_latitude FROM ' . db_prefix() . 'cities GROUP BY state, country,state_longitude, state_latitude) cities', 'leads.state = cities.state AND countries.short_name = cities.country');
            $this->db->like("countries.short_name", $where['country'], 'both');
            $this->db->group_by(array('cities.state', 'cities.state_latitude', 'cities.state_longitude'));
        } else if ($where['type'] == "all_city" || $where['type'] == "single_city") {
            $this->db->select('cities.city AS name, COUNT(leads.id) AS count, cities.city_latitude as latitude, cities.city_longitude as longitude');
            $this->db->from(db_prefix() . 'leads leads');
            $this->db->join(db_prefix() . 'countries countries', 'leads.country = countries.country_id');
            $this->db->join('(SELECT city,state,country,city_longitude, city_latitude FROM ' . db_prefix() . 'cities GROUP BY city,city_longitude, city_latitude) cities', 'leads.city = cities.city AND leads.state = cities.state AND countries.short_name = cities.country');
            $this->db->like("countries.short_name", $where['country'], 'both');
            $this->db->like("leads.state", $where['state'], 'both');
            if ($where['type'] == "single_city") {
                $this->db->like("leads.city", $where['city'], 'both');
            }
            $this->db->group_by(array('cities.city', 'cities.city_latitude', 'cities.city_longitude'));
        }
        if (isset($where['type']) && !empty($where['type'])) {
            if (!empty($where['product'])) {
                $this->db->join('(SELECT tgbls.rel_id, GROUP_CONCAT(tgs.name SEPARATOR ",") as tags
                  FROM ' . db_prefix() . 'taggables tgbls
                  JOIN ' . db_prefix() . 'tags tgs ON tgbls.tag_id = tgs.id
                  WHERE tgbls.rel_type="lead"
                  GROUP BY tgbls.rel_id) as lead_tags', 'lead_tags.rel_id = leads.id', 'left');
                $this->db->where('lead_tags.tags LIKE', '%' . $where['product'] . '%');
            }
            $this->db->where('leads.isDeleted', 'false');
            $this->db->where('leads.is_vendor', '0');
            $this->db->where('leads.lost', '0');
            $this->db->where('leads.junk', '0');
            $query = $this->db->get();
            return $query->result_array();
        }
    }
}
