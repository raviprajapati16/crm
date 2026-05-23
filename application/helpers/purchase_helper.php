<?php

defined('BASEPATH') or exit('No direct script access allowed');


function purchase_status_color_class($id, $replace_default_by_muted = false)
{
    if ($id == 1) {
        $class = 'default';
    } elseif ($id == 2) {
        $class = 'danger';
    } elseif ($id == 3) {
        $class = 'success';
    } elseif ($id == 4 || $id == 5) {
        // status sent and revised
        $class = 'info';
    } elseif ($id == 6) {
        $class = 'default';
    }
    if ($class == 'default') {
        if ($replace_default_by_muted == true) {
            $class = 'muted';
        }
    }

    return $class;
}

function format_purchase_status($status)
{
    $id = $status;
    $CI = &get_instance();
    $status = $CI->db->get_where(db_prefix() . 'purchase_status', ['id' => $id])->row();
    if (!empty($status)) {
        return "<span class='label purchase-status-{$id}' style='color:{$status->color}; border: 1px solid {$status->color}; padding: 5px; border-radius: 5px;'>{$status->name}</span>";
    }
    return 'N/A';
}

function purchase_number_prefix()
{
    $prefix = get_option('purchase_number_prefix');
    return replace_dynamic_prefix($prefix);
}

function format_purchase_number($id)
{
    $CI = &get_instance();
    $CI->db->where('id', $id);
    $purchase = $CI->db->get(db_prefix() . 'purchase')->row();
    if (!empty($purchase)) {
        return $purchase->purchase_number_prefix . $purchase->purchase_number;
    }
    return "N/A";
}


function get_purchase_item_taxes($itemid)
{
    $CI = &get_instance();
    $CI->db->where('itemid', $itemid);
    $CI->db->where('rel_type', 'purchase');
    $taxes = $CI->db->get(db_prefix() . 'item_tax')->result_array();
    $i     = 0;
    foreach ($taxes as $tax) {
        $taxes[$i]['taxname'] = $tax['taxname'] . '|' . $tax['taxrate'];
        $i++;
    }
    return $taxes;
}

function get_purchase_templates()
{
    $purchase_templates = [];
    if (is_dir(VIEWPATH . 'admin/purchase/templates')) {
        foreach (list_files(VIEWPATH . 'admin/purchase/templates') as $template) {
            $purchase_templates[] = $template;
        }
    }

    return $purchase_templates;
}



function staff_has_assigned_purchase($staff_id = '')
{
    $CI         = &get_instance();
    $staff_id = is_numeric($staff_id) ? $staff_id : get_staff_user_id();
    $cache    = $CI->app_object_cache->get('staff-total-assigned-purchase-' . $staff_id);
    if (is_numeric($cache)) {
        $result = $cache;
    } else {
        $result = total_rows(db_prefix() . 'purchase', ['assigned' => $staff_id]);
        $CI->app_object_cache->add('staff-total-assigned-purchase-' . $staff_id, $result);
    }

    return $result > 0 ? true : false;
}

function get_purchase_sql_where_staff($staff_id)
{
    $has_permission_view_own            = has_permission('purchase', '', 'view_own');
    $allow_staff_view_invoices_assigned = get_option('allow_staff_view_purchase_assigned');
    $whereUser = '';
    if ($has_permission_view_own) {
        if (manager_employee_data_access_permission_check("purchase")) {
            $whereUser = '( addedfrom IN (' . get_manager_assigned_staff_ids('', true) . ')';
            if ($allow_staff_view_invoices_assigned == 1) {
                $whereUser .= ' OR assigned IN (' . get_manager_assigned_staff_ids('', true) . ')';
            }
            $whereUser .= ')';
        } else {
            $whereUser = '( addedfrom=' . $staff_id;
            // $whereUser = '((' . db_prefix() . 'purchase.addedfrom=' . $staff_id . ' AND ' . db_prefix() . 'purchase.addedfrom IN (SELECT staff_id FROM ' . db_prefix() . 'staff_permissions WHERE feature = "purchase" AND capability="view_own"))';
            if ($allow_staff_view_invoices_assigned == 1) {
                $whereUser .= ' OR assigned=' . $staff_id;
            }
            $whereUser .= ')';
        }
    } else {
        $whereUser .= 'assigned=' . $staff_id;
    }

    return $whereUser;
}