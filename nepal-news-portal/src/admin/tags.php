<?php
admin_check();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '') ?: slugify($name);
        if (!$name) { flash_set('error','नाम आवश्यक छ।'); }
        else { save_tag(['name'=>$name,'slug'=>$slug], $id); flash_set('success','ट्याग सेभ गरियो।'); }
        redirect('admin/tags');
    }
    if ($action === 'delete') {
        delete_tag((int)($_POST['id'] ?? 0));
        flash_set('success', 'ट्याग मेटाइयो।');
        redirect('admin/tags');
    }
}

$tags    = get_tags();
$edit_id = (int)($_GET['edit'] ?? 0);
$edit    = $edit_id ? db_fetch("SELECT * FROM tags WHERE id=?", [$edit_id]) : null;

admin_html_start('ट्याग व्यवस्थापन');
admin_sidebar('tags');
?>
<div class="admin-content">
<?php admin_topbar('ट्याग व्यवस्थापन'); ?>
<div class="p-6">
<?php admin_flash(); ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <!-- Form -->
  <div class="stat-card">
    <h2 class="font-bold text-sm mb-4 flex items-center gap-2">
      <i data-lucide="<?= $edit?'pencil':'hash' ?>" class="w-4 h-4"></i>
      <?= $edit ? 'ट्याग सम्पादन' : 'नयाँ ट्याग' ?>
    </h2>
    <form method="POST" action="/admin/tags" class="space-y-3">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
      <div>
        <label class="form-label">नाम <span class="text-red-500">*</span></label>
        <input type="text" name="name" class="form-control" required value="<?= h($edit['name']??'') ?>">
      </div>
      <div>
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control" value="<?= h($edit['slug']??'') ?>">
      </div>
      <div class="flex gap-2">
        <button type="submit" class="btn btn-primary gap-1"><?= icon('save','w-3.5 h-3.5') ?> सेभ</button>
        <?php if ($edit): ?><a href="/admin/tags" class="btn btn-secondary">रद्द</a><?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Tags list -->
  <div class="lg:col-span-2">
    <h2 class="font-bold text-sm mb-3 flex items-center gap-2"><i data-lucide="hash" class="w-4 h-4"></i> सबै ट्यागहरू (<?= count($tags) ?>)</h2>
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>नाम</th><th>Slug</th><th>प्रयोग</th><th>कार्यहरू</th></tr></thead>
        <tbody>
        <?php foreach ($tags as $t): ?>
        <tr>
          <td class="font-semibold"><?= h($t['name']) ?></td>
          <td class="text-xs font-mono"><?= h($t['slug']) ?></td>
          <td><?= np_number((int)$t['usage_count']) ?></td>
          <td>
            <div class="flex gap-1">
              <a href="/admin/tags?edit=<?= $t['id'] ?>" class="btn btn-secondary btn-sm"><?= icon('pencil','w-3 h-3') ?></a>
              <form method="POST" action="/admin/tags" onsubmit="return confirm('मेटाउने?')">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                <button class="btn btn-danger btn-sm"><?= icon('trash-2','w-3 h-3') ?></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>
</div>
</body></html>
