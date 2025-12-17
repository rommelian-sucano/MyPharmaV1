<?php
// Generate 100 Sample Medicines and Associate with Pharmacies in MyPharma Application
require_once 'db.php';

// First, ensure the medicines table has all required columns
echo "Ensuring medicines table structure is correct...\n";

$alterStatements = [
    "ALTER TABLE medicines ADD COLUMN IF NOT EXISTS brand_name VARCHAR(100)",
    "ALTER TABLE medicines ADD COLUMN IF NOT EXISTS scientific_name VARCHAR(100)",
    "ALTER TABLE medicines ADD COLUMN IF NOT EXISTS manufacturer VARCHAR(255)",
    "ALTER TABLE medicines ADD COLUMN IF NOT EXISTS dosage_form VARCHAR(100)",
    "ALTER TABLE medicines ADD COLUMN IF NOT EXISTS strength VARCHAR(100)",
    "ALTER TABLE medicines ADD COLUMN IF NOT EXISTS description TEXT",
    "ALTER TABLE medicines ADD COLUMN IF NOT EXISTS uses TEXT",
    "ALTER TABLE medicines ADD COLUMN IF NOT EXISTS side_effects TEXT",
    "ALTER TABLE medicines ADD COLUMN IF NOT EXISTS contraindications TEXT",
    "ALTER TABLE medicines ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'active'",
    "ALTER TABLE medicines ADD COLUMN IF NOT EXISTS image_path VARCHAR(255)",
    "ALTER TABLE medicines ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
];

foreach ($alterStatements as $statement) {
    if ($conn->query($statement) === TRUE) {
        echo "Successfully executed: " . substr($statement, 0, 50) . "...\n";
    } else {
        echo "Error executing statement: " . $conn->error . "\n";
    }
}

