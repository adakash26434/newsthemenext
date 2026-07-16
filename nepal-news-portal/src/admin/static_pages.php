<?php
admin_check();
$pages = get_static_pages();
$edit  = null;
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit') {
    $edit = db_fetch("SELECT * FROM static_pages WHERE id=?", [(int)$_GET['id']]);
}

admin_html_start('पृष्ठहरू व्यवस्थापन');
admin_sidebar('pages');
?>
<div class="admin-content">
<?php admin_topbar('स्थिर पृष्ठहरू'); ?>
<div class="p-6">
<?php admin_flash(); ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  <!-- Form -->
  <div class="stat-card">
    <h2 class="font-bold text-sm mb-4" style="border-bottom:1px solid var(--c-admin-border);padding-bottom:8px">
      <?= $edit ? '✏️ पृष्ठ सम्पादन' : '+ नयाँ पृष्ठ' ?>
    </h2>
    <form method="POST" action="/admin/pages/save" x-data="{
      title: <?= json_encode($edit['title'] ?? '') ?>,
      slug: <?= json_encode($edit['slug'] ?? '') ?>,
      auto: <?= $edit ? 'false' : 'true' ?>,
      mkSlug(t){ return t.toLowerCase().replace(/[^\w\s-]/g,'').replace(/[\s_]+/g,'-').replace(/^-+|-+$/g,'')||'page'; }
    }">
      <?= csrf_field() ?>
      <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
      <div class="form-group">
        <label class="form-label">शीर्षक (नेपाली) *</label>
        <input type="text" name="title" class="form-control" required
               x-model="title" @input="if(auto) slug=mkSlug(title)"
               value="<?= h($edit['title'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Title (English)</label>
        <input type="text" name="title_en" class="form-control" value="<?= h($edit['title_en'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">URL Slug *</label>
        <div class="flex gap-2">
          <input type="text" name="slug" class="form-control flex-1" x-model="slug" @focus="auto=false">
          <button type="button" class="btn btn-secondary btn-sm" @click="slug=mkSlug(title);auto=false">Auto</button>
        </div>
        <p class="form-hint">URL: /page/<span x-text="slug||'slug'"></span></p>
      </div>
      <div class="form-group">
        <label class="form-label">विषयवस्तु (नेपाली)</label>
        <textarea name="body" class="form-control" rows="8"><?= h($edit['body'] ?? '') ?></textarea>
        <p class="form-hint">HTML tags प्रयोग गर्न मिल्छ।</p>
      </div>
      <div class="form-group">
        <label class="form-label">Content (English)</label>
        <textarea name="body_en" class="form-control" rows="6"><?= h($edit['body_en'] ?? '') ?></textarea>
      </div>
      <div class="form-group flex gap-4 flex-wrap">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" name="show_in_footer" <?= ($edit['show_in_footer'] ?? 1) ? 'checked' : '' ?> class="rounded">
          Footer मा देखाउनुस्
        </label>
      </div>
      <div class="form-group">
        <label class="form-label">क्रम</label>
        <input type="number" name="sort_order" class="form-control" value="<?= h($edit['sort_order'] ?? 0) ?>">
      </div>
      <div class="flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">💾 सेभ</button>
        <?php if ($edit): ?>
          <a href="/admin/pages" class="btn btn-secondary">रद्द</a>
          <a href="/page/<?= h($edit['slug']) ?>" target="_blank" class="btn btn-secondary">👁️ Preview</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- List -->
  <div>
    <h2 class="font-bold text-sm mb-4">सबै पृष्ठहरू</h2>
    <?php if (empty($pages)): ?>
      <p style="color:var(--c-muted)" class="text-sm">कुनै पृष्ठ छैन।</p>
    <?php else: ?>
    <div class="space-y-2">
      <?php foreach ($pages as $pg): ?>
      <div class="stat-card py-3 px-4 flex items-center justify-between gap-2">
        <div>
          <div class="font-semibold text-sm"><?= h($pg['title']) ?></div>
          <?php if ($pg['title_en']): ?><div class="text-xs" style="color:var(--c-muted)"><?= h($pg['title_en']) ?></div><?php endif; ?>
          <div class="text-xs mt-0.5" style="color:var(--c-muted)">/page/<?= h($pg['slug']) ?></div>
        </div>
        <div class="flex gap-1 flex-shrink-0">
          <a href="/admin/pages?action=edit&id=<?= $pg['id'] ?>" class="btn btn-secondary btn-sm">✏️</a>
          <a href="/page/<?= h($pg['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm">👁️</a>
          <form method="POST" action="/admin/pages/delete" onsubmit="return confirm('मेटाउने?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $pg['id'] ?>">
            <button class="btn btn-danger btn-sm">🗑️</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
</div>
</div>
</body></html>
