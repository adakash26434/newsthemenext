<?php
$categories_all = get_categories();
$cat = null;
foreach ($categories_all as $c) {
    if ($c['slug'] === ($_cat_slug ?? '')) { $cat = $c; break; }
}
if (!$cat) { http_response_code(404); require SRC_DIR . '/pages/404.php'; exit; }

$page_num = max(1, (int)($_GET['page'] ?? 1));
$total    = count_articles(['status'=>'published','category_slug'=>$cat['slug']]);
$pager    = paginate($total, ARTICLES_PER_PAGE, $page_num, "/category/{$cat['slug']}?page=%d");
$articles = get_articles(['status'=>'published','category_slug'=>$cat['slug'],'limit'=>$pager['per_page'],'offset'=>$pager['offset']]);
$latest   = get_articles(['status'=>'published','limit'=>6]);

$page_title = ($cat['name_np'] ?: $cat['name']) . ' — ' . site_name();
$page_desc  = ($cat['name_np'] ?: $cat['name']) . ' बारेका ताजा समाचार।';
require SRC_DIR . '/layout/header.php';
?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2">
    <div class="flex items-center gap-3 mb-5 pb-3" style="border-bottom:3px solid <?= h($cat['color'] ?: accent_color()) ?>">
      <span class="w-4 h-4 rounded-full" style="background:<?= h($cat['color'] ?: accent_color()) ?>"></span>
      <h1 class="text-2xl font-extrabold" style="color:<?= h($cat['color'] ?: accent_color()) ?>">
        <?= h($cat['name_np'] ?: $cat['name']) ?>
      </h1>
      <span class="text-sm" style="color:var(--c-muted)">(<?= np_number($total) ?> समाचार)</span>
    </div>

    <?php if (empty($articles)): ?>
      <div class="text-center py-16" style="color:var(--c-muted)">
        <p class="text-lg">यस श्रेणीमा अहिले कुनै समाचार छैन।</p>
      </div>
    <?php else: ?>
      <div class="space-y-4 mb-6">
        <?php foreach ($articles as $a): ?>
        <a href="/article/<?= h($a['slug']) ?>" class="article-card flex gap-4 p-4 group block">
          <div class="img-wrap flex-shrink-0 rounded-sm" style="width:120px;height:90px;aspect-ratio:unset">
            <?php if ($a['image_url']): ?>
              <img src="<?= h($a['image_url']) ?>" alt="" loading="lazy">
            <?php endif; ?>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-1 mb-1">
              <span class="lang-badge lang-<?= h($a['language']??'np') ?>"><?= ($a['language']??'np')==='en'?'EN':'NP' ?></span>
            </div>
            <h2 class="font-bold text-base leading-snug group-hover:underline mb-1"><?= h($a['title']) ?></h2>
            <p class="text-sm line-clamp-2 mb-2" style="color:var(--c-text2)"><?= h(excerpt($a['summary'], 18)) ?></p>
            <div class="meta flex items-center gap-2">
              <span><?= h($a['author_name']) ?></span>
              <span>&bull;</span>
              <span><?= time_ago($a['published_at'] ?? $a['created_at']) ?></span>
              <span>&bull;</span>
              <span><?= np_number((int)$a['views']) ?> पठन</span>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

      <?php if ($pager['total_pages'] > 1): ?>
      <div class="pagination mb-6">
        <?php if ($pager['has_prev']): ?>
          <a href="<?= sprintf($pager['url_pattern'], $pager['prev_page']) ?>">&laquo; अघिल्लो</a>
        <?php endif; ?>
        <?php for ($p = max(1,$pager['current']-2); $p <= min($pager['total_pages'],$pager['current']+2); $p++): ?>
          <?php if ($p === $pager['current']): ?>
            <span class="current"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= sprintf($pager['url_pattern'], $p) ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        <?php if ($pager['has_next']): ?>
          <a href="<?= sprintf($pager['url_pattern'], $pager['next_page']) ?>">अर्को &raquo;</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <aside>
    <?php render_ads('sidebar-top'); ?>
    <div class="sidebar-card">
      <div class="section-heading mb-3"><span>ताजा समाचार</span></div>
      <?php foreach ($latest as $la): ?>
      <div class="sidebar-article">
        <a href="/article/<?= h($la['slug']) ?>" class="thumb">
          <?php if ($la['image_url']): ?><img src="<?= h($la['image_url']) ?>" alt="" loading="lazy"><?php endif; ?>
        </a>
        <div class="info">
          <a href="/article/<?= h($la['slug']) ?>" class="title block hover:underline"><?= h($la['title']) ?></a>
          <div class="meta"><?= time_ago($la['published_at'] ?? $la['created_at']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php render_ads('sidebar-bottom'); ?>
    <div class="sidebar-card mt-4">
      <div class="section-heading mb-3"><span>सबै श्रेणीहरू</span></div>
      <?php foreach ($categories_all as $cw): ?>
        <a href="/category/<?= h($cw['slug']) ?>"
           class="flex items-center justify-between py-2 border-b text-sm font-semibold transition-colors <?= $cw['slug']===$cat['slug']?'font-extrabold':'' ?>"
           style="border-color:var(--c-border2);color:<?= $cw['slug']===$cat['slug']?h($cw['color']?:accent_color()):'inherit' ?>">
          <span class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full" style="background:<?= h($cw['color']?:accent_color()) ?>"></span>
            <?= h($cw['name_np'] ?: $cw['name']) ?>
          </span>
          <span class="text-xs" style="color:var(--c-muted)"><?= np_number((int)($cw['article_count']??0)) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </aside>
</div>
<?php require SRC_DIR . '/layout/footer.php'; ?>
