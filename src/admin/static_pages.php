<?php
admin_check();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id   = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $data = [
            'slug'          => trim($_POST['slug'] ?? ''),
            'title'         => trim($_POST['title'] ?? ''),
            'title_en'      => trim($_POST['title_en'] ?? ''),
            'body'          => $_POST['body']    ?? '',
            'body_en'       => $_POST['body_en'] ?? '',
            'show_in_footer'=> isset($_POST['show_in_footer']) ? 1 : 0,
            'sort_order'    => (int)($_POST['sort_order'] ?? 0),
        ];
        if (!$data['slug'] || !$data['title']) { flash_set('error','Slug र शीर्षक आवश्यक छ।'); }
        else { save_static_page($data, $id); flash_set('success','पृष्ठ सेभ गरियो।'); }
        redirect('admin/pages');
    }
    if ($action === 'delete') {
        delete_static_page((int)($_POST['id'] ?? 0));
        flash_set('success','पृष्ठ मेटाइयो।');
        redirect('admin/pages');
    }
}

$pages   = get_static_pages();
$edit_id = (int)($_GET['edit'] ?? 0);
$edit    = $edit_id ? db_fetch("SELECT * FROM static_pages WHERE id=?", [$edit_id]) : null;
$new_mode= isset($_GET['new']) || $edit;

admin_html_start('स्थिर पृष्ठहरू');
admin_sidebar('pages');
?>
<div class="admin-content">
<?php admin_topbar('स्थिर पृष्ठहरू'); ?>
<div class="p-6">
<?php admin_flash(); ?>

<?php if ($new_mode): ?>
<div class="stat-card mb-5">
  <h2 class="font-bold text-sm mb-4 flex items-center gap-2">
    <i data-lucide="<?= $edit?'pencil':'file-plus' ?>" class="w-4 h-4"></i>
    <?= $edit ? 'पृष्ठ सम्पादन' : 'नयाँ पृष्ठ' ?>
  </h2>
  <form method="POST" action="/admin/pages" class="space-y-4">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="form-label">Slug <span class="text-red-500">*</span></label>
        <input type="text" name="slug" class="form-control" required value="<?= h($edit['slug']??'') ?>" placeholder="about">
      </div>
      <div>
        <label class="form-label">शीर्षक (NP) <span class="text-red-500">*</span></label>
        <input type="text" name="title" class="form-control" required value="<?= h($edit['title']??'') ?>">
      </div>
      <div>
        <label class="form-label">Title (EN)</label>
        <input type="text" name="title_en" class="form-control" value="<?= h($edit['title_en']??'') ?>">
      </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="form-label">सामग्री (NP)</label>
        <textarea name="body" class="form-control" rows="10"><?= h($edit['body']??'') ?></textarea>
        <p class="form-help">HTML सामग्री थप्न सकिन्छ।</p>
      </div>
      <div>
        <label class="form-label">Content (EN)</label>
        <textarea name="body_en" class="form-control" rows="10"><?= h($edit['body_en']??'') ?></textarea>
      </div>
    </div>
    <div class="flex items-center gap-6">
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="show_in_footer" value="1" <?= ($edit['show_in_footer']??1)?'checked':'' ?> class="w-4 h-4">
        <span class="text-sm">Footer मा देखाउनुस्</span>
      </label>
      <div>
        <label class="form-label inline">क्रम:</label>
        <input type="number" name="sort_order" class="form-control inline" style="width:70px" value="<?= (int)($edit['sort_order']??0) ?>">
      </div>
    </div>
    <div class="flex gap-2">
      <button type="submit" class="btn btn-primary gap-1"><?= icon('save','w-3.5 h-3.5') ?> सेभ</button>
      <a href="/admin/pages" class="btn btn-secondary">रद्द</a>
    </div>
  </form>
</div>
<?php else: ?>
<div class="flex items-center justify-between mb-4">
  <h2 class="font-bold flex items-center gap-2"><i data-lucide="file-text" class="w-4 h-4"></i> सबै पृष्ठहरू</h2>
  <a href="/admin/pages?new" class="btn btn-primary gap-1"><?= icon('plus','w-3.5 h-3.5') ?> नयाँ पृष्ठ</a>
</div>
<?php endif; ?>

<div class="table-wrap">
  <table class="admin-table">
    <thead><tr><th>शीर्षक</th><th>Slug</th><th>Footer</th><th>क्रम</th><th>कार्यहरू</th></tr></thead>
    <tbody>
    <?php foreach ($pages as $p): ?>
    <tr>
      <td class="font-semibold"><?= h($p['title']) ?> <?php if ($p['title_en']): ?><span class="text-xs" style="color:var(--c-muted)">/ <?= h($p['title_en']) ?></span><?php endif; ?></td>
      <td class="text-xs font-mono"><?= h($p['slug']) ?></td>
      <td><?= $p['show_in_footer']?'<span class="badge badge-green">हो</span>':'<span class="badge badge-gray">होइन</span>' ?></td>
      <td><?= (int)$p['sort_order'] ?></td>
      <td>
        <div class="flex gap-1">
          <a href="/admin/pages?edit=<?= $p['id'] ?>" class="btn btn-secondary btn-sm"><?= icon('pencil','w-3 h-3') ?></a>
          <a href="/page/<?= h($p['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm"><?= icon('eye','w-3 h-3') ?></a>
          <form method="POST" action="/admin/pages" onsubmit="return confirm('मेटाउने?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
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
</body></html>
