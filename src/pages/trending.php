<?php
$page_title = 'ट्रेन्डिङ समाचार — ' . site_name();
$page_desc  = 'नेपालका सबैभन्दा धेरै पढिएका र चर्चित समाचारहरू';

$per_page = ARTICLES_PER_PAGE * 2;
$page     = max(1, (int)($_GET['page'] ?? 1));
$total    = count_articles(['status'=>'published']);
$pag      = paginate($total, $per_page, $page, '/trending?page={page}');
$articles = get_articles([
    'status' => 'published',
    'limit'  => $per_page,
    'offset' => $pag['offset'],
    'order'  => 'a.trending_score DESC, a.views DESC',
]);

require SRC_DIR . '/layout/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-5 p-4 rounded-xl" style="background:var(--c-surface);border:1px solid var(--c-border)">
      <span class="flex items-center justify-center w-10 h-10 rounded-full animate-pulse" style="background:var(--c-primary)">
        <?= icon('trending-up','w-5 h-5 text-white') ?>
      </span>
      <div>
        <h1 class="text-lg font-extrabold" style="color:var(--c-text)">ट्रेन्डिङ समाचार</h1>
        <p class="text-xs" style="color:var(--c-muted)">सर्वाधिक चर्चित र पढिएका समाचारहरू</p>
      </div>
    </div>

    <?php if (empty($articles)): ?>
    <div class="stat-card text-center py-8" style="color:var(--c-muted)">कुनै समाचार छैन।</div>
    <?php else: ?>

    <!-- Top 3 featured -->
    <?php if ($page === 1 && count($articles) >= 3): ?>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <?php foreach (array_slice($articles, 0, 3) as $i => $a): ?>
      <a href="/article/<?= h($a['slug']) ?>"
         class="article-card group overflow-hidden relative <?= $i===0 ? 'sm:col-span-2' : '' ?>">
        <?php if ($a['image_url']): ?>
        <div style="height:<?= $i===0?'200':'140' ?>px;overflow:hidden;background:var(--c-surface2)">
          <img src="<?= h($a['image_url']) ?>" alt="" loading="<?= $i===0?'eager':'lazy' ?>"
               class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
        </div>
        <?php endif; ?>
        <div class="p-3">
          <div class="flex items-center gap-2 mb-1.5">
            <span class="text-lg font-black opacity-20"><?= $i+1 ?></span>
            <span class="cat-badge text-xs" style="background:<?= h(category_color($a['category_color'])) ?>">
              <?= h($a['category_name_np']?:$a['category_name']) ?>
            </span>
          </div>
          <h2 class="font-bold text-sm leading-snug line-clamp-2 group-hover:underline"><?= h($a['title']) ?></h2>
          <div class="flex items-center gap-3 mt-1 text-xs" style="color:var(--c-muted)">
            <span><?= icon('eye','w-3 h-3') ?> <?= np_number((int)$a['views']) ?></span>
            <span><?= icon('clock','w-3 h-3') ?> <?= time_ago($a['published_at']??$a['created_at']) ?></span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php $start = 3; else: $start = 0; endif; ?>

    <!-- Remaining list -->
    <div class="space-y-3">
      <?php foreach (array_slice($articles, $start) as $i => $a): ?>
      <a href="/article/<?= h($a['slug']) ?>"
         class="flex gap-4 p-3 rounded-lg group hover:shadow-md transition-all"
         style="background:var(--c-surface);border:1px solid var(--c-border)">
        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-black"
             style="background:var(--c-surface2);color:var(--c-muted)">
          <?= $start + $i + 1 ?>
        </div>
        <?php if ($a['image_url']): ?>
        <div class="flex-shrink-0 rounded-lg overflow-hidden" style="width:80px;height:60px;background:var(--c-surface2)">
          <img src="<?= h($a['image_url']) ?>" alt="" loading="lazy" class="w-full h-full object-cover">
        </div>
        <?php endif; ?>
        <div class="flex-1 min-w-0">
          <span class="cat-badge inline-block mb-1" style="background:<?= h(category_color($a['category_color'])) ?>">
            <?= h($a['category_name_np']?:$a['category_name']) ?>
          </span>
          <h2 class="font-bold text-sm leading-snug line-clamp-2 group-hover:underline"><?= h($a['title']) ?></h2>
          <div class="flex items-center gap-3 mt-1 text-xs" style="color:var(--c-muted)">
            <span><?= icon('eye','w-3 h-3') ?> <?= np_number((int)$a['views']) ?></span>
            <span><?= icon('user','w-3 h-3') ?> <?= h($a['author_name']) ?></span>
            <span><?= icon('clock','w-3 h-3') ?> <?= time_ago($a['published_at']??$a['created_at']) ?></span>
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

    <!-- Top authors -->
    <?php $top_authors = db_fetchAll(
        "SELECT au.name, au.name_np, au.slug, au.avatar_url, COUNT(a.id) AS cnt
         FROM authors au JOIN articles a ON a.author_id = au.id AND a.status='published'
         GROUP BY au.id ORDER BY cnt DESC LIMIT 5"
    ); ?>
    <?php if (!empty($top_authors)): ?>
    <div class="sidebar-card">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('award','w-4 h-4') ?> शीर्ष लेखकहरू</span>
      </div>
      <div class="space-y-3">
        <?php foreach ($top_authors as $i => $au): ?>
        <a href="/author/<?= h($au['slug']) ?>" class="flex items-center gap-3 hover:underline">
          <?php if ($au['avatar_url']): ?>
          <img src="<?= h($au['avatar_url']) ?>" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
          <?php else: ?>
          <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-sm font-bold text-white" style="background:var(--c-primary)">
            <?= mb_strtoupper(mb_substr($au['name'],0,1)) ?>
          </div>
          <?php endif; ?>
          <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold truncate"><?= h($au['name_np']?:$au['name']) ?></div>
            <div class="text-xs" style="color:var(--c-muted)"><?= np_number((int)$au['cnt']) ?> समाचार</div>
          </div>
          <span class="text-sm font-black opacity-20"><?= $i+1 ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Popular tags -->
    <?php $tags = get_tags(); ?>
    <?php if (!empty($tags)): ?>
    <div class="sidebar-card">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('tags','w-4 h-4') ?> लोकप्रिय ट्यागहरू</span>
      </div>
      <div class="flex flex-wrap gap-2">
        <?php foreach (array_slice($tags, 0, 20) as $t): ?>
        <a href="/tag/<?= h($t['slug']) ?>" class="tag-cloud-item">
          #<?= h($t['name']) ?>
          <?php if ($t['usage_count'] > 0): ?>
          <span class="text-xs opacity-60">(<?= $t['usage_count'] ?>)</span>
          <?php endif; ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </aside>
</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
