<?php
$article = get_article_by_slug($_slug ?? '');
if (!$article) {
    http_response_code(404);
    require SRC_DIR . '/pages/404.php';
    exit;
}
increment_views((int)$article['id']);

// Reading time estimate
$word_count   = str_word_count(strip_tags($article['content'] . ' ' . $article['content_np']));
$reading_mins = max(1, (int)round($word_count / 200));

$page_title = $article['title'] . ' — ' . site_name();
$page_desc  = excerpt($article['summary'], 25);

// Share URLs
$current_url_full = (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']==='on'?'https':'http')
    . '://' . ($_SERVER['HTTP_HOST']??'localhost')
    . '/article/' . $article['slug'];
$share_title = urlencode($article['title']);
$share_url   = urlencode($current_url_full);

$related = get_articles(['status'=>'published','category_slug'=>$article['category_slug'],'limit'=>7]);
$related = array_slice(array_filter($related, fn($r)=>$r['id']!==$article['id']), 0, 6);
$popular = get_popular_articles(5);
$all_tags= get_tags();

require SRC_DIR . '/layout/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <!-- ════ Article ════ -->
  <article class="lg:col-span-2 rounded-lg" style="background:var(--c-surface);border:1px solid var(--c-border)">
    <div class="p-5 md:p-6">

      <!-- Breadcrumb -->
      <nav class="text-xs mb-4 flex items-center gap-1 flex-wrap" style="color:var(--c-muted)">
        <a href="/" class="hover:underline">🏠 गृहपृष्ठ</a>
        <span>&rsaquo;</span>
        <a href="/category/<?= h($article['category_slug']) ?>" class="hover:underline">
          <?= h($article['category_name_np']?:$article['category_name']) ?>
        </a>
      </nav>

      <!-- Badges row -->
      <div class="flex items-center gap-2 mb-3 flex-wrap">
        <span class="cat-badge" style="background:<?= h(category_color($article['category_color'])) ?>">
          <?= h($article['category_name_np']?:$article['category_name']) ?>
        </span>
        <span class="lang-badge lang-<?= h($article['language']??'np') ?>">
          <?= ($article['language']??'np')==='en'?'English':'नेपाली' ?>
        </span>
        <?php if ($article['featured']): ?>
          <span class="badge badge-blue">⭐ Featured</span>
        <?php endif; ?>
        <span class="reading-time">📖 <?= np_number($reading_mins) ?> मिनेट पढाइ</span>
      </div>

      <!-- Title -->
      <h1 class="text-2xl font-extrabold leading-tight mb-1"><?= h($article['title']) ?></h1>
      <?php if (!empty($article['title_np']) && $article['title_np']!==$article['title']): ?>
        <h2 class="text-base mb-3 font-medium" style="color:var(--c-muted)"><?= h($article['title_np']) ?></h2>
      <?php endif; ?>

      <!-- Meta bar -->
      <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm py-3 mb-4"
           style="border-top:1px solid var(--c-border);border-bottom:1px solid var(--c-border);color:var(--c-muted)">
        <span class="flex items-center gap-1 font-semibold" style="color:var(--c-text)">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          <a href="/author/<?= h($article['author_slug']) ?>" class="hover:underline">
            <?= h($article['author_name']) ?>
          </a>
        </span>
        <span>📅 <?= format_date($article['published_at']??$article['created_at'],true) ?></span>
        <span>👁️ <?= np_number((int)($article['views']+1)) ?> पठन</span>
        <?php if ($article['updated_at'] && $article['updated_at']!==$article['created_at']): ?>
          <span class="text-xs" style="color:var(--c-muted)">अपडेट: <?= time_ago($article['updated_at']) ?></span>
        <?php endif; ?>
      </div>

      <!-- Toolbar: font size + print -->
      <div class="article-toolbar" x-data="{
          sizes: ['text-sm','text-md','text-lg','text-xl'],
          cur: localStorage.getItem('art-font')||'text-md',
          setSize(s) { this.cur=s; localStorage.setItem('art-font',s);
            document.querySelectorAll('.article-content').forEach(e=>{
              e.classList.remove(...this.sizes); e.classList.add(s);
            });
          }
        }" x-init="setSize(cur)">
        <span class="text-xs font-semibold mr-1" style="color:var(--c-muted)">अक्षर आकार:</span>
        <button class="font-btn" style="font-size:0.7rem" @click="setSize('text-sm')">A-</button>
        <button class="font-btn" style="font-size:0.9rem" @click="setSize('text-md')">A</button>
        <button class="font-btn" style="font-size:1.05rem" @click="setSize('text-lg')">A+</button>
        <button class="font-btn" style="font-size:1.2rem" @click="setSize('text-xl')">A++</button>
        <button class="print-btn ml-auto" onclick="window.print()">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
          </svg>
          प्रिन्ट
        </button>
      </div>

      <!-- Featured image -->
      <?php if ($article['image_url']): ?>
      <figure class="mb-5 rounded-lg overflow-hidden">
        <img src="<?= h($article['image_url']) ?>" alt="<?= h($article['title']) ?>"
             class="w-full object-cover" style="max-height:460px;border-radius:8px">
        <figcaption class="text-xs text-center mt-1" style="color:var(--c-muted)"><?= h($article['title']) ?></figcaption>
      </figure>
      <?php endif; ?>

      <!-- Summary box -->
      <div class="mb-5 px-4 py-3 rounded-r text-sm font-medium leading-relaxed"
           style="background:var(--c-surface2);border-left:4px solid var(--c-primary);color:var(--c-text2)">
        <?= h($article['summary']) ?>
      </div>

      <!-- Article body -->
      <div class="article-content text-md prose max-w-none mb-4">
        <?= $article['content'] ?>
      </div>

      <!-- Mid-article ad -->
      <?php render_ads('article-middle'); ?>

      <!-- English version -->
      <?php if (!empty($article['content_np']) && $article['content_np']!==$article['content']): ?>
      <div class="mt-6 pt-4" style="border-top:1px solid var(--c-border)">
        <div class="flex items-center gap-2 mb-3">
          <span class="lang-badge lang-en">English Version</span>
        </div>
        <div class="article-content text-md prose max-w-none" lang="en">
          <?= $article['content_np'] ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Tags -->
      <?php if (!empty($article['tags'])): ?>
      <div class="mt-5 pt-4" style="border-top:1px solid var(--c-border)">
        <span class="text-sm font-bold mr-2" style="color:var(--c-text2)">🏷️ ट्यागहरू:</span>
        <?php foreach ($article['tags'] as $tag): ?>
          <a href="/search?q=<?= urlencode($tag['name']) ?>"
             class="inline-block text-xs font-semibold px-2 py-1 rounded mr-1 mb-1 transition-all hover:bg-red-700 hover:text-white"
             style="background:var(--c-tag-bg);color:var(--c-tag-text)">
            #<?= h($tag['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- ── Social Share bar ── -->
      <div class="share-bar">
        <span class="label">सेयर गर्नुस्:</span>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $share_url ?>" target="_blank" rel="noopener" class="share-btn share-fb">
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
          Facebook
        </a>
        <a href="https://twitter.com/intent/tweet?url=<?= $share_url ?>&text=<?= $share_title ?>" target="_blank" rel="noopener" class="share-btn share-twitter">
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
          Twitter
        </a>
        <a href="https://api.whatsapp.com/send?text=<?= $share_title ?>%20<?= $share_url ?>" target="_blank" rel="noopener" class="share-btn share-whatsapp">
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          WhatsApp
        </a>
        <a href="viber://forward?text=<?= $share_title ?>%20<?= $share_url ?>" class="share-btn share-viber">
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.398.002C8.555-.028 2.917.838.986 8.37c-.5 1.944-.73 4.576.188 7.794.742 2.637 2.65 4.868 5.132 5.944l.008 2.504s-.017.44.274.528c.354.108.562-.226.9-.586.175-.187.455-.505.72-.811l.023-.027c2.59 2.19 5.916 2.694 7.69 2.264 4.012-.986 5.303-3.898 5.478-4.79.38-1.948.315-4.463-.534-6.565-.263-.655-.642-1.26-1.07-1.797.4-1.17.527-2.63.27-3.874-.24-1.15-.845-2.253-1.67-3.026-1.408-1.32-3.24-1.842-5.997-1.92z"/></svg>
          Viber
        </a>
        <button class="share-btn share-copy" onclick="navigator.clipboard.writeText('<?= h($current_url_full) ?>').then(()=>this.textContent='✓ कपी भयो!').catch(()=>{})">
          📋 कपी
        </button>
      </div>

      <!-- Author bio -->
      <?php if ($article['author_bio']): ?>
      <div class="mt-5 p-4 rounded-lg flex gap-4" style="background:var(--c-surface2);border:1px solid var(--c-border)">
        <?php if ($article['author_avatar']): ?>
          <img src="<?= h($article['author_avatar']) ?>" alt="" class="w-16 h-16 rounded-full object-cover flex-shrink-0" style="border:2px solid var(--c-border)">
        <?php else: ?>
          <div class="w-16 h-16 rounded-full flex items-center justify-center flex-shrink-0" style="background:var(--c-tag-bg)">
            <span class="font-bold text-2xl" style="color:var(--c-primary)"><?= mb_substr($article['author_name'],0,1) ?></span>
          </div>
        <?php endif; ?>
        <div>
          <div class="font-bold text-sm">
            ✍️ <?= h($article['author_name']) ?>
            <?php if ($article['author_name_np']): ?>
              <span class="font-normal text-xs" style="color:var(--c-muted)">(<?= h($article['author_name_np']) ?>)</span>
            <?php endif; ?>
          </div>
          <p class="text-xs mt-1 leading-relaxed" style="color:var(--c-muted)"><?= h($article['author_bio']) ?></p>
          <a href="/author/<?= h($article['author_slug']) ?>"
             class="text-xs font-semibold mt-1 inline-block hover:underline" style="color:var(--c-primary-lt)">
            सबै लेखहरू &rarr;
          </a>
        </div>
      </div>
      <?php endif; ?>

      <!-- Bottom ad -->
      <?php render_ads('article-bottom'); ?>

    </div><!-- /article inner -->

    <!-- ── Related articles at bottom ── -->
    <?php if (!empty($related)): ?>
    <div class="px-5 md:px-6 pb-6 pt-2" style="border-top:1px solid var(--c-border)">
      <div class="section-heading mb-4"><span>🔗 सम्बन्धित समाचार</span></div>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
        <?php foreach (array_slice($related,0,3) as $rel): ?>
        <a href="/article/<?= h($rel['slug']) ?>" class="article-card block group">
          <div class="img-wrap">
            <?php if ($rel['image_url']): ?><img src="<?= h($rel['image_url']) ?>" alt="" loading="lazy"><?php endif; ?>
          </div>
          <div class="p-3">
            <h3 class="title text-xs group-hover:underline"><?= h($rel['title']) ?></h3>
            <p class="meta mt-1"><?= time_ago($rel['published_at']??$rel['created_at']) ?></p>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </article>

  <!-- ════ Sidebar ════ -->
  <aside>

    <!-- Top sidebar ad -->
    <?php render_ads('sidebar-top'); ?>

    <!-- Popular articles -->
    <?php if (!empty($popular)): ?>
    <div class="sidebar-card mb-5">
      <div class="section-heading mb-3"><span>🔥 सबैभन्दा पढिएका</span></div>
      <?php foreach ($popular as $i => $pop): ?>
      <div class="popular-item">
        <span class="popular-num"><?= $i+1 ?></span>
        <div>
          <a href="/article/<?= h($pop['slug']) ?>" class="ptitle block hover:underline"><?= h($pop['title']) ?></a>
          <div class="pmeta">👁️ <?= np_number((int)$pop['views']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- More from same category -->
    <?php if (!empty($related)): ?>
    <div class="sidebar-card mb-5">
      <div class="section-heading mb-3">
        <span><?= h($article['category_name_np']?:$article['category_name']) ?> बाट</span>
      </div>
      <?php foreach (array_slice($related,3,3) as $rel): ?>
      <div class="sidebar-article">
        <a href="/article/<?= h($rel['slug']) ?>" class="thumb">
          <?php if ($rel['image_url']): ?><img src="<?= h($rel['image_url']) ?>" alt="" loading="lazy"><?php endif; ?>
        </a>
        <div class="info">
          <a href="/article/<?= h($rel['slug']) ?>" class="title block hover:underline"><?= h($rel['title']) ?></a>
          <div class="meta"><?= time_ago($rel['published_at']??$rel['created_at']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Bottom sidebar ad -->
    <?php render_ads('sidebar-bottom'); ?>

    <!-- Hot tags -->
    <?php if (!empty($all_tags)): ?>
    <div class="sidebar-card">
      <div class="section-heading mb-3"><span>🏷️ ट्यागहरू</span></div>
      <div class="tag-cloud">
        <?php foreach ($all_tags as $tag): ?>
          <a href="/search?q=<?= urlencode($tag['name']) ?>">#<?= h($tag['name']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </aside>

</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
