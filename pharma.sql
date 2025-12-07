-- c:\xampp\htdocs\MyPharmaV1\migrations\complete_setup.sql
-- MyPharma Complete Database Setup for mypharma_v1
-- Final version with corrected notifications

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Drop database if exists (for fresh install)
-- DROP DATABASE IF EXISTS `mypharma_v1`;

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
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  INDEX `idx_user_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample users
INSERT IGNORE INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Admin User', 'admin@mypharma.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(2, 'Staff User', 'staff@mypharma.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff'),
(3, 'Regular User', 'user@mypharma.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
(4, 'John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'pending'),
(5, 'Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

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
  PRIMARY KEY (`id`),
  INDEX `idx_pharmacy_verified` (`verified`),
  INDEX `idx_pharmacy_name` (`name`)
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
-- Table structure for table `medicines`
-- Complete version with all required columns
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `medicines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `scientific_name` varchar(100) DEFAULT NULL,
  `brand_name` varchar(100) DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `dosage_form` varchar(50) DEFAULT NULL,
  `strength` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `uses` text DEFAULT NULL,
  `side_effects` text DEFAULT NULL,
  `contraindications` text DEFAULT NULL,
  `status` enum('active','inactive','discontinued') DEFAULT 'active',
  `category` varchar(100) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  INDEX `idx_medicines_name` (`name`),
  INDEX `idx_medicines_category` (`category`),
  INDEX `idx_medicines_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert 100 sample medicines
INSERT IGNORE INTO `medicines` (`id`, `name`, `scientific_name`, `brand_name`, `manufacturer`, `dosage_form`, `strength`, `description`, `uses`, `side_effects`, `contraindications`, `status`, `category`) VALUES
(1, 'Biogesic', 'Paracetamol', 'Biogesic', 'United Laboratories', 'Tablet', '500mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Fever and pain relief', 'Generally well-tolerated', 'Liver disease, alcohol dependence', 'active', 'Analgesic'),
(2, 'Advil', 'Ibuprofen', 'Advil', 'Pfizer', 'Tablet', '200mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Pain and inflammation relief', 'May cause stomach upset', 'Peptic ulcer disease, kidney problems', 'active', 'NSAID'),
(3, 'Amoxil', 'Amoxicillin', 'Amoxil', 'GlaxoSmithKline', 'Capsule', '500mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of bacterial infections', 'Diarrhea may occur', 'Penicillin allergy', 'active', 'Antibiotic'),
(4, 'Imodium', 'Loperamide', 'Imodium', 'Johnson & Johnson', 'Capsule', '2mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of acute diarrhea', 'Constipation may occur', 'Bacterial diarrhea, ulcerative colitis', 'active', 'Antidiarrheal'),
(5, 'Bayer Aspirin', 'Aspirin', 'Bayer Aspirin', 'Bayer', 'Tablet', '325mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Pain relief and cardiovascular protection', 'May cause stomach irritation', 'Bleeding disorders, peptic ulcers', 'active', 'Analgesic'),
(6, 'Zyrtec', 'Cetirizine', 'Zyrtec', 'Johnson & Johnson', 'Tablet', '10mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of allergic conditions', 'May cause drowsiness', 'Kidney disease, pregnancy', 'active', 'Antihistamine'),
(7, 'Claritin', 'Loratadine', 'Claritin', 'MSD', 'Tablet', '10mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of allergic rhinitis', 'Generally well-tolerated', 'Liver disease', 'active', 'Antihistamine'),
(8, 'Prilosec', 'Omeprazole', 'Prilosec', 'AstraZeneca', 'Capsule', '20mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of acid-related disorders', 'Headache or diarrhea may occur', 'Liver disease', 'active', 'PPI'),
(9, 'Glucophage', 'Metformin', 'Glucophage', 'Merck', 'Tablet', '500mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of type 2 diabetes', 'May cause stomach upset', 'Kidney disease, liver disease', 'active', 'Antidiabetic'),
(10, 'Lipitor', 'Atorvastatin', 'Lipitor', 'Pfizer', 'Tablet', '10mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of high cholesterol', 'Muscle pain may occur', 'Liver disease, pregnancy', 'active', 'Statin'),
(11, 'Cozaar', 'Losartan', 'Cozaar', 'Merck', 'Tablet', '50mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of high blood pressure', 'May cause dizziness', 'Pregnancy, kidney artery stenosis', 'active', 'ARB'),
(12, 'Norvasc', 'Amlodipine', 'Norvasc', 'Pfizer', 'Tablet', '5mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of high blood pressure and angina', 'May cause swelling of ankles', 'Liver disease', 'active', 'Calcium Channel Blocker'),
(13, 'Synthroid', 'Levothyroxine', 'Synthroid', 'AbbVie', 'Tablet', '100mcg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of thyroid hormone deficiency', 'Weight loss or palpitations may occur', 'Thyrotoxicosis, acute MI', 'active', 'Hormone'),
(14, 'Ventolin', 'Salbutamol', 'Ventolin', 'GlaxoSmithKline', 'Inhaler', '100mcg/inhalation', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Relief of bronchospasm', 'Tremor or nervousness may occur', 'Cardiac arrhythmias', 'active', 'Bronchodilator'),
(15, 'Deltasone', 'Prednisone', 'Deltasone', 'Pfizer', 'Tablet', '5mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of inflammation and autoimmune conditions', 'Weight gain, mood changes', 'Systemic fungal infections', 'active', 'Steroid'),
(16, 'Valium', 'Diazepam', 'Valium', 'Roche', 'Tablet', '5mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of anxiety and muscle spasms', 'Drowsiness, dizziness', 'Glaucoma, myasthenia gravis', 'active', 'Benzodiazepine'),
(17, 'Zoloft', 'Sertraline', 'Zoloft', 'Pfizer', 'Tablet', '50mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of depression and anxiety', 'Nausea, insomnia', 'MAOI use', 'active', 'Antidepressant'),
(18, 'Prozac', 'Fluoxetine', 'Prozac', 'Eli Lilly', 'Capsule', '20mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of depression and OCD', 'Nausea, headache', 'MAOI use, liver disease', 'active', 'Antidepressant'),
(19, 'Lexapro', 'Escitalopram', 'Lexapro', 'Lundbeck', 'Tablet', '10mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of depression and anxiety', 'Nausea, drowsiness', 'MAOI use', 'active', 'Antidepressant'),
(20, 'Ultram', 'Tramadol', 'Ultram', 'Janssen', 'Tablet', '50mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of moderate to severe pain', 'Nausea, dizziness', 'Acute intoxication, epilepsy', 'active', 'Analgesic'),
(21, 'Tuseran Forte', 'Chlorpheniramine + Phenylpropanolamine', 'Tuseran Forte', 'United Laboratories', 'Tablet', '4mg+12.5mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Relief of cold and allergy symptoms', 'May cause drowsiness', 'Avoid alcohol while taking this medicine', 'active', 'Antihistamine'),
(22, 'Solmux Broncho', 'Ambroxol', 'Solmux Broncho', 'Sanofi', 'Syrup', '30mg/5ml', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of respiratory conditions', 'Generally well-tolerated', 'Use with caution in patients with gastric ulcers', 'active', 'Expectorant'),
(23, 'Expess T.Forte', 'Tripolidine + Phenylpropanolamine', 'Expess T.Forte', 'United Laboratories', 'Tablet', '2.5mg+12.5mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Relief of upper respiratory tract symptoms', 'May cause drowsiness', 'Avoid driving or operating machinery', 'active', 'Antihistamine'),
(24, 'Allerkid', 'Chlorpheniramine', 'Allerkid', 'United Laboratories', 'Syrup', '2mg/5ml', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of allergic conditions in children', 'May cause drowsiness in children', 'Keep out of reach of children', 'active', 'Antihistamine'),
(25, 'Enervon C', 'Multivitamin + Ascorbic Acid', 'Enervon C', 'Merck', 'Tablet', 'Various', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Nutritional supplement', 'Generally well-tolerated', 'No known significant contraindications', 'active', 'Vitamin'),
(26, 'Vita-C Forte', 'Ascorbic Acid', 'Vita-C Forte', 'United Laboratories', 'Tablet', '500mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Vitamin C supplement', 'Generally well-tolerated', 'Large doses may cause diarrhea', 'active', 'Vitamin'),
(27, 'Femvital', 'Multivitamin + Iron', 'Femvital', 'United Laboratories', 'Tablet', 'Various', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Nutritional supplement for women', 'May cause constipation', 'Take with food to reduce stomach upset', 'active', 'Vitamin'),
(28, 'Bonamin', 'Dimenhydrinate', 'Bonamin', 'United Laboratories', 'Tablet', '50mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Prevention and treatment of motion sickness', 'May cause drowsiness', 'Avoid alcohol while taking this medicine', 'active', 'Antiemetic'),
(29, 'Lomotil', 'Loperamide', 'Lomotil', 'Pfizer', 'Capsule', '2mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of acute diarrhea', 'Constipation may occur', 'Do not use for more than 2 days without consulting a doctor', 'active', 'Antidiarrheal'),
(30, 'Diarium', 'Racecadotril', 'Diarium', 'Sanofi', 'Capsule', '100mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of acute diarrhea in adults', 'Generally well-tolerated', 'Not recommended for children under 18', 'active', 'Antidiarrheal'),
(31, 'Voltaren', 'Diclofenac Sodium', 'Voltaren', 'Novartis', 'Gel', '1%', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Topical treatment of pain and inflammation', 'Skin irritation may occur', 'For external use only', 'active', 'NSAID'),
(32, 'Cataflam', 'Diclofenac Potassium', 'Cataflam', 'Novartis', 'Tablet', '25mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of pain and inflammation', 'May cause stomach upset', 'Take with food or milk', 'active', 'NSAID'),
(33, 'Profenid', 'Diclofenac Sodium', 'Profenid', 'Pfizer', 'Injection', '75mg/ml', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of severe pain and inflammation', 'Pain at injection site may occur', 'For intramuscular use only', 'active', 'NSAID'),
(34, 'Mefenamic', 'Mefenamic Acid', 'Mefenamic', 'Pfizer', 'Capsule', '250mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of pain and inflammation', 'May cause stomach upset', 'Take with food or milk', 'active', 'NSAID'),
(35, 'Augmentin', 'Amoxicillin + Clavulanic Acid', 'Augmentin', 'GlaxoSmithKline', 'Tablet', '500mg+125mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of bacterial infections', 'Diarrhea may occur', 'Complete the full course of treatment', 'active', 'Antibiotic'),
(36, 'Cephalexin', 'Cephalexin', 'Cephalexin', 'Pfizer', 'Capsule', '500mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of bacterial infections', 'Diarrhea may occur', 'Complete the full course of treatment', 'active', 'Antibiotic'),
(37, 'Azithrox', 'Azithromycin', 'Azithrox', 'Pfizer', 'Tablet', '500mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of bacterial infections', 'Nausea or diarrhea may occur', 'Complete the full course of treatment', 'active', 'Antibiotic'),
(38, 'Tenormin', 'Atenolol', 'Tenormin', 'AstraZeneca', 'Tablet', '50mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of high blood pressure and angina', 'May cause fatigue or dizziness', 'Do not stop suddenly without consulting a doctor', 'active', 'Beta-blocker'),
(39, 'Norvasc', 'Amlodipine', 'Norvasc', 'Pfizer', 'Tablet', '5mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of high blood pressure and angina', 'May cause swelling of ankles', 'Take at the same time each day', 'active', 'Calcium Channel Blocker'),
(40, 'Cozaar', 'Losartan', 'Cozaar', 'Merck', 'Tablet', '50mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of high blood pressure', 'May cause dizziness', 'Avoid potassium supplements without consulting a doctor', 'active', 'ARB'),
(41, 'Glucophage', 'Metformin', 'Glucophage', 'Merck', 'Tablet', '500mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of type 2 diabetes', 'May cause stomach upset', 'Take with meals to reduce side effects', 'active', 'Antidiabetic'),
(42, 'Diamicron', 'Gliclazide', 'Diamicron', 'Servier', 'Tablet', '80mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of type 2 diabetes', 'May cause hypoglycemia', 'Monitor blood sugar regularly', 'active', 'Antidiabetic'),
(43, 'Ventolin', 'Salbutamol', 'Ventolin', 'GlaxoSmithKline', 'Inhaler', '100mcg/inhalation', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Relief of bronchospasm', 'Tremor or nervousness may occur', 'Do not exceed recommended dose', 'active', 'Bronchodilator'),
(44, 'Seretide', 'Fluticasone + Salmeterol', 'Seretide', 'GlaxoSmithKline', 'Inhaler', '250mcg+50mcg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Maintenance treatment of asthma', 'Throat irritation may occur', 'Rinse mouth after use', 'active', 'Bronchodilator'),
(45, 'Synthroid', 'Levothyroxine', 'Synthroid', 'AbbVie', 'Tablet', '100mcg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of thyroid hormone deficiency', 'Weight loss or palpitations may occur', 'Take on an empty stomach 30 minutes before breakfast', 'active', 'Hormone'),
(46, 'Claritin', 'Loratadine', 'Claritin', 'MSD', 'Tablet', '10mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of allergic rhinitis', 'Generally well-tolerated', 'No known significant contraindications', 'active', 'Antihistamine'),
(47, 'Zyrtec', 'Cetirizine', 'Zyrtec', 'Pfizer', 'Tablet', '10mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of allergic conditions', 'May cause drowsiness', 'Avoid alcohol while taking this medicine', 'active', 'Antihistamine'),
(48, 'Nexium', 'Esomeprazole', 'Nexium', 'AstraZeneca', 'Capsule', '20mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of acid-related disorders', 'Headache or diarrhea may occur', 'Take 1 hour before meals', 'active', 'PPI'),
(49, 'Prevacid', 'Lansoprazole', 'Prevacid', 'Takeda', 'Capsule', '30mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of acid-related disorders', 'Headache or diarrhea may occur', 'Take 1 hour before meals', 'active', 'PPI'),
(50, 'Lipitor', 'Atorvastatin', 'Lipitor', 'Pfizer', 'Tablet', '10mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of high cholesterol', 'Muscle pain may occur', 'Report unexplained muscle pain to doctor', 'active', 'Statin'),
(51, 'Tempra', 'Paracetamol', 'Tempra', 'Bristol-Myers Squibb', 'Syrup', '120mg/5ml', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Fever and pain relief for children', 'Generally well-tolerated', 'Follow dosing instructions carefully', 'active', 'Analgesic'),
(52, 'Calpol', 'Paracetamol', 'Calpol', 'GlaxoSmithKline', 'Syrup', '120mg/5ml', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Fever and pain relief for children', 'Generally well-tolerated', 'Follow dosing instructions carefully', 'active', 'Analgesic'),
(53, 'Advil', 'Ibuprofen', 'Advil', 'Pfizer', 'Tablet', '200mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Pain and inflammation relief', 'May cause stomach upset', 'Take with food or milk', 'active', 'NSAID'),
(54, 'Motrin', 'Ibuprofen', 'Motrin', 'Johnson & Johnson', 'Tablet', '200mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Pain and inflammation relief', 'May cause stomach upset', 'Take with food or milk', 'active', 'NSAID'),
(55, 'Tylenol', 'Acetaminophen', 'Tylenol', 'Johnson & Johnson', 'Tablet', '325mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Fever and pain relief', 'Generally well-tolerated', 'Do not exceed recommended dose', 'active', 'Analgesic'),
(56, 'Aleve', 'Naproxen', 'Aleve', 'Bayer', 'Tablet', '220mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Pain and inflammation relief', 'May cause stomach upset', 'Take with food or milk', 'active', 'NSAID'),
(57, 'Bayer Aspirin', 'Aspirin', 'Bayer Aspirin', 'Bayer', 'Tablet', '325mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Pain relief and cardiovascular protection', 'May cause stomach irritation', 'Do not give to children', 'active', 'Analgesic'),
(58, 'Bufferin', 'Aspirin', 'Bufferin', 'Bayer', 'Tablet', '325mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Pain relief and cardiovascular protection', 'May cause stomach irritation', 'Do not give to children', 'active', 'Analgesic'),
(59, 'Excedrin', 'Acetaminophen + Aspirin + Caffeine', 'Excedrin', 'GlaxoSmithKline', 'Tablet', '250mg+250mg+65mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of tension headaches', 'May cause stomach upset', 'Do not exceed recommended dose', 'active', 'Analgesic'),
(60, 'Anacin', 'Acetaminophen', 'Anacin', 'Pfizer', 'Tablet', '500mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Fever and pain relief', 'Generally well-tolerated', 'Do not exceed recommended dose', 'active', 'Analgesic'),
(61, 'Robitussin', 'Dextromethorphan', 'Robitussin', 'Pfizer', 'Syrup', '15mg/5ml', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Cough suppressant', 'May cause drowsiness', 'Do not exceed recommended dose', 'active', 'Antitussive'),
(62, 'Delsym', 'Dextromethorphan', 'Delsym', 'Pfizer', 'Syrup', '15mg/5ml', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Cough suppressant', 'May cause drowsiness', 'Do not exceed recommended dose', 'active', 'Antitussive'),
(63, 'Mucinex', 'Guaifenesin', 'Mucinex', 'Reckitt Benckiser', 'Tablet', '600mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Expectorant for chest congestion', 'Generally well-tolerated', 'Drink plenty of fluids', 'active', 'Expectorant'),
(64, 'Coricidin', 'Dextromethorphan + Guaifenesin', 'Coricidin', 'MSD', 'Tablet', '15mg+400mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of cold symptoms', 'May cause drowsiness', 'Avoid alcohol while taking this medicine', 'active', 'Cold Medicine'),
(65, 'Nyquil', 'Doxylamine + Acetaminophen + Dextromethorphan', 'Nyquil', 'Procter & Gamble', 'Syrup', '12.5mg+325mg+10mg/15ml', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Nighttime cold and flu relief', 'Causes drowsiness', 'Do not drive or operate machinery', 'active', 'Cold Medicine'),
(66, 'Dayquil', 'Phenylephrine + Acetaminophen + Dextromethorphan', 'Dayquil', 'Procter & Gamble', 'Syrup', '5mg+325mg+10mg/15ml', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Daytime cold and flu relief', 'May cause nervousness', 'Avoid caffeine while taking this medicine', 'active', 'Cold Medicine'),
(67, 'Theraflu', 'Phenylephrine + Acetaminophen + Dextromethorphan', 'Theraflu', 'GlaxoSmithKline', 'Powder', '5mg+325mg+10mg/sachet', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Cold and flu symptom relief', 'May cause nervousness', 'Dissolve in hot water and drink while warm', 'active', 'Cold Medicine'),
(68, 'Benadryl', 'Diphenhydramine', 'Benadryl', 'Johnson & Johnson', 'Syrup', '12.5mg/5ml', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of allergic reactions', 'Causes drowsiness', 'Do not drive or operate machinery', 'active', 'Antihistamine'),
(69, 'Reactine', 'Cetirizine', 'Reactine', 'Pfizer', 'Tablet', '10mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of allergic conditions', 'May cause drowsiness', 'Avoid alcohol while taking this medicine', 'active', 'Antihistamine'),
(70, 'Xyzal', 'Levocetirizine', 'Xyzal', 'Sanofi', 'Tablet', '5mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of allergic conditions', 'May cause drowsiness', 'Avoid alcohol while taking this medicine', 'active', 'Antihistamine'),
(71, 'Atarax', 'Hydroxyzine', 'Atarax', 'Pfizer', 'Tablet', '25mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of anxiety and allergic conditions', 'Causes drowsiness', 'Do not drive or operate machinery', 'active', 'Antihistamine'),
(72, 'Chlor-Trimeton', 'Chlorpheniramine', 'Chlor-Trimeton', 'GlaxoSmithKline', 'Tablet', '4mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of allergic conditions', 'Causes drowsiness', 'Do not drive or operate machinery', 'active', 'Antihistamine'),
(73, 'Prilosec', 'Omeprazole', 'Prilosec', 'AstraZeneca', 'Capsule', '20mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of acid-related disorders', 'Headache or diarrhea may occur', 'Take 1 hour before meals', 'active', 'PPI'),
(74, 'Aciphex', 'Rabeprazole', 'Aciphex', 'Eisai', 'Tablet', '20mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of acid-related disorders', 'Headache or diarrhea may occur', 'Take 1 hour before meals', 'active', 'PPI'),
(75, 'Protonix', 'Pantoprazole', 'Protonix', 'Pfizer', 'Tablet', '40mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of acid-related disorders', 'Headache or diarrhea may occur', 'Take 1 hour before meals', 'active', 'PPI'),
(76, 'Pepcid', 'Famotidine', 'Pepcid', 'Merck', 'Tablet', '20mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of acid-related disorders', 'Generally well-tolerated', 'Take at bedtime for best effect', 'active', 'H2 Blocker'),
(77, 'Zantac', 'Ranitidine', 'Zantac', 'Sanofi', 'Tablet', '150mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of acid-related disorders', 'Generally well-tolerated', 'Take at bedtime for best effect', 'active', 'H2 Blocker'),
(78, 'Tagamet', 'Cimetidine', 'Tagamet', 'GlaxoSmithKline', 'Tablet', '200mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of acid-related disorders', 'May cause drowsiness', 'Take at bedtime for best effect', 'active', 'H2 Blocker'),
(79, 'Crestor', 'Rosuvastatin', 'Crestor', 'AstraZeneca', 'Tablet', '10mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of high cholesterol', 'Muscle pain may occur', 'Report unexplained muscle pain to doctor', 'active', 'Statin'),
(80, 'Zocor', 'Simvastatin', 'Zocor', 'Merck', 'Tablet', '20mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of high cholesterol', 'Muscle pain may occur', 'Report unexplained muscle pain to doctor', 'active', 'Statin'),
(81, 'Mevacor', 'Lovastatin', 'Mevacor', 'Merck', 'Tablet', '20mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of high cholesterol', 'Muscle pain may occur', 'Report unexplained muscle pain to doctor', 'active', 'Statin'),
(82, 'Lescol', 'Fluvastatin', 'Lescol', 'Novartis', 'Capsule', '20mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of high cholesterol', 'Muscle pain may occur', 'Report unexplained muscle pain to doctor', 'active', 'Statin'),
(83, 'Altocor', 'Lovastatin + Niacin', 'Altocor', 'Merck', 'Tablet', '20mg+500mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of high cholesterol', 'Flushing may occur', 'Take at bedtime with a low-fat snack', 'active', 'Statin'),
(84, 'Caduet', 'Amlodipine + Atorvastatin', 'Caduet', 'Pfizer', 'Tablet', '5mg+10mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of high blood pressure and cholesterol', 'Swelling of ankles or muscle pain may occur', 'Report unexplained muscle pain to doctor', 'active', 'Combination'),
(85, 'Livalo', 'Pitavastatin', 'Livalo', 'Kowa', 'Tablet', '2mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of high cholesterol', 'Muscle pain may occur', 'Report unexplained muscle pain to doctor', 'active', 'Statin'),
(86, 'Glucotrol', 'Glipizide', 'Glucotrol', 'Pfizer', 'Tablet', '5mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of type 2 diabetes', 'May cause hypoglycemia', 'Monitor blood sugar regularly', 'active', 'Antidiabetic'),
(87, 'Diabeta', 'Glyburide', 'Diabeta', 'Merck', 'Tablet', '5mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of type 2 diabetes', 'May cause hypoglycemia', 'Monitor blood sugar regularly', 'active', 'Antidiabetic'),
(88, 'Micronase', 'Glyburide', 'Micronase', 'AbbVie', 'Tablet', '5mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of type 2 diabetes', 'May cause hypoglycemia', 'Monitor blood sugar regularly', 'active', 'Antidiabetic'),
(89, 'Glynase', 'Glyburide', 'Glynase', 'Pfizer', 'Tablet', '5mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of type 2 diabetes', 'May cause hypoglycemia', 'Monitor blood sugar regularly', 'active', 'Antidiabetic'),
(90, 'Amaryl', 'Glimepiride', 'Amaryl', 'Sanofi', 'Tablet', '2mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of type 2 diabetes', 'May cause hypoglycemia', 'Monitor blood sugar regularly', 'active', 'Antidiabetic'),
(91, 'Starlix', 'Repaglinide', 'Starlix', 'Novartis', 'Tablet', '1mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of type 2 diabetes', 'May cause hypoglycemia', 'Take immediately before meals', 'active', 'Antidiabetic'),
(92, 'Januvia', 'Sitagliptin', 'Januvia', 'Merck', 'Tablet', '100mg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of type 2 diabetes', 'Generally well-tolerated', 'Take once daily with or without food', 'active', 'Antidiabetic'),
(93, 'Levoxyl', 'Levothyroxine', 'Levoxyl', 'AbbVie', 'Tablet', '100mcg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of thyroid hormone deficiency', 'Weight loss or palpitations may occur', 'Take on an empty stomach 30 minutes before breakfast', 'active', 'Hormone'),
(94, 'Levothroid', 'Levothyroxine', 'Levothroid', 'Forest Pharmaceuticals', 'Tablet', '100mcg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of thyroid hormone deficiency', 'Weight loss or palpitations may occur', 'Take on an empty stomach 30 minutes before breakfast', 'active', 'Hormone'),
(95, 'Tirosint', 'Levothyroxine', 'Tirosint', 'Iroko Pharmaceuticals', 'Capsule', '100mcg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of thyroid hormone deficiency', 'Weight loss or palpitations may occur', 'Take on an empty stomach 30 minutes before breakfast', 'active', 'Hormone'),
(96, 'Unithroid', 'Levothyroxine', 'Unithroid', 'Acella Pharmaceuticals', 'Tablet', '100mcg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of thyroid hormone deficiency', 'Weight loss or palpitations may occur', 'Take on an empty stomach 30 minutes before breakfast', 'active', 'Hormone'),
(97, 'Cytomel', 'Liothyronine', 'Cytomel', 'AbbVie', 'Tablet', '50mcg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of thyroid hormone deficiency', 'Palpitations or tremors may occur', 'Take on an empty stomach 30 minutes before breakfast', 'active', 'Hormone'),
(98, 'Cynoplus', 'Liothyronine + Levothyroxine', 'Cynoplus', 'AbbVie', 'Tablet', '5mcg+50mcg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of thyroid hormone deficiency', 'Palpitations or tremors may occur', 'Take on an empty stomach 30 minutes before breakfast', 'active', 'Hormone'),
(99, 'Thyrolar', 'Liothyronine', 'Thyrolar', 'Forest Pharmaceuticals', 'Tablet', '5mcg', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Treatment of thyroid hormone deficiency', 'Palpitations or tremors may occur', 'Take on an empty stomach 30 minutes before breakfast', 'active', 'Hormone'),
(100, 'ProAir', 'Albuterol', 'ProAir', 'Teva Pharmaceuticals', 'Inhaler', '90mcg/inhalation', 'A trusted medicine commonly available in Pagadian City pharmacies.', 'Relief of bronchospasm', 'Tremor or nervousness may occur', 'Do not exceed recommended dose', 'active', 'Bronchodilator');

-- --------------------------------------------------------
-- Global settings for thresholds
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  `value` VARCHAR(255) NOT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_settings_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `system_settings` (`key`, `value`) 
VALUES 
('low_stock_threshold', '10'),
('expiry_alert_days', '30'),
('site_name', 'MyPharma'),
('currency', 'PHP'),
('max_login_attempts', '5'),
('session_timeout', '30')
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
  INDEX `idx_pharmacy_medicines_stock` (`stock`),
  INDEX `idx_pharmacy_medicines_expiry` (`expiry_date`),
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

-- Add more sample data for the additional medicines
INSERT IGNORE INTO `pharmacy_medicines` (`pharmacy_id`, `medicine_id`, `price`, `stock`, `expiry_date`, `updated_by`) VALUES
(1, 21, '6.50', 45, '2026-10-15', 2),
(1, 22, '85.00', 20, '2026-08-20', 2),
(1, 23, '7.25', 35, '2026-11-30', 2),
(1, 24, '65.00', 15, '2026-09-10', 2),
(2, 21, '7.00', 30, '2026-09-25', 2),
(2, 22, '90.00', 18, '2026-07-15', 2),
(2, 25, '12.50', 50, '2026-12-31', 2),
(2, 26, '8.75', 60, '2026-11-20', 2),
(3, 23, '7.50', 25, '2026-10-10', 2),
(3, 24, '70.00', 12, '2026-08-30', 2),
(3, 27, '15.00', 40, '2026-12-15', 2),
(3, 28, '5.25', 55, '2026-09-05', 2),
(4, 25, '13.00', 35, '2027-01-15', 2),
(4, 26, '9.00', 45, '2026-12-10', 2),
(4, 29, '22.50', 20, '2026-10-25', 2),
(4, 30, '95.00', 8, '2026-08-15', 2),
(5, 27, '16.00', 30, '2027-01-30', 2),
(5, 28, '5.50', 50, '2026-09-20', 2),
(5, 31, '120.00', 25, '2026-11-15', 2),
(5, 32, '18.75', 40, '2026-12-05', 2),
(6, 29, '23.00', 18, '2026-11-05', 2),
(6, 30, '100.00', 6, '2026-08-25', 2),
(6, 33, '45.00', 15, '2026-10-20', 2),
(6, 34, '12.50', 35, '2026-12-25', 2),
(7, 31, '125.00', 22, '2026-11-25', 2),
(7, 32, '19.50', 38, '2027-01-10', 2),
(7, 35, '55.00', 28, '2026-10-30', 2),
(7, 36, '28.00', 32, '2026-12-20', 2),
(8, 33, '48.00', 12, '2026-10-30', 2),
(8, 34, '13.00', 30, '2027-01-05', 2),
(8, 37, '65.00', 20, '2026-11-10', 2),
(8, 38, '8.50', 60, '2026-12-30', 2),
(9, 35, '58.00', 25, '2026-11-05', 2),
(9, 36, '29.50', 30, '2026-12-25', 2),
(9, 39, '9.75', 55, '2027-01-15', 2),
(9, 40, '25.00', 42, '2026-10-20', 2),
(10, 37, '68.00', 18, '2026-11-20', 2),
(10, 38, '9.00', 58, '2027-01-20', 2),
(10, 41, '12.50', 65, '2026-12-10', 2),
(10, 42, '32.00', 28, '2026-10-25', 2),
(11, 39, '10.25', 52, '2027-01-25', 2),
(11, 40, '26.50', 40, '2026-10-30', 2),
(11, 43, '280.00', 15, '2026-11-15', 2),
(11, 44, '350.00', 12, '2026-12-05', 2),
(12, 41, '13.00', 62, '2026-12-20', 2),
(12, 42, '33.50', 26, '2026-11-05', 2),
(12, 45, '5.50', 85, '2027-01-30', 2),
(12, 46, '8.75', 72, '2026-12-15', 2),
(13, 43, '290.00', 13, '2026-11-25', 2),
(13, 44, '360.00', 10, '2026-12-15', 2),
(13, 47, '9.50', 68, '2027-01-10', 2),
(13, 48, '45.00', 35, '2026-10-20', 2),
(14, 45, '5.75', 80, '2027-02-05', 2),
(14, 46, '9.00', 70, '2026-12-25', 2),
(14, 49, '48.00', 32, '2026-10-30', 2),
(14, 50, '28.50', 45, '2026-11-10', 2),
(15, 47, '10.00', 65, '2027-01-20', 2),
(15, 48, '46.50', 33, '2026-10-25', 2),
(15, 51, '55.00', 25, '2026-11-30', 2),
(15, 52, '60.00', 22, '2026-12-10', 2);

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
  INDEX `idx_price_history_date` (`changed_at`),
  CONSTRAINT `price_history_ibfk_1` FOREIGN KEY (`pharmacy_medicine_id`) REFERENCES `pharmacy_medicines` (`id`) ON DELETE CASCADE,
  CONSTRAINT `price_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample price history
