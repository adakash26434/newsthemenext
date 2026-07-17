<?php
$slug   = $_slug ?? '';
$author = get_author_by_slug($slug);
if (!$author) { http_response_code(404); require SRC_DIR . '/pages/404.php'; exit; }

$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = ARTICLES_PER_PAGE;
$opts     = ['status'=>'published','author_slug'=>$slug];
$total    = count_articles($opts);
$pag      = paginate($total, $per_page, $page, "/author/$slug?page={page}");
$articles = get_articles(array_merge($opts, ['limit'=>$per_page,'offset'=>$pag['offset']]));

$page_title = h($author['name']) . ' — ' . site_name();

require SRC_DIR . '/layout/header.php';
?>

<!-- Breadcrumb -->
<nav class="breadcrumb mb-4">
  <a href="/"><?= icon('home','w-3 h-3') ?> <?= lang_label('गृहपृष्ठ','Home') ?></a>
  <span>›</span>
  <span class="current"><?= h($author['name']) ?></span>
</nav>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2">
    <!-- Author profile -->
    <div class="flex gap-5 p-6 mb-6 rounded-xl" style="background:var(--c-surface);border:1px solid var(--c-border)">
      <?php if ($author['avatar_url']): ?>
        <img src="<?= h($author['avatar_url']) ?>" alt="<?= h($author['name']) ?>"
             class="w-20 h-20 rounded-full object-cover flex-shrink-0">
      <?php else: ?>
        <div class="w-20 h-20 rounded-full flex-shrink-0 flex items-center justify-center" style="background:var(--c-primary)">
          <?= icon('user-round','w-9 h-9','w-9 h-9 inline-block align-middle flex-shrink-0') ?>
        </div>
      <?php endif; ?>
      <div>
        <h1 class="text-xl font-extrabold mb-1" style="color:var(--c-text)"><?= h($author['name']) ?></h1>
        <?php if ($author['name_np']): ?><p class="text-sm mb-2" style="color:var(--c-muted)"><?= h($author['name_np']) ?></p><?php endif; ?>
        <?php if ($author['bio']): ?><p class="text-sm leading-relaxed" style="color:var(--c-text2)"><?= h($author['bio']) ?></p><?php endif; ?>
        <div class="mt-2 flex flex-wrap items-center gap-3">
          <span class="text-xs flex items-center gap-1" style="color:var(--c-muted)">
            <?= icon('newspaper','w-3.5 h-3.5') ?> <?= np_number($total) ?> समाचार
          </span>
          <a href="/rss/author/<?= h($author['slug']) ?>" target="_blank" class="text-xs flex items-center gap-1 px-2 py-0.5 rounded" style="background:rgba(249,115,22,.15);color:#f97316;border:1px solid rgba(249,115,22,.3)">
            <?= icon('rss','w-3 h-3') ?> RSS
          </a>
          <?php if ($author['twitter_url'] ?? ''): ?>
          <a href="<?= h($author['twitter_url']) ?>" target="_blank" rel="noopener noreferrer" class="text-xs flex items-center gap-1 hover:underline" style="color:var(--c-primary)">
            <?= icon('twitter','w-3.5 h-3.5') ?> Twitter
          </a>
          <?php endif; ?>
          <?php if ($author['facebook_url'] ?? ''): ?>
          <a href="<?= h($author['facebook_url']) ?>" target="_blank" rel="noopener noreferrer" class="text-xs flex items-center gap-1 hover:underline" style="color:var(--c-primary)">
            <?= icon('facebook','w-3.5 h-3.5') ?> Facebook
          </a>
          <?php endif; ?>
          <?php if ($author['linkedin_url'] ?? ''): ?>
          <a href="<?= h($author['linkedin_url']) ?>" target="_blank" rel="noopener noreferrer" class="text-xs flex items-center gap-1 hover:underline" style="color:var(--c-primary)">
            <?= icon('linkedin','w-3.5 h-3.5') ?> LinkedIn
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Articles -->
    <div class="section-heading mb-4">
      <span class="flex items-center gap-2"><?= icon('newspaper','w-4 h-4') ?> <?= h($author['name']) ?>का समाचार</span>
    </div>
    <?php if (empty($articles)): ?>
    <div class="stat-card text-center py-8" style="color:var(--c-muted)">
      <p>कुनै समाचार छैन।</p>
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

  <aside class="space-y-5">
    <?php render_ads('sidebar-top'); ?>
    <div class="sidebar-card">
      <div class="section-heading mb-3"><span class="flex items-center gap-2"><?= icon('flame','w-4 h-4') ?> सर्वाधिक पढिएका</span></div>
      <?php foreach (get_popular_articles(5) as $i => $pop): ?>
      <div class="popular-item">
        <span class="popular-num"><?= $i+1 ?></span>
        <div><a href="/article/<?= h($pop['slug']) ?>" class="ptitle hover:underline"><?= h($pop['title']) ?></a></div>
      </div>
      <?php endforeach; ?>
    </div>
  </aside>
</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
