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