// Array of well-known medicines in Pagadian City
$medicines = [
    // Pain Relief & Fever
    ['brand_name' => 'Biogesic', 'scientific_name' => 'Paracetamol', 'manufacturer' => 'United Laboratories', 'dosage_form' => 'Tablet', 'strength' => '500mg'],
    ['brand_name' => 'Alaxan FR', 'scientific_name' => 'Ibuprofen + Paracetamol', 'manufacturer' => 'United Laboratories', 'dosage_form' => 'Capsule', 'strength' => '200mg+325mg'],
    ['brand_name' => 'Medicol Advance', 'scientific_name' => 'Ibuprofen', 'manufacturer' => 'Medilife', 'dosage_form' => 'Tablet', 'strength' => '400mg'],
    ['brand_name' => 'Neozep Forte', 'scientific_name' => 'Phenylephrine + Chlorpheniramine', 'manufacturer' => 'United Laboratories', 'dosage_form' => 'Tablet', 'strength' => '10mg+4mg'],
    ['brand_name' => 'Decolgen Forte', 'scientific_name' => 'Phenylpropanolamine + Diphenhydramine', 'manufacturer' => 'GlaxoSmithKline', 'dosage_form' => 'Tablet', 'strength' => '12.5mg+25mg'],
    
    // Cold & Flu
    ['brand_name' => 'Tuseran Forte', 'scientific_name' => 'Chlorpheniramine + Phenylpropanolamine', 'manufacturer' => 'United Laboratories', 'dosage_form' => 'Syrup', 'strength' => '4mg/5ml'],
    ['brand_name' => 'Solmux Broncho', 'scientific_name' => 'Ambroxol', 'manufacturer' => 'Sanofi', 'dosage_form' => 'Syrup', 'strength' => '30mg/5ml'],
    ['brand_name' => 'Expess T.Forte', 'scientific_name' => 'Tripolidine + Phenylpropanolamine', 'manufacturer' => 'United Laboratories', 'dosage_form' => 'Tablet', 'strength' => '2.5mg+12.5mg'],
    ['brand_name' => 'Allerkid', 'scientific_name' => 'Chlorpheniramine', 'manufacturer' => 'United Laboratories', 'dosage_form' => 'Syrup', 'strength' => '2mg/5ml'],
    
    // Vitamins & Supplements
    ['brand_name' => 'Enervon C', 'scientific_name' => 'Multivitamin + Ascorbic Acid', 'manufacturer' => 'Merck', 'dosage_form' => 'Tablet', 'strength' => 'Various'],
    ['brand_name' => 'Vita-C Forte', 'scientific_name' => 'Ascorbic Acid', 'manufacturer' => 'United Laboratories', 'dosage_form' => 'Tablet', 'strength' => '500mg'],
    ['brand_name' => 'Femvital', 'scientific_name' => 'Multivitamin + Iron', 'manufacturer' => 'United Laboratories', 'dosage_form' => 'Tablet', 'strength' => 'Various'],
    
    // Digestive Health
    ['brand_name' => 'Bonamin', 'scientific_name' => 'Dimenhydrinate', 'manufacturer' => 'United Laboratories', 'dosage_form' => 'Tablet', 'strength' => '50mg'],
    ['brand_name' => 'Lomotil', 'scientific_name' => 'Loperamide', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Capsule', 'strength' => '2mg'],
    ['brand_name' => 'Diarium', 'scientific_name' => 'Racecadotril', 'manufacturer' => 'Sanofi', 'dosage_form' => 'Capsule', 'strength' => '100mg'],
    
    // Anti-inflammatory
    ['brand_name' => 'Voltaren', 'scientific_name' => 'Diclofenac Sodium', 'manufacturer' => 'Novartis', 'dosage_form' => 'Gel', 'strength' => '1%'],
    ['brand_name' => 'Cataflam', 'scientific_name' => 'Diclofenac Potassium', 'manufacturer' => 'Novartis', 'dosage_form' => 'Tablet', 'strength' => '25mg'],
    ['brand_name' => 'Profenid', 'scientific_name' => 'Diclofenac Sodium', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Injection', 'strength' => '75mg/ml'],
    ['brand_name' => 'Mefenamic', 'scientific_name' => 'Mefenamic Acid', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Capsule', 'strength' => '250mg'],
    
    // Antibiotics
    ['brand_name' => 'Amoxil', 'scientific_name' => 'Amoxicillin', 'manufacturer' => 'GlaxoSmithKline', 'dosage_form' => 'Capsule', 'strength' => '500mg'],
    ['brand_name' => 'Augmentin', 'scientific_name' => 'Amoxicillin + Clavulanic Acid', 'manufacturer' => 'GlaxoSmithKline', 'dosage_form' => 'Tablet', 'strength' => '500mg+125mg'],
    ['brand_name' => 'Cephalexin', 'scientific_name' => 'Cephalexin', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Capsule', 'strength' => '500mg'],
    ['brand_name' => 'Azithrox', 'scientific_name' => 'Azithromycin', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Tablet', 'strength' => '500mg'],
    
    // Cardiovascular
    ['brand_name' => 'Tenormin', 'scientific_name' => 'Atenolol', 'manufacturer' => 'AstraZeneca', 'dosage_form' => 'Tablet', 'strength' => '50mg'],
    ['brand_name' => 'Norvasc', 'scientific_name' => 'Amlodipine', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Tablet', 'strength' => '5mg'],
    ['brand_name' => 'Cozaar', 'scientific_name' => 'Losartan', 'manufacturer' => 'Merck', 'dosage_form' => 'Tablet', 'strength' => '50mg'],
    
    // Diabetes
    ['brand_name' => 'Glucophage', 'scientific_name' => 'Metformin', 'manufacturer' => 'Merck', 'dosage_form' => 'Tablet', 'strength' => '500mg'],
    ['brand_name' => 'Diamicron', 'scientific_name' => 'Gliclazide', 'manufacturer' => 'Servier', 'dosage_form' => 'Tablet', 'strength' => '80mg'],
    
    // Respiratory
    ['brand_name' => 'Ventolin', 'scientific_name' => 'Salbutamol', 'manufacturer' => 'GlaxoSmithKline', 'dosage_form' => 'Inhaler', 'strength' => '100mcg/inhalation'],
    ['brand_name' => 'Seretide', 'scientific_name' => 'Fluticasone + Salmeterol', 'manufacturer' => 'GlaxoSmithKline', 'dosage_form' => 'Inhaler', 'strength' => '250mcg+50mcg'],
    
    // Hormones
    ['brand_name' => 'Synthroid', 'scientific_name' => 'Levothyroxine', 'manufacturer' => 'AbbVie', 'dosage_form' => 'Tablet', 'strength' => '100mcg'],
    
    // Antihistamines
    ['brand_name' => 'Claritin', 'scientific_name' => 'Loratadine', 'manufacturer' => 'MSD', 'dosage_form' => 'Tablet', 'strength' => '10mg'],
    ['brand_name' => 'Zyrtec', 'scientific_name' => 'Cetirizine', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Tablet', 'strength' => '10mg'],
    
    // Gastrointestinal
    ['brand_name' => 'Nexium', 'scientific_name' => 'Esomeprazole', 'manufacturer' => 'AstraZeneca', 'dosage_form' => 'Capsule', 'strength' => '20mg'],
    ['brand_name' => 'Prevacid', 'scientific_name' => 'Lansoprazole', 'manufacturer' => 'Takeda', 'dosage_form' => 'Capsule', 'strength' => '30mg'],
    
    // Cholesterol
    ['brand_name' => 'Lipitor', 'scientific_name' => 'Atorvastatin', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Tablet', 'strength' => '10mg'],
    
    // More medicines to reach 100 samples
    ['brand_name' => 'Tempra', 'scientific_name' => 'Paracetamol', 'manufacturer' => 'Bristol-Myers Squibb', 'dosage_form' => 'Syrup', 'strength' => '120mg/5ml'],
    ['brand_name' => 'Calpol', 'scientific_name' => 'Paracetamol', 'manufacturer' => 'GlaxoSmithKline', 'dosage_form' => 'Syrup', 'strength' => '120mg/5ml'],
    ['brand_name' => 'Advil', 'scientific_name' => 'Ibuprofen', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Tablet', 'strength' => '200mg'],
    ['brand_name' => 'Motrin', 'scientific_name' => 'Ibuprofen', 'manufacturer' => 'Johnson & Johnson', 'dosage_form' => 'Tablet', 'strength' => '200mg'],
    ['brand_name' => 'Tylenol', 'scientific_name' => 'Acetaminophen', 'manufacturer' => 'Johnson & Johnson', 'dosage_form' => 'Tablet', 'strength' => '325mg'],
    ['brand_name' => 'Aleve', 'scientific_name' => 'Naproxen', 'manufacturer' => 'Bayer', 'dosage_form' => 'Tablet', 'strength' => '220mg'],
    ['brand_name' => 'Bayer Aspirin', 'scientific_name' => 'Aspirin', 'manufacturer' => 'Bayer', 'dosage_form' => 'Tablet', 'strength' => '325mg'],
    ['brand_name' => 'Bufferin', 'scientific_name' => 'Aspirin', 'manufacturer' => 'Bayer', 'dosage_form' => 'Tablet', 'strength' => '325mg'],
    ['brand_name' => 'Excedrin', 'scientific_name' => 'Acetaminophen + Aspirin + Caffeine', 'manufacturer' => 'GlaxoSmithKline', 'dosage_form' => 'Tablet', 'strength' => '250mg+250mg+65mg'],
    ['brand_name' => 'Anacin', 'scientific_name' => 'Acetaminophen', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Tablet', 'strength' => '500mg'],
    ['brand_name' => 'Robitussin', 'scientific_name' => 'Dextromethorphan', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Syrup', 'strength' => '15mg/5ml'],
    ['brand_name' => 'Delsym', 'scientific_name' => 'Dextromethorphan', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Syrup', 'strength' => '15mg/5ml'],
    ['brand_name' => 'Mucinex', 'scientific_name' => 'Guaifenesin', 'manufacturer' => 'Reckitt Benckiser', 'dosage_form' => 'Tablet', 'strength' => '600mg'],
    ['brand_name' => 'Coricidin', 'scientific_name' => 'Dextromethorphan + Guaifenesin', 'manufacturer' => 'MSD', 'dosage_form' => 'Tablet', 'strength' => '15mg+400mg'],
    ['brand_name' => 'Nyquil', 'scientific_name' => 'Doxylamine + Acetaminophen + Dextromethorphan', 'manufacturer' => 'Procter & Gamble', 'dosage_form' => 'Syrup', 'strength' => '12.5mg+325mg+10mg/15ml'],
    ['brand_name' => 'Dayquil', 'scientific_name' => 'Phenylephrine + Acetaminophen + Dextromethorphan', 'manufacturer' => 'Procter & Gamble', 'dosage_form' => 'Syrup', 'strength' => '5mg+325mg+10mg/15ml'],
    ['brand_name' => 'Theraflu', 'scientific_name' => 'Phenylephrine + Acetaminophen + Dextromethorphan', 'manufacturer' => 'GlaxoSmithKline', 'dosage_form' => 'Powder', 'strength' => '5mg+325mg+10mg/sachet'],
    ['brand_name' => 'Benadryl', 'scientific_name' => 'Diphenhydramine', 'manufacturer' => 'Johnson & Johnson', 'dosage_form' => 'Syrup', 'strength' => '12.5mg/5ml'],
    ['brand_name' => 'Reactine', 'scientific_name' => 'Cetirizine', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Tablet', 'strength' => '10mg'],
    ['brand_name' => 'Xyzal', 'scientific_name' => 'Levocetirizine', 'manufacturer' => 'Sanofi', 'dosage_form' => 'Tablet', 'strength' => '5mg'],
    ['brand_name' => 'Atarax', 'scientific_name' => 'Hydroxyzine', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Tablet', 'strength' => '25mg'],
    ['brand_name' => 'Chlor-Trimeton', 'scientific_name' => 'Chlorpheniramine', 'manufacturer' => 'GlaxoSmithKline', 'dosage_form' => 'Tablet', 'strength' => '4mg'],
    ['brand_name' => 'Prilosec', 'scientific_name' => 'Omeprazole', 'manufacturer' => 'AstraZeneca', 'dosage_form' => 'Capsule', 'strength' => '20mg'],
    ['brand_name' => 'Aciphex', 'scientific_name' => 'Rabeprazole', 'manufacturer' => 'Eisai', 'dosage_form' => 'Tablet', 'strength' => '20mg'],
    ['brand_name' => 'Protonix', 'scientific_name' => 'Pantoprazole', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Tablet', 'strength' => '40mg'],
    ['brand_name' => 'Pepcid', 'scientific_name' => 'Famotidine', 'manufacturer' => 'Merck', 'dosage_form' => 'Tablet', 'strength' => '20mg'],
    ['brand_name' => 'Zantac', 'scientific_name' => 'Ranitidine', 'manufacturer' => 'Sanofi', 'dosage_form' => 'Tablet', 'strength' => '150mg'],
    ['brand_name' => 'Tagamet', 'scientific_name' => 'Cimetidine', 'manufacturer' => 'GlaxoSmithKline', 'dosage_form' => 'Tablet', 'strength' => '200mg'],
    ['brand_name' => 'Crestor', 'scientific_name' => 'Rosuvastatin', 'manufacturer' => 'AstraZeneca', 'dosage_form' => 'Tablet', 'strength' => '10mg'],
    ['brand_name' => 'Zocor', 'scientific_name' => 'Simvastatin', 'manufacturer' => 'Merck', 'dosage_form' => 'Tablet', 'strength' => '20mg'],
    ['brand_name' => 'Mevacor', 'scientific_name' => 'Lovastatin', 'manufacturer' => 'Merck', 'dosage_form' => 'Tablet', 'strength' => '20mg'],
    ['brand_name' => 'Lescol', 'scientific_name' => 'Fluvastatin', 'manufacturer' => 'Novartis', 'dosage_form' => 'Capsule', 'strength' => '20mg'],
    ['brand_name' => 'Altocor', 'scientific_name' => 'Lovastatin + Niacin', 'manufacturer' => 'Merck', 'dosage_form' => 'Tablet', 'strength' => '20mg+500mg'],
    ['brand_name' => 'Caduet', 'scientific_name' => 'Amlodipine + Atorvastatin', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Tablet', 'strength' => '5mg+10mg'],
    ['brand_name' => 'Livalo', 'scientific_name' => 'Pitavastatin', 'manufacturer' => 'Kowa', 'dosage_form' => 'Tablet', 'strength' => '2mg'],
    ['brand_name' => 'Glucotrol', 'scientific_name' => 'Glipizide', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Tablet', 'strength' => '5mg'],
    ['brand_name' => 'Diabeta', 'scientific_name' => 'Glyburide', 'manufacturer' => 'Merck', 'dosage_form' => 'Tablet', 'strength' => '5mg'],
    ['brand_name' => 'Micronase', 'scientific_name' => 'Glyburide', 'manufacturer' => 'AbbVie', 'dosage_form' => 'Tablet', 'strength' => '5mg'],
    ['brand_name' => 'Glynase', 'scientific_name' => 'Glyburide', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Tablet', 'strength' => '5mg'],
    ['brand_name' => 'Amaryl', 'scientific_name' => 'Glimepiride', 'manufacturer' => 'Sanofi', 'dosage_form' => 'Tablet', 'strength' => '2mg'],
    ['brand_name' => 'Starlix', 'scientific_name' => 'Repaglinide', 'manufacturer' => 'Novartis', 'dosage_form' => 'Tablet', 'strength' => '1mg'],
    ['brand_name' => 'Januvia', 'scientific_name' => 'Sitagliptin', 'manufacturer' => 'Merck', 'dosage_form' => 'Tablet', 'strength' => '100mg'],
    ['brand_name' => 'Levoxyl', 'scientific_name' => 'Levothyroxine', 'manufacturer' => 'AbbVie', 'dosage_form' => 'Tablet', 'strength' => '100mcg'],
    ['brand_name' => 'Levothroid', 'scientific_name' => 'Levothyroxine', 'manufacturer' => 'Forest Pharmaceuticals', 'dosage_form' => 'Tablet', 'strength' => '100mcg'],
    ['brand_name' => 'Tirosint', 'scientific_name' => 'Levothyroxine', 'manufacturer' => 'Iroko Pharmaceuticals', 'dosage_form' => 'Capsule', 'strength' => '100mcg'],
    ['brand_name' => 'Unithroid', 'scientific_name' => 'Levothyroxine', 'manufacturer' => 'Acella Pharmaceuticals', 'dosage_form' => 'Tablet', 'strength' => '100mcg'],
    ['brand_name' => 'Cytomel', 'scientific_name' => 'Liothyronine', 'manufacturer' => 'AbbVie', 'dosage_form' => 'Tablet', 'strength' => '50mcg'],
    ['brand_name' => 'Cynoplus', 'scientific_name' => 'Liothyronine + Levothyroxine', 'manufacturer' => 'AbbVie', 'dosage_form' => 'Tablet', 'strength' => '5mcg+50mcg'],
    ['brand_name' => 'Thyrolar', 'scientific_name' => 'Liothyronine', 'manufacturer' => 'Forest Pharmaceuticals', 'dosage_form' => 'Tablet', 'strength' => '5mcg'],
    ['brand_name' => 'ProAir', 'scientific_name' => 'Albuterol', 'manufacturer' => 'Teva Pharmaceuticals', 'dosage_form' => 'Inhaler', 'strength' => '90mcg/inhalation'],
    ['brand_name' => 'Albuterol', 'scientific_name' => 'Albuterol', 'manufacturer' => 'Perrigo', 'dosage_form' => 'Inhaler', 'strength' => '90mcg/inhalation'],
    ['brand_name' => 'Xopenex', 'scientific_name' => 'Formoterol', 'manufacturer' => 'Sunovion Pharmaceuticals', 'dosage_form' => 'Inhaler', 'strength' => '4.5mcg/inhalation'],
    ['brand_name' => 'Serevent', 'scientific_name' => 'Formoterol', 'manufacturer' => 'GlaxoSmithKline', 'dosage_form' => 'Inhaler', 'strength' => '50mcg/inhalation'],
    ['brand_name' => 'Striverdi', 'scientific_name' => 'Formoterol + Budesonide', 'manufacturer' => 'Boehringer Ingelheim', 'dosage_form' => 'Inhaler', 'strength' => '4.5mcg+80mcg'],
    ['brand_name' => 'Brovana', 'scientific_name' => 'Arformoterol', 'manufacturer' => 'Sunovion Pharmaceuticals', 'dosage_form' => 'Inhaler', 'strength' => '15mcg/inhalation'],
    ['brand_name' => 'Perforomist', 'scientific_name' => 'Formoterol', 'manufacturer' => 'Orion Corporation', 'dosage_form' => 'Inhaler', 'strength' => '20mcg/inhalation'],
    ['brand_name' => 'Accolate', 'scientific_name' => 'Zafirlukast', 'manufacturer' => 'AstraZeneca', 'dosage_form' => 'Tablet', 'strength' => '20mg'],
    ['brand_name' => 'Zyflo', 'scientific_name' => 'Zileuton', 'manufacturer' => 'AbbVie', 'dosage_form' => 'Tablet', 'strength' => '600mg'],
    ['brand_name' => 'Dulera', 'scientific_name' => 'Formoterol + Mometasone', 'manufacturer' => 'Merck', 'dosage_form' => 'Inhaler', 'strength' => '5mcg+200mcg'],
    ['brand_name' => 'Symbicort', 'scientific_name' => 'Budesonide + Formoterol', 'manufacturer' => 'AstraZeneca', 'dosage_form' => 'Inhaler', 'strength' => '160mcg+4.5mcg'],
    ['brand_name' => 'Advair', 'scientific_name' => 'Fluticasone + Salmeterol', 'manufacturer' => 'GlaxoSmithKline', 'dosage_form' => 'Inhaler', 'strength' => '250mcg+50mcg'],
    ['brand_name' => 'Breobox', 'scientific_name' => 'Budesonide + Formoterol', 'manufacturer' => 'Cipla', 'dosage_form' => 'Inhaler', 'strength' => '160mcg+4.5mcg'],
    ['brand_name' => 'Pulmicort', 'scientific_name' => 'Budesonide', 'manufacturer' => 'AstraZeneca', 'dosage_form' => 'Inhaler', 'strength' => '200mcg/inhalation'],
    ['brand_name' => 'Adalat', 'scientific_name' => 'Nifedipine', 'manufacturer' => 'Bayer', 'dosage_form' => 'Tablet', 'strength' => '10mg'],
    ['brand_name' => 'Procardia', 'scientific_name' => 'Nifedipine', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Capsule', 'strength' => '10mg'],
    ['brand_name' => 'Cardizem', 'scientific_name' => 'Diltiazem', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Tablet', 'strength' => '120mg'],
    ['brand_name' => 'Verapamil', 'scientific_name' => 'Verapamil', 'manufacturer' => 'Pfizer', 'dosage_form' => 'Tablet', 'strength' => '80mg'],
    ['brand_name' => 'Calan', 'scientific_name' => 'Verapamil', 'manufacturer' => 'GSK', 'dosage_form' => 'Tablet', 'strength' => '80mg'],
    ['brand_name' => 'Isoptin', 'scientific_name' => 'Verapamil', 'manufacturer' => 'Merck', 'dosage_form' => 'Tablet', 'strength' => '80mg'],
    ['brand_name' => 'Covera', 'scientific_name' => 'Verapamil', 'manufacturer' => 'AbbVie', 'dosage_form' => 'Tablet', 'strength' => '240mg'],
    ['brand_name' => 'Avapro', 'scientific_name' => 'Irbesartan', 'manufacturer' => 'Sanofi', 'dosage_form' => 'Tablet', 'strength' => '150mg'],
    ['brand_name' => 'Diovan', 'scientific_name' => 'Valsartan', 'manufacturer' => 'Novartis', 'dosage_form' => 'Capsule', 'strength' => '80mg'],
    ['brand_name' => 'Benicar', 'scientific_name' => 'Olmesartan', 'manufacturer' => 'Daiichi Sankyo', 'dosage_form' => 'Tablet', 'strength' => '20mg'],
    ['brand_name' => 'Micardis', 'scientific_name' => 'Telmisartan', 'manufacturer' => 'Boehringer Ingelheim', 'dosage_form' => 'Tablet', 'strength' => '40mg'],
    ['brand_name' => 'Atacand', 'scientific_name' => 'Candesartan', 'manufacturer' => 'AstraZeneca', 'dosage_form' => 'Tablet', 'strength' => '8mg'],
    ['brand_name' => 'Teveten', 'scientific_name' => 'Eprosartan', 'manufacturer' => 'Warner Chilcott', 'dosage_form' => 'Tablet', 'strength' => '600mg'],
    ['brand_name' => 'Edarbi', 'scientific_name' => 'Azilsartan', 'manufacturer' => 'Takeda', 'dosage_form' => 'Tablet', 'strength' => '40mg']
];

$dosageForms = ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Ointment', 'Cream', 'Drops', 'Inhaler', 'Gel', 'Patch'];
$statuses = ['active', 'inactive', 'pending'];

echo "\nGenerating 100 well-known medicines and associating with pharmacies...\n";

// First, get all pharmacies from the database
$pharmacies = [];
$pharmacyResult = $conn->query("SELECT id FROM pharmacies");
if ($pharmacyResult && $pharmacyResult->num_rows > 0) {
    while ($row = $pharmacyResult->fetch_assoc()) {
        $pharmacies[] = $row['id'];
    }
}
echo "Found " . count($pharmacies) . " pharmacies in the database.\n";

$insertedMedicineCount = 0;
$insertedAssociationCount = 0;

// Insert the predefined 100 medicines
foreach ($medicines as $index => $medicine) {
    // Add missing fields
    $medicine['description'] = "A trusted medicine commonly available in Pagadian City pharmacies.";
    $medicine['uses'] = "Used for treatment of common conditions.";
    $medicine['side_effects'] = "Generally well-tolerated with minimal side effects.";
    $medicine['contraindications'] = "Follow doctor's advice for usage.";
    $medicine['status'] = $statuses[array_rand($statuses)];
    $medicine['image_path'] = null;
    
    // Prepare statement to insert medicine
    $stmt = $conn->prepare("
        INSERT INTO medicines 
        (brand_name, scientific_name, manufacturer, dosage_form, strength, description, uses, side_effects, contraindications, status, image_path, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    if ($stmt) {
        $stmt->bind_param(
            "sssssssssss",
            $medicine['brand_name'],
            $medicine['scientific_name'],
            $medicine['manufacturer'],
            $medicine['dosage_form'],
            $medicine['strength'],
            $medicine['description'],
            $medicine['uses'],
            $medicine['side_effects'],
            $medicine['contraindications'],
            $medicine['status'],
            $medicine['image_path']
        );
        
        if ($stmt->execute()) {
            $medicineId = $conn->insert_id;
            $insertedMedicineCount++;
            echo "Inserted medicine: {$medicine['brand_name']} (ID: {$medicineId})\n";
            
            // Associate this medicine with all pharmacies
            foreach ($pharmacies as $pharmacyId) {
                // Generate random price and stock
                $price = number_format(rand(500, 5000) / 100, 2, '.', ''); // Between 5.00 and 50.00
                $stock = rand(0, 200); // Between 0 and 200 units
                
                // Generate random expiry date (between 6 months and 3 years from now)
                $expiryDate = date('Y-m-d', strtotime('+' . rand(6, 36) . ' months'));
                
                // Insert into pharmacy_medicines table
                $assocStmt = $conn->prepare("
                    INSERT INTO pharmacy_medicines 
                    (pharmacy_id, medicine_id, price, stock, expiry_date, updated_by, updated_at)
                    VALUES (?, ?, ?, ?, ?, 2, NOW())
                ");
                
                if ($assocStmt) {
                    $assocStmt->bind_param("iiids", $pharmacyId, $medicineId, $price, $stock, $expiryDate);
                    
                    if ($assocStmt->execute()) {
                        $insertedAssociationCount++;
                    } else {
                        echo "Error associating medicine with pharmacy: " . $assocStmt->error . "\n";
                    }
                    
                    $assocStmt->close();
                }
            }
        } else {
            echo "Error inserting medicine: " . $stmt->error . "\n";
        }
        
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error . "\n";
    }
}

echo "\nSuccessfully inserted {$insertedMedicineCount} medicines.\n";
echo "Successfully associated medicines with pharmacies {$insertedAssociationCount} times.\n";
echo "Script completed.\n";
?>