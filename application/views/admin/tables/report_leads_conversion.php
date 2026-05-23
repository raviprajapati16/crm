<?php

defined('BASEPATH') or exit('No direct script access allowed');

$year = $this->ci->input->post('year');
$month = $this->ci->input->post('month');

$aColumns = [
    'id',
    'name',
    'last_status_change',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'leads';

$where  = [];

array_push($where, 'AND YEAR(last_status_change) = "' . $this->ci->input->post('year') . '"');

if (!empty($month)) {
    array_push($where, 'AND MONTH(last_status_change) = "' . $month . '"');
}
array_push($where, 'AND status = 1');
array_push($where, 'AND isDeleted = "false"');

$join = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, []);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = '<a href="javascript:;" onclick="init_lead(' . $aRow['id'] . '); return false;">#' . $aRow['id'] . '</a>';

    $row[] = '<a href="javascript:;" onclick="init_lead(' . $aRow['id'] . '); return false;">' . $aRow['name'] . '</a>';

    $row[] = _d($aRow['last_status_change']);
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
return $output;