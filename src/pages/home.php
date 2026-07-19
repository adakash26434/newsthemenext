<?php
$page_title = site_name() . ' — ' . site_tagline();
$page_desc  = 'नेपालको ताजा समाचार — राजनीति, अर्थतन्त्र, खेलकुद, प्रविधि र थप।';

// Auto-refresh live market data (max once every 6 hours)
try {
    $mf = BASE_DIR . '/market_fetch.php';
    if (file_exists($mf)) { require_once $mf; maybe_refresh_market(6); }
} catch (\Throwable $e) { /* silent */ }

// Fetch live data for homepage widgets
$live_data = [];
try {
    $live_file = SRC_DIR . '/lib/live_data_service.php';
    if (file_exists($live_file)) {
        require_once $live_file;
        $lds = live_data();
        $live_data = [
            'weather' => @$lds->getWeather(),
            'earthquakes' => @$lds->getEarthquakes(3),
            'air_quality' => @$lds->getAirQuality(),
        ];
    }
} catch (\Throwable $e) { /* silent */ }

$featured       = get_articles(['status'=>'published','featured'=>true,'limit'=>5]);
if (empty($featured))
    $featured   = get_articles(['status'=>'published','limit'=>5]);

$latest         = get_articles(['status'=>'published','limit'=>8]);
$popular        = get_popular_articles(6);
$all_tags       = get_tags();
$categories_all = get_categories();
$upcoming_evts  = get_upcoming_events(4);
$trending       = get_trending_articles(6);
$forex_widgets  = get_market_widgets('forex');
$gold_widgets   = get_market_widgets('gold');
$nepse_widgets  = get_market_widgets('nepse');

// Per-category article blocks
$cat_articles = [];
foreach (array_slice($categories_all, 0, 8) as $cat) {
    $arts = get_articles(['status'=>'published','category_slug'=>$cat['slug'],'limit'=>5]);
    if (!empty($arts)) $cat_articles[$cat['slug']] = ['cat'=>$cat,'articles'=>$arts];
}

// Top stories scroll bar
$top_stories = get_articles(['status'=>'published','limit'=>8,'order'=>'a.published_at DESC']);

// Demo articles for empty state
$demo_articles = [
    [
        'title' => 'नेपालको आर्थिक वृद्धिदर यो वर्ष ५.५ प्रतिशत हुने IMF को प्रक्षेपण',
        'summary' => 'अन्तर्राष्ट्रिय मुद्रा कोष (IMF) ले नेपालको चालु आर्थिक वर्षमा आर्थिक वृद्धिदर ५.५ प्रतिशत पुग्ने प्रक्षेपण गरेको छ।',
        'category' => 'अर्थतन्त्र',
        'category_color' => '#059669',
        'image_url' => 'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?w=800&q=80',
        'slug' => 'nepal-economic-growth-imf',
        'views' => 12500,
        'published_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
    ],
    [
        'title' => 'संसद्मा बजेट अधिवेशन सुरु, आर्थिक विधेयकमा तीव्र बहस',
        'summary' => 'संघीय संसद्को बजेट अधिवेशन सुरु भएको छ। आर्थिक विधेयकमा सत्तापक्ष र प्रतिपक्षबीच तीव्र बहस भइरहेको छ।',
        'category' => 'राजनीति',
        'category_color' => '#DC2626',
        'image_url' => 'https://images.unsplash.com/photo-1541872703-74c5e44368f9?w=800&q=80',
        'slug' => 'parliament-budget-session',
        'views' => 8750,
        'published_at' => date('Y-m-d H:i:s', strtotime('-3 hours')),
    ],
    [
        'title' => 'नेप्से सूचकांक ५ वर्षको उच्च विन्दुमा, एकै दिन ४२ अंकले उछाल',
        'summary' => 'नेपाल धितोपत्र विनिमय बजार (नेप्से) मा बुधबार ४२.३५ अंकले वृद्धि भई सूचकांक २३५६.१४ पुगेको छ।',
        'category' => 'शेयर बजार',
        'category_color' => '#7C3AED',
        'image_url' => 'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?w=800&q=80',
        'slug' => 'nepse-index-high',
        'views' => 6200,
        'published_at' => date('Y-m-d H:i:s', strtotime('-5 hours')),
    ],
    [
        'title' => 'नेपाल टेलिकमले ५जी सेवा विस्तारको योजना सार्वजनिक गर्यो',
        'summary' => 'नेपाल टेलिकमले आगामी दुई वर्षमा देशका प्रमुख शहरहरूमा ५जी इन्टरनेट सेवा विस्तार गर्ने योजना सार्वजनिक गरेको छ।',
        'category' => 'प्रविधि',
        'category_color' => '#2563EB',
        'image_url' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80',
        'slug' => 'ncell-5g-expansion',
        'views' => 5400,
        'published_at' => date('Y-m-d H:i:s', strtotime('-6 hours')),
    ],
];

