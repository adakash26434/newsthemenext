<?php
admin_check();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id   = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $slug = trim($_POST['slug'] ?? '') ?: slugify(trim($_POST['name'] ?? ''));
        $data = [
            'name'        => trim($_POST['name']        ?? ''),
            'name_np'     => trim($_POST['name_np']     ?? ''),
            'slug'        => $slug,
            'color'       => trim($_POST['color']       ?? '#7F1D1D'),
            'icon'        => trim($_POST['icon']        ?? ''),
            'sort_order'  => (int)($_POST['sort_order']  ?? 0),
            'description' => trim($_POST['description']  ?? ''),
        ];
        if (!$data['name']) { flash_set('error','नाम आवश्यक छ।'); }
        else { save_category($data, $id); flash_set('success', $id ? 'श्रेणी अपडेट गरियो।' : 'श्रेणी थपियो।'); }
        redirect('admin/categories');
    }
    if ($action === 'delete') {
        delete_category((int)($_POST['id'] ?? 0));
        flash_set('success', 'श्रेणी मेटाइयो।');
        redirect('admin/categories');
    }
}

$cats    = get_categories();
$edit_id = (int)($_GET['edit'] ?? 0);
$edit    = $edit_id ? db_fetch("SELECT * FROM categories WHERE id=?", [$edit_id]) : null;

admin_html_start('श्रेणी व्यवस्थापन');
admin_sidebar('categories');
?>
<div class="admin-content">
<?php admin_topbar('श्रेणी व्यवस्थापन'); ?>
<div class="p-6">
<?php admin_flash(); ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <!-- Form -->
  <div class="stat-card">
    <h2 class="font-bold text-sm mb-4 flex items-center gap-2">
      <i data-lucide="<?= $edit?'pencil':'plus-circle' ?>" class="w-4 h-4"></i>
      <?= $edit ? 'श्रेणी सम्पादन' : 'नयाँ श्रेणी' ?>
    </h2>
    <form method="POST" action="/admin/categories" class="space-y-3">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
      <div>
        <label class="form-label">नाम (NP) <span class="text-red-500">*</span></label>
        <input type="text" name="name" class="form-control" required value="<?= h($edit['name']??'') ?>" placeholder="अर्थतन्त्र">
      </div>
      <div>
        <label class="form-label">नाम (EN)</label>
        <input type="text" name="name_np" class="form-control" value="<?= h($edit['name_np']??'') ?>" placeholder="Economics">
      </div>
      <div>
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control" value="<?= h($edit['slug']??'') ?>" placeholder="arthatantra">
      </div>
      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="form-label">रंग</label>
          <input type="color" name="color" class="form-control h-10 p-1 cursor-pointer" value="<?= h($edit['color']??'#7F1D1D') ?>">
        </div>
        <div>
          <label class="form-label">क्रम</label>
          <input type="number" name="sort_order" class="form-control" value="<?= (int)($edit['sort_order']??0) ?>" min="0">
        </div>
      </div>
      <div>
        <label class="form-label">Lucide Icon Name</label>
        <input type="text" name="icon" class="form-control" value="<?= h($edit['icon']??'') ?>" placeholder="e.g. trending-up, briefcase, users">
        <p class="form-help">lucide.dev बाट icon नाम खोज्नुस्</p>
      </div>
      <div>
        <label class="form-label">विवरण (Description)</label>
        <textarea name="description" class="form-control" rows="2" placeholder="यस श्रेणीबारे छोटो विवरण..."><?= h($edit['description']??'') ?></textarea>
      </div>
      <div class="flex gap-2">
        <button type="submit" class="btn btn-primary gap-1"><?= icon('save','w-3.5 h-3.5') ?> सेभ गर्नुस्</button>
        <?php if ($edit): ?><a href="/admin/categories" class="btn btn-secondary">रद्द</a><?php endif; ?>
      </div>
    </form>
  </div>

  <!-- List -->
  <div class="lg:col-span-2">
    <h2 class="font-bold text-sm mb-3 flex items-center gap-2">
      <i data-lucide="grid" class="w-4 h-4"></i> सबै श्रेणीहरू (<?= count($cats) ?>)
    </h2>
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>नाम</th><th>Icon</th><th>रंग</th><th>लेख</th><th>क्रम</th><th>कार्यहरू</th></tr></thead>
        <tbody>
        <?php foreach ($cats as $c): ?>
        <tr>
          <td>
            <div class="font-semibold"><?= h($c['name']) ?></div>
            <?php if ($c['name_np']): ?><div class="text-xs" style="color:var(--c-muted)"><?= h($c['name_np']) ?></div><?php endif; ?>
          </td>
          <td>
            <?php if ($c['icon']): ?><i data-lucide="<?= h($c['icon']) ?>" class="w-4 h-4" style="color:<?= h($c['color']) ?>"></i><?php else: ?>—<?php endif; ?>
          </td>
          <td><div class="w-6 h-6 rounded" style="background:<?= h($c['color']) ?>"></div></td>
          <td><?= np_number((int)($c['article_count']??0)) ?></td>
          <td><?= (int)$c['sort_order'] ?></td>
          <td>
            <div class="flex gap-1">
              <a href="/admin/categories?edit=<?= $c['id'] ?>" class="btn btn-secondary btn-sm"><?= icon('pencil','w-3 h-3') ?></a>
              <form method="POST" action="/admin/categories" onsubmit="return confirm('मेटाउने?')">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
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
