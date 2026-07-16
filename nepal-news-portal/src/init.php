<?php
/**
 * Database initializer — creates all tables and seeds data.
 * Auto-runs on first visit. Safe to re-run.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

$db = get_db();

// ── Schema ─────────────────────────────────────────────────
$db->exec("
CREATE TABLE IF NOT EXISTS settings (
  key        TEXT PRIMARY KEY,
  value      TEXT NOT NULL DEFAULT '',
  updated_at TEXT DEFAULT (datetime('now'))
);
CREATE TABLE IF NOT EXISTS categories (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  name       TEXT NOT NULL,
  name_np    TEXT,
  slug       TEXT NOT NULL UNIQUE,
  color      TEXT DEFAULT '#991B1B',
  icon       TEXT DEFAULT '',
  sort_order INTEGER DEFAULT 0,
  created_at TEXT DEFAULT (datetime('now'))
);
CREATE TABLE IF NOT EXISTS authors (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  name       TEXT NOT NULL,
  name_np    TEXT,
  slug       TEXT NOT NULL UNIQUE,
  bio        TEXT,
  avatar_url TEXT,
  created_at TEXT DEFAULT (datetime('now'))
);
CREATE TABLE IF NOT EXISTS tags (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  name       TEXT NOT NULL,
  slug       TEXT NOT NULL UNIQUE,
  created_at TEXT DEFAULT (datetime('now'))
);
CREATE TABLE IF NOT EXISTS articles (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  title        TEXT NOT NULL,
  title_np     TEXT,
  slug         TEXT NOT NULL UNIQUE,
  content      TEXT NOT NULL,
  content_np   TEXT,
  summary      TEXT NOT NULL,
  summary_np   TEXT,
  language     TEXT NOT NULL DEFAULT 'np',
  status       TEXT NOT NULL DEFAULT 'draft',
  featured     INTEGER NOT NULL DEFAULT 0,
  is_breaking  INTEGER NOT NULL DEFAULT 0,
  image_url    TEXT,
  views        INTEGER NOT NULL DEFAULT 0,
  category_id  INTEGER NOT NULL REFERENCES categories(id),
  author_id    INTEGER NOT NULL REFERENCES authors(id),
  published_at TEXT,
  created_at   TEXT DEFAULT (datetime('now')),
  updated_at   TEXT DEFAULT (datetime('now'))
);
CREATE TABLE IF NOT EXISTS article_tags (
  article_id INTEGER NOT NULL REFERENCES articles(id) ON DELETE CASCADE,
  tag_id     INTEGER NOT NULL REFERENCES tags(id) ON DELETE CASCADE,
  PRIMARY KEY (article_id, tag_id)
);
CREATE TABLE IF NOT EXISTS advertisements (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  title      TEXT NOT NULL,
  type       TEXT NOT NULL DEFAULT 'image',
  image_url  TEXT,
  code       TEXT,
  link_url   TEXT,
  position   TEXT NOT NULL DEFAULT 'sidebar-top',
  active     INTEGER NOT NULL DEFAULT 1,
  sort_order INTEGER DEFAULT 0,
  clicks     INTEGER DEFAULT 0,
  impressions INTEGER DEFAULT 0,
  created_at TEXT DEFAULT (datetime('now')),
  updated_at TEXT DEFAULT (datetime('now'))
);
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  email      TEXT NOT NULL UNIQUE,
  name       TEXT DEFAULT '',
  confirmed  INTEGER DEFAULT 0,
  created_at TEXT DEFAULT (datetime('now'))
);

-- Events module
CREATE TABLE IF NOT EXISTS events (
  id                    INTEGER PRIMARY KEY AUTOINCREMENT,
  title                 TEXT NOT NULL,
  title_en              TEXT,
  slug                  TEXT NOT NULL UNIQUE,
  description           TEXT,
  description_en        TEXT,
  cover_image           TEXT,
  venue                 TEXT,
  venue_en              TEXT,
  start_datetime        TEXT,
  end_datetime          TEXT,
  registration_open     INTEGER DEFAULT 1,
  registration_deadline TEXT,
  capacity              INTEGER,
  status                TEXT DEFAULT 'upcoming',
  show_in_menu          INTEGER DEFAULT 1,
  created_at            TEXT DEFAULT (datetime('now'))
);
CREATE TABLE IF NOT EXISTS event_registrations (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  event_id      INTEGER NOT NULL REFERENCES events(id) ON DELETE CASCADE,
  full_name     TEXT NOT NULL,
  email         TEXT NOT NULL,
  phone         TEXT,
  organization  TEXT,
  message       TEXT,
  status        TEXT DEFAULT 'pending',
  registered_at TEXT DEFAULT (datetime('now'))
);
CREATE TABLE IF NOT EXISTS event_media (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  event_id    INTEGER NOT NULL REFERENCES events(id) ON DELETE CASCADE,
  media_type  TEXT DEFAULT 'photo',
  file_path   TEXT,
  video_url   TEXT,
  caption     TEXT,
  sort_order  INTEGER DEFAULT 0,
  created_at  TEXT DEFAULT (datetime('now'))
);

-- Static pages (About, Contact, Privacy, etc.)
CREATE TABLE IF NOT EXISTS static_pages (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  slug       TEXT NOT NULL UNIQUE,
  title      TEXT NOT NULL,
  title_en   TEXT,
  body       TEXT,
  body_en    TEXT,
  show_in_footer INTEGER DEFAULT 1,
  sort_order INTEGER DEFAULT 0,
  created_at TEXT DEFAULT (datetime('now')),
  updated_at TEXT DEFAULT (datetime('now'))
);

-- Menu items
CREATE TABLE IF NOT EXISTS menu_items (
  id         INTEGER PRIMARY KEY AUTOINCREMENT,
  label      TEXT NOT NULL,
  label_en   TEXT,
  link_type  TEXT DEFAULT 'url',
  url        TEXT,
  category_id INTEGER,
  event_id   INTEGER,
  page_id    INTEGER,
  parent_id  INTEGER,
  location   TEXT DEFAULT 'header',
  sort_order INTEGER DEFAULT 0,
  open_new_tab INTEGER DEFAULT 0,
  status     INTEGER DEFAULT 1,
  created_at TEXT DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_articles_status   ON articles(status);
CREATE INDEX IF NOT EXISTS idx_articles_featured ON articles(featured);
CREATE INDEX IF NOT EXISTS idx_articles_breaking ON articles(is_breaking);
CREATE INDEX IF NOT EXISTS idx_articles_slug     ON articles(slug);
CREATE INDEX IF NOT EXISTS idx_articles_cat      ON articles(category_id);
CREATE INDEX IF NOT EXISTS idx_articles_views    ON articles(views DESC);
CREATE INDEX IF NOT EXISTS idx_articles_language ON articles(language);
CREATE INDEX IF NOT EXISTS idx_ads_position      ON advertisements(position, active);
CREATE INDEX IF NOT EXISTS idx_events_status     ON events(status);
CREATE INDEX IF NOT EXISTS idx_event_reg         ON event_registrations(event_id);
");

// Migrations: add columns if missing
$migrations = [
    "ALTER TABLE articles ADD COLUMN is_breaking INTEGER NOT NULL DEFAULT 0",
    "ALTER TABLE advertisements ADD COLUMN impressions INTEGER DEFAULT 0",
    "ALTER TABLE newsletter_subscribers ADD COLUMN name TEXT DEFAULT ''",
];
foreach ($migrations as $m) {
    try { $db->exec($m); } catch (Exception $e) { /* already exists */ }
}

