ALTER TABLE `tblexpense_advance` ADD `report_id` INT NULL DEFAULT NULL AFTER `trip`;

ALTER TABLE `tblexpense_reports` ADD `rejection_reason` TEXT NULL DEFAULT NULL AFTER `trip_id`;