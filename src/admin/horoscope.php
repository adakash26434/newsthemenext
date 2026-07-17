<?php
admin_check();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'save_daily':
            save_daily_horoscope($_POST);
            break;
        case 'save_monthly':
            save_monthly_horoscope($_POST);
            break;
        case 'save_yearly':
            save_yearly_horoscope($_POST);
            break;
        case 'save_auspicious':
            save_auspicious_day($_POST);
            break;
        case 'save_auspicious_time':
            save_auspicious_time($_POST);
            break;
        case 'save_lagna':
            save_lagna_info($_POST);
            break;
        case 'save_bastu':
            save_bastu($_POST);
            break;
        case 'install_tables':
            require SRC_DIR . '/lib/horoscope_tables.php';
            install_horoscope_tables();
            flash_set('success', 'हजुरमा हजुर! हजुरको बारेमा धेरै कुरा छन्।');
            redirect('admin/horoscope');
            break;
        case 'generate_daily':
            generate_all_daily_rashifal();
            flash_set('success', 'दैनिक राशिफल जेनेरेट भयो।');
            redirect('admin/horoscope');
            break;
    }
}

// Helper functions
function save_daily_horoscope(array $data): void {
    $db = db();
    $stmt = $db->prepare("
        INSERT INTO horoscope_daily (sign, date, overall_score, love_score, career_score, health_score, finance_score, prediction, lucky_color, lucky_number, lucky_direction, caution, mantra)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            overall_score=VALUES(overall_score), love_score=VALUES(love_score),
            career_score=VALUES(career_score), health_score=VALUES(health_score),
            finance_score=VALUES(finance_score), prediction=VALUES(prediction),
            lucky_color=VALUES(lucky_color), lucky_number=VALUES(lucky_number),
            lucky_direction=VALUES(lucky_direction), caution=VALUES(caution), mantra=VALUES(mantra)
    ");
    
    $stmt->bind_param(
        'sdddddsssisss',
        $data['sign'], $data['date'],
        $data['overall_score'], $data['love_score'], $data['career_score'],
        $data['health_score'], $data['finance_score'], $data['prediction'],
        $data['lucky_color'], $data['lucky_number'], $data['lucky_direction'],
        $data['caution'], $data['mantra']
    );
    $stmt->execute();
    flash_set('success', 'दैनिक राशिफल सेभ भयो।');
    redirect('admin/horoscope?tab=daily');
}

function save_monthly_horoscope(array $data): void {
    $db = db();
    $stmt = $db->prepare("
        INSERT INTO horoscope_monthly (sign, month, year, overall_prediction, love_prediction, career_prediction, health_prediction, finance_prediction, important_dates, key_themes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            overall_prediction=VALUES(overall_prediction), love_prediction=VALUES(love_prediction),
            career_prediction=VALUES(career_prediction), health_prediction=VALUES(health_prediction),
            finance_prediction=VALUES(finance_prediction), important_dates=VALUES(important_dates), key_themes=VALUES(key_themes)
    ");
    
    $stmt->bind_param(
        'siisssssss',
        $data['sign'], $data['month'], $data['year'],
        $data['overall_prediction'], $data['love_prediction'], $data['career_prediction'],
        $data['health_prediction'], $data['finance_prediction'],
        $data['important_dates'], $data['key_themes']
    );
    $stmt->execute();
    flash_set('success', 'मासिक राशिफल सेभ भयो।');
    redirect('admin/horoscope?tab=monthly');
}

function save_yearly_horoscope(array $data): void {
    $db = db();
    $stmt = $db->prepare("
        INSERT INTO horoscope_yearly (sign, year, overview, love, career, health, finance, key_predictions)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            overview=VALUES(overview), love=VALUES(love), career=VALUES(career),
            health=VALUES(health), finance=VALUES(finance), key_predictions=VALUES(key_predictions)
    ");
    
    $stmt->bind_param(
        'sissssss',
        $data['sign'], $data['year'],
        $data['overview'], $data['love'], $data['career'],
        $data['health'], $data['finance'], $data['key_predictions']
    );
    $stmt->execute();
    flash_set('success', 'वार्षिक राशिफल सेभ भयो।');
    redirect('admin/horoscope?tab=yearly');
}

function save_auspicious_day(array $data): void {
    $db = db();
    $stmt = $db->prepare("
        INSERT INTO auspicious_days (nepali_date, english_date, day_name, day_type, title, description, significance, month, year, day)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            day_name=VALUES(day_name), day_type=VALUES(day_type), title=VALUES(title),
            description=VALUES(description), significance=VALUES(significance)
    ");
    
    $stmt->bind_param(
        'ssisssssii',
        $data['nepali_date'], $data['english_date'], $data['day_name'], $data['day_type'],
        $data['title'], $data['description'], $data['significance'],
        $data['month'], $data['year'], $data['day']
    );
    $stmt->execute();
    flash_set('success', 'शुभ दिन सेभ भयो।');
    redirect('admin/horoscope?tab=auspicious');
}

function save_auspicious_time(array $data): void {
    $db = db();
    $stmt = $db->prepare("
        INSERT INTO auspicious_times (nepali_date, english_date, abhijeet_mulat, brahma_muhurat, amrit_kalash, Ravi_kalash, chartime_start, chartime_end, labh_kalash, shubh_kalash, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            abhijeet_mulat=VALUES(abhijeet_mulat), brahma_muhurat=VALUES(brahma_muhurat),
            amrit_kalash=VALUES(amrit_kalash), Ravi_kalash=VALUES(Ravi_kalash),
            chartime_start=VALUES(chartime_start), chartime_end=VALUES(chartime_end),
            labh_kalash=VALUES(labh_kalash), shubh_kalash=VALUES(shubh_kalash), notes=VALUES(notes)
    ");
    
    $stmt->bind_param(
        'sssssssssss',
        $data['nepali_date'], $data['english_date'],
        $data['abhijeet_mulat'], $data['brahma_muhurat'], $data['amrit_kalash'],
        $data['ravi_kalash'], $data['chartime_start'], $data['chartime_end'],
        $data['labh_kalash'], $data['shubh_kalash'], $data['notes']
    );
    $stmt->execute();
    flash_set('success', 'शुभ समय सेभ भयो।');
    redirect('admin/horoscope?tab=subhatime');
}

function save_lagna_info(array $data): void {
    $db = db();
    $stmt = $db->prepare("
        INSERT INTO lagna_info (nepali_date, english_date, moon_sign, ascendant, nakshatra, tithi, yoga, karana, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            moon_sign=VALUES(moon_sign), ascendant=VALUES(ascendant), nakshatra=VALUES(nakshatra),
            tithi=VALUES(tithi), yoga=VALUES(yoga), karana=VALUES(karana), notes=VALUES(notes)
    ");
    
    $stmt->bind_param(
        'sssssssss',
        $data['nepali_date'], $data['english_date'],
        $data['moon_sign'], $data['ascendant'], $data['nakshatra'],
        $data['tithi'], $data['yoga'], $data['karana'], $data['notes']
    );
    $stmt->execute();
    flash_set('success', 'लग्न जानकारी सेभ भयो।');
    redirect('admin/horoscope?tab=lagna');
}

function save_bastu(array $data): void {
    $db = db();
    $stmt = $db->prepare("
        INSERT INTO bastu_recommendations (sign, fav_gem, fav_color, fav_day, fav_metal, fav_number, fav_direction, wear_gem, avoid_gem, home_direction, office_direction, good_feng_shui, bad_feng_shui)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            fav_gem=VALUES(fav_gem), fav_color=VALUES(fav_color), fav_day=VALUES(fav_day),
            fav_metal=VALUES(fav_metal), fav_number=VALUES(fav_number), fav_direction=VALUES(fav_direction),
            wear_gem=VALUES(wear_gem), avoid_gem=VALUES(avoid_gem), home_direction=VALUES(home_direction),
            office_direction=VALUES(office_direction), good_feng_shui=VALUES(good_feng_shui), bad_feng_shui=VALUES(bad_feng_shui)
    ");
    
    $stmt->bind_param(
        'sssssisssssss',
        $data['sign'], $data['fav_gem'], $data['fav_color'], $data['fav_day'],
        $data['fav_metal'], $data['fav_number'], $data['fav_direction'],
        $data['wear_gem'], $data['avoid_gem'], $data['home_direction'],
        $data['office_direction'], $data['good_feng_shui'], $data['bad_feng_shui']
    );
    $stmt->execute();
    flash_set('success', 'बस्तु सिफारिस सेभ भयो।');
    redirect('admin/horoscope?tab=bastu');
}

function generate_all_daily_rashifal(): void {
    $db = db();
    $signs = ['mesh', 'vrishabha', 'mithun', 'karkat', 'sinh', 'kanya', 'tula', 'vrishchik', 'dhanu', 'makar', 'kumbha', 'meen'];
    $today = date('Y-m-d');
    
    $predictions = [
        'mesh'      => 'आज तपाईंले नयाँ अवसरहरू पाउन सक्नुहुन्छ। कडा परिश्रमले सफलता ल्याउनेछ। आजको दिन सकारात्मक छ।',
        'vrishabha' => 'आज तपाईंको मेहनतले सकारात्मक परिणाम दिनेछ। आर्थिक स्थिति बलियो हुनेछ।',
        'mithun'    => 'आज तपाईंले नयाँ मानिसहरूसँग भेटघाट गर्ने सम्भावना छ। सामाजिक कार्यमा भाग लिनुहोस्।',
        'karkat'    => 'आज तपाईंको स्वास्थ्य राम्रो हुनेछ। परिवारसँग समय बिताउनुहोस्।',
        'sinh'      => 'आज तपाईंको नेतृत्व क्षमता प्रमाणित हुनेछ। नयाँ योजनाहरू सुरु गर्नुहोस्।',
        'kanya'     => 'आज तपाईंले विवरणमा ध्यान दिनु पर्नेछ। सानो कुरामा पनि ध्यान दिनुहोस्।',
        'tula'      => 'आज प्रेम जीवनमा सकारात्मक कदम चाल्नुहोस्। सम्बन्धमा सुधार हुनेछ।',
        'vrishchik' => 'आज तपाईंको जिज्ञासाले नयाँ ज्ञान ल्याउनेछ। अध्ययनमा समय बिताउनुहोस्।',
        'dhanu'     => 'आज तपाईंले यात्रा गर्ने योजना बनाउन सक्नुहुन्छ। यात्रा शुभ हुनेछ।',
        'makar'     => 'आज तपाईंको करियरमा प्रगति हुनेछ। नयाँ अवसरहरू आउनेछन्।',
        'kumbha'    => 'आज तपाईंले सामाजिक कार्यमा भाग लिनुहोस्। मानिसहरूसँग भेटघाट गर्नुहोस्।',
        'meen'      => 'आज तपाईंको कल्पनाशक्ति उच्च हुनेछ। रचनात्मक कार्यमा सफलता पाउनुहुनेछ।',
    ];
    
    $stmt = $db->prepare("
        INSERT INTO horoscope_daily (sign, date, overall_score, love_score, career_score, health_score, finance_score, prediction, lucky_color, lucky_number, lucky_direction, caution, mantra)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            overall_score=VALUES(overall_score), prediction=VALUES(prediction),
            lucky_color=VALUES(lucky_color), lucky_number=VALUES(lucky_number)
    ");
    
    $colors = ['रातो', 'सेतो', 'हरियो', 'पहेंलो', 'नीलो', 'गुलाबी'];
    $directions = ['पूर्व', 'पश्चिम', 'उत्तर', 'दक्षिण'];
    
    foreach ($signs as $sign) {
        $overall = round((mt_rand(60, 100) / 100) * 5, 2);
        $love = round((mt_rand(50, 100) / 100) * 5, 2);
        $career = round((mt_rand(50, 100) / 100) * 5, 2);
        $health = round((mt_rand(60, 100) / 100) * 5, 2);
        $finance = round((mt_rand(50, 100) / 100) * 5, 2);
        
        $stmt->bind_param(
            'sdddddsssisss',
            $sign, $today, $overall, $love, $career, $health, $finance,
            $predictions[$sign],
            $colors[array_rand($colors)],
            mt_rand(1, 9),
            $directions[array_rand($directions)],
            'धेरै जोड नदिनुस्',
            'ॐ नमः शिवाय'
        );
        $stmt->execute();
    }
}

// Get tab
$tab = $_GET['tab'] ?? 'dashboard';
$signs = ['mesh', 'vrishabha', 'mithun', 'karkat', 'sinh', 'kanya', 'tula', 'vrishchik', 'dhanu', 'makar', 'kumbha', 'meen'];
$sign_names = [
    'mesh' => 'मेष', 'vrishabha' => 'वृषभ', 'mithun' => 'मिथुन', 'karkat' => 'कर्कट',
    'sinh' => 'सिंह', 'kanya' => 'कन्या', 'tula' => 'तुला', 'vrishchik' => 'वृश्चिक',
    'dhanu' => 'धनु', 'makar' => 'मकर', 'kumbha' => 'कुम्भ', 'meen' => 'मीन'
];

admin_html_start('ज्योतिष');
admin_sidebar('horoscope');
?>
<div class="admin-content">
<?php admin_topbar('ज्योतिष'); ?>
<div class="p-6">
<?php admin_flash(); ?>

<!-- Tab Navigation -->
<div class="flex flex-wrap gap-2 mb-6">
    <a href="?tab=dashboard" class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?= $tab === 'dashboard' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' ?>">
        ड्यासबोर्ड
    </a>
    <a href="?tab=daily" class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?= $tab === 'daily' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' ?>">
        दैनिक राशिफल
    </a>
    <a href="?tab=monthly" class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?= $tab === 'monthly' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' ?>">
        मासिक राशिफल
    </a>
    <a href="?tab=yearly" class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?= $tab === 'yearly' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' ?>">
        वार्षिक राशिफल
    </a>
    <a href="?tab=auspicious" class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?= $tab === 'auspicious' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' ?>">
        शुभ दिन
    </a>
    <a href="?tab=subhatime" class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?= $tab === 'subhatime' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' ?>">
        शुभ समय
    </a>
    <a href="?tab=lagna" class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?= $tab === 'lagna' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' ?>">
        लग्न
    </a>
    <a href="?tab=bastu" class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?= $tab === 'bastu' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' ?>">
        बस्तु
    </a>
    <a href="?tab=gudmilan" class="px-4 py-2 rounded-lg text-sm font-medium transition-all <?= $tab === 'gudmilan' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-200' ?>">
        गुड मिलन
    </a>
</div>

<?php if ($tab === 'dashboard'): ?>
<!-- Dashboard -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">दैनिक राशिफल</p>
                <p class="text-2xl font-bold">12</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <i data-lucide="calendar" class="w-6 h-6 text-blue-600"></i>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">मासिक राशिफल</p>
                <p class="text-2xl font-bold">12</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i data-lucide="calendar-range" class="w-6 h-6 text-green-600"></i>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">शुभ दिन</p>
                <p class="text-2xl font-bold">-</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <i data-lucide="sun" class="w-6 h-6 text-yellow-600"></i>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">बस्तु सिफारिस</p>
                <p class="text-2xl font-bold">12</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <i data-lucide="gem" class="w-6 h-6 text-purple-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Install Tables Button -->
<div class="stat-card">
    <h3 class="font-bold text-lg mb-4">डेटाबेस सेटअप</h3>
    <p class="text-gray-600 mb-4">ज्योतिष टेबलहरू सिर्जना गर्न र प्रारम्भिक डेटा लोड गर्न:</p>
    <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="install_tables">
        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            <i data-lucide="database" class="w-4 h-4 inline mr-1"></i> टेबलहरू सिर्जना गर्नुहोस्
        </button>
    </form>
</div>

<div class="stat-card mt-4">
    <h3 class="font-bold text-lg mb-4">दैनिक राशिफल जेनेरेट गर्नुहोस्</h3>
    <p class="text-gray-600 mb-4">सबै राशिका लागि आजको दैनिक राशिफल जेनेरेट गर्नुहोस्:</p>
    <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="generate_daily">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i data-lucide="sparkles" class="w-4 h-4 inline mr-1"></i> दैनिक राशिफल जेनेरेट गर्नुहोस्
        </button>
    </form>
</div>

<?php elseif ($tab === 'daily'): ?>
<!-- Daily Horoscope -->
<div class="stat-card">
    <h3 class="font-bold text-lg mb-4">दैनिक राशिफल</h3>
    
    <form method="POST" class="mb-6 p-4 bg-gray-50 rounded-lg">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_daily">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="form-label">राशि चयन</label>
                <select name="sign" class="form-control" required>
                    <option value="">-- राशि चयन गर्नुहोस् --</option>
                    <?php foreach ($signs as $s): ?>
                    <option value="<?= $s ?>"><?= $sign_names[$s] ?> (<?= ucfirst($s) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">मिति</label>
                <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div>
                <label class="form-label">जम्मा स्कोर (1-5)</label>
                <input type="number" name="overall_score" class="form-control" step="0.1" min="1" max="5" value="3.0">
            </div>
            <div>
                <label class="form-label">प्रेम स्कोर</label>
                <input type="number" name="love_score" class="form-control" step="0.1" min="1" max="5" value="3.0">
            </div>
            <div>
                <label class="form-label">कार्य स्कोर</label>
                <input type="number" name="career_score" class="form-control" step="0.1" min="1" max="5" value="3.0">
            </div>
            <div>
                <label class="form-label">स्वास्थ्य स्कोर</label>
                <input type="number" name="health_score" class="form-control" step="0.1" min="1" max="5" value="3.0">
            </div>
            <div>
                <label class="form-label">आर्थिक स्कोर</label>
                <input type="number" name="finance_score" class="form-control" step="0.1" min="1" max="5" value="3.0">
            </div>
            <div>
                <label class="form-label">शुभ रंग</label>
                <input type="text" name="lucky_color" class="form-control" placeholder="जस्तै: रातो">
            </div>
            <div>
                <label class="form-label">शुभ नम्बर</label>
                <input type="number" name="lucky_number" class="form-control" min="1" max="99" value="3">
            </div>
            <div>
                <label class="form-label">शुभ दिशा</label>
                <input type="text" name="lucky_direction" class="form-control" placeholder="जस्तै: पूर्व">
            </div>
        </div>
        
        <div class="mt-4">
            <label class="form-label">राशिफल भविष्यवाणी</label>
            <textarea name="prediction" class="form-control" rows="3" placeholder="आजको राशिफल..."></textarea>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="form-label">चेतावनी</label>
                <input type="text" name="caution" class="form-control" placeholder="के कुरामा ध्यान दिनुहोस्">
            </div>
            <div>
                <label class="form-label">मन्त्र</label>
                <input type="text" name="mantra" class="form-control" placeholder="जस्तै: ॐ नमः शिवाय">
            </div>
        </div>
        
        <button type="submit" class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i data-lucide="save" class="w-4 h-4 inline mr-1"></i> सेभ गर्नुहोस्
        </button>
    </form>
    
    <!-- Today's Rashifal List -->
    <h4 class="font-bold text-md mb-3">आजका राशिफलहरू</h4>
    <div class="overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>राशि</th>
                    <th>जम्मा</th>
                    <th>शुभ रंग</th>
                    <th>शुभ नम्बर</th>
                    <th>कार्यहरू</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $db = db();
                $result = $db->query("SELECT * FROM horoscope_daily WHERE date = CURDATE() ORDER BY sign");
                while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><span class="font-medium"><?= $sign_names[$row['sign']] ?? $row['sign'] ?></span></td>
                    <td><?= $row['overall_score'] ?>/5</td>
                    <td><?= h($row['lucky_color']) ?></td>
                    <td><?= $row['lucky_number'] ?></td>
                    <td>
                        <a href="?tab=daily&view=<?= $row['sign'] ?>" class="text-blue-600 hover:underline">हेर्नुहोस्</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($tab === 'monthly'): ?>
<!-- Monthly Horoscope -->
<div class="stat-card">
    <h3 class="font-bold text-lg mb-4">मासिक राशिफल</h3>
    
    <form method="POST" class="mb-6 p-4 bg-gray-50 rounded-lg">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_monthly">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="form-label">राशि</label>
                <select name="sign" class="form-control" required>
                    <option value="">-- राशि चयन --</option>
                    <?php foreach ($signs as $s): ?>
                    <option value="<?= $s ?>"><?= $sign_names[$s] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">महिना</label>
                <select name="month" class="form-control" required>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m == date('n') ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="form-label">वर्ष</label>
                <input type="number" name="year" class="form-control" value="<?= date('Y') ?>" required>
            </div>
        </div>
        
        <div class="mt-4">
            <label class="form-label">समग्र भविष्यवाणी</label>
            <textarea name="overall_prediction" class="form-control" rows="2"></textarea>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="form-label">प्रेम</label>
                <textarea name="love_prediction" class="form-control" rows="2"></textarea>
            </div>
            <div>
                <label class="form-label">कार्य</label>
                <textarea name="career_prediction" class="form-control" rows="2"></textarea>
            </div>
            <div>
                <label class="form-label">स्वास्थ्य</label>
                <textarea name="health_prediction" class="form-control" rows="2"></textarea>
            </div>
            <div>
                <label class="form-label">आर्थिक</label>
                <textarea name="finance_prediction" class="form-control" rows="2"></textarea>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="form-label">महत्वपूर्ण मितिहरू</label>
                <textarea name="important_dates" class="form-control" rows="2" placeholder="महत्वपूर्ण मितिहरू..."></textarea>
            </div>
            <div>
                <label class="form-label">मुख्य विषयहरू</label>
                <textarea name="key_themes" class="form-control" rows="2" placeholder="यस महिनाका मुख्य विषयहरू..."></textarea>
            </div>
        </div>
        
        <button type="submit" class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i data-lucide="save" class="w-4 h-4 inline mr-1"></i> सेभ गर्नुहोस्
        </button>
    </form>
</div>

<?php elseif ($tab === 'yearly'): ?>
<!-- Yearly Horoscope -->
<div class="stat-card">
    <h3 class="font-bold text-lg mb-4">वार्षिक राशिफल</h3>
    
    <form method="POST" class="mb-6 p-4 bg-gray-50 rounded-lg">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_yearly">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">राशि</label>
                <select name="sign" class="form-control" required>
                    <option value="">-- राशि चयन --</option>
                    <?php foreach ($signs as $s): ?>
                    <option value="<?= $s ?>"><?= $sign_names[$s] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">वर्ष</label>
                <input type="number" name="year" class="form-control" value="<?= date('Y') ?>" required>
            </div>
        </div>
        
        <div class="mt-4">
            <label class="form-label">वार्षिक अवलोकन</label>
            <textarea name="overview" class="form-control" rows="3"></textarea>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div><label class="form-label">प्रेम</label><textarea name="love" class="form-control" rows="2"></textarea></div>
            <div><label class="form-label">कार्य</label><textarea name="career" class="form-control" rows="2"></textarea></div>
            <div><label class="form-label">स्वास्थ्य</label><textarea name="health" class="form-control" rows="2"></textarea></div>
            <div><label class="form-label">आर्थिक</label><textarea name="finance" class="form-control" rows="2"></textarea></div>
        </div>
        
        <div class="mt-4">
            <label class="form-label">मुख्य भविष्यवाणीहरू</label>
            <textarea name="key_predictions" class="form-control" rows="3"></textarea>
        </div>
        
        <button type="submit" class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i data-lucide="save" class="w-4 h-4 inline mr-1"></i> सेभ गर्नुहोस्
        </button>
    </form>
</div>

<?php elseif ($tab === 'auspicious'): ?>
<!-- Auspicious Days -->
<div class="stat-card">
    <h3 class="font-bold text-lg mb-4">शुभ दिनहरू</h3>
    
    <form method="POST" class="mb-6 p-4 bg-gray-50 rounded-lg">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_auspicious">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="form-label">नेपाली मिति</label>
                <input type="text" name="nepali_date" class="form-control" placeholder="जस्तै: बैशाख १, २०८१">
            </div>
            <div>
                <label class="form-label">अंग्रेजी मिति</label>
                <input type="date" name="english_date" class="form-control">
            </div>
            <div>
                <label class="form-label">दिनको नाम</label>
                <input type="text" name="day_name" class="form-control" placeholder="जस्तै: आइतबार">
            </div>
            <div>
                <label class="form-label">दिनको प्रकार</label>
                <select name="day_type" class="form-control">
                    <option value="shubh">शुभ</option>
                    <option value="chala">चला</option>
                    <option value="sadharan">साधारण</option>
                </select>
            </div>
            <div>
                <label class="form-label">महिना</label>
                <input type="number" name="month" class="form-control" value="<?= date('n') ?>" min="1" max="12">
            </div>
            <div>
                <label class="form-label">वर्ष</label>
                <input type="number" name="year" class="form-control" value="<?= date('Y') ?>">
            </div>
            <div>
                <label class="form-label">दिन</label>
                <input type="number" name="day" class="form-control" value="<?= date('j') ?>" min="1" max="32">
            </div>
        </div>
        
        <div class="mt-4">
            <label class="form-label">शीर्षक</label>
            <input type="text" name="title" class="form-control" placeholder="जस्तै: विवाहको शुभ मुहूर्त">
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="form-label">विवरण</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div>
                <label class="form-label">महत्व</label>
                <textarea name="significance" class="form-control" rows="3"></textarea>
            </div>
        </div>
        
        <button type="submit" class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i data-lucide="save" class="w-4 h-4 inline mr-1"></i> सेभ गर्नुहोस्
        </button>
    </form>
</div>

<?php elseif ($tab === 'subhatime'): ?>
<!-- Auspicious Times -->
<div class="stat-card">
    <h3 class="font-bold text-lg mb-4">शुभ समय</h3>
    
    <form method="POST" class="mb-6 p-4 bg-gray-50 rounded-lg">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_auspicious_time">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">नेपाली मिति</label>
                <input type="text" name="nepali_date" class="form-control" value="<?= nepali_date_today() ?>">
            </div>
            <div>
                <label class="form-label">अंग्रेजी मिति</label>
                <input type="date" name="english_date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div>
                <label class="form-label">अभिजीत मुहूर्त</label>
                <input type="text" name="abhijeet_mulat" class="form-control" placeholder="जस्तै: ११:४५ - १२:३७">
            </div>
            <div>
                <label class="form-label">ब्रह्म मुहूर्त</label>
                <input type="text" name="brahma_muhurat" class="form-control" placeholder="जस्तै: ०४:४५ - ०५:३०">
            </div>
            <div>
                <label class="form-label">अमृत कलश</label>
                <input type="text" name="amrit_kalash" class="form-control" placeholder="जस्तै: १२:१५ - १३:००">
            </div>
            <div>
                <label class="form-label">रवि कलश</label>
                <input type="text" name="ravi_kalash" class="form-control" placeholder="जस्तै: ०६:१५ - ०७:००">
            </div>
            <div>
                <label class="form-label">चार्टाइम सुरु</label>
                <input type="text" name="chartime_start" class="form-control" placeholder="जस्तै: १०:००">
            </div>
            <div>
                <label class="form-label">चार्टाइम अन्त्य</label>
                <input type="text" name="chartime_end" class="form-control" placeholder="जस्तै: ११:००">
            </div>
            <div>
                <label class="form-label">लाभ कलश</label>
                <input type="text" name="labh_kalash" class="form-control" placeholder="जस्तै: १४:०० - १५:३०">
            </div>
            <div>
                <label class="form-label">शुभ कलश</label>
                <input type="text" name="shubh_kalash" class="form-control" placeholder="जस्तै: ०९:०० - ११:००">
            </div>
        </div>
        
        <div class="mt-4">
            <label class="form-label">नोट</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
        </div>
        
        <button type="submit" class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i data-lucide="save" class="w-4 h-4 inline mr-1"></i> सेभ गर्नुहोस्
        </button>
    </form>
</div>

<?php elseif ($tab === 'lagna'): ?>
<!-- Lagna Info -->
<div class="stat-card">
    <h3 class="font-bold text-lg mb-4">लग्न जानकारी</h3>
    
    <form method="POST" class="mb-6 p-4 bg-gray-50 rounded-lg">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_lagna">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">नेपाली मिति</label>
                <input type="text" name="nepali_date" class="form-control" value="<?= nepali_date_today() ?>">
            </div>
            <div>
                <label class="form-label">अंग्रेजी मिति</label>
                <input type="date" name="english_date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div>
                <label class="form-label">चन्द्र राशि</label>
                <input type="text" name="moon_sign" class="form-control" placeholder="जस्तै: कर्कट">
            </div>
            <div>
                <label class="form-label">लग्न (Ascendant)</label>
                <input type="text" name="ascendant" class="form-control" placeholder="जस्तै: मिथुन">
            </div>
            <div>
                <label class="form-label">नक्षत्र</label>
                <input type="text" name="nakshatra" class="form-control" placeholder="जस्तै: अश्लेषा">
            </div>
            <div>
                <label class="form-label">तिथि</label>
                <input type="text" name="tithi" class="form-control" placeholder="जस्तै: एकादशी">
            </div>
            <div>
                <label class="form-label">योग</label>
                <input type="text" name="yoga" class="form-control" placeholder="जस्तै: सिद्धि">
            </div>
            <div>
                <label class="form-label">करण</label>
                <input type="text" name="karana" class="form-control" placeholder="जस्तै: बालव">
            </div>
        </div>
        
        <div class="mt-4">
            <label class="form-label">नोट</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
        </div>
        
        <button type="submit" class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i data-lucide="save" class="w-4 h-4 inline mr-1"></i> सेभ गर्नुहोस्
        </button>
    </form>
</div>

<?php elseif ($tab === 'bastu'): ?>
<!-- Bastu Recommendations -->
<div class="stat-card">
    <h3 class="font-bold text-lg mb-4">बस्तु सिफारिस</h3>
    
    <form method="POST" class="mb-6 p-4 bg-gray-50 rounded-lg">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_bastu">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="form-label">राशि</label>
                <select name="sign" class="form-control" required>
                    <option value="">-- राशि चयन --</option>
                    <?php foreach ($signs as $s): ?>
                    <option value="<?= $s ?>"><?= $sign_names[$s] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">मनपर्ने रत्न</label>
                <input type="text" name="fav_gem" class="form-control" placeholder="जस्तै: पु deb">
            </div>
            <div>
                <label class="form-label">मनपर्ने रंग</label>
                <input type="text" name="fav_color" class="form-control" placeholder="जस्तै: रातो">
            </div>
            <div>
                <label class="form-label">मनपर्ने दिन</label>
                <input type="text" name="fav_day" class="form-control" placeholder="जस्तै: मंगलबार">
            </div>
            <div>
                <label class="form-label">मनपर्ने धातु</label>
                <input type="text" name="fav_metal" class="form-control" placeholder="जस्तै: तामा">
            </div>
            <div>
                <label class="form-label">मनपर्ने नम्बर</label>
                <input type="number" name="fav_number" class="form-control" min="1" max="99">
            </div>
            <div>
                <label class="form-label">मनपर्ने दिशा</label>
                <input type="text" name="fav_direction" class="form-control" placeholder="जस्तै: पूर्व">
            </div>
            <div>
                <label class="form-label">लगाउने रत्न</label>
                <input type="text" name="wear_gem" class="form-control" placeholder="रत्न जुन लगाउनुपर्छ">
            </div>
            <div>
                <label class="form-label">नलगाउने रत्न</label>
                <input type="text" name="avoid_gem" class="form-control" placeholder="रत्न जुन नलगाउनुपर्छ">
            </div>
            <div>
                <label class="form-label">घरको दिशा</label>
                <input type="text" name="home_direction" class="form-control" placeholder="घर कुन दिशामा हुनुपर्छ">
            </div>
            <div>
                <label class="form-label">अफिसको दिशा</label>
                <input type="text" name="office_direction" class="form-control" placeholder="अफिस कुन दिशामा हुनुपर्छ">
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="form-label">राम्रो फेंगसुई</label>
                <textarea name="good_feng_shui" class="form-control" rows="2"></textarea>
            </div>
            <div>
                <label class="form-label">नराम्रो फेंगसुई</label>
                <textarea name="bad_feng_shui" class="form-control" rows="2"></textarea>
            </div>
        </div>
        
        <button type="submit" class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i data-lucide="save" class="w-4 h-4 inline mr-1"></i> सेभ गर्नुहोस्
        </button>
    </form>
</div>

<?php elseif ($tab === 'gudmilan'): ?>
<!-- Gud Milan -->
<div class="stat-card">
    <h3 class="font-bold text-lg mb-4">गुड मिलन (जोडी मिलान)</h3>
    
    <div class="overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>केटा राशि</th>
                    <th>केटी राशि</th>
                    <th>मिलन %</th>
                    <th>स्थिति</th>
                    <th>सारांश</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $db = db();
                $result = $db->query("SELECT * FROM gud_milan ORDER BY total_score DESC LIMIT 50");
                while ($row = $result->fetch_assoc()):
                    $boy_name = $sign_names[$row['boy_sign']] ?? $row['boy_sign'];
                    $girl_name = $sign_names[$row['girl_sign']] ?? $row['girl_sign'];
                ?>
                <tr>
                    <td><?= $boy_name ?></td>
                    <td><?= $girl_name ?></td>
                    <td><?= $row['total_score'] ?>%</td>
                    <td>
                        <span class="px-2 py-1 rounded text-xs <?= $row['compatibility'] === 'उत्तम' ? 'bg-green-100 text-green-800' : ($row['compatibility'] === 'राम्रो' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') ?>">
                            <?= $row['compatibility'] ?>
                        </span>
                    </td>
                    <td class="text-sm"><?= h($row['summary']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

</div>
</div>

<?php admin_html_end(); ?>
