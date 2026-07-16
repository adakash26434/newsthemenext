<?php
// Admin layout helper functions

function admin_html_start(string $title = 'Admin'): void { ?>
<!DOCTYPE html>
<html lang="ne" data-theme="light" x-data="{
  darkMode: (localStorage.getItem('theme')==='dark'),
  sidebarOpen: window.innerWidth >= 1024,
  init() {
    if (this.darkMode) document.documentElement.setAttribute('data-theme','dark');
    this.$watch('darkMode', v => {
      document.documentElement.setAttribute('data-theme', v?'dark':'light');
      localStorage.setItem('theme', v?'dark':'light');
    });
  }
}" x-init="init()">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($title) ?> — <?= h(site_name_np()) ?> Admin</title>
<link rel="stylesheet" href="/assets/style.css">
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
  :root {
    --c-primary: <?= h(primary_color()) ?>;
    --c-nav-bg:  <?= h(nav_color()) ?>;
    --c-primary-lt: <?= h(accent_color()) ?>;
  }
  [x-cloak] { display:none !important; }
</style>
</head>
<body>
<?php }

function admin_sidebar(string $active = ''): void {
$links = [
  ['dashboard',      '/admin',                      'dashboard',    'ड्यासबोर्ड',         '0 0 24 24','M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
  ['articles',       '/admin/articles',             'articles',     'लेखहरू',              '0 0 24 24','M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9'],
  ['categories',     '/admin/categories',           'categories',   'श्रेणीहरू',           '0 0 24 24','M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
  ['authors',        '/admin/authors',              'authors',      'लेखकहरू',             '0 0 24 24','M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0'],
  ['tags',           '/admin/tags',                 'tags',         'ट्यागहरू',            '0 0 24 24','M7 20l4-16m2 16l4-16M6 9h14M4 15h14'],
  ['advertisements', '/admin/advertisements',       'ads',          'विज्ञापन',            '0 0 24 24','M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
  ['events',         '/admin/events',               'events',       'कार्यक्रम',           '0 0 24 24','M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
  ['pages',          '/admin/pages',                'pages',        'पृष्ठहरू',            '0 0 24 24','M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
  ['subscribers',    '/admin/subscribers',          'subscribers',  'न्यूजलेटर',          '0 0 24 24','M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
  ['settings',       '/admin/settings',             'settings',     'सेटिङ्स',            '0 0 24 24','M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z'],
];
?>
<aside class="admin-sidebar" :class="sidebarOpen ? '' : '-translate-x-full'" x-bind:class="{ '-translate-x-full': !sidebarOpen }">
  <div class="brand">
    <span><?= h(site_name_np()) ?></span>
    <small>Admin Panel</small>
  </div>
  <nav class="admin-nav flex-1 py-2 overflow-y-auto">
    <?php foreach ($links as [$key, $href, $icon_key, $label, $vb, $path]): ?>
      <a href="<?= $href ?>" class="<?= $active === $key ? 'active' : '' ?>">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="<?= $vb ?>">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $path ?>"/>
        </svg>
        <?= h($label) ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="px-4 pb-4 flex-shrink-0 border-t" style="border-color:rgba(255,255,255,0.1)">
    <a href="/" target="_blank" class="flex items-center gap-2 text-xs mt-3 mb-1 px-2 py-1.5 rounded hover:bg-white hover:bg-opacity-10" style="color:rgba(255,255,255,0.65)">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
      </svg>
      साइट हेर्नुस्
    </a>
    <form method="POST" action="/admin/logout">
      <?= csrf_field() ?>
      <button type="submit" class="w-full text-left flex items-center gap-2 text-xs px-2 py-1.5 rounded hover:bg-white hover:bg-opacity-10" style="background:none;border:none;cursor:pointer;color:rgba(255,255,255,0.65);font-family:inherit">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
        </svg>
        लगआउट
      </button>
    </form>
  </div>
</aside>
<!-- Mobile overlay -->
<div class="admin-sidebar-overlay lg:hidden" x-show="sidebarOpen" x-cloak @click="sidebarOpen=false" style="display:none"></div>
<?php }

function admin_topbar(string $title = ''): void { ?>
<header class="admin-topbar">
  <div class="flex items-center gap-3">
    <button class="lg:hidden p-2 rounded" @click="sidebarOpen=!sidebarOpen" style="background:var(--c-border2)">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
    </button>
    <h1 class="text-sm font-bold" style="color:var(--c-text)"><?= h($title) ?></h1>
  </div>
  <div class="flex items-center gap-3">
    <button class="dark-toggle" @click="darkMode=!darkMode" title="Theme toggle">
      <span x-show="!darkMode">🌙</span>
      <span x-show="darkMode" x-cloak>☀️</span>
    </button>
    <span class="text-xs font-medium hidden sm:block" style="color:var(--c-muted)">
      👤 <?= h(setting('admin_username', DEFAULT_ADMIN_USERNAME)) ?>
    </span>
  </div>
</header>
<?php }

function admin_flash(): void {
  $s = flash_get('success');
  $e = flash_get('error');
  if ($s) echo '<div class="flash flash-success">' . h($s) . '</div>';
  if ($e) echo '<div class="flash flash-error">'   . h($e) . '</div>';
}
