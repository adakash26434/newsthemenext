<?php
$page_title = '404 — पृष्ठ फेला परेन';
$page_desc  = 'यो पृष्ठ अवस्थित छैन।';
if (!headers_sent()) http_response_code(404);
// If called standalone (not via header)
if (!defined('APP_START')) {
    require_once dirname(__DIR__) . '/config.php';
    require_once dirname(__DIR__) . '/database.php';
    require_once dirname(__DIR__) . '/helpers.php';
    require_once dirname(__DIR__) . '/layout/header.php';
}
?>
<div class="max-w-lg mx-auto text-center py-20">
  <div class="text-8xl font-black mb-4" style="color:var(--c-border)">४०४</div>
  <h1 class="text-2xl font-bold mb-3">पृष्ठ फेला परेन</h1>
  <p class="mb-6" style="color:var(--c-muted)">तपाईंले खोज्नुभएको पृष्ठ अवस्थित छैन वा सारिएको छ।</p>
  <div class="flex gap-3 justify-center">
    <a href="/" class="btn btn-primary">गृहपृष्ठमा जानुस्</a>
    <a href="/search" class="btn btn-secondary">खोज्नुस्</a>
  </div>
</div>
<?php require SRC_DIR . '/layout/footer.php'; ?>
