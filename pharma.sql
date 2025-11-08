-- c:\xampp\htdocs\MyPharmaV1\migrations\complete_setup.sql
-- MyPharma Complete Database Setup for mypharma_v1
-- Simplified working version

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `mypharma_v1` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `mypharma_v1`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('pending','user','staff','admin') NOT NULL DEFAULT 'pending',
  `pharmacy_name` varchar(100) DEFAULT NULL,
  `pharmacy_address` text DEFAULT NULL,
  `pharmacy_lat` decimal(10,8) DEFAULT NULL,
  `pharmacy_lng` decimal(11,8) DEFAULT NULL,
  `pharmacy_contact` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample users
INSERT IGNORE INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Admin User', 'admin@mypharma.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(2, 'Staff User', 'staff@mypharma.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff'),
(3, 'Regular User', 'user@mypharma.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- --------------------------------------------------------
-- Table structure for table `pharmacies`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `pharmacies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lng` decimal(11,8) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample pharmacies
INSERT IGNORE INTO `pharmacies` (`id`, `name`, `address`, `lat`, `lng`, `contact`, `verified`) VALUES
(1, 'Mercury Drug - Pagadian City', 'Rizal Avenue, Pagadian City, Zamboanga del Sur', '7.82300000', '123.43000000', '09123456789', 1),
(2, 'Watson''s Pharmacy - Pagadian', 'Gaisano Mall Pagadian, Rizal Avenue Extension', '7.82400000', '123.43100000', '09123456790', 1),
(3, 'Rose Pharmacy - Balangasan', 'Balangasan District, Pagadian City', '7.82500000', '123.43200000', '09123456791', 1),
(4, 'Healthfirst Pharmacy', 'San Jose District, Pagadian City', '7.82600000', '123.43300000', '09123456792', 1),
(5, 'Generics Pharmacy', 'Tikling, Pagadian City', '7.82700000', '123.43400000', '09123456793', 1),
(6, 'MediCare Drugstore', 'Bolong Proper, Pagadian City', '7.82800000', '123.43500000', '09123456794', 1),
(7, 'Life Pharmacy', 'Culianan, Pagadian City', '7.82900000', '123.43600000', '09123456795', 1),
(8, 'Family Pharmacy', 'Santa Maria, Pagadian City', '7.83000000', '123.43700000', '09123456796', 1),
(9, 'Wellness Pharmacy', 'Labuan, Pagadian City', '7.83100000', '123.43800000', '09123456797', 1),
(10, 'Sunrise Drugstore', 'Ditucalan, Pagadian City', '7.83200000', '123.43900000', '09123456798', 1),
(11, 'Golden Cross Pharmacy', 'Sibugay, Pagadian City', '7.83300000', '123.44000000', '09123456799', 1),
(12, 'Nature''s Best Pharmacy', 'Ipil Road, Pagadian City', '7.83400000', '123.44100000', '09123456800', 1),
(13, 'City Pharmacy', 'Plaridel, Pagadian City', '7.83500000', '123.44200000', '09123456801', 1),
(14, 'MedExpress', 'Buenavista, Pagadian City', '7.83600000', '123.44300000', '09123456802', 1),
(15, 'J.H. Cerilles Pharmacy', 'Balangasan District, Pagadian City', '7.82511000', '123.43115000', '09123456803', 1);

-- --------------------------------------------------------
-- SIMPLIFIED: Table structure for table `medicines`
-- This version uses simple ALTER TABLE with IF NOT EXISTS
-- --------------------------------------------------------

-- Create the medicines table with basic structure
CREATE TABLE IF NOT EXISTS `medicines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100),
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add missing columns one by one (safe method)
ALTER TABLE `medicines` 
ADD COLUMN IF NOT EXISTS `scientific_name` varchar(100),
ADD COLUMN IF NOT EXISTS `brand_name` varchar(100),
ADD COLUMN IF NOT EXISTS `image_path` varchar(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `manufacturer` varchar(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `description` text DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `category` varchar(100) DEFAULT NULL;

-- Insert sample medicines (simple approach)
INSERT IGNORE INTO `medicines` (`id`, `name`) VALUES
(1, 'Biogesic'),
(2, 'Advil'),
(3, 'Amoxil'),
(4, 'Imodium'),
(5, 'Bayer Aspirin'),
(6, 'Zyrtec'),
(7, 'Claritin'),
(8, 'Prilosec'),
(9, 'Glucophage'),
(10, 'Lipitor'),
(11, 'Cozaar'),
(12, 'Norvasc'),
(13, 'Synthroid'),
(14, 'Ventolin'),
(15, 'Deltasone'),
(16, 'Valium'),
(17, 'Zoloft'),
(18, 'Prozac'),
(19, 'Lexapro'),
(20, 'Ultram');

-- Update with additional data
UPDATE `medicines` SET 
  `scientific_name` = CASE `id`
    WHEN 1 THEN 'Paracetamol' WHEN 2 THEN 'Ibuprofen' WHEN 3 THEN 'Amoxicillin'
    WHEN 4 THEN 'Loperamide' WHEN 5 THEN 'Aspirin' WHEN 6 THEN 'Cetirizine'
    WHEN 7 THEN 'Loratadine' WHEN 8 THEN 'Omeprazole' WHEN 9 THEN 'Metformin'
    WHEN 10 THEN 'Atorvastatin' WHEN 11 THEN 'Losartan' WHEN 12 THEN 'Amlodipine'
    WHEN 13 THEN 'Levothyroxine' WHEN 14 THEN 'Albuterol' WHEN 15 THEN 'Prednisone'
    WHEN 16 THEN 'Diazepam' WHEN 17 THEN 'Sertraline' WHEN 18 THEN 'Fluoxetine'
    WHEN 19 THEN 'Escitalopram' WHEN 20 THEN 'Tramadol' ELSE `name` END,
  `brand_name` = `name`,
  `manufacturer` = CASE `id`
    WHEN 1 THEN 'United Laboratories' WHEN 2 THEN 'Pfizer' WHEN 3 THEN 'GlaxoSmithKline'
    WHEN 4 THEN 'Johnson & Johnson' WHEN 5 THEN 'Bayer' WHEN 6 THEN 'Johnson & Johnson'
    WHEN 7 THEN 'Bayer' WHEN 8 THEN 'AstraZeneca' WHEN 9 THEN 'Merck'
    WHEN 10 THEN 'Pfizer' WHEN 11 THEN 'Merck' WHEN 12 THEN 'Pfizer'
    WHEN 13 THEN 'AbbVie' WHEN 14 THEN 'GlaxoSmithKline' WHEN 15 THEN 'Pfizer'
    WHEN 16 THEN 'Roche' WHEN 17 THEN 'Pfizer' WHEN 18 THEN 'Eli Lilly'
    WHEN 19 THEN 'Lundbeck' WHEN 20 THEN 'Janssen' ELSE 'Unknown' END,
  `category` = CASE `id`
    WHEN 1 THEN 'Analgesic' WHEN 2 THEN 'NSAID' WHEN 3 THEN 'Antibiotic'
    WHEN 4 THEN 'Anti-diarrheal' WHEN 5 THEN 'Analgesic' WHEN 6 THEN 'Antihistamine'
    WHEN 7 THEN 'Antihistamine' WHEN 8 THEN 'PPI' WHEN 9 THEN 'Anti-diabetic'
    WHEN 10 THEN 'Statin' WHEN 11 THEN 'ARB' WHEN 12 THEN 'CCB'
    WHEN 13 THEN 'Hormone' WHEN 14 THEN 'Bronchodilator' WHEN 15 THEN 'Steroid'
    WHEN 16 THEN 'Benzodiazepine' WHEN 17 THEN 'Antidepressant' WHEN 18 THEN 'Antidepressant'
    WHEN 19 THEN 'Antidepressant' WHEN 20 THEN 'Analgesic' ELSE 'General' END;

-- --------------------------------------------------------
-- Global settings for thresholds
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  `value` VARCHAR(255) NOT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `system_settings` (`key`, `value`) 
VALUES ('low_stock_threshold','10'), ('expiry_alert_days','30')
ON DUPLICATE KEY UPDATE `value`=VALUES(`value`);

-- --------------------------------------------------------
-- Table structure for table `pharmacy_medicines` (stock and pricing)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `pharmacy_medicines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pharmacy_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `expiry_date` DATE DEFAULT NULL,
  `updated_by` INT DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `pharmacy_medicine_unique` (`pharmacy_id`,`medicine_id`),
  KEY `medicine_id` (`medicine_id`),
  CONSTRAINT `pharmacy_medicines_ibfk_1` FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pharmacy_medicines_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pm_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample pharmacy medicines with some expiry dates for testing
INSERT IGNORE INTO `pharmacy_medicines` (`id`, `pharmacy_id`, `medicine_id`, `price`, `stock`, `expiry_date`, `updated_by`, `updated_at`) VALUES
(1, 1, 1, '5.50', 100, '2026-12-31', 2, '2025-10-17 02:00:00'),
(2, 1, 2, '8.75', 5, '2025-03-15', 2, '2025-10-17 02:00:00'),
(3, 1, 3, '12.00', 30, '2026-06-30', 2, '2025-10-17 02:00:00'),
(4, 2, 1, '6.00', 80, '2026-08-15', 2, '2025-10-17 02:00:00'),
(5, 2, 4, '10.50', 40, '2025-11-30', 2, '2025-10-17 02:00:00'),
(6, 2, 5, '7.25', 60, '2026-05-20', 2, '2025-10-17 02:00:00'),
(7, 3, 6, '15.00', 25, '2026-09-10', 2, '2025-10-17 02:00:00'),
(8, 3, 7, '13.50', 8, '2026-07-25', 2, '2025-10-17 02:00:00'),
(9, 3, 8, '20.00', 20, '2026-12-15', 2, '2025-10-17 02:00:00'),
(10, 4, 9, '25.00', 15, '2026-04-05', 2, '2025-10-17 02:00:00'),
(11, 4, 10, '30.00', 10, '2026-03-18', 2, '2025-10-17 02:00:00'),
(12, 5, 11, '18.50', 45, '2026-11-22', 2, '2025-10-17 02:00:00'),
(13, 5, 12, '22.75', 30, '2026-10-30', 2, '2025-10-17 02:00:00'),
(14, 6, 13, '35.00', 12, '2026-08-14', 2, '2025-10-17 02:00:00'),
(15, 6, 14, '28.00', 18, '2026-02-28', 2, '2025-10-17 02:00:00'),
(16, 7, 15, '16.50', 50, '2026-06-12', 2, '2025-10-17 02:00:00'),
(17, 8, 16, '40.00', 8, '2026-05-08', 2, '2025-10-17 02:00:00'),
(18, 9, 17, '45.00', 6, '2026-07-19', 2, '2025-10-17 02:00:00'),
(19, 10, 18, '38.00', 9, '2026-09-03', 2, '2025-10-17 02:00:00'),
(20, 11, 19, '50.00', 5, '2026-01-25', 2, '2025-10-17 02:00:00'),
(21, 12, 20, '32.00', 22, '2026-12-08', 2, '2025-10-17 02:00:00'),
(22, 13, 1, '5.75', 75, '2026-10-17', 2, '2025-10-17 02:00:00'),
(23, 14, 2, '9.00', 45, '2026-11-05', 2, '2025-10-17 02:00:00'),
(24, 15, 3, '12.50', 35, '2026-04-12', 2, '2025-10-17 02:00:00'),
(25, 1, 4, '11.00', 38, '2026-03-20', 2, '2025-10-17 02:00:00');

-- --------------------------------------------------------
-- Table structure for table `price_history`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `price_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pharmacy_medicine_id` int(11) NOT NULL,
  `old_price` decimal(10,2) NOT NULL,
  `new_price` decimal(10,2) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `pharmacy_medicine_id` (`pharmacy_medicine_id`),
  KEY `changed_by` (`changed_by`),
  CONSTRAINT `price_history_ibfk_1` FOREIGN KEY (`pharmacy_medicine_id`) REFERENCES `pharmacy_medicines` (`id`) ON DELETE CASCADE,
  CONSTRAINT `price_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample price history
INSERT IGNORE INTO `price_history` (`id`, `pharmacy_medicine_id`, `old_price`, `new_price`, `changed_by`, `changed_at`) VALUES
(1, 1, '5.00', '5.50', 2, '2025-10-17 01:00:00'),
(2, 4, '5.50', '6.00', 2, '2025-10-17 01:30:00');

-- --------------------------------------------------------
-- Table structure for table `notifications`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample notifications including expiry alerts
INSERT IGNORE INTO `notifications` (`id`, `user_id`, `type`, `message`, `read`, `created_at`) VALUES
(1, NULL, 'price_update', 'Price for Biogesic updated at Mercury Drug - Pagadian City', 0, '2025-10-17 01:00:00'),
(2, NULL, 'price_update', 'Price for Biogesic updated at Watson''s Pharmacy - Pagadian', 0, '2025-10-17 01:30:00'),
(3, 2, 'expiry_alert', 'Advil at Mercury Drug - Pagadian City expires on 2025-03-15', 0, '2025-10-17 02:00:00'),
(4, 2, 'low_stock', 'Advil stock is low (5 units) at Mercury Drug - Pagadian City', 0, '2025-10-17 02:00:00');

-- --------------------------------------------------------
-- Table structure for table `activity_logs`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_logs_entity` (`entity_type`,`entity_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `activity_logs_ibfk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- NEW TABLES FOR STAFF DASHBOARD SYSTEM
-- --------------------------------------------------------

-- Table structure for table `user_pharmacies` (for staff management system)
CREATE TABLE IF NOT EXISTS `user_pharmacies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `pharmacy_id` INT NOT NULL,
  `user_role` ENUM('owner', 'staff', 'admin') DEFAULT 'staff',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `user_pharmacy_unique` (`user_id`, `pharmacy_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_pharmacy_id` (`pharmacy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Assign users to pharmacies for staff dashboard
INSERT IGNORE INTO `user_pharmacies` (`user_id`, `pharmacy_id`, `user_role`) VALUES
(1, 1, 'admin'),
(2, 1, 'staff'),
(1, 2, 'admin'),
(2, 3, 'staff');

-- --------------------------------------------------------
-- Table structure for table `medicines_inventory` (for staff inventory management)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `medicines_inventory` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pharmacy_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `generic_name` VARCHAR(255),
  `brand` VARCHAR(255),
  `category` VARCHAR(100),
  `description` TEXT,
  `price` DECIMAL(10,2) DEFAULT 0,
  `stock_quantity` INT DEFAULT 0,
  `expiry_date` DATE DEFAULT NULL,
  `manufacturer` VARCHAR(255),
  `prescription_required` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies`(`id`) ON DELETE CASCADE,
  INDEX `idx_pharmacy_id` (`pharmacy_id`),
  INDEX `idx_category` (`category`),
  INDEX `idx_stock` (`stock_quantity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample medicines for staff inventory management
INSERT IGNORE INTO `medicines_inventory` (
  `pharmacy_id`, `name`, `generic_name`, `brand`, `category`, `price`, `stock_quantity`, `expiry_date`, `manufacturer`
) VALUES
(1, 'Biogesic', 'Paracetamol', 'United Laboratories', 'Tablet', 5.50, 100, '2026-12-31', 'United Laboratories'),
(1, 'Advil', 'Ibuprofen', 'Pfizer', 'Tablet', 8.75, 5, '2025-03-15', 'Pfizer'),
(1, 'Amoxil', 'Amoxicillin', 'GlaxoSmithKline', 'Capsule', 12.00, 30, '2026-06-30', 'GlaxoSmithKline'),
(1, 'Imodium', 'Loperamide', 'Johnson & Johnson', 'Capsule', 10.50, 0, '2026-08-15', 'Johnson & Johnson'),
(1, 'Zyrtec', 'Cetirizine', 'Johnson & Johnson', 'Tablet', 15.00, 8, '2026-07-25', 'Johnson & Johnson'),
(2, 'Biogesic', 'Paracetamol', 'United Laboratories', 'Tablet', 6.00, 80, '2026-08-15', 'United Laboratories'),
(2, 'Neozep', 'Phenylephrine', 'United Laboratories', 'Tablet', 7.50, 25, '2026-09-10', 'United Laboratories'),
(3, 'Medicol', 'Ibuprofen', 'Medi-Rx', 'Tablet', 9.25, 12, '2026-05-20', 'Medi-Rx');

-- --------------------------------------------------------
-- Create useful views for expiry and stock alerts
-- --------------------------------------------------------

-- View for low stock alerts
CREATE OR REPLACE VIEW low_stock_alerts AS
SELECT 
    pm.*,
    COALESCE(m.brand_name, m.name, 'Unknown') as medicine_name,
    COALESCE(m.scientific_name, m.name, 'Unknown') as generic_name,
    p.name as pharmacy_name,
    (SELECT value FROM system_settings WHERE `key` = 'low_stock_threshold') as threshold
FROM pharmacy_medicines pm
JOIN medicines m ON pm.medicine_id = m.id
JOIN pharmacies p ON pm.pharmacy_id = p.id
WHERE pm.stock <= (SELECT value FROM system_settings WHERE `key` = 'low_stock_threshold');

-- View for expiry alerts
CREATE OR REPLACE VIEW expiry_alerts AS
SELECT 
    pm.*,
    COALESCE(m.brand_name, m.name, 'Unknown') as medicine_name,
    COALESCE(m.scientific_name, m.name, 'Unknown') as generic_name,
    p.name as pharmacy_name,
    DATEDIFF(pm.expiry_date, CURDATE()) as days_until_expiry
FROM pharmacy_medicines pm
JOIN medicines m ON pm.medicine_id = m.id
JOIN pharmacies p ON pm.pharmacy_id = p.id
WHERE pm.expiry_date IS NOT NULL 
AND pm.expiry_date <= DATE_ADD(CURDATE(), INTERVAL (SELECT value FROM system_settings WHERE `key` = 'expiry_alert_days') DAY)
AND pm.expiry_date >= CURDATE();

-- --------------------------------------------------------
-- Update AUTO_INCREMENT values
-- --------------------------------------------------------

ALTER TABLE `users` AUTO_INCREMENT=4;
ALTER TABLE `pharmacies` AUTO_INCREMENT=16;
ALTER TABLE `medicines` AUTO_INCREMENT=21;
ALTER TABLE `pharmacy_medicines` AUTO_INCREMENT=26;
ALTER TABLE `price_history` AUTO_INCREMENT=3;
ALTER TABLE `notifications` AUTO_INCREMENT=5;

-- --------------------------------------------------------
-- Display setup completion message
-- --------------------------------------------------------

SELECT 'MyPharma Database Setup Completed Successfully!' as message;
SELECT 'All tables and views created successfully' as status;
SELECT COUNT(*) as users_count FROM `users`;
SELECT COUNT(*) as pharmacies_count FROM `pharmacies`;
SELECT COUNT(*) as medicines_count FROM `medicines`;
SELECT COUNT(*) as pharmacy_medicines_count FROM `pharmacy_medicines`;
SELECT COUNT(*) as user_pharmacies_count FROM `user_pharmacies`;
SELECT COUNT(*) as medicines_inventory_count FROM `medicines_inventory`;
SELECT COUNT(*) as low_stock_items FROM low_stock_alerts;
SELECT COUNT(*) as expiry_alert_items FROM expiry_alerts;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;