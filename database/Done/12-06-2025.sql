ALTER TABLE `tblinvoices` CHANGE `number` `number` VARCHAR(500) NULL DEFAULT NULL;

ALTER TABLE `tblinvoices` CHANGE `prefix` `prefix` VARCHAR(500) NULL DEFAULT NULL;

ALTER TABLE `tblinvoices` ADD `loading_place` VARCHAR(500) NULL DEFAULT NULL AFTER `subscription_id`, ADD `discharge_place` VARCHAR(500) NULL DEFAULT NULL AFTER `loading_place`, ADD `payment_term` TEXT NULL DEFAULT NULL AFTER `discharge_place`, ADD `shipment_term` TEXT NULL DEFAULT NULL AFTER `payment_term`, ADD `delivery_term` TEXT NULL DEFAULT NULL AFTER `shipment_term`;

ALTER TABLE `tblinvoices` ADD `bank_ac_name` VARCHAR(250) NULL DEFAULT NULL AFTER `shipment_term`, ADD `bank_ac_no` VARCHAR(250) NULL DEFAULT NULL AFTER `bank_ac_name`, ADD `bank_name` VARCHAR(250) NULL DEFAULT NULL AFTER `bank_ac_no`, ADD `bank_ifsc_code` VARCHAR(250) NULL DEFAULT NULL AFTER `bank_name`, ADD `bank_swift_code` VARCHAR(250) NULL DEFAULT NULL AFTER `bank_ifsc_code`, ADD `bank_address` TEXT NULL DEFAULT NULL AFTER `bank_swift_code`;

ALTER TABLE `tblinvoices` ADD `transporter` VARCHAR(500) NULL DEFAULT NULL AFTER `bank_address`, ADD `lr_br_no` VARCHAR(500) NULL DEFAULT NULL AFTER `transporter`, ADD `vehicle_no` VARCHAR(500) NULL DEFAULT NULL AFTER `lr_br_no`;

ALTER TABLE `tblinvoices` ADD `type` INT NOT NULL DEFAULT '0' AFTER `id`;

ALTER TABLE `tblinvoices` ADD `taxable_amount` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `subtotal`;

ALTER TABLE `tblitemable` ADD `kind_of_packages` TEXT NOT NULL AFTER `hsn_code`, ADD `net_weight` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `kind_of_packages`, ADD `gross_weight` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `net_weight`;

ALTER TABLE `tblinvoices` ADD `total_packages` INT NOT NULL AFTER `delivery_term`, ADD `total_net_weight` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `total_packages`, ADD `total_gross_weight` INT NOT NULL DEFAULT '0.00' AFTER `total_net_weight`;

ALTER TABLE `tblinvoices` CHANGE `total_gross_weight` `total_gross_weight` DECIMAL(15,2) NOT NULL DEFAULT '0.00';