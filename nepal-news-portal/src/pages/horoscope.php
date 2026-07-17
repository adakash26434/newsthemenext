<?php
/**
 * Horoscope Page - Public
 * Daily, Monthly, Yearly Rashifal and Astrology
 */

require SRC_DIR . '/lib/horoscope_service.php';
require SRC_DIR . '/lib/horoscope_tables.php';

$hs = horoscope_service();
$signs = $hs->getZodiacSigns();
$page = $_GET['page'] ?? 'daily';
$selected_sign = $_GET['sign'] ?? '';

// Get current tab info
$tab = $_GET['tab'] ?? 'daily';

// Get sign data if selected
$sign_data = null;
if ($selected_sign && isset($signs[$selected_sign])) {
    $sign_data = $signs[$selected_sign];
}

$meta_title = 'ज्योतिष | ' . site_name();
$meta_description = 'दैनिक, मासिक र वार्षिक राशिफल। शुभ दिन, शुभ समय, बस्तु सिफारिस।';

function render_stars(float $score): string {
    $full = floor($score);
    $half = ($score - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    $html = str_repeat('<span class="star filled">★</span>', $full);
    $html .= str_repeat('<span class="star half">★</span>', $half);
    $html .= str_repeat('<span class="star empty">☆</span>', $empty);
    return $html;
}
?>
<!DOCTYPE html>
<html lang="ne" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($meta_title) ?></title>
    <meta name="description" content="<?= h($meta_description) ?>">
    <?php require SRC_DIR . '/layout/header.php'; ?>
    <style>
        .horoscope-page { max-width: 1200px; margin: 0 auto; padding: 20px 0; }
        .horoscope-tabs { display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap; }
        .horoscope-tab {
            padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px;
            background: var(--c-surface); border: 1px solid var(--c-border);
            color: var(--c-text2); transition: all 0.2s;
        }
        .horoscope-tab:hover { border-color: var(--c-secondary); color: var(--c-secondary); }
        .horoscope-tab.active { background: var(--c-secondary); color: #fff; border-color: var(--c-secondary); }
        
        .sign-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; margin-bottom: 30px; }
        .sign-card {
            background: var(--c-surface); border-radius: 12px; padding: 16px; text-align: center;
            border: 2px solid var(--c-border); cursor: pointer; transition: all 0.2s;
        }
        .sign-card:hover { border-color: var(--c-secondary); transform: translateY(-2px); }
        .sign-card.selected { border-color: var(--c-secondary); background: linear-gradient(135deg, var(--c-primary), var(--c-secondary)); color: #fff; }
        .sign-symbol { font-size: 2.5rem; margin-bottom: 8px; }
        .sign-name { font-weight: 700; font-size: 1rem; }
        .sign-dates { font-size: 10px; opacity: 0.7; }
        
        .rashifal-card {
            background: var(--c-surface); border-radius: 16px; padding: 24px;
            box-shadow: var(--shadow-md); margin-bottom: 24px;
        }
        .rashifal-header { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; }
        .rashifal-sign-info { flex: 1; }
        .rashifal-sign-name { font-size: 1.5rem; font-weight: 800; }
        .rashifal-sign-dates { color: var(--c-muted); font-size: 12px; }
        .rashifal-date { font-size: 12px; color: var(--c-muted); }
        
        .score-grid { display: grid; grid-cols-2 md:grid-cols-5 gap-4; margin-bottom: 20px; }
        .score-item {
            background: var(--c-surface2); border-radius: 10px; padding: 12px; text-align: center;
        }
        .score-label { font-size: 11px; color: var(--c-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .score-value { font-size: 1.5rem; font-weight: 800; color: var(--c-secondary); }
        .stars { font-size: 14px; color: #F59E0B; }
        
        .prediction-box {
            background: linear-gradient(135deg, var(--c-primary), var(--c-secondary));
            color: #fff; border-radius: 12px; padding: 20px; margin-bottom: 20px;
        }
        .prediction-title { font-size: 14px; opacity: 0.9; margin-bottom: 8px; }
        .prediction-text { font-size: 15px; line-height: 1.7; }
        
        .details-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
        .detail-item {
            background: var(--c-surface2); border-radius: 10px; padding: 16px;
            display: flex; align-items: center; gap: 12px;
        }
        .detail-icon { font-size: 1.5rem; }
        .detail-label { font-size: 11px; color: var(--c-muted); text-transform: uppercase; }
        .detail-value { font-weight: 700; font-size: 1rem; }
        
        .mantra-box {
            background: var(--c-surface2); border-radius: 10px; padding: 16px;
            border-left: 4px solid var(--c-secondary); margin-top: 20px;
        }
        .mantra-label { font-size: 11px; color: var(--c-muted); margin-bottom: 4px; }
        .mantra-text { font-size: 1.1rem; font-weight: 600; color: var(--c-secondary); }
        
        .caution-box {
            background: #FEF3C7; border-radius: 10px; padding: 16px; margin-top: 20px;
            border-left: 4px solid #D97706;
        }
        .caution-label { font-size: 12px; font-weight: 700; color: #92400E; margin-bottom: 4px; }
        .caution-text { font-size: 14px; color: #78350F; }
        
        .subha-times-grid { display: grid; grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap: 16px; }
        .subha-time-card {
            background: var(--c-surface); border-radius: 12px; padding: 20px; text-align: center;
            border: 1px solid var(--c-border);
        }
        .subha-time-icon { font-size: 2rem; margin-bottom: 12px; }
        .subha-time-name { font-weight: 700; margin-bottom: 8px; }
        .subha-time-value { font-size: 1.2rem; font-weight: 600; color: var(--c-secondary); }
        
        .bastu-grid { display: grid; grid-cols-1 md:grid-cols-3 gap: 20px; }
        .bastu-item {
            background: var(--c-surface2); border-radius: 12px; padding: 20px;
            border-left: 4px solid var(--c-secondary);
        }
        .bastu-label { font-size: 12px; color: var(--c-muted); margin-bottom: 8px; text-transform: uppercase; }
        .bastu-value { font-size: 1.1rem; font-weight: 700; }
        
        .lagna-info-grid { display: grid; grid-cols-2 md:grid-cols-4 gap: 16px; }
        .lagna-item {
            background: var(--c-surface); border-radius: 12px; padding: 20px; text-align: center;
            border: 1px solid var(--c-border);
        }
        .lagna-name { font-size: 12px; color: var(--c-muted); margin-bottom: 8px; }
        .lagna-value { font-size: 1.2rem; font-weight: 700; color: var(--c-secondary); }
        
        .gudmilan-form {
            background: var(--c-surface); border-radius: 16px; padding: 24px;
            margin-bottom: 24px; display: grid; grid-cols-1 md:grid-cols-3 gap: 16px;
            align-items: end;
        }
        .gudmilan-result {
            background: linear-gradient(135deg, var(--c-primary), var(--c-secondary));
            color: #fff; border-radius: 16px; padding: 24px; text-align: center;
        }
        .gudmilan-score { font-size: 3rem; font-weight: 800; }
        .gudmilan-status { font-size: 1.2rem; margin-top: 8px; }
        
        @media (max-width: 768px) {
            .sign-grid { grid-template-columns: repeat(3, 1fr); }
            .score-grid { grid-cols-2; }
            .rashifal-header { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
    <?php require SRC_DIR . '/layout/header.php'; ?>
    
    <main class="horoscope-page">
        <div class="container mx-auto px-4">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold mb-2" style="color: var(--c-text);">
                    <span class="flex items-center gap-3">
                        <span>🔮</span> ज्योतिष
                    </span>
                </h1>
                <p class="text-gray-500">दैनिक, मासिक र वार्षिक राशिफल। शुभ दिन, शुभ समय, बस्तु सिफारिस।</p>
            </div>
            
            <!-- Tabs -->
            <div class="horoscope-tabs">
                <a href="?tab=daily" class="horoscope-tab <?= $tab === 'daily' ? 'active' : '' ?>">दैनिक राशिफल</a>
                <a href="?tab=monthly" class="horoscope-tab <?= $tab === 'monthly' ? 'active' : '' ?>">मासिक राशिफल</a>
                <a href="?tab=yearly" class="horoscope-tab <?= $tab === 'yearly' ? 'active' : '' ?>">वार्षिक राशिफल</a>
                <a href="?tab=subhatime" class="horoscope-tab <?= $tab === 'subhatime' ? 'active' : '' ?>">शुभ समय</a>
                <a href="?tab=subhadin" class="horoscope-tab <?= $tab === 'subhadin' ? 'active' : '' ?>">शुभ दिन</a>
                <a href="?tab=lagna" class="horoscope-tab <?= $tab === 'lagna' ? 'active' : '' ?>">लग्न</a>
                <a href="?tab=bastu" class="horoscope-tab <?= $tab === 'bastu' ? 'active' : '' ?>">बस्तु</a>
                <a href="?tab=gudmilan" class="horoscope-tab <?= $tab === 'gudmilan' ? 'active' : '' ?>">गुड मिलन</a>
            </div>
            
            <?php if ($tab === 'daily'): ?>
            <!-- Daily Horoscope -->
            <div class="sign-grid">
                <?php foreach ($signs as $key => $sign): ?>
                <a href="?tab=daily&sign=<?= $key ?>" class="sign-card <?= $selected_sign === $key ? 'selected' : '' ?>">
                    <div class="sign-symbol"><?= $sign['symbol'] ?></div>
                    <div class="sign-name"><?= $sign['name'] ?></div>
                    <div class="sign-dates"><?= $sign['dates'] ?></div>
                </a>
                <?php endforeach; ?>
            </div>
            
            <?php if ($selected_sign && $sign_data): ?>
            <?php
            $daily = $hs->getDailyRashifal($selected_sign);
            $bastu = $hs->getBastuRecommendations($selected_sign);
            ?>
            <div class="rashifal-card">
                <div class="rashifal-header">
                    <div class="text-6xl"><?= $sign_data['symbol'] ?></div>
                    <div class="rashifal-sign-info">
                        <div class="rashifal-sign-name"><?= $sign_data['name'] ?> राशि</div>
                        <div class="rashifal-sign-dates"><?= $sign_data['dates'] ?></div>
                    </div>
                    <div class="rashifal-date">
                        <?= date('Y-m-d') ?> (<?= date('l') ?>)
                    </div>
                </div>
                
                <?php if ($daily): ?>
                <div class="score-grid">
                    <div class="score-item">
                        <div class="score-label">जम्मा</div>
                        <div class="score-value"><?= $daily['overall_score'] ?>/5</div>
                        <div class="stars"><?= render_stars($daily['overall_score']) ?></div>
                    </div>
                    <div class="score-item">
                        <div class="score-label">प्रेम</div>
                        <div class="score-value"><?= $daily['love_score'] ?>/5</div>
                        <div class="stars"><?= render_stars($daily['love_score']) ?></div>
                    </div>
                    <div class="score-item">
                        <div class="score-label">कार्य</div>
                        <div class="score-value"><?= $daily['career_score'] ?>/5</div>
                        <div class="stars"><?= render_stars($daily['career_score']) ?></div>
                    </div>
                    <div class="score-item">
                        <div class="score-label">स्वास्थ्य</div>
                        <div class="score-value"><?= $daily['health_score'] ?>/5</div>
                        <div class="stars"><?= render_stars($daily['health_score']) ?></div>
                    </div>
                    <div class="score-item">
                        <div class="score-label">आर्थिक</div>
                        <div class="score-value"><?= $daily['finance_score'] ?>/5</div>
                        <div class="stars"><?= render_stars($daily['finance_score']) ?></div>
                    </div>
                </div>
                
                <div class="prediction-box">
                    <div class="prediction-title">आजको भविष्यवाणी</div>
                    <div class="prediction-text"><?= h($daily['prediction']) ?></div>
                </div>
                
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="detail-icon">🎨</span>
                        <div>
                            <div class="detail-label">शुभ रंग</div>
                            <div class="detail-value"><?= h($daily['lucky_color'] ?? 'सेतो') ?></div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <span class="detail-icon">🔢</span>
                        <div>
                            <div class="detail-label">शुभ नम्बर</div>
                            <div class="detail-value"><?= $daily['lucky_number'] ?? 3 ?></div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <span class="detail-icon">🧭</span>
                        <div>
                            <div class="detail-label">शुभ दिशा</div>
                            <div class="detail-value"><?= h($daily['lucky_direction'] ?? 'पूर्व') ?></div>
                        </div>
                    </div>
                    <?php if ($bastu && !empty($bastu['fav_gem'])): ?>
                    <div class="detail-item">
                        <span class="detail-icon">💎</span>
                        <div>
                            <div class="detail-label">शुभ रत्न</div>
                            <div class="detail-value"><?= h($bastu['fav_gem']) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($daily['mantra'])): ?>
                <div class="mantra-box">
                    <div class="mantra-label">आजको मन्त्र</div>
                    <div class="mantra-text"><?= h($daily['mantra']) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($daily['caution'])): ?>
                <div class="caution-box">
                    <div class="caution-label">⚠️ चेतावनी</div>
                    <div class="caution-text"><?= h($daily['caution']) ?></div>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <p>आजको राशिफल छिट्टै आउनेछ। कृपया पछि हेर्नुहोस्।</p>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-12 text-gray-500">
                <p class="text-lg">आफ्नो राशि चयन गर्नुहोस्</p>
            </div>
            <?php endif; ?>
            
            <?php elseif ($tab === 'monthly'): ?>
            <!-- Monthly Horoscope -->
            <div class="sign-grid">
                <?php foreach ($signs as $key => $sign): ?>
                <a href="?tab=monthly&sign=<?= $key ?>" class="sign-card <?= $selected_sign === $key ? 'selected' : '' ?>">
                    <div class="sign-symbol"><?= $sign['symbol'] ?></div>
                    <div class="sign-name"><?= $sign['name'] ?></div>
                </a>
                <?php endforeach; ?>
            </div>
            
            <?php if ($selected_sign && $sign_data): ?>
            <?php $monthly = $hs->getMonthlyRashifal($selected_sign); ?>
            <div class="rashifal-card">
                <div class="rashifal-header">
                    <div class="text-6xl"><?= $sign_data['symbol'] ?></div>
                    <div class="rashifal-sign-info">
                        <div class="rashifal-sign-name"><?= $sign_data['name'] ?> - <?= date('F Y') ?></div>
                        <div class="rashifal-sign-dates"><?= $sign_data['dates'] ?></div>
                    </div>
                </div>
                
                <?php if ($monthly): ?>
                <div class="prediction-box">
                    <div class="prediction-title">मासिक अवलोकन</div>
                    <div class="prediction-text"><?= h($monthly['overall_prediction']) ?></div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <h4 class="font-bold mb-2">💕 प्रेम</h4>
                        <p class="text-sm"><?= h($monthly['love_prediction']) ?></p>
                    </div>
                    <div class="p-4 bg-green-50 rounded-lg">
                        <h4 class="font-bold mb-2">💼 कार्य</h4>
                        <p class="text-sm"><?= h($monthly['career_prediction']) ?></p>
                    </div>
                    <div class="p-4 bg-yellow-50 rounded-lg">
                        <h4 class="font-bold mb-2">❤️ स्वास्थ्य</h4>
                        <p class="text-sm"><?= h($monthly['health_prediction']) ?></p>
                    </div>
                    <div class="p-4 bg-purple-50 rounded-lg">
                        <h4 class="font-bold mb-2">💰 आर्थिक</h4>
                        <p class="text-sm"><?= h($monthly['finance_prediction']) ?></p>
                    </div>
                </div>
                <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <p>यस महिनाको राशिफल छिट्टै आउनेछ।</p>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php elseif ($tab === 'yearly'): ?>
            <!-- Yearly Horoscope -->
            <div class="sign-grid">
                <?php foreach ($signs as $key => $sign): ?>
                <a href="?tab=yearly&sign=<?= $key ?>" class="sign-card <?= $selected_sign === $key ? 'selected' : '' ?>">
                    <div class="sign-symbol"><?= $sign['symbol'] ?></div>
                    <div class="sign-name"><?= $sign['name'] ?></div>
                </a>
                <?php endforeach; ?>
            </div>
            
            <?php if ($selected_sign && $sign_data): ?>
            <?php $yearly = $hs->getYearlyRashifal($selected_sign); ?>
            <div class="rashifal-card">
                <div class="rashifal-header">
                    <div class="text-6xl"><?= $sign_data['symbol'] ?></div>
                    <div class="rashifal-sign-info">
                        <div class="rashifal-sign-name"><?= $sign_data['name'] ?> - <?= date('Y') ?> को राशिफल</div>
                    </div>
                </div>
                
                <?php if ($yearly): ?>
                <div class="prediction-box">
                    <div class="prediction-title">वार्षिक अवलोकन</div>
                    <div class="prediction-text"><?= h($yearly['overview']) ?></div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <h4 class="font-bold mb-2">💕 प्रेम</h4>
                        <p><?= h($yearly['love']) ?></p>
                    </div>
                    <div class="p-4 bg-green-50 rounded-lg">
                        <h4 class="font-bold mb-2">💼 कार्य</h4>
                        <p><?= h($yearly['career']) ?></p>
                    </div>
                    <div class="p-4 bg-yellow-50 rounded-lg">
                        <h4 class="font-bold mb-2">❤️ स्वास्थ्य</h4>
                        <p><?= h($yearly['health']) ?></p>
                    </div>
                    <div class="p-4 bg-purple-50 rounded-lg">
                        <h4 class="font-bold mb-2">💰 आर्थिक</h4>
                        <p><?= h($yearly['finance']) ?></p>
                    </div>
                </div>
                <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <p>वार्षिक राशिफल छिट्टै आउनेछ।</p>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <?php elseif ($tab === 'subhatime'): ?>
            <!-- Auspicious Times -->
            <?php $subha_times = $hs->getTodayAuspiciousTimes(); ?>
            <div class="rashifal-card">
                <h2 class="text-xl font-bold mb-6">📅 आजको शुभ समय (<?= date('Y-m-d') ?>)</h2>
                
                <?php if ($subha_times): ?>
                <div class="subha-times-grid">
                    <div class="subha-time-card">
                        <div class="subha-time-icon">☀️</div>
                        <div class="subha-time-name">अभिजीत मुहूर्त</div>
                        <div class="subha-time-value"><?= h($subha_times['abhijeet_mulat']) ?></div>
                    </div>
                    <div class="subha-time-card">
                        <div class="subha-time-icon">🌅</div>
                        <div class="subha-time-name">ब्रह्म मुहूर्त</div>
                        <div class="subha-time-value"><?= h($subha_times['brahma_muhurat']) ?></div>
                    </div>
                    <div class="subha-time-card">
                        <div class="subha-time-icon">💧</div>
                        <div class="subha-time-name">अमृत कलश</div>
                        <div class="subha-time-value"><?= h($subha_times['amrit_kalash']) ?></div>
                    </div>
                    <div class="subha-time-card">
                        <div class="subha-time-icon">🌞</div>
                        <div class="subha-time-name">रवि कलश</div>
                        <div class="subha-time-value"><?= h($subha_times['Ravi_kalash']) ?></div>
                    </div>
                    <div class="subha-time-card">
                        <div class="subha-time-icon">💰</div>
                        <div class="subha-time-name">लाभ कलश</div>
                        <div class="subha-time-value"><?= h($subha_times['labh_kalash']) ?></div>
                    </div>
                    <div class="subha-time-card">
                        <div class="subha-time-icon">✨</div>
                        <div class="subha-time-name">शुभ कलश</div>
                        <div class="subha-time-value"><?= h($subha_times['shubh_kalash']) ?></div>
                    </div>
                    <?php if (!empty($subha_times['chartime_start'])): ?>
                    <div class="subha-time-card">
                        <div class="subha-time-icon">📋</div>
                        <div class="subha-time-name">चार्टाइम</div>
                        <div class="subha-time-value"><?= h($subha_times['chartime_start']) ?> - <?= h($subha_times['chartime_end']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($subha_times['notes'])): ?>
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm"><?= h($subha_times['notes']) ?></p>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <p>आजको शुभ समय जानकारी छिट्टै आउनेछ।</p>
                </div>
                <?php endif; ?>
            </div>
            
            <?php elseif ($tab === 'subhadin'): ?>
            <!-- Auspicious Days -->
            <?php $subha_days = $hs->getAuspiciousDays(); ?>
            <div class="rashifal-card">
                <h2 class="text-xl font-bold mb-6">📆 शुभ दिनहरू (<?= date('F Y') ?>)</h2>
                
                <?php if (!empty($subha_days)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($subha_days as $day): ?>
                    <div class="p-4 bg-gradient-to-br from-green-50 to-blue-50 rounded-lg border border-green-200">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-2xl"><?= $day['day_type'] === 'shubh' ? '🟢' : '🟡' ?></span>
                            <span class="font-bold"><?= h($day['title']) ?></span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2"><?= h($day['description']) ?></p>
                        <p class="text-xs text-gray-500"><?= h($day['nepali_date']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <p>शुभ दिनहरूको जानकारी छिट्टै आउनेछ।</p>
                </div>
                <?php endif; ?>
            </div>
            
            <?php elseif ($tab === 'lagna'): ?>
            <!-- Lagna Info -->
            <?php $lagna = $hs->getTodayLagna(); ?>
            <div class="rashifal-card">
                <h2 class="text-xl font-bold mb-6">🌟 आजको लग्न (<?= date('Y-m-d') ?>)</h2>
                
                <?php if ($lagna): ?>
                <div class="lagna-info-grid">
                    <div class="lagna-item">
                        <div class="lagna-name">चन्द्र राशि</div>
                        <div class="lagna-value"><?= h($lagna['moon_sign']) ?></div>
                    </div>
                    <div class="lagna-item">
                        <div class="lagna-name">लग्न</div>
                        <div class="lagna-value"><?= h($lagna['ascendant']) ?></div>
                    </div>
                    <div class="lagna-item">
                        <div class="lagna-name">नक्षत्र</div>
                        <div class="lagna-value"><?= h($lagna['nakshatra']) ?></div>
                    </div>
                    <div class="lagna-item">
                        <div class="lagna-name">तिथि</div>
                        <div class="lagna-value"><?= h($lagna['tithi']) ?></div>
                    </div>
                    <div class="lagna-item">
                        <div class="lagna-name">योग</div>
                        <div class="lagna-value"><?= h($lagna['yoga']) ?></div>
                    </div>
                    <div class="lagna-item">
                        <div class="lagna-name">करण</div>
                        <div class="lagna-value"><?= h($lagna['karana']) ?></div>
                    </div>
                </div>
                
                <?php if (!empty($lagna['notes'])): ?>
                <div class="mt-6 p-4 bg-purple-50 rounded-lg">
                    <p><?= h($lagna['notes']) ?></p>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <p>आजको लग्न जानकारी छिट्टै आउनेछ।</p>
                </div>
                <?php endif; ?>
            </div>
            
            <?php elseif ($tab === 'bastu'): ?>
            <!-- Bastu Recommendations -->
            <div class="sign-grid">
                <?php foreach ($signs as $key => $sign): ?>
                <a href="?tab=bastu&sign=<?= $key ?>" class="sign-card <?= $selected_sign === $key ? 'selected' : '' ?>">
                    <div class="sign-symbol"><?= $sign['symbol'] ?></div>
                    <div class="sign-name"><?= $sign['name'] ?></div>
                </a>
                <?php endforeach; ?>
            </div>
            
            <?php if ($selected_sign && $sign_data): ?>
            <?php $bastu = $hs->getBastuRecommendations($selected_sign); ?>
            <div class="rashifal-card">
                <div class="rashifal-header">
                    <div class="text-6xl"><?= $sign_data['symbol'] ?></div>
                    <div class="rashifal-sign-info">
                        <div class="rashifal-sign-name"><?= $sign_data['name'] ?> - बस्तु सिफारिस</div>
                    </div>
                </div>
                
                <?php if ($bastu): ?>
                <div class="bastu-grid">
                    <div class="bastu-item">
                        <div class="bastu-label">💎 मनपर्ने रत्न</div>
                        <div class="bastu-value"><?= h($bastu['fav_gem']) ?></div>
                    </div>
                    <div class="bastu-item">
                        <div class="bastu-label">🎨 मनपर्ने रंग</div>
                        <div class="bastu-value"><?= h($bastu['fav_color']) ?></div>
                    </div>
                    <div class="bastu-item">
                        <div class="bastu-label">📅 मनपर्ने दिन</div>
                        <div class="bastu-value"><?= h($bastu['fav_day']) ?></div>
                    </div>
                    <div class="bastu-item">
                        <div class="bastu-label">⚙️ मनपर्ने धातु</div>
                        <div class="bastu-value"><?= h($bastu['fav_metal']) ?></div>
                    </div>
                    <div class="bastu-item">
                        <div class="bastu-label">🔢 मनपर्ने नम्बर</div>
                        <div class="bastu-value"><?= $bastu['fav_number'] ?></div>
                    </div>
                    <div class="bastu-item">
                        <div class="bastu-label">🧭 मनपर्ने दिशा</div>
                        <div class="bastu-value"><?= h($bastu['fav_direction']) ?></div>
                    </div>
                    <div class="bastu-item">
                        <div class="bastu-label">✅ लगाउने रत्न</div>
                        <div class="bastu-value"><?= h($bastu['wear_gem']) ?></div>
                    </div>
                    <div class="bastu-item">
                        <div class="bastu-label">❌ नलगाउने रत्न</div>
                        <div class="bastu-value"><?= h($bastu['avoid_gem']) ?></div>
                    </div>
                    <div class="bastu-item">
                        <div class="bastu-label">🏠 घरको दिशा</div>
                        <div class="bastu-value"><?= h($bastu['home_direction']) ?></div>
                    </div>
                </div>
                <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <p>बस्तु सिफारिस जानकारी छिट्टै आउनेछ।</p>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-12 text-gray-500">
                <p class="text-lg">आफ्नो राशि चयन गर्नुहोस्</p>
            </div>
            <?php endif; ?>
            
            <?php elseif ($tab === 'gudmilan'): ?>
            <!-- Gud Milan -->
            <div class="rashifal-card">
                <h2 class="text-xl font-bold mb-6">💑 गुड मिलन (जोडी मिलान)</h2>
                
                <form class="gudmilan-form" method="get">
                    <input type="hidden" name="tab" value="gudmilan">
                    <div>
                        <label class="form-label">केटा राशि</label>
                        <select name="boy" class="form-control">
                            <option value="">-- राशि चयन --</option>
                            <?php foreach ($signs as $key => $sign): ?>
                            <option value="<?= $key ?>"><?= $sign['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">केटी राशि</label>
                        <select name="girl" class="form-control">
                            <option value="">-- राशि चयन --</option>
                            <?php foreach ($signs as $key => $sign): ?>
                            <option value="<?= $key ?>"><?= $sign['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        जाँच गर्नुहोस्
                    </button>
                </form>
                
                <?php if (!empty($_GET['boy']) && !empty($_GET['girl'])): ?>
                <?php $milan = $hs->getGudMilan($_GET['boy'], $_GET['girl']); ?>
                <?php if ($milan): ?>
                <div class="gudmilan-result">
                    <div class="text-6xl mb-4">
                        <?= $signs[$_GET['boy']]['symbol'] ?? '' ?> + <?= $signs[$_GET['girl']]['symbol'] ?? '' ?>
                    </div>
                    <div class="gudmilan-score"><?= $milan['total_score'] ?>%</div>
                    <div class="gudmilan-status"><?= h($milan['compatibility']) ?> मिलान</div>
                    <p class="mt-4 opacity-80"><?= h($milan['summary']) ?></p>
                </div>
                <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <p>मिलान जानकारी फेला परेन।</p>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <p>माथि दुवै राशि चयन गरी जाँच गर्नुहोस्।</p>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
        </div>
    </main>
    
    <?php require SRC_DIR . '/layout/footer.php'; ?>
</body>
</html>
