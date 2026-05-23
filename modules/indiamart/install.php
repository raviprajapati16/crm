<?php

defined('BASEPATH') or exit('No direct script access allowed');
$CI = & get_instance();
if (!$CI->db->table_exists(db_prefix() . 'indiamart_settings')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "indiamart_settings` (
	  `id` INT NOT NULL AUTO_INCREMENT , 
	  `indiamart_key` VARCHAR(150) NOT NULL , 
	  `indiamart_number` VARCHAR(50) NOT NULL , 
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
	$CI->db->query("INSERT INTO `".db_prefix() . 'indiamart_settings'."` (`id`, `indiamart_number`, `indiamart_key`) VALUES (NULL, '', '');");
}

if (!$CI->db->table_exists(db_prefix() . 'indiamart_leads')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "indiamart_leads` (
	  `id` INT NOT NULL AUTO_INCREMENT , 
	  `QUERY_ID` VARCHAR(100) NOT NULL , 
	  `lead_data` MEDIUMTEXT NOT NULL , 
	  `is_imported` TINYINT NOT NULL DEFAULT '0' ,
	  `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}