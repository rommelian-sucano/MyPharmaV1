-- ===========================================

-- ===========================================

-- Start transaction for data consistency
START TRANSACTION;

-- --------------------------------------------------------
-- 1. ADD 10 NEW IN-DEMAND MEDICINES
-- --------------------------------------------------------

INSERT INTO `medicines` (
    `name`, `scientific_name`, `brand_name`, `manufacturer`, 
    `dosage_form`, `strength`, `description`, `uses`, 
    `side_effects`, `contraindications`, `status`, `category`
) VALUES
-- 1. Mefenamic Acid (Dolfenal) - Pain & Fever
(
    'Dolfenal', 
    'Mefenamic Acid', 
    'Dolfenal', 
    'United Laboratories', 
    'Capsule', 
    '500mg', 
    'A trusted non-steroidal anti-inflammatory drug (NSAID) commonly available in Pagadian City pharmacies.', 
    'Relief of mild to moderate pain, fever, and inflammation (e.g., headache, toothache, menstrual pain).', 
    'May cause stomach upset, heartburn, or dizziness. Rarely, it can affect kidney function.', 
    'Patients with peptic ulcers, severe kidney disease, or allergy to NSAIDs. Avoid in late pregnancy.', 
    'active', 
    'NSAID'
),

-- 2. Metformin (Glucophage) - Diabetes
(
    'Glucophage', 
    'Metformin HCl', 
    'Glucophage', 
    'Merck', 
    'Tablet', 
    '500mg', 
    'First-line medication for type 2 diabetes, widely available in Pagadian City.', 
    'Lowers blood sugar levels in type 2 diabetes. May also be used for polycystic ovary syndrome (PCOS).', 
    'Commonly causes gastrointestinal issues like nausea and diarrhea, especially at treatment start.', 
    'Contraindicated in patients with severe kidney impairment, liver disease, or conditions causing tissue hypoxia.', 
    'active', 
    'Antidiabetic'
),

-- 3. Amlodipine (Norvasc) - Hypertension
(
    'Norvasc', 
    'Amlodipine Besylate', 
    'Norvasc', 
    'Pfizer', 
    'Tablet', 
    '5mg', 
    'Most common calcium channel blocker for hypertension treatment in Pagadian City.', 
    'Lowers blood pressure and prevents chest pain (angina) by relaxing blood vessels.', 
    'Common side effects include swelling of the ankles/feet, dizziness, and flushing.', 
    'Patients with severe hypotension or known hypersensitivity to amlodipine.', 
    'active', 
    'Calcium Channel Blocker'
),

-- 4. Salbutamol (Ventolin) - Asthma/COPD
(
    'Ventolin Syrup', 
    'Salbutamol Sulfate', 
    'Ventolin', 
    'GlaxoSmithKline', 
    'Syrup', 
    '2mg/5ml', 
    'Essential bronchodilator for asthma and COPD management in Pagadian City.', 
    'Treats and prevents bronchospasm in asthma and chronic obstructive pulmonary disease (COPD).', 
    'May cause tremor, nervousness, headache, and a fast heartbeat.', 
    'Caution in patients with hyperthyroidism, heart conditions, or sensitivity to sympathomimetic amines.', 
    'active', 
    'Bronchodilator'
),

-- 5. Co-amoxiclav (Augmentin) - Antibiotic
(
    'Augmentin', 
    'Co-amoxiclav', 
    'Augmentin', 
    'GlaxoSmithKline', 
    'Tablet', 
    '625mg', 
    'Broad-spectrum antibiotic commonly prescribed for infections in Pagadian City.', 
    'Treats bacterial infections such as sinusitis, pneumonia, urinary tract infections, and skin infections.', 
    'Commonly causes diarrhea. May lead to yeast infections or allergic reactions.', 
    'Patients with a history of severe allergic reaction to penicillins or hepatic dysfunction.', 
    'active', 
    'Antibiotic'
),

-- 6. Omeprazole (Losec) - Antacid
(
    'Losec', 
    'Omeprazole', 
    'Losec', 
    'AstraZeneca', 
    'Capsule', 
    '20mg', 
    'Proton pump inhibitor widely used for acid-related disorders in Pagadian City.', 
    'Treats gastroesophageal reflux disease (GERD), peptic ulcers, and Zollinger-Ellison syndrome.', 
    'Generally well-tolerated. May cause headache, nausea, or abdominal pain.', 
    'Concurrent use with drugs like rilpivirine or nelfinavir.', 
    'active', 
    'PPI'
),

