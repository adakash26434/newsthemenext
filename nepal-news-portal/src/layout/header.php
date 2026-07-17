<?php
$_categories   = get_categories();
$_current_path = current_url();
$_site_name_np = site_name_np();
$_site_name_en = site_name_en();
$_site_tagline = site_tagline();
$_logo_url     = site_logo_url();
$_logo_text    = site_logo_text();
$_primary      = primary_color();
$_nav_color    = nav_color();
$_ticker_label = setting('ticker_label', 'ताजा खबर');
$_cur_lang     = current_lang();
$_breaking     = get_breaking_news(8);
$_upcoming_evts= get_upcoming_events(4);
$_favicon      = setting('favicon_url', '/assets/favicon.svg');
?>
<!DOCTYPE html>
<html lang="<?= $_cur_lang === 'en' ? 'en' : 'ne' ?>" data-theme="light"
  x-data="{
    darkMode: (localStorage.getItem('theme')==='dark'),
    searchOpen: false,
    mobileNav: false,
    scrolled: false,
    backTop: false,
    init() {
      if (this.darkMode) document.documentElement.setAttribute('data-theme','dark');
      this.$watch('darkMode', v => {
        document.documentElement.setAttribute('data-theme', v?'dark':'light');
        localStorage.setItem('theme', v?'dark':'light');
      });
      // Auto-detect system preference changes
      if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
          if (!localStorage.getItem('theme')) {
            this.darkMode = e.matches;
          }
        });
      }
      window.addEventListener('scroll', () => {
        this.scrolled = window.scrollY > 60;
        this.backTop  = window.scrollY > 400;
        const el = document.getElementById('read-prog');
        if (el) {
          const d = document.documentElement;
          const pct = (d.scrollTop/(d.scrollHeight - d.clientHeight))*100;
          el.style.width = Math.min(pct,100) + '%';
        }
      });
    }
  }"
  x-init="init()">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($page_title ?? $_site_name_np) ?></title>
<meta name="description" content="<?= h($page_desc ?? $_site_tagline) ?>">
<meta name="keywords" content="<?= h(setting('meta_keywords','नेपाल समाचार,nepal news')) ?>">
<meta name="author" content="<?= h($article['author_name'] ?? '') ?>">
<meta name="robots" content="index,follow, max-image-preview:large">
<?php if (!empty($article['published_at'])): ?>
<meta name="article:published_time" content="<?= date('c', strtotime($article['published_at'])) ?>">
<?php endif; ?>
<!-- Open Graph / Facebook -->
<meta property="og:title"       content="<?= h($page_title ?? $_site_name_np) ?>">
<meta property="og:description" content="<?= h($page_desc ?? $_site_tagline) ?>">
<meta property="og:type"        content="<?= h($og_type ?? 'website') ?>">
<meta property="og:site_name"  content="<?= h($_site_name_en) ?>">
<meta property="og:locale"      content="<?= current_lang()==='en' ? 'en_US' : 'ne_NP' ?>">
<?php if (!empty($og_image)): ?>
<meta property="og:image"       content="<?= h($og_image) ?>">
<meta property="og:image:width"  content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt"    content="<?= h($page_title ?? $_site_name_np) ?>">
<?php endif; ?>
<!-- Twitter Card -->
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?= h($page_title ?? $_site_name_np) ?>">
<meta name="twitter:description" content="<?= h($page_desc ?? $_site_tagline) ?>">
<?php if (!empty($og_image)): ?>
<meta name="twitter:image"       content="<?= h($og_image) ?>">
<?php endif; ?>
<meta name="twitter:site"        content="@<?= h(setting('social_twitter_handle','')) ?>">
<link rel="icon" href="<?= h($_favicon) ?>" type="image/svg+xml">
<link rel="stylesheet" href="/assets/style.css">
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" onload="lucide.createIcons()"></script>
<script>
(function(){
  var t = localStorage.getItem('theme');
  if (!t && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) t = 'dark';
  if (t === 'dark') document.documentElement.setAttribute('data-theme','dark');
})();
</script>
<style>
  :root {
    --c-primary:    <?= h($_primary) ?>;
    --c-secondary:  <?= h(secondary_color()) ?>;
    --c-nav-bg:     <?= h($_nav_color) ?>;
    --c-footer-bg:  <?= h($_nav_color) ?>;
    --c-primary-lt: <?= h(accent_color()) ?>;
  }
  [x-cloak]{display:none!important}
