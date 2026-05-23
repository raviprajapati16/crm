<?php

class Plant_visit_form_model extends App_Model
{

    public function __construct()
    {
        $this->load->model('leads_model');
    }

    public function get($where)
    {
        $this->db->select('*');
        $this->db->where($where);
        $this->db->where('deleted_at IS NULL');
        return $this->db->get(db_prefix() . 'plant_visit_forms')->result_array();
    }

    public function get_single($where)
    {
        $this->db->select('*');
        $this->db->where($where);
        $this->db->where('deleted_at IS NULL');
        return $this->db->get(db_prefix() . 'plant_visit_forms')->row_array();
    }

    public function get_by_id($id)
    {
        $this->db->select('*');
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'plant_visit_forms')->row_array();
    }

    public function get_member_data_by_id($id)
    {
        $this->db->select('*');
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'plant_visit_member_data')->row_array();
    }

    public function get_members_data($form_id)
    {
        $this->db->select('*');
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'plant_visit_member_data')->result_array();
    }

    public function add_new_form($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'plant_visit_forms', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New plant visit form created using OTP verification [Lead ID ' . $data['lead_id'] . '] [Form ID: ' . $insert_id . ']');
        }
        return $insert_id;
    }

    public function update_form($data, $id, $isDelete = false, $isCustomerSubmit = false)
    {
        $this->db->where('id', $id);
        if ($isDelete) {
            $data['deleted_at'] = date('Y-m-d H:i:s');
            $data['deleted_by'] = get_staff_full_name();
        } else {
            $data['updated_at'] = date('Y-m-d H:i:s');
            if ($isCustomerSubmit) {
                $data['updated_by'] = "customer";
            } else {
                $data['updated_by'] = get_staff_user_id();
            }
        }
        $this->db->update(db_prefix() . 'plant_visit_forms', $data);
        if ($this->db->affected_rows() > 0) {
            $form_data = $this->get_by_id($id);
            $by_customer = ($isCustomerSubmit) ? "By Customer" : "";
            if ($isDelete) {
                log_activity('Plant Visit form Deleted [ID: ' . $id . '] ' . $by_customer);
                if (!$isCustomerSubmit) {
                    $this->leads_model->log_lead_activity($form_data['lead_id'], 'Plant Visit form Deleted [ID: ' . $id . '] ');
                }
                // Delete lead inquiry form linked to office visit form.
                $getForm = $this->leads_model->get_inquiry_form_single_by_where(["office_visit_form_id" =>  $id]);
                if (!empty($getForm)) {
                    $this->leads_model->update_inquiry_form([], $getForm['id'], true, $isCustomerSubmit);
                }
            } else {
                log_activity('Plant Visit form Updated [ID: ' . $id . '] ' . $by_customer);
                if (!$isCustomerSubmit) {
                    $this->leads_model->log_lead_activity($form_data['lead_id'], 'Plant Visit form Updated [ID: ' . $id . '] ');
                }
            }
            return true;
        }
        return false;
    }

    public function update_member_data($data, $form_id)
    {
        $getFormData = $this->get_by_id($form_id);
        $upload_path = 'uploads/leads/' . $getFormData['lead_id'] . '/';
        $result = false;
        $this->db->where('form_id', $form_id);
        $existing_records = $this->db->get(db_prefix() . 'plant_visit_member_data')->result_array();
        $provided_ids = array_filter(array_column($data, 'id'));
        foreach ($data as $item) {
            if ($item['relation_with_applicant'] != "Other") {
                $item['other_relation'] = NULL;
            }

            if (isset($item['id']) && !empty($item['id'])) {
                $getFormItem = $this->get_member_data_by_id($item['id']);
                if (!empty($item['photo'])) {
                    $old_path = $upload_path . $getFormItem['photo'];
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
                if (!empty($item['aadhar_card'])) {
                    $old_path = $upload_path . $getFormItem['aadhar_card'];
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
                if (!empty($item['pan_card'])) {
                    $old_path = $upload_path . $getFormItem['pan_card'];
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
                if (!empty($item['signature'])) {
                    $old_path = $upload_path . $getFormItem['signature'];
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
                $this->db->where('id', $item['id']);
                $this->db->update(db_prefix() . 'plant_visit_member_data', $item);
            } else {
                $item['form_id'] = $form_id;
                $this->db->insert(db_prefix() . 'plant_visit_member_data', $item);
                $insert_id = $this->db->insert_id();
            }
        }
        if ($this->db->affected_rows() > 0) {
            $result = true;
        }
        if (!empty($provided_ids)) {
            foreach ($existing_records as $record) {
                if (!in_array($record['id'], $provided_ids)) {
                    $getFormItem = $this->get_member_data_by_id($record['id']);
                    if (!empty($item['photo'])) {
                        $old_path = $upload_path . $getFormItem['photo'];
                        if (file_exists($old_path)) {
                            unlink($old_path);
                        }
                    }
                    if (!empty($item['aadhar_card'])) {
                        $old_path = $upload_path . $getFormItem['aadhar_card'];
                        if (file_exists($old_path)) {
                            unlink($old_path);
                        }
                    }
                    if (!empty($item['pan_card'])) {
                        $old_path = $upload_path . $getFormItem['pan_card'];
                        if (file_exists($old_path)) {
                            unlink($old_path);
                        }
                    }
                    if (!empty($item['signature'])) {
                        $old_path = $upload_path . $getFormItem['signature'];
                        if (file_exists($old_path)) {
                            unlink($old_path);
                        }
                    }
                    $this->db->where('id', $record['id']);
                    $this->db->delete(db_prefix() . 'plant_visit_member_data');
                }
            }
        }
        return $result;
    }

    public function update_member_data_single($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'plant_visit_member_data', $data);
    }

    public function delete_member_data($form_id)
    {
        $this->db->where('form_id', $form_id);
        $this->db->delete(db_prefix() . 'plant_visit_member_data');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function get_plant_visitor_types()
    {
        $this->db->select('*');
        $this->db->where('deleted_at IS NULL');
        return $this->db->get(db_prefix() . 'plant_visitor_types')->result_array();
    }

    public function get_plant_visitor_type_by_id($id)
    {
        $this->db->select('*');
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'plant_visitor_types')->row_array();
    }

    public function count_plant_visit_form_by_visitor_type_id($id)
    {
        $this->db->where('visitor_type', $id);
        $this->db->where('deleted_at IS NULL');
        return $this->db->count_all_results(db_prefix() . 'plant_visit_forms');
    }

    public function add_plant_visitor_type($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'plant_visitor_types', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity("Plant visitor types new record created. [ID " . $insert_id . "]");
        }
        return $insert_id;
    }

    public function update_plant_visitor_type($data, $id, $isDelete = false)
    {
        $this->db->where('id', $id);
        if ($isDelete) {
            $data['deleted_at'] = date('Y-m-d H:i:s');
            $data['deleted_by'] = get_staff_full_name();
        }
        $this->db->update(db_prefix() . 'plant_visitor_types', $data);
        if ($this->db->affected_rows() > 0) {
            if ($isDelete) {
                log_activity("Plant visitor types record deleted. [ID " . $id . "]");
            } else {
                log_activity("Plant visitor types record update. [ID " . $id . "]");
            }
            return true;
        }
        return false;
    }

    public function get_relation_types()
    {
        $this->db->select('*');
        $this->db->where('deleted_at IS NULL');
        return $this->db->get(db_prefix() . 'plant_visit_relation_types')->result_array();
    }

    public function get_relation_type_by_id($id)
    {
        $this->db->select('*');
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'plant_visit_relation_types')->row_array();
    }

    public function add_relation_type($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'plant_visit_relation_types', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity("Plant visit relation types new record created. [ID " . $insert_id . "]");
        }
        return $insert_id;
    }

    public function update_relation_type($data, $id, $isDelete = false)
    {
        $this->db->where('id', $id);
        if ($isDelete) {
            $data['deleted_at'] = date('Y-m-d H:i:s');
            $data['deleted_by'] = get_staff_full_name();
        }
        $this->db->update(db_prefix() . 'plant_visit_relation_types', $data);
        if ($this->db->affected_rows() > 0) {
            if ($isDelete) {
                log_activity("Plant visit relation types record deleted. [ID " . $id . "]");
            } else {
                log_activity("Plant visit relation record update. [ID " . $id . "]");
            }
            return true;
        }
        return false;
    }

    public function get_meeting_data($where)
    {
        if (!empty($where['meeting_type']) && $where['meeting_type'] == "Plant Visit") {
            $this->db->select('id, lead_id, visit_date_time AS date_time, approval_staus AS status, "plant_visit_form" AS table_type');
            $this->db->from(db_prefix() . 'plant_visit_forms');
            $this->db->where('deleted_at IS NULL');
            $this->db->where('YEAR(visit_date_time)', $where['year']);
            $this->db->where('MONTH(visit_date_time)', $where['month']);
            if (!empty($where['staff_id'])) {
                $this->db->where('created_by', $where['staff_id']);
            }
            if (!empty($where['status'])) {
                $this->db->where('approval_staus', $where['status']);
            }
            $query = $this->db->get();
            return $query->result_array();
        } elseif (!empty($where['meeting_type'])) {
            $this->db->select('id, rel_id as lead_id, date AS date_time, status, "reminder" AS table_type');
            $this->db->from(db_prefix() . 'reminders');
            $this->db->where('deleted_at IS NULL');
            $this->db->where('YEAR(date)', $where['year']);
            $this->db->where('MONTH(date)', $where['month']);
            $this->db->where('reminder_action', $where['meeting_type']);
            $this->db->where('rel_type', 'lead');
            if (!empty($where['staff_id'])) {
                $this->db->where('staff', $where['staff_id']);
            }
            if (!empty($where['status'])) {
                $this->db->where('status', $where['status']);
            }
            $query = $this->db->get();
            return $query->result_array();
        } else {
            $this->db->select('id, lead_id, visit_date_time AS date_time, approval_staus AS status, "plant_visit_form" AS table_type');
            $this->db->from(db_prefix() . 'plant_visit_forms');
            $this->db->where('deleted_at IS NULL');
            $this->db->where('YEAR(visit_date_time)', $where['year']);
            $this->db->where('MONTH(visit_date_time)', $where['month']);
            if (!empty($where['staff_id'])) {
                $this->db->where('created_by', $where['staff_id']);
            }
            if (!empty($where['status'])) {
                $this->db->where('approval_staus', $where['status']);
            }
            $plant_visit_query = $this->db->get_compiled_select();
            $this->db->select('id, rel_id as lead_id, date AS date_time, status, "reminder" AS table_type');
            $this->db->from(db_prefix() . 'reminders');
            $this->db->where('deleted_at IS NULL');
            $this->db->where('YEAR(date)', $where['year']);
            $this->db->where('MONTH(date)', $where['month']);
            $this->db->where_in('reminder_action', ['Online Meeting', 'Face To Face', 'Plant Visit']);
            $this->db->where('rel_type', 'lead');
            if (!empty($where['staff_id'])) {
                $this->db->where('staff', $where['staff_id']);
            }
            if (!empty($where['status'])) {
                $this->db->where('status', $where['status']);
            }
            $reminder_query = $this->db->get_compiled_select();
            $combined_query = "($plant_visit_query) UNION ALL ($reminder_query) ORDER BY date_time DESC";
            $query = $this->db->query($combined_query);
            return $query->result_array();
        }
    }
}
