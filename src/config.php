<?php
// ══════════════════════════════════════════════════════════
//  Global Configuration — Single Source of Truth
// ══════════════════════════════════════════════════════════

define('BASE_DIR', dirname(__DIR__));
define('SRC_DIR',  BASE_DIR . '/src');
define('DATA_DIR', BASE_DIR . '/data');
define('DB_PATH',  DATA_DIR . '/news.db');

// Load database credentials (edit db.config.php for MySQL)
$_db_cfg = BASE_DIR . '/db.config.php';
if (file_exists($_db_cfg)) require_once $_db_cfg;
if (!defined('DB_HOST')) define('DB_HOST', '');
if (!defined('DB_PORT')) define('DB_PORT', '3306');
if (!defined('DB_NAME')) define('DB_NAME', '');
if (!defined('DB_USER')) define('DB_USER', '');
if (!defined('DB_PASS')) define('DB_PASS', '');

// Site identity (overridable via DB settings)
define('DEFAULT_SITE_NAME',    'न्यूज पोर्टल');
define('DEFAULT_SITE_NAME_EN', 'Nepal News Portal');
define('DEFAULT_SITE_TAGLINE', 'नेपालको विश्वसनीय समाचार पोर्टल');
define('SITE_URL', '');

// Admin credentials (change via Settings panel after first login)
define('DEFAULT_ADMIN_USERNAME', 'admin');
define('DEFAULT_ADMIN_PASSWORD', 'admin123');
define('SESSION_NAME', 'np_admin');

// Pagination defaults
define('ARTICLES_PER_PAGE', 15);
define('SIDEBAR_LATEST',     8);
define('FEATURED_COUNT',     5);

// Nepali month names (BS calendar)
define('NP_MONTHS', [
    1=>'बैशाख', 2=>'जेठ',    3=>'असार',   4=>'श्रावण',
    5=>'भाद्र', 6=>'आश्विन', 7=>'कार्तिक', 8=>'मंसिर',
    9=>'पुस',  10=>'माघ',   11=>'फाल्गुन',12=>'चैत्र',
]);

// Default theme colors
define('DEFAULT_COLOR_PRIMARY', '#7F1D1D');
define('DEFAULT_COLOR_NAV',     '#7F1D1D');
define('DEFAULT_COLOR_ACCENT',  '#991B1B');
