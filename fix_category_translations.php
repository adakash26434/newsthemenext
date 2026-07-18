<?php
/**
 * One-time fix: fill in missing Nepali category names.
 *
 * The category-language display bug (English category names showing in
 * Nepali mode) was fixed in code via cat_name() — but a handful of
 * categories on the live site (Economics, Banking, Insurance, Share
 * Market, Corporate) still show in English because their `name_np`
 * column is empty in the database itself. cat_name() correctly falls
 * back to the English name when no Nepali name exists — this script
 * fills in the missing Nepali names so the fallback is no longer needed.
 *
 * Safe to run more than once — only fills in categories where name_np
 * is currently empty; never overwrites an existing Nepali name.
 *
 * Run once via: php fix_category_translations.php
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

// slug => Nepali name (matches demo_data.sql / init.php's canonical list)
$known_translations = [
    'arthatantra'  => 'अर्थतन्त्र',
    'economics'    => 'अर्थतन्त्र',
    'economy'      => 'अर्थतन्त्र',
    'banking'      => 'बैंकिङ',
    'bima'         => 'बिमा',
    'insurance'    => 'बिमा',
    'share-bazar'  => 'शेयर बजार',
    'sharemarket'  => 'शेयर बजार',
    'corporate'    => 'कर्पोरेट',
    'rajniti'      => 'राजनीति',
    'samaj'        => 'समाज',
    'technology'   => 'प्रविधि',
    'sports'       => 'खेलकुद',
    'paryatan'     => 'पर्यटन',
    'world'        => 'विश्व',
    'bichar'       => 'विचार',
];

$cats = db_fetchAll("SELECT id, slug, name, name_np FROM categories");
$fixed = 0;
foreach ($cats as $c) {
    if (!empty($c['name_np'])) continue; // already has a Nepali name — don't touch it
    $key = strtolower($c['slug']);
    if (isset($known_translations[$key])) {
        db_query("UPDATE categories SET name_np = ? WHERE id = ?", [$known_translations[$key], $c['id']]);
        echo "✓ {$c['slug']} ({$c['name']}) -> {$known_translations[$key]}\n";
        $fixed++;
    } else {
        echo "? {$c['slug']} ({$c['name']}) — no known Nepali translation, please add manually via Admin\n";
    }
}

echo $fixed
    ? "\nDone. $fixed categor" . ($fixed === 1 ? 'y' : 'ies') . " updated.\n"
    : "\nNothing to fix — all categories already have a Nepali name.\n";
