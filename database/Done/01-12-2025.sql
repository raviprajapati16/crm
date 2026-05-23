ALTER TABLE `tblleads` ADD `assigned_customer_id` INT NULL DEFAULT NULL AFTER `gst_in`, ADD `assigned_customer_status` INT NULL DEFAULT NULL AFTER `assigned_customer_id`, ADD `assigned_customer_last_updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `assigned_customer_status`, ADD `assigned_customer_last_updated_by` INT NULL DEFAULT NULL AFTER `assigned_customer_last_updated_at`;

INSERT INTO `tblleads_status` (`id`, `name`, `statusorder`, `color`, `isdefault`) VALUES (NULL, 'LEAD DISTRIBUTION', '1000', '#7cb342', '1');

ALTER TABLE `tblleads` CHANGE `assigned_customer_last_updated_by` `assigned_customer_last_updated_by` VARCHAR(255) NULL DEFAULT NULL;