DELETE FROM tblpdf_settings WHERE `tblpdf_settings`.`id` = 2;

ALTER TABLE `tblproposals` DROP `bank_details`;

ALTER TABLE `tblproposals` ADD `bank_ac_name` VARCHAR(250) NULL DEFAULT NULL AFTER `type`, ADD `bank_ac_no` VARCHAR(250) NULL DEFAULT NULL AFTER `bank_ac_name`, ADD `bank_name` VARCHAR(250) NULL DEFAULT NULL AFTER `bank_ac_no`, ADD `bank_ifsc_code` VARCHAR(250) NULL DEFAULT NULL AFTER `bank_name`, ADD `bank_swift_code` VARCHAR(250) NULL DEFAULT NULL AFTER `bank_ifsc_code`, ADD `bank_address` TEXT NULL DEFAULT NULL AFTER `bank_swift_code`;