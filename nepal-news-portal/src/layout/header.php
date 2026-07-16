<?php
$_categories    = get_categories();
$_current_path  = current_url();
$_site_name_np  = site_name_np();
$_site_name_en  = site_name_en();
$_site_tagline  = site_tagline();
$_logo_url      = site_logo_url();
$_logo_text     = site_logo_text();
$_primary       = primary_color();
$_nav_color     = nav_color();
$_ticker_label  = setting('ticker_label', 'ताजा खबर');
$_cur_lang      = current_lang();
$_breaking_news = get_breaking_news(8);
$_upcoming_evts = get_upcoming_events(4);
$_footer_pages  = get_static_pages(true);
$_favicon       = setting('favicon_url', '/assets/favicon.svg');

$cat_icons = [
    'arthatantra'=>'💰','banking'=>'🏦','bima'=>'🛡️','share-bazar'=>'📈',
    'corporate'=>'🏢','paryatan'=>'✈️','rajniti'=>'🏛️','samaj'=>'👥',
    'bikas'=>'🔨','bichar'=>'💭','technology'=>'💻','sports'=>'⚽','world'=>'🌍',
];
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
<meta property="og:title"       content="<?= h($page_title ?? $_site_name_np) ?>">
<meta property="og:description" content="<?= h($page_desc ?? $_site_tagline) ?>">
<meta property="og:type"        content="<?= h($og_type ?? 'website') ?>">
<?php if (!empty($og_image)): ?>
<meta property="og:image" content="<?= h($og_image) ?>">
<?php endif; ?>
<meta name="robots" content="index,follow">
<link rel="canonical" href="<?= h((isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']==='on'?'https':'http').'://'.$_SERVER['HTTP_HOST'].$_current_path) ?>">
<link rel="alternate" hreflang="ne" href="?lang=np">
<link rel="alternate" hreflang="en" href="?lang=en">
<link rel="icon" href="<?= h($_favicon) ?>" type="image/svg+xml">
<link rel="stylesheet" href="/assets/style.css">
<script src="https://cdn.tailwindcss.com?plugins=typography"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<!-- Prevent FOUC: apply saved theme immediately -->
<script>
(function(){var t=localStorage.getItem('theme');if(t==='dark')document.documentElement.setAttribute('data-theme','dark');})();
</script>
<style>
  :root {
    --c-primary:    <?= h($_primary) ?>;
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
</head>
<body class="min-h-screen">

<!-- Reading progress bar -->
<div id="read-prog" class="reading-progress"></div>

<!-- ── Top utility bar ── -->
<div class="top-utility-bar">
  <div class="max-w-7xl mx-auto px-4 flex items-center justify-between flex-wrap gap-1">
    <span class="bs-date">📅 <?= bs_date_today() ?></span>
    <div class="flex items-center gap-0.5 flex-wrap">
      <?php if (setting('social_youtube','')): ?>
      <a href="<?= h(setting('social_youtube')) ?>" target="_blank" rel="noopener" class="gap-1">
        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.4a2.78 2.78 0 001.95-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon fill="#fff" points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg>
        TV
      </a>
      <?php endif; ?>
      <a href="/admin" class="gap-1">🔐 Admin</a>
      <!-- Language Toggle -->
      <span class="lang-toggle-wrap">
        <a href="?lang=np" class="<?= $_cur_lang==='np'?'active':'' ?>">NP</a>
        <a href="?lang=en" class="<?= $_cur_lang==='en'?'active':'' ?>">EN</a>
      </span>
      <!-- Dark/Light toggle -->
      <button class="dark-toggle" @click="darkMode=!darkMode" title="Dark/Light Mode">
        <span x-show="!darkMode" class="flex items-center gap-1">🌙 <span class="hidden sm:inline">Dark</span></span>
        <span x-show="darkMode" x-cloak class="flex items-center gap-1">☀️ <span class="hidden sm:inline">Light</span></span>
      </button>
    </div>
  </div>
</div>

<!-- ── Sticky header wrapper ── -->
<div class="header-sticky-wrap" :class="{'scrolled': scrolled}">

  <!-- ── Logo + Search bar ── -->
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

        <!-- Header Banner Ad (desktop) -->
        <div class="flex-1 hidden md:block max-w-2xl">
          <?php render_ads('header-banner'); ?>
        </div>

        <!-- Search + Mobile menu toggle -->
        <div class="flex items-center gap-2 flex-shrink-0">
          <button @click="searchOpen=true" class="search-btn" title="खोज्नुस्" aria-label="Search">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </button>
          <!-- Mobile menu toggle -->
          <button class="lg:hidden p-2 rounded-md" @click="mobileNav=!mobileNav" aria-label="Menu" style="background:var(--c-border2)">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Navigation ── -->
  <nav class="main-nav" :class="{'mobile-nav-open': mobileNav}">
    <div class="max-w-7xl mx-auto px-4">
      <ul class="nav-list" x-show="mobileNav || window.innerWidth >= 1024" x-cloak>
        <li><a href="/" class="<?= $_current_path==='/'?'active':'' ?>">
          <?= $_cur_lang==='en'?'Home':'गृहपृष्ठ' ?>
        </a></li>

        <?php foreach (array_slice($_categories, 0, 10) as $_cs): ?>
        <li>
          <a href="/category/<?= h($_cs['slug']) ?>"
             class="<?= str_contains($_current_path, '/category/'.$_cs['slug'])?'active':'' ?>">
            <?php if ($icon = $cat_icons[$_cs['slug']] ?? ''): ?>
              <span class="cat-nav-icon"><?= $icon ?></span>
            <?php endif; ?>
            <?= h($_cur_lang==='en' ? ($_cs['name_np']?:$_cs['name']) : ($_cs['name']?:$_cs['name_np'])) ?>
          </a>
        </li>
        <?php endforeach; ?>

        <!-- Events dropdown -->
        <li x-data="{open:false}" class="has-dropdown">
          <a href="/events" class="<?= str_starts_with($_current_path,'/event')?'active':'' ?>"
             @mouseenter="open=true" @mouseleave="open=false">
            <?= $_cur_lang==='en'?'Events':'कार्यक्रम' ?> <span class="nav-arrow">▾</span>
          </a>
          <?php if (!empty($_upcoming_evts)): ?>
          <ul class="dropdown-menu" x-show="open" x-cloak
              @mouseenter="open=true" @mouseleave="open=false">
            <li><a href="/events"><?= $_cur_lang==='en'?'All Events':'सबै कार्यक्रम' ?></a></li>
            <?php foreach ($_upcoming_evts as $_ev): ?>
            <li><a href="/event/<?= h($_ev['slug']) ?>"><?= h($_cur_lang==='en'?($_ev['title_en']?:$_ev['title']):$_ev['title']) ?></a></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </li>

      </ul>
    </div>
  </nav>

  <!-- ── Breaking news ticker ── -->
  <?php if (!empty($_breaking_news)): ?>
  <div class="breaking-ticker">
    <div class="max-w-7xl mx-auto px-4 flex items-center gap-3">
      <span class="ticker-label"><?= h($_ticker_label) ?></span>
      <div class="ticker-track-wrap">
        <div class="ticker-track">
          <?php foreach ($_breaking_news as $_bn): ?>
            <a href="/article/<?= h($_bn['slug']) ?>" class="ticker-item">
              <?= h($_cur_lang==='en'?($_bn['title_np']?:$_bn['title']):$_bn['title']) ?>
            </a>
          <?php endforeach; ?>
          <?php /* duplicate for seamless loop */ foreach ($_breaking_news as $_bn): ?>
            <a href="/article/<?= h($_bn['slug']) ?>" class="ticker-item">
              <?= h($_cur_lang==='en'?($_bn['title_np']?:$_bn['title']):$_bn['title']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div><!-- /sticky wrap -->

<!-- Search overlay -->
<div x-show="searchOpen" x-cloak
     class="search-overlay"
     @keydown.escape.window="searchOpen=false"
     @click.self="searchOpen=false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100">
  <div class="search-overlay-box" @click.stop>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-bold"><?= $_cur_lang==='en'?'Search News':'समाचार खोज्नुस्' ?></h2>
      <button @click="searchOpen=false" class="text-2xl leading-none" style="background:none;border:none;cursor:pointer;color:var(--c-muted)">&times;</button>
    </div>
    <form method="GET" action="/search">
      <input type="search" name="q" class="search-overlay-input"
             placeholder="<?= $_cur_lang==='en'?'Search in Nepali or English...':'नेपाली वा English मा खोज्नुस्...' ?>"
             autofocus x-ref="searchInput"
             x-effect="if(searchOpen) $nextTick(()=>$refs.searchInput.focus())">
      <div class="flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary flex-1 justify-center">🔍 <?= $_cur_lang==='en'?'Search':'खोज्नुस्' ?></button>
        <button type="button" @click="searchOpen=false" class="btn btn-secondary"><?= $_cur_lang==='en'?'Cancel':'रद्द' ?></button>
      </div>
    </form>
  </div>
</div>

<!-- Back to top -->
<button class="back-to-top" :class="backTop ? '' : 'hidden'" @click="window.scrollTo({top:0,behavior:'smooth'})" title="माथि जानुस्">
  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
  </svg>
</button>

<!-- Main content start -->
<main class="max-w-7xl mx-auto px-4 py-6">