// ── Seed settings ──────────────────────────────────────────
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
$stmt = $db->prepare("INSERT OR IGNORE INTO settings (key,value) VALUES (?,?)");
foreach ($defaults as $k => $v) $stmt->execute([$k, $v]);

// ── Seed categories ────────────────────────────────────────
$cat_count = (int)$db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
if ($cat_count === 0) {
    $cats = [
        ['अर्थतन्त्र',   'Economics',     'arthatantra',   '#1D4ED8', '💰', 1],
        ['बैंकिङ',       'Banking',       'banking',       '#0891B2', '🏦', 2],
        ['बिमा',         'Insurance',     'bima',          '#7C3AED', '🛡️', 3],
        ['शेयर बजार',   'Share Market',  'share-bazar',   '#059669', '📈', 4],
        ['कर्पोरेट',    'Corporate',     'corporate',     '#D97706', '🏢', 5],
        ['राजनीति',     'Politics',      'rajniti',       '#B91C1C', '🏛️', 6],
        ['समाज',         'Society',       'samaj',         '#0369A1', '👥', 7],
        ['प्रविधि',      'Technology',    'technology',    '#6D28D9', '💻', 8],
        ['खेलकुद',       'Sports',        'sports',        '#15803D', '⚽', 9],
        ['पर्यटन',       'Tourism',       'paryatan',      '#B45309', '✈️', 10],
        ['विश्व',        'World',         'world',         '#0E7490', '🌍', 11],
        ['विचार',        'Opinion',       'bichar',        '#6B7280', '💭', 12],
    ];
    $s2 = $db->prepare("INSERT INTO categories (name,name_np,slug,color,icon,sort_order) VALUES (?,?,?,?,?,?)");
    foreach ($cats as $c) $s2->execute([$c[0], $c[1], $c[2], $c[3], $c[4], $c[5]]);
}

