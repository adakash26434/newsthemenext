<?php
/**
 * RSS 2.0 Feed — all news or per-category
 * Endpoints:
 *   /rss.xml              → all published articles (latest 30)
 *   /rss/{category-slug}  → category-specific feed
 *
 * Also generates a Google News Sitemap at /google-news-sitemap.xml
 */
defined('APP_START') || define('APP_START', true);
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/database.php';
require_once __DIR__ . '/src/helpers.php';

// Ensure DB is initialized
try { get_db()->query("SELECT 1 FROM settings LIMIT 1"); }
catch (Exception $e) { require __DIR__ . '/src/init.php'; }

$uri      = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$base_url = setting('site_url', 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
$base_url = rtrim($base_url, '/');

// ── Determine feed type ───────────────────────────────────
$category_slug = '';
$tag_slug      = '';
$author_slug   = '';
$is_gnews = ($uri === '/google-news-sitemap.xml');

if (!$is_gnews) {
    // /rss/tag/{slug} or /rss/author/{slug} or /rss/{category-slug} or /rss.xml
    if (preg_match('#^/rss/tag/([^/]+)$#', $uri, $m)) {
        $tag_slug = $m[1];
    } elseif (preg_match('#^/rss/author/([^/]+)$#', $uri, $m)) {
        $author_slug = $m[1];
    } elseif (preg_match('#^/rss/([^/]+)$#', $uri, $m)) {
        $category_slug = $m[1];
    }
}

// ── Fetch articles ────────────────────────────────────────
$opts = ['status'=>'published','limit'=>30];
$category   = null;
$tag_obj    = null;
$author_obj = null;
if ($tag_slug) {
    $tag_obj = get_tag_by_slug($tag_slug);
    if (!$tag_obj) { http_response_code(404); exit; }
    $opts['tag_slug'] = $tag_slug;
}
if ($author_slug) {
    $author_obj = get_author_by_slug($author_slug);
    if (!$author_obj) { http_response_code(404); exit; }
    $opts['author_slug'] = $author_slug;
}
if ($category_slug) {
    $category = get_category_by_slug($category_slug);
    if (!$category) { http_response_code(404); exit; }
    $opts['category_slug'] = $category_slug;
}
if ($is_gnews) {
    // Google News: last 48 hours only, max 1000
    $opts['limit']      = 1000;
    $opts['gnews_mode'] = true;
}
$articles = get_articles($opts);

// For Google News, filter to last 48 hours
if ($is_gnews) {
    $cutoff   = time() - 172800; // 48 hours
    $articles = array_filter($articles, fn($a) => strtotime($a['published_at'] ?? $a['created_at']) >= $cutoff);
}

// ── Site info ─────────────────────────────────────────────
$site_name    = setting('site_name', 'Nepal News Portal');
$site_name_en = setting('site_name_en', 'Nepal News Portal');
$tagline      = setting('site_tagline', 'नेपालको विश्वसनीय समाचार पोर्टल');

// ── Output ────────────────────────────────────────────────
header('Content-Type: application/xml; charset=UTF-8');
header('Cache-Control: public, max-age=600');

if ($is_gnews) {
    // ── Google News Sitemap ───────────────────────────────
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
       . ' xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"'
       . ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

    foreach ($articles as $a) {
        $url   = $base_url . '/article/' . xmle($a['slug']);
        $pub   = date('c', strtotime($a['published_at'] ?? $a['created_at']));
        $title = xmle($a['title'] ?: $a['title_np'] ?? '');
        echo "  <url>\n";
        echo "    <loc>{$url}</loc>\n";
        echo "    <news:news>\n";
        echo "      <news:publication>\n";
        echo "        <news:name>" . xmle($site_name_en) . "</news:name>\n";
        echo "        <news:language>ne</news:language>\n";
        echo "      </news:publication>\n";
        echo "      <news:publication_date>{$pub}</news:publication_date>\n";
        echo "      <news:title>{$title}</news:title>\n";
        echo "    </news:news>\n";
        if ($a['image_url']) {
            echo "    <image:image>\n";
            echo "      <image:loc>" . xmle($base_url . $a['image_url']) . "</image:loc>\n";
            echo "    </image:image>\n";
        }
        echo "  </url>\n";
    }
    echo '</urlset>';
    exit;
}

// ── RSS 2.0 ───────────────────────────────────────────────
$feed_title  = $tag_obj
    ? '#' . h($tag_obj['name']) . ' — ' . $site_name
    : ($author_obj
        ? h($author_obj['name']) . ' — ' . $site_name
        : ($category ? h($category['name_np'] ?: $category['name']) . ' — ' . $site_name : $site_name));
$feed_desc   = $category ? ($category['name_np'] ?: $category['name']) . ' विभागका समाचार' : $tagline;
$feed_link   = $base_url . ($category ? '/category/' . $category['slug'] : '/');
$last_build  = !empty($articles) ? date('r', strtotime($articles[0]['published_at'] ?? $articles[0]['created_at'])) : date('r');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<rss version="2.0"'
   . ' xmlns:atom="http://www.w3.org/2005/Atom"'
   . ' xmlns:media="http://search.yahoo.com/mrss/"'
   . ' xmlns:content="http://purl.org/rss/1.0/modules/content/">' . "\n";
echo "<channel>\n";
echo "  <title>" . xmle($feed_title) . "</title>\n";
echo "  <link>{$feed_link}</link>\n";
echo "  <description>" . xmle($feed_desc) . "</description>\n";
echo "  <language>ne</language>\n";
echo "  <lastBuildDate>{$last_build}</lastBuildDate>\n";
echo "  <generator>Nepal News Portal</generator>\n";
echo "  <atom:link href=\"" . xmle($base_url . $uri) . "\" rel=\"self\" type=\"application/rss+xml\"/>\n";

foreach ($articles as $a) {
    $item_url  = $base_url . '/article/' . $a['slug'];
    $item_title= $a['title'] ?: ($a['title_np'] ?? '(शीर्षक)');
    $item_desc = excerpt($a['summary'] ?: strip_tags($a['content'] ?? ''), 40);
    $pub_date  = date('r', strtotime($a['published_at'] ?? $a['created_at']));
    $cat_name  = $a['category_name_np'] ?: $a['category_name'];

    echo "  <item>\n";
    echo "    <title>" . xmle($item_title) . "</title>\n";
    echo "    <link>{$item_url}</link>\n";
    echo "    <guid isPermaLink=\"true\">{$item_url}</guid>\n";
    echo "    <pubDate>{$pub_date}</pubDate>\n";
    echo "    <description>" . xmle($item_desc) . "</description>\n";
    if ($a['content']) {
        echo "    <content:encoded><![CDATA[" . $a['content'] . "]]></content:encoded>\n";
    }
    echo "    <category>" . xmle($cat_name) . "</category>\n";
    if ($a['author_name']) {
        echo "    <author>" . xmle($a['author_name']) . "</author>\n";
    }
    if ($a['image_url']) {
        echo "    <media:content url=\"" . xmle($base_url . $a['image_url']) . "\" medium=\"image\"/>\n";
        echo "    <enclosure url=\"" . xmle($base_url . $a['image_url']) . "\" type=\"image/jpeg\" length=\"0\"/>\n";
    }
    echo "  </item>\n";
}
echo "</channel>\n</rss>";

function xmle(string $s): string
{
    return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}
