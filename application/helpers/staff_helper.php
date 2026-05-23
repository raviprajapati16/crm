<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @since  2.3.3
 * Get available staff permissions, modules can use the filter too to hook permissions
 * @param  array  $data additional data passed from view role.php and member.php
 * @return array
 */
function get_available_staff_permissions($data = [])
{
    $viewGlobalName = _l('permission_view') . ' (' . _l('permission_global') . ')';

    $allPermissionsArray = [
        'view_own' => _l('permission_view_own'),
        'view'     => $viewGlobalName,
        'create'   => _l('permission_create'),
        'edit'     => _l('permission_edit'),
        'delete'   => _l('permission_delete'),
    ];

    $withoutViewOwnPermissionsArray = [
        'view'   => $viewGlobalName,
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    $withNotApplicableViewOwn = array_merge(['view_own' => ['not_applicable' => true, 'name' => _l('permission_view_own')]], $withoutViewOwnPermissionsArray);

    $corePermissions = [
        'bulk_pdf_exporter' => [
            'name'         => _l('bulk_pdf_exporter'),
            'capabilities' => [
                'view' => $viewGlobalName,
            ],
        ],
        'contracts' => [
            'name'         => _l('contracts'),
            'capabilities' => $allPermissionsArray,
        ],
        'credit_notes' => [
            'name'         => _l('credit_notes'),
            'capabilities' => $allPermissionsArray,
        ],
        'debit_notes' => [
            'name'         => _l('debit_notes'),
            'capabilities' => $allPermissionsArray,
        ],
        'customers' => [
            'name'         => _l('clients'),
            'capabilities' => $withNotApplicableViewOwn,
            'help'         => [
                'view_own' => _l('permission_customers_based_on_admins'),
            ],
        ],
        'email_templates' => [
            'name'         => _l('email_templates'),
            'capabilities' => [
                'view' => $viewGlobalName,
                'edit' => _l('permission_edit'),
            ],
        ],
        'pdf_settings' => [
            'name'         => _l('pdf_settings'),
            'capabilities' => [
                'view' => $viewGlobalName,
                'edit' => _l('permission_edit'),
            ],
        ],
        'estimates' => [
            'name'         => _l('estimates'),
            'capabilities' => $allPermissionsArray,
        ],
        'expenses' => [
            'name'         => _l('expenses'),
            'capabilities' => $allPermissionsArray,
        ],
        'invoices' => [
            'name'         => _l('invoices'),
            'capabilities' => $allPermissionsArray,
        ],
        'items' => [
            'name'         => _l('items'),
            'capabilities' => $withoutViewOwnPermissionsArray,
        ],
        'knowledge_base' => [
            'name'         => _l('knowledge_base'),
            'capabilities' => $withoutViewOwnPermissionsArray,
        ],
        'payments' => [
            'name'         => _l('payments'),
            'capabilities' => $withNotApplicableViewOwn,
            'help'         => [
                'view_own' => _l('permission_payments_based_on_invoices'),
            ],
        ],
        'projects' => [
            'name'         => _l('projects'),
            'capabilities' => $withNotApplicableViewOwn,
            'help'         => [
                'view'     => _l('help_project_permissions'),
                'view_own' => _l('permission_projects_based_on_assignee'),
            ],
        ],
        'proposals' => [
            'name'         => _l('proposals'),
            'capabilities' => $allPermissionsArray,
        ],
        'reports' => [
            'name'         => _l('reports'),
            'capabilities' => [
                'view' => $viewGlobalName,
            ],
        ],
        'roles' => [
            'name'         => _l('roles'),
            'capabilities' => $withoutViewOwnPermissionsArray,
        ],
        'settings' => [
            'name'         => _l('settings'),
            'capabilities' => [
                'view' => $viewGlobalName,
                'edit' => _l('permission_edit'),
            ],
        ],
        'staff' => [
            'name'         => _l('staff'),
            'capabilities' => $withoutViewOwnPermissionsArray,
        ],
        'subscriptions' => [
            'name'         => _l('subscriptions'),
            'capabilities' => $allPermissionsArray,
        ],
        'tasks' => [
            'name'         => _l('tasks'),
            'capabilities' => $withNotApplicableViewOwn,
            'help'        => [
                'view'     => _l('help_tasks_permissions'),
                'view_own' => _l('permission_tasks_based_on_assignee'),
            ],
        ],
        'checklist_templates' => [
            'name'         => _l('checklist_templates'),
            'capabilities' => [
                'create' => _l('permission_create'),
                'delete' => _l('permission_delete'),
            ],
        ],
        'meeting_dashboard' => [
            'name'         => "Meeting Dashboard",
            'capabilities' => [
                'view' =>  "View",
            ]
        ],
        'email_campaigns' => [
            'name'         => "Email Campaigns",
            'capabilities' => $allPermissionsArray
        ],
        'product_presentation' => [
            'name'         => "Presentation",
            'capabilities' => [
                'create' => _l('permission_create'),
                'edit' => _l('permission_edit'),
                'view' =>  "View",
                'share' =>  "Share",
                'download' =>  "Download",
                'delete' => _l('permission_delete'),
            ]
        ],
        'tutorials_videos' => [
            'name'         => "Videos",
            'capabilities' => [
                'view' =>  _l('view'),
                'create' => _l('permission_create'),
                'edit' => _l('permission_edit'),
                'delete' => _l('permission_delete'),
            ]
        ],
        // 'tutorials_links' => [
        //     'name'         => "Tutorials Links",
        //     'capabilities' => [
        //         'view' =>  _l('view'),
        //         'edit' => _l('permission_edit'),
        //     ]
        // ],
        'brochure' => [
            'name'         => "Catalogue",
            'capabilities' => [
                'create' => _l('permission_create'),
                'edit' => _l('permission_edit'),
                'view' =>  "View",
                'share' =>  "Share",
                'download' =>  "Download",
                'delete' => _l('permission_delete'),
            ]
        ],
        'attachments' => [
            'name'         => "Attachments (For All Modules)",
            'capabilities' => [
                'delete' => _l('permission_delete'),
                'download' => "Download",
            ]
        ],
        'goals_dashboard' => [
            'name'         => "Goals Dashboard",
            'capabilities' => [
                'view' =>  "View (Global)",
                'view_own' =>  "View (Own)",
                'report_generate' =>  "Report Generate",
            ]
        ],
    ];

    $corePermissions['leads'] = [
        'name'         => _l('leads'),
        'capabilities' => [
            'view_own' => _l('permission_view_own'),
            'view'   => $viewGlobalName,
            'delete' => _l('permission_delete'),
            'export' => _l('Export'),
        ],
        'help' => [
            'view' => _l('help_leads_permission_view'),
        ],
    ];

    $corePermissions['vendors'] = [
        'name'         => _l('vendors'),
        'capabilities' => [
            'view_own' => _l('permission_view_own'),
            'view'   => $viewGlobalName,
            'create'   => _l('permission_create'),
            'delete' => _l('permission_delete'),
            'export' => _l('Export'),
        ],
        'help' => [
            'view' => _l('help_vendors_permission_view'),
        ],
    ];

    $corePermissions['lead_dashboard'] = [
        'name'         => "Lead Dashboard",
        'capabilities' => [
            'view_own' => _l('permission_view_own'),
            'view'   => $viewGlobalName,
        ]
    ];

    $corePermissions['leads_map'] = [
        'name'         => "Leads Map",
        'capabilities' => [
            'view'   => "View",
        ]
    ];

    $corePermissions['contact_book'] = [
        'name'         => "Contact Book",
        'capabilities' => [
            'view_own' => _l('permission_view_own'),
            'view'   => $viewGlobalName,
            'create'   => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ]
    ];

    $corePermissions['customer_media'] = [
        'name'         => "Customer > Media",
        'capabilities' => [
            'view'   => "View",
            'create'   => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ]
    ];

    $corePermissions['expense_trip'] = [
        'name'         => "Expense > Trip",
        'capabilities' => [
            'view_own' => _l('permission_view_own'),
            'view'   => $viewGlobalName,
            'create'   => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];

    $corePermissions['expense_advance'] = [
        'name'         => "Expense > Advance",
        'capabilities' => [
            'view_own' => _l('permission_view_own'),
            'view'   => $viewGlobalName,
            'create'   => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
            'approve_reject_payment' => "Approve/Reject Payment",
        ],
    ];

    $corePermissions['expense_reports'] = [
        'name'         => "Expense > Reports",
        'capabilities' => [
            'view_own' => _l('permission_view_own'),
            'view'   => $viewGlobalName,
            'create'   => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
            'approve_reject_report' => "Approve/Reject Report",
        ],
    ];

    $corePermissions['purchase'] = [
        'name'         => "Purchase Orders",
        'capabilities' => [
            'view_own' => _l('permission_view_own'),
            'view'   => $viewGlobalName,
            'create'   => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];

    $corePermissions['product_stock'] = [
        'name'         => "Product Stock",
        'capabilities' => [
            'view'   => "View",
        ],
    ];

    return hooks()->apply_filters('staff_permissions', $corePermissions, $data);
}
/**
 * Get staff by ID or current logged in staff
 * @param  mixed $id staff id
 * @return mixed
 */
function get_staff($id = null)
{
    if (empty($id) && isset($GLOBALS['current_user'])) {
        return $GLOBALS['current_user'];
    }

    // Staff not logged in
    if (empty($id)) {
        return null;
    }

    if (!class_exists('staff_model', false)) {
        get_instance()->load->model('staff_model');
    }

    return get_instance()->staff_model->get($id);
}

/**
 * Return staff profile image url
 * @param  mixed $staff_id
 * @param  string $type
 * @return string
 */
function staff_profile_image_url($staff_id, $type = 'small')
{
    $url = base_url('assets/images/user-placeholder.jpg');

    if ((string) $staff_id === (string) get_staff_user_id() && isset($GLOBALS['current_user'])) {
        $staff = $GLOBALS['current_user'];
    } else {
        $CI = &get_instance();
        $CI->db->select('profile_image')
            ->where('staffid', $staff_id);

        $staff = $CI->db->get(db_prefix() . 'staff')->row();
    }

    if ($staff) {
        if (!empty($staff->profile_image)) {
            $profileImagePath = 'uploads/staff_profile_images/' . $staff_id . '/' . $type . '_' . $staff->profile_image;
            if (file_exists($profileImagePath)) {
                $url = base_url($profileImagePath);
            }
        }
    }

    return $url;
}

/**
 * Staff profile image with href
 * @param  boolean $id        staff id
 * @param  array   $classes   image classes
 * @param  string  $type
 * @param  array   $img_attrs additional <img /> attributes
 * @return string
 */
function staff_profile_image($id, $classes = ['staff-profile-image'], $type = 'small', $img_attrs = [])
{
    $url = base_url('assets/images/user-placeholder.jpg');

    $id = trim($id);

    $_attributes = '';
    foreach ($img_attrs as $key => $val) {
        $_attributes .= $key . '=' . '"' . html_escape($val) . '" ';
    }

    $blankImageFormatted = '<img src="' . $url . '" ' . $_attributes . ' class="' . implode(' ', $classes) . '" />';

    if ((string) $id === (string) get_staff_user_id() && isset($GLOBALS['current_user'])) {
        $result = $GLOBALS['current_user'];
    } else {
        $CI     = &get_instance();
        $result = $CI->app_object_cache->get('staff-profile-image-data-' . $id);

        if (!$result) {
            $CI->db->select('profile_image,firstname,lastname');
            $CI->db->where('staffid', $id);
            $result = $CI->db->get(db_prefix() . 'staff')->row();
            $CI->app_object_cache->add('staff-profile-image-data-' . $id, $result);
        }
    }

    if (!$result) {
        return $blankImageFormatted;
    }

    if ($result && $result->profile_image !== null) {
        $profileImagePath = 'uploads/staff_profile_images/' . $id . '/' . $type . '_' . $result->profile_image;
        if (file_exists($profileImagePath)) {
            $profile_image = '<img ' . $_attributes . ' src="' . base_url($profileImagePath) . '" class="' . implode(' ', $classes) . '" />';
        } else {
            return $blankImageFormatted;
        }
    } else {
        $profile_image = '<img src="' . $url . '" ' . $_attributes . ' class="' . implode(' ', $classes) . '" />';
    }

    return $profile_image;
}

/**
 * Get staff full name
 * @param  string $userid Optional
 * @return string Firstname and Lastname
 */
function get_staff_full_name($userid = '')
{
    $tmpStaffUserId = get_staff_user_id();
    if ($userid == '' || $userid == $tmpStaffUserId) {
        if (isset($GLOBALS['current_user'])) {
            return $GLOBALS['current_user']->firstname . ' ' . $GLOBALS['current_user']->lastname;
        }
        $userid = $tmpStaffUserId;
    }

    $CI = &get_instance();

    $staff = $CI->app_object_cache->get('staff-full-name-data-' . $userid);

    if (!$staff) {
        $CI->db->where('staffid', $userid);
        $staff = $CI->db->select('firstname,lastname')->from(db_prefix() . 'staff')->get()->row();
        $CI->app_object_cache->add('staff-full-name-data-' . $userid, $staff);
    }

    return html_escape($staff ? $staff->firstname . ' ' . $staff->lastname : '');
}

/**
 * Get staff default language
 * @param  mixed $staffid
 * @return mixed
 */
function get_staff_default_language($staffid = '')
{
    if (!is_numeric($staffid)) {
        // checking for current user if is admin
        if (isset($GLOBALS['current_user'])) {
            return $GLOBALS['current_user']->default_language;
        }

        $staffid = get_staff_user_id();
    }
    $CI = &get_instance();
    $CI->db->select('default_language');
    $CI->db->from(db_prefix() . 'staff');
    $CI->db->where('staffid', $staffid);
    $staff = $CI->db->get()->row();
    if ($staff) {
        return $staff->default_language;
    }

    return '';
}

function get_staff_recent_search_history($staff_id = null)
{
    $recentSearches = get_staff_meta($staff_id ? $staff_id : get_staff_user_id(), 'recent_searches');

    if ($recentSearches == '') {
        $recentSearches = [];
    } else {
        $recentSearches = json_decode($recentSearches);
    }

    return $recentSearches;
}

function update_staff_recent_search_history($history, $staff_id = null)
{
    $totalRecentSearches = hooks()->apply_filters('total_recent_searches', 5);
    $history             = array_reverse($history);
    $history             = array_unique($history);
    $history             = array_splice($history, 0, $totalRecentSearches);

    update_staff_meta($staff_id ? $staff_id : get_staff_user_id(), 'recent_searches', json_encode($history));

    return $history;
}


/**
 * Check if user is staff member
 * In the staff profile there is option to check IS NOT STAFF MEMBER eq like contractor
 * Some features are disabled when user is not staff member
 * @param  string  $staff_id staff id
 * @return boolean
 */
function is_staff_member($staff_id = '')
{
    $CI = &get_instance();
    if ($staff_id == '') {
        if (isset($GLOBALS['current_user'])) {
            return $GLOBALS['current_user']->is_not_staff === '0';
        }
        $staff_id = get_staff_user_id();
    }

    $CI->db->where('staffid', $staff_id)
        ->where('is_not_staff', 0);

    return $CI->db->count_all_results(db_prefix() . 'staff') > 0 ? true : false;
}

function get_global_emails($type = "bcc")
{
    if ($type == "cc") {
        $emailsStr = get_option('global_cc_emails');
    } else {
        $emailsStr = get_option('global_bcc_emails');
    }
    if (!empty($emailsStr)) {
        return array_values(array_unique(array_filter(explode(",", $emailsStr))));
    }
    return false;
}

function is_super_admin()
{
    if (get_staff_user_id() == "1" || get_staff_user_id() == "4") {
        return true;
    } else {
        return false;
    }
}

function get_manager_assigned_staff_ids($staff_id = '', $implode = false)
{
    $CI = &get_instance();
    if ($staff_id == '') {
        $staff_id = get_staff_user_id();
    }

    $CI->db->select('*');

    if (is_numeric($staff_id)) {
        $CI->db->where("(managerid = '$staff_id' OR FIND_IN_SET('$staff_id', managerid))", null, false);
    } else {
        $staff_id = $CI->db->escape($staff_id);
        $CI->db->where("(managerid = $staff_id OR FIND_IN_SET($staff_id, managerid))", null, false);
    }

    $CI->db->where('active', 1);
    $staff = $CI->db->get(db_prefix() . 'staff')->result_array();

    $ids = [];
    if (!empty($staff)) {
        $ids =  array_column($staff, 'staffid');
    }
    array_push($ids, $staff_id);
    if ($implode) {
        $ids = implode(",", $ids);
    }
    return $ids;
}

function checkDuplicateActiveStaffUser($email, $staff_id = null)
{
    $CI = &get_instance();
    $CI->db->where('email', $email);
    $CI->db->where('active', 1);
    if (!empty($staff_id)) {
        $CI->db->where('staffid !=', $staff_id);
    }
    $count =  $CI->db->count_all_results(db_prefix() . 'staff');
    if ($count > 0) {
        return true;
    } else {
        return false;
    }
}

function get_available_manager_permissions_for_under_staff()
{
    $corePermissions = [
        'leads' => "Leads",
        'leads_dashboard' => "Lead Dashboard",
        'vendors' => "Vendors",
        'customers' => "Customers",
        'proposals' => "Proposals",
        'contracts' => "Contracts",
        'estimates' => "Estimates",
        'invoices' => "Invoices",
        'email_campaigns' => "Email Campaigns",
        'payments' => "Payments",
        'tasks' => "Tasks",
    ];

    return $corePermissions;
}

function manager_employee_data_access_permission_check($module)
{
    $CI = &get_instance();
    if (is_manager()) {
        $CI->db->select('*');
        $CI->db->from(db_prefix() . 'roles');
        $CI->db->where('roleid', 3);
        $query = $CI->db->get();
        $role = $query->row();
        $permissionModule = unserialize($role->employee_permissions);
        if (!empty($permissionModule)) {
            if (in_array($module, $permissionModule)) {
                return true;
            }
        }
    }
    return false;
}


function is_staff_in_sales_department($staff_id = null)
{
    return true;
    // if (empty($staff_id)) {
    //     $staff_id = get_staff_user_id();
    // }
    // $CI = &get_instance();
    // $CI->db->from(db_prefix() . 'staff_departments');
    // $CI->db->where('staffid', $staff_id);
    // $CI->db->where('departmentid', 1);
    // $count = $CI->db->count_all_results();
    // if ($count > 0) {
    //     return true;
    // }
    // return false;
}

function get_staff_designation($staff_id)
{
    if (empty($staff_id) || $staff_id == '0') {
        return "-";
    }
    $staff = get_staff($staff_id);
    if (!empty($staff)) {
        $CI = &get_instance();
        $CI->db->from(db_prefix() . 'job_position');
        $CI->db->where('position_id', $staff->job_position);
        $query = $CI->db->get();
        $row = $query->row();
        if (!empty($row)) {
            return $row->position_name;
        }
    }
    return "-";
}