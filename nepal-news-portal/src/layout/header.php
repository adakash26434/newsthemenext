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
<meta property="og:title"       content="<?= h($page_title ?? $_site_name_np) ?>">
<meta property="og:description" content="<?= h($page_desc ?? $_site_tagline) ?>">
<meta property="og:type"        content="<?= h($og_type ?? 'website') ?>">
<?php if (!empty($og_image)): ?>
<meta property="og:image" content="<?= h($og_image) ?>">
<?php endif; ?>
<meta name="robots" content="index,follow">
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

<!-- ── Top utility bar ── -->
<div class="top-utility-bar">
  <div class="max-w-7xl mx-auto px-4 flex items-center justify-between flex-wrap gap-1">
    <span class="bs-date flex items-center gap-1.5">
      <?= icon('calendar','w-3 h-3') ?> <?= bs_date_today() ?>
    </span>
    <div class="flex items-center gap-1 flex-wrap">
      <?php if (setting('social_youtube','')): ?>
      <a href="<?= h(setting('social_youtube')) ?>" target="_blank" rel="noopener" class="utility-link flex items-center gap-1">
        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.4a2.78 2.78 0 001.95-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon fill="var(--c-nav-bg,#7F1D1D)" points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg>
        TV
      </a>
      <?php endif; ?>
      <a href="/epaper" class="utility-link flex items-center gap-1">
        <?= icon('newspaper','w-3 h-3') ?> ई-पेपर
      </a>
      <a href="/admin" class="utility-link flex items-center gap-1">
        <?= icon('lock','w-3 h-3') ?> Admin
      </a>
      <!-- Language toggle -->
      <span class="lang-toggle-wrap">
        <a href="?lang=np" class="<?= $_cur_lang==='np'?'active':'' ?>">NP</a>
        <a href="?lang=en" class="<?= $_cur_lang==='en'?'active':'' ?>">EN</a>
      </span>
      <!-- Dark/Light toggle -->
      <button class="dark-toggle" @click="darkMode=!darkMode" title="Dark/Light Mode">
        <span x-show="!darkMode" class="flex items-center gap-1">
          <?= icon('moon','w-3 h-3') ?> <span class="hidden sm:inline">Dark</span>
        </span>
        <span x-show="darkMode" x-cloak class="flex items-center gap-1">
          <?= icon('sun','w-3 h-3') ?> <span class="hidden sm:inline">Light</span>
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
  <nav class="main-nav" aria-label="Main navigation">
    <div class="max-w-7xl mx-auto px-4">
      <ul class="nav-list" :class="mobileNav ? 'mobile-open' : ''" x-show="mobileNav || window.innerWidth >= 1024" x-cloak>
        <li>
          <a href="/" class="<?= $_current_path==='/'?'active':'' ?>">
            <?= icon('home','w-3.5 h-3.5') ?>
            <span><?= $_cur_lang==='en'?'Home':'गृहपृष्ठ' ?></span>
          </a>
        </li>

        <?php foreach (array_slice($_categories, 0, 10) as $_cs): ?>
        <li>
          <a href="/category/<?= h($_cs['slug']) ?>"
             class="<?= str_contains($_current_path, '/category/'.$_cs['slug'])?'active':'' ?>">
            <?php if ($_cs['icon']): ?>
              <i data-lucide="<?= h($_cs['icon']) ?>" class="w-3.5 h-3.5 inline-block align-middle flex-shrink-0"></i>
            <?php endif; ?>
            <span><?= h($_cur_lang==='en' ? ($_cs['name_np']?:$_cs['name']) : ($_cs['name']?:$_cs['name_np'])) ?></span>
          </a>
        </li>
        <?php endforeach; ?>

        <!-- Trending -->
        <li>
          <a href="/trending" class="<?= $_current_path==='/trending'?'active':'' ?>">
            <?= icon('trending-up','w-3.5 h-3.5') ?>
            <span><?= $_cur_lang==='en'?'Trending':'ट्रेन्डिङ' ?></span>
          </a>
        </li>
        <!-- Breaking -->
        <li>
          <a href="/breaking" class="<?= $_current_path==='/breaking'?'active':'' ?>">
            <?= icon('zap','w-3.5 h-3.5') ?>
            <span><?= $_cur_lang==='en'?'Breaking':'ब्रेकिङ' ?></span>
          </a>
        </li>

        <!-- Events dropdown -->
        <li x-data="{open:false}" class="has-dropdown">
          <a href="/events" class="<?= str_starts_with($_current_path,'/event')?'active':'' ?>"
             @mouseenter="open=true" @mouseleave="open=false">
            <?= icon('calendar','w-3.5 h-3.5') ?>
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
      </ul>
    </div>
  </nav>

  <!-- ── Breaking news ticker ── -->
  <?php if (!empty($_breaking)): ?>
  <div class="breaking-ticker" role="marquee" aria-label="Breaking news">
    <div class="max-w-7xl mx-auto px-4 flex items-center gap-3">
      <span class="ticker-label flex items-center gap-1">
        <?= icon('zap','w-3.5 h-3.5') ?> <?= h($_ticker_label) ?>
      </span>
      <div class="ticker-track-wrap">
        <div class="ticker-track">
          <?php foreach ($_breaking as $_bn): ?>
            <a href="/article/<?= h($_bn['slug']) ?>" class="ticker-item">
              <?= h($_cur_lang==='en'?($_bn['title_np']?:$_bn['title']):$_bn['title']) ?>
            </a>
          <?php endforeach; ?>
          <?php foreach ($_breaking as $_bn): // duplicate for seamless loop ?>
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

