<?php
$q        = trim($_GET['q'] ?? '');
$page_num = max(1, (int)($_GET['page'] ?? 1));
$articles = [];
$total    = 0;
$pager    = null;
if ($q !== '') {
    $total  = count_articles(['search'=>$q,'status'=>'published']);
    $pager  = paginate($total, ARTICLES_PER_PAGE, $page_num, "/search?q=".urlencode($q)."&page=%d");
    $articles = get_articles(['search'=>$q,'status'=>'published','limit'=>$pager['per_page'],'offset'=>$pager['offset']]);
}
$page_title = $q ? "\"$q\" खोज — " . site_name() : "खोज्नुस् — " . site_name();
$page_desc  = $q ? "\"$q\" को खोज नतिजाहरू।" : 'समाचार खोज्नुस्।';
require SRC_DIR . '/layout/header.php';
?>
<div class="max-w-3xl mx-auto">
  <div class="section-heading mb-4"><span>समाचार खोज्नुस्</span></div>
  <form method="GET" action="/search" class="flex gap-2 mb-6">
    <input type="search" name="q" value="<?= h($q) ?>"
           placeholder="समाचार खोज्नुस्... (नेपाली वा English)"
           class="form-control flex-1 text-base">
    <button type="submit" class="btn btn-primary px-6">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
      </svg>
      खोज्नुस्
    </button>
  </form>
  <?php if ($q): ?>
    <p class="text-sm mb-4" style="color:var(--c-muted)">
      "<strong><?= h($q) ?></strong>" को लागि <strong><?= np_number($total) ?></strong> नतिजा पाइयो।
    </p>
    <?php if (empty($articles)): ?>
      <div class="text-center py-16 rounded" style="background:var(--c-surface);border:1px solid var(--c-border)">
        <p class="text-lg" style="color:var(--c-muted)">कुनै नतिजा फेला परेन।</p>
        <p class="text-sm mt-2" style="color:var(--c-muted)">अर्को शब्दले खोज्ने प्रयास गर्नुस्।</p>
      </div>
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
            <p class="text-xs line-clamp-2 mb-1" style="color:var(--c-text2)"><?= h(excerpt($a['summary'],18)) ?></p>
            <div class="meta"><?= h($a['author_name']) ?> &bull; <?= time_ago($a['published_at'] ?? $a['created_at']) ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php if ($pager && $pager['total_pages'] > 1): ?>
      <div class="pagination mb-4">
        <?php if ($pager['has_prev']): ?><a href="<?= sprintf($pager['url_pattern'], $pager['prev_page']) ?>">&laquo; अघिल्लो</a><?php endif; ?>
        <?php for ($p=max(1,$pager['current']-2);$p<=min($pager['total_pages'],$pager['current']+2);$p++): ?>
          <?php if ($p===$pager['current']): ?><span class="current"><?=$p?></span>
          <?php else: ?><a href="<?=sprintf($pager['url_pattern'],$p)?>"><?=$p?></a><?php endif; ?>
        <?php endfor; ?>
        <?php if ($pager['has_next']): ?><a href="<?= sprintf($pager['url_pattern'], $pager['next_page']) ?>">अर्को &raquo;</a><?php endif; ?>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php require SRC_DIR . '/layout/footer.php'; ?>
