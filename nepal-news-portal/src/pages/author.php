<?php
$author = db_fetch("SELECT * FROM authors WHERE slug=?", [$_author_slug ?? '']);
if (!$author) { http_response_code(404); require SRC_DIR . '/pages/404.php'; exit; }

$page_num = max(1, (int)($_GET['page'] ?? 1));
$total    = count_articles(['author_slug'=>$author['slug'],'status'=>'published']);
$pager    = paginate($total, ARTICLES_PER_PAGE, $page_num, "/author/{$author['slug']}?page=%d");
$articles = get_articles(['author_slug'=>$author['slug'],'status'=>'published','limit'=>$pager['per_page'],'offset'=>$pager['offset']]);

$page_title = ($author['name_np'] ?: $author['name']) . ' — ' . site_name();
$page_desc  = ($author['name_np'] ?: $author['name']) . 'का सबै लेखहरू।';
require SRC_DIR . '/layout/header.php';
?>
<div class="max-w-3xl mx-auto">
  <div class="rounded p-6 mb-6 flex items-start gap-5" style="background:var(--c-surface);border:1px solid var(--c-border)">
    <?php if ($author['avatar_url']): ?>
      <img src="<?= h($author['avatar_url']) ?>" alt="<?= h($author['name']) ?>"
           class="w-20 h-20 rounded-full object-cover flex-shrink-0" style="border:2px solid var(--c-border)">
    <?php else: ?>
      <div class="w-20 h-20 rounded-full flex items-center justify-center flex-shrink-0"
           style="background:var(--c-tag-bg)">
        <span class="text-3xl font-bold" style="color:var(--c-primary)"><?= mb_substr($author['name'],0,1) ?></span>
      </div>
    <?php endif; ?>
    <div>
      <h1 class="text-xl font-extrabold"><?= h($author['name']) ?></h1>
      <?php if ($author['name_np']): ?>
        <p class="text-sm" style="color:var(--c-muted)"><?= h($author['name_np']) ?></p>
      <?php endif; ?>
      <?php if ($author['bio']): ?>
        <p class="text-sm mt-2 leading-relaxed" style="color:var(--c-text2)"><?= h($author['bio']) ?></p>
      <?php endif; ?>
      <p class="text-xs mt-2" style="color:var(--c-muted)"><?= np_number($total) ?> लेख प्रकाशित</p>
    </div>
  </div>
  <div class="section-heading mb-4"><span>प्रकाशित लेखहरू</span></div>
  <?php if (empty($articles)): ?>
    <div class="text-center py-12" style="color:var(--c-muted)">कुनै लेख छैन।</div>
  <?php else: ?>
    <div class="space-y-4 mb-6">
      <?php foreach ($articles as $a): ?>
      <a href="/article/<?= h($a['slug']) ?>" class="article-card flex gap-4 p-4 group block">
        <div class="img-wrap flex-shrink-0 rounded-sm" style="width:110px;height:82px;aspect-ratio:unset">
          <?php if ($a['image_url']): ?><img src="<?= h($a['image_url']) ?>" alt="" loading="lazy"><?php endif; ?>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-1 mb-1">
            <span class="cat-badge" style="background:<?= h(category_color($a['category_color'])) ?>">
              <?= h($a['category_name_np'] ?: $a['category_name']) ?>
            </span>
            <span class="lang-badge lang-<?= h($a['language']??'np') ?>"><?= ($a['language']??'np')==='en'?'EN':'NP' ?></span>
          </div>
          <h2 class="font-bold text-sm leading-snug group-hover:underline mb-1"><?= h($a['title']) ?></h2>
          <div class="meta"><?= time_ago($a['published_at'] ?? $a['created_at']) ?> &bull; <?= np_number((int)$a['views']) ?> पठन</div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php if ($pager['total_pages'] > 1): ?>
    <div class="pagination mb-4">
      <?php if ($pager['has_prev']): ?><a href="<?= sprintf($pager['url_pattern'], $pager['prev_page']) ?>">&laquo;</a><?php endif; ?>
      <?php for ($p=max(1,$pager['current']-2); $p<=min($pager['total_pages'],$pager['current']+2); $p++): ?>
        <?php if ($p===$pager['current']): ?><span class="current"><?=$p?></span>
        <?php else: ?><a href="<?=sprintf($pager['url_pattern'],$p)?>"><?=$p?></a><?php endif; ?>
      <?php endfor; ?>
      <?php if ($pager['has_next']): ?><a href="<?= sprintf($pager['url_pattern'], $pager['next_page']) ?>">&raquo;</a><?php endif; ?>
    </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php require SRC_DIR . '/layout/footer.php'; ?>
