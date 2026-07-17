<?php
$page_title = '४०४ — पृष्ठ फेला परेन — ' . site_name();
if (headers_sent() === false) http_response_code(404);
if (!defined('SRC_DIR')) { define('BASE_DIR',dirname(__DIR__,2)); define('SRC_DIR',BASE_DIR.'/src'); define('DATA_DIR',BASE_DIR.'/data'); define('DB_PATH',DATA_DIR.'/news.db'); require_once SRC_DIR.'/config.php'; require_once SRC_DIR.'/database.php'; require_once SRC_DIR.'/helpers.php'; require_once SRC_DIR.'/init.php'; }
require SRC_DIR . '/layout/header.php';
?>

<div class="page-404 flex flex-col items-center">
  <?= icon('file-search','w-20 h-20 mx-auto mb-4','w-20 h-20 inline-block align-middle flex-shrink-0') ?>
  <div class="code">४०४</div>
  <h1 class="text-xl font-bold mt-3 mb-2">पृष्ठ फेला परेन</h1>
  <p class="mb-6" style="color:var(--c-text2)">तपाईंले खोज्नुभएको पृष्ठ अस्तित्वमा छैन वा सारिएको छ।</p>
  <div class="flex flex-wrap gap-3 justify-center">
    <a href="/" class="btn btn-primary gap-1"><?= icon('home','w-4 h-4') ?> गृहपृष्ठमा जानुस्</a>
    <a href="/search" class="btn btn-secondary gap-1"><?= icon('search','w-4 h-4') ?> समाचार खोज्नुस्</a>
  </div>
</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
