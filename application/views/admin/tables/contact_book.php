<?php
$CI = &get_instance();
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'id',
    'CONCAT(firstname, " ", lastname) as full_name',
    'email',
    'CONCAT("+", country_dial_code, " ", phone) as phone',
    'company',
    '(SELECT name FROM ' . db_prefix() . 'contact_book_category WHERE id = ' . db_prefix() . 'contact_book.category) as category_name',
    'CONCAT((SELECT firstname FROM ' . db_prefix() . 'staff WHERE staffid = ' . db_prefix() . 'contact_book.created_by), " ", (SELECT lastname FROM ' . db_prefix() . 'staff WHERE staffid = ' . db_prefix() . 'contact_book.created_by)) as contact_owner',

];

$join = [];

$additionalSelect = [
    'id',
    'firstname',
    'lastname',
    'state',
    'city',
    '(SELECT short_name FROM ' . db_prefix() . 'countries WHERE country_id = ' . db_prefix() . 'contact_book.country) as country',
    'category',
    'created_by',
    '(SELECT CONCAT(firstname, " ", lastname) FROM ' . db_prefix() . 'staff WHERE staffid = ' . db_prefix() . 'contact_book.created_by) as contact_owner',

];

$where = [];

if (!has_permission('contact_book', '', 'view')) {
    array_push($where, 'AND ' . db_prefix() . 'contact_book.created_by = ' . get_staff_user_id());
}

$sIndexColumn = 'id';
$sTable = db_prefix() . 'contact_book';
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output = $result['output'];
$rResult = $result['rResult'];
$no = $CI->input->post('start');

foreach ($rResult as $aRow) {
    $row = [];


    $row[] = $no += 1;
    $row[] = !empty($aRow['category_name']) ? $aRow['category_name'] : '-';
    $row[] = !empty($aRow['company']) ? $aRow['company'] : '-';
    $row[] = $aRow['firstname'] . ' ' . $aRow['lastname'];
    $row[] = !empty($aRow['email']) ? $aRow['email'] : '-';
    $row[] = !empty($aRow['phone']) ? $aRow['phone'] : '-';
    $row[] = !empty($aRow['state']) ? $aRow['state'] : '-';
    $row[] = !empty($aRow['country']) ? $aRow['country'] : '-';
    $row[] = !empty($aRow['contact_owner']) ? $aRow['contact_owner'] : '-';


    $html = '';
    if (has_permission('contact_book', '', 'view') || has_permission('contact_book', '', 'view_own')) {
        $html .= '<a href="javascript:;" class="btn btn-primary btn-xs" onclick="viewContactDetails(' . $aRow['id'] . ')"><i class="fa fa-eye"></i></a>';
    }
    if (has_permission('contact_book', '', 'edit')) {
        $html .= '&nbsp;<a href="javascript:;" class="btn btn-primary btn-xs" onclick="openContactModal(' . $aRow['id'] . ')"><i class="fa fa-pencil-square-o"></i></a>';
    }
    if (has_permission('contact_book', '', 'delete')) {
        $html .= '&nbsp;<a href="javascript:;" class="btn btn-danger btn-xs" onclick="deleteContact(' . $aRow['id'] . ')"><i class="fa fa-trash"></i></a>';
    }
    $row[] = $html;
    $output['aaData'][] = $row;
}