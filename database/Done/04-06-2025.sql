INSERT INTO `tbloptions` (`id`, `name`, `value`, `autoload`) VALUES (NULL, 'proposal_international_bank_details', 'Name : Farmworld Agrotech Pvt Ltd\r\nAccount No. : 4500000000\r\nBank Name : Kotak Mahindra Bank Ltd\r\nIFSC Code : KKBK0002792\r\nSwift Code : KKBKINBB\r\nAddress : GIDC Metoda, Rajkot (Gujarat)', '1'), (NULL, 'proposal_domestic_bank_details', 'Name : Farmworld Agrotech Pvt Ltd\r\nAccount No. : 39581986115\r\nBank Name : State Bank of India\r\nIFSC Code : SBIN0063758\r\nSwift Code : SBIN0063758\r\nAddress : SME Branch, Rajkot', '1');

ALTER TABLE `tblproposals` ADD `loading_place` VARCHAR(500) NULL DEFAULT NULL AFTER `download_allow_till`, ADD `discharge_place` VARCHAR(500) NULL DEFAULT NULL AFTER `loading_place`, ADD `payment_term` TEXT NULL DEFAULT NULL AFTER `discharge_place`, ADD `shipment_term` TEXT NULL DEFAULT NULL AFTER `payment_term`, ADD `delivery_term` TEXT NULL DEFAULT NULL AFTER `shipment_term`, ADD `notes` TEXT NULL DEFAULT NULL AFTER `delivery_term`;

ALTER TABLE `tblproposals` ADD `type` VARCHAR(100) NULL DEFAULT 'domestic' AFTER `notes`, ADD `bank_details` TEXT NULL DEFAULT NULL AFTER `type`;

INSERT INTO `tbloptions` (`id`, `name`, `value`, `autoload`) VALUES (NULL, 'company_pan_number', 'AADCF7767M', '1');

INSERT INTO `tbloptions` (`id`, `name`, `value`, `autoload`) VALUES (NULL, 'company_tan_number', 'RKTF00637A', '1');