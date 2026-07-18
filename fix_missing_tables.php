<?php
/**
 * One-time fix: create any tables that are missing on an already-running
 * (already-initialized) database.
 *
 * init.php's CREATE TABLE IF NOT EXISTS statements only run automatically
 * the very first time the site boots against an empty database (index.php
 * only calls init.php if `SELECT 1 FROM settings` fails). On a database
 * that was already initialized BEFORE a new table was added to the schema
 * (like reaction_counts), that new table never gets created — and every
 * query against it throws an uncaught PDOException.
 *
 * CONFIRMED BUG: reaction_counts was queried/inserted into by
 * get_reaction_counts()/add_reaction() in src/database.php and called on
 * every single article page view (src/pages/article.php), but was never
 * part of any CREATE TABLE statement. On a live database that predates
 * this feature, every article page would fatal-error — which is very
 * likely why article pages appeared broken/wouldn't open.
 *
 * This script only CREATEs missing tables (IF NOT EXISTS) — it never
 * drops or modifies existing data. Safe to run any number of times.
 *
 * Run once via: php fix_missing_tables.php
 */
if (!defined('BASE_DIR')) {
    define('BASE_DIR', __DIR__);
    define('SRC_DIR',  BASE_DIR . '/src');
    define('DATA_DIR', BASE_DIR . '/data');
    define('DB_PATH',  DATA_DIR . '/news.db');
    require_once SRC_DIR . '/config.php';
    require_once SRC_DIR . '/database.php';
    require_once SRC_DIR . '/helpers.php';
}

$driver = db_driver();
echo "Database driver: $driver\n\n";

$statements = $driver === 'mysql' ? [
    'reaction_counts' => "
        CREATE TABLE IF NOT EXISTS reaction_counts (
            id         INT NOT NULL AUTO_INCREMENT,
            article_id INT NOT NULL,
            type       VARCHAR(20) NOT NULL,
            count      INT DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY uq_article_type (article_id, type),
            KEY idx_article (article_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
] : [
    'reaction_counts' => "
        CREATE TABLE IF NOT EXISTS reaction_counts (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            article_id INTEGER NOT NULL,
            type       TEXT NOT NULL,
            count      INTEGER DEFAULT 0,
            UNIQUE (article_id, type)
        );
    ",
];

foreach ($statements as $table => $sql) {
    $existing = db_fetch(
        $driver === 'mysql'
            ? "SELECT COUNT(*) as c FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?"
            : "SELECT name FROM sqlite_master WHERE type='table' AND name = ?",
        [$table]
    );
    $already_exists = $driver === 'mysql' ? (($existing['c'] ?? 0) > 0) : !empty($existing);

    if ($already_exists) {
        echo "= $table already exists, skipping.\n";
        continue;
    }
    db()->exec($sql);
    echo "✓ Created missing table: $table\n";
}

// ── Performance indexes for scale (won't slow down as articles/pages grow) ──
// init.php's migrations only run automatically on a brand-new database —
// they never re-trigger on an already-initialized live DB, so indexes added
// to the schema after go-live have to be applied here instead.
echo "\nApplying performance indexes...\n";
$index_statements = $driver === 'mysql' ? [
    "ALTER TABLE articles ADD INDEX idx_art_status_pub (status, published_at)",
    "ALTER TABLE articles ADD INDEX idx_art_cat_status_pub (category_id, status, published_at)",
    "ALTER TABLE articles ADD INDEX idx_art_trending (trending_score DESC)",
    "ALTER TABLE article_tags ADD INDEX idx_atag_tag (tag_id)",
] : [
    "CREATE INDEX IF NOT EXISTS idx_art_status_pub ON articles(status, published_at)",
    "CREATE INDEX IF NOT EXISTS idx_art_cat_status_pub ON articles(category_id, status, published_at)",
    "CREATE INDEX IF NOT EXISTS idx_art_trending ON articles(trending_score DESC)",
    "CREATE INDEX IF NOT EXISTS idx_atag_tag ON article_tags(tag_id)",
];
foreach ($index_statements as $sql) {
    try {
        db()->exec($sql);
        echo "✓ $sql\n";
    } catch (Exception $e) {
        echo "= already applied (or column missing): " . substr($sql, 0, 60) . "...\n";
    }
}

echo "\nDone. Re-run any time — already-existing tables are left untouched.\n";