-- 7. Loratadine (Claritin) - Allergy
(
    'Claritin', 
    'Loratadine', 
    'Claritin', 
    'MSD', 
    'Tablet', 
    '10mg', 
    'Non-drowsy antihistamine popular for allergy relief in Pagadian City.', 
    'Relieves symptoms of allergic rhinitis (hay fever) and chronic urticaria (hives).', 
    'Generally has few side effects. May rarely cause headache or fatigue.', 
    'Known hypersensitivity to loratadine or any component.', 
    'active', 
    'Antihistamine'
),

-- 8. Loperamide (Imodium) - Antidiarrheal
(
    'Imodium', 
    'Loperamide HCl', 
    'Imodium', 
    'Johnson & Johnson', 
    'Capsule', 
    '2mg', 
    'Essential medicine for acute diarrhea control, widely available in Pagadian City.', 
    'Controls and provides relief from the symptoms of acute, non-specific diarrhea.', 
    'Constipation, dizziness, or abdominal cramping.', 
    'Not for use in dysentery (diarrhea with blood/mucus) or pseudomembranous colitis.', 
    'active', 
    'Antidiarrheal'
),

-- 9. Vitamin C (Poten-Cee) - Vitamin
(
    'Poten-Cee', 
    'Ascorbic Acid', 
    'Poten-Cee', 
    'United Laboratories', 
    'Tablet', 
    '500mg', 
    'Most popular vitamin C supplement for immune support in Pagadian City.', 
    'Prevention and treatment of vitamin C deficiency. Supports immune function and wound healing.', 
    'Generally safe. Very high doses may cause diarrhea, nausea, or stomach cramps.', 
    'No significant contraindications at standard doses.', 
    'active', 
    'Vitamin'
),

-- 10. Carbocisteine (Solmux) - Cough
(
    'Solmux', 
    'Carbocisteine', 
    'Solmux', 
    'United Laboratories', 
    'Capsule', 
    '500mg', 
    'Trusted mucolytic agent for productive cough relief in Pagadian City.', 
    'Relieves productive cough by thinning and breaking down thick phlegm in respiratory tract infections.', 
    'Generally well-tolerated. May cause mild gastrointestinal upset.', 
    'Active peptic ulcer. Caution in patients with history of gastrointestinal bleeding.', 
    'active', 
    'Expectorant'
);

-- --------------------------------------------------------
-- 2. GET THE IDS OF THE NEWLY ADDED MEDICINES
-- --------------------------------------------------------

-- Create a temporary table to store the IDs of new medicines
CREATE TEMPORARY TABLE IF NOT EXISTS new_medicine_ids (
    medicine_id INT PRIMARY KEY,
    medicine_name VARCHAR(100),
    category VARCHAR(100),
    base_price DECIMAL(10,2)
);

-- Insert the IDs of the 10 new medicines we just added
INSERT INTO new_medicine_ids (medicine_id, medicine_name, category, base_price)
SELECT 
    id, 
    name, 
    category,
    CASE 
        WHEN name = 'Dolfenal' THEN 8.50
        WHEN name = 'Glucophage' THEN 12.00
        WHEN name = 'Norvasc' THEN 7.75
        WHEN name = 'Ventolin Syrup' THEN 120.00
        WHEN name = 'Augmentin' THEN 65.00
        WHEN name = 'Losec' THEN 18.50
        WHEN name = 'Claritin' THEN 9.25
        WHEN name = 'Imodium' THEN 11.00
        WHEN name = 'Poten-Cee' THEN 7.00
        WHEN name = 'Solmux' THEN 85.00
    END as base_price
FROM medicines 
WHERE name IN (
    'Dolfenal', 'Glucophage', 'Norvasc', 'Ventolin Syrup', 
    'Augmentin', 'Losec', 'Claritin', 'Imodium', 
    'Poten-Cee', 'Solmux'
) 
ORDER BY id DESC 
LIMIT 10;

-- --------------------------------------------------------
-- 3. STOCK ALL NEW MEDICINES IN ALL 15 PHARMACIES
-- --------------------------------------------------------

-- Variable to track progress
SET @pharmacy_count = 0;
SET @medicine_count = 0;

