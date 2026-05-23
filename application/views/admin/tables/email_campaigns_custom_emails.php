<?php

defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();
$aColumns = [
    'null as sr_no',
    'email',
    db_prefix() . 'mail_services.service_name',
];

$sIndexColumn = 'id';
$sTable = db_prefix() . 'emailcampaign_emails';
$additionalSelect = [db_prefix() . 'emailcampaign_emails.id'];

$join = [];
$where = [];

$join = [
    'JOIN ' . db_prefix() . 'mail_services ON ' . db_prefix() . 'mail_services.id = ' . db_prefix() . 'emailcampaign_emails.service_id',
];

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

$no = $CI->input->post('start');
foreach ($rResult as $key => $aRow) {
    $row = [];
    foreach ($aRow as $column => $value) {
        $_data = $value;
        if ($column == 'email') {
            $_data .= '<div class="row-options">';
            if (has_permission('email_campaigns', '', 'edit')) {
                $_data .= '<a href="javascript:;" onclick="openQuestionModal(' . $aRow[db_prefix() . 'emailcampaign_emails.id'] . ')" class="text-info">' . _l('Edit') . '</a>';
            }
            if (has_permission('email_campaigns', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('email_campaigns_emails/delete/' . $aRow[db_prefix() . 'emailcampaign_emails.id']) . '" class="text-danger _delete">' . _l('Delete') . '</a>';
            }
            $_data .= '</div>';
            $row[] = $_data;
        } else if ($column == 'sr_no') {
            $row[] = $no += 1;
        } else {
            $row[] = $_data;
        }
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
