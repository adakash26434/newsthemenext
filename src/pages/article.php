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
$popular         = get_popular_articles(5);
$reaction_counts = get_reaction_counts((int)$article['id']);
$comments        = get_comments((int)$article['id']);
$comment_count   = count($comments);
$prev_next       = get_prev_next_articles((int)$article['id'], $article['published_at'] ?? $article['created_at']);

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
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $_base_url . '/article/' . $article['slug']],
    'inLanguage'       => current_lang() === 'en' ? 'en-NP' : 'ne-NP',
    'articleSection'   => $article['category_name_np'] ?: $article['category_name'],
    'keywords'         => implode(', ', array_column($article['tags'] ?? [], 'name')),
    'wordCount'        => str_word_count(strip_tags($article['content'] ?? '')),
    'timeRequired'     => 'PT' . reading_time($article['content'] ?? '') . 'M',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

require SRC_DIR . '/layout/header.php';
?>
<?php if (!empty($json_ld)): ?>
<script type="application/ld+json"><?= $json_ld ?></script>
<?php endif; ?>

<!-- BreadcrumbList Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "गृहपृष्ठ",
      "item": "<?= $_base_url ?>"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "<?= h($article['category_name_np'] ?: $article['category_name']) ?>",
      "item": "<?= $_base_url ?>/category/<?= h($article['category_slug']) ?>"
    },
    {
      "@type": "ListItem",
      "position": 3,
      "name": "<?= h(mb_substr($title_main, 0, 60)) ?><?php if (mb_strlen($title_main) > 60) echo '...'; ?>"
    }
  ]
}
</script>

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

      <!-- Title — karobardaily style: large, prominent, centered on desktop -->
      <h1 class="article-main-title mb-4 leading-tight" style="color:var(--c-text)">
        <?= h($title_main) ?>
      </h1>

      <!-- Author + meta row -->
      <div class="flex flex-wrap items-center gap-x-4 gap-y-2 mb-4 pb-4 text-sm" style="color:var(--c-muted);border-bottom:1px solid var(--c-border)">
        <?php if ($article['author_name']): ?>
        <a href="/author/<?= h($article['author_slug']) ?>" class="flex items-center gap-2 font-bold hover:underline" style="color:var(--c-text)">
          <?php if ($article['author_avatar']): ?>
            <img src="<?= h($article['author_avatar']) ?>" alt="" class="w-8 h-8 rounded-full object-cover ring-2" style="ring-color:var(--c-border)">
          <?php else: ?>
            <span class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background:var(--c-primary)"><?= mb_substr($article['author_name'],0,1) ?></span>
          <?php endif; ?>
          <?= h($article['author_name']) ?>
        </a>
        <?php endif; ?>
        <span class="flex items-center gap-1 text-xs">
          <?= icon('clock','w-3.5 h-3.5') ?>
          <?= time_ago_np($article['published_at']??$article['created_at']) ?>
        </span>
        <span class="flex items-center gap-1 text-xs" title="बिक्रम सम्वत्">
          <?= icon('calendar-days','w-3 h-3') ?>
          <?= format_bs_date($article['published_at'] ?? $article['created_at']) ?>
        </span>
        <span class="flex items-center gap-1 text-xs">
          <?= icon('eye','w-3.5 h-3.5') ?> <?= np_number((int)$article['views']) ?> दृश्य
        </span>
        <span class="flex items-center gap-1 text-xs reading-time">
          <?= icon('book-open','w-3.5 h-3.5') ?> <?= reading_time_label($article['content']??'') ?>
        </span>
        <!-- Font size + action controls -->
        <div class="flex items-center gap-1 ml-auto flex-wrap">
          <button class="font-btn" onclick="changeFontSize(-1)" title="Decrease font">A-</button>
          <button class="font-btn" onclick="changeFontSize(1)"  title="Increase font">A+</button>
          <button class="font-btn print-btn" onclick="window.print()" title="Print"><?= icon('printer','w-3 h-3') ?></button>
          <button id="bookmark-btn" class="font-btn"
                  title="सुरक्षित गर्नुस्"
                  onclick="toggleBookmark(<?= (int)$article['id'] ?>, <?= json_encode(h($title_main)) ?>, '<?= h($article['slug']) ?>')"
                  ><?= icon('bookmark','w-3 h-3') ?></button>
          <button id="tts-btn" class="font-btn" title="पढेर सुनाउनुस्"
                  onclick="ttsToggle()"><?= icon('volume-2','w-3 h-3') ?></button>
        </div>
      </div>

      <!-- Featured image — BEFORE summary, full-width like karobardaily -->
      <?php if ($article['image_url']): ?>
      <div class="mb-5 rounded-lg overflow-hidden">
        <img src="<?= h($article['image_url']) ?>" alt="<?= h($title_main) ?>"
             class="w-full object-cover" style="max-height:520px">
        <?php if ($article['image_credit']): ?>
        <p class="text-xs px-2 pt-1" style="color:var(--c-muted)">फोटो: <?= h($article['image_credit']) ?></p>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- Summary box — after image -->
      <?php if ($summary_main): ?>
      <div class="p-4 mb-5 rounded-lg text-sm font-medium leading-relaxed" style="background:var(--c-surface2);border-left:4px solid var(--c-primary-lt);color:var(--c-text2)">
        <?= h($summary_main) ?>
      </div>
      <?php endif; ?>

      <!-- Article body -->
      <div class="article-content" id="article-body" x-data>
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
          <a href="/tag/<?= h($tag['slug'] ?? slug_from_title($tag['name'])) ?>" class="tag-cloud-item">
            #<?= h($tag['name']) ?>
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

      <!-- Reactions -->
      <div class="reactions-bar mt-5 pt-4" style="border-top:1px solid var(--c-border)"
           x-data="reactionBox(<?= (int)$article['id'] ?>, <?= htmlspecialchars(json_encode($reaction_counts), ENT_QUOTES, 'UTF-8') ?>)">
        <p class="text-xs font-bold mb-2.5 flex items-center gap-1.5" style="color:var(--c-muted)">
          <?= icon('smile','w-3.5 h-3.5') ?> तपाईंलाई कस्तो लाग्यो?
        </p>
        <div class="flex flex-wrap gap-2">
          <?php foreach (['like'=>'👍','love'=>'❤️','wow'=>'😮','sad'=>'😢','helpful'=>'🙏'] as $rtype=>$emoji): ?>
          <button @click="react('<?= $rtype ?>')"
                  :class="voted==='<?= $rtype ?>' ? 'reacted' : ''"
                  :disabled="!!voted"
                  class="reaction-btn">
            <span><?= $emoji ?></span>
            <span class="reaction-count" x-text="counts['<?= $rtype ?>'] || 0"></span>
          </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Author bio - Enhanced -->
      <?php if ($article['author_bio'] || $article['author_avatar']): ?>
      <div class="author-box">
        <?php if ($article['author_avatar']): ?>
          <div class="author-avatar"><img src="<?= h($article['author_avatar']) ?>" alt=""></div>
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
          <?php
            $au_full = $article['author_slug'] ? get_author_by_slug($article['author_slug']) : null;
            if ($au_full): ?>
          <div class="flex items-center gap-2 mt-2">
            <?php if ($au_full['twitter_url'] ?? ''): ?>
            <a href="<?= h($au_full['twitter_url']) ?>" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1 text-xs hover:underline" style="color:rgba(255,255,255,.7)"><?= icon('twitter','w-3 h-3') ?> Twitter</a>
            <?php endif; ?>
            <?php if ($au_full['facebook_url'] ?? ''): ?>
            <a href="<?= h($au_full['facebook_url']) ?>" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1 text-xs hover:underline" style="color:rgba(255,255,255,.7)"><?= icon('facebook','w-3 h-3') ?> Facebook</a>
            <?php endif; ?>
            <?php if ($au_full['linkedin_url'] ?? ''): ?>
            <a href="<?= h($au_full['linkedin_url']) ?>" target="_blank" rel="noopener noreferrer" class="flex items-center gap-1 text-xs hover:underline" style="color:rgba(255,255,255,.7)"><?= icon('linkedin','w-3 h-3') ?> LinkedIn</a>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Correction Notice -->
      <?php if (!empty($article['correction_note'])): ?>
      <div class="mt-5 p-4 rounded-lg flex gap-3" style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3)">
        <?= icon('alert-triangle','w-4 h-4 flex-shrink-0','w-4 h-4 inline-block align-middle flex-shrink-0','color:rgba(180,120,0,1)') ?>
        <div>
          <p class="font-bold text-xs mb-0.5" style="color:rgba(180,120,0,1)"><?= lang_label('सुधार नोट','Correction Notice') ?></p>
          <p class="text-sm" style="color:var(--c-text2)"><?= nl2br(h($article['correction_note'])) ?></p>
        </div>
      </div>
      <?php endif; ?>

      <!-- Comments Section -->
      <div class="mt-8 pt-5" id="comments" style="border-top:1px solid var(--c-border)">
        <h3 class="font-extrabold text-base mb-5 flex items-center gap-2">
          <?= icon('message-circle','w-4 h-4') ?> टिप्पणीहरू
          <?php if ($comment_count > 0): ?>
          <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                style="background:var(--c-primary);color:#fff">
            <?= np_number($comment_count) ?>
          </span>
          <?php endif; ?>
        </h3>
        <?php if (!empty($comments)): ?>
        <div class="space-y-4 mb-6">
          <?php foreach ($comments as $cmt): ?>
          <div class="comment-item">
            <div class="comment-avatar"><?= h(mb_strtoupper(mb_substr($cmt['name'],0,1))) ?></div>
            <div class="comment-body flex-1">
              <div class="comment-meta">
                <span class="comment-name"><?= h($cmt['name']) ?></span>
                <?php if ($cmt['website']): ?>
                <a href="<?= h($cmt['website']) ?>" target="_blank" rel="nofollow noopener"
                   style="color:var(--c-primary-lt)" class="text-xs hover:underline">
                  <?= icon('external-link','w-3 h-3') ?>
                </a>
                <?php endif; ?>
                <span class="comment-date"><?= time_ago($cmt['created_at']) ?></span>
              </div>
              <p class="comment-text"><?= nl2br(h($cmt['content'])) ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php
        $flash_cmt_ok = flash_get('comment_success');
        $flash_cmt_er = flash_get('comment_error');
        if (setting('comments_enabled','1') !== '0'):
        ?>
        <div class="comment-form-wrap">
          <h4 class="flex items-center gap-1.5 mb-4">
            <?= icon('edit-3','w-4 h-4') ?> टिप्पणी लेख्नुस्
          </h4>
          <?php if ($flash_cmt_ok): ?>
          <div class="comment-pending-notice flex items-center gap-1.5 mb-3">
            <?= icon('check-circle','w-4 h-4') ?> <?= h($flash_cmt_ok) ?>
          </div>
          <?php endif; ?>
          <?php if ($flash_cmt_er): ?>
          <div class="p-3 rounded text-sm bg-red-50 border border-red-200 text-red-700 mb-3">
            <?= h($flash_cmt_er) ?>
          </div>
          <?php endif; ?>
          <form method="POST" action="/comment/<?= (int)$article['id'] ?>">
            <?= csrf_field() ?>
            <input type="text" name="website_confirm" value="" autocomplete="off" tabindex="-1"
                   style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0" aria-hidden="true">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
              <input type="text" name="name" placeholder="नाम *" required maxlength="100"
                     class="admin-input text-sm" value="<?= h($_POST['name'] ?? '') ?>">
              <input type="email" name="email" placeholder="इमेल (वैकल्पिक)" maxlength="200"
                     class="admin-input text-sm" value="<?= h($_POST['email'] ?? '') ?>">
            </div>
            <input type="url" name="website" placeholder="वेबसाइट (वैकल्पिक)" maxlength="200"
                   class="admin-input text-sm w-full mb-3" value="<?= h($_POST['website'] ?? '') ?>">
            <textarea name="content" rows="4" required maxlength="2000"
                      placeholder="तपाईंको टिप्पणी लेख्नुस् *"
                      class="admin-input text-sm w-full mb-3 resize-none"><?= h($_POST['content'] ?? '') ?></textarea>
            <button type="submit" class="btn btn-primary flex items-center gap-2">
              <?= icon('send','w-4 h-4') ?> टिप्पणी पठाउनुस्
            </button>
            <p class="text-xs mt-2 flex items-center gap-1" style="color:var(--c-muted)">
              <?= icon('info','w-3 h-3') ?> टिप्पणी समीक्षा पछि प्रकाशित हुन्छ।
            </p>
          </form>
        </div>
        <?php endif; ?>
      </div>

      <!-- Prev / Next navigation -->
      <?php if ($prev_next['prev'] || $prev_next['next']): ?>
      <div class="flex items-stretch gap-3 mt-6 mb-2">
        <?php if ($prev_next['prev']): ?>
        <a href="/article/<?= h($prev_next['prev']['slug']) ?>"
           class="flex-1 flex flex-col gap-1 p-3 rounded-lg hover:shadow-md transition-all"
           style="background:var(--c-surface);border:1px solid var(--c-border)">
          <span class="text-xs flex items-center gap-1" style="color:var(--c-muted)">
            <?= icon('arrow-left','w-3 h-3') ?> अघिल्लो
          </span>
          <span class="text-sm font-semibold line-clamp-2 hover:underline" style="color:var(--c-text)">
            <?= h($prev_next['prev']['title']) ?>
          </span>
        </a>
        <?php else: ?><div class="flex-1"></div><?php endif; ?>

        <?php if ($prev_next['next']): ?>
        <a href="/article/<?= h($prev_next['next']['slug']) ?>"
           class="flex-1 flex flex-col gap-1 p-3 rounded-lg hover:shadow-md transition-all text-right"
           style="background:var(--c-surface);border:1px solid var(--c-border)">
          <span class="text-xs flex items-center gap-1 justify-end" style="color:var(--c-muted)">
            अर्को <?= icon('arrow-right','w-3 h-3') ?>
          </span>
          <span class="text-sm font-semibold line-clamp-2 hover:underline" style="color:var(--c-text)">
            <?= h($prev_next['next']['title']) ?>
          </span>
        </a>
        <?php else: ?><div class="flex-1"></div><?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- Related articles -->
      <?php if (!empty($related)): ?>
      <div class="mt-8">
        <div class="section-heading mb-4">
          <span class="flex items-center gap-2"><?= icon('layers','w-4 h-4') ?> सम्बन्धित समाचार</span>
        </div>
        <div class="related-grid">
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

    <!-- Most commented -->
    <?php $most_commented_side = get_most_commented_articles(5); ?>
    <?php if (!empty($most_commented_side)): ?>
    <div class="sidebar-card">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('message-circle','w-4 h-4') ?> सर्वाधिक टिप्पणी</span>
      </div>
      <?php foreach ($most_commented_side as $i => $mc): ?>
      <div class="popular-item">
        <span class="popular-num"><?= $i+1 ?></span>
        <div>
          <a href="/article/<?= h($mc['slug']) ?>" class="ptitle block hover:underline"><?= h($mc['title']) ?></a>
          <div class="pmeta flex items-center gap-1">
            <?= icon('message-circle','w-2.5 h-2.5') ?> <?= np_number((int)$mc['comment_count']) ?>
            &nbsp;<?= icon('eye','w-2.5 h-2.5') ?> <?= np_number((int)$mc['views']) ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Tags cloud -->
    <?php $article_tags = $article['tags'] ?? []; ?>
    <?php if (!empty($article_tags)): ?>
    <div class="sidebar-card">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('tags','w-4 h-4') ?> ट्यागहरू</span>
      </div>
      <div class="flex flex-wrap gap-2">
        <?php foreach ($article_tags as $at): ?>
        <a href="/tag/<?= h($at['slug'] ?? slug_from_title($at['name'])) ?>" class="tag-cloud-item">#<?= h($at['name']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </aside>
