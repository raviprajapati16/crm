<?php

defined('BASEPATH') or exit('No direct script access allowed');

$this->ci->load->model('gdpr_model');
$lockAfterConvert      = get_option('lead_lock_after_convert_to_customer');
$has_permission_delete = has_permission('leads', '', 'delete');
$custom_fields         = get_table_custom_fields('leads');
$consentLeads          = get_option('gdpr_enable_consent_for_leads');
$statuses              = $this->ci->leads_model->get_status();

$aColumns = [
    '1',
    db_prefix() . 'leads.id as id',
    db_prefix() . 'leads.name as name',
];
if (is_gdpr() && $consentLeads == '1') {
    $aColumns[] = '1';
}
$aColumns = array_merge($aColumns, [
    'company',
    db_prefix() . 'leads.email as email',
    db_prefix() . 'leads.phonenumber as phonenumber',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE rel_id = ' . db_prefix() . 'leads.id and rel_type="lead" ORDER by tag_order ASC LIMIT 1) as tags',
    'firstname as assigned_firstname',
    db_prefix() . 'leads_status.name as status_name',
    db_prefix() . 'leads_sources.name as source_name',
    'lastcontact',
    'dateadded',
    'reminderdate',
    db_prefix() . 'leads.datedeleted as datedeleted',
    'deletedBy',
    'isDeleted',
    'CalculateLeadPriority(' . db_prefix() . 'leads.lastcontact, reminderdate, ' . db_prefix() . 'leads.status) as lapselead',
]);

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'leads';

$join = [
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'leads.assigned',
    'LEFT JOIN ' . db_prefix() . 'leads_status ON ' . db_prefix() . 'leads_status.id = ' . db_prefix() . 'leads.status',
    'JOIN ' . db_prefix() . 'leads_sources ON ' . db_prefix() . 'leads_sources.id = ' . db_prefix() . 'leads.source',
];

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'leads.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}
array_push($join, 'Left Join (SELECT Max(tblreminders.`date`) AS reminderdate,tblreminders.id,tblreminders.description,tblreminders.isnotified,tblreminders.rel_id, tblreminders.staff,tblreminders.rel_type,tblreminders.notify_by_email,tblreminders.creator FROM tblreminders GROUP BY tblreminders.rel_id) as reminder ON tblleads.id = reminder.rel_id ');


$where  = [];
$filter = false;
if ($this->ci->input->post('custom_view')) {
    $filter = $this->ci->input->post('custom_view');
    if ($filter == 'lost') {
        array_push($where, 'AND lost = 1');
    } elseif ($filter == 'junk') {
        array_push($where, 'AND junk = 1');
    } elseif ($filter == 'not_assigned') {
        array_push($where, 'AND assigned = 0');
    } elseif ($filter == 'contacted_today') {
        array_push($where, 'AND lastcontact LIKE "' . date('Y-m-d') . '%"');
    } elseif ($filter == 'created_today') {
        array_push($where, 'AND dateadded LIKE "' . date('Y-m-d') . '%"');
    } elseif ($filter == 'public') {
        array_push($where, 'AND is_public = 1');
    } elseif ($filter == 'today_leads') {
        array_push($where, 'AND reminderdate = "' . date('Y-m-d') . '%"');
    } elseif (startsWith($filter, 'consent_')) {
        array_push($where, 'AND ' . db_prefix() . 'leads.id IN (SELECT lead_id FROM ' . db_prefix() . 'consents WHERE purpose_id=' . strafter($filter, 'consent_') . ' and action="opt-in" AND date IN (SELECT MAX(date) FROM ' . db_prefix() . 'consents WHERE purpose_id=' . strafter($filter, 'consent_') . ' AND lead_id=' . db_prefix() . 'leads.id))');
    } elseif ($filter == "lapsed_lead") {
        array_push($where, 'AND ( reminderdate < now())');
    } elseif ($filter == "deleted") {
        array_push($where, " AND isDeleted = 'true'");
    }
}
if (!$filter || $filter != 'deleted') {
    array_push($where, " AND isDeleted = 'false'");
}
if (!$this->ci->input->post('custom_view') || $this->ci->input->post('custom_view') != "lapsed_lead") {
    // array_push($where, 'AND  (reminderdate >= (now() - interval 30 day) OR reminderdate is null)');
    //array_push($where, 'AND  reminderdate >= (now() - interval 30 day) OR reminderdate is null OR reminderdate = ""');
}

if (!$filter || ($filter && $filter != 'lost' && $filter != 'junk')) {
    array_push($where, 'AND lost = 0 AND junk = 0');
}

if (has_permission('leads', '', 'view') && $this->ci->input->post('assigned')) {
    array_push($where, 'AND assigned =' . $this->ci->input->post('assigned'));
}

