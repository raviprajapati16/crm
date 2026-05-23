<?php

class Office_visitor_form_model extends App_Model
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
        return $this->db->get(db_prefix() . 'office_visitors_forms')->result_array();
    }

    public function get_single($where)
    {
        $this->db->select('*');
        $this->db->where($where);
        $this->db->where('deleted_at IS NULL');
        return $this->db->get(db_prefix() . 'office_visitors_forms')->row_array();
    }

    public function get_by_id($id)
    {
        $this->db->select('*');
        $this->db->where('id', $id);
        return $this->db->get(db_prefix() . 'office_visitors_forms')->row_array();
    }

    public function get_members_data($form_id)
    {
        $this->db->select('*');
        $this->db->where('form_id', $form_id);
        return $this->db->get(db_prefix() . 'office_visitors_data')->result_array();
    }

    public function add_new_form($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();
        $this->db->insert(db_prefix() . 'office_visitors_forms', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            log_activity('New office visit form created using OTP verification [Lead ID ' . $data['lead_id'] . '] [Form ID: ' . $insert_id . ']');
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
        $this->db->update(db_prefix() . 'office_visitors_forms', $data);
        if ($this->db->affected_rows() > 0) {
            $form_data = $this->get_by_id($id);
            $by_customer = ($isCustomerSubmit) ? "By Customer" : "";
            if ($isDelete) {
                log_activity('Office Visitor form Deleted [ID: ' . $id . '] ' . $by_customer);
                if (!$isCustomerSubmit) {
                    $this->leads_model->log_lead_activity($form_data['lead_id'], 'Office Visitor form Deleted [ID: ' . $id . '] ');
                }
                // Delete lead inquiry form linked to office visit form.
                $getForm = $this->leads_model->get_inquiry_form_single_by_where(["office_visit_form_id" =>  $id]);
                if (!empty($getForm)) {
                    $this->leads_model->update_inquiry_form([], $getForm['id'], true, $isCustomerSubmit);
                }
            } else {
                log_activity('Office Visitor form Updated [ID: ' . $id . '] ' . $by_customer);
                if (!$isCustomerSubmit) {
                    $this->leads_model->log_lead_activity($form_data['lead_id'], 'Office Visitor form Updated [ID: ' . $id . '] ');
                }
            }
            return true;
        }
        return false;
    }

    public function update_ovf_form($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'office_visitors_forms', $data);
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function update_member_data($data, $form_id)
    {
        $result = false;
        $this->db->where('form_id', $form_id);
        $existing_records = $this->db->get(db_prefix() . 'office_visitors_data')->result_array();
        $provided_ids = array_filter(array_column($data, 'id'));
        foreach ($data as $item) {
            if ($item['relation_with_applicant'] != "Other") {
                $item['other_relation'] = NULL;
            }
            if (isset($item['id']) && !empty($item['id'])) {
                $this->db->where('id', $item['id']);
                $this->db->update(db_prefix() . 'office_visitors_data', $item);
            } else {
                $item['form_id'] = $form_id;
                $this->db->insert(db_prefix() . 'office_visitors_data', $item);
                $insert_id = $this->db->insert_id();
            }
        }
        if ($this->db->affected_rows() > 0) {
            $result = true;
        }
        if (!empty($provided_ids)) {
            foreach ($existing_records as $record) {
                if (!in_array($record['id'], $provided_ids)) {
                    $this->db->where('id', $record['id']);
                    $this->db->delete(db_prefix() . 'office_visitors_data');
                }
            }
        }
        return $result;
    }

    public function delete_member_data($form_id)
    {
        $this->db->where('form_id', $form_id);
        $this->db->delete(db_prefix() . 'office_visitors_data');
        if ($this->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    public function check_ovf_linked_inquiry_form($data)
    {
        $by_customer = (!is_staff_logged_in() && !is_admin()) ? "By Customer" : "";
        $this->db->select('*');
        $this->db->where('office_visit_form_id', $data['officeFormId']);
        $lif_data = $this->db->get(db_prefix() . 'lead_inquiry_forms')->row_array();
        if (!empty($lif_data)) {
            $updateData = [];
            $this->db->where('id', $lif_data['id']);
            $updateData['main_group_id'] = $data['mainGroupId'];
            $updateData['sub_group_id'] = $data['subGroupId'];
            $updateData['deleteddate'] = NULL;
            $updateData['deleted_by'] = NULL;
            $updateData['updated_by'] = "auto-by-office-visit-form";
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            $this->db->update(db_prefix() . 'lead_inquiry_forms', $updateData);
            if ($this->db->affected_rows() > 0) {
                log_activity('Leads Inquiry form [formID : ' . $lif_data['id'] . '] auto updated by office visit form [formID : ' . $data['officeFormId'] . '] '.$by_customer);
                if (is_staff_logged_in() || is_admin()) {
                    $this->leads_model->log_lead_activity($lif_data['lead_id'], 'Leads Inquiry form [formID : ' . $lif_data['id'] . '] auto updated by office visit form [formID : ' . $data['officeFormId'] . ']');
                }
                return $lif_data['id'];
            }
        } else {
            $formData = [
                "formkey" => generateUniqueString(),
                "lead_id" => $data['lead_id'],
                "office_visit_form_id" => $data['officeFormId'],
                "main_group_id" => $data['mainGroupId'],
                "sub_group_id" => $data['subGroupId'],
                "is_temp" => "1",
                "created_at" => date('Y-m-d H:i:s'),
                "created_by" => "auto-by-office-visit-form",
            ];
            $this->db->insert(db_prefix() . 'lead_inquiry_forms', $formData);
            $insert_id = $this->db->insert_id();
            log_activity('Leads Inquiry form [formID : ' . $insert_id . '] auto created by office visit form [formID : ' . $data['officeFormId'] . '] '.$by_customer);
            if (is_staff_logged_in() || is_admin()) {
                $this->leads_model->log_lead_activity($data['lead_id'], 'Leads Inquiry form [formID : ' . $insert_id . '] auto created by office visit form [formID : ' . $data['officeFormId'] . ']');
            }
            return $insert_id;
        }
        return false;
    }

    public function ovf_delete_inquiry_form($data)
    {

        $by_customer = (!is_staff_logged_in() && !is_admin()) ? "By Customer" : "";
        $getForm = $this->leads_model->get_inquiry_form_single_by_where(["office_visit_form_id" => $data['officeFormId']]);
        $updateData['deleted_by'] = "auto-by-office-visit-form";
        $updateData['deleteddate'] = date('Y-m-d H:i:s');
        $this->db->where('office_visit_form_id', $data['officeFormId']);
        $this->db->update(db_prefix() . 'lead_inquiry_forms', $updateData);
        if ($this->db->affected_rows() > 0) {
            log_activity('Leads Inquiry form [formID : ' . $getForm['id'] . '] auto deleted by office visit form [formID : ' . $data['officeFormId'] . '] '.$by_customer);
            if (is_staff_logged_in() || is_admin()) {
                $this->leads_model->log_lead_activity($data['lead_id'], 'Leads Inquiry form [formID : ' . $getForm['id'] . '] auto deleted by office visit form [formID : ' . $data['officeFormId'] . ']');
            }
            return true;
        }
        return false;
    }

    public function ovf_delete_question_data($form_id)
    {
        $this->db->where('form_id', $form_id);
        $this->db->delete(db_prefix() . 'lead_inquiry_forms_data');
    }
}