</style>
<?php if (setting('google_analytics')): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= h(setting('google_analytics')) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= h(setting('google_analytics')) ?>');</script>
<?php endif; ?>
<link rel="canonical" href="<?= h(isset($canonical_url) ? $canonical_url : (rtrim(setting('site_url',''), '/') . strtok($_SERVER['REQUEST_URI'] ?? '/', '?'))) ?>">
<link rel="alternate" type="application/rss+xml" title="<?= h(site_name()) ?> RSS" href="/rss.xml">
</head>
<body class="min-h-screen">
<!-- Skip to main content for accessibility -->
<a href="#main-content" class="skip-link">Skip to main content</a>

<?php
// ── Site-wide announcement banner ─────────────────────────
$_ann_text = setting('announcement_text','');
$_ann_url  = setting('announcement_url','');
$_ann_type = setting('announcement_type','info');
$_ann_colors = [
  'info'    => ['background:#1e40af;color:#fff','background:rgba(30,64,175,.15);color:#1e40af;border-bottom:1px solid rgba(30,64,175,.2)'],
  'warning' => ['background:#92400e;color:#fff','background:rgba(146,64,14,.1);color:#92400e;border-bottom:1px solid rgba(146,64,14,.2)'],
  'success' => ['background:#14532d;color:#fff','background:rgba(20,83,45,.1);color:#14532d;border-bottom:1px solid rgba(20,83,45,.2)'],
  'danger'  => ['background:#7F1D1D;color:#fff','background:rgba(127,29,29,.1);color:#7F1D1D;border-bottom:1px solid rgba(127,29,29,.2)'],
];
$_ann_style = ($_ann_colors[$_ann_type] ?? $_ann_colors['info'])[1];
?>
<?php if ($_ann_text): ?>
<div class="text-sm py-2 px-4 text-center" style="<?= $_ann_style ?>">
  <span class="inline-flex items-center gap-2">
    <?= icon('megaphone','w-3.5 h-3.5') ?>
    <?php if ($_ann_url): ?>
      <a href="<?= h($_ann_url) ?>" class="hover:underline font-medium"><?= h($_ann_text) ?></a>
    <?php else: ?>
      <span class="font-medium"><?= h($_ann_text) ?></span>
    <?php endif; ?>
  </span>
</div>
<?php endif; ?>

<div id="read-prog" class="reading-progress"></div>

<!-- ── Top utility bar — Enhanced ── -->
<div class="top-utility-bar" style="background:var(--c-surface2);border-bottom:1px solid var(--c-border)">
  <div class="max-w-7xl mx-auto px-4 flex items-center justify-between flex-wrap gap-2">
    <!-- Date + Social -->
    <div class="flex items-center gap-3 flex-wrap">
      <span class="bs-date flex items-center gap-1.5 font-medium" style="color:var(--c-text2)">
        <?= icon('calendar','w-3.5 h-3.5') ?> <?= bs_date_today() ?>
      </span>
      <?php if (setting('social_facebook','')): ?>
      <a href="<?= h(setting('social_facebook')) ?>" target="_blank" rel="noopener" class="utility-link flex items-center gap-1" title="Facebook">
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
      </a>
      <?php endif; ?>
      <?php if (setting('social_twitter','')): ?>
      <a href="<?= h(setting('social_twitter')) ?>" target="_blank" rel="noopener" class="utility-link flex items-center gap-1" title="Twitter/X">
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
      </a>
      <?php endif; ?>
      <?php if (setting('social_youtube','')): ?>
      <a href="<?= h(setting('social_youtube')) ?>" target="_blank" rel="noopener" class="utility-link flex items-center gap-1" title="YouTube">
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.4a2.78 2.78 0 001.95-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon fill="var(--c-nav-bg,#7F1D1D)" points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg>
        <span class="hidden md:inline">YouTube</span>
      </a>
      <?php endif; ?>
    </div>
    
    <!-- Right side controls -->
    <div class="flex items-center gap-2 flex-wrap">
      <!-- e-Paper -->
      <a href="/epaper" class="utility-link flex items-center gap-1" title="ई-पेपर">
        <?= icon('newspaper','w-3.5 h-3.5') ?>
        <span class="hidden sm:inline">ई-पेपर</span>
      </a>
      
      <!-- Search button -->
      <button @click="searchOpen=true" class="utility-link flex items-center gap-1" title="खोज्नुस्">
        <?= icon('search','w-3.5 h-3.5') ?>
        <span class="hidden md:inline"><?= $_cur_lang==='en'?'Search':'खोज' ?></span>
      </button>
      
      <!-- Language toggle — Enhanced pill style -->
      <div class="flex items-center rounded-full overflow-hidden border" style="border-color:var(--c-border)">
        <a href="?lang=np" 
           class="px-2.5 py-1 text-xs font-bold rounded-full transition-all duration-150
                  <?= $_cur_lang==='np' ? 'text-white' : 'text-muted hover:text-primary' ?>"
           style="<?= $_cur_lang==='np' ? 'background:var(--c-primary)' : '' ?>">
          नेपाली
        </a>
        <a href="?lang=en" 
           class="px-2.5 py-1 text-xs font-bold rounded-full transition-all duration-150
                  <?= $_cur_lang==='en' ? 'text-white' : 'text-muted hover:text-primary' ?>"
           style="<?= $_cur_lang==='en' ? 'background:var(--c-primary)' : '' ?>">
          English
        </a>
      </div>
      
      <!-- Dark/Light toggle — Enhanced icon button -->
      <button 
        class="theme-toggle btn-icon !w-9 !h-9 !rounded-full !border"
        @click="darkMode=!darkMode" 
        :title="darkMode ? 'Light Mode' : 'Dark Mode'"
        style="background:var(--c-surface);border-color:var(--c-border)">
        <!-- Sun icon (shown in dark mode to switch to light) -->
        <span x-show="darkMode" x-cloak class="flex items-center justify-center">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
          </svg>
        </span>
        <!-- Moon icon (shown in light mode to switch to dark) -->
        <span x-show="!darkMode" class="flex items-center justify-center">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
          </svg>
        </span>
      </button>
    </div>
  </div>