if (
    $this->ci->input->post('status')
    && count($this->ci->input->post('status')) > 0
    && ($filter != 'lost' && $filter != 'junk')
) {
    array_push($where, 'AND status IN (' . implode(',', $this->ci->input->post('status')) . ')');
}

if (
    $this->ci->input->post('countries')
    && count($this->ci->input->post('countries')) > 0
) {
    array_push($where, 'AND country in (' . implode(',', $this->ci->input->post('countries')) . ')');
}
try {
    if (
        $this->ci->input->post('states')
        && count($this->ci->input->post('states')) > 0
    ) {
        array_push($where, "AND state in ("  . "'"  . implode("','", $this->ci->input->post('states')) . "')");
    }
} catch (Exception $e) {
    echo 'Message: ' . $e->getMessage();
}

if (
    $this->ci->input->post('cities')
    && count($this->ci->input->post('cities')) > 0
) {
    array_push($where, 'AND city in (' . "'" . implode("','", $this->ci->input->post('cities')) . "')");
}

if ($this->ci->input->post('source')) {
    array_push($where, 'AND source =' . $this->ci->input->post('source'));
}

if (!has_permission('leads', '', 'view')) {
    // array_push($where, 'AND (assigned =' . get_staff_user_id() . ' OR addedfrom = ' . get_staff_user_id() . ' OR is_public = 1)');
    array_push($where, 'AND (assigned =' . get_staff_user_id() . ' OR is_public = 1)');
}
if ($this->ci->input->post('folloup')) {
    $followupdate = implode('-', array_reverse(explode('-', $this->ci->input->post('folloup'))));

    array_push($where, ' AND reminderdate like "'  . $followupdate . '%"');
}
if ($this->ci->input->post('datefilter') &&  $this->ci->input->post('from_date') && $this->ci->input->post('to_date')) {
    $dtfilter = $this->ci->input->post('datefilter');
    $fromdate = implode('-', array_reverse(explode('-', $this->ci->input->post('from_date'))));
    $todate = implode('-', array_reverse(explode('-', $this->ci->input->post('to_date'))));

    array_push($where, " AND DATE(" . $dtfilter . ") BETWEEN '" . $fromdate . "' AND '" . $todate . "'");
}

array_push($where, 'AND is_vendor = "0"');