<!-- ── Search overlay ── -->
<div x-show="searchOpen" x-cloak class="search-overlay"
     @keydown.escape.window="searchOpen=false" @click.self="searchOpen=false"
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
  <div class="search-overlay-box" @click.stop>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-base font-bold flex items-center gap-2">
        <?= icon('search','w-4 h-4') ?>
        <?= $_cur_lang==='en'?'Search News':'समाचार खोज्नुस्' ?>
      </h2>
      <button @click="searchOpen=false" class="p-1 rounded" style="background:none;border:none;cursor:pointer;color:var(--c-muted)">
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
               x-effect="if(searchOpen) $nextTick(()=>$refs.searchInput.focus())">
        <!-- Suggestions dropdown -->
        <div x-show="suggestions.length > 0" x-cloak
             class="absolute left-0 right-0 mt-1 rounded-xl overflow-hidden z-50"
             style="background:var(--c-surface);border:1px solid var(--c-border);box-shadow:0 8px 24px rgba(0,0,0,.15)">
          <template x-for="s in suggestions" :key="s.slug">
            <a :href="'/article/'+s.slug"
               class="flex items-center gap-3 px-4 py-2.5 hover:opacity-80 transition-opacity border-b"
               style="border-color:var(--c-border)"
               @click="searchOpen=false;suggestions=[]">
              <template x-if="s.image">
                <img :src="s.image" class="w-10 h-8 rounded object-cover flex-shrink-0" alt="">
              </template>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold truncate" x-text="s.title" style="color:var(--c-text)"></div>
                <div class="text-xs" x-text="s.category" style="color:var(--c-muted)"></div>
              </div>
            </a>
          </template>
        </div>
      </div>
      <div class="flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary flex-1 justify-center gap-1">
          <?= icon('search','w-4 h-4') ?> <?= $_cur_lang==='en'?'Search':'खोज्नुस्' ?>
        </button>
        <button type="button" @click="searchOpen=false;suggestions=[]" class="btn btn-secondary gap-1">
          <?= icon('x','w-4 h-4') ?> <?= $_cur_lang==='en'?'Cancel':'रद्द' ?>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Back to top -->
<button class="back-to-top" :class="backTop ? '' : 'hidden'" @click="window.scrollTo({top:0,behavior:'smooth'})" title="माथि जानुस्">
  <?= icon('arrow-up','w-5 h-5') ?>
</button>

<!-- Main content wrapper -->
<main class="max-w-7xl mx-auto px-4 py-6">
