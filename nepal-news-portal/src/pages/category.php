<?php
$slug = $_slug ?? '';
$cat  = get_category_by_slug($slug);
if (!$cat) { http_response_code(404); require SRC_DIR . '/pages/404.php'; exit; }

$lang       = current_lang();
$cat_name   = $lang==='en' ? ($cat['name_np']?:$cat['name']) : ($cat['name']?:$cat['name_np']);
$page       = max(1, (int)($_GET['page'] ?? 1));
$per_page   = ARTICLES_PER_PAGE;
$opts       = ['status'=>'published','category_slug'=>$slug];
$total      = count_articles($opts);
$pag        = paginate($total, $per_page, $page, "/category/$slug?page={page}");
$articles   = get_articles(array_merge($opts, ['limit'=>$per_page,'offset'=>$pag['offset']]));

$page_title = h($cat_name) . ' — ' . site_name();
$page_desc  = "$cat_name श्रेणीका ताजा समाचार।";

require SRC_DIR . '/layout/header.php';
?>

<!-- Category header -->
<div class="category-header mb-6" style="background:linear-gradient(135deg,<?= h($cat['color']?:primary_color()) ?>,<?= h(category_color($cat['color'])) ?>)">
  <div class="flex items-center gap-3">
    <?php if ($cat['icon']): ?>
      <i data-lucide="<?= h($cat['icon']) ?>" class="w-8 h-8" style="color:#fff;opacity:.9"></i>
    <?php endif; ?>
    <div>
      <h1 class="text-2xl font-extrabold text-white"><?= h($cat_name) ?></h1>
      <p class="text-sm mt-1 flex items-center gap-3" style="color:rgba(255,255,255,.75)">
        <span><?= np_number($total) ?> समाचार</span>
      </p>
    </div>
    <a href="/rss/<?= h($slug) ?>" target="_blank" rel="noopener noreferrer" class="ml-auto flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.25);text-decoration:none">
      <?= icon('rss','w-3.5 h-3.5') ?> RSS
    </a>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2">
    <?php if (empty($articles)): ?>
    <div class="stat-card text-center py-10" style="color:var(--c-muted)">
      <i data-lucide="newspaper" class="w-10 h-10 mx-auto mb-3 opacity-30"></i>
      <p>यस श्रेणीमा कुनै समाचार छैन।</p>
    </div>
    <?php else: ?>
    <div class="space-y-4">
      <?php foreach ($articles as $a): ?>
      <a href="/article/<?= h($a['slug']) ?>" class="flex gap-4 p-3 rounded-lg group transition-all hover:shadow-md" style="background:var(--c-surface);border:1px solid var(--c-border)">
        <div class="flex-shrink-0 rounded-lg overflow-hidden" style="width:120px;height:90px;background:var(--c-surface2)">
          <?php if ($a['image_url']): ?>
            <img src="<?= h($a['image_url']) ?>" alt="" loading="lazy" class="w-full h-full object-cover">
          <?php endif; ?>
        </div>
        <div class="flex-1 min-w-0">
          <h2 class="font-bold text-base leading-snug line-clamp-2 group-hover:underline" style="color:var(--c-text)">
            <?= h($a['title']) ?>
          </h2>
          <?php if ($a['summary']): ?>
          <p class="text-sm mt-1 line-clamp-2" style="color:var(--c-text2)"><?= h(excerpt($a['summary'],18)) ?></p>
          <?php endif; ?>
          <div class="flex items-center gap-3 mt-2 text-xs flex-wrap" style="color:var(--c-muted)">
            <span class="flex items-center gap-1"><?= icon('user','w-3 h-3') ?> <?= h($a['author_name']) ?></span>
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
    <div class="sidebar-card">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('grid','w-4 h-4') ?> सबै श्रेणीहरू</span>
      </div>
      <?php foreach (get_categories() as $c): ?>
      <a href="/category/<?= h($c['slug']) ?>"
         class="flex items-center justify-between py-2 text-sm font-semibold hover:underline"
         style="border-bottom:1px solid var(--c-border2);<?= $c['slug']===$slug?'color:var(--c-primary-lt)':'' ?>">
        <span class="flex items-center gap-2">
          <?php if ($c['icon']): ?>
            <i data-lucide="<?= h($c['icon']) ?>" class="w-3.5 h-3.5" style="color:<?= h($c['color']?:accent_color()) ?>"></i>
          <?php else: ?>
            <span class="w-2 h-2 rounded-full" style="background:<?= h($c['color']?:accent_color()) ?>"></span>
          <?php endif; ?>
          <?= h($c['name_np']?:$c['name']) ?>
        </span>
        <span style="color:var(--c-muted);font-size:11px"><?= np_number((int)($c['article_count']??0)) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
    <?php render_ads('sidebar-bottom'); ?>
  </aside>
</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
