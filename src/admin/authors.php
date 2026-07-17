<?php
admin_check();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $data = [
            'name'         => trim($_POST['name']         ?? ''),
            'name_np'      => trim($_POST['name_np']      ?? ''),
            'slug'         => trim($_POST['slug']         ?? '') ?: slugify(trim($_POST['name'] ?? '')),
            'bio'          => trim($_POST['bio']          ?? ''),
            'avatar_url'   => trim($_POST['avatar_url']   ?? ''),
            'twitter_url'  => trim($_POST['twitter_url']  ?? ''),
            'facebook_url' => trim($_POST['facebook_url'] ?? ''),
            'linkedin_url' => trim($_POST['linkedin_url'] ?? ''),
        ];
        $upload = handle_upload('avatar_file', 'authors');
        if ($upload) $data['avatar_url'] = $upload;
        if (!$data['name']) { flash_set('error','नाम आवश्यक छ।'); }
        else { save_author($data, $id); flash_set('success', $id ? 'लेखक अपडेट गरियो।' : 'लेखक थपियो।'); }
        redirect('admin/authors');
    }
    if ($action === 'delete') {
        delete_author((int)($_POST['id'] ?? 0));
        flash_set('success', 'लेखक मेटाइयो।');
        redirect('admin/authors');
    }
}

$authors = get_authors();
$edit_id = (int)($_GET['edit'] ?? 0);
$edit    = $edit_id ? db_fetch("SELECT * FROM authors WHERE id=?", [$edit_id]) : null;

admin_html_start('लेखक व्यवस्थापन');
admin_sidebar('authors');
?>
<div class="admin-content">
<?php admin_topbar('लेखक व्यवस्थापन'); ?>
<div class="p-6">
<?php admin_flash(); ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <!-- Form -->
  <div class="stat-card">
    <h2 class="font-bold text-sm mb-4 flex items-center gap-2">
      <i data-lucide="<?= $edit?'pencil':'user-plus' ?>" class="w-4 h-4"></i>
      <?= $edit ? 'लेखक सम्पादन' : 'नयाँ लेखक' ?>
    </h2>
    <form method="POST" action="/admin/authors" enctype="multipart/form-data" class="space-y-3">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
      <?php if ($edit && $edit['avatar_url']): ?>
        <img src="<?= h($edit['avatar_url']) ?>" alt="" class="w-16 h-16 rounded-full object-cover mb-2">
      <?php endif; ?>
      <div>
        <label class="form-label">नाम (NP) <span class="text-red-500">*</span></label>
        <input type="text" name="name" class="form-control" required value="<?= h($edit['name']??'') ?>">
      </div>
      <div>
        <label class="form-label">नाम (EN)</label>
        <input type="text" name="name_np" class="form-control" value="<?= h($edit['name_np']??'') ?>">
      </div>
      <div>
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control" value="<?= h($edit['slug']??'') ?>">
      </div>
      <div>
        <label class="form-label">Avatar Upload</label>
        <input type="file" name="avatar_file" class="form-control" accept="image/*">
      </div>
      <div>
        <label class="form-label">Avatar URL</label>
        <input type="url" name="avatar_url" class="form-control" value="<?= h($edit['avatar_url']??'') ?>" placeholder="https://...">
      </div>
      <div>
        <label class="form-label">परिचय (Bio)</label>
        <textarea name="bio" class="form-control" rows="3"><?= h($edit['bio']??'') ?></textarea>
      </div>
      <div>
        <label class="form-label">Twitter/X URL</label>
        <input type="url" name="twitter_url" class="form-control" value="<?= h($edit['twitter_url']??'') ?>" placeholder="https://twitter.com/...">
      </div>
      <div>
        <label class="form-label">Facebook URL</label>
        <input type="url" name="facebook_url" class="form-control" value="<?= h($edit['facebook_url']??'') ?>" placeholder="https://facebook.com/...">
      </div>
      <div>
        <label class="form-label">LinkedIn URL</label>
        <input type="url" name="linkedin_url" class="form-control" value="<?= h($edit['linkedin_url']??'') ?>" placeholder="https://linkedin.com/in/...">
      </div>
      <div class="flex gap-2">
        <button type="submit" class="btn btn-primary gap-1"><?= icon('save','w-3.5 h-3.5') ?> सेभ</button>
        <?php if ($edit): ?><a href="/admin/authors" class="btn btn-secondary">रद्द</a><?php endif; ?>
      </div>
    </form>
  </div>

  <!-- List -->
  <div class="lg:col-span-2">
    <h2 class="font-bold text-sm mb-3 flex items-center gap-2"><i data-lucide="users" class="w-4 h-4"></i> सबै लेखकहरू</h2>
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>लेखक</th><th>Slug</th><th>कार्यहरू</th></tr></thead>
        <tbody>
        <?php foreach ($authors as $au): ?>
        <tr>
          <td>
            <div class="flex items-center gap-2">
              <?php if ($au['avatar_url']): ?>
                <img src="<?= h($au['avatar_url']) ?>" alt="" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
              <?php else: ?>
                <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center" style="background:var(--c-primary)">
                  <i data-lucide="user" class="w-4 h-4 text-white" style="color:#fff"></i>
                </div>
              <?php endif; ?>
              <div>
                <div class="font-semibold"><?= h($au['name']) ?></div>
                <?php if ($au['name_np']): ?><div class="text-xs" style="color:var(--c-muted)"><?= h($au['name_np']) ?></div><?php endif; ?>
              </div>
            </div>
          </td>
          <td class="text-xs font-mono"><?= h($au['slug']) ?></td>
          <td>
            <div class="flex gap-1">
              <a href="/admin/authors?edit=<?= $au['id'] ?>" class="btn btn-secondary btn-sm"><?= icon('pencil','w-3 h-3') ?></a>
              <a href="/author/<?= h($au['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm"><?= icon('eye','w-3 h-3') ?></a>
              <form method="POST" action="/admin/authors" onsubmit="return confirm('मेटाउने?')">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $au['id'] ?>">
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
