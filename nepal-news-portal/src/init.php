<?php
// ══════════════════════════════════════════════════════════
//  Database Schema Initialisation
//  Runs on every request (cheap — uses IF NOT EXISTS).
//  Supports both MySQL/MariaDB and SQLite.
// ══════════════════════════════════════════════════════════

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

$db     = get_db();
$driver = db_driver(); // 'mysql' | 'sqlite'
$mysql  = $driver === 'mysql';

// ── Schema ─────────────────────────────────────────────────
if ($mysql) {
    // MySQL / MariaDB schema
    $db->exec("
    CREATE TABLE IF NOT EXISTS settings (
        id         INT NOT NULL AUTO_INCREMENT,
        `key`      VARCHAR(100) NOT NULL,
        value      TEXT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_settings_key (`key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS categories (
        id         INT NOT NULL AUTO_INCREMENT,
        name       VARCHAR(100) NOT NULL,
        name_np    VARCHAR(100),
        slug       VARCHAR(120) NOT NULL,
        color      VARCHAR(20)  DEFAULT '#7F1D1D',
        icon       VARCHAR(50)  DEFAULT '',
        sort_order INT          DEFAULT 0,
        created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_cat_slug (slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS authors (
        id         INT NOT NULL AUTO_INCREMENT,
        name       VARCHAR(120) NOT NULL,
        name_np    VARCHAR(120),
        slug       VARCHAR(140) NOT NULL,
        bio        TEXT,
        avatar_url VARCHAR(500) DEFAULT '',
        created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_auth_slug (slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS tags (
        id   INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(120) NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_tag_slug (slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS articles (
        id           INT NOT NULL AUTO_INCREMENT,
        title        VARCHAR(500) NOT NULL,
        title_np     VARCHAR(500),
        slug         VARCHAR(520) NOT NULL,
        summary      TEXT,
        summary_np   TEXT,
        content      LONGTEXT,
        content_np   LONGTEXT,
        image_url    VARCHAR(500) DEFAULT '',
        language     VARCHAR(5)   DEFAULT 'np',
        status       VARCHAR(20)  DEFAULT 'draft',
        featured     TINYINT(1)   DEFAULT 0,
        is_breaking  TINYINT(1)   DEFAULT 0,
        views        INT          DEFAULT 0,
        category_id  INT          NOT NULL,
        author_id    INT          NOT NULL,
        published_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
        scheduled_at DATETIME     DEFAULT NULL,
        created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP,
        updated_at   DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_art_slug (slug),
        KEY idx_art_status    (status),
        KEY idx_art_featured  (featured),
        KEY idx_art_breaking  (is_breaking),
        KEY idx_art_views     (views),
        KEY idx_art_cat       (category_id),
        KEY idx_art_author    (author_id),
        KEY idx_art_pub       (published_at),
        CONSTRAINT fk_art_cat    FOREIGN KEY (category_id) REFERENCES categories(id),
        CONSTRAINT fk_art_author FOREIGN KEY (author_id)   REFERENCES authors(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS article_tags (
        article_id INT NOT NULL,
        tag_id     INT NOT NULL,
        PRIMARY KEY (article_id, tag_id),
        CONSTRAINT fk_atag_art FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
        CONSTRAINT fk_atag_tag FOREIGN KEY (tag_id)     REFERENCES tags(id)     ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS advertisements (
        id          INT NOT NULL AUTO_INCREMENT,
        title       VARCHAR(200)  NOT NULL,
        type        VARCHAR(20)   DEFAULT 'image',
        image_url   VARCHAR(500)  DEFAULT '',
        code        LONGTEXT,
        link_url    VARCHAR(500)  DEFAULT '',
        position    VARCHAR(60)   NOT NULL,
        device      VARCHAR(20)   DEFAULT 'all',
        active      TINYINT(1)    DEFAULT 1,
        sort_order  INT           DEFAULT 1,
        clicks      INT           DEFAULT 0,
        impressions INT           DEFAULT 0,
        start_date  DATETIME      DEFAULT NULL,
        end_date    DATETIME      DEFAULT NULL,
        created_at  DATETIME      DEFAULT CURRENT_TIMESTAMP,
        updated_at  DATETIME      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_ads_pos (position, active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id         INT NOT NULL AUTO_INCREMENT,
        email      VARCHAR(254) NOT NULL,
        name       VARCHAR(200) DEFAULT '',
        confirmed  TINYINT(1)   DEFAULT 0,
        created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_sub_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS events (
        id                    INT NOT NULL AUTO_INCREMENT,
        title                 VARCHAR(500) NOT NULL,
        title_en              VARCHAR(500),
        slug                  VARCHAR(520) NOT NULL,
        description           LONGTEXT,
        description_en        LONGTEXT,
        cover_image           VARCHAR(500) DEFAULT '',
        venue                 VARCHAR(300),
        venue_en              VARCHAR(300),
        start_datetime        DATETIME,
        end_datetime          DATETIME,
        registration_open     TINYINT(1)   DEFAULT 1,
        registration_deadline DATETIME,
        capacity              INT,
        status                VARCHAR(30)  DEFAULT 'upcoming',
        show_in_menu          TINYINT(1)   DEFAULT 1,
        created_at            DATETIME     DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_evt_slug (slug),
        KEY idx_evt_status (status),
        KEY idx_evt_start  (start_datetime)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS event_registrations (
        id            INT NOT NULL AUTO_INCREMENT,
        event_id      INT NOT NULL,
        full_name     VARCHAR(200) NOT NULL,
        email         VARCHAR(254) NOT NULL,
        phone         VARCHAR(30)  DEFAULT '',
        organization  VARCHAR(200) DEFAULT '',
        message       TEXT,
        status        VARCHAR(30)  DEFAULT 'pending',
        registered_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_ereg_evt (event_id),
        CONSTRAINT fk_ereg_evt FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS event_media (
        id         INT NOT NULL AUTO_INCREMENT,
        event_id   INT NOT NULL,
        media_type VARCHAR(20)  DEFAULT 'photo',
        file_path  VARCHAR(500) DEFAULT '',
        video_url  VARCHAR(500) DEFAULT '',
        caption    VARCHAR(500) DEFAULT '',
        sort_order INT          DEFAULT 0,
        created_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_emedia_evt (event_id),
        CONSTRAINT fk_emedia_evt FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS static_pages (
        id             INT NOT NULL AUTO_INCREMENT,
        slug           VARCHAR(120) NOT NULL,
        title          VARCHAR(300) NOT NULL,
        title_en       VARCHAR(300),
        body           LONGTEXT,
        body_en        LONGTEXT,
        show_in_footer TINYINT(1)   DEFAULT 1,
        sort_order     INT          DEFAULT 0,
        created_at     DATETIME     DEFAULT CURRENT_TIMESTAMP,
        updated_at     DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_pg_slug (slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS epapers (
        id           INT NOT NULL AUTO_INCREMENT,
        edition_date DATE,
        headline     VARCHAR(300) DEFAULT '',
        pdf_path     VARCHAR(500) DEFAULT '',
        cover_image  VARCHAR(500) DEFAULT '',
        created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_ep_date (edition_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS article_views_log (
        id          INT NOT NULL AUTO_INCREMENT,
        article_id  INT NOT NULL,
        viewed_date DATE NOT NULL,
        view_count  INT DEFAULT 1,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_art_day (article_id, viewed_date),
        KEY idx_avl_date (viewed_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS market_widgets (
        id          INT NOT NULL AUTO_INCREMENT,
        widget_type VARCHAR(30) NOT NULL,
        label       VARCHAR(100) NOT NULL,
        value       VARCHAR(100) NOT NULL DEFAULT '',
        change_pct  DECIMAL(6,2) NULL,
        sort_order  INT DEFAULT 0,
        updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_mw_type (widget_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS redirects (
        id          INT NOT NULL AUTO_INCREMENT,
        old_path    VARCHAR(500) NOT NULL,
        new_path    VARCHAR(500) NOT NULL,
        status_code INT DEFAULT 301,
        hit_count   INT DEFAULT 0,
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_redir_old (old_path(250))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS search_logs (
        id           INT NOT NULL AUTO_INCREMENT,
        term         VARCHAR(200),
        result_count INT DEFAULT 0,
        searched_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_sl_term (term(100))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS rate_limits (
        id         INT NOT NULL AUTO_INCREMENT,
        action_key VARCHAR(200) NOT NULL,
        attempts   INT DEFAULT 0,
        expires_at DATETIME,
        PRIMARY KEY (id),
        UNIQUE KEY uq_rl_key (action_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Migrations for existing MySQL installs
    $migrations = [
        "ALTER TABLE advertisements ADD COLUMN device VARCHAR(20) DEFAULT 'all'",
        "ALTER TABLE advertisements ADD COLUMN start_date DATETIME DEFAULT NULL",
        "ALTER TABLE advertisements ADD COLUMN end_date DATETIME DEFAULT NULL",
        "ALTER TABLE advertisements ADD COLUMN impressions INT DEFAULT 0",
        "ALTER TABLE articles ADD COLUMN is_breaking TINYINT(1) NOT NULL DEFAULT 0",
        "ALTER TABLE articles ADD COLUMN seo_title VARCHAR(300) DEFAULT ''",
        "ALTER TABLE articles ADD COLUMN seo_desc VARCHAR(500) DEFAULT ''",
        "ALTER TABLE articles ADD COLUMN trending_score FLOAT DEFAULT 0",
        "ALTER TABLE articles ADD COLUMN type VARCHAR(20) DEFAULT 'news'",
        "ALTER TABLE articles ADD COLUMN image_credit VARCHAR(200) DEFAULT ''",
        "ALTER TABLE articles ADD COLUMN KEY idx_art_trending (trending_score DESC)",
        "ALTER TABLE article_translations ADD FULLTEXT INDEX ft_search (title, summary, body)",
        "ALTER TABLE authors ADD COLUMN IF NOT EXISTS twitter_url VARCHAR(300) DEFAULT ''",
        "ALTER TABLE authors ADD COLUMN IF NOT EXISTS facebook_url VARCHAR(300) DEFAULT ''",
        "ALTER TABLE authors ADD COLUMN IF NOT EXISTS linkedin_url VARCHAR(300) DEFAULT ''",
        "ALTER TABLE newsletter_subscribers ADD COLUMN IF NOT EXISTS token VARCHAR(64) DEFAULT ''",
        "ALTER TABLE categories ADD COLUMN IF NOT EXISTS description TEXT DEFAULT ''",
        "ALTER TABLE articles ADD COLUMN IF NOT EXISTS correction_note TEXT DEFAULT NULL",
    ];
    foreach ($migrations as $m) {
        try { $db->exec($m); } catch (Exception $e) { /* column already exists */ }
    }

} else {
    // SQLite schema
    $db->exec("
    CREATE TABLE IF NOT EXISTS settings (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        `key`      TEXT NOT NULL UNIQUE,
        value      TEXT,
        updated_at TEXT DEFAULT (CURRENT_TIMESTAMP)
    );
    CREATE TABLE IF NOT EXISTS categories (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        name       TEXT NOT NULL,
        name_np    TEXT,
        slug       TEXT NOT NULL UNIQUE,
        color      TEXT DEFAULT '#7F1D1D',
        icon       TEXT DEFAULT '',
        sort_order INTEGER DEFAULT 0,
        created_at TEXT DEFAULT (CURRENT_TIMESTAMP)
    );
    CREATE TABLE IF NOT EXISTS authors (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        name       TEXT NOT NULL,
        name_np    TEXT,
        slug       TEXT NOT NULL UNIQUE,
        bio        TEXT,
        avatar_url TEXT DEFAULT '',
        created_at TEXT DEFAULT (CURRENT_TIMESTAMP)
    );
    CREATE TABLE IF NOT EXISTS tags (
        id   INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE
    );
    CREATE TABLE IF NOT EXISTS articles (
        id           INTEGER PRIMARY KEY AUTOINCREMENT,
        title        TEXT NOT NULL,
        title_np     TEXT,
        slug         TEXT NOT NULL UNIQUE,
        summary      TEXT,
        summary_np   TEXT,
        content      TEXT,
        content_np   TEXT,
        image_url    TEXT DEFAULT '',
        language     TEXT DEFAULT 'np',
        status       TEXT DEFAULT 'draft',
        featured     INTEGER DEFAULT 0,
        is_breaking  INTEGER DEFAULT 0,
        views        INTEGER DEFAULT 0,
        category_id  INTEGER NOT NULL REFERENCES categories(id),
        author_id    INTEGER NOT NULL REFERENCES authors(id),
        published_at TEXT DEFAULT (CURRENT_TIMESTAMP),
        scheduled_at TEXT DEFAULT NULL,
        created_at   TEXT DEFAULT (CURRENT_TIMESTAMP),
        updated_at   TEXT DEFAULT (CURRENT_TIMESTAMP)
    );
    CREATE TABLE IF NOT EXISTS article_tags (
        article_id INTEGER NOT NULL REFERENCES articles(id) ON DELETE CASCADE,
        tag_id     INTEGER NOT NULL REFERENCES tags(id)     ON DELETE CASCADE,
        PRIMARY KEY (article_id, tag_id)
    );
    CREATE TABLE IF NOT EXISTS advertisements (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        title       TEXT NOT NULL,
        type        TEXT DEFAULT 'image',
        image_url   TEXT DEFAULT '',
        code        TEXT,
        link_url    TEXT DEFAULT '',
        position    TEXT NOT NULL,
        device      TEXT DEFAULT 'all',
        active      INTEGER DEFAULT 1,
        sort_order  INTEGER DEFAULT 1,
        clicks      INTEGER DEFAULT 0,
        impressions INTEGER DEFAULT 0,
        start_date  TEXT DEFAULT NULL,
        end_date    TEXT DEFAULT NULL,
        created_at  TEXT DEFAULT (CURRENT_TIMESTAMP),
        updated_at  TEXT DEFAULT (CURRENT_TIMESTAMP)
    );
    CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        email      TEXT NOT NULL UNIQUE,
        name       TEXT DEFAULT '',
        confirmed  INTEGER DEFAULT 0,
        created_at TEXT DEFAULT (CURRENT_TIMESTAMP)
    );
    CREATE TABLE IF NOT EXISTS events (
        id                    INTEGER PRIMARY KEY AUTOINCREMENT,
        title                 TEXT NOT NULL,
        title_en              TEXT,
        slug                  TEXT NOT NULL UNIQUE,
        description           TEXT,
        description_en        TEXT,
        cover_image           TEXT DEFAULT '',
        venue                 TEXT,
        venue_en              TEXT,
        start_datetime        TEXT,
        end_datetime          TEXT,
        registration_open     INTEGER DEFAULT 1,
        registration_deadline TEXT,
        capacity              INTEGER,
        status                TEXT DEFAULT 'upcoming',
        show_in_menu          INTEGER DEFAULT 1,
        created_at            TEXT DEFAULT (CURRENT_TIMESTAMP)
    );
    CREATE TABLE IF NOT EXISTS event_registrations (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        event_id      INTEGER NOT NULL REFERENCES events(id) ON DELETE CASCADE,
        full_name     TEXT NOT NULL,
        email         TEXT NOT NULL,
        phone         TEXT DEFAULT '',
        organization  TEXT DEFAULT '',
        message       TEXT,
        status        TEXT DEFAULT 'pending',
        registered_at TEXT DEFAULT (CURRENT_TIMESTAMP)
    );
    CREATE TABLE IF NOT EXISTS event_media (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        event_id   INTEGER NOT NULL REFERENCES events(id) ON DELETE CASCADE,
        media_type TEXT DEFAULT 'photo',
        file_path  TEXT DEFAULT '',
        video_url  TEXT DEFAULT '',
        caption    TEXT DEFAULT '',
        sort_order INTEGER DEFAULT 0,
        created_at TEXT DEFAULT (CURRENT_TIMESTAMP)
    );
    CREATE TABLE IF NOT EXISTS static_pages (
        id             INTEGER PRIMARY KEY AUTOINCREMENT,
        slug           TEXT NOT NULL UNIQUE,
        title          TEXT NOT NULL,
        title_en       TEXT,
        body           TEXT,
        body_en        TEXT,
        show_in_footer INTEGER DEFAULT 1,
        sort_order     INTEGER DEFAULT 0,
        created_at     TEXT DEFAULT (CURRENT_TIMESTAMP),
        updated_at     TEXT DEFAULT (CURRENT_TIMESTAMP)
    );

    CREATE TABLE IF NOT EXISTS epapers (
        id           INTEGER PRIMARY KEY AUTOINCREMENT,
        edition_date TEXT,
        headline     TEXT DEFAULT '',
        pdf_path     TEXT DEFAULT '',
        cover_image  TEXT DEFAULT '',
        created_at   TEXT DEFAULT (CURRENT_TIMESTAMP)
    );
    CREATE TABLE IF NOT EXISTS article_views_log (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        article_id  INTEGER NOT NULL,
        viewed_date TEXT NOT NULL,
        view_count  INTEGER DEFAULT 1,
        UNIQUE (article_id, viewed_date)
    );
    CREATE TABLE IF NOT EXISTS market_widgets (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        widget_type TEXT NOT NULL,
        label       TEXT NOT NULL,
        value       TEXT NOT NULL DEFAULT '',
        change_pct  REAL NULL,
        sort_order  INTEGER DEFAULT 0,
        updated_at  TEXT DEFAULT (CURRENT_TIMESTAMP)
    );
    CREATE TABLE IF NOT EXISTS redirects (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        old_path    TEXT NOT NULL UNIQUE,
        new_path    TEXT NOT NULL,
        status_code INTEGER DEFAULT 301,
        hit_count   INTEGER DEFAULT 0,
        created_at  TEXT DEFAULT (CURRENT_TIMESTAMP)
    );
    CREATE TABLE IF NOT EXISTS search_logs (
        id           INTEGER PRIMARY KEY AUTOINCREMENT,
        term         TEXT,
        result_count INTEGER DEFAULT 0,
        searched_at  TEXT DEFAULT (CURRENT_TIMESTAMP)
    );
    CREATE TABLE IF NOT EXISTS rate_limits (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        action_key TEXT NOT NULL UNIQUE,
        attempts   INTEGER DEFAULT 0,
        expires_at TEXT
    );

    CREATE INDEX IF NOT EXISTS idx_art_status   ON articles(status);
    CREATE INDEX IF NOT EXISTS idx_art_featured ON articles(featured);
    CREATE INDEX IF NOT EXISTS idx_art_breaking ON articles(is_breaking);
    CREATE INDEX IF NOT EXISTS idx_art_views    ON articles(views DESC);
    CREATE INDEX IF NOT EXISTS idx_art_cat      ON articles(category_id);
    CREATE INDEX IF NOT EXISTS idx_ads_pos      ON advertisements(position, active);
    CREATE INDEX IF NOT EXISTS idx_evt_status   ON events(status);
    CREATE INDEX IF NOT EXISTS idx_ereg_evt     ON event_registrations(event_id);
    CREATE INDEX IF NOT EXISTS idx_ep_date      ON epapers(edition_date);
    CREATE INDEX IF NOT EXISTS idx_mw_type      ON market_widgets(widget_type);
    ");

    // SQLite migrations for existing installs
    $migrations = [
        "ALTER TABLE advertisements ADD COLUMN device TEXT DEFAULT 'all'",
        "ALTER TABLE advertisements ADD COLUMN start_date TEXT DEFAULT NULL",
        "ALTER TABLE advertisements ADD COLUMN end_date TEXT DEFAULT NULL",
        "ALTER TABLE advertisements ADD COLUMN impressions INTEGER DEFAULT 0",
        "ALTER TABLE articles ADD COLUMN is_breaking INTEGER NOT NULL DEFAULT 0",
        "ALTER TABLE newsletter_subscribers ADD COLUMN name TEXT DEFAULT ''",
        "ALTER TABLE categories ADD COLUMN description TEXT DEFAULT ''",
        "ALTER TABLE articles ADD COLUMN correction_note TEXT DEFAULT NULL",
        "ALTER TABLE articles ADD COLUMN seo_title TEXT DEFAULT ''",
        "ALTER TABLE articles ADD COLUMN seo_desc TEXT DEFAULT ''",
        "ALTER TABLE articles ADD COLUMN trending_score REAL DEFAULT 0",
        "ALTER TABLE articles ADD COLUMN type TEXT DEFAULT 'news'",
        "ALTER TABLE articles ADD COLUMN image_credit TEXT DEFAULT ''",
        "ALTER TABLE authors ADD COLUMN twitter_url TEXT DEFAULT ''",
        "ALTER TABLE authors ADD COLUMN facebook_url TEXT DEFAULT ''",
        "ALTER TABLE authors ADD COLUMN linkedin_url TEXT DEFAULT ''",
        "ALTER TABLE newsletter_subscribers ADD COLUMN token TEXT DEFAULT ''",
    ];
    foreach ($migrations as $m) {
        try { $db->exec($m); } catch (Exception $e) { /* already exists */ }
    }
}

// ── Seed Settings ──────────────────────────────────────────
$defaults = [
    'site_name'          => 'न्यूज पोर्टल नेपाल',
    'site_name_en'       => 'Nepal News Portal',
    'site_tagline'       => 'नेपालको विश्वसनीय समाचार पोर्टल',
    'site_logo_url'      => '',
    'site_logo_text'     => 'न्यूज पोर्टल',
    'primary_color'      => '#7F1D1D',
    'nav_color'          => '#7F1D1D',
    'accent_color'       => '#991B1B',
    'ticker_label'       => 'ताजा खबर',
    'social_facebook'    => '',
    'social_twitter'     => '',
    'social_youtube'     => '',
    'social_instagram'   => '',
    'social_tiktok'      => '',
    'contact_email'      => '',
    'contact_phone'      => '',
    'contact_address'    => 'काठमाडौं, नेपाल',
    'footer_about'       => 'नेपालको विश्वसनीय र निष्पक्ष समाचार पोर्टल।',
    'footer_about_en'    => 'Nepal\'s trusted and impartial news portal.',
    'meta_keywords'      => 'नेपाल समाचार, ताजा खबर, nepal news, nepali news',
    'google_analytics'   => '',
    'youtube_channel'    => '',
    'youtube_embed'      => '',
    'admin_username'     => DEFAULT_ADMIN_USERNAME,
    'admin_password'     => password_hash(DEFAULT_ADMIN_PASSWORD, PASSWORD_DEFAULT),
    'default_lang'       => 'np',
    'registration_no'    => '',
    'founded_year'       => '',
    'copyright_text'     => '',
];
if ($mysql) {
    $stmt = $db->prepare("INSERT IGNORE INTO settings (`key`, value) VALUES (?, ?)");
} else {
    $stmt = $db->prepare("INSERT OR IGNORE INTO settings (`key`, value) VALUES (?, ?)");
}
foreach ($defaults as $k => $v) $stmt->execute([$k, $v]);

// ── Seed Categories ────────────────────────────────────────
$cat_count = (int)$db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
if ($cat_count === 0) {
    $cats = [
        ['अर्थतन्त्र',  'Economics',    'arthatantra',  '#1D4ED8', 'trending-up', 1],
        ['बैंकिङ',      'Banking',      'banking',      '#0891B2', 'landmark',    2],
        ['बिमा',        'Insurance',    'bima',         '#7C3AED', 'shield',      3],
        ['शेयर बजार',  'Share Market', 'share-bazar',  '#059669', 'bar-chart-2', 4],
        ['कर्पोरेट',   'Corporate',    'corporate',    '#D97706', 'briefcase',   5],
        ['राजनीति',    'Politics',     'rajniti',      '#B91C1C', 'building-2',  6],
        ['समाज',        'Society',      'samaj',        '#0369A1', 'users',       7],
        ['प्रविधि',     'Technology',   'technology',   '#6D28D9', 'cpu',         8],
        ['खेलकुद',      'Sports',       'sports',       '#15803D', 'trophy',      9],
        ['पर्यटन',      'Tourism',      'paryatan',     '#B45309', 'plane',       10],
        ['विश्व',       'World',        'world',        '#0E7490', 'globe-2',     11],
        ['विचार',       'Opinion',      'bichar',       '#6B7280', 'message-circle', 12],
    ];
    $s2 = $db->prepare("INSERT INTO categories (name, name_np, slug, color, icon, sort_order) VALUES (?,?,?,?,?,?)");
    foreach ($cats as $c) $s2->execute($c);
}

// ── Seed Authors ───────────────────────────────────────────
$auth_count = (int)$db->query("SELECT COUNT(*) FROM authors")->fetchColumn();
if ($auth_count === 0) {
    $authors = [
        ['संवाददाता',        'Staff Reporter',       'team',              ''],
        ['रमेश शर्मा',       'Ramesh Sharma',        'ramesh-sharma',     ''],
        ['सीता अधिकारी',     'Sita Adhikari',        'sita-adhikari',     ''],
        ['बिकाश थापा',       'Bikash Thapa',         'bikash-thapa',      ''],
        ['अनिता कर्माचार्य', 'Anita Karmacharya',    'anita-karmacharya', ''],
    ];
    $s3 = $db->prepare("INSERT INTO authors (name, name_np, slug, bio) VALUES (?,?,?,?)");
    foreach ($authors as $a) $s3->execute($a);
}

// ── Seed Articles ──────────────────────────────────────────
$art_count = (int)$db->query("SELECT COUNT(*) FROM articles")->fetchColumn();
if ($art_count === 0) {
    $cat_ids  = [];
    foreach ($db->query("SELECT id, slug FROM categories")->fetchAll(PDO::FETCH_ASSOC) as $r)
        $cat_ids[$r['slug']] = $r['id'];
    $auth_ids = [];
    foreach ($db->query("SELECT id, slug FROM authors")->fetchAll(PDO::FETCH_ASSOC) as $r)
        $auth_ids[$r['slug']] = $r['id'];

    $articles_seed = [
        ['नेपाल राष्ट्र बैंकले नयाँ मौद्रिक नीति जारी गर्यो', 'arthatantra', 'ramesh-sharma', 1, 125, '<p>नेपाल राष्ट्र बैंकले आर्थिक वर्षको नयाँ मौद्रिक नीति जारी गरेको छ। यस नीतिमा ब्याज दर घटाउने र तरलता बढाउने महत्वपूर्ण व्यवस्थाहरू समावेश गरिएका छन्।</p>', 'राष्ट्र बैंकले नयाँ मौद्रिक नीति जारी गर्यो।', 1, 2],
        ['नेप्से परिसूचक ३५ अंकले बढ्यो',                     'share-bazar', 'sita-adhikari', 1, 80,  '<p>नेपाल स्टक एक्सचेन्जमा आजको कारोबारमा उत्साहजनक वातावरण रह्यो। नेप्से परिसूचक ३५ अंकले बढेर बन्द भएको छ।</p>',                       'नेप्से ३५ अंकले बढ्यो।',            0, 4],
        ['पर्यटन वर्षमा १० लाख पर्यटक भित्र्याउने लक्ष्य',   'paryatan',   'team',          0, 60,  '<p>नेपाल सरकारले चालू पर्यटन वर्षमा १० लाख पर्यटक भित्र्याउने लक्ष्य राखेको छ।</p>',                                                     'पर्यटन वर्षमा १० लाख पर्यटक लक्ष्य।',0, 6],
        ['रेमिट्यान्स आप्रवाहमा उल्लेखनीय वृद्धि',          'arthatantra', 'bikash-thapa',  0, 50,  '<p>चालू आर्थिक वर्षको पहिलो ६ महिनामा रेमिट्यान्स आप्रवाहमा उल्लेखनीय वृद्धि भएको छ।</p>',                                             'रेमिट्यान्स ८ खर्ब रुपैयाँ नाघ्यो।',0, 8],
        ['प्रविधि क्षेत्रमा नेपाली स्टार्टअपको उदय',        'technology',  'anita-karmacharya', 0, 45, '<p>नेपालमा प्रविधि स्टार्टअपहरूको संख्या तीव्र गतिमा बढिरहेको छ।</p>',                                                                'नेपालमा प्रविधि स्टार्टअप बूम।',    0, 10],
        ['खेलकुद: नेपाली क्रिकेट टिमको सफलता',               'sports',      'team',          0, 35,  '<p>नेपाली क्रिकेट टिमले अन्तर्राष्ट्रिय टुर्नामेन्टमा उल्लेखनीय सफलता हासिल गरेको छ।</p>',                                            'नेपाली क्रिकेट टिमको शानदार प्रदर्शन।',0, 12],
    ];
    $s4 = $db->prepare(
        "INSERT INTO articles (title,slug,category_id,author_id,featured,views,content,summary,language,status,is_breaking,published_at)
         VALUES (?,?,?,?,?,?,?,?,'np','published',?,datetime('now',?))"
    );
    foreach ($articles_seed as $art) {
        [$title,$cat_slug,$auth_slug,$featured,$views,$content,$summary,$is_breaking,$hrs] = $art;
        // Build unique slug
        $slug_base = mb_strtolower(trim(preg_replace('/[\s_]+/','‑', preg_replace('/[^\w\s-]/u','',$title))));
        $slug_base = trim(preg_replace('/[\s_]+/', '-', preg_replace('/[^\w\s-]/u', '', mb_strtolower($title))), '-');
        $slug_base = substr($slug_base ?: 'article-'.time(), 0, 150);
        $final = $slug_base; $si = 1;
        while ($db->query("SELECT COUNT(*) FROM articles WHERE slug=".$db->quote($final))->fetchColumn() > 0)
            $final = $slug_base . '-' . $si++;
        $ago = 'datetime(\'now\',\'-'.$hrs.' hours\')';
        $db->exec("INSERT OR IGNORE INTO articles (title,slug,category_id,author_id,featured,views,content,summary,language,status,is_breaking,published_at)
                   VALUES ("
                   .$db->quote($title).","
                   .$db->quote($final).","
                   .((int)($cat_ids[$cat_slug]??1)).","
                   .((int)($auth_ids[$auth_slug]??1)).","
                   .(int)$featured.","
                   .(int)$views.","
                   .$db->quote($content).","
                   .$db->quote($summary).",'np','published',".(int)$is_breaking.",".$ago.")");
    }
}

// ── Seed Advertisements ────────────────────────────────────
$ad_count = (int)$db->query("SELECT COUNT(*) FROM advertisements")->fetchColumn();
if ($ad_count === 0) {
    $ignore = $mysql ? 'INSERT IGNORE' : 'INSERT OR IGNORE';
    $db->exec("$ignore INTO advertisements (title, type, image_url, link_url, position, active, sort_order, device) VALUES
        ('Header Banner',      'image', '', 'https://example.com', 'header-banner',  0, 1, 'all'),
        ('Sidebar Top Ad',     'image', '', 'https://example.com', 'sidebar-top',    0, 1, 'all'),
        ('Sidebar Bottom Ad',  'image', '', 'https://example.com', 'sidebar-bottom', 0, 2, 'all'),
        ('Article Middle Ad',  'image', '', 'https://example.com', 'article-middle', 0, 1, 'all'),
        ('In-Feed Ad',         'image', '', 'https://example.com', 'in-feed',        0, 1, 'all'),
        ('Footer Banner',      'image', '', 'https://example.com', 'footer-banner',  0, 1, 'all')
    ");
}

// ── Seed Static Pages ──────────────────────────────────────
$pg_count = (int)$db->query("SELECT COUNT(*) FROM static_pages")->fetchColumn();
if ($pg_count === 0) {
    $pages = [
        ['about',    'हाम्रो बारेमा',   'About Us',            '<p>न्यूज पोर्टल नेपाल — नेपालको विश्वसनीय समाचार पोर्टल हो।</p>', '<p>Nepal News Portal — Nepal\'s trusted news source.</p>', 1, 1],
        ['contact',  'सम्पर्क',         'Contact Us',          '<p>इमेल: info@newsportal.com.np</p>',                            '<p>Email: info@newsportal.com.np</p>',                       1, 2],
        ['privacy',  'गोपनीयता नीति',  'Privacy Policy',      '<p>हामी तपाईंको गोपनीयतालाई सम्मान गर्छौं।</p>',               '<p>We respect your privacy.</p>',                            1, 3],
        ['advertise','विज्ञापन',        'Advertise With Us',   '<p>विज्ञापनका लागि हामीसँग सम्पर्क गर्नुस्।</p>',             '<p>Contact us for advertising opportunities.</p>',           1, 4],
    ];
    $sp = $db->prepare("INSERT INTO static_pages (slug,title,title_en,body,body_en,show_in_footer,sort_order) VALUES (?,?,?,?,?,?,?)");
    foreach ($pages as $p) $sp->execute($p);
}

// ── Seed Market Widgets ────────────────────────────────────
$mw_count = (int)$db->query("SELECT COUNT(*) FROM market_widgets")->fetchColumn();
if ($mw_count === 0) {
    $ignore = $mysql ? 'INSERT IGNORE' : 'INSERT OR IGNORE';
    $db->exec("$ignore INTO market_widgets (widget_type, label, value, change_pct, sort_order) VALUES
        ('forex',    'USD / NPR', '134.25', 0.15,  1),
        ('forex',    'EUR / NPR', '145.60', -0.30, 2),
        ('forex',    'GBP / NPR', '169.80', 0.20,  3),
        ('forex',    'AUD / NPR', '86.50',  -0.10, 4),
        ('forex',    'INR / NPR', '1.60',   0.00,  5),
        ('gold',     'सुन (१० ग्राम)', '१,१२,५००', 0.80, 1),
        ('gold',     'चाँदी (किलो)',   '१,३२,०००', -0.50, 2),
        ('nepse',    'नेप्से',    '2,356.14', 1.25, 1),
        ('nepse',    'Turnover',  'रू. ५.२ अर्ब', 0.00, 2),
        ('fuel',     'पेट्रोल',  '१८१ प्रतिलिटर',  0.00, 1),
        ('fuel',     'डिजेल',   '१६९ प्रतिलिटर',  0.00, 2)
    ");
}

if (php_sapi_name() === 'cli') {
    echo "✅ Database initialised. Admin: admin / admin123\n";
}
