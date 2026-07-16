<?php
admin_check();
$ads  = get_all_ads();
$edit = isset($_GET['id']) ? db_fetch("SELECT * FROM advertisements WHERE id=?",[(int)$_GET['id']]) : null;

$positions = [
    'header-banner'        => 'Header Banner (970×100)',
    'header-banner-inline' => 'Header Banner Inline (970×90)',
    'sidebar-top'          => 'Sidebar Top (300×250)',
    'sidebar-bottom'       => 'Sidebar Bottom (300×250)',
    'article-middle'       => 'Article Middle (728×90)',
    'article-bottom'       => 'Article Bottom (728×90)',
    'in-feed'              => 'In-Feed (हर ५ लेख पछि)',
    'popup'                => 'Popup/Modal',
];

admin_html_start('विज्ञापन व्यवस्थापन');
admin_sidebar('advertisements');
?>
<div class="admin-content">
<?php admin_topbar('विज्ञापन व्यवस्थापन'); ?>
<div class="p-6">
<?php admin_flash(); ?>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
  <!-- Form (2 cols) -->
  <div class="lg:col-span-2">
    <div class="stat-card">
      <h2 class="font-bold text-sm mb-4" style="border-bottom:1px solid var(--c-admin-border);padding-bottom:8px">
        <?= $edit ? '✏️ विज्ञापन सम्पादन' : '+ नयाँ विज्ञापन' ?>
      </h2>
      <form method="POST" action="/admin/advertisements/save" enctype="multipart/form-data"
            x-data="{adType: <?= json_encode($edit['type'] ?? 'image') ?>}">
        <?= csrf_field() ?>
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
        <div class="form-group">
          <label class="form-label">शीर्षक *</label>
          <input type="text" name="title" class="form-control" required value="<?= h($edit['title']??'') ?>" placeholder="विज्ञापनको नाम">
        </div>
        <div class="form-group">
          <label class="form-label">स्थान (Position) *</label>
          <select name="position" class="form-control">
            <?php foreach ($positions as $v => $l): ?>
              <option value="<?= $v ?>" <?= ($edit['position']??'')===$v?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">प्रकार</label>
          <select name="type" class="form-control" x-model="adType">
            <option value="image" <?= ($edit['type']??'')==='image'?'selected':'' ?>>🖼️ Image</option>
            <option value="code"  <?= ($edit['type']??'')==='code'?'selected':'' ?>>💻 HTML/Script Code (AdSense)</option>
          </select>
        </div>
        <!-- Image type -->
        <div x-show="adType==='image'">
          <div class="form-group">
            <label class="form-label">Image Upload</label>
            <?php if (!empty($edit['image_url'])): ?>
              <div class="mb-2"><img src="<?= h($edit['image_url']) ?>" alt="" style="max-height:80px;max-width:200px;object-fit:contain;border:1px solid var(--c-border);border-radius:4px"></div>
            <?php endif; ?>
            <input type="file" name="image_file" class="form-control mb-2" accept="image/*">
            <p class="form-hint">अथवा URL दिनुस्:</p>
            <input type="url" name="image_url" class="form-control" value="<?= h($edit['image_url']??'') ?>" placeholder="https://example.com/banner.jpg">
          </div>
          <div class="form-group">
            <label class="form-label">Link URL</label>
            <input type="url" name="link_url" class="form-control" value="<?= h($edit['link_url']??'') ?>" placeholder="https://advertiser.com">
          </div>
        </div>
        <!-- Code type -->
        <div x-show="adType==='code'" x-cloak>
          <div class="form-group">
            <label class="form-label">HTML/Script Code</label>
            <textarea name="code" class="form-control" rows="6" placeholder="&lt;script&gt;...AdSense code...&lt;/script&gt;"><?= h($edit['code']??'') ?></textarea>
            <p class="form-hint">Google AdSense वा अन्य ad script code यहाँ राख्नुस्।</p>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div class="form-group">
            <label class="form-label">क्रम</label>
            <input type="number" name="sort_order" class="form-control" value="<?= h($edit['sort_order']??0) ?>" min="0">
          </div>
          <div class="form-group">
            <label class="form-label">स्थिति</label>
            <label class="flex items-center gap-2 mt-2 cursor-pointer font-medium">
              <input type="checkbox" name="active" <?= ($edit['active']??1)?'checked':'' ?> class="rounded">
              सक्रिय (Active)
            </label>
          </div>
        </div>
        <div class="flex gap-2 mt-4">
          <button type="submit" class="btn btn-primary">💾 सेभ</button>
          <?php if ($edit): ?><a href="/admin/advertisements" class="btn btn-secondary">रद्द</a><?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- List (3 cols) -->
  <div class="lg:col-span-3">
    <h2 class="font-bold text-sm mb-4">सबै विज्ञापनहरू</h2>
    <?php if (empty($ads)): ?>
      <p style="color:var(--c-muted)" class="text-sm stat-card p-4 text-center">कुनै विज्ञापन छैन।</p>
    <?php else: ?>
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr>
          <th>शीर्षक</th>
          <th>स्थान</th>
          <th>प्रकार</th>
          <th>स्थिति</th>
          <th>Clicks</th>
          <th>कार्यहरू</th>
        </tr></thead>
        <tbody>
        <?php foreach ($ads as $ad): ?>
        <tr>
          <td class="font-semibold text-sm"><?= h($ad['title']) ?></td>
          <td>
            <span class="text-xs" style="color:var(--c-muted)"><?= h($positions[$ad['position']] ?? $ad['position']) ?></span>
          </td>
          <td><span class="badge badge-gray text-xs"><?= $ad['type']==='code'?'Code':'Image' ?></span></td>
          <td>
            <span class="badge <?= $ad['active']?'badge-green':'badge-gray' ?>">
              <?= $ad['active']?'सक्रिय':'निष्क्रिय' ?>
            </span>
          </td>
          <td><?= np_number((int)$ad['clicks']) ?></td>
          <td>
            <div class="flex gap-1">
              <a href="/admin/advertisements?id=<?= $ad['id'] ?>" class="btn btn-secondary btn-sm">✏️</a>
              <form method="POST" action="/admin/advertisements/delete" onsubmit="return confirm('मेटाउने?')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $ad['id'] ?>">
                <button class="btn btn-danger btn-sm">🗑️</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="mt-4 p-3 rounded text-xs" style="background:var(--c-ad-bg);border:1px solid var(--c-ad-border)">
      <strong>Position Guide:</strong><br>
      • <b>header-banner</b>: Logo छेउमा (desktop मा)<br>
      • <b>header-banner-inline</b>: Breaking news bar माथि<br>
      • <b>sidebar-top/bottom</b>: Sidebar मा<br>
      • <b>article-middle/bottom</b>: लेख भित्र<br>
      • <b>in-feed</b>: News list मा हर ५ लेख पछि
    </div>
    <?php endif; ?>
  </div>
</div>
</div>
</div>
</body></html>
