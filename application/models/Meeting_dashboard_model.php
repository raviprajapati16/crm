<?php

class Meeting_dashboard_model extends App_Model
{
    public function get_meeting_data($where)
    {
        $this->db->select('id, rel_id as lead_id, date AS date_time, status, "reminder" AS table_type');
        $this->db->from(db_prefix() . 'reminders');
        $this->db->where('deleted_at IS NULL');
        $this->db->where('YEAR(date)', $where['year']);
        $this->db->where('MONTH(date)', $where['month']);
        if (!empty($where['meeting_type'])) {
            $this->db->where('reminder_action', $where['meeting_type']);
        } else {
            $this->db->where_in('reminder_action', ['Online Meeting', 'Face To Face', "Plant Visit"]);
        }
        $this->db->where('rel_type', 'lead');

        if (!empty($where['staff_id'])) {
            $this->db->where('staff', $where['staff_id']);
        }
        if (!empty($where['status'])) {
            $this->db->where('status', $where['status']);
        }

        $query = $this->db->get();
        return $query->result_array();
    }
}
