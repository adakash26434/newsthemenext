<?php
$page_title = site_name() . ' — ' . site_tagline();
$page_desc  = 'नेपालको ताजा समाचार — राजनीति, अर्थतन्त्र, खेलकुद, प्रविधि र थप।';

$featured       = get_articles(['status'=>'published','featured'=>true,'limit'=>5]);
if (empty($featured))
    $featured   = get_articles(['status'=>'published','limit'=>5]);

$latest         = get_articles(['status'=>'published','limit'=>8]);
$popular        = get_popular_articles(6);
$all_tags       = get_tags();
$categories_all = get_categories();
$upcoming_evts  = get_upcoming_events(4);

// Per-category article blocks
$cat_articles = [];
foreach (array_slice($categories_all, 0, 8) as $cat) {
    $arts = get_articles(['status'=>'published','category_slug'=>$cat['slug'],'limit'=>5]);
    if (!empty($arts)) $cat_articles[$cat['slug']] = ['cat'=>$cat,'articles'=>$arts];
}

// Top stories scroll bar
$top_stories = get_articles(['status'=>'published','limit'=>8,'order'=>'a.published_at DESC']);

require SRC_DIR . '/layout/header.php';
?>

<!-- Inline header ad -->
<?php render_ads('header-banner-inline', false); ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <!-- ════ Main column ════ -->
  <div class="lg:col-span-2">

    <!-- Top Stories horizontal scroll -->
    <?php if (!empty($top_stories)): ?>
    <div class="mb-6">
      <div class="section-heading">
        <span class="flex items-center gap-2">
          <?= icon('zap','w-4 h-4') ?> ताजा समाचार
        </span>
      </div>
      <div class="top-stories-scroll">
        <?php foreach ($top_stories as $ts): ?>
        <a href="/article/<?= h($ts['slug']) ?>" class="top-story-card group block">
          <div class="img">
            <?php if ($ts['image_url']): ?>
              <img src="<?= h($ts['image_url']) ?>" alt="" loading="lazy">
            <?php else: ?>
              <div class="img-placeholder"></div>
            <?php endif; ?>
          </div>
          <div class="body">
            <span class="cat-badge mb-1 inline-block" style="background:<?= h(category_color($ts['category_color'])) ?>">
              <?= h($ts['category_name_np']?:$ts['category_name']) ?>
            </span>
            <div class="title group-hover:underline"><?= h($ts['title']) ?></div>
            <div class="text-xs mt-1 flex items-center gap-1" style="color:var(--c-muted)">
              <?= icon('clock','w-2.5 h-2.5') ?> <?= time_ago($ts['published_at']??$ts['created_at']) ?>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Hero featured block -->
    <?php if (!empty($featured)): $hero = $featured[0]; ?>
    <div class="mb-6">
      <a href="/article/<?= h($hero['slug']) ?>" class="hero-article block group mb-4">
        <div class="img-wrap" style="overflow:hidden">
          <?php if ($hero['image_url']): ?>
            <img src="<?= h($hero['image_url']) ?>" alt="<?= h($hero['title']) ?>" loading="eager"
                 style="width:100%;height:400px;object-fit:cover">
          <?php else: ?>
            <div style="height:280px;background:linear-gradient(135deg,var(--c-primary),rgba(127,29,29,0.4))"></div>
          <?php endif; ?>
        </div>
        <div class="p-5">
          <div class="flex items-center gap-2 mb-2 flex-wrap">
            <span class="cat-badge" style="background:<?= h(category_color($hero['category_color'])) ?>">
              <?= h($hero['category_name_np']?:$hero['category_name']) ?>
            </span>
            <?php if ($hero['is_breaking']): ?>
              <span class="badge badge-red flex items-center gap-1"><?= icon('zap','w-2.5 h-2.5') ?> ब्रेकिङ</span>
            <?php endif; ?>
          </div>
          <h2 class="hero-title group-hover:underline mb-2"><?= h($hero['title']) ?></h2>
          <p class="text-sm mb-3 line-clamp-2" style="color:var(--c-text2)"><?= h(excerpt($hero['summary']??'',25)) ?></p>
          <div class="flex items-center gap-3 text-xs flex-wrap" style="color:var(--c-muted)">
            <span class="font-semibold flex items-center gap-1" style="color:var(--c-text)">
              <?= icon('user','w-3 h-3') ?> <?= h($hero['author_name']) ?>
            </span>
            <span class="flex items-center gap-1"><?= icon('clock','w-3 h-3') ?> <?= time_ago($hero['published_at']??$hero['created_at']) ?></span>
            <span class="flex items-center gap-1"><?= icon('eye','w-3 h-3') ?> <?= np_number((int)$hero['views']) ?></span>
            <span class="flex items-center gap-1"><?= icon('book-open','w-3 h-3') ?> <?= reading_time_label($hero['content']??'') ?></span>
          </div>
        </div>
      </a>

      <!-- 2×2 secondary featured -->
      <?php if (count($featured) > 1): ?>
      <div class="grid grid-cols-2 gap-3">
        <?php foreach (array_slice($featured,1,4) as $fa): ?>
        <a href="/article/<?= h($fa['slug']) ?>" class="article-card block group">
          <div class="img-wrap">
            <?php if ($fa['image_url']): ?>
              <img src="<?= h($fa['image_url']) ?>" alt="" loading="lazy">
            <?php endif; ?>
          </div>
          <div class="p-3">
            <span class="cat-badge mb-1 inline-block" style="background:<?= h(category_color($fa['category_color'])) ?>">
              <?= h($fa['category_name_np']?:$fa['category_name']) ?>
            </span>
            <h3 class="title group-hover:underline"><?= h($fa['title']) ?></h3>
            <p class="meta mt-1 flex items-center gap-1">
              <?= icon('clock','w-2.5 h-2.5') ?> <?= time_ago($fa['published_at']??$fa['created_at']) ?>
              &bull; <?= icon('eye','w-2.5 h-2.5') ?> <?= np_number((int)$fa['views']) ?>
            </p>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Article middle ad -->
    <?php render_ads('article-middle'); ?>

    <!-- Category sections -->
    <?php
    $cat_count = 0;
    foreach ($cat_articles as $slug => $block):
        $cat    = $block['cat'];
        $arts   = $block['articles'];
        $lead   = $arts[0];
        $rest   = array_slice($arts, 1, 4);
        $cat_count++;
    ?>
    <div class="mb-8">
      <div class="section-heading" style="border-left-color:<?= h($cat['color']?:accent_color()) ?>">
        <span class="flex items-center gap-2" style="color:<?= h($cat['color']?:accent_color()) ?>">
          <?php if ($cat['icon']): ?><i data-lucide="<?= h($cat['icon']) ?>" class="w-4 h-4"></i><?php endif; ?>
          <?= h($cat['name_np']?:$cat['name']) ?>
        </span>
        <a href="/category/<?= h($cat['slug']) ?>" class="flex items-center gap-1">
          थप हेर्नुस् <?= icon('arrow-right','w-3.5 h-3.5') ?>
        </a>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <!-- Lead article -->
        <a href="/article/<?= h($lead['slug']) ?>" class="article-card block group md:col-span-2">
          <div class="img-wrap">
            <?php if ($lead['image_url']): ?>
              <img src="<?= h($lead['image_url']) ?>" alt="" loading="lazy">
            <?php endif; ?>
          </div>
          <div class="p-3">
            <h3 class="title text-sm group-hover:underline"><?= h($lead['title']) ?></h3>
            <p class="meta mt-1 flex items-center gap-1">
              <?= icon('user','w-2.5 h-2.5') ?> <?= h($lead['author_name']) ?>
              &bull; <?= icon('clock','w-2.5 h-2.5') ?> <?= time_ago($lead['published_at']??$lead['created_at']) ?>
            </p>
          </div>
        </a>
        <!-- Rest as list -->
        <div class="md:col-span-3 space-y-0">
          <?php foreach ($rest as $ra): ?>
          <a href="/article/<?= h($ra['slug']) ?>"
             class="flex gap-3 p-2 rounded group transition-colors hover:bg-white hover:shadow-sm"
             style="border-bottom:1px solid var(--c-border2)">
            <div class="flex-shrink-0 rounded overflow-hidden bg-gray-100" style="width:72px;height:54px">
              <?php if ($ra['image_url']): ?>
                <img src="<?= h($ra['image_url']) ?>" alt="" loading="lazy" class="w-full h-full object-cover">
              <?php endif; ?>
            </div>
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
    </div>

    <!-- In-feed ad after every 3rd category -->
    <?php if ($cat_count % 3 === 0) render_ads('in-feed'); ?>
    <?php endforeach; ?>

  </div><!-- /main -->

  <!-- ════ Sidebar ════ -->
  <aside class="lg:col-span-1">

    <!-- Sidebar top ad -->
    <?php render_ads('sidebar-top'); ?>

    <!-- Most popular -->
    <?php if (!empty($popular)): ?>
    <div class="sidebar-card mb-5">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('flame','w-4 h-4') ?> सबैभन्दा पढिएका</span>
      </div>
      <?php foreach ($popular as $i => $pop): ?>
      <div class="popular-item">
        <span class="popular-num"><?= $i+1 ?></span>
        <div>
          <a href="/article/<?= h($pop['slug']) ?>" class="ptitle block hover:underline">
            <?= h($pop['title']) ?>
          </a>
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
    <?php endif; ?>

    <!-- Latest news -->
    <div class="sidebar-card mb-5">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('newspaper','w-4 h-4') ?> ताजा समाचार</span>
      </div>
      <?php foreach ($latest as $la): ?>
      <div class="sidebar-article">
        <a href="/article/<?= h($la['slug']) ?>" class="thumb flex-shrink-0">
          <?php if ($la['image_url']): ?><img src="<?= h($la['image_url']) ?>" alt="" loading="lazy"><?php endif; ?>
        </a>
        <div class="info flex-1 min-w-0">
          <a href="/article/<?= h($la['slug']) ?>" class="title block hover:underline"><?= h($la['title']) ?></a>
          <div class="meta flex items-center gap-1 flex-wrap">
            <span class="inline-block px-1.5 rounded" style="background:<?= h(category_color($la['category_color'])) ?>;color:#fff;font-size:0.6rem;font-weight:700">
              <?= h($la['category_name_np']?:$la['category_name']) ?>
            </span>
            <?= icon('clock','w-2.5 h-2.5') ?> <?= time_ago($la['published_at']??$la['created_at']) ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Sidebar bottom ad -->
    <?php render_ads('sidebar-bottom'); ?>

    <!-- Newsletter widget -->
    <div class="newsletter-widget mb-5">
      <h3 class="flex items-center gap-2"><?= icon('mail','w-4 h-4') ?> न्यूजलेटर</h3>
      <p>ताजा समाचार इमेलमा पाउनुस्</p>
      <form method="POST" action="/newsletter/subscribe">
        <?= csrf_field() ?>
        <input type="email" name="email" class="newsletter-input" placeholder="तपाईंको इमेल..." required>
        <button type="submit" class="newsletter-btn">सदस्य बन्नुस् →</button>
      </form>
    </div>

    <!-- Tag cloud -->
    <?php if (!empty($all_tags)): ?>
    <div class="sidebar-card mb-5">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('hash','w-4 h-4') ?> ट्यागहरू</span>
      </div>
      <div class="tag-cloud">
        <?php foreach ($all_tags as $tag): ?>
          <a href="/search?q=<?= urlencode($tag['name']) ?>"><?= h($tag['name']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Upcoming events widget -->
    <?php if (!empty($upcoming_evts)): ?>
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
    <?php endif; ?>

    <!-- All categories widget -->
    <div class="sidebar-card">
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
          <?= h($cw['name_np']?:$cw['name']) ?>
        </span>
        <span class="text-xs font-normal" style="color:var(--c-muted)"><?= np_number((int)($cw['article_count']??0)) ?></span>
      </a>
      <?php endforeach; ?>
    </div>

  </aside>

</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
