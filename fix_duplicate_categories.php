<?php
/**
 * One-time cleanup: Merge duplicate categories.
 *
 * ROOT CAUSE (found during deep review, confirmed on live site):
 * src/init.php auto-seeds a canonical category list on first run (slugs:
 * technology, sports, paryatan, world, bichar). Separately, demo_data.sql
 * seeds its OWN category list for the same topics but with different
 * (and in one case broken — "par tourism" with a literal space) slugs:
 * prabidhi, khel-kurod, "par tourism", sansar, opinions. Because
 * `INSERT IGNORE` only skips rows on an exact slug match, importing
 * demo_data.sql after init.php had already run created 5 duplicate
 * category rows for the same real-world topics — which is why the
 * live site's nav shows both "Sports" and "खेलकुद" as separate items,
 * and why /category/par tourism was a broken URL.
 *
 * This script is idempotent — safe to run more than once. It:
 *   1. For each known duplicate pair, moves all articles from the
 *      old (duplicate) category to the canonical one.
 *   2. Deletes the now-empty duplicate category row.
 *   3. Adds a 301 redirect from the old URL to the canonical URL
 *      (so any old bookmarked/shared/indexed link still works).
 *
 * Run once via: php fix_duplicate_categories.php
 */
if (!defined('BASE_DIR')) {
    define('BASE_DIR', __DIR__);
    define('SRC_DIR',  BASE_DIR . '/src');
    define('DATA_DIR', BASE_DIR . '/data');
    define('DB_PATH',  DATA_DIR . '/news.db');
    require_once SRC_DIR . '/config.php';
    require_once SRC_DIR . '/database.php';
    require_once SRC_DIR . '/helpers.php';
    require_once SRC_DIR . '/init.php';
}

// old (duplicate) slug => canonical slug that should survive
$duplicate_map = [
    'prabidhi'     => 'technology',
    'khel-kurod'   => 'sports',
    'par tourism'  => 'paryatan',
    'sansar'       => 'world',
    'opinions'     => 'bichar',
];

$merged = 0;
$is_mysql = db_driver() === 'mysql';

function upsert_redirect(string $old_path, string $new_path, bool $is_mysql): void {
    $existing = db_fetch("SELECT id FROM redirects WHERE old_path = ?", [$old_path]);
    if ($existing) {
        db_query("UPDATE redirects SET new_path = ? WHERE id = ?", [$new_path, $existing['id']]);
    } else {
        db_query("INSERT INTO redirects (old_path, new_path, status_code) VALUES (?, ?, 301)", [$old_path, $new_path]);
    }
}

foreach ($duplicate_map as $old_slug => $new_slug) {
    $old = db_fetch("SELECT * FROM categories WHERE slug = ?", [$old_slug]);
    $new = db_fetch("SELECT * FROM categories WHERE slug = ?", [$new_slug]);

    if ($old && $new) {
        // Both exist — move articles from old to new, then delete old.
        db_query("UPDATE articles SET category_id = ? WHERE category_id = ?", [$new['id'], $old['id']]);
        db_query("DELETE FROM categories WHERE id = ?", [$old['id']]);
        upsert_redirect('/category/' . $old_slug, '/category/' . $new_slug, $is_mysql);
        echo "✓ Merged '$old_slug' → '$new_slug' (articles moved, redirect added)\n";
        $merged++;
    } elseif ($old && !$new) {
        // Only the broken/duplicate one exists — just rename it in place.
        db_query("UPDATE categories SET slug = ? WHERE id = ?", [$new_slug, $old['id']]);
        upsert_redirect('/category/' . $old_slug, '/category/' . $new_slug, $is_mysql);
        echo "✓ Renamed '$old_slug' → '$new_slug' (no duplicate found, redirect added)\n";
        $merged++;
    } else {
        echo "- '$old_slug' not present, nothing to merge\n";
    }
}

echo $merged
    ? "\nDone. $merged categor" . ($merged === 1 ? 'y' : 'ies') . " cleaned up.\n"
    : "\nNo duplicates found — categories table is already clean.\n";
