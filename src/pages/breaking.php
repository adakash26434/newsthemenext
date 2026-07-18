<?php
$page_title = 'ब्रेकिङ न्यूज — ' . site_name();
$page_desc  = 'नेपालका ताजा ब्रेकिङ समाचारहरू';

$per_page = ARTICLES_PER_PAGE;
$page     = max(1, (int)($_GET['page'] ?? 1));
$opts     = ['status'=>'published','is_breaking'=>true];
$total    = count_articles($opts);
$pag      = paginate($total, $per_page, $page, '/breaking?page={page}');
$articles = get_articles(array_merge($opts, ['limit'=>$per_page,'offset'=>$pag['offset']]));

require SRC_DIR . '/layout/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-5 p-4 rounded-xl"
         style="background:linear-gradient(135deg,#7F1D1D,#991B1B);color:#fff">
      <span class="relative flex h-10 w-10 flex-shrink-0">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-300 opacity-50"></span>
        <span class="relative flex h-10 w-10 rounded-full items-center justify-center"
              style="background:rgba(255,255,255,.15)">
          <?= icon('zap','w-5 h-5') ?>
        </span>
      </span>
      <div>
        <h1 class="text-lg font-extrabold tracking-wide">ब्रेकिङ न्यूज</h1>
        <p class="text-sm opacity-80"><?= np_number($total) ?> ताजा समाचार</p>
      </div>
    </div>

    <?php if (empty($articles)): ?>
    <div class="stat-card text-center py-10" style="color:var(--c-muted)">
      <?= icon('zap-off','w-10 h-10 mx-auto mb-3 opacity-30') ?>
      <p>हाल कुनै ब्रेकिङ समाचार छैन।</p>
    </div>
    <?php else: ?>

    <!-- First article — full featured -->
    <?php $first = $articles[0]; ?>
    <a href="/article/<?= h($first['slug']) ?>"
       class="block mb-5 rounded-xl overflow-hidden group relative"
       style="background:var(--c-surface);border:2px solid #DC2626">
      <?php if ($first['image_url']): ?>
      <div style="height:250px;overflow:hidden;background:var(--c-surface2)">
        <img src="<?= h($first['image_url']) ?>" alt="" loading="eager"
             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
      </div>
      <?php endif; ?>
      <div class="absolute top-3 left-3">
        <span class="px-2 py-0.5 text-xs font-bold rounded text-white"
              style="background:#DC2626">
          🔴 BREAKING
        </span>
      </div>
      <div class="p-4">
        <h2 class="text-xl font-extrabold leading-snug mb-2 group-hover:underline" style="color:var(--c-text)">
          <?= h($first['title']) ?>
        </h2>
        <?php if ($first['summary']): ?>
        <p class="text-sm leading-relaxed mb-2 line-clamp-2" style="color:var(--c-text2)"><?= h($first['summary']) ?></p>
        <?php endif; ?>
        <div class="flex items-center gap-4 text-xs" style="color:var(--c-muted)">
          <span class="flex items-center gap-1"><?= icon('user','w-3 h-3') ?> <?= h($first['author_name']) ?></span>
          <span class="flex items-center gap-1"><?= icon('clock','w-3 h-3') ?> <?= time_ago($first['published_at']??$first['created_at']) ?></span>
          <span class="flex items-center gap-1"><?= icon('eye','w-3 h-3') ?> <?= np_number((int)$first['views']) ?></span>
        </div>
      </div>
    </a>

    <!-- Rest of breaking news -->
    <div class="space-y-3">
      <?php foreach (array_slice($articles, 1) as $a): ?>
      <a href="/article/<?= h($a['slug']) ?>"
         class="flex gap-4 p-3 rounded-lg group hover:shadow-md transition-all"
         style="background:var(--c-surface);border:1px solid #FECACA">
        <div class="flex-shrink-0 flex items-start pt-1">
          <span class="inline-block w-2 h-2 rounded-full mt-1 animate-pulse" style="background:#DC2626"></span>
        </div>
        <?php if ($a['image_url']): ?>
        <div class="flex-shrink-0 rounded-lg overflow-hidden" style="width:90px;height:68px;background:var(--c-surface2)">
          <img src="<?= h($a['image_url']) ?>" alt="" loading="lazy" class="w-full h-full object-cover">
        </div>
        <?php endif; ?>
        <div class="flex-1 min-w-0">
          <span class="cat-badge inline-block mb-1" style="background:<?= h(category_color($a['category_color'])) ?>">
            <?= h($a['category_name_np']?:$a['category_name']) ?>
          </span>
          <h2 class="font-bold leading-snug line-clamp-2 group-hover:underline"><?= h($a['title']) ?></h2>
          <div class="flex items-center gap-3 mt-1 text-xs" style="color:var(--c-muted)">
            <span class="flex items-center gap-1"><?= icon('clock','w-3 h-3') ?> <?= time_ago($a['published_at']??$a['created_at']) ?></span>
            <span class="flex items-center gap-1"><?= icon('eye','w-3 h-3') ?> <?= np_number((int)$a['views']) ?></span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php render_pagination($pag); ?>
    <?php endif; ?>

  </div>

  <!-- Sidebar -->
  <aside class="space-y-5">
    <?php render_ads('sidebar-top'); ?>

    <!-- Trending in sidebar -->
    <div class="sidebar-card">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('trending-up','w-4 h-4') ?> ट्रेन्डिङ</span>
      </div>
      <?php foreach (get_trending_articles(6) as $i => $pop): ?>
      <div class="popular-item">
        <span class="popular-num"><?= $i+1 ?></span>
        <div><a href="/article/<?= h($pop['slug']) ?>" class="ptitle hover:underline"><?= h($pop['title']) ?></a>
        <div class="text-xs mt-0.5" style="color:var(--c-muted)"><?= time_ago($pop['published_at']??$pop['created_at']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Popular tags -->
    <?php $all_tags = get_tags(); ?>
    <?php if (!empty($all_tags)): ?>
    <div class="sidebar-card">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('tags','w-4 h-4') ?> ट्यागहरू</span>
      </div>
      <div class="flex flex-wrap gap-2">
        <?php foreach (array_slice($all_tags,0,15) as $t): ?>
        <a href="/tag/<?= h($t['slug']) ?>" class="tag-cloud-item">#<?= h($t['name']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </aside>
</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
