<?php
admin_check();
$tags = get_tags();
admin_html_start('ट्यागहरू');
admin_sidebar('tags');
?>
<div class="admin-content">
<?php admin_topbar('ट्याग व्यवस्थापन'); ?>
<div class="p-6">
<?php admin_flash(); ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2">
    <table class="data-table">
      <thead><tr><th>ट्याग नाम</th><th>Slug</th><th>लेख संख्या</th><th>कार्य</th></tr></thead>
      <tbody>
        <?php if (empty($tags)): ?>
          <tr><td colspan="4" class="text-center py-8" style="color:var(--c-muted)">कुनै ट्याग छैन।</td></tr>
        <?php endif; ?>
        <?php foreach ($tags as $t): ?>
        <?php $cnt = db_count("SELECT COUNT(*) FROM article_tags WHERE tag_id=?",[$t['id']]); ?>
        <tr>
          <td>
            <span class="inline-block text-xs font-semibold px-2 py-1 rounded"
                  style="background:var(--c-tag-bg);color:var(--c-tag-text)">#<?= h($t['name']) ?></span>
          </td>
          <td class="text-xs font-mono" style="color:var(--c-muted)"><?= h($t['slug']) ?></td>
          <td class="text-sm"><?= np_number($cnt) ?></td>
          <td>
            <form method="POST" action="/admin/tags/delete"
                  onsubmit="return confirm('यो ट्याग मेटाउने?')">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= $t['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">मेट्नुस्</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="stat-card">
    <h2 class="font-bold text-sm mb-4">नयाँ ट्याग थप्नुस्</h2>
    <form method="POST" action="/admin/tags/save">
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label">ट्याग नाम *</label>
        <input type="text" name="name" class="form-control" required placeholder="जस्तै: नेपाल राष्ट्र बैंक">
      </div>
      <div class="form-group">
        <label class="form-label">Slug *</label>
        <input type="text" name="slug" class="form-control" required placeholder="nepal-rastra-bank">
      </div>
      <button type="submit" class="btn btn-primary w-full justify-center">थप्नुस्</button>
    </form>
  </div>
</div>
</div>
</div>
</body></html>
