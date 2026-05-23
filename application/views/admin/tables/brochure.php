<?php
$CI = &get_instance();
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'null as number',
    'thumbnail',
    'null as action'
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'brochure';
$additionalSelect = ['hash', 'file_name', 'id', 'title'];

$join = [];
$where = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output = $result['output'];
$rResult = $result['rResult'];

$no = 0;
foreach ($rResult as $key => $aRow) {
    $no += 1;
    $row = [];
    foreach ($aRow as $column => $value) {
        $_data = $value;
        if ($column == 'number') {
            $_data = $no;
            $row[] = $_data;
        } else if ($column == 'thumbnail') {
            $url = site_url('uploads/brochures/thumbnails/' . $value);

            $_data = "<div style='display: flex; align-items: center;'>";

            $_data .= "<div style='margin-right: 15px;'>";
            $_data .= "<img src='" . $url . "' class='img-rounded' alt='" . $aRow['title'] . "' width='40' style='border:1px solid black;' />";
            $_data .= "</div>";

            $_data .= "<div>";
            $_data .= "<div style='font-size:18px;'>" . $aRow['title'] . "</div>";
            $_data .= '<div class="row-options" style="margin-top: 5px;">';
            if (has_permission('brochure', '', 'edit')) {
                $_data .= '<a href="javascript:;" class="text-info" onclick="openBrochureModal(' . $aRow['id'] . ')">' . _l('Edit') . '</a>';
            }
            if (has_permission('brochure', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('brochure/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('Delete') . '</a>';
            }
            $_data .= '</div>';
            $_data .= "</div>";

            $_data .= "</div>";

            $row[] = $_data;
        } else if ($column == 'action') {
            $_data = "";
            if (has_permission('brochure', '', 'download')) {
                $pdf_url = base_url('uploads/brochures/' . $aRow['file_name']);
                $_data .= "<a href='" . $pdf_url . "' target='_blank' class='btn btn-xs btn-primary' data-toggle='tooltip' data-title='Download Presentation' download><i class='fa fa-download' aria-hidden='true'></i></a> ";
            }
            if (has_permission('brochure', '', 'view')) {
                $_data .= "<a href='" . admin_url('brochure/view/' . $aRow['id']) . "' target='_blank' class='btn btn-xs btn-primary' data-toggle='tooltip' data-title='View Presentation' href='javascript:;'><i class='fa fa-eye' aria-hidden='true'></i></a> ";
            }
            if (has_permission('brochure', '', 'share')) {
                $_data .= "<a class='btn btn-xs btn-primary copybtn' data-url='" . site_url('brochure/view/' . $aRow['hash']) . "' data-toggle='tooltip' data-title='Copy Presentation Public URL' href='javascript:;'><i class='fa fa-copy' aria-hidden='true'></i></a> ";
            }
            $row[] = $_data;
        }
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}