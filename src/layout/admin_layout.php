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
<!-- Quill.js for WYSIWYG editor -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
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
  
  /* Toast Notifications */
  .toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 99999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    pointer-events: none;
  }
  .toast {
    background: var(--c-admin-surface);
    border: 1px solid var(--c-admin-border);
    border-radius: var(--r-md);
    padding: 14px 18px;
    box-shadow: var(--shadow-lg);
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 280px;
    max-width: 400px;
    animation: toastIn 0.3s ease-out;
    pointer-events: auto;
  }
  .toast.success { border-left: 4px solid var(--c-success); }
  .toast.error { border-left: 4px solid var(--c-error); }
  .toast.warning { border-left: 4px solid var(--c-warning); }
  .toast.info { border-left: 4px solid var(--c-info); }
  .toast-icon { flex-shrink: 0; }
  .toast.success .toast-icon { color: var(--c-success); }
  .toast.error .toast-icon { color: var(--c-error); }
  .toast.warning .toast-icon { color: var(--c-warning); }
  .toast.info .toast-icon { color: var(--c-info); }
  .toast-content { flex: 1; }
  .toast-title { font-weight: 600; font-size: 0.85rem; color: var(--c-text); }
  .toast-message { font-size: 0.8rem; color: var(--c-muted); margin-top: 2px; }
  .toast-close {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--c-muted);
    padding: 4px;
    border-radius: var(--r-sm);
    transition: color 0.15s, background 0.15s;
  }
  .toast-close:hover { color: var(--c-text); background: var(--c-admin-bg); }
  @keyframes toastIn {
    from { opacity: 0; transform: translateX(100%); }
    to { opacity: 1; transform: translateX(0); }
  }
  @keyframes toastOut {
    from { opacity: 1; transform: translateX(0); }
    to { opacity: 0; transform: translateX(100%); }
  }
  
  /* Quill Editor Customization */
  .ql-toolbar.ql-snow {
    border: 1px solid var(--c-admin-border);
    border-bottom: none;
    border-radius: var(--r-md) var(--r-md) 0 0;
    background: var(--c-admin-bg);
  }
  .ql-container.ql-snow {
    border: 1px solid var(--c-admin-border);
    border-radius: 0 0 var(--r-md) var(--r-md);
    font-family: inherit;
    font-size: 0.95rem;
  }
  .ql-editor { min-height: 250px; }
  .ql-snow .ql-stroke { stroke: var(--c-text2); }
  .ql-snow .ql-fill { fill: var(--c-text2); }
  .ql-snow .ql-picker { color: var(--c-text2); }
  [data-theme="dark"] .ql-toolbar.ql-snow,
  [data-theme="dark"] .ql-container.ql-snow {
    border-color: var(--c-admin-border);
  }
</style>
</head>
<body>

<!-- Toast Notifications Container -->
<div class="toast-container" id="toast-container"></div>

<script>
window.showToast = function(type, title, message, duration = 5000) {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const icons = {
        success: 'check-circle',
        error: 'alert-circle',
        warning: 'alert-triangle',
        info: 'info'
    };
    
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <i data-lucide="${icons[type] || 'info'}" class="toast-icon w-5 h-5"></i>
        <div class="toast-content">
            <div class="toast-title">${title}</div>
            ${message ? `<div class="toast-message">${message}</div>` : ''}
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    `;
    
    container.appendChild(toast);
    if (window.lucide) lucide.createIcons();
    
    if (duration > 0) {
        setTimeout(() => {
            toast.style.animation = 'toastOut 0.3s ease-in forwards';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
}

// Auto-show flash messages as toasts
document.addEventListener('DOMContentLoaded', function() {
    const flashSuccess = document.querySelector('.flash-success');
    const flashError = document.querySelector('.flash-error');
    
    if (flashSuccess) {
        const text = flashSuccess.textContent.trim();
        showToast('success', 'सफल', text);
        flashSuccess.remove();
    }
    if (flashError) {
        const text = flashError.textContent.trim();
        showToast('error', 'त्रुटि', text);
        flashError.remove();
    }
});
</script>
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
  ['comments',       '/admin/comments',      'message-circle',   'टिप्पणीहरू'],
  ['media',          '/admin/media',         'image',            'मिडिया'],
  ['subscribers',    '/admin/subscribers',   'mail',             'न्यूजलेटर'],
  ['epaper',         '/admin/epaper',        'newspaper',        'ई-पेपर'],
  ['market',         '/admin/market',        'bar-chart-2',      'बजार दर'],
  ['horoscope',      '/admin/horoscope',     'sparkles',         'ज्योतिष'],
  ['live_data',      '/admin/live_data',     'activity',         'Live Data'],
  ['redirects',      '/admin/redirects',     'corner-right-down','रिडाइरेक्ट'],
  ['settings',       '/admin/settings',      'settings',         'सेटिङ्स'],
  ['ai_settings',    '/admin/ai_settings',   'bot',              'AI Chat'],
]; ?>
<aside class="admin-sidebar" :class="sidebarOpen ? '' : '-translate-x-full'">
  <div class="brand">
    <span><?= h(site_name_np()) ?></span>
    <small>Admin Panel</small>
  </div>
  <nav class="admin-nav flex-1 py-2 overflow-y-auto">
    <?php
    $pending_comments = 0;
    try { $pending_comments = count_comments('pending'); } catch(\Exception $e){}
    ?>
    <?php foreach ($links as [$key, $href, $icon, $label]): ?>
    <a href="<?= $href ?>" class="<?= $active === $key ? 'active' : '' ?>" style="display:flex;align-items:center;gap:.625rem">
      <i data-lucide="<?= h($icon) ?>" class="w-4 h-4 flex-shrink-0"></i>
      <span style="flex:1"><?= h($label) ?></span>
      <?php if ($key === 'comments' && $pending_comments > 0): ?>
      <span class="sidebar-badge"><?= $pending_comments ?></span>
      <?php endif; ?>
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

function admin_html_end(): void { ?>
</div></div>
</body></html>
<?php }

function admin_flash(): void {
  $s = flash_get('success');
  $e = flash_get('error');
  if ($s) echo '<div class="flash flash-success"><i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>' . h($s) . '</div>';
  if ($e) echo '<div class="flash flash-error"><i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>'   . h($e) . '</div>';
}
