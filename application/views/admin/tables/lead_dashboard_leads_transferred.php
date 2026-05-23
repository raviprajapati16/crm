<?php
$CI = &get_instance();
$where = $CI->input->post();
$dateRange = explode(" - ", $where['daterange']);
$startDate = to_sql_date($dateRange[0]);
$endDate = to_sql_date($dateRange[1]);

$limit = (int)$where['length'];
$offset = (int)$where['start'];
$search_value = $where['search']['value'];

if (!empty($where['assignee'])) {
    $sql = "
    WITH RankedActivities AS (
        SELECT
            a.*,
            l.name AS lead_name,
            l.id AS lead_id,
            IFNULL(
                NULLIF(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(a.additional_data, 'profile/', -1), '\"', 1), '\"', 1), ''),
                '0'
            ) AS profile_id,
            (SELECT full_name FROM tbllead_activity_log WHERE id = a.id) AS transferred_by,
            ROW_NUMBER() OVER (
                PARTITION BY a.leadid
                ORDER BY a.date DESC
            ) as row_num
        FROM
            tbllead_activity_log a
        JOIN
            tblleads l ON l.id = a.leadid
        WHERE
            DATE(a.date) BETWEEN ? AND ?
            AND a.description LIKE '%not_lead_activity_assigned_to%'
            AND a.staffid != ? AND a.staffid != 0
            AND l.assigned = ?
    ),
    FilteredActivities AS (
        SELECT *
        FROM RankedActivities
        WHERE
            row_num = 1
            AND profile_id = ?
            " . (!empty($search_value) ? "AND (
                lead_name LIKE ? OR
                lead_id LIKE ? OR
                transferred_by LIKE ?
            )" : "") . "
    ),
    TotalCountCTE AS (
        SELECT COUNT(*) as total_count
        FROM FilteredActivities
    )
    SELECT
        lead_id,
        lead_name,
        profile_id,
        transferred_by,
        date AS assigned_time,
        (SELECT total_count FROM TotalCountCTE) AS total_records
    FROM
        FilteredActivities
    LIMIT ? OFFSET ?";

    $params = [
        $startDate,
        $endDate,
        $where['assignee'],
        $where['assignee'],
        $where['assignee']
    ];

    if (!empty($search_value)) {
        $search_param = "%{$search_value}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }

    $params[] = $limit;
    $params[] = $offset;

    $query = $CI->db->query($sql, $params);
    $result = $query->result_array();

    $total_records = !empty($result) ? $result[0]['total_records'] : 0;
}

$output = [
    "draw" => $where['draw'],
    "iTotalRecords" => $total_records,
    "iTotalDisplayRecords" => $total_records,
    "aaData" => []
];

if (!empty($result)) {
    foreach ($result as $key => $aRow) {
        $row = [];
        $row[] = "<a href='".admin_url('leads/index/'.$aRow['lead_id'])."' target='_blank'>#" . $aRow['lead_id'] . "</a>";
        $row[] = "<a href='".admin_url('leads/index/'.$aRow['lead_id'])."' target='_blank'>" . $aRow['lead_name'] . "</a>";
        $row[] = $aRow['transferred_by'];
        $row[] = _d($aRow['assigned_time'], true);
        $row['DT_RowClass'] = 'has-row-options';
        $output['aaData'][] = $row;
    }
}

return $output;