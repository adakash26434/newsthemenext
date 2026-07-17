<?php
$page_title = 'सुरक्षित गरिएका समाचार — ' . site_name();
require SRC_DIR . '/layout/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-5 p-4 rounded-xl"
         style="background:var(--c-surface);border:1px solid var(--c-border)">
      <span class="flex items-center justify-center w-10 h-10 rounded-full" style="background:var(--c-primary)">
        <?= icon('bookmark','w-5 h-5 text-white') ?>
      </span>
      <div>
        <h1 class="text-lg font-extrabold" style="color:var(--c-text)">सुरक्षित गरिएका समाचार</h1>
        <p class="text-xs" style="color:var(--c-muted)">तपाईंले Bookmark गर्नुभएका समाचारहरू</p>
      </div>
      <button onclick="if(confirm('सबै Bookmark मेटाउने?')){localStorage.removeItem('nnp_bookmarks');renderSaved();}"
              class="ml-auto btn text-xs" style="background:#ef4444;color:#fff;border:none">
        <?= icon('trash-2','w-3.5 h-3.5') ?> सबै मेटाउनुस्
      </button>
    </div>

    <!-- Article list (rendered by JS from localStorage) -->
    <div id="saved-list" class="space-y-3">
      <!-- populated by renderSaved() -->
    </div>
    <div id="saved-empty" class="stat-card text-center py-12 hidden" style="color:var(--c-muted)">
      <?= icon('bookmark-x','w-12 h-12 mx-auto mb-3 opacity-30') ?>
      <p class="text-base font-semibold mb-1">कुनै सुरक्षित समाचार छैन।</p>
      <p class="text-sm">समाचार पढ्दा Bookmark बटन थिच्नुस्।</p>
      <a href="/" class="btn btn-primary mt-4 inline-flex gap-2">
        <?= icon('home','w-4 h-4') ?> गृहपृष्ठमा जानुस्
      </a>
    </div>

  </div>

  <!-- Sidebar -->
  <aside class="space-y-5">
    <?php render_ads('sidebar-top'); ?>
    <div class="sidebar-card">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('flame','w-4 h-4') ?> सर्वाधिक पढिएका</span>
      </div>
      <?php foreach (get_popular_articles(5) as $i => $pop): ?>
      <div class="popular-item">
        <span class="popular-num"><?= $i+1 ?></span>
        <div><a href="/article/<?= h($pop['slug']) ?>" class="ptitle hover:underline"><?= h($pop['title']) ?></a></div>
      </div>
      <?php endforeach; ?>
    </div>
  </aside>
</div>

<script>
function renderSaved() {
  var bookmarks = JSON.parse(localStorage.getItem('nnp_bookmarks') || '[]');
  // Sort newest first
  bookmarks.sort(function(a,b){ return (b.saved_at||0)-(a.saved_at||0); });
  var list = document.getElementById('saved-list');
  var empty = document.getElementById('saved-empty');
  list.innerHTML = '';
  if (!bookmarks.length) {
    list.classList.add('hidden');
    empty.classList.remove('hidden');
    return;
  }
  list.classList.remove('hidden');
  empty.classList.add('hidden');
  bookmarks.forEach(function(b, idx) {
    var div = document.createElement('div');
    div.className = 'flex items-center gap-4 p-3 rounded-lg';
    div.style.cssText = 'background:var(--c-surface);border:1px solid var(--c-border)';
    div.innerHTML = '<div class="flex-shrink-0 text-sm font-black opacity-20 w-5 text-right">' + (idx+1) + '</div>' +
      '<div class="flex-1 min-w-0">' +
        '<a href="/article/' + encodeURIComponent(b.slug) + '" class="font-bold leading-snug hover:underline text-sm line-clamp-2" style="color:var(--c-text)">' +
          (b.title || b.slug) +
        '</a>' +
      '</div>' +
      '<button onclick="removeBookmark(\'' + b.id + '\')" title="हटाउनुस्" class="flex-shrink-0 btn btn-sm" style="background:none;border:1px solid var(--c-border);padding:.25rem .5rem;color:var(--c-muted)">' +
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>' +
      '</button>';
    list.appendChild(div);
  });
}
function removeBookmark(articleId) {
  var saved = JSON.parse(localStorage.getItem('nnp_bookmarks') || '[]');
  saved = saved.filter(function(b){ return String(b.id) !== String(articleId); });
  localStorage.setItem('nnp_bookmarks', JSON.stringify(saved));
  renderSaved();
}
document.addEventListener('DOMContentLoaded', renderSaved);
</script>

<?php require SRC_DIR . '/layout/footer.php'; ?>