</div>

<script>
var _fontSize = parseInt(localStorage.getItem('nnp_fontsize') || '16', 10);
(function(){ var b = document.getElementById('article-body'); if(b) b.style.fontSize = _fontSize + 'px'; })();
function changeFontSize(d) {
  _fontSize = Math.min(22, Math.max(13, _fontSize + d));
  var b = document.getElementById('article-body');
  if(b) b.style.fontSize = _fontSize + 'px';
  localStorage.setItem('nnp_fontsize', _fontSize);
}
</script>
<style>
.tag-cloud-item {
  display:inline-block;padding:2px 9px;border-radius:20px;font-size:11.5px;font-weight:500;
  background:var(--c-surface2);color:var(--c-text2);border:1px solid var(--c-border);
  transition:background .15s,color .15s;
}
.tag-cloud-item:hover{background:var(--c-primary);color:#fff;}
/* Lightbox */
#nnp-lightbox {
  display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.92);
  align-items:center;justify-content:center;cursor:zoom-out;
}
#nnp-lightbox.open { display:flex; }
#nnp-lightbox img  { max-width:94vw;max-height:92vh;object-fit:contain;border-radius:4px;box-shadow:0 8px 40px rgba(0,0,0,.7); }
#nnp-lightbox-cap  { position:absolute;bottom:12px;left:0;right:0;text-align:center;color:rgba(255,255,255,.7);font-size:13px;padding:0 20px; }
#nnp-lightbox-close { position:absolute;top:14px;right:18px;color:#fff;font-size:28px;cursor:pointer;opacity:.8;line-height:1; }
.article-content img { cursor:zoom-in;transition:opacity .15s; }
.article-content img:hover { opacity:.9; }
</style>