// ── Seed authors ───────────────────────────────────────────
$auth_count = (int)$db->query("SELECT COUNT(*) FROM authors")->fetchColumn();
if ($auth_count === 0) {
    $authors = [
        ['संवाददाता','Staff Reporter','team',''],
        ['रमेश शर्मा','Ramesh Sharma','ramesh-sharma',''],
        ['सीता अधिकारी','Sita Adhikari','sita-adhikari',''],
        ['बिकाश थापा','Bikash Thapa','bikash-thapa',''],
        ['अनिता कर्माचार्य','Anita Karmacharya','anita-karmacharya',''],
    ];
    $s3 = $db->prepare("INSERT INTO authors (name,name_np,slug,bio) VALUES (?,?,?,?)");
    foreach ($authors as $a) $s3->execute($a);
}

// ── Seed articles ──────────────────────────────────────────
$art_count = (int)$db->query("SELECT COUNT(*) FROM articles")->fetchColumn();
if ($art_count === 0) {
    $cat_ids  = [];
    $cat_rows = $db->query("SELECT id,slug FROM categories")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cat_rows as $r) $cat_ids[$r['slug']] = $r['id'];
    $auth_ids = [];
    $auth_rows = $db->query("SELECT id,slug FROM authors")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($auth_rows as $r) $auth_ids[$r['slug']] = $r['id'];

    $articles_seed = [
        ['नेपाल राष्ट्र बैंकले नयाँ मौद्रिक नीति जारी गर्यो','arthatantra','ramesh-sharma',1,125,'नेपाल राष्ट्र बैंकले आर्थिक वर्षको नयाँ मौद्रिक नीति जारी गरेको छ। यस नीतिमा ब्याज दर घटाउने र तरलता बढाउने महत्वपूर्ण व्यवस्थाहरू समावेश गरिएका छन्।','राष्ट्र बैंकले नयाँ मौद्रिक नीति जारी गर्यो।',1,2],
        ['नेप्से परिसूचक ३५ अंकले बढ्यो','share-bazar','sita-adhikari',1,80,'नेपाल स्टक एक्सचेन्जमा आजको कारोबारमा उत्साहजनक वातावरण रह्यो। नेप्से परिसूचक ३५ अंकले बढेर बन्द भएको छ।','नेप्से ३५ अंकले बढ्यो।',0,4],
        ['पर्यटन वर्षमा १० लाख पर्यटक भित्र्याउने लक्ष्य','paryatan','team',0,60,'नेपाल सरकारले चालू पर्यटन वर्षमा १० लाख पर्यटक भित्र्याउने लक्ष्य राखेको छ।','पर्यटन वर्षमा १० लाख पर्यटक लक्ष्य।',0,6],
        ['रेमिट्यान्स आप्रवाहमा उल्लेखनीय वृद्धि','arthatantra','bikash-thapa',0,50,'चालू आर्थिक वर्षको पहिलो ६ महिनामा रेमिट्यान्स आप्रवाहमा उल्लेखनीय वृद्धि भएको छ। राष्ट्र बैंकका अनुसार यस अवधिमा रेमिट्यान्स ८ खर्ब रुपैयाँ नाघेको छ।','रेमिट्यान्स ८ खर्ब रुपैयाँ नाघ्यो।',0,8],
        ['प्रविधि क्षेत्रमा नेपाली स्टार्टअपको उदय','technology','anita-karmacharya',0,45,'नेपालमा प्रविधि स्टार्टअपहरूको संख्या तीव्र गतिमा बढिरहेको छ। गत वर्ष मात्र ५०० भन्दा बढी नयाँ प्रविधि कम्पनी दर्ता भएका छन्।','नेपालमा प्रविधि स्टार्टअप बूम।',0,10],
        ['खेलकुद: नेपाली क्रिकेट टिमको सफलता','sports','team',0,35,'नेपाली क्रिकेट टिमले अन्तर्राष्ट्रिय टुर्नामेन्टमा उल्लेखनीय सफलता हासिल गरेको छ।','नेपाली क्रिकेट टिमको शानदार प्रदर्शन।',0,12],
    ];
    $stmt2 = $db->prepare(
        "INSERT OR IGNORE INTO articles
         (title,category_id,author_id,featured,views,content,summary,language,status,is_breaking,published_at)
         VALUES (?,?,?,?,?,?,?,'np','published',?,datetime('now',?))");
    foreach ($articles_seed as $art) {
        [$title,$cat_slug,$auth_slug,$featured,$views,$content,$summary] = $art;
        $is_breaking = $art[7] ?? 0;
        $hours_ago   = '-' . ($art[8] ?? 2) . ' hours';
        $stmt2->execute([
            $title,
            $cat_ids[$cat_slug]   ?? 1,
            $auth_ids[$auth_slug] ?? 1,
            $featured, $views, $content, $summary, $is_breaking, $hours_ago
        ]);
    }
}

