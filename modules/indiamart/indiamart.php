<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Indiamart Leads API Module
Description: This Module Helps to get leads from your indiamart account and import in CRM
Version: 1.0.0
Requires at least: 2.3.*
Author: Asif Thebepotra
Author URI: https://about.me/asifthebepotra
*/

define('INDIAMART_MODULE_NAME', 'indiamart');
define('INDIAMART_FETCH_URL', 'https://mapi.indiamart.com/wservce/crm/crmListing/v2/?glusr_crm_key=%s');
//hooks()->add_action('admin_init', 'indiamart_init_menu_items');
/**
* Register activation module hook
*/
register_activation_hook(INDIAMART_MODULE_NAME, 'indiamart_activation_hook');


function indiamart_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}
/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(INDIAMART_MODULE_NAME, [INDIAMART_MODULE_NAME]);

function indiamart_init_menu_items(){
	if(is_admin()){
		$CI = &get_instance();
		$CI->app_menu->add_sidebar_menu_item('indiamart-options',[
			'collapse' => true,
			'name' => _l('indiamart'),
			'position'=>24,
			'icon'=>'fa fa-cubes'
		]);
		$CI->app_menu->add_sidebar_children_item('indiamart-options',[
			'slug' 		=> 'indiamart-fetch-leads',
			'name' 		=> _l('indiamart_fetch_leads'),
			'href'     	=> admin_url(INDIAMART_MODULE_NAME.'/fetch_leads'),
            'position' 	=> 0,
		]);
		$CI->app_menu->add_sidebar_children_item('indiamart-options',[
			'slug' 		=> 'indiamart-leads-history',
			'name' 		=> _l('indiamart_leads_history'),
			'href'     	=> admin_url(INDIAMART_MODULE_NAME.'/leads_history'),
            'position' 	=> 1,
		]);
		$CI->app_menu->add_sidebar_children_item('indiamart-options',[
			'slug' 		=> 'indiamart-setting',
			'name' 		=> _l('indiamart_settings'),
			'href'     	=> admin_url(INDIAMART_MODULE_NAME.'/settings'),
            'position' 	=> 2,
		]);
	}
}