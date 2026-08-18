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
    $allowed_roles_names = ['Managing Director', 'Director', 'Sales & Marketing'];
    $CI =& get_instance();
    $CI->db->select('roleid');
    $CI->db->from(db_prefix() . 'roles');
    $CI->db->where_in('name', $allowed_roles_names);
    $role_ids_res = $CI->db->get()->result_array();
    
    $allowed_role_ids = [];
    foreach($role_ids_res as $r) {
        $allowed_role_ids[] = $r['roleid'];
    }
    
    $filtered_notify_staff = [];
    foreach($source_staff as $s) {
        if(in_array($s['role'], $allowed_role_ids)) {
            $filtered_notify_staff[] = $s;
        }
    }
    
    return $filtered_notify_staff;
}