// Use demo articles if no real articles
if (empty($featured) && empty($top_stories)) {
    $featured = array_slice($demo_articles, 0, 4);
    $top_stories = $demo_articles;
    $latest = $demo_articles;
    $trending = array_slice($demo_articles, 0, 4);
    $popular = $demo_articles;
}

require SRC_DIR . '/layout/header.php';
?>

<!-- Inline header ad -->
<?php render_ads('header-banner-inline', false); ?>

<!-- ══════════════════════════════════════════════════════
     BULLETIN TICKER — Full-width scrolling news bar
     ══════════════════════════════════════════════════════ -->
<?php
$bulletin_items = get_articles(['status'=>'published','limit'=>12,'order'=>'a.published_at DESC']);
if (!empty($bulletin_items)):
?>
<div class="bulletin-ticker-wrap mb-5 home-fullbleed">
  <div class="bulletin-label">
    <?= icon('radio','w-3 h-3') ?> बुलेटिन
  </div>
  <div class="bulletin-scroll">
    <div class="bulletin-scroll-inner">
      <?php for ($bi=0; $bi<2; $bi++): ?>
      <?php foreach ($bulletin_items as $bi_art): ?>
      <a href="/article/<?= h($bi_art['slug']) ?>" class="bulletin-item">
        <?= h(mb_substr($bi_art['title'], 0, 70)) ?><?= mb_strlen($bi_art['title'])>70?'…':'' ?>
      </a>
      <span class="bulletin-sep">◆</span>
      <?php endforeach; ?>
      <?php endfor; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════
     ZONE 1: HERO — Full-width main news banner
     Layout: 1 big card left + 4 secondary cards right
     ══════════════════════════════════════════════════════ -->
