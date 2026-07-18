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
$_ticker_label = setting('ticker_label', 'ब्रेकिङ');
$_cur_lang     = current_lang();
$_breaking     = get_breaking_news(10);
$_favicon      = setting('favicon_url', '/assets/favicon.svg');
?>
<!DOCTYPE html>
<html lang="<?= $_cur_lang === 'en' ? 'en' : 'ne' ?>" data-theme="light"
  x-data="{
    darkMode: (localStorage.getItem('theme')==='dark'),
    searchOpen: false,
    mobileNav: false,
    scrolled: false,
    init() {
      if (this.darkMode) document.documentElement.setAttribute('data-theme','dark');
      this.$watch('darkMode', v => {
        document.documentElement.setAttribute('data-theme', v?'dark':'light');
        localStorage.setItem('theme', v?'dark':'light');
      });
      if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
          if (!localStorage.getItem('theme')) this.darkMode = e.matches;
        });
      }
      window.addEventListener('scroll', () => {
        this.scrolled = window.scrollY > 50;
        const el = document.getElementById('read-prog');
        if (el) {
          const d = document.documentElement;
          el.style.width = Math.min((d.scrollTop/(d.scrollHeight-d.clientHeight))*100,100)+'%';
        }
      }, {passive:true});
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
<!-- Open Graph -->
<meta property="og:title"       content="<?= h($page_title ?? $_site_name_np) ?>">
<meta property="og:description" content="<?= h($page_desc ?? $_site_tagline) ?>">
<meta property="og:type"        content="<?= h($og_type ?? 'website') ?>">
<meta property="og:site_name"   content="<?= h($_site_name_en) ?>">
<meta property="og:locale"      content="<?= current_lang()==='en' ? 'en_US' : 'ne_NP' ?>">
<?php if (!empty($og_image)): ?>
<meta property="og:image"        content="<?= h($og_image) ?>">
<meta property="og:image:width"  content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt"    content="<?= h($page_title ?? $_site_name_np) ?>">
<?php endif; ?>
<!-- Twitter Card -->
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?= h($page_title ?? $_site_name_np) ?>">
<meta name="twitter:description" content="<?= h($page_desc ?? $_site_tagline) ?>">
<?php if (!empty($og_image)): ?><meta name="twitter:image" content="<?= h($og_image) ?>"><?php endif; ?>
<meta name="twitter:site"        content="@<?= h(setting('social_twitter_handle','')) ?>">
<link rel="canonical" href="<?= 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ?>">
<link rel="icon" href="<?= h($_favicon) ?>" type="image/svg+xml">
<link rel="alternate" type="application/rss+xml" title="<?= h($_site_name_en) ?> RSS" href="/rss">
<link rel="manifest" href="/assets/manifest.json">
<meta name="theme-color" content="<?= h($_primary) ?>">
<!-- PWA -->
<link rel="apple-touch-icon" href="/assets/favicon.svg">
<!-- Preconnect -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Devanagari:wght@400;500;600;700;800;900&family=Mukta:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<!-- CSS -->
<link rel="stylesheet" href="/assets/style.css?v=2.3">
<!-- Inline brand color tokens -->
<style>
:root {
  --c-primary:   <?= h($_primary) ?>;
  --c-nav-bg:    <?= h($_nav_color) ?>;
  --c-primary-lt:<?= h(lighten_color($_primary)) ?>;
  --c-primary-dk:<?= h(darken_color($_primary)) ?>;
  --c-footer-bg: <?= h($_nav_color) ?>;
}
</style>
<!-- Tailwind CDN -->
<script src="https://cdn.tailwindcss.com?plugins=line-clamp"></script>
<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>
<body>
<!-- Skip link -->
<a href="#main-content" class="skip-link">मुख्य सामग्रीमा जानुस्</a>

<!-- Reading progress bar -->
<div id="read-prog" style="position:fixed;top:0;left:0;height:3px;z-index:9999;background:var(--c-primary);width:0;transition:width 80ms linear"></div>

<!-- ═══════════════════════════════════════════════════════
     TOP UTILITY BAR
