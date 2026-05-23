CREATE TABLE `tblcustomer_media` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NOT NULL,
    `rel_type` VARCHAR(50) NOT NULL,
    `rel_id` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `created_by` INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