$aColumns = hooks()->apply_filters('leads_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$additionalColumns = hooks()->apply_filters('leads_table_additional_columns_sql', [
    'junk',
    'lost',
    'color',
    'status',
    'assigned',
    'lastname as assigned_lastname',
    db_prefix() . 'leads.addedfrom as addedfrom',
    '(SELECT count(leadid) FROM ' . db_prefix() . 'clients WHERE ' . db_prefix() . 'clients.leadid=' . db_prefix() . 'leads.id) as is_converted',
    'zip',
]);

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalColumns);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['id'] . '"><label></label></div>';

    $hrefAttr = 'href="' . admin_url('leads/index/' . $aRow['id']) . '" onclick="init_lead(' . $aRow['id'] . ');return false;"';
    $row[]    = '<a ' . $hrefAttr . '>' . $aRow['id'] . '</a>';

    $nameRow = '<a ' . $hrefAttr . '>' . $aRow['name'] . '</a>';

    $nameRow .= '<div class="row-options">';
    $nameRow .= '<a ' . $hrefAttr . '>' . _l('view') . '</a>';

    $locked = false;

    if ($aRow['is_converted'] > 0) {
        $locked = ((!is_admin() && $lockAfterConvert == 1) ? true : false);
    }

    if (!$locked) {
        $nameRow .= ' | <a href="' . admin_url('leads/index/' . $aRow['id'] . '?edit=true') . '" onclick="init_lead(' . $aRow['id'] . ', true);return false;">' . _l('edit') . '</a>';
    }

    if ($aRow['addedfrom'] == get_staff_user_id() || $has_permission_delete) {
        if ($aRow['isDeleted'] == "true") {
            $nameRow .= ' | <a href="' . admin_url('leads/restore/' . $aRow['id']) . '" class="_restore text-success">' . _l('restore') . '</a>';
        } else {
            $nameRow .= ' | <a href="' . admin_url('leads/delete/' . $aRow['id']) . '" class="_delete text-danger">' . _l('delete') . '</a>';
        }
    }
    $nameRow .= '</div>';


    $row[] = $nameRow;

    if (is_gdpr() && $consentLeads == '1') {
        $consentHTML = '<p class="bold"><a href="#" onclick="view_lead_consent(' . $aRow['id'] . '); return false;">' . _l('view_consent') . '</a></p>';
        $consents    = $this->ci->gdpr_model->get_consent_purposes($aRow['id'], 'lead');

        foreach ($consents as $consent) {
            $consentHTML .= '<p style="margin-bottom:0px;">' . $consent['name'] . (!empty($consent['consent_given']) ? '<i class="fa fa-check text-success pull-right"></i>' : '<i class="fa fa-remove text-danger pull-right"></i>') . '</p>';
        }
        $row[] = $consentHTML;
    }
    $lapsed = "";
    if ($aRow['lapselead'] == "10") {
        $lapsed = 'Lapsed lead';
    }

    $row[] = $aRow['company'] . '<p style="color: black;">' . $lapsed . '</p>';

    $row[] = ($aRow['email'] != '' ? '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>' : '');

    $row[] = ($aRow['phonenumber'] != '' ? '<a href="tel:' . $aRow['phonenumber'] . '">' . $aRow['phonenumber'] . '</a>' : '');

    if ($aRow['tags'] === "") {
        $row[] .= render_tags(array("No Products"));
    } else {
        $row[] .= render_tags($aRow['tags']);
    }

    if ($aRow['reminderdate'] != '') {
        $currentDate = date('Y-m-d');
        $dt = date('d/m/Y H:i:s', strtotime($aRow['reminderdate']));
        $row[] = '<p data-toggle="tooltip" data-title="' . $aRow['reminderdate'] . '" class="text-has-action is-date" data-original-title="" title="" style="color:black;">' . $aRow['reminderdate'] > $currentDate ? $dt : time_ago($aRow['reminderdate']) . '</p>';
    } else {
        $row[] = '';
    }

    $assignedOutput = '';
    if ($aRow['assigned'] != 0) {
        $full_name = $aRow['assigned_firstname'] . ' ' . $aRow['assigned_lastname'];

        $assignedOutput = '<a data-toggle="tooltip" data-title="' . $full_name . '" href="' . admin_url('profile/' . $aRow['assigned']) . '">' . staff_profile_image($aRow['assigned'], [
            'staff-profile-image-small',
        ]) . '</a>';

        // For exporting
        $assignedOutput .= '<span class="hide">' . $full_name . '</span>';
    }

    $row[] = $assignedOutput;

    if ($aRow['status_name'] == null) {
        if ($aRow['lost'] == 1) {
            $outputStatus = '<span class="label label-danger inline-block">' . _l('lead_lost') . '</span>';
        } elseif ($aRow['junk'] == 1) {
            $outputStatus = '<span class="label label-warning inline-block">' . _l('lead_junk') . '</span>';
        }
    } else {
        $outputStatus = '<span class="inline-block label label-' . (empty($aRow['color']) ? 'default' : '') . '" style="color:' . $aRow['color'] . ';border:1px solid ' . $aRow['color'] . '">' . $aRow['status_name'];
        if (!$locked) {
            $outputStatus .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
            $outputStatus .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsStatus-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
            $outputStatus .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';
            $outputStatus .= '</a>';

            $outputStatus .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' . $aRow['id'] . '">';
            foreach ($statuses as $leadChangeStatus) {
                if ($aRow['status'] != $leadChangeStatus['id']) {
                    $outputStatus .= '<li>
                  <a href="#" onclick="lead_mark_as(' . $leadChangeStatus['id'] . ',' . $aRow['id'] . '); return false;">
                     ' . $leadChangeStatus['name'] . '
                  </a>
               </li>';
                }
            }
            $outputStatus .= '</ul>';
            $outputStatus .= '</div>';
        }
        $outputStatus .= '</span>';
    }

    $row[] = $outputStatus;

    $row[] = $aRow['source_name'];

    $row[] = ($aRow['lastcontact'] == '0000-00-00 00:00:00' || !is_date($aRow['lastcontact']) ? '' : '<span data-toggle="tooltip" data-title="' . _dt($aRow['lastcontact']) . '" class="text-has-action is-date">' . time_ago($aRow['lastcontact']) . '</span>');

    $row[] = '<span data-toggle="tooltip" data-title="' . _dt($aRow['dateadded']) . '" class="text-has-action is-date">' . time_ago($aRow['dateadded']) . '</span>';

    $row[] = _dt($aRow['datedeleted']);
    $row[] = $aRow['deletedBy'];

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $row['DT_RowId'] = 'lead_' . $aRow['id'];

    $rowClass = get_lead_row_color_class(
        $aRow['lapselead'],
        $aRow['lastcontact'],
        $aRow['assigned'],
        get_staff_user_id()
    );

    if ($rowClass !== '') {
        $row['DT_RowClass'] = $rowClass;
    }

    if (isset($row['DT_RowClass'])) {
        $row['DT_RowClass'] .= ' has-row-options';
    } else {
        $row['DT_RowClass'] = 'has-row-options';
    }

    $row = hooks()->apply_filters('leads_table_row_data', $row, $aRow);

    $output['aaData'][] = $row;
}
