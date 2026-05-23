<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Biomatric_model extends App_Model
{
    public function insert_attendance($json_data)
    {
        $data = json_decode($json_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_activity('Bio-Matric Cron JOB Error : Invalid JSON data');
        }
        $records = 0;
        foreach ($data as $record) {
            $biomax_staff_id = $record['EmployeeCode'];
            $punch_time = $record['LogDate'];
            $this->db->where('biomax_staff_id', $biomax_staff_id);
            $this->db->where('punch_time', $punch_time);
            $query = $this->db->get('tblbiomax_attendance');
            if ($query->num_rows() == 0) {
                $insert_data = [
                    'biomax_staff_id' => $biomax_staff_id,
                    'punch_time' => $punch_time,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $check = $this->db->insert(db_prefix().'biomax_attendance', $insert_data);
                if ($check) {
                    $records += 1;
                } else {
                    log_activity('Bio-Matric Cron JOB : Record insert error :' . json_encode($record));
                }
            }
        }
        return $records;
    }
}
