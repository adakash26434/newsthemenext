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
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" onload="lucide.createIcons()"></script>
<script>(function(){var t=localStorage.getItem('theme');if(t==='dark')document.documentElement.setAttribute('data-theme','dark');})();</script>
<style>
  :root {
    --c-primary:    <?= h(primary_color()) ?>;
    --c-nav-bg:     <?= h(nav_color()) ?>;
    --c-primary-lt: <?= h(accent_color()) ?>;
  }
  [x-cloak]{display:none!important}
</style>
</head>
<body>
<script>document.addEventListener('DOMContentLoaded',function(){if(window.lucide)lucide.createIcons();});</script>
<?php }

function admin_sidebar(string $active = ''): void {
$links = [
  ['dashboard',      '/admin',               'layout-dashboard', 'ड्यासबोर्ड'],
  ['articles',       '/admin/articles',      'newspaper',        'लेखहरू'],
  ['categories',     '/admin/categories',    'tag',              'श्रेणीहरू'],
  ['authors',        '/admin/authors',       'user-pen',         'लेखकहरू'],
  ['tags',           '/admin/tags',          'hash',             'ट्यागहरू'],
  ['advertisements', '/admin/advertisements','megaphone',        'विज्ञापन'],
  ['events',         '/admin/events',        'calendar-days',    'कार्यक्रम'],
  ['pages',          '/admin/pages',         'file-text',        'पृष्ठहरू'],
  ['subscribers',    '/admin/subscribers',   'mail',             'न्यूजलेटर'],
  ['settings',       '/admin/settings',      'settings',         'सेटिङ्स'],
]; ?>
<aside class="admin-sidebar" :class="sidebarOpen ? '' : '-translate-x-full'">
  <div class="brand">
    <span><?= h(site_name_np()) ?></span>
    <small>Admin Panel</small>
  </div>
  <nav class="admin-nav flex-1 py-2 overflow-y-auto">
    <?php foreach ($links as [$key, $href, $icon, $label]): ?>
    <a href="<?= $href ?>" class="<?= $active === $key ? 'active' : '' ?>">
      <i data-lucide="<?= h($icon) ?>" class="w-4 h-4 flex-shrink-0"></i>
      <?= h($label) ?>
    </a>
    <?php endforeach; ?>
  </nav>
  <div class="px-4 pb-4 flex-shrink-0" style="border-top:1px solid rgba(255,255,255,0.1)">
    <a href="/" target="_blank" class="sidebar-bottom-link mt-3">
      <i data-lucide="external-link" class="w-3.5 h-3.5"></i> साइट हेर्नुस्
    </a>
    <form method="POST" action="/admin/logout">
      <?= csrf_field() ?>
      <button type="submit" class="sidebar-bottom-link w-full text-left">
        <i data-lucide="log-out" class="w-3.5 h-3.5"></i> लगआउट
      </button>
    </form>
  </div>
</aside>
<div class="admin-sidebar-overlay lg:hidden" x-show="sidebarOpen" x-cloak @click="sidebarOpen=false" style="display:none"></div>
<?php }

function admin_topbar(string $title = ''): void { ?>
<header class="admin-topbar">
  <div class="flex items-center gap-3">
    <button class="lg:hidden p-2 rounded" @click="sidebarOpen=!sidebarOpen" style="background:var(--c-border2)">
      <i data-lucide="menu" class="w-5 h-5"></i>
    </button>
    <h1 class="text-sm font-bold" style="color:var(--c-text)"><?= h($title) ?></h1>
  </div>
  <div class="flex items-center gap-3">
    <button class="dark-toggle" @click="darkMode=!darkMode" title="Theme toggle">
      <span x-show="!darkMode"><i data-lucide="moon" class="w-4 h-4"></i></span>
      <span x-show="darkMode" x-cloak><i data-lucide="sun" class="w-4 h-4"></i></span>
    </button>
    <span class="text-xs font-medium hidden sm:flex items-center gap-1" style="color:var(--c-muted)">
      <i data-lucide="circle-user" class="w-4 h-4"></i> <?= h(setting('admin_username', DEFAULT_ADMIN_USERNAME)) ?>
    </span>
  </div>
</header>
<?php }

function admin_flash(): void {
  $s = flash_get('success');
  $e = flash_get('error');
  if ($s) echo '<div class="flash flash-success"><i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>' . h($s) . '</div>';
  if ($e) echo '<div class="flash flash-error"><i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>'   . h($e) . '</div>';
}