</div>

<!-- ── Sticky header wrapper ── -->
<div class="header-sticky-wrap" :class="{'scrolled': scrolled}">

  <!-- Logo row -->
  <div class="site-header">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex items-center justify-between gap-4">
        <!-- Logo -->
        <a href="/" class="logo-wrap flex-shrink-0" aria-label="<?= h($_site_name_np) ?>">
          <?php if ($_logo_url): ?>
            <img src="<?= h($_logo_url) ?>" alt="<?= h($_site_name_np) ?>" class="logo-img">
          <?php else: ?>
            <div class="logo-text"><?= h($_logo_text) ?></div>
            <div class="logo-tagline"><?= h($_site_tagline) ?></div>
          <?php endif; ?>
        </a>

        <!-- Header banner ad -->
        <div class="flex-1 hidden md:block max-w-2xl">
          <?php render_ads('header-banner', false); ?>
        </div>

        <!-- Search + menu toggle -->
        <div class="flex items-center gap-2 flex-shrink-0">
          <button @click="searchOpen=true" class="search-btn" title="खोज्नुस्" aria-label="Search">
            <?= icon('search','w-5 h-5') ?>
          </button>
          <button class="lg:hidden p-2 rounded-md" @click="mobileNav=!mobileNav" aria-label="Menu" style="background:var(--c-border2)">
            <?= icon('menu','w-5 h-5') ?>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Main navigation ── -->
  <nav class="main-nav" aria-label="Main navigation" style="background:linear-gradient(135deg, var(--c-primary) 0%, var(--c-secondary) 100%)">
    <div class="max-w-7xl mx-auto px-4">
      <ul class="nav-list" :class="mobileNav ? 'mobile-open' : ''" x-show="mobileNav || window.innerWidth >= 1024" x-cloak>
        <li>
          <a href="/" class="<?= $_current_path==='/'?'active':'' ?>">
            <?= icon('home','w-4 h-4') ?>
            <span><?= $_cur_lang==='en'?'Home':'गृहपृष्ठ' ?></span>
          </a>
        </li>

        <?php foreach (array_slice($_categories, 0, 10) as $_cs): ?>
        <li>
          <a href="/category/<?= h($_cs['slug']) ?>"
             class="<?= str_contains($_current_path, '/category/'.$_cs['slug'])?'active':'' ?>"
             style="<?= !empty($_cs['color']) ? '--cat-color:' . h($_cs['color']) : '' ?>">
            <?php if ($_cs['icon']): ?>
              <i data-lucide="<?= h($_cs['icon']) ?>" class="w-4 h-4 inline-block align-middle flex-shrink-0"></i>
            <?php else: ?>
              <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:var(--cat-color, var(--c-primary-lt))"></span>
            <?php endif; ?>
            <span><?= h($_cur_lang==='en' ? ($_cs['name_np']?:$_cs['name']) : ($_cs['name']?:$_cs['name_np'])) ?></span>
          </a>
        </li>
        <?php endforeach; ?>

        <!-- More dropdown for extra categories -->
        <?php if (count($_categories) > 10): ?>
        <li x-data="{open:false}" class="has-dropdown">
          <a href="#" @mouseenter="open=true" @mouseleave="open=false" class="flex items-center gap-1">
            <?= icon('more-horizontal','w-4 h-4') ?>
            <span><?= $_cur_lang==='en'?'More':'थप' ?></span>
            <?= icon('chevron-down','w-3 h-3') ?>
          </a>
          <ul class="dropdown-menu" x-show="open" x-cloak
              @mouseenter="open=true" @mouseleave="open=false">
            <?php foreach (array_slice($_categories, 10) as $_cs): ?>
            <li>
              <a href="/category/<?= h($_cs['slug']) ?>">
                <?php if ($_cs['icon']): ?>
                  <i data-lucide="<?= h($_cs['icon']) ?>" class="w-3.5 h-3.5 inline-block align-middle mr-1"></i>
                <?php endif; ?>
                <?= h($_cur_lang==='en' ? ($_cs['name_np']?:$_cs['name']) : ($_cs['name']?:$_cs['name_np'])) ?>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Trending -->
        <li>
          <a href="/trending" class="<?= $_current_path==='/trending'?'active':'' ?>">
            <?= icon('trending-up','w-4 h-4') ?>
            <span><?= $_cur_lang==='en'?'Trending':'ट्रेन्डिङ' ?></span>
          </a>
        </li>
        <!-- Breaking -->
        <li>
          <a href="/breaking" class="<?= $_current_path==='/breaking'?'active':'' ?>">
            <?= icon('zap','w-4 h-4') ?>
            <span><?= $_cur_lang==='en'?'Breaking':'ब्रेकिङ' ?></span>
          </a>
        </li>

        <!-- Events dropdown -->
        <li x-data="{open:false}" class="has-dropdown">
          <a href="/events" class="<?= str_starts_with($_current_path,'/event')?'active':'' ?>"
             @mouseenter="open=true" @mouseleave="open=false">
            <?= icon('calendar','w-4 h-4') ?>
            <span><?= $_cur_lang==='en'?'Events':'कार्यक्रम' ?></span>
            <?= icon('chevron-down','w-3 h-3') ?>
          </a>
          <?php if (!empty($_upcoming_evts)): ?>
          <ul class="dropdown-menu" x-show="open" x-cloak
              @mouseenter="open=true" @mouseleave="open=false">
            <li><a href="/events"><?= $_cur_lang==='en'?'All Events':'सबै कार्यक्रम' ?></a></li>
            <?php foreach ($_upcoming_evts as $_ev): ?>
            <li>
              <a href="/event/<?= h($_ev['slug']) ?>">
                <?= h($_cur_lang==='en'?($_ev['title_en']?:$_ev['title']):$_ev['title']) ?>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </li>

        <!-- Horoscope dropdown -->
        <li x-data="{open:false}" class="has-dropdown">
          <a href="/horoscope" class="<?= str_starts_with($_current_path,'/horoscope')?'active':'' ?>"
             @mouseenter="open=true" @mouseleave="open=false">
            <?= icon('sparkles','w-4 h-4') ?>
            <span><?= $_cur_lang==='en'?'Horoscope':'ज्योतिष' ?></span>
            <?= icon('chevron-down','w-3 h-3') ?>
          </a>
          <ul class="dropdown-menu" x-show="open" x-cloak
              @mouseenter="open=true" @mouseleave="open=false">
            <li><a href="/horoscope?tab=daily"><?= $_cur_lang==='en'?'Daily Rashifal':'दैनिक राशिफल' ?></a></li>
            <li><a href="/horoscope?tab=monthly"><?= $_cur_lang==='en'?'Monthly Rashifal':'मासिक राशिफल' ?></a></li>
            <li><a href="/horoscope?tab=yearly"><?= $_cur_lang==='en'?'Yearly Rashifal':'वार्षिक राशिफल' ?></a></li>
            <li class="dropdown-divider"></li>
            <li><a href="/horoscope?tab=subhatime"><?= $_cur_lang==='en'?'Auspicious Time':'शुभ समय' ?></a></li>
            <li><a href="/horoscope?tab=subhadin"><?= $_cur_lang==='en'?'Auspicious Days':'शुभ दिन' ?></a></li>
            <li><a href="/horoscope?tab=lagna"><?= $_cur_lang==='en'?'Lagna':'लग्न' ?></a></li>
            <li class="dropdown-divider"></li>
            <li><a href="/horoscope?tab=bastu"><?= $_cur_lang==='en'?'Bastu':'बस्तु' ?></a></li>
            <li><a href="/horoscope?tab=gudmilan"><?= $_cur_lang==='en'?'Gud Milan':'गुड मिलन' ?></a></li>
          </ul>
        </li>

        <!-- Live Data dropdown -->
        <li x-data="{open:false}" class="has-dropdown">
          <a href="/live-data" class="<?= str_starts_with($_current_path,'/live-data')?'active':'' ?>"
             @mouseenter="open=true" @mouseleave="open=false">
            <?= icon('activity','w-4 h-4') ?>
            <span><?= $_cur_lang==='en'?'Live Data':'लाइभ डेटा' ?></span>
            <?= icon('chevron-down','w-3 h-3') ?>
          </a>
          <ul class="dropdown-menu" x-show="open" x-cloak
              @mouseenter="open=true" @mouseleave="open=false">
            <li><a href="/live-data?tab=earthquake"><?= $_cur_lang==='en'?'Earthquakes':'भूकम्प' ?></a></li>
            <li><a href="/live-data?tab=weather"><?= $_cur_lang==='en'?'Weather':'मौसम' ?></a></li>
            <li><a href="/live-data?tab=air"><?= $_cur_lang==='en'?'Air Quality':'वातावरण' ?></a></li>
            <li class="dropdown-divider"></li>
            <li><a href="/live-data?tab=alerts"><?= $_cur_lang==='en'?'Alerts':'चेतावनी' ?></a></li>
            <li><a href="/live-data?tab=notices"><?= $_cur_lang==='en'?'Government Notices':'सरकारी सूचना' ?></a></li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>

  <!-- ── Breaking news ticker ── -->
  <?php if (!empty($_breaking)): ?>
  <div class="breaking-ticker" role="marquee" aria-label="Breaking news" style="background:linear-gradient(90deg, var(--c-surface2) 0%, var(--c-bg) 50%, var(--c-surface2) 100%);border-top:1px solid var(--c-border);border-bottom:1px solid var(--c-border)">
    <div class="max-w-7xl mx-auto px-4 flex items-center gap-3">
      <span class="ticker-label flex items-center gap-1.5" style="animation:pulse-glow 2s ease-in-out infinite">
        <?= icon('zap','w-4 h-4') ?> <?= h($_ticker_label) ?>
      </span>
      <div class="ticker-track-wrap">
        <div class="ticker-track">
          <?php foreach ($_breaking as $_bn): ?>
            <a href="/article/<?= h($_bn['slug']) ?>" class="ticker-item">
              <span class="inline-block w-1.5 h-1.5 rounded-full mr-2" style="background:var(--c-primary)"></span>
              <?= h($_cur_lang==='en'?($_bn['title_np']?:$_bn['title']):$_bn['title']) ?>
            </a>
          <?php endforeach; ?>
          <?php foreach ($_breaking as $_bn): // duplicate for seamless loop ?>
            <a href="/article/<?= h($_bn['slug']) ?>" class="ticker-item">
              <span class="inline-block w-1.5 h-1.5 rounded-full mr-2" style="background:var(--c-primary)"></span>
              <?= h($_cur_lang==='en'?($_bn['title_np']?:$_bn['title']):$_bn['title']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <style>
    @keyframes pulse-glow {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.7; }
    }
  </style>
  <?php endif; ?>

