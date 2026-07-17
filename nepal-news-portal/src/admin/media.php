<?php
admin_check();

$upload_dir = BASE_DIR . '/uploads';
$upload_url = '/uploads';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

$error = '';
$success = '';

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    csrf_check();
    $file = basename($_POST['file'] ?? '');
    if ($file && file_exists($upload_dir . '/' . $file)) {
        unlink($upload_dir . '/' . $file);
        flash_set('success', 'फाइल मेटाइयो।');
    }
    redirect('admin/media');
}

// Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['media']['name'])) {
    csrf_check();
    $allowed = ['image/jpeg','image/png','image/gif','image/webp','image/svg+xml'];
    $max_size = 5 * 1024 * 1024; // 5 MB
    $file = $_FILES['media'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'अपलोड गर्दा समस्या भयो। (error '.$file['error'].')';
    } elseif (!in_array($file['type'], $allowed)) {
        $error = 'अनुमत फाइल प्रकार: JPG, PNG, GIF, WebP, SVG मात्र।';
    } elseif ($file['size'] > $max_size) {
        $error = 'फाइल साइज ५ MB भन्दा बढी छ।';
    } else {
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $name = slug_from_title(pathinfo($file['name'], PATHINFO_FILENAME)) . '-' . time() . '.' . $ext;
        $dest = $upload_dir . '/' . $name;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            flash_set('success', 'फाइल सफलतापूर्वक अपलोड भयो। URL: ' . $upload_url . '/' . $name);
        } else {
            $error = 'फाइल सार्न सकिएन।';
        }
    }
    if ($error) flash_set('error', $error);
    redirect('admin/media');
}

// List files
$files = [];
if (is_dir($upload_dir)) {
    foreach (glob($upload_dir . '/*') as $f) {
        if (is_file($f)) {
            $files[] = [
                'name'  => basename($f),
                'url'   => $upload_url . '/' . basename($f),
                'size'  => filesize($f),
                'mtime' => filemtime($f),
                'type'  => mime_content_type($f),
            ];
        }
    }
    usort($files, fn($a,$b) => $b['mtime'] - $a['mtime']);
}

admin_html_start('मिडिया लाइब्रेरी');
admin_sidebar('media');
?>
<div class="admin-content">
<?php admin_topbar('मिडिया लाइब्रेरी'); ?>
<div class="p-6">
<?php admin_flash(); ?>

<!-- Upload form -->
<div class="stat-card mb-6">
  <h3 class="font-bold mb-3 flex items-center gap-2">
    <i data-lucide="upload-cloud" class="w-4 h-4"></i> नयाँ फाइल अपलोड गर्नुस्
  </h3>
  <form method="POST" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
    <?= csrf_field() ?>
    <div class="flex-1 min-w-52">
      <label class="admin-label">फाइल छान्नुस् (JPG, PNG, WebP, GIF, SVG — अधिकतम ५ MB)</label>
      <input type="file" name="media" accept="image/*" required class="admin-input text-sm w-full">
    </div>
    <button type="submit" class="btn btn-primary flex items-center gap-2">
      <i data-lucide="upload" class="w-4 h-4"></i> अपलोड
    </button>
  </form>
</div>

<!-- File grid -->
<?php if (empty($files)): ?>
<div class="stat-card text-center py-10" style="color:var(--c-muted)">
  <i data-lucide="image-off" class="w-10 h-10 mx-auto mb-3 opacity-40"></i>
  <p>कुनै फाइल छैन।</p>
</div>
<?php else: ?>
<p class="text-sm mb-4" style="color:var(--c-muted)">
  <i data-lucide="images" class="w-4 h-4 inline"></i> कुल <?= count($files) ?> फाइल
</p>
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
  <?php foreach ($files as $f): ?>
  <?php $is_img = str_starts_with($f['type'],'image/'); ?>
  <div class="media-card group">
    <?php if ($is_img): ?>
    <div class="media-thumb">
      <img src="<?= h($f['url']) ?>" alt="<?= h($f['name']) ?>" loading="lazy">
    </div>
    <?php else: ?>
    <div class="media-thumb flex items-center justify-center" style="background:var(--c-surface2)">
      <i data-lucide="file" class="w-8 h-8 opacity-40"></i>
    </div>
    <?php endif; ?>
    <div class="p-2">
      <p class="text-xs font-medium truncate mb-1" title="<?= h($f['name']) ?>"><?= h($f['name']) ?></p>
      <p class="text-xs mb-2" style="color:var(--c-muted)"><?= round($f['size']/1024, 1) ?> KB</p>
      <div class="flex gap-1.5">
        <button type="button"
                onclick="navigator.clipboard.writeText('<?= h($f['url']) ?>').then(()=>alert('URL कपि भयो!'))"
                class="btn btn-sm flex-1 text-xs" style="font-size:10px">
          <i data-lucide="copy" class="w-3 h-3 inline"></i> Copy URL
        </button>
        <form method="POST" onsubmit="return confirm('मेटाउने?')">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="file" value="<?= h($f['name']) ?>">
          <button class="btn btn-sm text-xs" style="background:#ef4444;color:#fff;border:none;font-size:10px">
            <i data-lucide="trash-2" class="w-3 h-3 inline"></i>
          </button>
        </form>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</div>
<?php admin_html_end(); ?>
