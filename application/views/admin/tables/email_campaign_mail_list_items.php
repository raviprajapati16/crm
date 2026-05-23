<?php
$CI = &get_instance();
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'null as sr_no',
    'name',
    'email',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'emailcampaign_mail_list_items';
$where = [];
array_push($where, "AND list_id = " . $list_id);

$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, ['id']);
$output       = $result['output'];
$rResult      = $result['rResult'];
$no = $CI->input->post('start');
foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'name' || $aColumns[$i] == 'email') {
            if (!empty(!empty($_data))) {
                $_data .= '<div class="row-options">';
                if (has_permission('email_campaigns', '', 'edit')) {
                    $_data .= '<a href="javascript:;" data-id="' . $aRow['id'] . '" data-name="' . $aRow['name'] . '" data-email="' . $aRow['email'] . '" onclick="openMailListItemModal(' . $aRow['id'] . ')">' . _l('Edit') . '</a>';
                }

                if (has_permission('email_campaigns', '', 'delete')) {
                    $_data .= ' | <a href="' . admin_url('email_campaign_mail_list/delete_item/' . $aRow['id']) . '" class="text-danger _delete">' . _l('Delete') . '</a>';
                }
                $_data .= '</div>';
            }
        } elseif ($aColumns[$i] == 'null as sr_no') {
            $_data = $no += 1;
        }
        $row[] = $_data;
    }
    $output['aaData'][] = $row;
}