-- Get total counts for logging
SELECT COUNT(*) INTO @pharmacy_count FROM pharmacies;
SELECT COUNT(*) INTO @medicine_count FROM new_medicine_ids;

-- Insert stock for each pharmacy-medicine combination
INSERT INTO `pharmacy_medicines` (
    `pharmacy_id`, `medicine_id`, `price`, `stock`, `expiry_date`, `updated_by`
)
SELECT 
    p.id as pharmacy_id,
    nm.medicine_id as medicine_id,
    -- Price variation: ±10% from base price for realistic pricing
    ROUND(nm.base_price * (0.9 + (RAND() * 0.2)), 2) as price,
    
    -- Realistic stock levels based on category
    CASE nm.category
        -- High demand OTC items
        WHEN 'Vitamin' THEN FLOOR(80 + (RAND() * 40))
        WHEN 'NSAID' THEN FLOOR(60 + (RAND() * 40))
        WHEN 'Antidiarrheal' THEN FLOOR(50 + (RAND() * 30))
        WHEN 'Expectorant' THEN FLOOR(40 + (RAND() * 30))
        WHEN 'Antihistamine' THEN FLOOR(45 + (RAND() * 25))
        
        -- Prescription/common maintenance drugs
        WHEN 'PPI' THEN FLOOR(35 + (RAND() * 25))
        WHEN 'Calcium Channel Blocker' THEN FLOOR(30 + (RAND() * 20))
        WHEN 'Antidiabetic' THEN FLOOR(25 + (RAND() * 20))
        WHEN 'Bronchodilator' THEN FLOOR(20 + (RAND() * 15))
        WHEN 'Antibiotic' THEN FLOOR(25 + (RAND() * 20))
        ELSE FLOOR(30 + (RAND() * 20))
    END as stock,
    
    -- Expiry dates: 6 months to 2 years from now
    DATE_ADD(
        CURDATE(), 
        INTERVAL FLOOR(180 + (RAND() * 550)) DAY
    ) as expiry_date,
    
    -- Updated by admin (user_id 1)
    1 as updated_by
    
FROM 
    pharmacies p
CROSS JOIN 
    new_medicine_ids nm
WHERE 
    p.id BETWEEN 1 AND 15  -- All 15 pharmacies from your setup
ORDER BY 
    p.id, nm.medicine_id;

-- --------------------------------------------------------
-- 4. CREATE PRICE HISTORY ENTRIES
-- --------------------------------------------------------

-- Add initial price history entries for the new stock
INSERT INTO `price_history` (
    `pharmacy_medicine_id`, `old_price`, `new_price`, `changed_by`
)
SELECT 
    pm.id as pharmacy_medicine_id,
    pm.price as old_price,  -- Same as new price initially
    pm.price as new_price,
    1 as changed_by  -- Changed by admin
FROM 
    pharmacy_medicines pm
    INNER JOIN new_medicine_ids nm ON pm.medicine_id = nm.medicine_id
WHERE 
    pm.pharmacy_id BETWEEN 1 AND 15;

-- --------------------------------------------------------
-- 5. CREATE INITIAL NOTIFICATIONS FOR NEW STOCK
-- --------------------------------------------------------

-- Notification for new medicines added
INSERT INTO `notifications` (`type`, `title`, `message`, `created_at`)
VALUES 
    ('system', 'New Medicines Added', CONCAT(@medicine_count, ' new in-demand medicines have been added to the system and stocked across all ', @pharmacy_count, ' pharmacies.'), NOW()),
    ('system', 'Inventory Updated', 'Medicine inventory has been updated with essential drugs for Pagadian City residents.', NOW());

-- Create low stock alerts for any items with stock < 10
INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `created_at`)
SELECT 
    1 as user_id,  -- Notify admin
    'low_stock' as type,
    'Low Stock Alert' as title,
    CONCAT(
        (SELECT name FROM medicines WHERE id = pm.medicine_id),
        ' stock is low (', pm.stock, ' units) at ',
        (SELECT name FROM pharmacies WHERE id = pm.pharmacy_id)
    ) as message,
    NOW() as created_at
FROM 
    pharmacy_medicines pm
    INNER JOIN new_medicine_ids nm ON pm.medicine_id = nm.medicine_id
WHERE 
    pm.stock < 10 
    AND pm.stock > 0;

-- --------------------------------------------------------
-- 6. LOG THE ACTIVITY
-- --------------------------------------------------------

