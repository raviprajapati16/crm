<?php
hooks()->add_action('app_init', 'my_change_default_url_to_admin');

function my_change_default_url_to_admin()
{
    $CI = &get_instance();

    if (!is_client_logged_in() && !$CI->uri->segment(1)) {
        redirect(site_url('admin/authentication'));
    }
}

function get_staff_for_notification($source_staff)
{
    $roles_setting = get_option('staff_notification_roles');
    $allowed_role_ids = !empty($roles_setting) ? unserialize($roles_setting) : [];
    
    if(!is_array($allowed_role_ids)) {
        $allowed_role_ids = [];
    }
    
    $filtered_notify_staff = [];
    foreach($source_staff as $s) {
        if(in_array($s['role'], $allowed_role_ids)) {
            $filtered_notify_staff[] = $s;
        }
    }
    
    return $filtered_notify_staff;
}

function has_explicit_financial_year_permission($staff_id = '') {
    $staff_id = $staff_id == '' ? get_staff_user_id() : $staff_id;
    $CI = &get_instance();
    
    $table = (strpos(db_prefix(), 'tbl') !== false) ? db_prefix() . 'staff_permissions' : 'staff_permissions';
    
    $CI->db->where('staff_id', $staff_id);
    $CI->db->where('feature', 'financial_years_filter');
    $CI->db->where('capability', 'view');
    if ($CI->db->count_all_results($table) > 0) {
        return true;
    }
    
    // Check if the permission exists explicitly in the staff's role.
    // This is needed if the admin checked the role permission but didn't check
    // the 'Update all staff permissions' checkbox.
    $staff = $CI->db->select('role')->where('staffid', $staff_id)->get(db_prefix() . 'staff')->row();
    if ($staff && $staff->role) {
        $role = $CI->db->where('roleid', $staff->role)->get(db_prefix() . 'roles')->row();
        if ($role && !empty($role->permissions)) {
            $permissions = unserialize($role->permissions);
            if (is_array($permissions) && isset($permissions['financial_years_filter']) && in_array('view', $permissions['financial_years_filter'])) {
                return true;
            }
        }
    }
    
    return false;
}