<?php if (!empty($featured)): $hero = $featured[0]; ?>
<div class="hero-section mb-6 home-fullbleed">
  <div class="hero-main-card">
    <?php if ($hero['image_url']): ?>
    <div class="hero-main-img">
      <img src="<?= h($hero['image_url']) ?>" alt="<?= h($hero['title']) ?>" loading="eager">
    </div>
    <?php endif; ?>
    <div class="hero-main-overlay">
      <div class="flex items-center gap-2 mb-3 flex-wrap">
        <span class="hero-main-badge" style="background:<?= h(category_color($hero['category_color'])) ?>">
          <?php if ($hero['is_breaking']): ?>
          <?= icon('zap','w-3 h-3') ?> ब्रेकिङ
          <?php else: ?>
          <?= h($hero['category_name_np']?:$hero['category_name']) ?>
          <?php endif; ?>
        </span>
      </div>
      <h2 class="hero-main-title">
        <a href="/article/<?= h($hero['slug']) ?>"><?= h($hero['title']) ?></a>
      </h2>
      <p class="hero-main-summary"><?= h(excerpt($hero['summary']??'',30)) ?></p>
      <div class="flex items-center gap-4 mt-3 text-sm" style="color:rgba(255,255,255,0.75)">
        <span class="flex items-center gap-1"><?= icon('user','w-4 h-4') ?> <?= h($hero['author_name']) ?></span>
        <span class="flex items-center gap-1"><?= icon('clock','w-4 h-4') ?> <?= time_ago($hero['published_at']??$hero['created_at']) ?></span>
        <span class="flex items-center gap-1"><?= icon('eye','w-4 h-4') ?> <?= np_number((int)$hero['views']) ?></span>
      </div>
    </div>
  </div>

  <?php if (count($featured) > 1): ?>
  <div class="hero-secondary-grid">
    <?php foreach (array_slice($featured,1,4) as $fa): ?>
    <a href="/article/<?= h($fa['slug']) ?>" class="hero-secondary-card">
      <div class="img">
        <?php if ($fa['image_url']): ?>
          <img src="<?= h($fa['image_url']) ?>" alt="" loading="lazy">
        <?php endif; ?>
        <span class="stat-badge">
          <?= icon('eye','w-3 h-3') ?> <?= np_number((int)($fa['views'] ?? 0)) ?>
        </span>
      </div>
      <div class="body">
        <span class="cat-badge mb-1 inline-block" style="background:<?= h(category_color($fa['category_color'])) ?>">
          <?= h($fa['category_name_np']?:$fa['category_name']) ?>
        </span>
        <h3 class="title"><?= h($fa['title']) ?></h3>
        <div class="flex items-center gap-2 mt-2 text-xs" style="color:var(--c-muted)">
          <?= icon('clock','w-3 h-3') ?> <?= time_ago($fa['published_at']??$fa['created_at']) ?>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════
     ZONE 2: ताजा समाचार (left) + विदेशी मुद्रा दर (right)
     ══════════════════════════════════════════════════════ -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

  <!-- Left: Latest news list -->
  <div class="lg:col-span-2">
    <div class="section-heading mb-4">
      <span class="flex items-center gap-2"><?= icon('newspaper','w-4 h-4') ?> ताजा समाचार</span>
    </div>
    <?php
    $latest12 = get_articles(['status'=>'published','limit'=>12]);
    foreach ($latest12 as $la):
    ?>
    <div class="sidebar-article mb-3">
      <a href="/article/<?= h($la['slug']) ?>" class="thumb flex-shrink-0" style="width:96px;height:68px;overflow:hidden;border-radius:6px;display:block;position:relative">
        <?php if ($la['image_url']): ?>
          <img src="<?= h($la['image_url']) ?>" alt="" loading="lazy" style="width:100%;height:100%;object-fit:cover">
        <?php else: ?>
          <div style="width:100%;height:100%;background:var(--c-bg3)"></div>
        <?php endif; ?>
        <span class="stat-badge stat-badge-sm">
          <?= icon('eye','w-2.5 h-2.5') ?> <?= np_number((int)($la['views'] ?? 0)) ?>
        </span>
      </a>
      <div class="info flex-1 min-w-0">
        <a href="/article/<?= h($la['slug']) ?>" class="title block hover:underline font-semibold" style="font-size:0.97rem;line-height:1.4;color:var(--c-text1)"><?= h($la['title']) ?></a>
        <div class="meta flex items-center gap-2 mt-1 flex-wrap">
          <span class="inline-block px-1.5 rounded" style="background:<?= h(category_color($la['category_color'])) ?>;color:#fff;font-size:0.6rem;font-weight:700">
            <?= h($la['category_name_np']?:$la['category_name']) ?>
          </span>
          <span class="flex items-center gap-1 text-xs" style="color:var(--c-muted)">
            <?= icon('clock','w-2.5 h-2.5') ?> <?= time_ago($la['published_at']??$la['created_at']) ?>
          </span>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Right: Market widgets (Forex / Gold / NEPSE) -->
  <aside class="lg:col-span-1">
    <?php render_ads('sidebar-top'); ?>

    <?php if (!empty($forex_widgets) || !empty($gold_widgets) || !empty($nepse_widgets)): ?>
    <div class="market-widget-card mb-5">
      <?php
      if (!function_exists('_mw_section')) {
      function _mw_section(array $rows, string $icon_name, string $label): void {
          if (empty($rows)) return;
          echo '<div class="market-widget-header">' . icon($icon_name,'w-3 h-3') . ' ' . h($label) . '</div>';
          foreach ($rows as $mw) {
              $chg = $mw['change_pct'] !== null ? (float)$mw['change_pct'] : null;
              $cls = $chg === null ? '' : ($chg > 0 ? 'up' : ($chg < 0 ? 'down' : 'flat'));
              $val = h($mw['value']);
              echo '<div class="market-row">';
              echo '<span class="market-label">' . h($mw['label']) . '</span>';
              echo '<div class="flex items-center gap-2">';
              echo '<span class="market-value">' . $val . '</span>';
              if ($chg !== null && $chg != 0) {
                  echo '<span class="market-change ' . $cls . '">' . ($chg > 0 ? '+' : '') . number_format($chg, 2) . '%</span>';
              }
              echo '</div></div>';
          }
      }
      }
      ?>
      <?php if (!empty($forex_widgets)): _mw_section($forex_widgets, 'globe', 'विदेशी मुद्रा दर'); endif; ?>
      <?php if (!empty($gold_widgets)):  _mw_section($gold_widgets,  'gem',   'सुन / चाँदी');     endif; ?>
      <?php if (!empty($nepse_widgets)): _mw_section($nepse_widgets, 'trending-up', 'नेप्से');    endif; ?>
      <?php
      $mf_last = setting('market_last_fetch','');
      $mf_ago  = $mf_last ? time_ago_np($mf_last) : '';
      ?>
      <div class="text-center py-2" style="font-size:10px;color:var(--c-muted)">
        दर सांकेतिक मात्र · स्रोत: <a href="https://www.nrb.org.np" target="_blank" style="color:inherit;text-decoration:underline">नेपाल राष्ट्र बैंक</a>
        <?php if ($mf_ago): ?>&nbsp;· <?= h($mf_ago) ?> अपडेट<?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Trending articles -->
    <?php if (!empty($trending)): ?>
    <div class="sidebar-card mb-5">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('trending-up','w-4 h-4') ?> ट्रेन्डिङ</span>
      </div>
      <?php foreach ($trending as $ti => $tr): ?>
      <div class="popular-item">
        <span class="popular-num" style="background:linear-gradient(135deg,var(--c-primary),var(--c-primary-lt));color:#fff"><?= $ti+1 ?></span>
        <div>
          <a href="/article/<?= h($tr['slug']) ?>" class="ptitle block hover:underline">
            <?= h(mb_substr($tr['title'],0,65)) ?><?= mb_strlen($tr['title'])>65?'…':'' ?>
          </a>
          <div class="pmeta flex items-center gap-1 flex-wrap">
            <span class="inline-block px-1.5 py-0 rounded" style="background:<?= h(category_color($tr['category_color'])) ?>;color:#fff;font-size:0.6rem;font-weight:700">
              <?= h($tr['category_name_np']?:$tr['category_name']) ?>
            </span>
            <?= icon('flame','w-2.5 h-2.5') ?> <?= np_number((int)$tr['views']) ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Tag cloud -->
    <?php if (!empty($all_tags)): ?>
    <div class="sidebar-card mb-5">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('hash','w-4 h-4') ?> ट्यागहरू</span>
      </div>
      <div class="tag-cloud">
        <?php foreach ($all_tags as $tag): ?>
          <a href="/tag/<?= h($tag['slug']) ?>">#<?= h($tag['name']) ?>
            <?php if (($tag['usage_count']??0) > 1): ?><sup style="font-size:9px;opacity:.6"><?= np_number((int)$tag['usage_count']) ?></sup><?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </aside>