INSERT IGNORE INTO `price_history` (`id`, `pharmacy_medicine_id`, `old_price`, `new_price`, `changed_by`, `changed_at`) VALUES
(1, 1, '5.00', '5.50', 2, '2025-10-17 01:00:00'),
(2, 4, '5.50', '6.00', 2, '2025-10-17 01:30:00'),
(3, 2, '8.50', '8.75', 2, '2025-10-16 10:00:00'),
(4, 5, '10.00', '10.50', 2, '2025-10-16 14:30:00'),
(5, 6, '7.00', '7.25', 2, '2025-10-15 09:15:00');

-- --------------------------------------------------------
-- Table structure for table `notifications`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  INDEX `idx_notifications_read` (`read`),
  INDEX `idx_notifications_type` (`type`),
  INDEX `idx_notifications_user` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample notifications including expiry alerts (YOUR PROVIDED CODE)
INSERT IGNORE INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `read`, `created_at`) VALUES
(1, NULL, 'price_update', 'Price Update', 'Price for Biogesic updated at Mercury Drug - Pagadian City', 0, '2025-10-17 01:00:00'),
(2, NULL, 'price_update', 'Price Update', 'Price for Biogesic updated at Watson''s Pharmacy - Pagadian', 0, '2025-10-17 01:30:00'),
(3, 2, 'expiry_alert', 'Expiry Alert', 'Advil at Mercury Drug - Pagadian City expires on 2025-03-15', 0, '2025-10-17 02:00:00'),
(4, 2, 'low_stock', 'Low Stock Alert', 'Advil stock is low (5 units) at Mercury Drug - Pagadian City', 0, '2025-10-17 02:00:00'),
(5, 2, 'new_user', 'New User Registration', 'A new user has registered and is awaiting approval', 0, '2025-10-17 03:00:00'),
(6, 1, 'system', 'System Update', 'Database backup completed successfully', 1, '2025-10-16 23:00:00'),
(7, 2, 'expiry_alert', 'Expiry Alert', 'Imodium at Watson''s Pharmacy - Pagadian expires on 2025-11-30', 0, '2025-10-17 04:00:00'),
(8, NULL, 'price_update', 'Price Update', 'Price for Amoxil updated at Mercury Drug - Pagadian City', 0, '2025-10-17 05:00:00'),
(9, 1, 'low_stock', 'Low Stock Alert', 'Zyrtec stock is low (8 units) at Rose Pharmacy - Balangasan', 0, '2025-10-17 06:00:00'),
(10, NULL, 'new_pharmacy', 'New Pharmacy', 'A new pharmacy has been registered and needs verification', 0, '2025-10-17 07:00:00');

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
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_logs_entity` (`entity_type`,`entity_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`),
  INDEX `idx_activity_action` (`action`),
  CONSTRAINT `activity_logs_ibfk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample activity logs
