CREATE TABLE `tblexpense_reimbursement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `type` enum('reimbursed','refunded') NOT NULL DEFAULT 'reimbursed',
  `date` date NOT NULL,
  `paid_through` int(11) DEFAULT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);


ALTER TABLE `tblexpenses` DROP `status`;