// ── Seed advertisements ────────────────────────────────────
$ad_count = (int)$db->query("SELECT COUNT(*) FROM advertisements")->fetchColumn();
if ($ad_count === 0) {
    $db->exec("INSERT INTO advertisements (title,type,image_url,link_url,position,active,sort_order) VALUES
               ('Header Banner','image','','https://example.com','header-banner',0,1),
               ('Sidebar Ad 1','image','','https://example.com','sidebar-top',0,1),
               ('Sidebar Ad 2','image','','https://example.com','sidebar-bottom',0,2),
               ('Article Middle Ad','image','','https://example.com','article-middle',0,1),
               ('In-feed Ad','image','','https://example.com','in-feed',0,1)");
}

// ── Seed static pages ──────────────────────────────────────
$pg_count = (int)$db->query("SELECT COUNT(*) FROM static_pages")->fetchColumn();
if ($pg_count === 0) {
    $pages = [
        ['about',   'हाम्रो बारेमा', 'About Us',
         '<p>न्यूज पोर्टल नेपाल — नेपालको विश्वसनीय र निष्पक्ष समाचार पोर्टल हो। हामी पाठकहरूलाई सबैभन्दा ताजा र प्रामाणिक समाचार प्रदान गर्न प्रतिबद्ध छौं।</p>',
         '<p>Nepal News Portal is committed to delivering the most accurate and latest news to our readers.</p>',
         1, 1],
        ['contact', 'सम्पर्क', 'Contact Us',
         '<p>हामीसँग सम्पर्क गर्नुस्:<br>इमेल: info@newsportal.com.np<br>फोन: +९७७-१-XXXXXXX<br>ठेगाना: काठमाडौं, नेपाल</p>',
         '<p>Contact us: Email: info@newsportal.com.np | Phone: +977-1-XXXXXXX | Address: Kathmandu, Nepal</p>',
         1, 2],
        ['privacy', 'गोपनीयता नीति', 'Privacy Policy',
         '<p>हामी तपाईंको व्यक्तिगत जानकारीको सुरक्षालाई उच्च प्राथमिकता दिन्छौं। यो पोर्टलमा प्रदान गरिएको कुनै पनि जानकारी तेस्रो पक्षसँग साझा गरिने छैन।</p>',
         '<p>We prioritize the protection of your personal information. Any information provided on this portal will not be shared with third parties.</p>',
         1, 3],
        ['advertise', 'विज्ञापन', 'Advertise With Us',
         '<p>हाम्रो पोर्टलमा विज्ञापन दिन इच्छुक हुनुहुन्छ भने हामीसँग सम्पर्क गर्नुस्।</p>',
         '<p>If you are interested in advertising with us, please get in touch.</p>',
         1, 4],
    ];
    $sp = $db->prepare("INSERT INTO static_pages (slug,title,title_en,body,body_en,show_in_footer,sort_order) VALUES (?,?,?,?,?,?,?)");
    foreach ($pages as $p) $sp->execute($p);
}

if (php_sapi_name() === 'cli') {
    echo "✅ Database initialized.\n";
    echo "   Admin: admin / admin123\n";
}