INSERT IGNORE INTO `activity_logs` (`user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(2, 'UPDATE', 'pharmacy_medicines', 1, 'Updated price from 5.00 to 5.50', '192.168.1.100', 'Mozilla/5.0', '2025-10-17 01:00:00'),
(2, 'UPDATE', 'pharmacy_medicines', 4, 'Updated price from 5.50 to 6.00', '192.168.1.100', 'Mozilla/5.0', '2025-10-17 01:30:00'),
(1, 'LOGIN', 'users', 1, 'User logged in successfully', '192.168.1.101', 'Mozilla/5.0', '2025-10-17 02:00:00'),
(2, 'CREATE', 'notifications', 3, 'Created expiry alert notification', '192.168.1.100', 'Mozilla/5.0', '2025-10-17 02:00:00'),
(2, 'LOGIN', 'users', 2, 'User logged in successfully', '192.168.1.102', 'Chrome/120.0', '2025-10-17 03:00:00'),
(1, 'UPDATE', 'system_settings', 1, 'Updated low_stock_threshold to 10', '192.168.1.101', 'Mozilla/5.0', '2025-10-17 04:00:00'),
(2, 'UPDATE', 'pharmacy_medicines', 3, 'Updated stock from 25 to 30', '192.168.1.100', 'Mozilla/5.0', '2025-10-17 05:00:00'),
(3, 'REGISTER', 'users', 5, 'New user registration: Jane Smith', '192.168.1.103', 'Safari/16.0', '2025-10-17 06:00:00');

-- --------------------------------------------------------
-- Password reset table for security
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`email`),
  INDEX `idx_password_reset_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  INDEX `idx_pharmacy_id` (`pharmacy_id`),
  INDEX `idx_user_role` (`user_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Assign users to pharmacies for staff dashboard
INSERT IGNORE INTO `user_pharmacies` (`user_id`, `pharmacy_id`, `user_role`) VALUES
(1, 1, 'admin'),
(2, 1, 'staff'),
(1, 2, 'admin'),
(2, 3, 'staff'),
(1, 3, 'admin'),
(2, 2, 'staff'),
(1, 4, 'admin'),
(2, 5, 'staff'),
(1, 6, 'admin');

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
  INDEX `idx_stock` (`stock_quantity`),
  INDEX `idx_expiry` (`expiry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample medicines for staff inventory management
INSERT IGNORE INTO `medicines_inventory` (
  `pharmacy_id`, `name`, `generic_name`, `brand`, `category`, `price`, `stock_quantity`, `expiry_date`, `manufacturer`, `prescription_required`
) VALUES
(1, 'Biogesic', 'Paracetamol', 'United Laboratories', 'Analgesic', 5.50, 100, '2026-12-31', 'United Laboratories', 0),
(1, 'Advil', 'Ibuprofen', 'Pfizer', 'NSAID', 8.75, 5, '2025-03-15', 'Pfizer', 0),
(1, 'Amoxil', 'Amoxicillin', 'GlaxoSmithKline', 'Antibiotic', 12.00, 30, '2026-06-30', 'GlaxoSmithKline', 1),
(1, 'Imodium', 'Loperamide', 'Johnson & Johnson', 'Antidiarrheal', 10.50, 0, '2026-08-15', 'Johnson & Johnson', 0),
(1, 'Zyrtec', 'Cetirizine', 'Johnson & Johnson', 'Antihistamine', 15.00, 8, '2026-07-25', 'Johnson & Johnson', 0),
(2, 'Biogesic', 'Paracetamol', 'United Laboratories', 'Analgesic', 6.00, 80, '2026-08-15', 'United Laboratories', 0),
(2, 'Neozep', 'Phenylephrine', 'United Laboratories', 'Cold Medicine', 7.50, 25, '2026-09-10', 'United Laboratories', 0),
(3, 'Medicol', 'Ibuprofen', 'Medi-Rx', 'NSAID', 9.25, 12, '2026-05-20', 'Medi-Rx', 0),
(3, 'Solmux', 'Carbocisteine', 'Unilab', 'Expectorant', 85.00, 18, '2026-08-30', 'Unilab', 0),
(4, 'Tuseran Forte', 'Chlorpheniramine + Phenylpropanolamine', 'United Laboratories', 'Antihistamine', 6.50, 45, '2026-10-15', 'United Laboratories', 0),
(5, 'Enervon C', 'Multivitamin + Ascorbic Acid', 'Merck', 'Vitamin', 12.50, 50, '2026-12-31', 'Merck', 0);

-- --------------------------------------------------------
-- Create useful views for expiry and stock alerts
-- --------------------------------------------------------

-- Drop existing views if they exist
DROP VIEW IF EXISTS low_stock_alerts;
DROP VIEW IF EXISTS expiry_alerts;
DROP VIEW IF EXISTS available_medicines;
DROP VIEW IF EXISTS pharmacy_summary;

-- View for low stock alerts
CREATE VIEW low_stock_alerts AS
SELECT 
    pm.*,
    COALESCE(m.brand_name, m.name, 'Unknown') as medicine_name,
    COALESCE(m.scientific_name, m.name, 'Unknown') as generic_name,
    p.name as pharmacy_name,
    p.contact as pharmacy_contact,
    (SELECT value FROM system_settings WHERE `key` = 'low_stock_threshold') as threshold
FROM pharmacy_medicines pm
JOIN medicines m ON pm.medicine_id = m.id
JOIN pharmacies p ON pm.pharmacy_id = p.id
WHERE pm.stock <= (SELECT CAST(value AS UNSIGNED) FROM system_settings WHERE `key` = 'low_stock_threshold')
AND pm.stock > 0;

-- View for expiry alerts
CREATE VIEW expiry_alerts AS
SELECT 
    pm.*,
    COALESCE(m.brand_name, m.name, 'Unknown') as medicine_name,
    COALESCE(m.scientific_name, m.name, 'Unknown') as generic_name,
    p.name as pharmacy_name,
    p.contact as pharmacy_contact,
    DATEDIFF(pm.expiry_date, CURDATE()) as days_until_expiry
FROM pharmacy_medicines pm
JOIN medicines m ON pm.medicine_id = m.id
JOIN pharmacies p ON pm.pharmacy_id = p.id
WHERE pm.expiry_date IS NOT NULL 
AND pm.expiry_date <= DATE_ADD(CURDATE(), INTERVAL (SELECT CAST(value AS UNSIGNED) FROM system_settings WHERE `key` = 'expiry_alert_days') DAY)
AND pm.expiry_date >= CURDATE();

-- View for available medicines across all pharmacies
CREATE VIEW available_medicines AS
SELECT 
    m.id as medicine_id,
    m.name as medicine_name,
    m.scientific_name,
    m.brand_name,
    m.category,
    COUNT(DISTINCT pm.pharmacy_id) as available_in_pharmacies,
    MIN(pm.price) as lowest_price,
    MAX(pm.price) as highest_price,
    AVG(pm.price) as average_price
FROM medicines m
LEFT JOIN pharmacy_medicines pm ON m.id = pm.medicine_id AND pm.stock > 0
WHERE m.status = 'active'
GROUP BY m.id, m.name, m.scientific_name, m.brand_name, m.category;

-- View for pharmacy summary
CREATE VIEW pharmacy_summary AS
SELECT 
    p.id,
    p.name,
    p.address,
    p.contact,
    p.verified,
    COUNT(DISTINCT pm.medicine_id) as total_medicines,
    SUM(pm.stock) as total_stock,
    COUNT(DISTINCT up.user_id) as total_staff
FROM pharmacies p
LEFT JOIN pharmacy_medicines pm ON p.id = pm.pharmacy_id
LEFT JOIN user_pharmacies up ON p.id = up.pharmacy_id
GROUP BY p.id, p.name, p.address, p.contact, p.verified;

-- --------------------------------------------------------
-- Stored Procedures for common operations
-- --------------------------------------------------------

DELIMITER $$

-- Procedure to update stock and log the change
CREATE PROCEDURE IF NOT EXISTS update_medicine_stock(
    IN p_pharmacy_medicine_id INT,
    IN p_new_stock INT,
    IN p_updated_by INT
)
BEGIN
    DECLARE old_stock INT;
    DECLARE v_pharmacy_id INT;
    DECLARE v_medicine_id INT;
    DECLARE v_medicine_name VARCHAR(255);
    DECLARE v_pharmacy_name VARCHAR(255);
    
    -- Get current stock and IDs
    SELECT pm.stock, pm.pharmacy_id, pm.medicine_id, 
           COALESCE(m.brand_name, m.name), p.name
    INTO old_stock, v_pharmacy_id, v_medicine_id, v_medicine_name, v_pharmacy_name
    FROM pharmacy_medicines pm
    JOIN medicines m ON pm.medicine_id = m.id
    JOIN pharmacies p ON pm.pharmacy_id = p.id
    WHERE pm.id = p_pharmacy_medicine_id;
    
    -- Update stock
    UPDATE pharmacy_medicines 
    SET stock = p_new_stock, updated_by = p_updated_by, updated_at = NOW()
    WHERE id = p_pharmacy_medicine_id;
    
    -- Log the activity
    INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details)
    VALUES (p_updated_by, 'UPDATE_STOCK', 'pharmacy_medicines', p_pharmacy_medicine_id, 
            CONCAT('Stock updated from ', old_stock, ' to ', p_new_stock, ' for ', v_medicine_name, ' at ', v_pharmacy_name));
    
    -- Check and create low stock alert if needed
    IF p_new_stock <= (SELECT CAST(value AS UNSIGNED) FROM system_settings WHERE `key` = 'low_stock_threshold') AND p_new_stock > 0 THEN
        INSERT INTO notifications (user_id, type, title, message, created_at)
        VALUES (p_updated_by, 'low_stock', 'Low Stock Alert', 
                CONCAT(v_medicine_name, ' stock is low (', p_new_stock, ' units) at ', v_pharmacy_name),
                NOW());
    END IF;
END$$

-- Procedure to update price and log the change
CREATE PROCEDURE IF NOT EXISTS update_medicine_price(
    IN p_pharmacy_medicine_id INT,
    IN p_new_price DECIMAL(10,2),
    IN p_updated_by INT
)
BEGIN
    DECLARE old_price DECIMAL(10,2);
    DECLARE v_medicine_name VARCHAR(255);
    DECLARE v_pharmacy_name VARCHAR(255);
    
    -- Get current price and names
    SELECT pm.price, COALESCE(m.brand_name, m.name), p.name
    INTO old_price, v_medicine_name, v_pharmacy_name
    FROM pharmacy_medicines pm
    JOIN medicines m ON pm.medicine_id = m.id
    JOIN pharmacies p ON pm.pharmacy_id = p.id
    WHERE pm.id = p_pharmacy_medicine_id;
    
    -- Update price
    UPDATE pharmacy_medicines 
    SET price = p_new_price, updated_by = p_updated_by, updated_at = NOW()
    WHERE id = p_pharmacy_medicine_id;
    
    -- Add to price history
    INSERT INTO price_history (pharmacy_medicine_id, old_price, new_price, changed_by)
    VALUES (p_pharmacy_medicine_id, old_price, p_new_price, p_updated_by);
    
    -- Log the activity
    INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details)
    VALUES (p_updated_by, 'UPDATE_PRICE', 'pharmacy_medicines', p_pharmacy_medicine_id, 
            CONCAT('Price updated from ', old_price, ' to ', p_new_price, ' for ', v_medicine_name, ' at ', v_pharmacy_name));
    
    -- Create notification
    INSERT INTO notifications (type, title, message, created_at)
    VALUES ('price_update', 'Price Update', 
            CONCAT('Price for ', v_medicine_name, ' updated at ', v_pharmacy_name, ' from PHP ', old_price, ' to PHP ', p_new_price),
            NOW());
