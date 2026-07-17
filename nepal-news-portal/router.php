<?php
/**
 * PHP built-in server router — replicates .htaccess mod_rewrite
 * Run from nepal-news-portal/ directory:
 *   php -S 0.0.0.0:3000 router.php
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve real static files directly (CSS, JS, images, fonts, etc.)
$file = __DIR__ . $uri;
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    // Add cache headers for static assets in production-like environment
    $ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
    if (in_array($ext, ['css','js','woff','woff2','ttf','eot','otf'])) {
        header('Cache-Control: public, max-age=86400'); // 1 day
    } elseif (in_array($ext, ['jpg','jpeg','png','gif','webp','svg','ico'])) {
        header('Cache-Control: public, max-age=604800'); // 7 days
    }
    return false; // PHP built-in server serves the file from __DIR__
}

// Everything else → index.php (mod_rewrite equivalent)
require __DIR__ . '/index.php';
