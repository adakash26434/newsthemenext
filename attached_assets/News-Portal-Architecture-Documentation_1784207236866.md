# न्युज पोर्टल — Project Architecture & Documentation
### (Reference: karobardaily.com जस्तै तर best-practice, scalable र single-source architecture मा)

**Tech Stack:** PHP (Vanilla / OOP, PDO) · MySQL/MariaDB (production DB) · SQLite (dev/test + lightweight cache) · Tailwind CSS · Alpine.js

---

## 0. यो Documentation किन यसरी बनाइयो

Tapaile चाहनुभएको मुख्य ३ वटा कुरा यो doc ले cover गर्छ:

1. **Single Source of Truth architecture** — logo, theme colors, menu, ads, footer info, language text जुनसुकै एउटा ठाउँमा change गर्दा site भरि automatically update होस्, ठाउँ-ठाउँमा hardcode नहोस्।
2. **Full Admin-managed CMS** — news, category, tags, media, ads, logo, site info, menu, event (pre-registration + gallery), language, theme — सबै admin panel बाट नै control होस्।
3. **Fast + Responsive** — mobile/desktop दुबैमा राम्रो, caching, optimized asset, lazy loading सहित।

Design reference: **karobardaily.com** को structure (top bar with ePaper/Login/TV, mega category menu, breaking news ticker, ad banner zones, "बुलेटिन" ticker, category-wise blocks, most-read, YouTube shorts block, footer with company/legal info) लाई आधार मानेर, अझ राम्रो (bilingual + dark/light + events module) बनाइएको छ।

---

## 1. Design Principles (जुन सधैं पालना गर्ने)

