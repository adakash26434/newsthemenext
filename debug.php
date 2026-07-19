<?php
/**
 * Debug Script - Check Database Status
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/database.php';
require_once __DIR__ . '/src/helpers.php';

echo "<pre>";
echo "🔍 Database Debug\n";
echo "================\n\n";

try {
    // Test connection
    $db = get_db();
    echo "✅ Database Connected\n";
    echo "Driver: " . db_driver() . "\n\n";
    
    // Check tables
    echo "📊 Tables:\n";
    if (db_driver() === 'mysql') {
        $tables = db_fetchAll("SHOW TABLES");
        foreach ($tables as $t) {
            $name = array_values($t)[0];
            $count = db_fetch("SELECT COUNT(*) as c FROM `$name`")['c'];
            echo "  ✓ $name: $count rows\n";
        }
    } else {
        $tables = db_fetchAll("SELECT name FROM sqlite_master WHERE type='table'");
        foreach ($tables as $t) {
            $name = $t['name'];
            $count = db_fetch("SELECT COUNT(*) as c FROM `$name`")['c'];
            echo "  ✓ $name: $count rows\n";
        }
    }
    
    echo "\n📰 Testing get_articles():\n";
    $articles = get_articles(['status'=>'published','limit'=>3]);
    echo "Found " . count($articles) . " published articles\n";
    
    if (!empty($articles)) {
        foreach ($articles as $a) {
            echo "  - " . mb_substr($a['title'], 0, 50) . "...\n";
        }
    } else {
        echo "  (No articles found - will use demo data)\n";
    }
    
    echo "\n📂 Testing get_categories():\n";
    $cats = get_categories();
    echo "Found " . count($cats) . " categories\n";

    // ── Diagnostic: which categories are missing a Nepali name? ──
    // (explains categories that show in English even in Nepali mode —
    // this is a data gap, not a code bug: cat_name() correctly falls
    // back to the English name when name_np is empty)
    echo "\n🈺 Category translation check:\n";
    foreach ($cats as $c) {
        $flag = empty($c['name_np']) ? '❌ MISSING name_np' : 'ok';
        echo "  - {$c['name']} (slug: {$c['slug']}) -> name_np: '" . ($c['name_np'] ?: '') . "' [$flag]\n";
    }

    // ── Diagnostic: duplicate categories for the same topic ──
    echo "\n🔁 Duplicate-category check (same Nepali name, different slug):\n";
    $by_name_np = [];
    foreach ($cats as $c) {
        if (empty($c['name_np'])) continue;
        $by_name_np[$c['name_np']][] = $c['slug'];
    }
    $dupes_found = false;
    foreach ($by_name_np as $name_np => $slugs) {
        if (count($slugs) > 1) {
            $dupes_found = true;
            echo "  ❌ '$name_np' exists as multiple categories: " . implode(', ', $slugs) . "\n";
        }
    }
    if (!$dupes_found) echo "  ✅ No duplicates found.\n";

    // ── Diagnostic: does the specific 404-ing article actually exist? ──
    echo "\n📄 Testing get_article_by_slug('nepse-index-high'):\n";
    $test_article = get_article_by_slug('nepse-index-high');
    if ($test_article) {
        echo "  ✅ Found in DB — id={$test_article['id']}, status={$test_article['status']}, category_id={$test_article['category_id']}, author_id={$test_article['author_id']}\n";
        echo "  If this shows ✅ but the live URL still 404s, the problem is NOT the database —\n";
        echo "  check .htaccess / server rewrite config instead (see ROUTING TEST below).\n";
    } else {
        echo "  ❌ NOT found in DB. This confirms the article was deleted/renamed, or never existed\n";
        echo "  with this exact slug — the 404 is correct application behaviour, not a bug.\n";
    }

    // ── Diagnostic: does live_data_service.php load without a fatal ParseError? ──
    echo "\n🌐 Testing src/lib/live_data_service.php load:\n";
    try {
        require_once __DIR__ . '/src/lib/live_data_service.php';
        $lds = live_data();
        echo "  ✅ Loaded successfully (no ParseError).\n";
        $w = @$lds->getWeather();
        echo "  Weather fetch returned: " . (empty($w) ? "❌ EMPTY (external API likely unreachable from this server)" : "✅ " . count($w) . " field(s)") . "\n";
    } catch (\Throwable $e) {
        echo "  ❌ FATAL: " . get_class($e) . ": " . $e->getMessage() . " (line {$e->getLine()})\n";
        echo "  This is exactly the bug that silently empties the weather/AQI/earthquake widgets —\n";
        echo "  if you see this, the file on the server is an OLDER version; redeploy src/lib/live_data_service.php.\n";
    }

    echo "\n✅ All checks completed.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}
echo "</pre>";

// ── ROUTING TEST: confirms whether .htaccess correctly reaches this
//    script/index.php for 2-segment URLs like /article/{slug} ──
echo "<pre>\n🔗 Routing check:\n";
echo "REQUEST_URI as PHP sees it: " . h($_SERVER['REQUEST_URI'] ?? '(not set)') . "\n";
echo "If you loaded this page as https://yourdomain/debug.php it should show '/debug.php' above.\n";
echo "</pre>";

// ── FATAL ERROR LOG: shows the real error behind any blank-page symptom ──
// index.php now logs every fatal/parse error here via register_shutdown_function.
// This is the fastest way to see WHY a page went blank, without SSH/log access.
echo "<pre>\n💥 Recent fatal errors (data/php-error.log):\n";
$err_log = __DIR__ . '/data/php-error.log';
if (file_exists($err_log)) {
    $lines = file($err_log);
    $last20 = array_slice($lines, -20);
    echo empty($last20) ? "(log file is empty — no fatal errors recorded yet)\n" : h(implode('', $last20));
} else {
    echo "(no log file yet — either no fatal errors have occurred since this update was deployed,\n";
    echo " or this is an older deployment that doesn't have the shutdown-function logger yet.)\n";
}
echo "</pre>";