END$$

DELIMITER ;

-- --------------------------------------------------------
-- Update AUTO_INCREMENT values
-- --------------------------------------------------------

ALTER TABLE `users` AUTO_INCREMENT=100;
ALTER TABLE `pharmacies` AUTO_INCREMENT=100;
ALTER TABLE `medicines` AUTO_INCREMENT=200;
ALTER TABLE `pharmacy_medicines` AUTO_INCREMENT=1000;
ALTER TABLE `price_history` AUTO_INCREMENT=100;
ALTER TABLE `notifications` AUTO_INCREMENT=100;
ALTER TABLE `activity_logs` AUTO_INCREMENT=100;
ALTER TABLE `user_pharmacies` AUTO_INCREMENT=100;
ALTER TABLE `medicines_inventory` AUTO_INCREMENT=100;

-- --------------------------------------------------------
-- Create scheduled events for automated tasks
-- --------------------------------------------------------

-- Enable event scheduler
SET GLOBAL event_scheduler = ON;

-- Drop existing events if they exist
DROP EVENT IF EXISTS daily_stock_check;
DROP EVENT IF EXISTS daily_expiry_check;
DROP EVENT IF EXISTS cleanup_old_logs;

-- Event for daily stock check
CREATE EVENT IF NOT EXISTS daily_stock_check
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    -- Call stored procedure for low stock alerts
    CALL check_low_stock_alerts();