INSERT INTO `activity_logs` (
    `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`
)
VALUES 
    (
        1, 
        'BULK_INSERT', 
        'medicines', 
        0, 
        CONCAT('Added ', @medicine_count, ' new in-demand medicines for Pagadian City and stocked them in all ', @pharmacy_count, ' pharmacies.'),
        '127.0.0.1'
    );

-- --------------------------------------------------------
-- 7. CLEAN UP TEMPORARY TABLE
-- --------------------------------------------------------

DROP TEMPORARY TABLE IF EXISTS new_medicine_ids;

-- --------------------------------------------------------
-- 8. COMMIT ALL CHANGES
-- --------------------------------------------------------

COMMIT;

-- --------------------------------------------------------
-- 9. VERIFICATION QUERIES
-- --------------------------------------------------------

SELECT '========================================' as '';
SELECT 'UPDATE COMPLETED SUCCESSFULLY!' as '';
SELECT '========================================' as '';
SELECT '' as '';

SELECT 'DATABASE STATISTICS AFTER UPDATE:' as '';
SELECT '========================================' as '';
SELECT CONCAT('Total Medicines: ', COUNT(*)) FROM `medicines`;
SELECT CONCAT('Total Pharmacy Medicine Records: ', COUNT(*)) FROM `pharmacy_medicines`;
SELECT '' as '';

SELECT 'NEWLY ADDED MEDICINES:' as '';
SELECT '========================================' as '';
SELECT id, name, scientific_name, category 
FROM medicines 
ORDER BY id DESC 
LIMIT 10;
SELECT '' as '';

SELECT 'STOCK DISTRIBUTION SUMMARY:' as '';
SELECT '========================================' as '';
SELECT 
    p.name as 'Pharmacy',
    COUNT(pm.medicine_id) as 'Total Medicines',
    SUM(pm.stock) as 'Total Stock',
    ROUND(AVG(pm.price), 2) as 'Avg Price'
FROM pharmacy_medicines pm
JOIN pharmacies p ON pm.pharmacy_id = p.id
JOIN medicines m ON pm.medicine_id = m.id
WHERE m.id IN (
    SELECT id FROM medicines 
    WHERE name IN ('Dolfenal', 'Glucophage', 'Norvasc', 'Ventolin Syrup', 'Augmentin', 
                   'Losec', 'Claritin', 'Imodium', 'Poten-Cee', 'Solmux')
)
GROUP BY p.id, p.name
ORDER BY p.name;
SELECT '' as '';

SELECT 'SAMPLE STOCK CHECK (Solmux across pharmacies):' as '';
SELECT '========================================' as '';
SELECT 
    p.name as 'Pharmacy',
    pm.price as 'Price (₱)',
    pm.stock as 'Stock',
    DATE_FORMAT(pm.expiry_date, '%Y-%m-%d') as 'Expiry Date'
FROM pharmacy_medicines pm
JOIN pharmacies p ON pm.pharmacy_id = p.id
JOIN medicines m ON pm.medicine_id = m.id
WHERE m.name = 'Solmux'
ORDER BY p.name;
SELECT '' as '';

SELECT 'CATEGORY DISTRIBUTION OF NEW MEDICINES:' as '';
SELECT '========================================' as '';
SELECT 
    category as 'Medicine Category',
    COUNT(*) as 'Number of Items',
    CONCAT('₱', FORMAT(MIN(
        SELECT MIN(price) 
        FROM pharmacy_medicines pm2 
        WHERE pm2.medicine_id = m.id
    ), 2)) as 'Lowest Price',
    CONCAT('₱', FORMAT(MAX(
        SELECT MAX(price) 
        FROM pharmacy_medicines pm2 
        WHERE pm2.medicine_id = m.id
    ), 2)) as 'Highest Price'
FROM medicines m
WHERE m.name IN (
    'Dolfenal', 'Glucophage', 'Norvasc', 'Ventolin Syrup', 'Augmentin',
    'Losec', 'Claritin', 'Imodium', 'Poten-Cee', 'Solmux'
)
GROUP BY category
ORDER BY COUNT(*) DESC;
SELECT '' as '';

SELECT 'READY FOR USE!' as '';
SELECT '========================================' as '';
SELECT 'The new medicines are now available in all 15 pharmacies.' as '';
SELECT 'Patients in Pagadian City can now search for these essential medicines.' as '';
