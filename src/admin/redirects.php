<?php
admin_check();
$redirects = get_redirects();
admin_html_start('URL Redirect व्यवस्थापन');
admin_sidebar('redirects');
?>
<div class="admin-content">
<?php admin_topbar('URL Redirect व्यवस्थापन'); ?>
<div class="p-6">

<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="admin-page-title"><?= icon('arrow-right','w-6 h-6') ?> URL Redirect व्यवस्थापन</h1>
    <p class="text-sm mt-1" style="color:var(--c-muted)">
      पुरानो URL लाई नयाँ URL मा redirect गर्नुहोस् (301/302)। Article slug परिवर्तन गर्दा automatic थपिन्छ।
    </p>
  </div>
  <a href="/admin/redirects?action=new" class="btn-primary text-sm">
    <?= icon('plus','w-4 h-4') ?> नयाँ Redirect
  </a>
</div>

<?php if (($msg = flash_get('success'))): ?>
  <div class="alert alert-success mb-4"><?= h($msg) ?></div>
<?php endif; ?>

<!-- Add Form -->
<?php if (isset($_GET['action']) && $_GET['action'] === 'new'): ?>
<div class="admin-card mb-6">
  <div class="admin-card-header"><?= icon('plus','w-4 h-4') ?> नयाँ Redirect थप्नुहोस्</div>
  <div class="admin-card-body">
    <form method="post" action="/admin/redirects/save" class="grid gap-4 md:grid-cols-3">
      <?= csrf_field() ?>
      <div>
        <label class="form-label">पुरानो URL <span class="text-red-500">*</span></label>
        <input type="text" name="old_path" required class="form-input" placeholder="/old-article-slug">
        <p class="text-xs mt-1" style="color:var(--c-muted)">/ बाट सुरु गर्नुहोस्</p>
      </div>
      <div>
        <label class="form-label">नयाँ URL <span class="text-red-500">*</span></label>
        <input type="text" name="new_path" required class="form-input" placeholder="/article/new-slug">
      </div>
      <div>
        <label class="form-label">Status Code</label>
        <select name="status_code" class="form-input">
          <option value="301">301 — Permanent</option>
          <option value="302">302 — Temporary</option>
        </select>
      </div>
      <div class="md:col-span-3">
        <button type="submit" class="btn-primary">
          <?= icon('save','w-4 h-4') ?> सेभ गर्नुहोस्
        </button>
        <a href="/admin/redirects" class="btn-outline text-sm ml-2">रद्द</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- List -->
<div class="admin-card">
  <div class="admin-card-header">
    <?= icon('list','w-4 h-4') ?> Redirect सूची
    <span class="ml-2 text-xs px-2 py-0.5 rounded-full" style="background:var(--c-primary);color:#fff">
      <?= count($redirects) ?>
    </span>
  </div>
  <div class="admin-card-body p-0">
    <?php if (empty($redirects)): ?>
      <div class="text-center py-10" style="color:var(--c-muted)">
        <?= icon('arrow-right-left','w-8 h-8') ?>
        <p class="mt-2">कुनै redirect छैन।</p>
      </div>
    <?php else: ?>
    <div class="overflow-x-auto">
      <table class="admin-table">
        <thead><tr>
          <th>पुरानो URL</th><th>→ नयाँ URL</th><th>Code</th><th>Hits</th><th>मिति</th><th>कार्य</th>
        </tr></thead>
        <tbody>
          <?php foreach ($redirects as $r): ?>
          <tr>
            <td class="font-mono text-sm" style="color:var(--c-danger)"><?= h($r['old_path']) ?></td>
            <td class="font-mono text-sm" style="color:var(--c-success)"><?= h($r['new_path']) ?></td>
            <td>
              <span class="badge <?= $r['status_code'] == 301 ? 'badge-blue' : 'badge-yellow' ?>">
                <?= (int)$r['status_code'] ?>
              </span>
            </td>
            <td><?= np_number((int)($r['hit_count'] ?? 0)) ?></td>
            <td class="text-xs" style="color:var(--c-muted)">
              <?= $r['created_at'] ? date('Y-m-d', strtotime($r['created_at'])) : '' ?>
            </td>
            <td>
              <form method="post" action="/admin/redirects/delete" class="inline"
                    onsubmit="return confirm('Redirect मेटाउने?')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                <button type="submit" class="admin-action-btn text-red-600">
                  <?= icon('trash-2','w-3.5 h-3.5') ?>
                </button>
              </form>
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
