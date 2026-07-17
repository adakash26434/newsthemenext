<?php
/**
 * Debug Script - Check Database Status
 */

require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/database.php';

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
    
    echo "\n✅ All checks passed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}
echo "</pre>";
