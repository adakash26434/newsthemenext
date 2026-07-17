<?php
if (is_admin()) { redirect('admin'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    if (admin_login($user, $pass)) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user']      = $user;
        redirect('admin');
    } else {
        $error = 'गलत प्रयोगकर्ता नाम वा पासवर्ड।';
    }
}
$error = $error ?? null;
?>
<!DOCTYPE html>
<html lang="ne" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — <?= h(site_name_np()) ?></title>
<link rel="stylesheet" href="/assets/style.css">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" onload="lucide.createIcons()"></script>
<style>:root{--c-primary:<?= h(primary_color()) ?>;--c-nav-bg:<?= h(nav_color()) ?>;--c-primary-lt:<?= h(accent_color()) ?>}</style>
</head>
<body class="min-h-screen flex items-center justify-center" style="background:var(--c-admin-bg)">
<div class="w-full max-w-sm mx-4">
  <!-- Logo / brand -->
  <div class="text-center mb-8">
    <div class="text-2xl font-extrabold mb-1" style="color:var(--c-primary)"><?= h(site_name_np()) ?></div>
    <div class="text-xs" style="color:var(--c-muted)">Admin Panel</div>
  </div>

  <div class="rounded-xl p-7 shadow-lg" style="background:var(--c-admin-surface);border:1px solid var(--c-admin-border)">
    <h1 class="text-base font-bold mb-5 flex items-center gap-2" style="color:var(--c-text)">
      <i data-lucide="lock" class="w-5 h-5" style="color:var(--c-primary)"></i>
      लगइन गर्नुस्
    </h1>

    <?php if ($error): ?>
    <div class="flash flash-error mb-4">
      <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i> <?= h($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/admin/login" class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="form-label flex items-center gap-1">
          <i data-lucide="user" class="w-3.5 h-3.5"></i> प्रयोगकर्ता नाम
        </label>
        <input type="text" name="username" class="form-control" required autofocus
               value="<?= h($_POST['username'] ?? '') ?>" placeholder="admin">
      </div>
      <div>
        <label class="form-label flex items-center gap-1">
          <i data-lucide="key-round" class="w-3.5 h-3.5"></i> पासवर्ड
        </label>
        <input type="password" name="password" class="form-control" required placeholder="••••••••">
      </div>
      <button type="submit" class="btn btn-primary w-full justify-center gap-2 mt-2">
        <i data-lucide="log-in" class="w-4 h-4"></i> लगइन गर्नुस्
      </button>
    </form>

    <div class="mt-5 pt-4 text-xs text-center" style="border-top:1px solid var(--c-admin-border);color:var(--c-muted)">
      <a href="/" class="flex items-center justify-center gap-1 hover:underline" style="color:var(--c-primary-lt)">
        <i data-lucide="arrow-left" class="w-3 h-3"></i> साइटमा फिर्ता
      </a>
    </div>
  </div>
</div>
</body>
</html>
