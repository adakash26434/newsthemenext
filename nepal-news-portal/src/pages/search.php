<?php
$q        = trim($_GET['q'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = ARTICLES_PER_PAGE;

$total    = 0;
$articles = [];
$pag      = null;

if ($q !== '') {
    $opts     = ['status'=>'published','search'=>$q];
    $total    = count_articles($opts);
    $pag      = paginate($total, $per_page, $page, '/search?q='.urlencode($q).'&page={page}');
    $articles = get_articles(array_merge($opts, ['limit'=>$per_page,'offset'=>$pag['offset']]));
}

$page_title = ($q ? '"' . h($q) . '" खोजको नतिजा' : 'समाचार खोज्नुस्') . ' — ' . site_name();

require SRC_DIR . '/layout/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2">
    <!-- Search bar -->
    <div class="mb-6 p-5 rounded-xl" style="background:var(--c-surface);border:1px solid var(--c-border)">
      <h1 class="text-xl font-bold mb-4 flex items-center gap-2" style="color:var(--c-text)">
        <?= icon('search','w-5 h-5') ?> समाचार खोज्नुस्
      </h1>
      <form method="GET" action="/search" class="flex gap-2">
        <input type="search" name="q" value="<?= h($q) ?>" class="form-control flex-1"
               placeholder="नेपाली वा English मा खोज्नुस्..." autofocus>
        <button type="submit" class="btn btn-primary gap-1">
          <?= icon('search','w-4 h-4') ?> खोज्नुस्
        </button>
      </form>
    </div>

    <?php if ($q !== ''): ?>
    <div class="mb-4 flex items-center gap-2" style="color:var(--c-muted)">
      <?= icon('info','w-4 h-4') ?>
      <span class="text-sm"><strong><?= h($q) ?></strong> को लागि <strong><?= np_number($total) ?></strong> नतिजा फेला परे</span>
    </div>

    <?php if (empty($articles)): ?>
    <div class="stat-card text-center py-10" style="color:var(--c-muted)">
      <?= icon('search-x','w-10 h-10 mx-auto mb-3 opacity-30') ?>
      <p class="mb-2">कुनै नतिजा फेला परेन।</p>
      <p class="text-sm">अर्को कीवर्ड प्रयास गर्नुस्।</p>
    </div>
    <?php else: ?>
    <div class="space-y-4">
      <?php foreach ($articles as $a): ?>
      <a href="/article/<?= h($a['slug']) ?>" class="flex gap-4 p-3 rounded-lg group hover:shadow-md transition-all" style="background:var(--c-surface);border:1px solid var(--c-border)">
        <?php if ($a['image_url']): ?>
        <div class="flex-shrink-0 rounded-lg overflow-hidden" style="width:100px;height:75px;background:var(--c-surface2)">
          <img src="<?= h($a['image_url']) ?>" alt="" loading="lazy" class="w-full h-full object-cover">
        </div>
        <?php endif; ?>
        <div class="flex-1 min-w-0">
          <span class="cat-badge inline-block mb-1" style="background:<?= h(category_color($a['category_color'])) ?>">
            <?= h($a['category_name_np']?:$a['category_name']) ?>
          </span>
          <h2 class="font-bold leading-snug line-clamp-2 group-hover:underline"><?= h($a['title']) ?></h2>
          <?php if ($a['summary']): ?>
          <p class="text-sm mt-1 line-clamp-2" style="color:var(--c-text2)"><?= h(excerpt($a['summary'],15)) ?></p>
          <?php endif; ?>
          <div class="flex items-center gap-3 mt-1.5 text-xs" style="color:var(--c-muted)">
            <span class="flex items-center gap-1"><?= icon('user','w-3 h-3') ?> <?= h($a['author_name']) ?></span>
            <span class="flex items-center gap-1"><?= icon('clock','w-3 h-3') ?> <?= time_ago($a['published_at']??$a['created_at']) ?></span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php if ($pag) render_pagination($pag); ?>
    <?php endif; ?>
    <?php endif; ?>
  </div>

  <aside class="space-y-5">
    <?php render_ads('sidebar-top'); ?>
    <div class="sidebar-card">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('hash','w-4 h-4') ?> लोकप्रिय ट्यागहरू</span>
      </div>
      <div class="tag-cloud">
        <?php foreach (array_slice(get_tags(), 0, 20) as $t): ?>
          <a href="/search?q=<?= urlencode($t['name']) ?>"><?= h($t['name']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </aside>
</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