════════════════════════════════════════════════════════ -->
<div class="top-utility-bar">
  <div class="max-w-7xl mx-auto px-4 flex items-center justify-between gap-2 h-9">

    <!-- Left: BS date + social icons -->
    <div class="flex items-center gap-3">
      <span class="bs-date hidden sm:inline-flex items-center gap-1 text-xs font-medium" style="color:var(--c-muted)">
        <?= icon('calendar','w-3 h-3') ?>
        <?= bs_date_np() ?>
      </span>
      <span class="h-3 w-px bg-gray-200 hidden sm:block"></span>
      <?php if (setting('social_facebook','')): ?>
      <a href="<?= h(setting('social_facebook')) ?>" target="_blank" rel="noopener" class="utility-link" title="Facebook" aria-label="Facebook">
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
      </a>
      <?php endif; ?>
      <?php if (setting('social_twitter','')): ?>
      <a href="<?= h(setting('social_twitter')) ?>" target="_blank" rel="noopener" class="utility-link" title="Twitter/X" aria-label="Twitter">
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.259 5.63zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
      </a>
      <?php endif; ?>
      <?php if (setting('social_youtube','')): ?>
      <a href="<?= h(setting('social_youtube')) ?>" target="_blank" rel="noopener" class="utility-link" title="YouTube" aria-label="YouTube">
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.4a2.78 2.78 0 001.95-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon fill="white" points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg>
      </a>
      <?php endif; ?>
    </div>

    <!-- Right: ePaper, Login, Search, Dark mode -->
    <div class="flex items-center gap-1">
      <a href="/epaper" class="utility-link gap-1">
        <?= icon('newspaper','w-3.5 h-3.5') ?> <span class="hidden sm:inline">ई-पेपर</span>
      </a>
      <?php if (setting('youtube_channel','')): ?>
      <a href="<?= h(setting('youtube_channel')) ?>" target="_blank" rel="noopener" class="utility-link gap-1">
        <?= icon('tv-2','w-3.5 h-3.5') ?> <span class="hidden sm:inline">TV</span>
      </a>
      <?php endif; ?>
      <!-- Lang toggle -->
      <span class="lang-toggle-wrap ml-1">
        <a href="?lang=np" class="<?= $_cur_lang==='np'?'active':'' ?>">नेपाली</a>
        <a href="?lang=en" class="<?= $_cur_lang==='en'?'active':'' ?>">EN</a>
      </span>
      <!-- Dark mode -->
      <button @click="darkMode=!darkMode" class="dark-toggle ml-1" :title="darkMode ? 'Light Mode' : 'Dark Mode'" aria-label="Toggle dark mode">
        <svg x-show="!darkMode" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        <svg x-show="darkMode" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
      </button>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     LOGO / BRAND AREA
════════════════════════════════════════════════════════ -->
<div class="logo-area" style="background:var(--c-surface);border-bottom:1px solid var(--c-border)">
  <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
    <!-- Logo -->
    <a href="/" class="logo-wrap flex-shrink-0" aria-label="<?= h($_site_name_np) ?> - गृहपृष्ठ">
      <?php if ($_logo_url && !str_contains($_logo_url,'placeholder')): ?>
        <img src="<?= h($_logo_url) ?>" alt="<?= h($_site_name_np) ?>" class="logo-img" width="180" height="54">
      <?php else: ?>
        <div class="flex flex-col">
          <span class="logo-text"><?= h($_logo_text ?: $_site_name_np) ?></span>
          <?php if ($_site_tagline): ?>
            <span class="logo-tagline"><?= h($_site_tagline) ?></span>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </a>

    <!-- Center: tagline (desktop only) -->
    <div class="hidden lg:block text-center flex-1">
      <?php if ($_site_tagline && $_logo_url): ?>
        <p class="text-xs font-medium" style="color:var(--c-muted)"><?= h($_site_tagline) ?></p>
      <?php endif; ?>
    </div>

    <!-- Right: header ad / actions -->
    <div class="flex items-center gap-3">
      <?php render_ads('header-banner', false); ?>
      <!-- Search button -->
      <button @click="searchOpen=true"
              class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all"
              style="background:var(--c-surface2);color:var(--c-text2);border:1px solid var(--c-border)"
              aria-label="खोज्नुस्">
        <?= icon('search','w-4 h-4') ?>
        <span class="hidden md:inline text-xs">खोज्नुस्...</span>
        <kbd class="hidden md:inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-mono" style="background:var(--c-border);color:var(--c-muted)">Ctrl K</kbd>
      </button>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     STICKY HEADER WRAP (nav + ticker)
