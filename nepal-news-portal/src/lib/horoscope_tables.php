<?php
/**
 * Horoscope Database Tables Setup
 * Run this to create all necessary tables for horoscope feature
 */

function install_horoscope_tables(): void {
    $db = db();
    
    // Daily Horoscope Table
    $db->query("
        CREATE TABLE IF NOT EXISTS horoscope_daily (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sign VARCHAR(20) NOT NULL,
            date DATE NOT NULL,
            overall_score DECIMAL(3,2) DEFAULT 3.00,
            love_score DECIMAL(3,2) DEFAULT 3.00,
            career_score DECIMAL(3,2) DEFAULT 3.00,
            health_score DECIMAL(3,2) DEFAULT 3.00,
            finance_score DECIMAL(3,2) DEFAULT 3.00,
            prediction TEXT,
            lucky_color VARCHAR(50),
            lucky_number INT,
            lucky_direction VARCHAR(30),
            lucky gemstone VARCHAR(50),
            caution TEXT,
            mantra VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_sign_date (sign, date),
            INDEX idx_date (date),
            INDEX idx_sign (sign)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Monthly Horoscope Table
    $db->query("
        CREATE TABLE IF NOT EXISTS horoscope_monthly (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sign VARCHAR(20) NOT NULL,
            month INT NOT NULL,
            year INT NOT NULL,
            overall_prediction TEXT,
            love_prediction TEXT,
            career_prediction TEXT,
            health_prediction TEXT,
            finance_prediction TEXT,
            important_dates TEXT,
            key_themes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_sign_month_year (sign, month, year),
            INDEX idx_month_year (month, year)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Yearly Horoscope Table
    $db->query("
        CREATE TABLE IF NOT EXISTS horoscope_yearly (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sign VARCHAR(20) NOT NULL,
            year INT NOT NULL,
            overview TEXT,
            love TEXT,
            career TEXT,
            health TEXT,
            finance TEXT,
            predictions JSON,
            key_predictions TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_sign_year (sign, year),
            INDEX idx_year (year)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Auspicious Times Table (Subha Samaya)
    $db->query("
        CREATE TABLE IF NOT EXISTS auspicious_times (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nepali_date VARCHAR(50) NOT NULL,
            english_date DATE NOT NULL,
            abhijeet_mulat VARCHAR(100),
            brahma_muhurat VARCHAR(100),
            amrit_kalash VARCHAR(100),
            Ravi_kalash VARCHAR(100),
            chartime_start VARCHAR(20),
            chartime_end VARCHAR(20),
            labh_kalash VARCHAR(100),
            shubh_kalash VARCHAR(100),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_nepali_date (nepali_date),
            INDEX idx_english_date (english_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Auspicious Days Table (Subha Din)
    $db->query("
        CREATE TABLE IF NOT EXISTS auspicious_days (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nepali_date VARCHAR(50) NOT NULL,
            english_date DATE NOT NULL,
            day_name VARCHAR(30),
            day_type VARCHAR(20),
            title VARCHAR(100),
            description TEXT,
            significance VARCHAR(255),
            month INT NOT NULL,
            year INT NOT NULL,
            day INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_month_year (month, year)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Lagna (Ascendant) Info Table
    $db->query("
        CREATE TABLE IF NOT EXISTS lagna_info (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nepali_date VARCHAR(50) NOT NULL,
            english_date DATE NOT NULL,
            moon_sign VARCHAR(30),
            ascendant VARCHAR(30),
            nakshatra VARCHAR(30),
            tithi VARCHAR(30),
            yoga VARCHAR(30),
            karana VARCHAR(30),
            sun_time VARCHAR(50),
            moon_time VARCHAR(50),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_nepali_date (nepali_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Gud Milan (Compatibility) Table
    $db->query("
        CREATE TABLE IF NOT EXISTS gud_milan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            boy_sign VARCHAR(20) NOT NULL,
            girl_sign VARCHAR(20) NOT NULL,
            varna INT DEFAULT 1,
            vasya INT DEFAULT 0.5,
            tatva INT DEFAULT 0.5,
            grah INT DEFAULT 2.5,
            nadi INT DEFAULT 8,
            gana INT DEFAULT 1,
            manglik INT DEFAULT 0,
            total_score DECIMAL(4,2) DEFAULT 0,
            compatibility VARCHAR(20),
            summary TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_signs (boy_sign, girl_sign)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Bastu (Items) Recommendations Table
    $db->query("
        CREATE TABLE IF NOT EXISTS bastu_recommendations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sign VARCHAR(20) NOT NULL,
            fav_gem VARCHAR(50),
            fav_color VARCHAR(50),
            fav_day VARCHAR(20),
            fav_metal VARCHAR(30),
            fav_number INT,
            fav_direction VARCHAR(30),
            wear_gem VARCHAR(100),
            avoid_gem VARCHAR(100),
            home_direction VARCHAR(30),
            office_direction VARCHAR(30),
            good_feng_shui TEXT,
            bad_feng_shui TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_sign (sign)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Horoscope API Cache Table
    $db->query("
        CREATE TABLE IF NOT EXISTS horoscope_api_cache (
            id INT AUTO_INCREMENT PRIMARY KEY,
            api_source VARCHAR(50) NOT NULL,
            data_type VARCHAR(30) NOT NULL,
            sign VARCHAR(20),
            date_key VARCHAR(20),
            data JSON,
            fetched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP,
            INDEX idx_source_type (api_source, data_type),
            INDEX idx_date_key (date_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Initialize default data
    init_horoscope_defaults();
}

/**
 * Initialize default horoscope data
 */
function init_horoscope_defaults(): void {
    $db = db();
    
    // Check if already initialized
    $result = $db->query("SELECT COUNT(*) as cnt FROM horoscope_daily");
    $row = $result->fetch_assoc();
    if ($row['cnt'] > 0) return;
    
    $signs = ['mesh', 'vrishabha', 'mithun', 'karkat', 'sinh', 'kanya', 'tula', 'vrishchik', 'dhanu', 'makar', 'kumbha', 'meen'];
    
    // Generate today's rashifal for all signs
    $today = date('Y-m-d');
    $predictions = [
        'mesh'      => 'आज तपाईंले नयाँ अवसरहरू पाउन सक्नुहुन्छ। कडा परिश्रमले सफलता ल्याउनेछ।',
        'vrishabha' => 'आज तपाईंको मेहनतले सकारात्मक परिणाम दिनेछ। आर्थिक स्थिति बलियो हुनेछ।',
        'mithun'    => 'आज तपाईंले नयाँ मानिसहरूसँग भेटघाट गर्ने सम्भावना छ।',
        'karkat'    => 'आज तपाईंको स्वास्थ्य राम्रो हुनेछ। परिवारसँग समय बिताउनुहोस्।',
        'sinh'      => 'आज तपाईंको नेतृत्व क्षमता प्रमाणित हुनेछ।',
        'kanya'     => 'आज तपाईंले विवरणमा ध्यान दिनु पर्नेछ।',
        'tula'      => 'आज प्रेम जीवनमा सकारात्मक कदम चाल्नुहोस्।',
        'vrishchik' => 'आज तपाईंको जिज्ञासाले नयाँ ज्ञान ल्याउनेछ।',
        'dhanu'     => 'आज तपाईंले यात्रा गर्ने योजना बनाउन सक्नुहुन्छ।',
        'makar'     => 'आज तपाईंको करियरमा प्रगति हुनेछ।',
        'kumbha'    => 'आज तपाईंले सामाजिक कार्यमा भाग लिनुहोस्।',
        'meen'      => 'आज तपाईंको कल्पनाशक्ति उच्च हुनेछ।',
    ];
    
    $stmt = $db->prepare("
        INSERT INTO horoscope_daily (sign, date, overall_score, love_score, career_score, health_score, finance_score, prediction, lucky_color, lucky_number, lucky_direction, caution, mantra)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($signs as $sign) {
        $scores = [
            'overall' => round((mt_rand(60, 100) / 100) * 5, 2),
            'love' => round((mt_rand(50, 100) / 100) * 5, 2),
            'career' => round((mt_rand(50, 100) / 100) * 5, 2),
            'health' => round((mt_rand(60, 100) / 100) * 5, 2),
            'finance' => round((mt_rand(50, 100) / 100) * 5, 2),
        ];
        
        $colors = ['रातो', 'सेतो', 'हरियो', 'पहेंलो', 'नीलो'];
        $directions = ['पूर्व', 'पश्चिम', 'उत्तर', 'दक्षिण'];
        
        $stmt->bind_param(
            'sdddddsssiss',
            $sign, $today,
            $scores['overall'], $scores['love'], $scores['career'], $scores['health'], $scores['finance'],
            $predictions[$sign],
            $colors[array_rand($colors)],
            mt_rand(1, 9),
            $directions[array_rand($directions)],
            $caution = 'धेरै जोड नदिनुस्',
            $mantra = 'ॐ नमः शिवाय'
        );
        $stmt->execute();
    }
    
    // Initialize Gud Milan for all combinations
    $stmt = $db->prepare("
        INSERT INTO gud_milan (boy_sign, girl_sign, total_score, compatibility, summary)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    foreach ($signs as $boy) {
        foreach ($signs as $girl) {
            $score = round((mt_rand(12, 36)) / 36 * 100, 2);
            $comp = $score >= 75 ? 'उत्तम' : ($score >= 50 ? 'राम्रो' : ($score >= 25 ? 'ठीक' : 'चेतावनी'));
            $summary = "{$boy} र {$girl} को मिलान {$comp} छ।";
            
            $stmt->bind_param('ssdss', $boy, $girl, $score, $comp, $summary);
            $stmt->execute();
        }
    }
    
    // Initialize Bastu for all signs
    $stmt = $db->prepare("
        INSERT INTO bastu_recommendations (sign, fav_gem, fav_color, fav_day, fav_metal, fav_number, fav_direction, wear_gem, avoid_gem, home_direction, office_direction)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $bastu_data = [
        'mesh'      => ['माणिक', 'रातो', 'मंगलबार', 'तामा', 9, 'पूर्व', 'पु rub', 'फिरोजा', 'पूर्व', 'उत्तर'],
        'vrishabha' => ['पुdeb', 'सेतो', 'शुक्रबार', 'कासा', 6, 'दक्षिण', 'पुdeb', 'मोती', 'दक्षिण', 'दक्षिण'],
        'mithun'    => ['पारद', 'हरियो', 'बुधबार', 'काँच', 5, 'उत्तर', 'पारद', 'मोती', 'पश्चिम', 'पूर्व'],
        'karkat'    => ['मोती', 'चाँदी', 'सोमबार', 'चाँदी', 2, 'पश्चिम', 'मोती', 'गोमेद', 'पश्चिम', 'उत्तर'],
        'sinh'      => ['माणिक', 'सुनौलो', 'रविवार', 'सुन', 1, 'उत्तर', 'माणिक', 'नीलम', 'उत्तर', 'पश्चिम'],
        'kanya'     => ['नीलम', 'खैरो', 'बुधबार', 'काँच', 5, 'दक्षिण', 'नीलम', 'माणिक', 'दक्षिण', 'दक्षिण'],
        'tula'      => ['फिरोजा', 'गुलाबी', 'शुक्रबार', 'कासा', 6, 'पूर्व', 'फिरोजा', 'माणिक', 'पूर्व', 'पूर्व'],
        'vrishchik' => ['लहल', 'रातो', 'मंगलबार', 'तामा', 8, 'दक्षिण', 'लहल', 'मोती', 'दक्षिण', 'पश्चिम'],
        'dhanu'     => ['पु deb', 'हरियो', 'बिहिबार', 'पीतल', 3, 'दक्षिण', 'पु deb', 'नीलम', 'दक्षिण', 'उत्तर'],
        'makar'     => ['गोमेद', 'कालो', 'शनिबार', 'सिसा', 9, 'उत्तर', 'गोमेद', 'फिरोजा', 'उत्तर', 'दक्षिण'],
        'kumbha'    => ['नीलम', 'नीलो', 'शनिबार', 'काँच', 4, 'उत्तर', 'नीलम', 'माणिक', 'उत्तर', 'उत्तर'],
        'meen'      => ['अकिक', 'सुनौलो', 'बिहिबार', 'चाँदी', 7, 'पश्चिम', 'अकिक', 'गोमेद', 'पश्चिम', 'पूर्व'],
    ];
    
    foreach ($signs as $sign) {
        $data = $bastu_data[$sign] ?? $bastu_data['mesh'];
        $stmt->bind_param(
            'sssssisssss',
            $sign, $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7], $data[8], $data[9]
        );
        $stmt->execute();
    }
    
    // Initialize today's auspicious times
    $nepali_date = nepali_date_today();
    $stmt = $db->prepare("
        INSERT INTO auspicious_times (nepali_date, english_date, abhijeet_mulat, brahma_muhurat, amrit_kalash, Ravi_kalash, labh_kalash, shubh_kalash, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        'sssssssss',
        $nepali_date, $today,
        $abhijeet = '११:४५ - १२:३७',
        $brahma = '०४:४५ - ०५:३०',
        $amrit = '१२:१५ - १३:००',
        $ravi = '०६:१५ - ०७:००',
        $labh = '१४:०० - १५:३०',
        $shubh = '०९:०० - ११:००',
        $notes = 'आजको दिन शुभ कार्यका लागि उपयुक्त छ।'
    );
    $stmt->execute();
    
    // Initialize Lagna info
    $stmt = $db->prepare("
        INSERT INTO lagna_info (nepali_date, english_date, moon_sign, ascendant, nakshatra, tithi, yoga, karana, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->bind_param(
        'sssssssss',
        $nepali_date, $today,
        $moon = 'कर्कट',
        $asc = 'मिथुन',
        $nakshatra = 'अश्लेषा',
        $tithi = 'एकादशी',
        $yoga = 'सिद्धि',
        $karana = 'बालव',
        $notes = 'आजको दिन महत्वपूर्ण छ।'
    );
    $stmt->execute();
}

// Helper function for Nepali date
function nepali_date_today(): string {
    $months = ['बैशाख', 'जेठ', 'असार', 'श्रावण', 'भदौ', 'असोज', 'कार्तिक', 'मंसिर', 'पौष', 'माघ', 'फाल्गुन', 'चैत्र'];
    $month_idx = (int)date('n') - 1;
    if ($month_idx < 0) $month_idx = 11;
    $bs_year = (int)date('Y') + 56;
    return $months[$month_idx] . ' ' . (int)date('j') . ', ' . $bs_year;
}
