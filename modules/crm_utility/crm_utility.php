<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: CRM Utility
Description: This module helps to extend existing modules functionality with hooks
Version: 1.0.0
Requires at least: 2.3.*
Author: Asif Thebepotra
Author URI: https://about.me/asifthebepotra
*/

define('CRM_UTILITY_MODULE_NAME', 'crm_utility');

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(CRM_UTILITY_MODULE_NAME, [CRM_UTILITY_MODULE_NAME]);

/**
* Load the module helper
*/
$CI = & get_instance();
$CI->load->helper(CRM_UTILITY_MODULE_NAME . '/crm_utility');

/**
* Register activation module hook
*/
//register_activation_hook(CRM_UTILITY_MODULE_NAME, 'crm_utility_activation_hook');
// function crm_utility_activation_hook()
// {
//     require_once(__DIR__ . '/install.php');
// }

/**
* Get Interface HTML for Leads Section
**/
function fetch_leads_interface_data($params){
    $CI = &get_instance();
    if(isset($params['show'])){
        $html = '';
        $viewData = $params;
        switch ($params['show']) {
            case 'dropdown':
                $CI->load->model(CRM_UTILITY_MODULE_NAME.'/crm_utility_model');
                $viewData['tags'] = $CI->crm_utility_model->getTags('lead');
                break;
            default:
                // code...
                break;
        }
        $CI->load->view(CRM_UTILITY_MODULE_NAME."/admin/leads_interface",$viewData);
    }
}
hooks()->add_action('init_leads_interface', 'fetch_leads_interface_data');

/*
* Update Where Condition In Leads
*/
function leads_table_additional_where($oWhere){
    $CI = &get_instance();
    $post = $CI->input->post();
    $addtionalKeys = ['view_products'];
    $tags = db_prefix()."tags";
    foreach ($addtionalKeys as $key) {
        if(isset($post[$key]) && !empty($post[$key])){
            $whereVal = '';
            if(is_array($post[$key])){
                $tag_products = array_filter($post[$key]);
                $whereVal = implode(",", $tag_products);
            }
            array_push($oWhere, "AND tags IN($whereVal)");
        }
    }
    return $oWhere;
}
//hooks()->add_action('leads_table_additional_where', 'leads_table_additional_where');

function leads_table_additional_columns_sql($columns){
    $tags = db_prefix()."tags";
    $newColumns = ["{$tags}.id as tagId"];
    //return array_merge($columns,$newColumns);
    return $columns;
}
//hooks()->add_action('leads_table_additional_columns_sql', 'leads_table_additional_columns_sql');