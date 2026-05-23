<?php

/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Products Management
Description: Products Management.
Version: 2.3.0
Requires at least: 2.3.*
*/

define('product_management_module_name','product_management');

hooks()->add_action('admin_init', 'product_management_init_menu_items');
/**

* Register activation module hook

*/

register_activation_hook(product_management_module_name, 'product_management_activation_hook');


function product_management_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

/**

* Register language files, must be registered if the module is using languages

*/

register_language_files(product_management_module_name, [product_management_module_name]);

function product_management_init_menu_items(){
    if(is_admin()){
		$CI = &get_instance();
		$CI->app_menu->add_sidebar_menu_item('product_management',[
			'name' => _l('Product Management'),
			'position'=>23,
			'icon'=>'fa fa-balance-scale',
            'href'     	=> admin_url(product_management_module_name.'/')
		]);
    }
}
