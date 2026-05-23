<?php

class Vendors_model extends App_Model
{
    public function get_vendors($where, $isCountCheck = false)
    {
        $is_manager = manager_employee_data_access_permission_check("vendors");
        $view_permission = has_permission('vendors', '', 'view');
        $permissionCheck = ($view_permission || $is_manager);
        $staff_user_id = get_staff_user_id();
        $managerStaffIds = get_manager_assigned_staff_ids();
        $this->db->select('*, CalculateLeadPriority(lastcontact,reminderdate,status) AS lapselead');
        $this->db->from('tblleadsview');
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
            } else {
                $this->db->where("lastcontact >=", $fromDate);
                $this->db->where("lastcontact <=", $toDate);
            }
        }

        //search
        $search = $where['search']['value'];
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
        $this->db->order_by('ISNULL(reminderdate), reminderdate ASC');
        $this->db->order_by('lapselead', 'DESC');
        $this->db->order_by('dateadded', 'DESC');
        if ($filter != "deleted") {
            $this->db->where('isDeleted', 'false');
        }
        $this->db->where('is_vendor','1');
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

    public function get_all_vendors()
    {
        $this->db->select('id as #, name as Name, company as Company, email as Email, phonenumber as `Phone Number`, tags as Products, DATE_FORMAT(reminderdate, "%d/%m/%Y %h:%i %p") AS `Follow up date`, CONCAT(assigned_firstname, " ", assigned_lastname) AS Assigned, status_name as Status, source_name as Source, DATE_FORMAT(lastcontact, "%d/%m/%Y %h:%i %p") AS `Last Contact`, DATE_FORMAT(dateadded, "%d/%m/%Y %h:%i %p") AS Created, short_name as Country, state as State, city as City, DATE_FORMAT(datedeleted, "%d/%m/%Y %h:%i %p") AS `Deleted Date`, deletedBy AS `Deleted By`');
        $this->db->from('tblfilteredleads');
        $this->db->where('is_vendor', '1');
        $this->db->order_by('lapselead', 'DESC');
        $this->db->order_by('reminderdate', 'ASC');
        $this->db->order_by('dateadded', 'DESC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function staff_can_access_lead($id, $staff_id = '')
    {
        $staff_id = $staff_id == '' ? get_staff_user_id() : $staff_id;
        if (has_permission('vendors', $staff_id, 'view') || has_permission('vendors', $staff_id, 'view_own')) {
            return true;
        }
        if (total_rows(db_prefix() . 'leads', 'id="' . $id . '" AND (assigned=' . $staff_id . ' OR is_public=1 OR addedfrom=' . $staff_id . ')') > 0) {
            return true;
        }
        return false;
    }

    public function create_quotation_form($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'vendor_quoation_forms', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New Vendor Quotation Form Created [Quotation Form ID: ' . $insert_id . ']');
        }
        return $insert_id;
    }

    public function create_quotation_form_items($data, $isCustomerSubmit = false)
    {
        if ($isCustomerSubmit) {
            $data['created_by'] = "vendor";
        } else {
            $data['created_by'] = get_staff_user_id();
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'vendor_quoation_form_items', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    public function get_quotation_forms_by_lead_id($lead_id)
    {
        $this->db->where('lead_id', $lead_id);
        $this->db->where('deleted_at IS NULL');
        return $this->db->get(db_prefix() . 'vendor_quoation_forms')->result_array();
    }

    public function get_quotation_forms_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'vendor_quoation_forms')->row_array();
    }

    public function get_quotation_forms_by_key($key)
    {
        $this->db->where('formkey', $key);
        return $this->db->get(db_prefix() . 'vendor_quoation_forms')->row_array();
    }

    public function get_quotation_forms_items_by_form_id($id)
    {
        $this->db->where('vendor_quotation_form_id', $id);
        $this->db->order_by('id', 'asc');
        return $this->db->get(db_prefix() . 'vendor_quoation_form_items')->result_array();
    }

    public function get_quotation_forms_items_by_id($id)
    {
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'vendor_quoation_form_items')->row_array();
    }

    public function update_quotation_form($data, $id, $isDelete = false, $isCustomerSubmit = false)
    {
        $this->db->where('id', $id);
        if (!$isCustomerSubmit) {
            if ($isDelete) {
                $data['deleted_at'] = date('Y-m-d H:i:s');
                $data['deleted_by'] = get_staff_full_name();
            } else {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['updated_by'] = get_staff_user_id();
            }
        } else {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['updated_by'] = "vendor";
        }
        $this->db->update(db_prefix() . 'vendor_quoation_forms', $data);
        if ($this->db->affected_rows() > 0) {
            if (!$isCustomerSubmit) {
                if ($isDelete) {
                    log_activity('Vendor Quotation form Deleted [Form ID: ' . $id . ']');
                } else {
                    log_activity('Vendor Quotation form Updated [Form ID: ' . $id . ']');
                }
            }
            return true;
        }
        return false;
    }

    public function update_quotation_form_items($data, $id, $isCustomerSubmit = false)
    {
        $this->db->where('id', $id);
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($isCustomerSubmit) {
            $data['updated_by'] = "vendor";
        } else {
            $data['updated_by'] = get_staff_user_id();
        }
        $this->db->update(db_prefix() . 'vendor_quoation_form_items', $data);
        if ($this->db->affected_rows() > 0) {
            $this->update_quotation_form([], $data['update_quotation_form']);
            return true;
        }
        return false;
    }

    public function delete_quotation_form_items($id, $isCustomerSubmit = false)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'vendor_quoation_form_items');
        if ($this->db->affected_rows() > 0) {
            if (!$isCustomerSubmit) {
                log_activity('Vendor Quotation Form Item Deleted [ID: ' . $id . ']');
            }
            return true;
        }
        return false;
    }
}
