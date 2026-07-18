<?php
/**
 * Live Data Page - Public
 * Real-time earthquake, weather, alerts, government notices
 */

require SRC_DIR . '/lib/live_data_service.php';
require SRC_DIR . '/lib/live_data_tables.php';

$live = live_data();
$tab = $_GET['tab'] ?? 'dashboard';

// Pre-fetch data for dashboard
$dashboard = $live->getDashboard();

$meta_title = 'Live Data | ' . site_name();
?>
<!DOCTYPE html>
<html lang="ne" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($meta_title) ?></title>
    <?php require SRC_DIR . '/layout/header.php'; ?>
    <style>
        .live-page { max-width: 1200px; margin: 0 auto; padding: 20px 0; }
        
        .live-tabs { display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap; }
        .live-tab {
            padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 14px;
            background: var(--c-surface); border: 1px solid var(--c-border);
            color: var(--c-text2); transition: all 0.2s; cursor: pointer;
        }
        .live-tab:hover { border-color: var(--c-secondary); color: var(--c-secondary); }
        .live-tab.active { background: var(--c-secondary); color: #fff; border-color: var(--c-secondary); }
        
        .live-card {
            background: var(--c-surface); border-radius: 16px; padding: 24px;
            box-shadow: var(--shadow-md); margin-bottom: 24px;
        }
        .live-card-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 16px; padding-bottom: 12px;
            border-bottom: 2px solid var(--c-border);
        }
        .live-card-title { font-size: 1.2rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .live-badge { 
            padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;
            background: #10B981; color: #fff;
        }
        .live-badge.alert { background: #EF4444; }
        .live-badge.warning { background: #F59E0B; }
        
        /* Earthquake styles */
        .earthquake-list { display: flex; flex-direction: column; gap: 12px; }
        .earthquake-item {
            display: flex; align-items: center; gap: 16px; padding: 16px;
            background: var(--c-surface2); border-radius: 12px;
            border-left: 4px solid #EF4444;
        }
        .earthquake-item.minor { border-left-color: #F59E0B; }
        .earthquake-item.moderate { border-left-color: #EF4444; }
        .earthquake-item.strong { border-left-color: #DC2626; }
        .earthquake-mag {
            width: 60px; height: 60px; border-radius: 50%; display: flex;
            align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 800;
            background: #FEE2E2; color: #DC2626; flex-shrink: 0;
        }
        .earthquake-mag.minor { background: #FEF3C7; color: #D97706; }
        .earthquake-mag.moderate { background: #FEE2E2; color: #DC2626; }
        .earthquake-mag.strong { background: #FECACA; color: #991B1B; }
        .earthquake-info { flex: 1; }
        .earthquake-place { font-weight: 600; margin-bottom: 4px; }
        .earthquake-meta { font-size: 12px; color: var(--c-muted); }
        .earthquake-depth { font-size: 12px; color: var(--c-text2); }
        
        /* Weather styles */
        .weather-current {
            display: flex; align-items: center; gap: 24px; padding: 20px;
            background: linear-gradient(135deg, var(--c-primary), var(--c-secondary));
            border-radius: 16px; color: #fff; margin-bottom: 20px;
        }
        .weather-temp { font-size: 4rem; font-weight: 800; line-height: 1; }
        .weather-icon { font-size: 4rem; }
        .weather-details { flex: 1; }
        .weather-city { font-size: 1.5rem; font-weight: 600; margin-bottom: 4px; }
        .weather-desc { font-size: 1.1rem; opacity: 0.9; }
        .weather-meta { display: flex; gap: 16px; margin-top: 12px; font-size: 14px; opacity: 0.9; }
        .weather-extra { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; }
        .weather-extra-item {
            background: var(--c-surface2); border-radius: 12px; padding: 16px; text-align: center;
        }
        .weather-extra-icon { font-size: 1.5rem; margin-bottom: 8px; }
        .weather-extra-label { font-size: 12px; color: var(--c-muted); margin-bottom: 4px; }
        .weather-extra-value { font-size: 1.1rem; font-weight: 700; }
        
        .forecast-list { display: flex; gap: 12px; overflow-x: auto; padding: 8px 0; }
        .forecast-item {
            min-width: 100px; padding: 16px; background: var(--c-surface2);
            border-radius: 12px; text-align: center;
        }
        .forecast-day { font-size: 12px; color: var(--c-muted); margin-bottom: 8px; }
        .forecast-icon { font-size: 2rem; margin-bottom: 8px; }
        .forecast-temps { font-weight: 700; }
        .forecast-high { color: #EF4444; }
        .forecast-low { color: #3B82F6; }
        
        /* Alert styles */
        .alert-list { display: flex; flex-direction: column; gap: 12px; }
        .alert-item {
            display: flex; align-items: flex-start; gap: 16px; padding: 16px;
            background: #FEF3C7; border-radius: 12px;
            border-left: 4px solid #D97706;
        }
        .alert-item.severe { background: #FEE2E2; border-left-color: #DC2626; }
        .alert-item.info { background: #CFFAFE; border-left-color: #0891B2; }
        .alert-icon { font-size: 1.5rem; }
        .alert-content { flex: 1; }
        .alert-title { font-weight: 700; margin-bottom: 4px; }
        .alert-text { font-size: 14px; color: #374151; }
        .alert-time { font-size: 12px; color: var(--c-muted); margin-top: 8px; }
        .alert-source { font-size: 11px; color: var(--c-muted); background: rgba(255,255,255,0.5); padding: 2px 8px; border-radius: 10px; }
        
        /* Notice styles */
        .notice-list { display: flex; flex-direction: column; gap: 12px; }
        .notice-item {
            display: flex; align-items: center; gap: 16px; padding: 16px;
            background: var(--c-surface2); border-radius: 12px;
            border-left: 4px solid var(--c-secondary);
        }
        .notice-icon { font-size: 1.5rem; }
        .notice-content { flex: 1; }
        .notice-title { font-weight: 600; margin-bottom: 4px; }
        .notice-desc { font-size: 14px; color: var(--c-text2); }
        .notice-meta { font-size: 12px; color: var(--c-muted); margin-top: 4px; }
        
        /* Air Quality styles (colour-cleanup: was a loud green gradient card
           regardless of actual AQI level; now a neutral card, with the AQI
           number itself colour-coded by severity via inline style in the page) */
        .aqi-display {
            display: flex; align-items: center; gap: 24px; padding: 24px;
            background: var(--c-surface2); border: 1px solid var(--c-border);
            border-radius: 16px; color: var(--c-text); margin-bottom: 20px;
        }
        .aqi-value { font-size: 5rem; font-weight: 800; line-height: 1; }
        .aqi-info { flex: 1; }
        .aqi-status { font-size: 1.5rem; font-weight: 700; margin-bottom: 4px; }
        .aqi-desc { font-size: 1rem; color: var(--c-text2); }
        .pollutants-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; }
        .pollutant-item {
            background: var(--c-surface2); border-radius: 12px; padding: 16px; text-align: center;
        }
        .pollutant-label { font-size: 12px; color: var(--c-muted); margin-bottom: 4px; }
        .pollutant-value { font-size: 1.2rem; font-weight: 700; }
        
        /* Commodity styles */
        .commodities-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
        .commodity-item {
            background: var(--c-surface2); border-radius: 12px; padding: 20px;
            display: flex; align-items: center; gap: 16px;
        }
        .commodity-icon { font-size: 2.5rem; }
        .commodity-info { flex: 1; }
        .commodity-name { font-size: 14px; color: var(--c-muted); margin-bottom: 4px; }
        .commodity-price { font-size: 1.3rem; font-weight: 800; }
        .commodity-change { font-size: 12px; padding: 2px 8px; border-radius: 10px; display: inline-block; margin-top: 4px; }
        .commodity-change.up { background: color-mix(in srgb, var(--c-success) 15%, transparent); color: var(--c-success); }
        .commodity-change.down { background: color-mix(in srgb, var(--c-error) 15%, transparent); color: var(--c-error); }
        .commodity-change.stable { background: var(--c-surface2); color: var(--c-muted); }
        
        /* Sun times (colour-cleanup: was an orange gradient card; now neutral,
           sun icon already carries the "warmth" visually without a loud background) */
        .sun-times {
            display: flex; gap: 24px; justify-content: center; padding: 24px;
            background: var(--c-surface2); border: 1px solid var(--c-border);
            border-radius: 16px; color: var(--c-text);
        }
        .sun-time-item { text-align: center; }
        .sun-time-icon { font-size: 3rem; margin-bottom: 8px; }
        .sun-time-label { font-size: 14px; color: var(--c-text2); margin-bottom: 4px; }
        .sun-time-value { font-size: 2rem; font-weight: 800; }
        
        /* Grid layouts */
        .grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px; }
        
        @media (max-width: 768px) {
            .grid-2 { grid-template-columns: 1fr; }
            .weather-current { flex-direction: column; text-align: center; }
            .weather-temp { font-size: 3rem; }
            .aqi-value { font-size: 3rem; }
        }
        
        .auto-refresh {
            display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--c-muted);
        }
        .refresh-dot {
            width: 8px; height: 8px; border-radius: 50%; background: #10B981;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
    <script>
        // Auto-refresh every 5 minutes
        setTimeout(() => location.reload(), 300000);
    </script>
</head>
<body>
    <?php require SRC_DIR . '/layout/header.php'; ?>
    
    <main class="live-page">
        <div class="container mx-auto px-4">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold mb-2" style="color: var(--c-text);">
                    <span class="flex items-center gap-3">
                        <span>📡</span> Live Data
                    </span>
                </h1>
                <p class="text-gray-500">नेपालको लाइभ डेटा - भूकम्प, मौसम, चेतावनी, सरकारी सूचना</p>
                <div class="auto-refresh mt-2">
                    <span class="refresh-dot"></span>
                    <span>Auto-refreshes every 5 minutes • Last updated: <?= date('H:i:s') ?></span>
                </div>
            </div>
            
            <!-- Tabs -->
            <div class="live-tabs">
                <a href="?tab=dashboard" class="live-tab <?= $tab === 'dashboard' ? 'active' : '' ?>">Dashboard</a>
                <a href="?tab=earthquake" class="live-tab <?= $tab === 'earthquake' ? 'active' : '' ?>">भूकम्प</a>
                <a href="?tab=weather" class="live-tab <?= $tab === 'weather' ? 'active' : '' ?>">मौसम</a>
                <a href="?tab=air" class="live-tab <?= $tab === 'air' ? 'active' : '' ?>">वातावरण</a>
                <a href="?tab=alerts" class="live-tab <?= $tab === 'alerts' ? 'active' : '' ?>">चेतावनी</a>
                <a href="?tab=notices" class="live-tab <?= $tab === 'notices' ? 'active' : '' ?>">सूचना</a>
            </div>
            
            <?php if ($tab === 'dashboard' || $tab === ''): ?>
            <!-- Dashboard -->
            <div class="grid-2">
                <!-- Earthquakes Summary -->
                <div class="live-card">
                    <div class="live-card-header">
                        <div class="live-card-title">
                            <span>🌍</span> भूकम्प
                            <span class="live-badge alert">LIVE</span>
                        </div>
                        <a href="?tab=earthquake" class="text-sm text-blue-600 hover:underline">View All →</a>
                    </div>
                    <div class="earthquake-list">
                        <?php foreach (array_slice($dashboard['earthquakes'] ?? [], 0, 3) as $eq): ?>
                        <?php 
                            $mag_class = $eq['magnitude'] >= 5 ? 'strong' : ($eq['magnitude'] >= 4 ? 'moderate' : 'minor');
                        ?>
                        <div class="earthquake-item <?= $mag_class ?>">
                            <div class="earthquake-mag <?= $mag_class ?>"><?= $eq['magnitude'] ?></div>
                            <div class="earthquake-info">
                                <div class="earthquake-place"><?= h($eq['place']) ?></div>
                                <div class="earthquake-meta"><?= date('M j, H:i', strtotime($eq['time'])) ?></div>
                            </div>
                            <div class="earthquake-depth"><?= $eq['depth'] ?> km</div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($dashboard['earthquakes'])): ?>
                        <div class="text-center py-4 text-gray-500">केहि भूकम्प छैनन्</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Weather Summary -->
                <div class="live-card">
                    <div class="live-card-header">
                        <div class="live-card-title">
                            <span>🌤️</span> मौसम
                            <span class="live-badge">LIVE</span>
                        </div>
                        <a href="?tab=weather" class="text-sm text-blue-600 hover:underline">View All →</a>
                    </div>
                    <?php if (!empty($dashboard['weather'])): ?>
                    <div class="weather-current">
                        <div class="weather-icon"><?= $dashboard['weather']['current']['weather_code'] === 0 ? '☀️' : '☁️' ?></div>
                        <div class="weather-temp"><?= round($dashboard['weather']['current']['temperature']) ?>°C</div>
                        <div class="weather-details">
                            <div class="weather-city"><?= $dashboard['weather']['city'] ?></div>
                            <div class="weather-desc"><?= $dashboard['weather']['current']['weather_text'] ?></div>
                            <div class="weather-meta">
                                <span>💧 <?= $dashboard['weather']['current']['humidity'] ?>%</span>
                                <span>💨 <?= $dashboard['weather']['current']['wind_speed'] ?> km/h</span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Air Quality Summary -->
                <div class="live-card">
                    <div class="live-card-header">
                        <div class="live-card-title">
                            <span>🌬️</span> हावाको गुणस्तर
                            <span class="live-badge">LIVE</span>
                        </div>
                        <a href="?tab=air" class="text-sm text-blue-600 hover:underline">View All →</a>
                    </div>
                    <?php if (!empty($dashboard['air_quality'])):
                        $_aqi = $dashboard['air_quality']['aqi'];
                        $_aqi_color = $_aqi <= 50 ? 'var(--c-success)' : ($_aqi <= 100 ? 'var(--c-warning)' : 'var(--c-error)');
                    ?>
                    <div class="aqi-display">
                        <div class="aqi-value" style="color:<?= $_aqi_color ?>"><?= $_aqi ?></div>
                        <div class="aqi-info">
                            <div class="aqi-status"><?= $dashboard['air_quality']['status_np'] ?></div>
                            <div class="aqi-desc">AQI - US Standard</div>
                        </div>
                    </div>
                    <div class="pollutants-grid">
                        <div class="pollutant-item">
                            <div class="pollutant-label">PM2.5</div>
                            <div class="pollutant-value"><?= round($dashboard['air_quality']['pm25']) ?> µg/m³</div>
                        </div>
                        <div class="pollutant-item">
                            <div class="pollutant-label">PM10</div>
                            <div class="pollutant-value"><?= round($dashboard['air_quality']['pm10']) ?> µg/m³</div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Alerts Summary -->
                <div class="live-card">
                    <div class="live-card-header">
                        <div class="live-card-title">
                            <span>⚠️</span> चेतावनीहरू
                            <span class="live-badge <?= !empty($dashboard['alerts']) ? 'alert' : '' ?>"><?= count($dashboard['alerts'] ?? []) ?></span>
                        </div>
                        <a href="?tab=alerts" class="text-sm text-blue-600 hover:underline">View All →</a>
                    </div>
                    <div class="alert-list">
                        <?php foreach (array_slice($dashboard['alerts'] ?? [], 0, 3) as $alert): ?>
                        <div class="alert-item <?= $alert['severity'] ?? '' ?>">
                            <div class="alert-icon">⚠️</div>
                            <div class="alert-content">
                                <div class="alert-title"><?= h($alert['type']) ?></div>
                                <div class="alert-text"><?= h($alert['text']) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($dashboard['alerts'])): ?>
                        <div class="text-center py-4 text-gray-500">कुनै चेतावनी छैन</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sun Times & Commodities -->
            <div class="grid-2 mt-6">
                <?php if (!empty($dashboard['sun_times'])): ?>
                <div class="live-card">
                    <div class="live-card-header">
                        <div class="live-card-title">
                            <span>🌅</span> सूर्योदय र सूर्यास्त
                        </div>
                    </div>
                    <div class="sun-times">
                        <div class="sun-time-item">
                            <div class="sun-time-icon">🌅</div>
                            <div class="sun-time-label">सूर्योदय</div>
                            <div class="sun-time-value"><?= $dashboard['sun_times']['sunrise'] ?></div>
                        </div>
                        <div class="sun-time-item">
                            <div class="sun-time-icon">🌇</div>
                            <div class="sun-time-label">सूर्यास्त</div>
                            <div class="sun-time-value"><?= $dashboard['sun_times']['sunset'] ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="live-card">
                    <div class="live-card-header">
                        <div class="live-card-title">
                            <span>📊</span> वस्तु मूल्य
                        </div>
                    </div>
                    <div class="commodities-grid">
                        <?php foreach ($dashboard['commodities'] ?? [] as $item): ?>
                        <div class="commodity-item">
                            <div class="commodity-icon"><?= strpos($item['name'], 'सुन') !== false ? '🥇' : '⛽' ?></div>
                            <div class="commodity-info">
                                <div class="commodity-name"><?= h($item['name']) ?></div>
                                <div class="commodity-price"><?= h($item['price']) ?></div>
                                <span class="commodity-change <?= $item['trend'] ?>"><?= h($item['change']) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <?php elseif ($tab === 'earthquake'): ?>
            <!-- Earthquake Page -->
            <div class="live-card">
                <div class="live-card-header">
                    <div class="live-card-title">
                        <span>🌍</span> नेपालमा भूकम्प
                        <span class="live-badge alert">LIVE</span>
                    </div>
                    <span class="text-sm text-gray-500">पछिल्लो ७ दिन</span>
                </div>
                <div class="earthquake-list">
                    <?php foreach ($dashboard['earthquakes'] as $eq): ?>
                    <?php 
                        $mag_class = $eq['magnitude'] >= 5 ? 'strong' : ($eq['magnitude'] >= 4 ? 'moderate' : 'minor');
                    ?>
                    <div class="earthquake-item <?= $mag_class ?>">
                        <div class="earthquake-mag <?= $mag_class ?>">
                            <?= $eq['magnitude'] ?>
                            <div style="font-size: 10px; font-weight: normal;"><?= strtoupper($eq['mag_type']) ?></div>
                        </div>
                        <div class="earthquake-info">
                            <div class="earthquake-place"><?= h($eq['place']) ?></div>
                            <div class="earthquake-meta">
                                <?= date('Y-m-d H:i:s', strtotime($eq['time'])) ?>
                                • USGS
                            </div>
                        </div>
                        <div class="earthquake-depth">
                            <div style="font-size: 12px; color: var(--c-muted);">गहिराई</div>
                            <div style="font-weight: 700;"><?= $eq['depth'] ?> km</div>
                        </div>
                        <?php if ($eq['magnitude'] >= 4.5): ?>
                        <a href="<?= h($eq['url']) ?>" target="_blank" class="text-sm text-blue-600 hover:underline">USGS ↗</a>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($dashboard['earthquakes'])): ?>
                    <div class="text-center py-8 text-gray-500">
                        <p>पछिल्लो ७ दिनमा कुनै भूकम्प रेकर्ड भएको छैन।</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php elseif ($tab === 'weather'): ?>
            <!-- Weather Page -->
            <?php if (!empty($dashboard['weather'])): ?>
            <div class="live-card">
                <div class="live-card-header">
                    <div class="live-card-title">
                        <span>🌤️</span> <?= $dashboard['weather']['city'] ?> को मौसम
                        <span class="live-badge">LIVE</span>
                    </div>
                    <span class="text-sm text-gray-500">Updated: <?= $dashboard['weather']['updated_at'] ?></span>
                </div>
                
                <div class="weather-current">
                    <div class="weather-icon"><?= $dashboard['weather']['current']['weather_code'] === 0 ? '☀️' : '☁️' ?></div>
                    <div class="weather-temp"><?= round($dashboard['weather']['current']['temperature']) ?>°C</div>
                    <div class="weather-details">
                        <div class="weather-city"><?= $dashboard['weather']['city'] ?></div>
                        <div class="weather-desc"><?= $dashboard['weather']['current']['weather_text'] ?></div>
                        <div>Feels like: <?= round($dashboard['weather']['current']['feels_like']) ?>°C</div>
                    </div>
                </div>
                
                <div class="weather-extra">
                    <div class="weather-extra-item">
                        <div class="weather-extra-icon">💧</div>
                        <div class="weather-extra-label">आर्द्रता</div>
                        <div class="weather-extra-value"><?= $dashboard['weather']['current']['humidity'] ?>%</div>
                    </div>
                    <div class="weather-extra-item">
                        <div class="weather-extra-icon">💨</div>
                        <div class="weather-extra-label">हावाको गति</div>
                        <div class="weather-extra-value"><?= $dashboard['weather']['current']['wind_speed'] ?> km/h</div>
                    </div>
                    <div class="weather-extra-item">
                        <div class="weather-extra-icon">🧭</div>
                        <div class="weather-extra-label">हावाको दिशा</div>
                        <div class="weather-extra-value"><?= $dashboard['weather']['current']['wind_direction_text'] ?></div>
                    </div>
                    <div class="weather-extra-item">
                        <div class="weather-extra-icon">☁️</div>
                        <div class="weather-extra-label">बादल</div>
                        <div class="weather-extra-value"><?= $dashboard['weather']['current']['cloud_cover'] ?>%</div>
                    </div>
                    <div class="weather-extra-item">
                        <div class="weather-extra-icon">🌧️</div>
                        <div class="weather-extra-label">वर्षा</div>
                        <div class="weather-extra-value"><?= $dashboard['weather']['current']['precipitation'] ?> mm</div>
                    </div>
                </div>
            </div>
            
            <!-- 7-Day Forecast -->
            <div class="live-card">
                <div class="live-card-header">
                    <div class="live-card-title">
                        <span>📅</span> ७ दिनको पूर्वानुमान
                    </div>
                </div>
                <div class="forecast-list">
                    <?php foreach ($dashboard['weather']['daily'] as $day): ?>
                    <div class="forecast-item">
                        <div class="forecast-day"><?= date('D', strtotime($day['date'])) ?></div>
                        <div class="forecast-icon"><?= $day['weather_text'] === 'सफा' ? '☀️' : '🌧️' ?></div>
                        <div class="forecast-temps">
                            <span class="forecast-high"><?= round($day['temp_max']) ?>°</span>
                            <span class="forecast-low"><?= round($day['temp_min']) ?>°</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php elseif ($tab === 'air'): ?>
            <!-- Air Quality Page -->
            <?php if (!empty($dashboard['air_quality'])): ?>
            <div class="live-card">
                <div class="live-card-header">
                    <div class="live-card-title">
                        <span>🌬️</span> हावाको गुणस्तर
                        <span class="live-badge">LIVE</span>
                    </div>
                    <span class="text-sm text-gray-500">Updated: <?= $dashboard['air_quality']['updated_at'] ?></span>
                </div>
                
                <?php $_aqi2 = $dashboard['air_quality']['aqi']; $_aqi2_color = $_aqi2 <= 50 ? 'var(--c-success)' : ($_aqi2 <= 100 ? 'var(--c-warning)' : 'var(--c-error)'); ?>
                <div class="aqi-display">
                    <div class="aqi-value" style="color:<?= $_aqi2_color ?>"><?= $_aqi2 ?></div>
                    <div class="aqi-info">
                        <div class="aqi-status"><?= $dashboard['air_quality']['status_np'] ?></div>
                        <div class="aqi-desc"><?= $dashboard['air_quality']['status'] ?></div>
                    </div>
                </div>
                
                <h4 class="font-bold mb-4">प्रदूषक विवरण</h4>
                <div class="pollutants-grid">
                    <div class="pollutant-item">
                        <div class="pollutant-label">PM2.5</div>
                        <div class="pollutant-value"><?= round($dashboard['air_quality']['pm25'], 1) ?> µg/m³</div>
                    </div>
                    <div class="pollutant-item">
                        <div class="pollutant-label">PM10</div>
                        <div class="pollutant-value"><?= round($dashboard['air_quality']['pm10'], 1) ?> µg/m³</div>
                    </div>
                    <div class="pollutant-item">
                        <div class="pollutant-label">Ozone (O₃)</div>
                        <div class="pollutant-value"><?= round($dashboard['air_quality']['ozone'], 1) ?> µg/m³</div>
                    </div>
                    <div class="pollutant-item">
                        <div class="pollutant-label">NO₂</div>
                        <div class="pollutant-value"><?= round($dashboard['air_quality']['no2'], 1) ?> µg/m³</div>
                    </div>
                    <div class="pollutant-item">
                        <div class="pollutant-label">SO₂</div>
                        <div class="pollutant-value"><?= round($dashboard['air_quality']['so2'], 1) ?> µg/m³</div>
                    </div>
                    <div class="pollutant-item">
                        <div class="pollutant-label">CO</div>
                        <div class="pollutant-value"><?= round($dashboard['air_quality']['co'], 1) ?> µg/m³</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php elseif ($tab === 'alerts'): ?>
            <!-- Alerts Page -->
            <div class="live-card">
                <div class="live-card-header">
                    <div class="live-card-title">
                        <span>⚠️</span> चेतावनीहरू
                        <span class="live-badge <?= !empty($dashboard['alerts']) ? 'alert' : '' ?>"><?= count($dashboard['alerts'] ?? []) ?></span>
                    </div>
                </div>
                <div class="alert-list">
                    <?php foreach ($dashboard['alerts'] as $alert): ?>
                    <div class="alert-item <?= $alert['severity'] ?? '' ?>">
                        <div class="alert-icon">⚠️</div>
                        <div class="alert-content">
                            <div class="alert-title">
                                <?= h($alert['type']) ?>
                                <span class="alert-source"><?= h($alert['source']) ?></span>
                            </div>
                            <div class="alert-text"><?= h($alert['text']) ?></div>
                            <div class="alert-time"><?= h($alert['time']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($dashboard['alerts'])): ?>
                    <div class="text-center py-8 text-gray-500">
                        <p>⚠️ कुनै सक्रिय चेतावनी छैन</p>
                        <p class="text-sm mt-2">नेपालको वातावरण र मौसम सामान्य छ।</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php elseif ($tab === 'notices'): ?>
            <!-- Notices Page -->
            <div class="live-card">
                <div class="live-card-header">
                    <div class="live-card-title">
                        <span>📋</span> सरकारी सूचनाहरू
                    </div>
                </div>
                <div class="notice-list">
                    <?php foreach ($dashboard['notices'] as $notice): ?>
                    <div class="notice-item">
                        <div class="notice-icon">📢</div>
                        <div class="notice-content">
                            <div class="notice-title"><?= h($notice['title']) ?></div>
                            <div class="notice-desc"><?= h($notice['description']) ?></div>
                            <div class="notice-meta">
                                <span>📅 <?= $notice['date'] ?></span>
                                <span>•</span>
                                <span><?= h($notice['source']) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </main>
    
    <?php require SRC_DIR . '/layout/footer.php'; ?>
</body>
</html>
