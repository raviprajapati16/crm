<?php
defined('BASEPATH') or exit('No direct script access allowed');

$has_permission_delete = has_permission('hrm', '', 'delete');
$has_permission_edit   = has_permission('hrm', '', 'edit');
$has_permission_create = has_permission('hrm', '', 'create');

$custom_fields = get_custom_fields('staff', [
    'show_on_table' => 1,
]);
$aColumns = [
    db_prefix() . 'staff.staffid',
    'staff_identifi',
    "firstname",
    'email',
    db_prefix() . 'staff_contract.contract_status',
    'doj',
    'birthday',
    'sex',
    db_prefix() . 'roles.name',
    'last_login',
    'active',
    'status_work',
];
$sIndexColumn = 'staffid';
$sTable       = db_prefix() . 'staff';
$join         = [
    'LEFT JOIN ' . db_prefix() . 'roles ON ' . db_prefix() . 'roles.roleid = ' . db_prefix() . 'staff.role',
    'LEFT JOIN ' . db_prefix() . 'staff_contract ON ' . db_prefix() . 'staff_contract.staff = ' . db_prefix() . 'staff.staffid AND ' . db_prefix() . 'staff_contract.deleted_at IS NULL'
];

$i            = 0;
foreach ($custom_fields as $field) {
    $select_as = 'cvalue_' . $i;
    if ($field['type'] == 'date_picker' || $field['type'] == 'date_picker_time') {
        $select_as = 'date_picker_cvalue_' . $i;
    }
    array_push($aColumns, 'ctable_' . $i . '.value as ' . $select_as);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $i . ' ON ' . db_prefix() . 'staff.staffid = ctable_' . $i . '.relid AND ctable_' . $i . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $i . '.fieldid=' . $field['id']);
    $i++;
}
// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$where = hooks()->apply_filters('staff_table_sql_where', []);
$where = array();

if ($this->ci->input->post('exclude_inactive')) {
    array_push($where, 'AND active = 1');
} else {
    array_push($where, 'AND active IN (0,1)');
}

$department_id = $this->ci->input->post('hrm_deparment');

if (isset($department_id) && strlen($department_id) > 0) {

    $where[] = ' AND departmentid IN (select
        departmentid
        from    (select * from ' . db_prefix() . 'departments
        order by ' . db_prefix() . 'departments.parent_id, ' . db_prefix() . 'departments.departmentid) departments_sorted,
        (select @pv := ' . $department_id . ') initialisation
        where   find_in_set(parent_id, @pv)
        and     length(@pv := concat(@pv, ",", departmentid)) OR departmentid = ' . $department_id . ')';
}

if ($this->ci->input->post('status_work')) {
    $where_status = '';
    $status = $this->ci->input->post('status_work');
    foreach ($status as $statues) {
        if ($status != '') {
            if ($where_status == '') {
                $where_status .= ' status_work = "' . $statues . '"';
            } else {
                $where_status .= ' or status_work = "' . $statues . '"';
            }
        }
    }
    if ($where_status != '') {

        array_push($where, 'AND ' . $where_status);
    }
}

if ($this->ci->input->post('contract_status')) {
    $where_status = '';
    $status = $this->ci->input->post('contract_status');
    $include_null = false;

    foreach ($status as $statues) {
        if (!empty($statues)) {
            if ($statues == "Permanent") {
                $include_null = true;
            }
            if (empty($where_status)) {
                $where_status .= ' contract_status = "' . $statues . '"';
            } else {
                $where_status .= ' OR contract_status = "' . $statues . '"';
            }
        }
    }

    if ($include_null) {
        $where_status = '(contract_status IS NULL OR ' . $where_status . ')';
    }

    if (!empty($where_status)) {
        array_push($where, 'AND ' . $where_status);
    }
}

if ($this->ci->input->post('manager_filter')) {
    $where_status = '';
    $manager_id = $this->ci->input->post('manager_filter');
    if (!empty($manager_id)) {
        $managerWhere = "(managerid = '$manager_id' OR FIND_IN_SET('$manager_id', managerid))";
        array_push($where, 'AND ' . $managerWhere);
    }
}

