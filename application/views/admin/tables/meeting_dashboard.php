<?php
$CI = &get_instance();
$reminder_query = '';
$limit = (int)$where['length'];
$offset = (int)$where['start'];

$combined_query = '';

$search_value = $where['search']['value'];

$CI->db->select('reminders.id, reminders.rel_id as lead_id, reminders.reminder_action as meeting_type, reminders.date AS date_time, reminders.status, CONCAT(staff.firstname, " ", staff.lastname) AS staff_name, "reminder" AS table_type');
$CI->db->from(db_prefix() . 'reminders');
$CI->db->join(db_prefix() . 'staff AS staff', 'staff.staffid = reminders.staff', 'left');
$CI->db->where(db_prefix() . 'reminders.deleted_at IS NULL');
$CI->db->where('YEAR(' . db_prefix() . 'reminders.date)', $where['year']);
$CI->db->where('MONTH(' . db_prefix() . 'reminders.date)', $where['month']);

$CI->db->where(db_prefix() . 'reminders.rel_type', 'lead');

if (!empty($where['type'])) {
    $CI->db->where('reminder_action', $where['type']);
} else {
    $CI->db->where_in(db_prefix() . 'reminders.reminder_action', ['Online Meeting', 'Face To Face', 'Plant Visit']);
}

if (!empty($where['staff_id'])) {
    $CI->db->where(db_prefix() . 'reminders.staff', $where['staff_id']);
}
if (!empty($where['status'])) {
    $CI->db->where(db_prefix() . 'reminders.status', $where['status']);
}
if (!empty($where['date'])) {
    $CI->db->where('DATE(' . db_prefix() . 'reminders.date)', to_sql_date($where['date']));
}

if (!empty($search_value)) {
    $CI->db->group_start();
    $CI->db->like('reminders.id', $search_value);
    $CI->db->or_like('reminders.rel_id', $search_value);
    $CI->db->or_like('reminders.reminder_action', $search_value);
    $CI->db->or_like('reminders.status', $search_value);
    $CI->db->or_like('staff.firstname', $search_value);
    $CI->db->or_like('staff.lastname', $search_value);
    $CI->db->or_like('reminders.created_at', $search_value);
    $CI->db->group_end();
}

$combined_query = $CI->db->get_compiled_select();

$total_records_query = "SELECT COUNT(*) as total FROM ($combined_query) as combined_results";
$total_records_result = $CI->db->query($total_records_query)->row();
$total_records = $total_records_result->total;

$combined_query .= " ORDER BY date_time ASC LIMIT $limit OFFSET $offset";

$rResult = $CI->db->query($combined_query)->result_array();

$output = [
    "draw" => $where['draw'],
    "iTotalRecords" => (int) $total_records,
    "iTotalDisplayRecords" => (int) $total_records,
    "aaData" => []
];

if (!empty($rResult)) {
    foreach ($rResult as $aRow) {
        $row = [];
        $row[] = "<a href='javascript:;' onclick=init_lead('" . $aRow['lead_id'] . "');return false;>#" . $aRow['id'] . "<a/>";
        $row[] = "<a href='javascript:;' onclick=init_lead('" . $aRow['lead_id'] . "');return false;>" . $aRow['lead_id'] . "<a/>";
        $row[] = $aRow['meeting_type'];
        $row[] = $aRow['staff_name'];
        $row[] = "<span class='label label-" . ($aRow['status'] == 'Approved' || $aRow['status'] == 'Attend' || $aRow['status'] == 'Visited' || $aRow['status'] == 'Present' ? 'success' : ($aRow['status'] == 'Not Approved' || $aRow['status'] == 'Not Attend' || $aRow['status'] == 'Absent' ? 'danger' : 'warning')) . "'>" . $aRow['status'] . "</span>";
        $row[] = _d($aRow['date_time']);
        $row['DT_RowClass'] = 'has-row-options';
        $output['aaData'][] = $row;
    }
}

return $output;
