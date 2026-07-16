<?php
admin_check();
$authors = get_authors();
admin_html_start('लेखकहरू');
admin_sidebar('authors');
?>
<div class="admin-content">
<?php admin_topbar('लेखक व्यवस्थापन'); ?>
<div class="p-6">
<?php admin_flash(); ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2">
    <table class="data-table">
      <thead><tr><th>अवतार</th><th>नाम</th><th>नाम (नेपाली)</th><th>Slug</th><th>Bio</th><th>कार्य</th></tr></thead>
      <tbody>
        <?php if (empty($authors)): ?>
          <tr><td colspan="6" class="text-center py-8" style="color:var(--c-muted)">कुनै लेखक छैन।</td></tr>
        <?php endif; ?>
        <?php foreach ($authors as $a): ?>
        <tr>
          <td>
            <?php if ($a['avatar_url']): ?>
              <img src="<?= h($a['avatar_url']) ?>" alt="" class="w-10 h-10 rounded-full object-cover">
            <?php else: ?>
              <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm"
                   style="background:var(--c-tag-bg);color:var(--c-primary)">
                <?= mb_substr($a['name'],0,1) ?>
              </div>
            <?php endif; ?>
          </td>
          <td class="font-semibold text-sm"><?= h($a['name']) ?></td>
          <td class="text-sm"><?= h($a['name_np'] ?? '') ?></td>
          <td class="text-xs font-mono" style="color:var(--c-muted)"><?= h($a['slug']) ?></td>
          <td class="text-xs" style="max-width:200px;white-space:normal;color:var(--c-muted)"><?= h(mb_substr($a['bio']??'',0,80)) ?></td>
          <td>
            <div class="actions">
              <a href="/admin/authors?action=edit&id=<?= $a['id'] ?>" class="btn btn-secondary btn-sm">सम्पादन</a>
              <form method="POST" action="/admin/authors/delete" onsubmit="return confirm('यो लेखक मेटाउने?')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">मेट्नुस्</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php
  $edit_id = (int)($_GET['id'] ?? 0);
  $edit_a  = $edit_id ? db_fetch("SELECT * FROM authors WHERE id=?", [$edit_id]) : null;
  ?>
  <div class="stat-card">
    <h2 class="font-bold text-sm mb-4"><?= $edit_a ? 'लेखक सम्पादन' : 'नयाँ लेखक' ?></h2>
    <form method="POST" action="/admin/authors/save">
      <?= csrf_field() ?>
      <?php if ($edit_a): ?><input type="hidden" name="id" value="<?= $edit_id ?>"><?php endif; ?>
      <div class="form-group">
        <label class="form-label">पूरा नाम (English) *</label>
        <input type="text" name="name" class="form-control" required
               value="<?= h($edit_a['name']??'') ?>" placeholder="Full Name">
      </div>
      <div class="form-group">
        <label class="form-label">पूरा नाम (नेपाली)</label>
        <input type="text" name="name_np" class="form-control"
               value="<?= h($edit_a['name_np']??'') ?>" placeholder="पूरा नाम नेपालीमा">
      </div>
      <div class="form-group">
        <label class="form-label">Slug (URL) *</label>
        <input type="text" name="slug" class="form-control" required
               value="<?= h($edit_a['slug']??'') ?>" placeholder="author-name">
      </div>
      <div class="form-group">
        <label class="form-label">परिचय (Bio)</label>
        <textarea name="bio" class="form-control" rows="3"
                  placeholder="लेखकको संक्षिप्त परिचय..."><?= h($edit_a['bio']??'') ?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Avatar URL</label>
        <input type="url" name="avatar_url" class="form-control"
               value="<?= h($edit_a['avatar_url']??'') ?>" placeholder="https://...">
      </div>
      <div class="flex gap-2">
        <button type="submit" class="btn btn-primary flex-1 justify-center"><?= $edit_a?'अपडेट':'थप्नुस्' ?></button>
        <?php if ($edit_a): ?><a href="/admin/authors" class="btn btn-secondary">रद्द</a><?php endif; ?>
      </div>
    </form>
  </div>
</div>
</div>
</div>
</body></html>
