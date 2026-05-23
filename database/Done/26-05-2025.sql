CREATE TABLE `tblexpense_trip` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(250) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `business_purpose` text,
  `country` int DEFAULT NULL,
  `visa_required` int DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `tblexpense_advance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `payment_mode` int DEFAULT NULL,
  `reference` varchar(250) DEFAULT NULL,
  `notes` text,
  `trip` int DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Pending',
  `status_changed_by` int DEFAULT NULL,
  `status_updated_at` timestamp NULL DEFAULT NULL,
  `reject_reason` text,
  `created_by` int DEFAULT NULL,
  `created_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
);


CREATE TABLE `tblexpense_merchant` (`id` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(250) NULL DEFAULT NULL , `details` TEXT NULL DEFAULT NULL , `created_by` INT NULL DEFAULT NULL , `created_at` TIMESTAMP NULL DEFAULT NULL , PRIMARY KEY (`id`));

CREATE TABLE `tblexpense_reports` (
  `id` int NOT NULL AUTO_INCREMENT,
  `report_name` varchar(250) DEFAULT NULL,
  `business_purpose` text,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'draft',
  `status_updated_by` int DEFAULT NULL,
  `status_updated_at` timestamp NULL DEFAULT NULL,
  `trip_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `tbloptions` (`id`, `name`, `value`, `autoload`) VALUES (NULL, 'expense_receipt_required', '1', '1');

INSERT INTO `tbloptions` (`id`, `name`, `value`, `autoload`) VALUES (NULL, 'expense_receipt_amount_threshold', '300', '1');


DROP TABLE `tblexpenses`;

  CREATE TABLE `tblexpenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` int NOT NULL,
  `report_id` int DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `merchant_id` int DEFAULT NULL,
  `note` text DEFAULT NULL,
  `billable` int DEFAULT '0',
  `reimbursement` int NOT NULL DEFAULT '0',
  `date` date DEFAULT NULL,
  `expense_city` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category` (`category`)
);

ALTER TABLE `tblexpenses` ADD `status` VARCHAR(100) NULL DEFAULT 'Unreported' AFTER `report_id`;