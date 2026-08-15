<?php

defined('BASEPATH') or exit('No direct script access allowed');

function app_init_admin_sidebar_menu_items()
{
    $CI = &get_instance();

    $CI->app_menu->add_sidebar_menu_item('dashboards', [
        'collapse' => true,
        'name' => "Dashboards",
        'position' => 1,
        'icon' => 'fa fa-home',
    ]);

    $CI->app_menu->add_sidebar_children_item('dashboards', [
        'name' => "Master Dashboard",
        'href' => admin_url(),
        'icon' => '',
        'position' => 1,
    ]);

    if (has_permission('lead_dashboard', '', 'view') || has_permission('lead_dashboard', '', 'view_own')) {
        $CI->app_menu->add_sidebar_children_item('dashboards', [
            'name' => "Lead Dashboard",
            'href' => admin_url('lead_dashboard'),
            'icon' => '',
            'position' => 2,
        ]);
    }

    if (has_permission('goals_dashboard', '', 'view') || has_permission('goals_dashboard', '', 'view_own')) {
        $CI->app_menu->add_sidebar_children_item('dashboards', [
            'name' => "Goals Dashboard",
            'href' => admin_url('goals_dashboard'),
            'position' => 3,
            'icon' => '',
        ]);
    }

    if (has_permission('meeting_dashboard', '', 'view')) {
        $pv_badge = "";
        $pv_count = get_count_today_plant_visit();
        if ($pv_count > 0) {
            $pv_badge = '<i class="fa fa-bell shake-animation text-warning" aria-hidden="true"></i>';
        }
        $CI->app_menu->add_sidebar_children_item('dashboards', [
            'name' => "Meeting Dashboard " . $pv_badge,
            'href' => admin_url('meeting_dashboard'),
            'position' => 4,
            'icon' => '',
        ]);
    }

    // if (has_permission('lead_dashboard', '', 'view')) {
    //     $CI->app_menu->add_sidebar_children_item('dashboards', [
    //         'name' => _l('leads_map'),
    //         'href' => admin_url('leads_map'),
    //         'position' => 5,
    //         'icon' => '',
    //     ]);
    // }

    $CI->app_menu->add_sidebar_menu_item('tasks', [
        'name' => _l('als_tasks'),
        'href' => admin_url('tasks'),
        'icon' => 'fa fa-tasks',
        'position' => 2,
    ]);

    if (has_permission('leads', '', 'view') || has_permission('leads', '', 'view_own')) {
        $CI->app_menu->add_sidebar_menu_item('leadsnew', [
            'name' => 'Leads',
            'href' => admin_url('leadsnew'),
            'icon' => 'fa fa-tty',
            'position' => 3,
        ]);
    }

    if (
        has_permission('customers', '', 'view')
        || (have_assigned_customers()
            || (!have_assigned_customers() && has_permission('customers', '', 'create')))
    ) {
        $CI->app_menu->add_sidebar_menu_item('customers', [
            'name' => _l('als_clients'),
            'href' => admin_url('clients'),
            'position' => 4,
            'icon' => 'fa fa-user-o',
        ]);
    }

    $vendor_permission = has_permission('vendors', '', 'view') || has_permission('vendors', '', 'view_own');
    $purchase_permission = has_permission('purchase', '', 'view') || has_permission('purchase', '', 'view_own');
    $debit_note_permission = has_permission('debit_notes', '', 'view') || has_permission('debit_notes', '', 'view_own');
    $product_stock_permission = has_permission('product_stock', '', 'view');
    $item_permission = has_permission('items', '', 'view');

    if ($vendor_permission || $purchase_permission || $debit_note_permission || $product_stock_permission || $item_permission) {
        $CI->app_menu->add_sidebar_menu_item('purchase-menu', [
            'collapse' => true,
            'name' => "Purchase",
            'position' => 5,
            'icon' => 'fa fa-shopping-cart',
        ]);
    }

    if (has_permission('vendors', '', 'view') || has_permission('vendors', '', 'view_own')) {
        $CI->app_menu->add_sidebar_children_item('purchase-menu', [
            'name' => _l('vendors'),
            'href' => admin_url('vendors'),
            'icon' => '',
            'position' => 1,
        ]);
    }
    if (has_permission('purchase', '', 'view') || has_permission('purchase', '', 'view_own')) {
        $CI->app_menu->add_sidebar_children_item('purchase-menu', [
            'name' => "Purchase Orders",
            'href' => admin_url('purchase'),
            'icon' => '',
            'position' => 2,
        ]);
    }

    if (has_permission('debit_notes', '', 'view') || has_permission('debit_notes', '', 'view_own')) {
        $CI->app_menu->add_sidebar_children_item('purchase-menu', [
            'slug' => 'debit-notes',
            'name' => "Debit Note",
            'href' => admin_url('debit_notes'),
            'position' => 3,
        ]);
    }

    if (has_permission('product_stock', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('purchase-menu', [
            'name' => "Product Stock",
            'href' => admin_url('product_stock'),
            'icon' => '',
            'position' => 4,
        ]);
    }

    if (has_permission('items', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('purchase-menu', [
            'slug' => 'items',
            'name' => "Product Master",
            'href' => admin_url('invoice_items'),
            'position' => 5,
        ]);
    }

    $proposal_badge = "";
    $sales_badge = "";
    $proposal_request_counts = get_count_proposal_download_request();
    if ($proposal_request_counts > 0) {
        $proposal_badge = '<span class="badge menu-badge bg-warning">' . $proposal_request_counts . '</span>';
        $sales_badge = ' <i class="fa fa-bell shake-animation text-warning" aria-hidden="true"></i>';
    }

    $CI->app_menu->add_sidebar_menu_item('sales', [
        'collapse' => true,
        'name' => _l('als_sales') . $sales_badge,
        'position' => 6,
        'icon' => 'fa fa-balance-scale',
    ]);

    if (
        (has_permission('proposals', '', 'view') || has_permission('proposals', '', 'view_own'))
        || (staff_has_assigned_proposals() && get_option('allow_staff_view_proposals_assigned') == 1)
    ) {
        $CI->app_menu->add_sidebar_children_item('sales', [
            'slug' => 'proposals',
            'name' => _l('proposals') . $proposal_badge,
            'href' => admin_url('proposals'),
            'position' => 1,
        ]);
    }

    if (
        (has_permission('invoices', '', 'view') || has_permission('invoices', '', 'view_own'))
        || (staff_has_assigned_invoices() && get_option('allow_staff_view_invoices_assigned') == 1)
    ) {
        $CI->app_menu->add_sidebar_children_item('sales', [
            'slug' => 'invoices',
            'name' => _l('invoices'),
            'href' => admin_url('invoices'),
            'position' => 2,
        ]);
    }

    if (
        has_permission('payments', '', 'view') || has_permission('invoices', '', 'view_own')
        || (get_option('allow_staff_view_invoices_assigned') == 1 && staff_has_assigned_invoices())
    ) {
        $CI->app_menu->add_sidebar_children_item('sales', [
            'slug' => 'payments',
            'name' => _l('payments'),
            'href' => admin_url('payments'),
            'position' => 3,
        ]);
    }

    if (has_permission('credit_notes', '', 'view') || has_permission('credit_notes', '', 'view_own')) {
        $CI->app_menu->add_sidebar_children_item('sales', [
            'slug' => 'credit_notes',
            'name' => _l('credit_notes'),
            'href' => admin_url('credit_notes'),
            'position' => 4,
        ]);
    }


    // if ((has_permission('estimates', '', 'view') || has_permission('estimates', '', 'view_own'))
    //     || (staff_has_assigned_estimates() && get_option('allow_staff_view_estimates_assigned') == 1)
    // ) {
    //     $CI->app_menu->add_sidebar_children_item('sales', [
    //         'slug' => 'estimates',
    //         'name' => _l('estimates'),
    //         'href' => admin_url('estimates'),
    //         'position' => 1,
    //     ]);
    // }

    if (has_permission('expenses', '', 'view') || has_permission('expenses', '', 'view_own')) {
        $CI->app_menu->add_sidebar_menu_item('expenses', [
            'name' => _l('expenses'),
            'collapse' => true,
            'icon' => 'fa fa-file-text-o',
            'position' => 7,
        ]);

        if (has_permission('expense_trip', '', 'view') || has_permission('expense_trip', '', 'view_own')) {
            $CI->app_menu->add_sidebar_children_item('expenses', [
                'slug' => 'expense-trip',
                'name' => "Trips",
                'href' => admin_url('expense_trip'),
                'position' => 1,
            ]);
        }

        if (has_permission('expense_advance', '', 'view') || has_permission('expense_advance', '', 'view_own')) {
            $CI->app_menu->add_sidebar_children_item('expenses', [
                'slug' => 'expense-advance',
                'name' => "Advance",
                'href' => admin_url('expense_advance'),
                'position' => 2,
            ]);
        }

        if (has_permission('expense_reports', '', 'view') || has_permission('expense_reports', '', 'view_own')) {
            $CI->app_menu->add_sidebar_children_item('expenses', [
                'slug' => 'expense-reports',
                'name' => "Reports",
                'href' => admin_url('expense_reports'),
                'position' => 3,
            ]);
        }

        $CI->app_menu->add_sidebar_children_item('expenses', [
            'slug' => 'expenses',
            'name' => _l('expenses'),
            'href' => admin_url('expenses'),
            'position' => 4,
        ]);
    }

    if (has_permission('contact_book', '', 'view') || has_permission('contact_book', '', 'view_own')) {
        $CI->app_menu->add_sidebar_menu_item('contact_book', [
            'name' => _l('contact_book'),
            'href' => admin_url('contact_book'),
            'position' => 8,
            'icon' => 'fa fa-address-book',
        ]);
    }

    if (has_permission('contracts', '', 'view') || has_permission('contracts', '', 'view_own')) {
        $CI->app_menu->add_sidebar_menu_item('contracts', [
            'name' => _l('contracts'),
            'href' => admin_url('contracts'),
            'icon' => 'fa fa-file',
            'position' => 9,
        ]);
    }

    if (has_permission('brochure', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('brochure', [
            'name' => 'Catalogue',
            'href' => admin_url('brochure'),
            'icon' => 'fa fa-file-text-o',
            'position' => 10,
        ]);
    }
    if (has_permission('tutorials_videos', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('videos', [
            'name' => "Videos",
            'href' => admin_url('tutorials_videos'),
            'icon' => 'fa fa-video-camera',
            'position' => 11,
        ]);

        // if (has_permission('tutorials_links', '', 'view')) {
        //     $CI->app_menu->add_sidebar_children_item('utilities', [
        //         'name' => "Tutorials Links",
        //         'href' => admin_url('tutorials_videos/links'),
        //         'icon' => '',
        //         'position' => 7,
        //     ]);
        // }
    }

    if (has_permission('product_presentation', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('product_presentation', [
            'name' => 'Presentation',
            'href' => admin_url('product_presentation'),
            'icon' => 'fa fa-file-text-o',
            'position' => 12,
        ]);
    }



    if (has_permission('reports', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('reports', [
            'collapse' => true,
            'name' => _l('als_reports'),
            'href' => admin_url('reports'),
            'icon' => 'fa fa-area-chart',
            'position' => 13,
        ]);
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug' => 'sales-reports',
            'name' => _l('als_reports_sales_submenu'),
            'href' => admin_url('reports/sales'),
            'position' => 1,
        ]);
        // $CI->app_menu->add_sidebar_children_item('reports', [
        //     'slug' => 'expenses-reports',
        //     'name' => _l('als_reports_expenses'),
        //     'href' => admin_url('reports/expenses'),
        //     'position' => 1,
        // ]);
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug' => 'expenses-vs-income-reports',
            'name' => _l('als_expenses_vs_income'),
            'href' => admin_url('reports/expenses_vs_income'),
            'position' => 2,
        ]);
        $CI->app_menu->add_sidebar_children_item('reports', [
            'slug' => 'leads-reports',
            'name' => _l('als_reports_leads_submenu'),
            'href' => admin_url('reports/leads'),
            'position' => 3,
        ]);

        if (is_admin()) {
            $CI->app_menu->add_sidebar_children_item('reports', [
                'slug' => 'timesheets-reports',
                'name' => _l('timesheets_overview'),
                'href' => admin_url('staff/timesheets?view=all'),
                'position' => 4,
            ]);
        }

        // $CI->app_menu->add_sidebar_children_item('reports', [
        //     'slug' => 'knowledge-base-reports',
        //     'name' => _l('als_kb_articles_submenu'),
        //     'href' => admin_url('reports/knowledge_base_articles'),
        //     'position' => 5,
        // ]);
    }

    // Utilities
    $CI->app_menu->add_sidebar_menu_item('utilities', [
        'collapse' => true,
        'name' => _l('als_utilities'),
        'position' => 14,
        'icon' => 'fa fa-cogs',
    ]);



    if (has_permission('bulk_pdf_exporter', '', 'view')) {


        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug' => 'bulk-pdf-exporter',
            'name' => _l('bulk_pdf_exporter'),
            'href' => admin_url('utilities/bulk_pdf_exporter'),
            'position' => 1,
        ]);
    }
    $CI->app_menu->add_sidebar_children_item('utilities', [
        'slug' => 'media',
        'name' => 'Sharing Folder',
        'href' => admin_url('utilities/media'),
        'position' => 0,
    ]);

    $CI->app_menu->add_sidebar_children_item('utilities', [
        'slug' => 'calendar',
        'name' => _l('als_calendar_submenu'),
        'href' => admin_url('utilities/calendar'),
        'position' => 2,
    ]);

    if (is_admin()) {
        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug' => 'announcements',
            'name' => _l('als_announcements_submenu'),
            'href' => admin_url('announcements'),
            'position' => 3,
        ]);

        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug' => 'activity-log',
            'name' => _l('als_activity_log_submenu'),
            'href' => admin_url('utilities/activity_log'),
            'position' => 4,
        ]);

        $CI->app_menu->add_sidebar_children_item('utilities', [
            'slug' => 'ticket-pipe-log',
            'name' => _l('ticket_pipe_log'),
            'href' => admin_url('utilities/pipe_log'),
            'position' => 5,
        ]);
    }



    if (has_permission('knowledge_base', '', 'view')) {
        $CI->app_menu->add_sidebar_children_item('utilities', [
            'name' => _l('als_kb'),
            'href' => admin_url('knowledge_base'),
            'icon' => '',
            'position' => 7,
        ]);
    }

    if (is_staff_member()) {
        $CI->app_menu->add_sidebar_menu_item('emails', [
            'name' => 'Emails',
            'href' => admin_url('webmails'),
            'icon' => 'fa fa-envelope',
            'position' => 15,
        ]);
    }

    if (has_permission('email_campaigns', '', 'view') || has_permission('email_campaigns', '', 'view_own')) {
        $CI->app_menu->add_sidebar_menu_item('email_campaigns', [
            'collapse' => true,
            'name' => _l('email_campaigns'),
            'position' => 16,
            'icon' => 'fa fa fa-bullhorn',
        ]);
        $CI->app_menu->add_sidebar_children_item('email_campaigns', [
            'name' => _l('email_templates'),
            'href' => admin_url('email_campaign_templates'),
            'icon' => '',
            'position' => 0,
        ]);
        $CI->app_menu->add_sidebar_children_item('email_campaigns', [
            'name' => _l('Email Lists'),
            'href' => admin_url('email_campaign_mail_list'),
            'icon' => '',
            'position' => 1,
        ]);
        if (has_permission('email_campaigns', '', 'view')) {
            $CI->app_menu->add_sidebar_children_item('email_campaigns', [
                'name' => _l('Custom Emails'),
                'href' => admin_url('email_campaigns_emails'),
                'icon' => '',
                'position' => 2,
            ]);
        }
        $CI->app_menu->add_sidebar_children_item('email_campaigns', [
            'name' => _l('manage_email_campaigns'),
            'href' => admin_url('email_campaigns'),
            'icon' => '',
            'position' => 3,
        ]);
    }

    $CI->app_menu->add_sidebar_menu_item('projects', [
        'name' => _l('projects'),
        'href' => admin_url('projects'),
        'icon' => 'fa fa-bars',
        'position' => 17,
    ]);

    if (has_permission('subscriptions', '', 'view') || has_permission('subscriptions', '', 'view_own')) {
        $CI->app_menu->add_sidebar_menu_item('subscriptions', [
            'name' => _l('subscriptions'),
            'href' => admin_url('subscriptions'),
            'icon' => 'fa fa-repeat',
            'position' => 18,
        ]);
    }

    if ((!is_staff_member() && get_option('access_tickets_to_none_staff_members') == 1) || is_staff_member()) {
        $CI->app_menu->add_sidebar_menu_item('support', [
            'name' => _l('support'),
            'href' => admin_url('tickets'),
            'icon' => 'fa fa-ticket',
            'position' => 19,
        ]);
    }

    // if (has_permission('tatatel_calllogs', '', 'view')) {
    //     $CI->app_menu->add_sidebar_menu_item('tatatel_calllogs', [
    //         'name' => _l('tatatel_calllogs'),
    //         'href' => admin_url('tatatel_calllogs'),
    //         'icon' => 'fa fa-file',
    //         'position' => 65,
    //     ]);
    // }


    // Setup menu
    if (has_permission('staff', '', 'view')) {
        $CI->app_menu->add_setup_menu_item('staff', [
            'name' => _l('als_staff'),
            'href' => admin_url('staff'),
            'position' => 5,
        ]);
    }

    if (is_admin()) {
        $CI->app_menu->add_setup_menu_item('customers', [
            'collapse' => true,
            'name' => _l('clients'),
            'position' => 10,
        ]);

        $CI->app_menu->add_setup_children_item('customers', [
            'slug' => 'customer-groups',
            'name' => _l('customer_groups'),
            'href' => admin_url('clients/groups'),
            'position' => 5,
        ]);
        $CI->app_menu->add_setup_menu_item('support', [
            'collapse' => true,
            'name' => _l('support'),
            'position' => 15,
        ]);

        $CI->app_menu->add_setup_children_item('support', [
            'slug' => 'departments',
            'name' => _l('acs_departments'),
            'href' => admin_url('departments'),
            'position' => 5,
        ]);
        $CI->app_menu->add_setup_children_item('support', [
            'slug' => 'tickets-predefined-replies',
            'name' => _l('acs_ticket_predefined_replies_submenu'),
            'href' => admin_url('tickets/predefined_replies'),
            'position' => 10,
        ]);
        $CI->app_menu->add_setup_children_item('support', [
            'slug' => 'tickets-priorities',
            'name' => _l('acs_ticket_priority_submenu'),
            'href' => admin_url('tickets/priorities'),
            'position' => 15,
        ]);
        $CI->app_menu->add_setup_children_item('support', [
            'slug' => 'tickets-statuses',
            'name' => _l('acs_ticket_statuses_submenu'),
            'href' => admin_url('tickets/statuses'),
            'position' => 20,
        ]);

        $CI->app_menu->add_setup_children_item('support', [
            'slug' => 'tickets-services',
            'name' => _l('acs_ticket_services_submenu'),
            'href' => admin_url('tickets/services'),
            'position' => 25,
        ]);
        $CI->app_menu->add_setup_children_item('support', [
            'slug' => 'tickets-spam-filters',
            'name' => _l('spam_filters'),
            'href' => admin_url('spam_filters/view/tickets'),
            'position' => 30,
        ]);

        $CI->app_menu->add_setup_menu_item('leads', [
            'collapse' => true,
            'name' => _l('acs_leads'),
            'position' => 20,
        ]);
        $CI->app_menu->add_setup_children_item('leads', [
            'slug' => 'leads-sources',
            'name' => _l('acs_leads_sources_submenu'),
            'href' => admin_url('leads/sources'),
            'position' => 5,
        ]);
        $CI->app_menu->add_setup_children_item('leads', [
            'slug' => 'leads-questionnaire-group',
            'name' => _l('questionnaire_group'),
            'href' => admin_url('leads_questionnaire_group'),
            'position' => 5,
        ]);
        $CI->app_menu->add_setup_children_item('leads', [
            'slug' => 'leads-statuses',
            'name' => _l('acs_leads_statuses_submenu'),
            'href' => admin_url('leads/statuses'),
            'position' => 10,
        ]);
        $CI->app_menu->add_setup_children_item('leads', [
            'slug' => 'leads-email-integration',
            'name' => _l('leads_email_integration'),
            'href' => admin_url('leads/email_integration'),
            'position' => 15,
        ]);
        $CI->app_menu->add_setup_children_item('leads', [
            'slug' => 'web-to-lead',
            'name' => _l('web_to_lead'),
            'href' => admin_url('leads/forms'),
            'position' => 20,
        ]);
        $CI->app_menu->add_setup_children_item('leads', [
            'slug' => 'ovf-terms-and-conditions',
            'name' => _l('office_visit_terms_and_conditions'),
            'href' => admin_url('leads_office_visitor_forms/terms_and_conditions'),
            'position' => 25,
        ]);
        $CI->app_menu->add_setup_children_item('leads', [
            'slug' => 'pvf-terms-and-conditions',
            'name' => _l('plant_visit_form_settings'),
            'href' => admin_url('leads_plant_visit_forms/plant_visit_settings'),
            'position' => 30,
        ]);
        $CI->app_menu->add_setup_children_item('leads', [
            'slug' => 'leads-form-images',
            'name' => _l('lead_form_images'),
            'href' => admin_url('leads_questionnaire_group/lead_inquiry_form_images'),
            'position' => 35,
        ]);

        $CI->app_menu->add_setup_menu_item('finance', [
            'collapse' => true,
            'name' => _l('acs_finance'),
            'position' => 25,
        ]);
        $CI->app_menu->add_setup_children_item('finance', [
            'slug' => 'taxes',
            'name' => _l('acs_sales_taxes_submenu'),
            'href' => admin_url('taxes'),
            'position' => 5,
        ]);
        $CI->app_menu->add_setup_children_item('finance', [
            'slug' => 'currencies',
            'name' => _l('acs_sales_currencies_submenu'),
            'href' => admin_url('currencies'),
            'position' => 10,
        ]);
        $CI->app_menu->add_setup_children_item('finance', [
            'slug' => 'payment-modes',
            'name' => _l('acs_sales_payment_modes_submenu'),
            'href' => admin_url('paymentmodes'),
            'position' => 15,
        ]);
        $CI->app_menu->add_setup_children_item('finance', [
            'slug' => 'expenses-categories',
            'name' => _l('acs_expense_categories'),
            'href' => admin_url('expenses/categories'),
            'position' => 20,
        ]);

        $CI->app_menu->add_setup_children_item('finance', [
            'slug' => 'expenses-merchants',
            'name' => "Expense Merchants",
            'href' => admin_url('expenses_merchants'),
            'position' => 25,
        ]);

        $CI->app_menu->add_setup_menu_item('contracts', [
            'collapse' => true,
            'name' => _l('acs_contracts'),
            'position' => 30,
        ]);

        $CI->app_menu->add_setup_children_item('contracts', [
            'slug' => 'contracts-types',
            'name' => _l('acs_contract_types'),
            'href' => admin_url('contracts/types'),
            'position' => 5,
        ]);

        $modules_name = _l('modules');

        if ($modulesNeedsUpgrade = $CI->app_modules->number_of_modules_that_require_database_upgrade()) {
            $modules_name .= '<span class="badge menu-badge bg-warning">' . $modulesNeedsUpgrade . '</span>';
        }

        // $CI->app_menu->add_setup_menu_item('modules', [
        //     'href' => admin_url('modules'),
        //     'name' => $modules_name,
        //     'position' => 35,
        // ]);

        $CI->app_menu->add_setup_menu_item('custom-fields', [
            'href' => admin_url('custom_fields'),
            'name' => _l('asc_custom_fields'),
            'position' => 45,
        ]);

        $CI->app_menu->add_setup_menu_item('gdpr', [
            'href' => admin_url('gdpr'),
            'name' => _l('gdpr_short'),
            'position' => 50,
        ]);

        $CI->app_menu->add_setup_menu_item('roles', [
            'href' => admin_url('roles'),
            'name' => _l('acs_roles'),
            'position' => 55,
        ]);

        $CI->app_menu->add_setup_menu_item('contact_book_category', [
            'href' => admin_url('contact_book_category'),
            'name' => _l('contact_book_category'),
            'position' => 60,
        ]);

        $CI->app_menu->add_setup_menu_item('sales_purchase_setting', [
            'collapse' => true,
            'name' => "Sales & Purchase Settings",
            'position' => 65,
        ]);

        $CI->app_menu->add_setup_children_item('sales_purchase_setting', [
            'slug' => 'proposal-settings',
            'name' => "Proposal Settings",
            'href' => admin_url('proposal_settings'),
            'position' => 5,
        ]);

        $CI->app_menu->add_setup_children_item('sales_purchase_setting', [
            'slug' => 'invoice-settings',
            'name' => "Invoice Settings",
            'href' => admin_url('invoice_settings'),
            'position' => 10,
        ]);

        $CI->app_menu->add_setup_children_item('sales_purchase_setting', [
            'slug' => 'purchase-settings',
            'name' => "Purchase Settings",
            'href' => admin_url('purchase_settings'),
            'position' => 15,
        ]);

        $CI->app_menu->add_setup_children_item('sales_purchase_setting', [
            'slug' => 'stock-settings',
            'name' => "Stock Settings",
            'href' => admin_url('stock_settings'),
            'position' => 15,
        ]);

        $CI->app_menu->add_setup_children_item('sales_purchase_setting', [
            'slug' => 'contract-settings',
            'name' => "Agreement Settings",
            'href' => admin_url('contract_settings'),
            'position' => 15,
        ]);

        /*             $CI->app_menu->add_setup_menu_item('api', [
                          'href'     => admin_url('api'),
                          'name'     => 'API',
                          'position' => 65,
                  ]);*/
    }

    if (has_permission('settings', '', 'view')) {
        $CI->app_menu->add_setup_menu_item('settings', [
            'href' => admin_url('settings'),
            'name' => _l('acs_settings'),
            'position' => 200,
        ]);
    }

    if (has_permission('email_templates', '', 'view')) {
        $CI->app_menu->add_setup_menu_item('email-templates', [
            'href' => admin_url('emails'),
            'name' => _l('acs_email_templates'),
            'position' => 40,
        ]);
    }

    if (has_permission('pdf_settings', '', 'view')) {
        $CI->app_menu->add_setup_menu_item('pdf-settings', [
            'href' => admin_url('pdfsettings'),
            'name' => _l('pdf_settings'),
            'position' => 45,
        ]);
    }

    if (is_admin()) {
        $CI->app_menu->add_setup_menu_item('mail-services', [
            'href' => admin_url('mailservices'),
            'name' => _l('mail_services'),
            'position' => 45,
        ]);
    }

    if (is_admin()) {
        $CI->app_menu->add_setup_menu_item('lead-source-apis', [
            'collapse' => true,
            'name' => "Lead Source APIs",
            'position' => 50,
        ]);
        $CI->app_menu->add_setup_children_item('lead-source-apis', [
            'slug' => 'lead-source-apis-settings',
            'name' => "APIs settings",
            'href' => admin_url('leadsource_apis/settings'),
            'position' => 5,
        ]);
        $CI->app_menu->add_setup_children_item('lead-source-apis', [
            'slug' => 'lead-source-apis-fetch-leads',
            'name' => "Fetch Leads",
            'href' => admin_url('leadsource_apis/fetch_leads'),
            'position' => 10,
        ]);
        $CI->app_menu->add_setup_children_item('lead-source-apis', [
            'slug' => 'lead-source-apis-leads-leads_history',
            'name' => "Leads History",
            'href' => admin_url('leadsource_apis/leads_history'),
            'position' => 15,
        ]);
        $CI->app_menu->add_setup_children_item('lead-source-apis', [
            'slug' => 'lead-source-google-sheets',
            'name' => "Google Sheets",
            'href' => admin_url('google_sheets/index'),
            'position' => 15,
        ]);
    }
}
