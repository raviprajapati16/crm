INSERT INTO `tbloptions` (`name`, `value`, `autoload`) VALUES 
('predefined_clientnote_debit_note', '', 1),
('predefined_terms_debit_note', '', 1),
('next_debit_note_number', '2', 1),
('debit_note_prefix', 'DN-', 1),
('debit_note_number_decrement_on_delete', '1', 1),
('pdf_format_debit_note', 'A4-PORTRAIT', 1),
('show_pdf_signature_debit_note', '1', 0),
('show_debit_note_reminders_on_calendar', '1', 1),
('show_debits_applied_on_invoice', '1', 1),
('show_project_on_debit_note', '1', 1),
('debit_note_number_format', '1', 1);



CREATE TABLE `tbldebitnotes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vendorid` int NOT NULL,
  `deleted_vendor_name` varchar(100) DEFAULT NULL,
  `number` int NOT NULL,
  `prefix` varchar(50) DEFAULT NULL,
  `number_format` int NOT NULL DEFAULT '1',
  `datecreated` datetime NOT NULL,
  `date` date NOT NULL,
  `adminnote` text,
  `terms` text,
  `clientnote` text,
  `currency` int NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `total_tax` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total` decimal(15,2) NOT NULL,
  `adjustment` decimal(15,2) DEFAULT NULL,
  `addedfrom` int DEFAULT NULL,
  `status` int DEFAULT '1',
  `project_id` int NOT NULL DEFAULT '0',
  `discount_percent` decimal(15,2) DEFAULT '0.00',
  `discount_total` decimal(15,2) DEFAULT '0.00',
  `discount_type` varchar(30) NOT NULL,
  `billing_street` varchar(200) DEFAULT NULL,
  `billing_city` varchar(100) DEFAULT NULL,
  `billing_state` varchar(100) DEFAULT NULL,
  `billing_zip` varchar(100) DEFAULT NULL,
  `billing_country` int DEFAULT NULL,
  `shipping_street` varchar(200) DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_state` varchar(100) DEFAULT NULL,
  `shipping_zip` varchar(100) DEFAULT NULL,
  `shipping_country` int DEFAULT NULL,
  `include_shipping` tinyint(1) NOT NULL,
  `show_shipping_on_debit_note` tinyint(1) NOT NULL DEFAULT '1',
  `show_quantity_as` int NOT NULL DEFAULT '1',
  `reference_no` varchar(100) DEFAULT NULL,
  `deleted_by` varchar(100) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `currency` (`currency`),
  KEY `project_id` (`project_id`)
);


CREATE TABLE `tbldebitnote_refunds` (
  `id` int NOT NULL AUTO_INCREMENT,
  `debit_note_id` int NOT NULL,
  `staff_id` int NOT NULL,
  `refunded_on` date NOT NULL,
  `payment_mode` varchar(40) NOT NULL,
  `note` text,
  `amount` decimal(15,2) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `deleted_by` varchar(100) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `tbldebits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `purchase_id` int NOT NULL,
  `debit_id` int NOT NULL,
  `staff_id` int NOT NULL,
  `date` date NOT NULL,
  `date_applied` datetime NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_by` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `tblemailtemplates` (`emailtemplateid`, `type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES (NULL, 'debit_note', 'debit-note-send-to-vendor', 'english', 'Send Debit Note To Email', 'Debit Note With Number #{debit_note_number} Created', 'Dear {vendor_name}<br /><br />We have attached the debit note with number <strong>#{debit_note_number} </strong>for your reference.<br /><br /><strong>Date:</strong>&nbsp;{debit_note_date}<br /><strong>Total Amount:</strong>&nbsp;{debit_note_total}<br /><br /><span style=\"font-size: 12pt;\">Please contact us for more information.</span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname}', '', '0', '1', '0');

INSERT INTO `tblpdf_settings` (`id`, `rel_type`, `header`, `header_repeat`, `footer`, `watermark`, `watermark_type`, `updated_by`, `updated_at`) VALUES (NULL, 'debit-notes', '<table xss=\"removed\" width=\"737\" height=\"90\">\r\n<tbody>\r\n<tr>\r\n<td xss=\"removed\"><img src=\"https://farmworld.in/wp-content/uploads/2020/07/logo.png\" width=\"130\" height=\"120\" alt=\"\" xss=\"removed\" /></td>\r\n<td xss=\"removed\" scope=\"row\" style=\"text-align: right;\"><span xss=\"removed\"><strong>Farmworld Agrotech Pvt. Ltd.<br /></strong></span>Plot No. 373, Road - U, <br />Khirasara Industrial Estate Khirsara (Ranmalji), <br />Taluka: Lodhika Rajkot (Gujarat) INDIA - 360021.&nbsp;</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<hr />', '1', 'Farmworld Agrotech Pvt. Ltd.', 'logo-png-small.png', 'image', '1', '2025-06-27 12:07:37');

ALTER TABLE `tblitems` ADD `net_weight` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `hsn_code`, ADD `gross_weight` DECIMAL(15,2) NOT NULL DEFAULT '0.00' AFTER `net_weight`;