</div><!-- /sticky wrap -->

<!-- ── Search overlay ── -->
<div x-show="searchOpen" x-cloak class="search-overlay"
     @keydown.escape.window="searchOpen=false" @click.self="searchOpen=false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     style="background:rgba(0,0,0,.6);backdrop-filter:blur(8px)">
  <div class="search-overlay-box" @click.stop style="background:var(--c-surface);border-radius:var(--r-xl);padding:24px;max-width:580px;width:100%;box-shadow:var(--shadow-xl)">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-bold flex items-center gap-2" style="color:var(--c-text)">
        <?= icon('search','w-5 h-5') ?>
        <?= $_cur_lang==='en'?'Search News':'समाचार खोज्नुस्' ?>
      </h2>
      <button @click="searchOpen=false" class="btn-icon" style="width:32px;height:32px">
        <?= icon('x','w-5 h-5') ?>
      </button>
    </div>
    <form method="GET" action="/search" autocomplete="off"
          x-data="{suggestions:[], sq:'', loadSugg(){
            if(this.sq.length < 2){this.suggestions=[];return;}
            fetch('/search?q='+encodeURIComponent(this.sq)+'&ajax=suggest')
              .then(r=>r.json()).then(d=>this.suggestions=d).catch(()=>{this.suggestions=[];});
          }}">
      <div class="relative">
        <input type="search" name="q" class="search-overlay-input"
               placeholder="<?= $_cur_lang==='en'?'Search in Nepali or English...':'नेपाली वा English मा खोज्नुस्...' ?>"
               autofocus x-ref="searchInput"
               x-model="sq"
               @input.debounce.300ms="loadSugg()"
               @keydown.escape.stop="suggestions=[]"
               x-effect="if(searchOpen) $nextTick(()=>$refs.searchInput.focus())"
               style="width:100%;padding:14px 16px;border-radius:var(--r-lg);border:2px solid var(--c-border);background:var(--c-bg);font-size:15px;outline:none;transition:border-color .2s"
               @focus="this.style.borderColor='var(--c-primary)'"
               @blur="this.style.borderColor='var(--c-border)'">
        <!-- Search icon inside input -->
        <span class="absolute left-4 top-1/2 -translate-y-1/2" style="color:var(--c-muted)">
          <?= icon('search','w-5 h-5') ?>
        </span>
        <input type="search" x-model="sq" @input.debounce.300ms="loadSugg()" @keydown.escape.stop="suggestions=[]"
               x-effect="if(searchOpen) $nextTick(()=>$refs.searchInput.focus())" class="absolute left-0 top-0 w-full h-full opacity-0" style="pointer-events:none" autofocus>
        <!-- Suggestions dropdown -->
        <div x-show="suggestions.length > 0" x-cloak
             class="absolute left-0 right-0 mt-2 rounded-xl overflow-hidden z-50"
             style="background:var(--c-surface);border:1px solid var(--c-border);box-shadow:var(--shadow-lg);max-height:400px;overflow-y:auto">
          <template x-for="s in suggestions" :key="s.slug">
            <a :href="'/article/'+s.slug"
               class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors border-b last:border-b-0"
               style="border-color:var(--c-border)"
               @click="searchOpen=false;suggestions=[]">
              <template x-if="s.image">
                <img :src="s.image" class="w-12 h-10 rounded-lg object-cover flex-shrink-0" alt="">
              </template>
              <template x-if="!s.image">
                <div class="w-12 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background:var(--c-surface2)">
                  <?= icon('file-text','w-5 h-5') ?>
                </div>
              </template>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold truncate" x-text="s.title" style="color:var(--c-text)"></div>
                <div class="text-xs flex items-center gap-1 mt-0.5" style="color:var(--c-muted)">
                  <template x-if="s.category">
                    <span x-text="s.category"></span>
                  </template>
                </div>
              </div>
              <span class="flex-shrink-0" style="color:var(--c-muted)">
                <?= icon('arrow-right','w-4 h-4') ?>
              </span>
            </a>
          </template>
        </div>
      </div>
      <div class="flex gap-3 mt-4">
        <button type="submit" class="btn btn-primary flex-1 justify-center gap-2" style="padding:12px">
          <?= icon('search','w-4 h-4') ?> <?= $_cur_lang==='en'?'Search':'खोज्नुस्' ?>
        </button>
        <button type="button" @click="searchOpen=false;suggestions=[]" class="btn btn-secondary gap-1">
          <?= icon('x','w-4 h-4') ?> <?= $_cur_lang==='en'?'Cancel':'रद्द' ?>
        </button>
      </div>
      <!-- Search tips -->
      <p class="text-xs mt-3 text-center" style="color:var(--c-muted)">
        <?= $_cur_lang==='en' ? 'Press ESC to close' : 'ESC दबाउनुस् बन्द गर्न' ?>
      </p>
    </form>
  </div>
</div>

<!-- Back to top -->
<button class="back-to-top" :class="backTop ? '' : 'hidden'" @click="window.scrollTo({top:0,behavior:'smooth'})" title="माथि जानुस्" aria-label="Go to top">
  <?= icon('arrow-up','w-5 h-5') ?>
</button>

<!-- Main content wrapper -->
<main id="main-content" class="max-w-7xl mx-auto px-4 py-6" role="main" tabindex="-1">
