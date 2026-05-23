<?php
$CI = &get_instance();
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'null as sr_no',
    'title',
    'description',
    'null as preview',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'tutorial_videos';
$additionalSelect = ['id', 'link'];

$join = [];
$where = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output = $result['output'];
$rResult = $result['rResult'];

$no = $CI->input->post('start');
foreach ($rResult as $key => $aRow) {
    $row = [];
    foreach ($aRow as $column => $value) {
        $_data = $value;
        if ($column == 'title') {
            $_data .= '<div class="row-options">';
            if (has_permission('tutorials_videos', '', 'edit')) {
                $_data .= '<a href="javascript:;" onclick="openTutorialModal(' . $aRow['id'] . ')">' . _l('Edit') . '</a>';
            }
            if (has_permission('tutorials_videos', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('tutorials_videos/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('Delete') . '</a>';
            }
            $_data .= '</div>';
            $row[] = $_data;
        } else if ($column == 'preview') {
            if (has_permission('tutorials_videos', '', 'view')) {
                $_data = "<a href='" . $aRow['link'] . "' target='_blank' class='btn btn-xs btn-primary' data-toggle='tooltip' data-title='View' href='javascript:;'><i class='fa fa-play' aria-hidden='true'></i></a> ";
                $_data .= "<a class='btn btn-xs btn-primary copybtn' data-url='" . $aRow['link'] . "' data-toggle='tooltip' data-title='Copy video Public URL' href='javascript:;'><i class='fa fa-copy' aria-hidden='true'></i></a> ";

                $row[] = $_data;
            }
        } else if ($column == 'sr_no') {
            $row[] = $no += 1;
        } else {
            $row[] = $_data;
        }
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}