<?php
$slug = $_slug ?? '';
$tag  = get_tag_by_slug($slug);
if (!$tag) { http_response_code(404); require SRC_DIR . '/pages/404.php'; exit; }

// AJAX load more
if (!empty($_GET['ajax']) && isset($_GET['offset'])) {
    $ajax_off  = max(0, (int)$_GET['offset']);
    $ajax_lim  = (int)ARTICLES_PER_PAGE;
    $ajax_arts = get_articles(['status'=>'published','tag_slug'=>$slug,'limit'=>$ajax_lim,'offset'=>$ajax_off]);
    $has_more  = count($ajax_arts) === $ajax_lim;
    ob_start();
    foreach ($ajax_arts as $_a):
        $_at = current_lang()==='en' ? ($_a['title_np']?:$_a['title']) : $_a['title'];
?>
<a href="/article/<?= h($_a['slug']) ?>" class="article-card group overflow-hidden flex flex-col">
  <?php if ($_a['image_url']): ?>
  <div style="height:130px;overflow:hidden;flex-shrink:0;background:var(--c-surface2)">
    <img src="<?= h($_a['image_url']) ?>" alt="" loading="lazy" style="width:100%;height:100%;object-fit:cover">
  </div>
  <?php endif; ?>
  <div class="p-3 flex-1">
    <h4 class="text-sm font-bold leading-snug line-clamp-2 group-hover:underline mb-1"><?= h($_at) ?></h4>
    <div class="text-xs flex items-center gap-2" style="color:var(--c-muted)">
      <?= icon('user','w-3 h-3') ?> <?= h($_a['author_name']) ?>
      &nbsp;<?= icon('clock','w-3 h-3') ?> <?= time_ago($_a['published_at']??$_a['created_at']) ?>
    </div>
  </div>
</a>
<?php endforeach;
    $html = ob_get_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['html' => $html, 'has_more' => $has_more, 'next_offset' => $ajax_off + $ajax_lim]);
    exit;
}

$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = ARTICLES_PER_PAGE;
$opts     = ['status'=>'published','tag_slug'=>$slug];
$total    = count_articles($opts);
$pag      = paginate($total, $per_page, $page, "/tag/$slug?page={page}");
$articles = get_articles(array_merge($opts, ['limit'=>$per_page,'offset'=>$pag['offset']]));

$page_title = '#' . h($tag['name']) . ' — ' . site_name();
$page_desc  = 'Tag: ' . h($tag['name']);

require SRC_DIR . '/layout/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2">

    <!-- Tag header -->
    <div class="flex items-center gap-3 mb-5 p-4 rounded-xl" style="background:var(--c-surface);border:1px solid var(--c-border)">
      <span class="flex items-center justify-center w-10 h-10 rounded-full" style="background:var(--c-primary)">
        <?= icon('tag','w-5 h-5 text-white') ?>
      </span>
      <div>
        <h1 class="text-lg font-extrabold" style="color:var(--c-text)">#<?= h($tag['name']) ?></h1>
        <p class="text-xs" style="color:var(--c-muted)"><?= np_number($total) ?> समाचार</p>
      </div>
    </div>

    <!-- Articles -->
    <div class="section-heading mb-4">
      <span class="flex items-center gap-2"><?= icon('newspaper','w-4 h-4') ?> सम्बन्धित समाचार</span>
    </div>

    <?php if (empty($articles)): ?>
    <div class="stat-card text-center py-8" style="color:var(--c-muted)">कुनै समाचार छैन।</div>
    <?php else: ?>
    <div class="space-y-4">
      <?php foreach ($articles as $a): ?>
      <a href="/article/<?= h($a['slug']) ?>"
         class="flex gap-4 p-3 rounded-lg group hover:shadow-md transition-all"
         style="background:var(--c-surface);border:1px solid var(--c-border)">
        <?php if ($a['image_url']): ?>
        <div class="flex-shrink-0 rounded-lg overflow-hidden" style="width:100px;height:75px;background:var(--c-surface2)">
          <img src="<?= h($a['image_url']) ?>" alt="" loading="lazy" class="w-full h-full object-cover">
        </div>
        <?php endif; ?>
        <div class="flex-1 min-w-0">
          <span class="cat-badge inline-block mb-1" style="background:<?= h(category_color($a['category_color'])) ?>">
            <?= h($a['category_name_np']?:$a['category_name']) ?>
          </span>
          <h2 class="font-bold leading-snug line-clamp-2 group-hover:underline">
            <?= h(current_lang()==='en' ? ($a['title_np']?:$a['title']) : $a['title']) ?>
          </h2>
          <div class="flex items-center gap-3 mt-1 text-xs" style="color:var(--c-muted)">
            <span class="flex items-center gap-1"><?= icon('user','w-3 h-3') ?> <?= h($a['author_name']) ?></span>
            <span class="flex items-center gap-1"><?= icon('clock','w-3 h-3') ?> <?= time_ago($a['published_at']??$a['created_at']) ?></span>
            <span class="flex items-center gap-1"><?= icon('eye','w-3 h-3') ?> <?= np_number((int)$a['views']) ?></span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <?php render_pagination($pag); ?>

    <!-- Load More -->
    <?php if ($total > $per_page): ?>
    <div class="mt-5">
      <div id="cat-more-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4"></div>
      <div class="text-center">
        <button class="load-more-btn"
                data-url="/tag/<?= h($slug) ?>?ajax=1"
                data-offset="<?= $per_page ?>"
                onclick="loadMore(this)">
          <?= icon('plus-circle','w-4 h-4') ?> थप समाचार लोड गर्नुस्
        </button>
      </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

  </div>

  <!-- Sidebar -->
  <aside class="space-y-5">
    <?php render_ads('sidebar-top'); ?>
    <div class="sidebar-card">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('flame','w-4 h-4') ?> सर्वाधिक पढिएका</span>
      </div>
      <?php foreach (get_popular_articles(5) as $i => $pop): ?>
      <div class="popular-item">
        <span class="popular-num"><?= $i+1 ?></span>
        <div><a href="/article/<?= h($pop['slug']) ?>" class="ptitle hover:underline"><?= h($pop['title']) ?></a></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- All tags -->
    <div class="sidebar-card">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('tags','w-4 h-4') ?> ट्यागहरू</span>
      </div>
      <div class="flex flex-wrap gap-2">
        <?php foreach (get_all_tags() as $t): ?>
        <a href="/tag/<?= h($t['slug']) ?>"
           class="tag-cloud-item <?= $t['slug']===$slug ? 'active' : '' ?>">
          #<?= h($t['name']) ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </aside>
</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