END;

-- Event for daily expiry check
CREATE EVENT IF NOT EXISTS daily_expiry_check
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    -- Call stored procedure for expiry alerts
    CALL check_expiry_alerts();
END;

-- Event for cleaning up old logs (keep logs for 90 days)
CREATE EVENT IF NOT EXISTS cleanup_old_logs
ON SCHEDULE EVERY 1 WEEK
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
    DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY) AND `read` = 1;
    DELETE FROM password_resets WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY);
END;

-- --------------------------------------------------------
-- Create triggers for automated actions
-- --------------------------------------------------------

DELIMITER $$

-- Trigger to update updated_at timestamp on pharmacy_medicines
CREATE TRIGGER IF NOT EXISTS before_pharmacy_medicines_update
BEFORE UPDATE ON pharmacy_medicines
FOR EACH ROW
BEGIN
    SET NEW.updated_at = NOW();
END$$

-- Trigger to create notification when stock is updated to low level
CREATE TRIGGER IF NOT EXISTS after_pharmacy_medicines_update
AFTER UPDATE ON pharmacy_medicines
FOR EACH ROW
BEGIN
    DECLARE threshold INT;
    DECLARE medicine_name VARCHAR(255);
    DECLARE pharmacy_name VARCHAR(255);
    
    -- Get low stock threshold
    SELECT CAST(value AS UNSIGNED) INTO threshold 
    FROM system_settings WHERE `key` = 'low_stock_threshold';
    
    -- Get medicine and pharmacy names
    SELECT COALESCE(m.brand_name, m.name), p.name 
    INTO medicine_name, pharmacy_name
    FROM medicines m, pharmacies p
    WHERE m.id = NEW.medicine_id AND p.id = NEW.pharmacy_id;
    
    -- Check if stock became low
    IF NEW.stock <= threshold AND OLD.stock > threshold AND NEW.stock > 0 THEN
        INSERT INTO notifications (type, title, message, created_at)
        VALUES ('low_stock', 'Low Stock Alert', 
                CONCAT(medicine_name, ' stock is now low (', NEW.stock, ' units) at ', pharmacy_name),
                NOW());
    END IF;
    
    -- Check if stock became zero
    IF NEW.stock = 0 AND OLD.stock > 0 THEN
        INSERT INTO notifications (type, title, message, created_at)
        VALUES ('out_of_stock', 'Out of Stock', 
                CONCAT(medicine_name, ' is now out of stock at ', pharmacy_name),
                NOW());
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------
-- Display setup completion message with statistics
-- --------------------------------------------------------

