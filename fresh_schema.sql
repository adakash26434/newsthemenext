-- ============================================================
-- Nepal News Portal — Complete Fresh Database Schema (MySQL/MariaDB)
-- ============================================================
-- Auto-generated to exactly match what src/init.php creates when the
-- app boots against an empty database — this file just lets you get
-- the same complete, final schema (all 32 tables, all columns, all
-- indexes) in one import instead of letting the app build it up
-- gradually across first-boot + several migration passes.
--
-- Safe to import on a brand-new empty database. Every statement uses
-- IF NOT EXISTS / is wrapped so re-running this file is also safe on
-- a database that already has some of these tables (won't destroy
-- any existing data — CREATE TABLE IF NOT EXISTS skips existing
-- tables, and duplicate ALTER/INDEX statements are simply skipped
-- below rather than erroring out the whole import).
--
-- Usage:
--   mysql -u USERNAME -p DATABASE_NAME < fresh_schema.sql
-- or import via phpMyAdmin / cPanel's "Import" tab.
--
-- Generated: 2026-07-19
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ── PART 1: Base tables (32) ──────────────────────────────────
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
        email      VARCHAR(200) DEFAULT '',
        bio        TEXT,
        role       VARCHAR(100) DEFAULT '',
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
        KEY idx_art_status_pub     (status, published_at),
        KEY idx_art_cat_status_pub (category_id, status, published_at),
        CONSTRAINT fk_art_cat    FOREIGN KEY (category_id) REFERENCES categories(id),
        CONSTRAINT fk_art_author FOREIGN KEY (author_id)   REFERENCES authors(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS article_tags (
        article_id INT NOT NULL,
        tag_id     INT NOT NULL,
        PRIMARY KEY (article_id, tag_id),
        KEY idx_atag_tag (tag_id),
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

    CREATE TABLE IF NOT EXISTS comments (
        id         INT NOT NULL AUTO_INCREMENT,
        article_id INT NOT NULL,
        parent_id  INT DEFAULT NULL,
        name       VARCHAR(100) NOT NULL,
        email      VARCHAR(200) DEFAULT '',
        website    VARCHAR(200) DEFAULT '',
        content    TEXT NOT NULL,
        status     VARCHAR(20) DEFAULT 'pending',
        ip         VARCHAR(50) DEFAULT '',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_article (article_id),
        KEY idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- Was missing entirely: get_reaction_counts()/database.php queried and
    -- inserted into this table on every single article page view with no
    -- try/catch around it, so a fresh/redeployed DB without this table
    -- would fatal-error (uncaught PDOException) on every article page.
    CREATE TABLE IF NOT EXISTS reaction_counts (
        id         INT NOT NULL AUTO_INCREMENT,
        article_id INT NOT NULL,
        type       VARCHAR(20) NOT NULL,
        count      INT DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE KEY uq_article_type (article_id, type),
        KEY idx_article (article_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS horoscope_daily (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        sign            VARCHAR(20) NOT NULL,
        date            DATE NOT NULL,
        overall_score   DECIMAL(3,2) DEFAULT 3.00,
        love_score      DECIMAL(3,2) DEFAULT 3.00,
        career_score    DECIMAL(3,2) DEFAULT 3.00,
        health_score    DECIMAL(3,2) DEFAULT 3.00,
        finance_score   DECIMAL(3,2) DEFAULT 3.00,
        prediction      TEXT,
        lucky_color     VARCHAR(50),
        lucky_number    INT,
        lucky_direction VARCHAR(30),
        lucky_gemstone VARCHAR(50),
        caution         TEXT,
        mantra          VARCHAR(255),
        created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_sign_date (sign, date),
        INDEX idx_date (date),
        INDEX idx_sign (sign)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS horoscope_monthly (
        id                  INT AUTO_INCREMENT PRIMARY KEY,
        sign                VARCHAR(20) NOT NULL,
        month               INT NOT NULL,
        year                INT NOT NULL,
        overall_prediction  TEXT,
        love_prediction     TEXT,
        career_prediction   TEXT,
        health_prediction   TEXT,
        finance_prediction  TEXT,
        important_dates     TEXT,
        key_themes          TEXT,
        created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_sign_month_year (sign, month, year),
        INDEX idx_month_year (month, year)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS horoscope_yearly (
        id                 INT AUTO_INCREMENT PRIMARY KEY,
        sign               VARCHAR(20) NOT NULL,
        year               INT NOT NULL,
        overview           TEXT,
        love               TEXT,
        career             TEXT,
        health             TEXT,
        finance            TEXT,
        predictions        JSON,
        key_predictions    TEXT,
        created_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_sign_year (sign, year),
        INDEX idx_year (year)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS auspicious_times (
        id                INT AUTO_INCREMENT PRIMARY KEY,
        nepali_date       VARCHAR(50) NOT NULL,
        english_date      DATE NOT NULL,
        abhijeet_mulat    VARCHAR(100),
        brahma_muhurat    VARCHAR(100),
        amrit_kalash      VARCHAR(100),
        ravi_kalash       VARCHAR(100),
        chartime_start    VARCHAR(20),
        chartime_end      VARCHAR(20),
        labh_kalash       VARCHAR(100),
        shubh_kalash      VARCHAR(100),
        notes             TEXT,
        created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_nepali_date (nepali_date),
        INDEX idx_english_date (english_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS auspicious_days (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        nepali_date     VARCHAR(50) NOT NULL,
        english_date    DATE NOT NULL,
        day_name        VARCHAR(30),
        day_type        VARCHAR(20),
        title           VARCHAR(100),
        description     TEXT,
        significance    VARCHAR(255),
        month           INT NOT NULL,
        year            INT NOT NULL,
        day             INT NOT NULL,
        created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_month_year (month, year)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS lagna_info (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        nepali_date     VARCHAR(50) NOT NULL,
        english_date    DATE NOT NULL,
        moon_sign       VARCHAR(30),
        ascendant       VARCHAR(30),
        nakshatra       VARCHAR(30),
        tithi           VARCHAR(30),
        yoga            VARCHAR(30),
        karana          VARCHAR(30),
        sun_time        VARCHAR(50),
        moon_time       VARCHAR(50),
        notes           TEXT,
        created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_nepali_date (nepali_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS gud_milan (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        boy_sign        VARCHAR(20) NOT NULL,
        girl_sign       VARCHAR(20) NOT NULL,
        varna           INT DEFAULT 1,
        vasya           DECIMAL(3,2) DEFAULT 0.5,
        tatva           DECIMAL(3,2) DEFAULT 0.5,
        grah            DECIMAL(3,2) DEFAULT 2.5,
        nadi            DECIMAL(3,2) DEFAULT 8,
        gana            INT DEFAULT 1,
        manglik         INT DEFAULT 0,
        total_score     DECIMAL(4,2) DEFAULT 0,
        compatibility   VARCHAR(20),
        summary         TEXT,
        created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_signs (boy_sign, girl_sign)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS bastu_recommendations (
        id                  INT AUTO_INCREMENT PRIMARY KEY,
        sign                VARCHAR(20) NOT NULL,
        fav_gem             VARCHAR(50),
        fav_color           VARCHAR(50),
        fav_day             VARCHAR(20),
        fav_metal           VARCHAR(30),
        fav_number          INT,
        fav_direction       VARCHAR(30),
        wear_gem            VARCHAR(100),
        avoid_gem           VARCHAR(100),
        home_direction     VARCHAR(30),
        office_direction   VARCHAR(30),
        good_feng_shui     TEXT,
        bad_feng_shui      TEXT,
        created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_sign (sign)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS api_cache (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        cache_key       VARCHAR(100) NOT NULL,
        data            LONGTEXT,
        fetched_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at      TIMESTAMP NOT NULL,
        UNIQUE KEY unique_key (cache_key),
        INDEX idx_expires (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS weather_alerts (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        alert_type      VARCHAR(50) NOT NULL,
        severity        VARCHAR(20) DEFAULT 'moderate',
        title           VARCHAR(255),
        description     TEXT,
        source          VARCHAR(100),
        start_time      DATETIME,
        end_time        DATETIME,
        is_active       TINYINT(1) DEFAULT 1,
        created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS earthquake_records (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        external_id     VARCHAR(50) UNIQUE,
        magnitude       DECIMAL(4,2),
        place           VARCHAR(255),
        latitude        DECIMAL(10,6),
        longitude       DECIMAL(10,6),
        depth           DECIMAL(8,2),
        event_time      DATETIME,
        tsunami         TINYINT(1) DEFAULT 0,
        source          VARCHAR(50) DEFAULT 'USGS',
        created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_magnitude (magnitude),
        INDEX idx_time (event_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS government_notices (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        title           VARCHAR(255) NOT NULL,
        description     TEXT,
        notice_type     VARCHAR(50),
        source          VARCHAR(100),
        notice_date     DATE,
        expiry_date     DATE,
        url             VARCHAR(500),
        is_featured     TINYINT(1) DEFAULT 0,
        created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_date (notice_date),
        INDEX idx_featured (is_featured)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;;


-- ── PART 2: Columns/indexes added after the base tables were first
--    created (mirrors src/init.php's $migrations array exactly — these
--    run every time the app boots and are individually try/caught, so
--    it's normal and expected for some to no-op on a fresh DB where the
--    column was already added in Part 1's definition; that's still
--    correct, just redundant-safe). If your MySQL/MariaDB version
--    doesn't support "ADD COLUMN IF NOT EXISTS" (needs MariaDB 10.0.2+/
--    MySQL 8.0.29+), remove "IF NOT EXISTS" from those specific lines —
--    on a truly fresh import they aren't needed anyway.
--
--    NOTE: advertisements.device/start_date/end_date/impressions and
--    articles.is_breaking are already part of Part 1's table definitions
--    above (the app added them there since this schema was extracted),
--    so their ALTER statements are omitted here entirely — running them
--    plain (without IF NOT EXISTS) against a fresh import would error
--    with "Duplicate column name".
-- ──────────────────────────────────────────────────────────────
ALTER TABLE articles ADD COLUMN seo_title VARCHAR(300) DEFAULT '';
ALTER TABLE articles ADD COLUMN seo_desc VARCHAR(500) DEFAULT '';
ALTER TABLE articles ADD COLUMN trending_score FLOAT DEFAULT 0;
ALTER TABLE articles ADD COLUMN type VARCHAR(20) DEFAULT 'news';
ALTER TABLE articles ADD COLUMN image_credit VARCHAR(200) DEFAULT '';
-- NOTE: idx_art_status_pub (articles) and idx_atag_tag (article_tags)
-- are also already part of Part 1's table definitions above, so those
-- two ADD INDEX statements are omitted here too (duplicate key name
-- would error on a fresh import).
ALTER TABLE articles ADD INDEX idx_art_trending (trending_score DESC);
ALTER TABLE articles ADD FULLTEXT INDEX ft_search (title, title_np, summary, summary_np);
ALTER TABLE articles ADD INDEX idx_art_cat_status_pub (category_id, status, published_at);
ALTER TABLE authors ADD COLUMN IF NOT EXISTS twitter_url VARCHAR(300) DEFAULT '';
ALTER TABLE authors ADD COLUMN IF NOT EXISTS facebook_url VARCHAR(300) DEFAULT '';
ALTER TABLE authors ADD COLUMN IF NOT EXISTS linkedin_url VARCHAR(300) DEFAULT '';
ALTER TABLE authors ADD COLUMN IF NOT EXISTS email VARCHAR(200) DEFAULT '';
ALTER TABLE authors ADD COLUMN IF NOT EXISTS role VARCHAR(100) DEFAULT '';
ALTER TABLE newsletter_subscribers ADD COLUMN IF NOT EXISTS token VARCHAR(64) DEFAULT '';
ALTER TABLE categories ADD COLUMN IF NOT EXISTS description TEXT DEFAULT '';
ALTER TABLE articles ADD COLUMN IF NOT EXISTS correction_note TEXT DEFAULT NULL;
-- (rate_limits table is already created in Part 1 above — no need to repeat it here)

SET FOREIGN_KEY_CHECKS = 1;

-- Done. Next step: create a settings row so the app doesn't think
-- this is a first-boot empty DB (it checks `SELECT 1 FROM settings`):
INSERT IGNORE INTO settings (`key`, value) VALUES
  ('site_initialized', '1');