if ($this->ci->input->post('staff_role')) {
    $where_role = '';
    $staff_role      = $this->ci->input->post('staff_role');
    foreach ($staff_role as $staff_id) {
        if ($staff_id != '') {
            if ($where_role == '') {
                $where_role .= '( ' . db_prefix() . 'staff.role = ' . $staff_id;
            } else {
                $where_role .= ' or ' . db_prefix() . 'staff.role = ' . $staff_id;
            }
        }
    }

    if ($where_role != '') {
        $where_role .= ' )';
        if ($where_role != '') {
            array_push($where, 'AND ' . $where_role);
        } else {
            array_push($where, $where_role);
        }
    }
}
array_push($where, 'AND datedeleted IS NULL');

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'profile_image',
    'CONCAT(TRIM(firstname), " ", TRIM(lastname))',
    db_prefix() . 'staff.staffid',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
            $_data = $aRow[strafter($aColumns[$i], 'as ')];
        } else {
            $_data = $aRow[$aColumns[$i]];
        }
        if ($aColumns[$i] == db_prefix() . 'staff.staffid') {
            $_data = $aRow[db_prefix() . 'staff.staffid'];
        } elseif ($aColumns[$i] == 'staff_identifi') {
            $_data = $aRow['staff_identifi'];
        } elseif ($aColumns[$i] == 'birthday') {
            $_data = _d($aRow['birthday']);
        } elseif ($aColumns[$i] == db_prefix() . 'staff_contract.contract_status') {
            if (empty($aRow[db_prefix() . 'staff_contract.contract_status'])) {
                $aRow[db_prefix() . 'staff_contract.contract_status'] = "Permanent";
            }
            if ($aRow[db_prefix() . 'staff_contract.contract_status'] == "Permanent") {
                $_data = "<span class='label label-success'>" . $aRow[db_prefix() . 'staff_contract.contract_status'] . "</span>";
            } else if ($aRow[db_prefix() . 'staff_contract.contract_status'] == "Probation" || $aRow[db_prefix() . 'staff_contract.contract_status'] == "Bond") {
                $_data = "<span class='label label-warning'>" . $aRow[db_prefix() . 'staff_contract.contract_status'] . "</span>";
            }
        } elseif ($aColumns[$i] == 'doj') {
            $_data = _d($aRow['doj']);
        } elseif ($aColumns[$i] == 'last_login') {
            $_data = _d($aRow['last_login']);
        } elseif ($aColumns[$i] == 'sex') {
            $_data = _l($aRow['sex']);
        } elseif ($aColumns[$i] == 'status_work') {
            $_data = _l($aRow['status_work']);
        } elseif ($aColumns[$i] == 'active') {
            $checked = '';
            if ($aRow['active'] == 1) {
                $checked = 'checked';
            }

            $is_disabled = "";
            if (($aRow[db_prefix() . 'staff.staffid'] == get_staff_user_id() || (is_admin($aRow[db_prefix() . 'staff.staffid']) || !has_permission('hrm', '', 'edit')) && !is_admin())) {
                $is_disabled = "disabled";
            }

            if (checkDuplicateActiveStaffUser($aRow['email'], $aRow[db_prefix() . 'staff.staffid'])) {
                $is_disabled = "disabled";
            }

            $_data = '<div class="onoffswitch">
                <input type="checkbox" ' . $is_disabled . ' data-switch-url="' . admin_url() . 'staff/change_staff_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow[db_prefix() . 'staff.staffid'] . '" data-id="' . $aRow[db_prefix() . 'staff.staffid'] . '" ' . $checked . '>
                <label class="onoffswitch-label" for="c_' . $aRow[db_prefix() . 'staff.staffid'] . '"></label>
            </div>';

            // For exporting
            $_data .= '<span class="hide">' . ($checked == 'checked' ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';
        } elseif ($aColumns[$i] == "firstname") {
            $_data = '<a href="' . admin_url('hrm/member/' . $aRow[db_prefix() . 'staff.staffid']) . '">' . staff_profile_image($aRow[db_prefix() . 'staff.staffid'], [
                'staff-profile-image-small',
            ]) . '</a>';
            $_data .= ' <a href="' . admin_url('hrm/member/' . $aRow[db_prefix() . 'staff.staffid']) . '">' . $aRow['CONCAT(TRIM(firstname), " ", TRIM(lastname))'] . '</a>';

            $_data .= '<div class="row-options">';

            if (has_permission('hrm', '', 'edit')) {
                $_data .= '<a href="' . admin_url('hrm/member/' . $aRow[db_prefix() . 'staff.staffid']) . '" class="text-danger">' . _l('edit') . '</a>';
            }



            if (has_permission('hrm', '', 'delete')) {
                if ($has_permission_delete && $output['iTotalRecords'] > 1 && $aRow[db_prefix() . 'staff.staffid'] != get_staff_user_id()) {
                    $_data .= ' | <a href="#" onclick="delete_staff_member(' . $aRow[db_prefix() . 'staff.staffid'] . '); return false;" class="text-danger">' . _l('delete') . '</a>';
                }
            }

            $_data .= '</div>';
        } elseif ($aColumns[$i] == 'email') {
            $_data = '<a href="mailto:' . $_data . '">' . $_data . '</a>';
        } elseif ($aColumns[$i] == db_prefix() . 'staff_departments.departmentid') {
            if ($aRow[db_prefix() . 'staff_departments.departmentid'] != '') {
                $team = $this->ci->hrm_model->get_department_name($aRow[db_prefix() . 'staff_departments.departmentid']);

                $str = '';
                $j = 0;

                foreach ($team as $value) {
                    $j++;
                    $str .= '<span class="label label-tag tag-id-1"><span class="tag">' . $value['name'] . '</span><span class="hide">, </span></span>&nbsp';

                    if ($j % 2 == 0) {
                        $str .= '<br><br/>';
                    }
                }
                $_data = $str;
            } else {
                $_data = '';
            }
        } else {
            if (strpos($aColumns[$i], 'date_picker_') !== false) {
                $_data = (strpos($_data, ' ') !== false ? _dt($_data) : _d($_data));
            }
        }
        $row[] = $_data;
    }

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