SELECT '========================================' as separator;
SELECT 'MYPHARMA DATABASE SETUP COMPLETED' as message;
SELECT '========================================' as separator;
SELECT '' as empty_line;
SELECT 'DATABASE STATISTICS:' as section;
SELECT '========================================' as separator;
SELECT CONCAT('✓ Users: ', COUNT(*)) as count FROM `users`;
SELECT CONCAT('✓ Pharmacies: ', COUNT(*)) as count FROM `pharmacies`;
SELECT CONCAT('✓ Medicines: ', COUNT(*)) as count FROM `medicines`;
SELECT CONCAT('✓ Pharmacy Medicines: ', COUNT(*)) as count FROM `pharmacy_medicines`;
SELECT CONCAT('✓ Price History: ', COUNT(*)) as count FROM `price_history`;
SELECT CONCAT('✓ Notifications: ', COUNT(*)) as count FROM `notifications`;
SELECT CONCAT('✓ Activity Logs: ', COUNT(*)) as count FROM `activity_logs`;
SELECT CONCAT('✓ User Pharmacies: ', COUNT(*)) as count FROM `user_pharmacies`;
SELECT CONCAT('✓ Medicines Inventory: ', COUNT(*)) as count FROM `medicines_inventory`;
SELECT '' as empty_line;
SELECT 'ALERT STATISTICS:' as section;
SELECT '========================================' as separator;
SELECT CONCAT('⚠ Low Stock Items: ', COUNT(*)) as count FROM low_stock_alerts;
SELECT CONCAT('⚠ Expiry Alert Items: ', COUNT(*)) as count FROM expiry_alerts;
SELECT '' as empty_line;
SELECT 'SAMPLE LOGIN CREDENTIALS:' as section;
SELECT '========================================' as separator;
SELECT '👑 Admin: admin@mypharma.com / password' as credentials;
SELECT '👨‍💼 Staff: staff@mypharma.com / password' as credentials;
SELECT '👤 User: user@mypharma.com / password' as credentials;
SELECT '' as empty_line;
SELECT 'TEST NOTIFICATIONS CREATED:' as section;
SELECT '========================================' as separator;
SELECT CONCAT('• Price Updates: ', COUNT(*)) as count FROM notifications WHERE type = 'price_update';
SELECT CONCAT('• Expiry Alerts: ', COUNT(*)) as count FROM notifications WHERE type = 'expiry_alert';
SELECT CONCAT('• Low Stock Alerts: ', COUNT(*)) as count FROM notifications WHERE type = 'low_stock';
SELECT CONCAT('• System Notifications: ', COUNT(*)) as count FROM notifications WHERE type = 'system';
SELECT '' as empty_line;
SELECT 'AUTOMATED FEATURES:' as section;
SELECT '========================================' as separator;
SELECT '✅ Scheduled Events: daily_stock_check, daily_expiry_check, cleanup_old_logs' as feature;
SELECT '✅ Stored Procedures: update_medicine_stock, update_medicine_price' as feature;
SELECT '✅ Database Triggers: Automatic timestamp updates, stock alerts' as feature;
SELECT '✅ Views: low_stock_alerts, expiry_alerts, available_medicines, pharmacy_summary' as feature;
SELECT '' as empty_line;
SELECT 'SETUP COMPLETED SUCCESSFULLY! 🎉' as final_message;
SELECT 'MyPharma Database is ready for use.' as status;
SELECT '========================================' as separator;

