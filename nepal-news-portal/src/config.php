<?php
// ══════════════════════════════════════════════════════════
//  Global Configuration — Single Source of Truth
// ══════════════════════════════════════════════════════════

define('BASE_DIR',  dirname(__DIR__));
define('SRC_DIR',   BASE_DIR . '/src');
define('DATA_DIR',  BASE_DIR . '/data');
define('DB_PATH',   DATA_DIR . '/news.db');

// Default site identity (can be overridden via DB settings)
define('DEFAULT_SITE_NAME',    'न्यूज पोर्टल');
define('DEFAULT_SITE_NAME_EN', 'Nepal News Portal');
define('DEFAULT_SITE_TAGLINE', 'नेपालको विश्वसनीय समाचार पोर्टल');
define('SITE_URL',              '');   // empty = relative URLs

// Default admin credentials (overridable via DB settings)
define('DEFAULT_ADMIN_USERNAME', 'admin');
define('DEFAULT_ADMIN_PASSWORD', 'admin123');
define('SESSION_NAME',           'np_admin');

// Pagination
define('ARTICLES_PER_PAGE', 15);
define('SIDEBAR_LATEST',     8);
define('FEATURED_COUNT',     5);

// Nepali month names (BS)
define('NP_MONTHS', [
    1=>'बैशाख',2=>'जेठ',3=>'असार',4=>'श्रावण',
    5=>'भाद्र',6=>'आश्विन',7=>'कार्तिक',8=>'मंसिर',
    9=>'पुस',10=>'माघ',11=>'फाल्गुन',12=>'चैत्र',
]);

// Default theme colors
define('DEFAULT_COLOR_PRIMARY',   '#7F1D1D');
define('DEFAULT_COLOR_NAV',       '#7F1D1D');
define('DEFAULT_COLOR_ACCENT',    '#991B1B');
