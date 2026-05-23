ALTER TABLE `tblproposals` ADD `proposal_number` VARCHAR(300) NULL DEFAULT NULL AFTER `id`;

ALTER TABLE `tblproposals` ADD `proposal_number_prefix` VARCHAR(500) NULL DEFAULT NULL AFTER `id`;