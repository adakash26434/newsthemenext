<?php
$static = get_static_page($_slug ?? '');
if (!$static) { http_response_code(404); require SRC_DIR . '/pages/404.php'; exit; }

$lang        = current_lang();
$page_title  = ($lang==='en' ? ($static['title_en'] ?: $static['title']) : $static['title']) . ' — ' . site_name();
$page_body   = $lang==='en' ? ($static['body_en'] ?: $static['body']) : $static['body'];
$page_title_txt = $lang==='en' ? ($static['title_en'] ?: $static['title']) : $static['title'];

require SRC_DIR . '/layout/header.php';
?>

<!-- Breadcrumb -->
<nav class="breadcrumb mb-5" aria-label="Breadcrumb">
  <a href="/"><?= lang_label('गृहपृष्ठ','Home') ?></a>
  <span>›</span>
  <span><?= h($page_title_txt) ?></span>
</nav>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
  <!-- Main content -->
  <article class="lg:col-span-3">
    <div class="stat-card px-6 py-8">
      <h1 class="text-2xl sm:text-3xl font-extrabold mb-6" style="color:var(--c-primary)">
        <?= h($page_title_txt) ?>
      </h1>
      <div class="prose-content static-page-body">
        <?= $page_body ?>
      </div>
    </div>
  </article>

  <!-- Sidebar -->
  <aside class="space-y-5">
    <!-- Other pages -->
    <?php $all_pages = get_static_pages(); ?>
    <?php if (count($all_pages) > 1): ?>
    <div class="stat-card">
      <h3 class="font-bold text-sm mb-3 pb-2" style="border-bottom:1px solid var(--c-border)">
        <?= lang_label('अन्य पृष्ठहरू','Other Pages') ?>
      </h3>
      <ul class="space-y-1.5">
        <?php foreach ($all_pages as $pg): ?>
        <li>
          <a href="/page/<?= h($pg['slug']) ?>"
             class="text-sm <?= $pg['slug']===$_slug?'font-bold':'hover:underline' ?>"
             style="color:var(--c-primary-lt)">
            › <?= h($lang==='en'?($pg['title_en']?:$pg['title']):$pg['title']) ?>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <!-- Contact info -->
    <?php $c_email = setting('contact_email',''); $c_phone = setting('contact_phone',''); ?>
    <?php if ($c_email || $c_phone): ?>
    <div class="stat-card">
      <h3 class="font-bold text-sm mb-3">📞 <?= lang_label('सम्पर्क','Contact') ?></h3>
      <div class="space-y-2 text-sm">
        <?php if ($c_email): ?><p>✉️ <a href="mailto:<?= h($c_email) ?>" class="hover:underline" style="color:var(--c-primary-lt)"><?= h($c_email) ?></a></p><?php endif; ?>
        <?php if ($c_phone): ?><p>📞 <a href="tel:<?= h($c_phone) ?>" class="hover:underline" style="color:var(--c-primary-lt)"><?= h($c_phone) ?></a></p><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php render_ads('sidebar-top'); ?>
  </aside>
</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
