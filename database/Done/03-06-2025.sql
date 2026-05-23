ALTER TABLE `tblcontact_book` ADD `country_dial_code` VARCHAR(20) NULL DEFAULT NULL AFTER `email`;

ALTER TABLE `tblitems` ADD `hsn_code` VARCHAR(200) NULL DEFAULT NULL AFTER `rate_currency_2`;

ALTER TABLE `tblitemable` ADD `hsn_code` VARCHAR(200) NULL DEFAULT NULL AFTER `sub_group_id`;