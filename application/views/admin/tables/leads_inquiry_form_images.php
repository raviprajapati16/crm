<?php

defined('BASEPATH') or exit('No direct script access allowed');
$CI = &get_instance();

$aColumns = [
    'id',
    'title',
    'value',
    'is_active',
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'lead_inquiry_forms_images';

$join = [];
$additionalSelect = ['type'];
$where = [];

if($type == "popup_images"){
    array_push($where,'AND `type` = "popup-image"');
}else if($type == "slider_images"){
    array_push($where,'AND `type` = "background-image-slider"');
}

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $key => $aRow) {
    $row = [];
    foreach ($aRow as $column => $value) {
        if ($column == "title") {
            $value = '<a class="id-col" data-type="' . $aRow['type'] . '" href="javascript:;" onclick="addImageModal(' . $aRow['id'] . ',this)">'.$aRow['title'].'</a>
            <div class="row-options"><a href="javascript:;" data-id="' . $aRow['type'] . '" onclick="addImageModal(' . $aRow['id'] . ',this)">Edit </a> | <a href=' . admin_url('leads_questionnaire_group/delete_image/' . $aRow['id']) . ' class="text-danger _delete">Delete</a></div>';
        } else if ($column == "is_active") {
            $is_checked = ($aRow['is_active']) ? 'checked' : '';
            $value = '<div class="mleft5"><div class="onoffswitch">
                        <input type="checkbox" data-type="'.$type.'" data-id="' . $aRow['id'] . '" name="onoffswitch" class="onoffswitch-checkbox formswitch" id="form_switch_' . $aRow['id'] . '" ' . $is_checked . '>
                        <label class="onoffswitch-label" for="form_switch_' . $aRow['id'] . '"></label>
                    </div>
                </div>';
        } else if ($column == "value") {
            $value = '<img width="100px" src="'.site_url('uploads/lead_inquiry_form_images/'.$value).'" class="img-thumbnail" alt="'.$aRow['title'].'">';
        }
        $row[] = $value;
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
