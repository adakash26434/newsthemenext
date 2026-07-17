<?php
// Dynamic sitemap.xml
header('Content-Type: application/xml; charset=UTF-8');

$base = (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']==='on'?'https':'http').'://'.$_SERVER['HTTP_HOST'];

function sitemap_url(string $loc, string $lastmod = '', string $freq = 'weekly', float $priority = 0.5): string {
    $out  = '<url>';
    $out .= '<loc>' . htmlspecialchars($loc) . '</loc>';
    if ($lastmod) $out .= '<lastmod>' . date('Y-m-d', strtotime($lastmod)) . '</lastmod>';
    $out .= '<changefreq>' . $freq . '</changefreq>';
    $out .= '<priority>' . $priority . '</priority>';
    $out .= '</url>';
    return $out;
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Homepage
echo sitemap_url($base . '/', '', 'always', 1.0);
// Events
echo sitemap_url($base . '/events', '', 'daily', 0.8);
// Search
echo sitemap_url($base . '/search', '', 'weekly', 0.3);

// Categories
$categories = get_categories();
foreach ($categories as $cat) {
    echo sitemap_url($base . '/category/' . $cat['slug'], $cat['updated_at'] ?? '', 'hourly', 0.8);
}

// Authors
$authors = get_authors();
foreach ($authors as $au) {
    echo sitemap_url($base . '/author/' . $au['slug'], '', 'weekly', 0.5);
}

// Articles
$articles = get_articles(['status' => 'published', 'limit' => 1000]);
foreach ($articles as $art) {
    echo sitemap_url(
        $base . '/article/' . $art['slug'],
        $art['published_at'] ?? $art['created_at'] ?? '',
        'weekly',
        0.7
    );
}

// Events
$events = get_events(['limit' => 500]);
foreach ($events as $ev) {
    echo sitemap_url($base . '/event/' . $ev['slug'], $ev['updated_at'] ?? $ev['created_at'] ?? '', 'weekly', 0.6);
}

// Static pages
$pages = get_static_pages();
foreach ($pages as $pg) {
    echo sitemap_url($base . '/page/' . $pg['slug'], $pg['updated_at'] ?? '', 'monthly', 0.5);
}

// ePaper archive
echo sitemap_url($base . '/epaper', '', 'daily', 0.6);
$epapers = get_epapers(50);
foreach ($epapers as $ep) {
    if (!empty($ep['pdf_path'])) {
        echo sitemap_url($base . '/epaper', $ep['edition_date'] ?? '', 'never', 0.4);
        break; // archive page already listed; one entry is enough
    }
}

echo '</urlset>';