| Principle | Meaning |
|---|---|
| **DRY (Don't Repeat Yourself)** | कुनै पनि UI टुक्रा (header, footer, ad slot, news card) एउटै PHP partial/component बाट मात्र render हुने। २ ठाउँमा उही HTML कहिल्यै copy-paste नगर्ने। |
| **Single Source of Truth** | Site settings, theme colors, language text, menu — सबै centralized (DB + एउटा config loader) बाट आउने, हरेक page ले त्यहीं बाट पढ्ने। |
| **Separation of Concerns** | Data (Model) / Logic (Controller-like service) / Display (View/partials) छुट्टाछुट्टै। |
| **Admin-first** | Developer ले हरेक पटक code नछोइकन Admin ले content, design token, menu, ads change गर्न सकोस्। |
| **Performance by default** | Cache, indexed queries, optimized images, minimal JS — सुरुदेखि नै। |
| **Progressive enhancement** | Alpine.js ले UI interactivity थप्ने मात्र, core content PHP-rendered HTML बाटै आउने (SEO + speed को लागि)। |

---

## 2. High-Level Architecture

```
                         ┌───────────────────────────┐
                         │        Browser (User)      │
                         │  Desktop / Mobile / Tablet  │
                         └──────────────┬──────────────┘
                                        │ HTTPS
                         ┌──────────────▼──────────────┐
                         │     Nginx/Apache + PHP-FPM   │
                         └──────────────┬──────────────┘
                                        │
              ┌─────────────────────────┼─────────────────────────┐
              │                         │                         │
    ┌─────────▼─────────┐   ┌───────────▼───────────┐   ┌─────────▼─────────┐
    │   Public Front-end │   │      Admin Panel        │   │   REST/AJAX API    │
    │  (news, events,    │   │  (CRUD everything)      │   │ (Alpine.js fetch,  │
    │   category, search)│   │  /admin/*               │   │  search, load-more)│
    └─────────┬─────────┘   └───────────┬───────────┘   └─────────┬─────────┘
              │                         │                         │
              └────────────┬────────────┴────────────┬────────────┘
                            │                         │
                  ┌─────────▼─────────┐     ┌─────────▼─────────┐
                  │   Core / Shared    │     │   File Storage     │
                  │  (Config, Auth,    │     │ /storage/uploads/   │
                  │  DB layer, i18n,   │     │ (images, videos,    │
                  │  Cache, Helpers)   │     │  pdf epaper)        │
                  └─────────┬─────────┘     └────────────────────┘
                            │
                  ┌─────────▼─────────┐
                  │   MySQL/MariaDB    │  ← production, single source of truth for data
                  │  (SQLite: local     │
                  │   dev + query cache)│
                  └────────────────────┘
```

**नियम:** Front-end र Admin दुबैले उही **Core layer** (DB class, Settings class, Language class, Auth class) प्रयोग गर्छन् — यसैले duplicate logic हुँदैन।

---

## 3. Folder Structure (Single Source Enforced)

```
news-portal/
│
├── public/                     ← Web root (only this exposed to internet)
│   ├── index.php               ← Front controller (all public routes यहीं बाट)
│   ├── admin/
│   │   └── index.php           ← Admin front controller (all /admin/* routes)
│   ├── assets/
│   │   ├── css/app.css         ← Compiled Tailwind (single file, purged)
│   │   ├── js/app.js           ← Alpine.js + site JS (single bundle)
│   │   └── uploads/ → symlink to /storage/uploads
│   └── .htaccess / nginx rewrite rules
│
├── app/
│   ├── Core/
│   │   ├── Database.php        ← PDO wrapper (MySQL prod / SQLite dev, same interface)
│   │   ├── Router.php
│   │   ├── Settings.php        ← Loads site_settings table → cached array (SINGLE SOURCE)
│   │   ├── Lang.php            ← Loads active language strings (EN/NE)
│   │   ├── Auth.php            ← Admin login/session/role-check
│   │   ├── Cache.php           ← File/SQLite based cache
│   │   ├── ImageService.php    ← Resize/webp/thumbnail on upload
│   │   └── View.php            ← Renders layout + partials
│   │
│   ├── Models/                 ← One class per table (Article, Category, Event, Ad, Menu, User...)
│   ├── Controllers/
│   │   ├── Site/               ← Home, Category, Article, Search, Event, EPaper, Static pages
│   │   └── Admin/               ← Dashboard, News, Category, Media, Ads, Menu, Event, Settings, Users
│   └── Services/                ← EventRegistrationService, AdRotationService, SeoService, etc.
│
├── resources/
│   ├── views/
│   │   ├── layout/
│   │   │   ├── master.php      ← ⭐ ONE master layout for whole site (site) 
│   │   │   ├── header.php      ← ⭐ ONE header (logo, top bar, menu) 
│   │   │   ├── footer.php      ← ⭐ ONE footer (about, social, legal)
│   │   │   └── admin_master.php
│   │   ├── partials/
│   │   │   ├── news-card.php   ← ⭐ ONE news card component (used everywhere: home, category, search, related)
│   │   │   ├── ad-slot.php     ← ⭐ ONE ad component, called with zone code e.g. <?= adSlot('home_top') ?>
│   │   │   ├── ticker.php      ← Breaking news ticker
│   │   │   └── event-card.php
│   │   ├── site/               ← home.php, category.php, single-article.php, event pages...
│   │   └── admin/               ← admin CRUD screens
│   │
│   ├── lang/
│   │   ├── en.json              ← UI text (buttons, labels) — English
│   │   └── ne.json              ← UI text — Nepali
│   │
│   └── theme/
│       └── theme-tokens.css     ← ⭐ ONE CSS-variables file (colors/fonts for light+dark) — SINGLE THEME SOURCE
│
├── database/
│   ├── migrations/               ← versioned .sql files (numbered, never edit old ones)
│   └── seeders/
│
├── storage/
│   ├── uploads/                  ← images, event photos/videos, epaper PDFs
│   ├── cache/                    ← page cache, sqlite cache db
│   └── logs/
│
├── config/
│   ├── config.php                ← DB credentials, base URL, env (dev/prod)
│   └── tailwind.config.js
│
└── composer.json
```

**Fastest possible impact of this structure:** Logo replace गर्नुपर्‍यो भने → Admin → Site Settings बाट image upload गर्ने मात्र, किनकि `header.php` र `footer.php` ले settings बाटै logo path तान्छन्, कतै pun hardcoded हुँदैन।

---

## 4. Database Design (MySQL/MariaDB — production)

> SQLite को प्रयोग सेक्सन ९ मा छुट्टै व्याख्या गरिएको छ। Schema दुबै engine मा उस्तै चल्ने गरी standard SQL (no MySQL-only syntax जस्तै `ENUM` भन्दा `VARCHAR` + check गर्न सकिन्छ, तर ENUM पनि MariaDB/SQLite दुबैमा ठिकै chाल्छ) design गरिएको छ।

### 4.1 Core content tables

```sql
-- Languages (extensible: भोलि थप भाषा थप्न मिल्ने)
CREATE TABLE languages (
  code        VARCHAR(5) PRIMARY KEY,   -- 'en', 'ne'
  name        VARCHAR(50),
  is_default  TINYINT(1) DEFAULT 0,
  is_active   TINYINT(1) DEFAULT 1
);

-- Categories (multi-level, e.g. अर्थतन्त्र > बैंकिङ)
CREATE TABLE categories (
  id          INT PRIMARY KEY AUTO_INCREMENT,
  parent_id   INT NULL,
  slug        VARCHAR(100) UNIQUE,
  icon        VARCHAR(100) NULL,
  sort_order  INT DEFAULT 0,
  status      TINYINT(1) DEFAULT 1,
  FOREIGN KEY (parent_id) REFERENCES categories(id)
);

CREATE TABLE category_translations (
  category_id INT,
  lang_code   VARCHAR(5),
  name        VARCHAR(150),
  PRIMARY KEY (category_id, lang_code),
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Authors / Admin users
CREATE TABLE users (
  id           INT PRIMARY KEY AUTO_INCREMENT,
  name         VARCHAR(150),
  email        VARCHAR(150) UNIQUE,
  password     VARCHAR(255),
  avatar       VARCHAR(255) NULL,
  role         VARCHAR(20) DEFAULT 'reporter', -- super_admin, editor, reporter, ad_manager
  status       TINYINT(1) DEFAULT 1,
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Articles (language-independent core row)
CREATE TABLE articles (
  id             INT PRIMARY KEY AUTO_INCREMENT,
  category_id    INT,
  author_id      INT,
  slug           VARCHAR(220) UNIQUE,
  featured_image VARCHAR(255),
  type           VARCHAR(20) DEFAULT 'news',   -- news, video, photo-gallery
  is_breaking    TINYINT(1) DEFAULT 0,
  is_featured    TINYINT(1) DEFAULT 0,
  status         VARCHAR(20) DEFAULT 'draft',  -- draft, published, scheduled
  published_at   DATETIME NULL,
  views          INT DEFAULT 0,
  created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME NULL,
  FOREIGN KEY (category_id) REFERENCES categories(id),
  FOREIGN KEY (author_id) REFERENCES users(id),
  INDEX idx_status_pub (status, published_at),
  INDEX idx_category (category_id)
);

-- Per-language article content (title/body separate per language = true bilingual)
CREATE TABLE article_translations (
  article_id   INT,
  lang_code    VARCHAR(5),
  title        VARCHAR(300),
  summary      VARCHAR(500),
  body         LONGTEXT,
  seo_title    VARCHAR(300),
  seo_desc     VARCHAR(500),
  PRIMARY KEY (article_id, lang_code),
  FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE
);

CREATE TABLE tags (
  id INT PRIMARY KEY AUTO_INCREMENT,
  slug VARCHAR(100) UNIQUE
);
CREATE TABLE tag_translations (
  tag_id INT, lang_code VARCHAR(5), name VARCHAR(100),
  PRIMARY KEY (tag_id, lang_code)
);
CREATE TABLE article_tag (
  article_id INT, tag_id INT,
  PRIMARY KEY (article_id, tag_id)
);

-- Media library (single place all uploaded files register — reusable everywhere)
CREATE TABLE media (
  id           INT PRIMARY KEY AUTO_INCREMENT,
  file_path    VARCHAR(255),
  file_type    VARCHAR(20),      -- image, video, pdf
  alt_text     VARCHAR(255),
  uploaded_by  INT,
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### 4.2 Site settings, theme, menu, ads (the "single source" tables)

```sql
-- ⭐ ONE table drives logo, site name, contact, social links, SEO defaults
CREATE TABLE site_settings (
  setting_key   VARCHAR(100) PRIMARY KEY,
  setting_value TEXT
);
-- rows example: site_logo, site_name_en, site_name_ne, favicon, contact_email,
-- contact_phone, address, facebook_url, youtube_url, twitter_url,
-- default_og_image, google_analytics_id, footer_about_en, footer_about_ne...

-- ⭐ Theme tokens editable from Admin (colors, fonts) → written into theme-tokens.css cache
CREATE TABLE theme_settings (
  token_key   VARCHAR(50) PRIMARY KEY,  -- primary-color, accent-color, font-heading...
  light_value VARCHAR(20),
  dark_value  VARCHAR(20)
);

-- Menu builder (drag/drop order in admin), supports dropdown (Event menu etc.)
CREATE TABLE menus (
  id          INT PRIMARY KEY AUTO_INCREMENT,
  parent_id   INT NULL,
  location    VARCHAR(20) DEFAULT 'header',  -- header, footer
  link_type   VARCHAR(20) DEFAULT 'category', -- category, custom_url, event, static_page
  ref_id      INT NULL,       -- category_id / event id / page id if applicable
  custom_url  VARCHAR(255) NULL,
  sort_order  INT DEFAULT 0,
  status      TINYINT(1) DEFAULT 1,
  FOREIGN KEY (parent_id) REFERENCES menus(id)
);
CREATE TABLE menu_translations (
  menu_id INT, lang_code VARCHAR(5), label VARCHAR(100),
  PRIMARY KEY (menu_id, lang_code)
);

-- Advertisement zones + ads (banner sizes जस्तो karobardaily मा देखेको: header, in-feed, sidebar, popup)
CREATE TABLE ad_zones (
  id     INT PRIMARY KEY AUTO_INCREMENT,
  code   VARCHAR(50) UNIQUE,   -- 'home_top_970x100', 'sidebar_300x250', 'in_article'
  name   VARCHAR(100),
  width  INT, height INT
);
CREATE TABLE ads (
  id           INT PRIMARY KEY AUTO_INCREMENT,
  zone_id      INT,
  title        VARCHAR(150),
  image_path   VARCHAR(255) NULL,
  script_code  TEXT NULL,        -- for AdSense/JS tag ads
  target_url   VARCHAR(255) NULL,
  start_date   DATE,
  end_date     DATE,
  impressions  INT DEFAULT 0,
  clicks       INT DEFAULT 0,
  status       TINYINT(1) DEFAULT 1,
  FOREIGN KEY (zone_id) REFERENCES ad_zones(id)
);
```

### 4.3 Events module (pre-registration + event-wise photo/video)

```sql
CREATE TABLE events (
  id                  INT PRIMARY KEY AUTO_INCREMENT,
  slug                VARCHAR(200) UNIQUE,
  cover_image         VARCHAR(255),
  venue               VARCHAR(255),
  start_datetime      DATETIME,
  end_datetime        DATETIME,
  registration_open   TINYINT(1) DEFAULT 1,
  registration_deadline DATETIME NULL,
  capacity            INT NULL,
  status              VARCHAR(20) DEFAULT 'upcoming', -- upcoming, ongoing, completed, cancelled
  created_by          INT,
  created_at          DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE event_translations (
  event_id INT, lang_code VARCHAR(5), title VARCHAR(300), description LONGTEXT,
  PRIMARY KEY (event_id, lang_code)
);

-- Pre-registration form submissions
CREATE TABLE event_registrations (
  id            INT PRIMARY KEY AUTO_INCREMENT,
  event_id      INT,
  full_name     VARCHAR(150),
  email         VARCHAR(150),
  phone         VARCHAR(20),
  organization  VARCHAR(150) NULL,
  extra_fields  TEXT NULL,           -- JSON string: custom form fields (flexible, no schema change needed)
  status        VARCHAR(20) DEFAULT 'pending', -- pending, confirmed, cancelled, attended
  registered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (event_id) REFERENCES events(id),
  INDEX idx_event (event_id)
);

-- Event-wise photo/video gallery
CREATE TABLE event_media (
  id          INT PRIMARY KEY AUTO_INCREMENT,
  event_id    INT,
  media_type  VARCHAR(10),          -- photo, video
  file_path   VARCHAR(255) NULL,     -- for uploaded photo
  video_url   VARCHAR(255) NULL,     -- for YouTube/Vimeo embed
  caption     VARCHAR(255) NULL,
  sort_order  INT DEFAULT 0,
  FOREIGN KEY (event_id) REFERENCES events(id)
);
```

### 4.4 Supporting tables

```sql
CREATE TABLE static_pages (        -- About, Contact, Privacy, Unicode help...
  id INT PRIMARY KEY AUTO_INCREMENT, slug VARCHAR(100) UNIQUE
);
CREATE TABLE static_page_translations (
  page_id INT, lang_code VARCHAR(5), title VARCHAR(200), body LONGTEXT,
  PRIMARY KEY (page_id, lang_code)
);

CREATE TABLE epapers (              -- Daily ePaper PDF (jasto karobardaily मा छ)
  id INT PRIMARY KEY AUTO_INCREMENT,
  edition_date DATE,
  cover_image VARCHAR(255),
  pdf_path VARCHAR(255)
);

CREATE TABLE article_views_log (    -- "most read" feature को लागि हल्का log
  id INT PRIMARY KEY AUTO_INCREMENT,
  article_id INT,
  viewed_date DATE,
  view_count INT DEFAULT 1,
  UNIQUE KEY uniq_day (article_id, viewed_date)
);

CREATE TABLE newsletter_subscribers (
  id INT PRIMARY KEY AUTO_INCREMENT, email VARCHAR(150) UNIQUE, subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

---

## 5. Single Source of Truth — कसरी काम गर्छ (technical flow)

### 5.1 Global Theme (colors, logo, dark/light)

1. `theme_settings` table मा admin ले primary color, accent color, font जस्ता tokens edit गर्छ।
2. Save गर्दा एउटा **`Settings::rebuildThemeCache()`** function ले `theme-tokens.css` फाइल regenerate गर्छ (CSS custom properties मात्र):

```css
/* resources/theme/theme-tokens.css — AUTO-GENERATED, DO NOT HAND-EDIT */
:root {
  --color-primary: #C8102E;
  --color-accent:  #0B3D91;
  --font-heading: 'Noto Sans Devanagari', sans-serif;
}
.dark {
  --color-primary: #E8394F;
  --color-accent:  #4F83CC;
}
```

3. `master.php` layout ले यो एउटै file हरेक page मा include गर्छ — Tailwind config मा `colors.primary = 'var(--color-primary)'` गरेर tie गरिन्छ। एउटा color admin बाट बदल्दा **site भरि, dark र light दुबै mode मा** एकैचोटि apply हुन्छ।

### 5.2 Dark / Light mode (Tailwind + Alpine.js)

```js
// tailwind.config.js
module.exports = { darkMode: 'class', theme: { extend: { colors: {
  primary: 'var(--color-primary)', accent: 'var(--color-accent)'
}}}}
```

```html
<html x-data="{ dark: localStorage.getItem('theme') === 'dark' }"
      x-init="$watch('dark', v => { localStorage.setItem('theme', v ? 'dark':'light');
               document.documentElement.classList.toggle('dark', v) })"
      :class="{ 'dark': dark }">
  <button @click="dark = !dark">🌓</button>
```

Site load हुँदा नै `<script>` inline (head मा, सबैभन्दा माथि) ले साइट flash-नगरी (no FOUC) correct theme apply गर्छ।

### 5.3 Language (English / Nepali)

- URL structure: `/ne/news/slug` (default, no prefix पनि हुन सक्छ) र `/en/news/slug`।
- हरेक request मा `Lang::init($localeFromUrl)` ले:
  - UI labels `lang/ne.json` वा `lang/en.json` बाट लोड (menu button, "थप पढ्नुहोस्", "Read more" जस्ता static text)।
  - Content (article title/body/category name/menu label) ती-ती `*_translations` table बाट, `WHERE lang_code = :current` गरेर।
- Translation नभएको भाषामा fallback: default language content देखिने (empty page कहिल्यै नआउने)।
- Admin मा हरेक news/category/event edit गर्दा **Tab: नेपाली | English** — दुबै भाषाको content एउटै form मा।

### 5.4 Header/Footer/Logo/Ads — everywhere single component

```php
<?php // header.php — every page includes this, nowhere duplicated
$logo = Settings::get('site_logo');
$menu = Menu::getTree('header', Lang::current());
?>
<header>
  <img src="<?= $logo ?>" alt="<?= Settings::get('site_name_' . Lang::current()) ?>">
  <?= renderMenu($menu) ?>
  <?= adSlot('header_strip') ?>   <!-- one function, reused for every ad zone -->
</header>
```

`adSlot('zone_code')` function ले `ads` table बाट active (date range भित्र) ad तानेर देखाउँछ — नयाँ ad थप्न/हटाउन/म्याद सकिन दिन admin बाट मात्र, code नछोइकन।

---

## 6. Admin Panel — Full Module List

| Module | के गर्न मिल्छ |
|---|---|
| **Dashboard** | Today's traffic, latest registrations, pending comments, quick stats |
| **News Manager** | Add/Edit/Delete news, bilingual (NE/EN tabs), category, tags, featured image, breaking-news toggle, schedule publish, draft/published status, SEO fields |
| **Category Manager** | Add/reorder/nest categories, bilingual name, icon |
| **Media Library** | Central upload/browse/reuse images & videos (auto thumbnail + webp) |
| **Menu Builder** | Drag-drop menu items, dropdown submenu support (used for **Event** menu with Upcoming/Past/Register-now sub-items), assign header/footer |
| **Advertisement Manager** | Zones (header/sidebar/in-article/popup), upload banner or paste script tag, schedule start/end date, view impressions & clicks |
| **Event Manager** | Create event (bilingual title/desc, venue, date/time, cover image), toggle registration on/off, set capacity/deadline |
| **Event Registrations** | View/export (CSV) registrants per event, mark attended, email/SMS export list |
| **Event Gallery** | Upload event-wise photos, add YouTube/Vimeo video links, reorder |
| **ePaper Manager** | Upload daily PDF + cover, auto BS-date tagging |
| **Site Settings** | Logo, favicon, site name (NE/EN), contact info, social links, default SEO/OG image, Google Analytics ID |
| **Theme Settings** | Primary/accent color picker (light & dark variant), heading font choice |
| **Language Manager** | Add/activate languages, edit UI-text JSON via UI (no manual file edit needed) |
| **Users & Roles** | Super Admin, Editor, Reporter (own posts only), Ad Manager (ads only) — role-based permission |
| **Static Pages** | About, Contact, Privacy, Unicode-help — bilingual rich text editor |
| **Comments (optional)** | Approve/spam/delete reader comments |

**Role-based access:** हरेक admin route मा `Auth::can('manage_ads')` जस्तो check — role table मा permission list, single place बाट control।

---

## 7. Public Front-End Structure

```
/                          → Home (ticker + featured + category blocks + most-read + shorts)
/news/category/{slug}      → Category listing (paginated)
/news/{slug}                → Single article (related news, share buttons, font-size toggle, print)
/news/tag/{slug}            → Tag listing
/event                      → Events landing (Upcoming / Past tabs)
/event/{slug}               → Single event (details + Register button + gallery + past photos/videos)
/event/{slug}/register      → Pre-registration form (AJAX submit via Alpine, no full reload)
/search?q=                  → Search results
/e-paper                    → ePaper archive, BS-date wise
/about, /contact             → Static pages
/{en|ne}/...                 → language-prefixed versions of all above
```

**Homepage block order (karobardaily reference अनुसार, अझ optimized):**
1. Top utility bar (Login, ePaper, TV, Language switch, Dark/Light toggle)
2. Logo + main dropdown menu (incl. **Event ▾** → Upcoming Events / Past Events / Register)
3. Top ad banner
4. Breaking news ticker
5. Hero: Lead story + secondary stories grid
6. Ad strip
7. "बुलेटिन" scrolling short-headline ticker
8. Category-wise blocks (each: 1 featured + list, "थप हेर्नुहोस्" link)
9. Most-read / Trending sidebar widget
10. Upcoming Events widget (card carousel)
11. Video/Shorts embed block
12. Footer (About, contact, social, useful links, legal/registration number — jasto karobardaily मा छ)

---

## 8. Event Module — Detailed Flow

**Pre-registration:**
1. Admin creates Event → toggles "Registration Open".
2. Public event page मा dynamic form (fields configurable: name, email, phone + optional extra fields stored as JSON — schema नबदली नयाँ field थप्न मिल्ने)।
3. Alpine.js ले form validate + AJAX submit गर्छ (`/api/event/{id}/register`) → success message, no page reload।
4. Confirmation email (PHP `mail()` वा SMTP जस्तै PHPMailer) auto पठाउने।
5. Admin → Event Registrations मा real-time list, CSV export, capacity भरिएपछि auto "Registration Closed"।

**Event-wise photo/video:**
1. Event भइसकेपछि Admin → Event Gallery मा जुन event हो उसैको लागि छुट्टै photo upload / video-link add।
2. Public single-event page मा gallery lightbox (Alpine.js modal) मा देखिन्छ — event अनुसार filter भएर।

---

## 9. Role of SQLite in this Stack

| Environment | DB Engine | किन |
|---|---|---|
| Production (live server) | **MySQL/MariaDB** | Concurrent writes, बढी traffic, replication/backup सजिलो |
| Local development | **SQLite** | Setup-free — developer ले MySQL install नगरी काम गर्न सक्ने, same schema |
| Lightweight cache layer (optional, both env) | **SQLite** | Rendered page/query result cache राख्न (Redis नभएको hosting मा हल्का caching को लागि उपयुक्त) |

यो सम्भव बनाउन **PDO** प्रयोग गर्ने (driver फेरे मात्र पुग्छ, code नबदली):
```php
// config/config.php
$dsn = ($env === 'production')
  ? "mysql:host=localhost;dbname=news_portal;charset=utf8mb4"
  : "sqlite:" . __DIR__ . "/../storage/dev.sqlite";
```

---

## 10. Nepali-specific Best Practices (Research-based)

- **Bikram Sambat (BS) date**: प्रत्येक article/event मा AD date मात्र नभई BS date पनि देखाउने (karobardaily जस्तै "२०८३ असार ३२ गते")। यसको लागि PHP composer library `ernilambar/nepali-date` (AD↔BS convert + Devanagari numeral format) प्रयोग गर्न सकिन्छ, वा आफ्नै lookup-table based helper class बनाउन सकिन्छ (BS महिनाको दिन संख्या स्थिर नभएकोले hardcoded algorithm सम्भव छैन — lookup table नै standard approach हो)।
- **Font**: Unicode Devanagari (Noto Sans Devanagari / Mukta) — Preeti/legacy font हैन, ताकि copy-paste, SEO, search सबै ठीक होस्। (Site मा "Unicode" help page राख्ने चलन पनि यसैले हो, जुन reference site मा पनि छ।)
- **Numerals**: Setting मा टगल — Devanagari अंक (१,२,३) वा English अंक (1,2,3) देखाउने छनौट।
- **Breaking news ticker + बुलेटिन**: छिटो अपडेट हुने headline-only strip, cache नगरी हल्का AJAX polling (३०-६० सेकेन्ड) ले refresh।
- **Author attribution**: "कारोबार संवाददाता" जस्तो generic byline पनि सम्भव हुने गरी author name optional/nullable राख्नु राम्रो।
- **Social + TV/YouTube integration**: Footer र homepage मा YouTube channel embed block (shorts) — यो reference site मा भएकै pattern, धेरै Nepali news portal मा common practice हो।

---

## 11. Performance Strategy

1. **Caching**: Homepage/category pages को rendered HTML `storage/cache/` (file वा SQLite) मा ६०-१२० सेकेन्ड TTL सहित राख्ने — नयाँ news publish हुनासाथ cache invalidate।
2. **Images**: Upload हुनासाथ auto-resize (thumbnail, medium, full) + WebP conversion; `loading="lazy"` सबै below-fold image मा।
3. **CSS/JS**: Tailwind JIT + purge (एउटै compiled `app.css`), Alpine.js CDN वा bundled — HTTP request minimum राख्ने।
4. **DB**: सबै listing query मा proper index (माथि schema मा `idx_status_pub`, `idx_category` जस्ता), pagination (`LIMIT`/`OFFSET` वा keyset pagination ठूलो data भएपछि)।
5. **CDN**: Static assets (images/css/js) CDN वा server-level gzip/Brotli + long cache headers।
6. **Server**: PHP OPcache enable, PHP-FPM tuning, MariaDB query cache/indexes review।
7. **Mobile-first Tailwind**: सबै component मobile breakpoint बाट design सुरु गर्ने (`sm: md: lg:`), ad banners मा responsive size (mobile मा ठूलो desktop banner नराख्ने)।

---

## 12. SEO & Discoverability

- `schema.org/NewsArticle` structured data हरेक article page मा।
- Auto `sitemap.xml` generation (cron/daily), `robots.txt`।
- OG/Twitter meta tags — Settings बाट default image, article बाट override।
- Canonical URL, hreflang tags (`en`/`ne`) language pages बीच।
- Clean slug-based URLs (`/news/{slug}` जस्तै, ID मात्र होइन)।

---

## 13. Security Checklist

- सबै DB query **PDO prepared statements** (कहिल्यै raw string concat नगर्ने)।
- Admin login: password hashing (`password_hash`), brute-force rate limiting, session regenerate।
- CSRF token सबै admin form मा।
- File upload: extension + MIME whitelist, resize गरेर मात्र store, executable file कहिल्यै allow नगर्ने।
- Role-based access control हरेक admin controller मा check।
- `.env`/`config.php` वेबरूट बाहिर वा `.htaccess` deny।

---

## 14. Development Roadmap (Suggested Phases)

| Phase | Scope |
|---|---|
| **1. Foundation** | DB schema, Core classes (DB, Settings, Lang, Auth, Cache), master layout, theme-token system, dark/light toggle |
| **2. News CMS** | Category/Article CRUD (admin), homepage + category + single-article (public), search |
| **3. Media/Ads/Menu** | Media library, Ad zones + rotation, Menu builder (incl. Event dropdown) |
| **4. Events** | Event CRUD, pre-registration form + AJAX + export, event gallery |
| **5. i18n + ePaper** | Full EN/NE toggle, ePaper module, BS date integration |
| **6. Polish** | Performance pass (caching, image pipeline), SEO, security audit, mobile QA |
| **7. Launch** | Production deploy, backups, monitoring |

---

## 15. Naming & Coding Conventions (single-source discipline)

- Table names: snake_case, plural (`articles`, `event_registrations`)।
- PHP classes: PascalCase (`ArticleController`, `EventService`)।
- Every reusable UI block **must** live in `resources/views/partials/` — कतै inline duplicate HTML लेख्ने होइन।
- Every color/spacing token **must** reference a CSS variable — hardcoded hex/px सीधै component मा नलेख्ने।
- Every user-facing static text **must** go through `Lang::get('key')` — कतै hardcoded Nepali/English string सीधै view मा नलेख्ने (translation ब्रेक हुन्छ)।

---

## 16. Summary

यो architecture ले:
- ✅ **एउटा change, सबैतिर update** (Settings/theme-tokens/menu/lang tables + shared partials बाट)
- ✅ **Admin बाट सम्पूर्ण control** (news, media, ads, logo, menu, event, language, theme — code नछोइकन)
- ✅ **Event module पूर्ण** (pre-registration + event-wise gallery)
- ✅ **Bilingual (NE/EN) + Dark/Light** built-in
- ✅ **Fast** (caching, optimized images, indexed queries, minimal JS)
- ✅ **karobardaily.com जस्तै proven layout pattern, तर अझ व्यवस्थित र maintainable कोडबेससहित**

लाई सम्भव बनाउँछ।
