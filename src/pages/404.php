<?php
$page_title = '४०४ — पृष्ठ फेला परेन — ' . site_name();
if (headers_sent() === false) http_response_code(404);
if (!defined('SRC_DIR')) {
    define('BASE_DIR',dirname(__DIR__,2));
    define('SRC_DIR',BASE_DIR.'/src');
    define('DATA_DIR',BASE_DIR.'/data');
    define('DB_PATH',DATA_DIR.'/news.db');
    require_once SRC_DIR.'/config.php';
    require_once SRC_DIR.'/database.php';
    require_once SRC_DIR.'/helpers.php';
    require_once SRC_DIR.'/init.php';
}
$popular_404 = get_popular_articles(6);
require SRC_DIR . '/layout/header.php';
?>

<div class="page-404 flex flex-col items-center pb-0">
  <?= icon('file-search','w-16 h-16 mx-auto mb-3','w-16 h-16 inline-block align-middle flex-shrink-0') ?>
  <div class="code">४०४</div>
  <h1 class="text-xl font-bold mt-3 mb-2">पृष्ठ फेला परेन</h1>
  <p class="mb-6 text-center" style="color:var(--c-text2)">तपाईंले खोज्नुभएको पृष्ठ अस्तित्वमा छैन वा सारिएको छ।</p>
  <div class="flex flex-wrap gap-3 justify-center mb-10">
    <a href="/" class="btn btn-primary gap-1"><?= icon('home','w-4 h-4') ?> गृहपृष्ठमा जानुस्</a>
    <a href="/search" class="btn btn-secondary gap-1"><?= icon('search','w-4 h-4') ?> समाचार खोज्नुस्</a>
    <a href="/trending" class="btn btn-secondary gap-1"><?= icon('trending-up','w-4 h-4') ?> ट्रेन्डिङ</a>
  </div>
</div>

<?php if (!empty($popular_404)): ?>
<div class="mb-10" style="max-width:900px;margin-left:auto;margin-right:auto">
  <div class="section-heading mb-5">
    <span class="flex items-center gap-2"><?= icon('flame','w-4 h-4') ?> सबैभन्दा पढिएका समाचार</span>
  </div>
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
    <?php foreach ($popular_404 as $pa): ?>
    <a href="/article/<?= h($pa['slug']) ?>" class="article-card block group">
      <?php if ($pa['image_url']): ?>
      <div class="img-wrap">
        <img src="<?= h($pa['image_url']) ?>" alt="" loading="lazy">
      </div>
      <?php endif; ?>
      <div class="p-3">
        <span class="cat-badge mb-1 inline-block" style="background:<?= h(category_color($pa['category_color'])) ?>">
          <?= h($pa['category_name_np']?:$pa['category_name']) ?>
        </span>
        <h3 class="title group-hover:underline"><?= h(mb_substr($pa['title'],0,70)) ?><?= mb_strlen($pa['title'])>70?'…':'' ?></h3>
        <p class="meta mt-1"><?= icon('eye','w-2.5 h-2.5') ?> <?= np_number((int)$pa['views']) ?> &bull; <?= time_ago($pa['published_at']??$pa['created_at']) ?></p>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php require SRC_DIR . '/layout/footer.php'; ?>
