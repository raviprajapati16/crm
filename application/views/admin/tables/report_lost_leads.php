<?php
$CI = &get_instance();
$reminder_query = '';
$limit = (int)$where['length'];
$offset = (int)$where['start'];
$combined_query = '';
$search_value = $where['search']['value'];
$CI->db->select('leads.id');
$CI->db->from(db_prefix() . 'leads leads');
$CI->db->where('leads.lost', '1');
$CI->db->where('YEAR(leads.last_status_change)', $where['year']);
if (!empty($where['month'])) {
    $CI->db->where('MONTH(leads.last_status_change)', $where['month']);
}

if (!empty($search_value)) {
    $CI->db->group_start();
    $CI->db->like('leads.id', $search_value);
    $CI->db->or_like('leads.name', $search_value);
    $CI->db->group_end();
}

$leadsData = $CI->db->get()->result_array();

$total_records = 0;
$recordArr = array();
if (!empty($leadsData)) {
    $leadsIdArr = array_column($leadsData, 'id');

    //fetch direct proposals based on leads (without linked estimate)
    $CI->db->select('proposals.rel_id as lead_id, proposals.id as proposal_id, proposals.total, proposals.currency');
    $CI->db->from(db_prefix() . 'proposals proposals');
    $CI->db->where('proposals.rel_type', 'lead');
    $CI->db->where_in('proposals.rel_id', $leadsIdArr);
    $CI->db->where('proposals.estimate_id IS NULL');
    $proposalsData = $CI->db->get()->result_array();
    $recordArr = array_merge($proposalsData, $recordArr);

    $result = [];
    if (!empty($recordArr)) {
        foreach ($recordArr as $item) {
            $leadId = $item['lead_id'];
            if (!isset($result[$leadId])) {
                $result[$leadId] = [
                    'lead_id' => $leadId,
                    'currency' => $item['currency'],
                    'total' => 0,
                    'proposal_ids' => [],
                ];
            }
            $result[$leadId]['total'] += $item['total'];
            $result[$leadId]['proposal_ids'][] = $item['proposal_id'];
        }
    }
}

$output = [
    "draw" => $where['draw'],
    "iTotalRecords" => count($result),
    "iTotalDisplayRecords" => count($result),
    "aaData" => []
];

if (!empty($result)) {
    foreach ($result as $key => $aRow) {
        $proposalIds = [];
        if (isset($aRow['proposal_ids'])) {
            $aRow['proposal_ids'] = array_values(array_unique(array_filter($aRow['proposal_ids'])));
            foreach ($aRow['proposal_ids'] as $id) {
                $proposalIds[]  = "<a target='_blank' href='" . admin_url('proposals#' . $id) . "'>#" . format_proposal_number($id) . "<a/>";
            }
        }

        $row = [];
        $row[] = "<a href='javascript:;' onclick=init_lead('" . $aRow['lead_id'] . "');return false;>#" . $aRow['lead_id'] . "<a/>";
        $row[] = "<a href='javascript:;' onclick=init_lead('" . $aRow['lead_id'] . "');return false;>" . get_lead_full_name($aRow['lead_id']) . "<a/>";
        $row[] = implode(", ", $proposalIds);
        $row[] = app_format_money($aRow['total'], $aRow['currency']) . '/-';
        $row['DT_RowClass'] = 'has-row-options';
        $output['aaData'][] = $row;
    }
}

return $output;
