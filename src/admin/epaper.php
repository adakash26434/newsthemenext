<?php
admin_check();
$epapers = get_epapers(50);
$editing = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editing = get_epaper_by_id((int)$_GET['id']);
}
admin_html_start('ई-पेपर व्यवस्थापन');
admin_sidebar('epaper');
?>
<div class="admin-content">
<?php admin_topbar('ई-पेपर व्यवस्थापन'); ?>
<div class="p-6">

<div class="flex items-center justify-between mb-6">
  <h1 class="admin-page-title"><?= icon('newspaper','w-6 h-6') ?> ई-पेपर व्यवस्थापन</h1>
  <a href="/admin/epaper?action=new" class="btn-primary text-sm">
    <?= icon('plus','w-4 h-4') ?> नयाँ अंक थप्नुहोस्
  </a>
</div>

<?php if (($msg = flash_get('success'))): ?>
  <div class="alert alert-success mb-4"><?= h($msg) ?></div>
<?php endif; ?>
<?php if (($err = flash_get('error'))): ?>
  <div class="alert alert-error mb-4"><?= h($err) ?></div>
<?php endif; ?>

<!-- Add/Edit Form -->
<?php if (isset($_GET['action']) && in_array($_GET['action'], ['new','edit'])): ?>
<div class="admin-card mb-6">
  <div class="admin-card-header">
    <?= icon('file-text','w-4 h-4') ?>
    <?= $editing ? 'ई-पेपर सम्पादन' : 'नयाँ ई-पेपर थप्नुहोस्' ?>
  </div>
  <div class="admin-card-body">
    <form method="post" action="/admin/epaper/save" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-2">
      <?= csrf_field() ?>
      <?php if ($editing): ?>
        <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
      <?php endif; ?>

      <div>
        <label class="form-label">प्रकाशन मिति (AD) <span class="text-red-500">*</span></label>
        <input type="date" name="edition_date" required class="form-input"
               value="<?= h($editing['edition_date'] ?? date('Y-m-d')) ?>">
      </div>

      <div>
        <label class="form-label">शीर्षक / हेडलाइन</label>
        <input type="text" name="headline" class="form-input" placeholder="मुख्य समाचार शीर्षक"
               value="<?= h($editing['headline'] ?? '') ?>">
      </div>

      <div>
        <label class="form-label">PDF फाइल अपलोड</label>
        <input type="file" name="pdf_file" accept=".pdf" class="form-input">
        <?php if (!empty($editing['pdf_path'])): ?>
          <p class="text-xs mt-1" style="color:var(--c-muted)">
            हालको: <a href="<?= h($editing['pdf_path']) ?>" target="_blank" class="underline">PDF हेर्नुहोस्</a>
          </p>
        <?php endif; ?>
      </div>

      <div>
        <label class="form-label">कभर इमेज</label>
        <input type="file" name="cover_file" accept="image/*" class="form-input">
        <?php if (!empty($editing['cover_image'])): ?>
          <img src="<?= h($editing['cover_image']) ?>" alt="" class="w-16 h-20 object-cover mt-2 rounded border">
        <?php endif; ?>
      </div>

      <div class="md:col-span-2 flex items-center gap-3">
        <button type="submit" class="btn-primary">
          <?= icon('save','w-4 h-4') ?> सेभ गर्नुहोस्
        </button>
        <a href="/admin/epaper" class="btn-outline text-sm">रद्द गर्नुहोस्</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- List -->
<div class="admin-card">
  <div class="admin-card-header"><?= icon('archive','w-4 h-4') ?> ई-पेपर सूची</div>
  <div class="admin-card-body p-0">
    <?php if (empty($epapers)): ?>
      <div class="text-center py-10" style="color:var(--c-muted)">
        <?= icon('inbox','w-8 h-8') ?><p class="mt-2">कुनै ई-पेपर छैन।</p>
      </div>
    <?php else: ?>
    <div class="overflow-x-auto">
      <table class="admin-table">
        <thead><tr>
          <th>मिति</th><th>BS मिति</th><th>शीर्षक</th><th>PDF</th><th>कभर</th><th>कार्य</th>
        </tr></thead>
        <tbody>
          <?php foreach ($epapers as $ep): ?>
          <tr>
            <td class="font-mono text-sm"><?= h($ep['edition_date'] ?? '') ?></td>
            <td class="text-sm"><?= $ep['edition_date'] ? \BsDate::formatShort($ep['edition_date']) : '' ?></td>
            <td><?= h($ep['headline'] ?? '—') ?></td>
            <td>
              <?php if ($ep['pdf_path']): ?>
                <a href="<?= h($ep['pdf_path']) ?>" target="_blank"
                   class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded"
                   style="background:var(--c-primary);color:#fff">
                  <?= icon('download','w-3 h-3') ?> PDF
                </a>
              <?php else: ?>
                <span style="color:var(--c-muted)">—</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($ep['cover_image']): ?>
                <img src="<?= h($ep['cover_image']) ?>" alt="" class="w-10 h-12 object-cover rounded border">
              <?php else: ?>—<?php endif; ?>
            </td>
            <td>
              <div class="flex items-center gap-2">
                <a href="/admin/epaper?action=edit&id=<?= (int)$ep['id'] ?>"
                   class="admin-action-btn"><?= icon('pencil','w-3.5 h-3.5') ?></a>
                <form method="post" action="/admin/epaper/delete" class="inline"
                      onsubmit="return confirm('ई-पेपर मेटाउने?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$ep['id'] ?>">
                  <button type="submit" class="admin-action-btn text-red-600">
                    <?= icon('trash-2','w-3.5 h-3.5') ?>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php admin_html_end(); ?>
