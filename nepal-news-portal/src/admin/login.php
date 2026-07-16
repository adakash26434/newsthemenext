<?php
$page_title = 'Admin Login — ' . site_name();
?>
<!DOCTYPE html>
<html lang="ne" data-theme="light" x-data="{darkMode:(localStorage.getItem('theme')==='dark'),init(){if(this.darkMode)document.documentElement.setAttribute('data-theme','dark');this.$watch('darkMode',v=>{document.documentElement.setAttribute('data-theme',v?'dark':'light');localStorage.setItem('theme',v?'dark':'light');})}}" x-init="init()">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= h($page_title) ?></title>
<link rel="stylesheet" href="/assets/style.css">
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<style>
  :root { --c-primary:<?= h(primary_color()) ?>;--c-nav-bg:<?= h(nav_color()) ?>;--c-primary-lt:<?= h(accent_color()) ?>; }
  [x-cloak]{display:none!important}
</style>
</head>
<body class="min-h-screen flex items-center justify-center" style="background:var(--c-bg)">

<div class="w-full max-w-sm">
  <div class="text-center mb-6">
    <?php $logo = site_logo_url(); ?>
    <?php if ($logo): ?>
      <img src="<?= h($logo) ?>" alt="Logo" class="h-12 mx-auto mb-2">
    <?php else: ?>
      <div class="text-3xl font-black mb-1" style="color:var(--c-primary)"><?= h(site_name()) ?></div>
    <?php endif; ?>
    <p class="text-sm" style="color:var(--c-muted)">Admin Panel</p>
  </div>

  <div class="rounded-lg p-8 shadow-lg" style="background:var(--c-surface);border:1px solid var(--c-border)">
    <?php
    $err = flash_get('error');
    if ($err): ?>
      <div class="flash flash-error mb-4"><?= h($err) ?></div>
    <?php endif; ?>

    <form method="POST" action="/admin/login">
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label">प्रयोगकर्ता नाम</label>
        <input type="text" name="username" required autofocus
               class="form-control" placeholder="admin">
      </div>
      <div class="form-group">
        <label class="form-label">पासवर्ड</label>
        <input type="password" name="password" required
               class="form-control" placeholder="••••••••">
      </div>
      <button type="submit" class="btn btn-primary w-full justify-center mt-2">लगइन गर्नुस्</button>
    </form>
  </div>

  <div class="text-center mt-4">
    <a href="/" class="text-sm hover:underline" style="color:var(--c-muted)">&larr; साइटमा फर्कनुस्</a>
    &nbsp;&bull;&nbsp;
    <button @click="darkMode=!darkMode" class="dark-toggle text-xs">
      <span x-show="!darkMode">🌙 Dark</span>
      <span x-show="darkMode" x-cloak>☀️ Light</span>
    </button>
  </div>
</div>

</body>
</html>