════════════════════════════════════════════════════════ -->
<div class="header-sticky-wrap" :class="scrolled ? 'scrolled' : ''">

  <!-- ── Main Navigation ── -->
  <nav class="main-nav" aria-label="Main navigation" style="background:var(--c-nav-bg)">
    <div class="max-w-7xl mx-auto px-4 flex items-center justify-between">

      <!-- Nav list -->
      <ul class="nav-list" :class="mobileNav ? 'mobile-open' : ''" role="list">
        <li>
          <a href="/" class="<?= $_current_path==='/'?'active':'' ?>" aria-current="<?= $_current_path==='/'?'page':'false' ?>">
            <?= icon('home','w-3.5 h-3.5 flex-shrink-0') ?> <span><?= $_cur_lang==='en'?'Home':'गृहपृष्ठ' ?></span>
          </a>
        </li>
        <?php foreach (array_slice($_categories, 0, 9) as $c): ?>
        <li>
          <a href="/category/<?= h($c['slug']) ?>"
             class="<?= (strpos($_current_path,'/category/'.$c['slug'])===0)?'active':'' ?>"
             aria-current="<?= (strpos($_current_path,'/category/'.$c['slug'])===0)?'page':'false' ?>">
            <?php if ($c['icon']): ?><i data-lucide="<?= h($c['icon']) ?>" class="w-3.5 h-3.5 flex-shrink-0"></i><?php endif; ?>
            <span><?= h(cat_name($c, $_cur_lang)) ?></span>
          </a>
        </li>
        <?php endforeach; ?>
        <li>
          <a href="/breaking" class="<?= $_current_path==='/breaking'?'active':'' ?>">
            <?= icon('zap','w-3.5 h-3.5 flex-shrink-0') ?> <span><?= $_cur_lang==='en'?'Breaking':'ब्रेकिङ' ?></span>
          </a>
        </li>
        <li>
          <a href="/live-data">
            <?= icon('activity','w-3.5 h-3.5 flex-shrink-0') ?> <span><?= $_cur_lang==='en'?'Markets':'बजार' ?></span>
          </a>
        </li>
        <li>
          <a href="/epaper">
            <?= icon('newspaper','w-3.5 h-3.5 flex-shrink-0') ?> <span>ई-पेपर</span>
          </a>
        </li>
      </ul>

      <!-- Mobile hamburger -->
      <button @click="mobileNav=!mobileNav" class="lg:hidden flex items-center justify-center w-9 h-9 rounded"
              style="color:#fff;background:rgba(255,255,255,.12)" aria-label="Menu" :aria-expanded="mobileNav">
        <svg x-show="!mobileNav" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        <svg x-show="mobileNav" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
  </nav>

  <!-- ── Breaking News Ticker ── -->
  <?php if (!empty($_breaking)): ?>
  <div class="breaking-ticker" role="region" aria-label="Breaking news">
    <div class="max-w-7xl mx-auto px-4 flex items-center gap-0 h-8 overflow-hidden">
      <span class="ticker-label flex items-center gap-1 flex-shrink-0 mr-3">
        <?= icon('zap','w-3.5 h-3.5') ?> <?= h($_ticker_label) ?>
      </span>
      <div class="ticker-track-wrap flex-1 overflow-hidden">
        <div class="ticker-track">
          <?php foreach ($_breaking as $_bn): ?>
            <a href="/article/<?= h($_bn['slug']) ?>" class="ticker-item">
              <span class="ticker-dot"></span><?= h(mb_substr($_bn['title'],0,80)) ?>
            </a>
          <?php endforeach; ?>
          <?php foreach ($_breaking as $_bn): ?>
            <a href="/article/<?= h($_bn['slug']) ?>" class="ticker-item">
              <span class="ticker-dot"></span><?= h(mb_substr($_bn['title'],0,80)) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <button class="ticker-pause flex-shrink-0 ml-2" id="tickerPauseBtn" onclick="tickerToggle()" title="Pause ticker" aria-label="Pause breaking news">
        <?= icon('pause','w-3 h-3') ?>
      </button>
    </div>
  </div>
  <?php endif; ?>

</div><!-- /.header-sticky-wrap -->

<!-- ═══════════════════════════════════════════════════════
     SEARCH OVERLAY