-- --------------------------------------------------------
-- Sample queries to test the database
-- --------------------------------------------------------

SELECT '' as empty_line;
SELECT 'SAMPLE QUERIES TO TEST:' as section;
SELECT '========================================' as separator;
SELECT '1. Find Biogesic availability:' as query;
SELECT '   SELECT * FROM available_medicines WHERE medicine_name LIKE "%Biogesic%";' as query_example;
SELECT '' as empty_line;
SELECT '2. Check low stock alerts:' as query;
SELECT '   SELECT * FROM low_stock_alerts;' as query_example;
SELECT '' as empty_line;
SELECT '3. View pharmacy summary:' as query;
SELECT '   SELECT * FROM pharmacy_summary;' as query_example;
SELECT '' as empty_line;
SELECT '4. Get user notifications:' as query;
SELECT '   SELECT * FROM notifications WHERE user_id = 2 OR user_id IS NULL ORDER BY created_at DESC;' as query_example;
SELECT '' as empty_line;
SELECT '5. Find medicines by category:' as query;
SELECT '   SELECT name, brand_name, category FROM medicines WHERE category = "Analgesic";' as query_example;
SELECT '' as empty_line;
SELECT 'Database ready! Start developing your MyPharma application.' as ready_message;

-- --------------------------------------------------------
-- End of script
-- --------------------------------------------------------

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