</div>

<!-- ══════════════════════════════════════════════════════
     ZONE 3: Category sections + full sidebar
     ══════════════════════════════════════════════════════ -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <div class="lg:col-span-2">
    <?php render_ads('article-middle'); ?>

    <?php
    // Each widget below is independently try/caught: if any ONE of them
    // throws (bad data, a future DB issue, etc.) the rest of the page —
    // including the footer — still renders instead of going blank.
    // (This is a structural safety net; see data/php-error.log for the
    // real error if a section silently stops appearing.)
    try {
    $cat_count = 0;
    foreach ($cat_articles as $slug => $block):
        $cat    = $block['cat'];
        $arts   = $block['articles'];
        $lead   = $arts[0];
        $rest   = array_slice($arts, 1, 4);
        $cat_count++;
    ?>
    <div class="mb-8">
      <div class="flex items-center justify-between mb-4 pb-2" style="border-bottom: 2px solid <?= h($cat['color']?:accent_color()) ?>">
        <h2 class="flex items-center gap-2 text-lg font-bold" style="color:<?= h($cat['color']?:accent_color()) ?>">
          <?php if ($cat['icon']): ?><i data-lucide="<?= h($cat['icon']) ?>" class="w-5 h-5"></i><?php endif; ?>
          <?= h(cat_name($cat, $_cur_lang ?? current_lang())) ?>
        </h2>
        <a href="/category/<?= h($cat['slug']) ?>" class="flex items-center gap-1 text-sm font-semibold hover:underline" style="color:<?= h($cat['color']?:accent_color()) ?>">
          थप हेर्नुस् <?= icon('arrow-right','w-4 h-4') ?>
        </a>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php if ($lead): ?>
        <a href="/article/<?= h($lead['slug']) ?>" class="news-card block lg:col-span-2">
          <div class="news-card-img">
            <?php if ($lead['image_url']): ?>
              <img src="<?= h($lead['image_url']) ?>" alt="" loading="lazy">
            <?php endif; ?>
            <div class="news-card-img-overlay"></div>
            <span class="cat-badge absolute top-3 left-3" style="background:<?= h($cat['color']?:accent_color()) ?>">
              <?= h(cat_name($cat, $_cur_lang ?? current_lang())) ?>
            </span>
          </div>
          <div class="news-card-body">
            <h3 class="news-card-title"><?= h($lead['title']) ?></h3>
            <div class="news-card-meta">
              <span class="flex items-center gap-1"><?= icon('user','w-3 h-3') ?> <?= h($lead['author_name']) ?></span>
              <span class="flex items-center gap-1"><?= icon('clock','w-3 h-3') ?> <?= time_ago($lead['published_at']??$lead['created_at']) ?></span>
              <span class="flex items-center gap-1"><?= icon('eye','w-3 h-3') ?> <?= np_number((int)$lead['views']) ?></span>
            </div>
          </div>
        </a>
        <?php endif; ?>
        <?php foreach ($rest as $ra): ?>
        <a href="/article/<?= h($ra['slug']) ?>" class="article-card-compact">
          <div class="img-wrap">
            <?php if ($ra['image_url']): ?><img src="<?= h($ra['image_url']) ?>" alt="" loading="lazy"><?php endif; ?>
          </div>
          <div class="p-3">
            <h3 class="title text-sm"><?= h($ra['title']) ?></h3>
            <p class="meta mt-1 flex items-center gap-1">
              <?= icon('clock','w-2.5 h-2.5') ?> <?= time_ago($ra['published_at']??$ra['created_at']) ?>
            </p>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php if ($cat_count % 3 === 0) render_ads('in-feed'); ?>
    <?php endforeach; ?>
    } catch (\Throwable $e) {
        error_log('[home.php category-blocks] ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
    }
    ?>
  </div>

  <!-- Zone 3 sidebar -->
  <aside class="lg:col-span-1">

    <!-- Most popular -->
    <?php try { if (!empty($popular)): ?>
    <div class="sidebar-card mb-5">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('flame','w-4 h-4') ?> सबैभन्दा पढिएका</span>
      </div>
      <?php foreach ($popular as $i => $pop): ?>
      <div class="popular-item">
        <span class="popular-num"><?= $i+1 ?></span>
        <div>
          <a href="/article/<?= h($pop['slug']) ?>" class="ptitle block hover:underline"><?= h($pop['title']) ?></a>
          <div class="pmeta flex items-center gap-1 flex-wrap">
            <span class="inline-block px-1.5 py-0 rounded" style="background:<?= h(category_color($pop['category_color'])) ?>;color:#fff;font-size:0.6rem;font-weight:700">
              <?= h($pop['category_name_np']?:$pop['category_name']) ?>
            </span>
            <?= icon('eye','w-2.5 h-2.5') ?> <?= np_number((int)$pop['views']) ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; } catch (\Throwable $e) { error_log('[home.php popular] ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine()); } ?>

    <!-- Most commented -->
    <?php try {
    $most_commented = get_most_commented_articles(5);
    if (!empty($most_commented)): ?>
    <div class="sidebar-card mb-5">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('message-circle','w-4 h-4') ?> सर्वाधिक टिप्पणी</span>
      </div>
      <?php foreach ($most_commented as $i => $mc): ?>
      <div class="popular-item">
        <span class="popular-num"><?= $i+1 ?></span>
        <div>
          <a href="/article/<?= h($mc['slug']) ?>" class="ptitle block hover:underline"><?= h($mc['title']) ?></a>
          <div class="pmeta flex items-center gap-1">
            <?= icon('message-circle','w-2.5 h-2.5') ?> <?= np_number((int)$mc['comment_count']) ?> टिप्पणी
            &nbsp;<?= icon('eye','w-2.5 h-2.5') ?> <?= np_number((int)$mc['views']) ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; } catch (\Throwable $e) { error_log('[home.php most_commented] ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine()); } ?>

    <!-- Newsletter widget -->
    <?php try { ?>
    <div class="newsletter-widget mb-5">
      <h3 class="flex items-center gap-2"><?= icon('mail','w-4 h-4') ?> न्यूजलेटर</h3>
      <p>ताजा समाचार इमेलमा पाउनुस्</p>
      <form method="POST" action="/newsletter/subscribe">
        <?= csrf_field() ?>
        <input type="email" name="email" class="newsletter-input" placeholder="तपाईंको इमेल..." required>
        <button type="submit" class="newsletter-btn">सदस्य बन्नुस् →</button>
      </form>
    </div>
    <?php } catch (\Throwable $e) { error_log('[home.php newsletter] ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine()); } ?>

    <!-- Upcoming events widget -->
    <?php try { if (!empty($upcoming_evts)): ?>
    <div class="sidebar-card mb-5">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('calendar','w-4 h-4') ?> आगामी कार्यक्रम</span>
        <a href="/events" class="flex items-center gap-1">सबै <?= icon('arrow-right','w-3 h-3') ?></a>
      </div>
      <?php foreach ($upcoming_evts as $ev): ?>
      <a href="/event/<?= h($ev['slug']) ?>" class="event-widget-item block">
        <div class="event-widget-date flex-shrink-0">
          <?php if ($ev['start_datetime']): ?>
            <div class="day"><?= np_number((int)date('j', strtotime($ev['start_datetime']))) ?></div>
            <div><?= NP_MONTHS[(int)date('n', strtotime($ev['start_datetime']))] ?? '' ?></div>
          <?php else: ?>
            <div><?= icon('calendar','w-5 h-5') ?></div>
          <?php endif; ?>
        </div>
        <div>
          <div class="event-widget-title"><?= h($ev['title']) ?></div>
          <?php if ($ev['venue']): ?>
            <div class="text-xs mt-0.5 flex items-center gap-1" style="color:var(--c-muted)">
              <?= icon('map-pin','w-2.5 h-2.5') ?> <?= h($ev['venue']) ?>
            </div>
          <?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; } catch (\Throwable $e) { error_log('[home.php upcoming_events] ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine()); } ?>

    <!-- All categories widget -->
    <?php try { ?>
    <div class="sidebar-card mb-5">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('grid','w-4 h-4') ?> सबै श्रेणीहरू</span>
      </div>
      <?php foreach ($categories_all as $cw): ?>
      <a href="/category/<?= h($cw['slug']) ?>"
         class="flex items-center justify-between py-2 border-b text-sm font-semibold hover:underline"
         style="border-color:var(--c-border2)">
        <span class="flex items-center gap-2">
          <?php if ($cw['icon']): ?>
            <i data-lucide="<?= h($cw['icon']) ?>" class="w-3.5 h-3.5 flex-shrink-0" style="color:<?= h($cw['color']?:accent_color()) ?>"></i>
          <?php else: ?>
            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:<?= h($cw['color']?:accent_color()) ?>"></span>
          <?php endif; ?>
          <?= h(cat_name($cw, $_cur_lang ?? current_lang())) ?>
        </span>
        <span class="text-xs font-normal" style="color:var(--c-muted)"><?= np_number((int)($cw['article_count']??0)) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
    <?php } catch (\Throwable $e) { error_log('[home.php all_categories] ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine()); } ?>

    <?php try { render_ads('sidebar-bottom'); } catch (\Throwable $e) { error_log('[home.php sidebar-bottom ad] ' . $e->getMessage()); } ?>
  </aside>

</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