════════════════════════════════════════════════════════ -->
<div x-show="searchOpen" x-cloak x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     class="search-overlay" @keydown.escape.window="searchOpen=false"
     @click.self="searchOpen=false" role="dialog" aria-modal="true" aria-label="Search">
  <div class="search-overlay-box"
       x-data="{
         q:'', suggestions:[], searching:false, timer:null,
         fetch() {
           clearTimeout(this.timer);
           if (this.q.length < 2) { this.suggestions=[]; return; }
           this.searching=true;
           this.timer = setTimeout(() => {
             fetch('/search?q='+encodeURIComponent(this.q)+'&format=json&limit=6')
               .then(r=>r.json()).then(d=>{ this.suggestions=d.results||[]; this.searching=false; })
               .catch(()=>{ this.suggestions=[]; this.searching=false; });
           }, 280);
         }
       }"
       @click.stop>
    <form method="GET" action="/search">
      <div class="relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color:var(--c-muted)"><?= icon('search','w-5 h-5') ?></span>
        <input type="text" name="q" x-model="q" @input="fetch()" @keydown.escape="searchOpen=false"
               class="search-overlay-input pl-10 pr-12" placeholder="<?= $_cur_lang==='en'?'Search articles...':'समाचार खोज्नुस्...' ?>"
               autofocus autocomplete="off" aria-label="Search">
        <button type="button" @click="searchOpen=false;q='';suggestions=[]"
                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 rounded" style="color:var(--c-muted)">
          <?= icon('x','w-4 h-4') ?>
        </button>
      </div>
      <!-- Suggestions -->
      <div x-show="suggestions.length > 0" class="mt-2 rounded-lg overflow-hidden" style="border:1px solid var(--c-border)">
        <template x-for="s in suggestions" :key="s.slug">
          <a :href="'/article/'+s.slug"
             class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors border-b last:border-b-0"
             style="border-color:var(--c-border)"
             @click="searchOpen=false;suggestions=[]">
            <template x-if="s.image">
              <img :src="s.image" class="w-12 h-10 rounded-lg object-cover flex-shrink-0" alt="">
            </template>
            <template x-if="!s.image">
              <div class="w-12 h-10 rounded-lg flex-shrink-0 flex items-center justify-center" style="background:var(--c-surface2)"><?= icon('file-text','w-4 h-4') ?></div>
            </template>
            <div class="flex-1 min-w-0">
              <div class="text-sm font-semibold truncate" x-text="s.title" style="color:var(--c-text)"></div>
              <div class="text-xs mt-0.5" style="color:var(--c-muted)" x-text="s.category"></div>
            </div>
            <?= icon('arrow-right','w-4 h-4 flex-shrink-0') ?>
          </a>
        </template>
      </div>
      <div class="flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary flex-1 justify-center">
          <?= icon('search','w-4 h-4') ?> <?= $_cur_lang==='en'?'Search':'खोज्नुस्' ?>
        </button>
        <button type="button" @click="searchOpen=false;q='';suggestions=[]" class="btn btn-secondary">
          <?= icon('x','w-4 h-4') ?>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Back to top -->
<button id="back-to-top-btn" class="back-to-top" @click="window.scrollTo({top:0,behavior:'smooth'})" title="माथि जानुस्" aria-label="Back to top">
  <?= icon('arrow-up','w-5 h-5') ?>
</button>

<!-- Keyboard shortcut: Ctrl+K opens search -->
<script>
document.addEventListener('keydown', function(e) {
  if ((e.ctrlKey||e.metaKey) && e.key==='k') {
    e.preventDefault();
    document.querySelector('[\\@click]');
    // Trigger Alpine search via CustomEvent
    window.dispatchEvent(new CustomEvent('open-search'));
  }
});

// Ticker pause toggle
function tickerToggle() {
  var t = document.querySelector('.ticker-track');
  var btn = document.getElementById('tickerPauseBtn');
  if (!t) return;
  if (t.style.animationPlayState === 'paused') {
    t.style.animationPlayState = '';
    btn.innerHTML = '<?= icon('pause','w-3 h-3') ?>';
  } else {
    t.style.animationPlayState = 'paused';
    btn.innerHTML = '<?= icon('play','w-3 h-3') ?>';
  }
}

// Back to top visibility
window.addEventListener('scroll', function() {
  var btn = document.getElementById('back-to-top-btn');
  if (btn) btn.classList.toggle('visible', window.scrollY > 400);
}, {passive:true});

// Lucide icons init
document.addEventListener('DOMContentLoaded', function() { lucide.createIcons(); });
</script>

<!-- Main content -->
<main id="main-content" class="max-w-7xl mx-auto px-4 py-6" role="main" tabindex="-1">
