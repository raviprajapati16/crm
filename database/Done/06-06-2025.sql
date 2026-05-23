CREATE TABLE `tblextra_amount` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rel_type` enum('proposal','estimate','invoice') NOT NULL,
  `rel_id` int NOT NULL,
  `label` text,
  `amount` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

ALTER TABLE `tblproposals` ADD `taxable_amount` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `total`;