<?php require SRC_DIR . '/layout/footer.php'; ?>

<!-- Image Lightbox -->
<div id="nnp-lightbox" onclick="this.classList.remove('open')">
  <span id="nnp-lightbox-close" onclick="document.getElementById('nnp-lightbox').classList.remove('open')">&times;</span>
  <img id="nnp-lightbox-img" src="" alt="">
  <div id="nnp-lightbox-cap"></div>
</div>
<div class="reading-progress" id="reading-progress"></div>
<script>
// ── Reading progress bar ─────────────────────────────────
(function(){
  var bar  = document.getElementById('reading-progress');
  var body = document.getElementById('article-body');
  if (!bar || !body) return;
  function update() {
    var top  = body.getBoundingClientRect().top + window.scrollY;
    var end  = top + body.offsetHeight;
    var pct  = Math.max(0, Math.min(100, (window.scrollY - top) / (end - top - window.innerHeight + 100) * 100));
    bar.style.width = pct + '%';
  }
  window.addEventListener('scroll', update, {passive:true});
  update();
})();
initBookmark(<?= (int)$article['id'] ?>);
// ── Image lightbox ───────────────────────────────────────
(function(){
  var lb  = document.getElementById('nnp-lightbox');
  var img = document.getElementById('nnp-lightbox-img');
  var cap = document.getElementById('nnp-lightbox-cap');
  if (!lb) return;
  document.getElementById('article-body').querySelectorAll('img').forEach(function(el){
    el.addEventListener('click', function(e){
      e.stopPropagation();
      img.src = el.src;
      cap.textContent = el.alt || '';
      lb.classList.add('open');
    });
  });
  document.addEventListener('keydown', function(e){ if(e.key==='Escape') lb.classList.remove('open'); });
})();
// ── Text-to-Speech ───────────────────────────────────────
(function(){
  var synth  = window.speechSynthesis;
  var utt    = null;
  var btn    = document.getElementById('tts-btn');
  if (!synth || !btn) return;
  window.ttsToggle = function() {
    if (synth.speaking) { synth.cancel(); btn.title='पढेर सुनाउनुस्'; btn.style.color=''; return; }
    var text = (document.getElementById('article-body') || document.body).innerText.slice(0,3000);
    utt = new SpeechSynthesisUtterance(text);
    utt.lang = 'ne-NP';
    utt.rate = 0.9;
    utt.onend = function(){ btn.title='पढेर सुनाउनुस्'; btn.style.color=''; };
    synth.speak(utt);
    btn.title='रोक्नुस्';
    btn.style.color = 'var(--c-primary)';
  };
})();
</script>
