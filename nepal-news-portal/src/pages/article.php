<?php
$slug    = $_slug ?? '';
$article = get_article_by_slug($slug);
if (!$article) { http_response_code(404); require SRC_DIR . '/pages/404.php'; exit; }

increment_views($article['id']);

$lang        = current_lang();
$title_main  = $lang==='en' ? ($article['title_np']?:$article['title']) : $article['title'];
$content_main= $lang==='en' ? ($article['content_np']?:$article['content']) : $article['content'];
$summary_main= $lang==='en' ? ($article['summary_np']?:$article['summary']) : $article['summary'];

$page_title  = h($title_main) . ' — ' . site_name();
$page_desc   = excerpt($summary_main ?: strip_tags($content_main ?? ''), 25);
$og_image    = $article['image_url'] ?? '';
$og_type     = 'article';

$related = get_articles([
    'status'      => 'published',
    'category_id' => $article['category_id'],
    'exclude_id'  => $article['id'],
    'limit'       => 4,
]);
$popular = get_popular_articles(5);

// ── JSON-LD Structured Data (schema.org/NewsArticle) ──────
$_base_url    = rtrim(setting('site_url', ''), '/');
$canonical_url = $_base_url . '/article/' . $article['slug'];
$json_ld = json_encode([
    '@context'         => 'https://schema.org',
    '@type'            => 'NewsArticle',
    'headline'         => strip_tags($title_main),
    'description'      => $page_desc,
    'image'            => $article['image_url'] ? [$_base_url . $article['image_url']] : [],
    'datePublished'    => date('c', strtotime($article['published_at'] ?? $article['created_at'])),
    'dateModified'     => date('c', strtotime($article['updated_at'] ?? $article['published_at'] ?? $article['created_at'])),
    'author'           => [
        '@type' => 'Person',
        'name'  => $article['author_name'] ?? 'संवाददाता',
        'url'   => $article['author_slug'] ? $_base_url . '/author/' . $article['author_slug'] : '',
    ],
    'publisher'        => [
        '@type' => 'Organization',
        'name'  => site_name_en(),
        'logo'  => ['@type' => 'ImageObject', 'url' => $_base_url . site_logo_url()],
    ],
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical_url],
    'inLanguage'       => current_lang() === 'en' ? 'en-NP' : 'ne-NP',
    'articleSection'   => $article['category_name_np'] ?: $article['category_name'],
    'keywords'         => implode(', ', array_column($article['tags'] ?? [], 'name')),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

require SRC_DIR . '/layout/header.php';
?>
<?php if (!empty($json_ld)): ?>
<script type="application/ld+json"><?= $json_ld ?></script>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <!-- ── Main article ── -->
  <article class="lg:col-span-2">

    <!-- Breadcrumb -->
    <nav class="breadcrumb mb-4">
      <a href="/"><?= icon('home','w-3 h-3') ?> <?= lang_label('गृहपृष्ठ','Home') ?></a>
      <span>›</span>
      <a href="/category/<?= h($article['category_slug']) ?>"><?= h($article['category_name_np']?:$article['category_name']) ?></a>
      <span>›</span>
      <span><?= h(mb_substr($title_main,0,40)) ?>…</span>
    </nav>

    <div class="bg-white rounded-xl border p-5 sm:p-7" style="border-color:var(--c-border);background:var(--c-surface)">

      <!-- Badges -->
      <div class="flex flex-wrap items-center gap-2 mb-3">
        <span class="cat-badge" style="background:<?= h(category_color($article['category_color'])) ?>">
          <?= h($article['category_name_np']?:$article['category_name']) ?>
        </span>
        <?php if ($article['is_breaking']): ?>
          <span class="badge badge-red flex items-center gap-1"><?= icon('zap','w-2.5 h-2.5') ?> ब्रेकिङ</span>
        <?php endif; ?>
        <?php if ($article['featured']): ?>
          <span class="badge badge-yellow flex items-center gap-1"><?= icon('star','w-2.5 h-2.5') ?> विशेष</span>
        <?php endif; ?>
      </div>

      <!-- Title -->
      <h1 class="text-2xl sm:text-3xl font-extrabold mb-3 leading-tight" style="color:var(--c-text)">
        <?= h($title_main) ?>
      </h1>

      <!-- Meta bar -->
      <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 mb-4 pb-4 text-xs" style="color:var(--c-muted);border-bottom:1px solid var(--c-border)">
        <?php if ($article['author_name']): ?>
        <a href="/author/<?= h($article['author_slug']) ?>" class="flex items-center gap-1.5 font-semibold hover:underline" style="color:var(--c-text2)">
          <?php if ($article['author_avatar']): ?>
            <img src="<?= h($article['author_avatar']) ?>" alt="" class="w-6 h-6 rounded-full object-cover">
          <?php else: ?>
            <?= icon('user-round','w-4 h-4') ?>
          <?php endif; ?>
          <?= h($article['author_name']) ?>
        </a>
        <?php endif; ?>
        <span class="flex items-center gap-1">
          <?= icon('calendar','w-3.5 h-3.5') ?>
          <?= format_date($article['published_at']??$article['created_at'], false) ?>
        </span>
        <span class="flex items-center gap-1" title="बिक्रम सम्वत्">
          <?= icon('calendar-days','w-3 h-3') ?>
          <?= format_bs_date($article['published_at'] ?? $article['created_at']) ?>
        </span>
        <span class="flex items-center gap-1">
          <?= icon('eye','w-3.5 h-3.5') ?> <?= np_number((int)$article['views']) ?> दृश्य
        </span>
        <span class="flex items-center gap-1 reading-time">
          <?= icon('book-open','w-3.5 h-3.5') ?> <?= reading_time_label($article['content']??'') ?>
        </span>
        <!-- Font size controls -->
        <div class="flex items-center gap-1 ml-auto">
          <button class="font-btn" onclick="changeFontSize(-1)" title="Decrease font">A-</button>
          <button class="font-btn" onclick="changeFontSize(1)"  title="Increase font">A+</button>
          <button class="font-btn print-btn" onclick="window.print()" title="Print"><?= icon('printer','w-3 h-3') ?></button>
        </div>
      </div>

      <!-- Summary box -->
      <?php if ($summary_main): ?>
      <div class="p-4 mb-5 rounded-lg text-sm italic font-medium" style="background:var(--c-surface2);border-left:3px solid var(--c-primary-lt);color:var(--c-text2)">
        <?= h($summary_main) ?>
      </div>
      <?php endif; ?>

      <!-- Featured image -->
      <?php if ($article['image_url']): ?>
      <div class="mb-5 rounded-lg overflow-hidden">
        <img src="<?= h($article['image_url']) ?>" alt="<?= h($title_main) ?>"
             class="w-full object-cover" style="max-height:480px">
      </div>
      <?php endif; ?>

      <!-- Article body -->
      <div class="article-content" id="article-body">
        <?= $content_main ?>
      </div>

      <!-- Mid-article ad -->
      <?php render_ads('article-middle'); ?>

      <!-- Tags -->
      <?php if (!empty($article['tags'])): ?>
      <div class="mt-6 flex flex-wrap items-center gap-2">
        <span class="text-xs font-bold flex items-center gap-1" style="color:var(--c-muted)">
          <?= icon('hash','w-3.5 h-3.5') ?> ट्यागहरू:
        </span>
        <?php foreach ($article['tags'] as $tag): ?>
          <a href="/search?q=<?= urlencode($tag['name']) ?>" class="tag-cloud-item">
            <?= h($tag['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Share bar -->
      <div class="mt-6 pt-4" style="border-top:1px solid var(--c-border)">
        <p class="text-xs font-bold mb-3 flex items-center gap-1" style="color:var(--c-muted)">
          <?= icon('share-2','w-3.5 h-3.5') ?> शेयर गर्नुस्:
        </p>
        <div class="share-bar">
          <?php
          $url   = urlencode('https://'.$_SERVER['HTTP_HOST'].'/article/'.h($article['slug']));
          $title = urlencode($title_main);
          ?>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $url ?>" target="_blank" rel="noopener" class="share-btn share-fb">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg> Facebook
          </a>
          <a href="https://twitter.com/intent/tweet?url=<?= $url ?>&text=<?= $title ?>" target="_blank" rel="noopener" class="share-btn share-twitter">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.713 6.231z"/></svg> Twitter
          </a>
          <a href="https://wa.me/?text=<?= $title ?>%20<?= $url ?>" target="_blank" rel="noopener" class="share-btn share-whatsapp">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.521.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg> WhatsApp
          </a>
          <a href="viber://forward?text=<?= $title ?>%20<?= $url ?>" class="share-btn share-viber">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.4 0C5.5.3 1.2 5 1.5 10.9c.1 2.4.9 4.6 2.4 6.4L2.5 21l3.9-1.4C8 20.5 9.7 21 11.5 21c5.9 0 10.5-4.7 10.5-10.5S17.3 0 11.4 0zM16 15.1c-.3.8-1.5 1.5-2.2 1.5-.6 0-2.6-.5-5-3-2.4-2.5-2.8-4.5-2.8-5.1 0-.7.6-1.9 1.4-2.2.4-.1.8-.1 1 .3.3.4.8 1.7.9 1.8.1.2.1.4 0 .6-.2.2-.3.4-.5.5-.1.2-.3.3-.1.6.7 1.1 1.5 1.8 2.6 2.4.2.1.4.1.6-.1.2-.2.6-.7.8-.9.2-.2.4-.2.6-.1.6.3 1.3.7 1.8 1 .1.2.1.8-.1 1.7z"/></svg> Viber
          </a>
          <button class="share-btn share-copy" onclick="navigator.clipboard.writeText(window.location.href).then(()=>alert('लिंक कपि गरियो!'))">
            <?= icon('copy','w-3.5 h-3.5') ?> Copy Link
          </button>
        </div>
      </div>

      <!-- Author bio -->
      <?php if ($article['author_bio'] || $article['author_avatar']): ?>
      <div class="mt-6 p-4 rounded-lg flex gap-4" style="background:var(--c-surface2);border:1px solid var(--c-border)">
        <?php if ($article['author_avatar']): ?>
          <img src="<?= h($article['author_avatar']) ?>" alt="" class="w-14 h-14 rounded-full object-cover flex-shrink-0">
        <?php else: ?>
          <div class="w-14 h-14 rounded-full flex-shrink-0 flex items-center justify-center" style="background:var(--c-primary)">
            <?= icon('user','w-6 h-6 text-white') ?>
          </div>
        <?php endif; ?>
        <div>
          <a href="/author/<?= h($article['author_slug']) ?>" class="font-bold text-sm hover:underline" style="color:var(--c-primary-lt)">
            <?= h($article['author_name']) ?>
          </a>
          <?php if ($article['author_bio']): ?>
            <p class="text-xs mt-1" style="color:var(--c-text2)"><?= h($article['author_bio']) ?></p>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Related articles -->
      <?php if (!empty($related)): ?>
      <div class="mt-8">
        <div class="section-heading mb-4">
          <span class="flex items-center gap-2"><?= icon('layers','w-4 h-4') ?> सम्बन्धित समाचार</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <?php foreach (array_slice($related,0,4) as $ra): ?>
          <a href="/article/<?= h($ra['slug']) ?>" class="article-card flex gap-3 p-3 group">
            <?php if ($ra['image_url']): ?>
            <div class="flex-shrink-0 rounded overflow-hidden" style="width:72px;height:54px;background:var(--c-surface2)">
              <img src="<?= h($ra['image_url']) ?>" alt="" loading="lazy" class="w-full h-full object-cover">
            </div>
            <?php endif; ?>
            <div class="flex-1 min-w-0">
              <div class="text-sm font-semibold leading-snug line-clamp-2 group-hover:underline"><?= h($ra['title']) ?></div>
              <div class="text-xs mt-1 flex items-center gap-1" style="color:var(--c-muted)">
                <?= icon('clock','w-2.5 h-2.5') ?> <?= time_ago($ra['published_at']??$ra['created_at']) ?>
              </div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </article>

  <!-- ── Sidebar ── -->
  <aside class="lg:col-span-1 space-y-5">
    <?php render_ads('sidebar-top'); ?>
    <?php if (!empty($popular)): ?>
    <div class="sidebar-card">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('flame','w-4 h-4') ?> सर्वाधिक पढिएका</span>
      </div>
      <?php foreach ($popular as $i => $pop): ?>
      <div class="popular-item">
        <span class="popular-num"><?= $i+1 ?></span>
        <div>
          <a href="/article/<?= h($pop['slug']) ?>" class="ptitle block hover:underline"><?= h($pop['title']) ?></a>
          <div class="pmeta"><?= icon('eye','w-2.5 h-2.5') ?> <?= np_number((int)$pop['views']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php render_ads('sidebar-bottom'); ?>
  </aside>
</div>

<script>
var _fontSize = 16;
function changeFontSize(d) {
  _fontSize = Math.min(22, Math.max(13, _fontSize + d));
  document.getElementById('article-body').style.fontSize = _fontSize + 'px';
}
</script>
<style>
.tag-cloud-item {
  display:inline-block;padding:2px 9px;border-radius:20px;font-size:11.5px;font-weight:500;
  background:var(--c-surface2);color:var(--c-text2);border:1px solid var(--c-border);
  transition:background .15s,color .15s;
}
.tag-cloud-item:hover{background:var(--c-primary);color:#fff;}
</style>

<?php require SRC_DIR . '/layout/footer.php'; ?>
