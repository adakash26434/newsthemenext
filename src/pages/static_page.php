<?php
$slug    = $_slug ?? '';
$pg      = get_static_page($slug);
if (!$pg) { http_response_code(404); require SRC_DIR . '/pages/404.php'; exit; }

$lang       = current_lang();
$title_main = $lang==='en' ? ($pg['title_en']?:$pg['title']) : $pg['title'];
$body_main  = $lang==='en' ? ($pg['body_en']?:$pg['body']) : $pg['body'];
$page_title = h($title_main) . ' — ' . site_name();

require SRC_DIR . '/layout/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <article class="lg:col-span-2">
    <div class="p-6 rounded-xl" style="background:var(--c-surface);border:1px solid var(--c-border)">
      <h1 class="text-2xl font-extrabold mb-4 pb-3" style="color:var(--c-text);border-bottom:2px solid var(--c-primary)">
        <?= h($title_main) ?>
      </h1>
      <div class="static-page-body"><?= $body_main ?></div>
    </div>
  </article>

  <aside class="space-y-5">
    <?php render_ads('sidebar-top'); ?>
    <div class="sidebar-card">
      <div class="section-heading mb-3"><span class="flex items-center gap-2"><?= icon('file-text','w-4 h-4') ?> पृष्ठहरू</span></div>
      <?php foreach (get_static_pages(true) as $sp): ?>
      <a href="/page/<?= h($sp['slug']) ?>"
         class="flex items-center gap-1 py-2 text-sm font-semibold hover:underline"
         style="border-bottom:1px solid var(--c-border2);<?= $sp['slug']===$slug?'color:var(--c-primary-lt)':'' ?>">
        <?= icon('chevron-right','w-3 h-3') ?>
        <?= h($lang==='en'?($sp['title_en']?:$sp['title']):$sp['title']) ?>
      </a>
      <?php endforeach; ?>
    </div>
  </aside>
</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
