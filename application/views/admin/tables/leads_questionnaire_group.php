<?php

defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();

$aColumns = [
    'null as record_index',
    db_prefix() . 'items_groups.name as main_group_name',
    db_prefix() . 'sub_groups.name as sub_group_name',
    'null as question_count',
    'null as manage',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'items_groups';


$join = [
    'LEFT JOIN ' . db_prefix() . 'sub_groups ON ' . db_prefix() . 'sub_groups.group_id = ' . db_prefix() . 'items_groups.id',
];

$additionalSelect = [
    db_prefix() . "items_groups.id as main_group_id",
    db_prefix() . "sub_groups.id as sub_group_id",
];


$where = [];
array_push($where, 'AND ' . db_prefix() . 'items_groups.deleted_at IS NULL');
array_push($where, 'AND ' . db_prefix() . 'sub_groups.deleted_at IS NULL');

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $key => $aRow) {
    $row = [];
    foreach ($aRow as $column => $value) {
        if ($column == "record_index") {
            $value = $key + 1;
        }
        if ($column == "manage") {
            $link = admin_url('leads_questionnaire_group/manage_questions/' . $aRow['main_group_id'] . '/' . $aRow['sub_group_id']);
            $value = "<a href='" . $link . "' class='btn btn-primary'>" . _l("manage") . "</a>";
            if(isset($row[3]) && $row[3] > 0){
                $value .= " <a href='javascript:;' data-maingroup='" . $aRow['main_group_id'] . "' data-subgroup='" . $aRow['sub_group_id'] . "' class='btn btn-success mleft5 copy-group'><i class='fa fa-files-o' aria-hidden='true'></i> " . _l("copy") . "</a>";
            }
        }
        if ($column == "question_count") {
            $CI->db->where('main_group_id', $aRow['main_group_id']);
            if ($aRow['sub_group_id']) {
                $CI->db->where('sub_group_id', $aRow['sub_group_id']);
            }
            $CI->db->where('datedeleted IS NULL');
            $value = $CI->db->count_all_results(db_prefix() . "lead_questions");
        }
        $row[] = $value;
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
