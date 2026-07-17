<?php
admin_check();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $data = [
            'title'      => trim($_POST['title'] ?? ''),
            'type'       => $_POST['type']        ?? 'image',
            'image_url'  => trim($_POST['image_url'] ?? ''),
            'code'       => $_POST['code']        ?? '',
            'link_url'   => trim($_POST['link_url'] ?? ''),
            'position'   => $_POST['position']    ?? '',
            'device'     => $_POST['device']      ?? 'all',
            'active'     => isset($_POST['active']) ? 1 : 0,
            'sort_order' => (int)($_POST['sort_order'] ?? 1),
            'start_date' => $_POST['start_date'] ?: '',
            'end_date'   => $_POST['end_date']   ?: '',
        ];
        // Handle image upload
        $upload = handle_upload('image_file', 'ads');
        if ($upload) $data['image_url'] = $upload;

        if (!$data['title'] || !$data['position']) {
            flash_set('error', 'शीर्षक र स्थान आवश्यक छ।');
        } else {
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            save_ad($data, $id);
            flash_set('success', $id ? 'विज्ञापन अपडेट गरियो।' : 'नयाँ विज्ञापन थपियो।');
        }
        redirect('admin/advertisements');
    }

    if ($action === 'toggle') {
        $id  = (int)($_POST['id'] ?? 0);
        $cur = db_fetch("SELECT active FROM advertisements WHERE id=?", [$id]);
        if ($cur) {
            db_query("UPDATE advertisements SET active=? WHERE id=?", [$cur['active']?0:1, $id]);
            flash_set('success', 'स्थिति परिवर्तन गरियो।');
        }
        redirect('admin/advertisements');
    }

    if ($action === 'delete') {
        delete_ad((int)($_POST['id'] ?? 0));
        flash_set('success', 'विज्ञापन मेटाइयो।');
        redirect('admin/advertisements');
    }
}

$edit_id  = (int)($_GET['edit'] ?? 0);
$edit_ad  = $edit_id ? db_fetch("SELECT * FROM advertisements WHERE id=?", [$edit_id]) : null;
$all_ads  = get_all_ads();
$new_mode = isset($_GET['new']) || $edit_ad;

// Ad positions reference
$positions = [
    'header-banner'  => ['Header Banner (728×90)',       'desktop'],
    'header-banner-inline' => ['Header Banner Inline',    'all'],
    'sidebar-top'    => ['Sidebar Top (300×250)',         'desktop'],
    'sidebar-bottom' => ['Sidebar Bottom (300×250)',      'desktop'],
    'article-middle' => ['Article Middle (728×90)',       'all'],
    'article-bottom' => ['Article Bottom (728×90)',       'all'],
    'in-feed'        => ['In-Feed / Native Ad',           'all'],
    'footer-banner'  => ['Footer Banner (728×90)',        'all'],
    'popup'          => ['Popup / Interstitial',          'all'],
];

admin_html_start('विज्ञापन व्यवस्थापन');
admin_sidebar('advertisements');
?>
<div class="admin-content">
<?php admin_topbar('विज्ञापन व्यवस्थापन'); ?>
<div class="p-6">
<?php admin_flash(); ?>

<?php if ($new_mode): ?>
<!-- ── Add / Edit Form ── -->
<div class="rounded-lg mb-6" style="border:1px solid var(--c-admin-border);background:var(--c-admin-surface)">
  <div class="px-5 py-3 flex items-center justify-between" style="border-bottom:1px solid var(--c-admin-border)">
    <h2 class="font-bold text-sm flex items-center gap-2">
      <i data-lucide="<?= $edit_ad ? 'pencil' : 'plus-circle' ?>" class="w-4 h-4"></i>
      <?= $edit_ad ? 'विज्ञापन सम्पादन' : 'नयाँ विज्ञापन' ?>
    </h2>
    <a href="/admin/advertisements" class="btn btn-secondary btn-sm gap-1">
      <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> सूचीमा फिर्ता
    </a>
  </div>
  <form method="POST" action="/admin/advertisements" enctype="multipart/form-data" class="p-5"
        x-data="{adType: '<?= h($edit_ad['type'] ?? 'image') ?>'}">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <?php if ($edit_ad): ?><input type="hidden" name="id" value="<?= $edit_ad['id'] ?>"><?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <!-- Left -->
      <div class="space-y-4">
        <div>
          <label class="form-label">शीर्षक <span class="text-red-500">*</span></label>
          <input type="text" name="title" class="form-control" required
                 value="<?= h($edit_ad['title'] ?? '') ?>" placeholder="विज्ञापनको नाम">
        </div>

        <div>
          <label class="form-label">स्थान (Position) <span class="text-red-500">*</span></label>
          <select name="position" class="form-control">
            <?php foreach ($positions as $pos_key => $pos_info): ?>
            <option value="<?= h($pos_key) ?>" <?= ($edit_ad['position']??'')===$pos_key?'selected':'' ?>>
              <?= h($pos_info[0]) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="form-label">प्रकार</label>
            <select name="type" class="form-control" x-model="adType">
              <option value="image" <?= ($edit_ad['type']??'image')==='image'?'selected':'' ?>>Image</option>
              <option value="code"  <?= ($edit_ad['type']??'')==='code' ?'selected':'' ?>>HTML/AdSense Code</option>
            </select>
          </div>
          <div>
            <label class="form-label">Device</label>
            <select name="device" class="form-control">
              <option value="all"     <?= ($edit_ad['device']??'all')==='all'    ?'selected':'' ?>>सबै (All)</option>
              <option value="desktop" <?= ($edit_ad['device']??'')==='desktop'   ?'selected':'' ?>>Desktop only</option>
              <option value="mobile"  <?= ($edit_ad['device']??'')==='mobile'    ?'selected':'' ?>>Mobile only</option>
            </select>
          </div>
        </div>

        <div>
          <label class="form-label">Link URL (क्लिक गर्दा जाने ठेगाना)</label>
          <input type="url" name="link_url" class="form-control"
                 value="<?= h($edit_ad['link_url'] ?? '') ?>" placeholder="https://...">
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="form-label">शुरु मिति (optional)</label>
            <input type="datetime-local" name="start_date" class="form-control"
                   value="<?= h($edit_ad['start_date'] ? date('Y-m-d\TH:i', strtotime($edit_ad['start_date'])) : '') ?>">
          </div>
          <div>
            <label class="form-label">अन्त्य मिति (optional)</label>
            <input type="datetime-local" name="end_date" class="form-control"
                   value="<?= h($edit_ad['end_date'] ? date('Y-m-d\TH:i', strtotime($edit_ad['end_date'])) : '') ?>">
          </div>
        </div>

        <div class="flex items-center gap-4">
          <div>
            <label class="form-label">क्रम</label>
            <input type="number" name="sort_order" class="form-control" style="width:80px"
                   value="<?= (int)($edit_ad['sort_order'] ?? 1) ?>" min="1">
          </div>
          <div class="pt-5">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" name="active" value="1" <?= ($edit_ad['active']??1)?'checked':'' ?> class="w-4 h-4">
              <span class="text-sm font-medium">सक्रिय (Active)</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Right -->
      <div class="space-y-4">
        <!-- Image type -->
        <div x-show="adType === 'image'">
          <label class="form-label">विज्ञापन छवि</label>
          <?php if (!empty($edit_ad['image_url'])): ?>
          <div class="mb-2 p-2 rounded" style="border:1px solid var(--c-border);background:var(--c-surface2)">
            <img src="<?= h($edit_ad['image_url']) ?>" alt="" class="max-h-28 rounded object-contain">
            <p class="text-xs mt-1" style="color:var(--c-muted)"><?= h($edit_ad['image_url']) ?></p>
          </div>
          <?php endif; ?>
          <input type="file" name="image_file" class="form-control" accept="image/*">
          <p class="text-xs mt-1" style="color:var(--c-muted)">अथवा URL प्रविष्ट गर्नुस्:</p>
          <input type="url" name="image_url" class="form-control mt-1"
                 value="<?= h($edit_ad['image_url'] ?? '') ?>" placeholder="https://...">
          <div class="mt-3 p-3 rounded text-xs" style="background:var(--c-surface2);color:var(--c-muted)">
            <strong>Recommended sizes:</strong><br>
            Header/Footer Banner: 728×90 px<br>
            Sidebar: 300×250 px<br>
            In-Feed: 600×300 px
          </div>
        </div>

        <!-- Code type -->
        <div x-show="adType === 'code'" x-cloak>
          <label class="form-label">HTML / AdSense Code</label>
          <textarea name="code" class="form-control font-mono text-xs" rows="8"
                    placeholder="&lt;script async src=&quot;...&quot;&gt;&lt;/script&gt;&#10;&lt;ins class=&quot;adsbygoogle&quot; ...&gt;&lt;/ins&gt;"><?= h($edit_ad['code'] ?? '') ?></textarea>
          <p class="text-xs mt-1" style="color:var(--c-muted)">Google AdSense, custom HTML, या JavaScript embed code</p>
        </div>

        <!-- Stats (edit only) -->
        <?php if ($edit_ad): ?>
        <div class="p-3 rounded" style="background:var(--c-surface2);border:1px solid var(--c-border)">
          <h4 class="text-xs font-bold mb-2" style="color:var(--c-muted)">PERFORMANCE</h4>
          <div class="grid grid-cols-3 gap-2 text-center">
            <div>
              <div class="text-lg font-bold"><?= np_number((int)$edit_ad['impressions']) ?></div>
              <div class="text-xs" style="color:var(--c-muted)">Impressions</div>
            </div>
            <div>
              <div class="text-lg font-bold"><?= np_number((int)$edit_ad['clicks']) ?></div>
              <div class="text-xs" style="color:var(--c-muted)">Clicks</div>
            </div>
            <div>
              <div class="text-lg font-bold">
                <?= $edit_ad['impressions'] > 0 ? number_format(($edit_ad['clicks']/$edit_ad['impressions'])*100, 2) : '0.00' ?>%
              </div>
              <div class="text-xs" style="color:var(--c-muted)">CTR</div>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="mt-5 flex gap-3">
      <button type="submit" class="btn btn-primary gap-1">
        <i data-lucide="save" class="w-4 h-4"></i> <?= $edit_ad ? 'अपडेट गर्नुस्' : 'थप्नुस्' ?>
      </button>
      <a href="/admin/advertisements" class="btn btn-secondary">रद्द गर्नुस्</a>
    </div>
  </form>
</div>

<?php else: ?>
<!-- Add button -->
<div class="flex items-center justify-between mb-4">
  <h2 class="font-bold flex items-center gap-2">
    <i data-lucide="megaphone" class="w-4 h-4"></i> सबै विज्ञापनहरू (<?= count($all_ads) ?>)
  </h2>
  <a href="/admin/advertisements?new" class="btn btn-primary gap-1">
    <i data-lucide="plus" class="w-3.5 h-3.5"></i> नयाँ विज्ञापन
  </a>
</div>
<?php endif; ?>

<!-- Ads list -->
<?php if (!empty($all_ads)): ?>
<div class="table-wrap">
  <table class="admin-table">
    <thead><tr>
      <th>शीर्षक</th>
      <th>स्थान</th>
      <th>प्रकार</th>
      <th>Device</th>
      <th>Impr.</th>
      <th>Clicks</th>
      <th>CTR</th>
      <th>मिति</th>
      <th>स्थिति</th>
      <th>कार्यहरू</th>
    </tr></thead>
    <tbody>
    <?php foreach ($all_ads as $ad): ?>
    <tr>
      <td>
        <div class="font-semibold text-sm"><?= h($ad['title']) ?></div>
        <?php if ($ad['image_url'] && $ad['type']==='image'): ?>
          <img src="<?= h($ad['image_url']) ?>" alt="" class="mt-1 rounded" style="height:28px;max-width:80px;object-fit:cover">
        <?php endif; ?>
      </td>
      <td>
        <span class="badge badge-blue text-xs"><?= h($ad['position']) ?></span>
      </td>
      <td class="text-xs"><?= h($ad['type']) ?></td>
      <td class="text-xs"><?= h($ad['device'] ?? 'all') ?></td>
      <td class="text-xs"><?= np_number((int)$ad['impressions']) ?></td>
      <td class="text-xs"><?= np_number((int)$ad['clicks']) ?></td>
      <td class="text-xs">
        <?= $ad['impressions'] > 0 ? number_format(($ad['clicks']/$ad['impressions'])*100, 1).'%' : '—' ?>
      </td>
      <td class="text-xs" style="color:var(--c-muted)">
        <?php if ($ad['start_date']): ?><div><?= date('M d', strtotime($ad['start_date'])) ?> →</div><?php endif; ?>
        <?php if ($ad['end_date']):   ?><div>→ <?= date('M d', strtotime($ad['end_date'])) ?></div><?php endif; ?>
        <?php if (!$ad['start_date'] && !$ad['end_date']): ?>सदैव<?php endif; ?>
      </td>
      <td>
        <form method="POST" action="/admin/advertisements" style="display:inline">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="toggle">
          <input type="hidden" name="id" value="<?= $ad['id'] ?>">
          <button type="submit" class="badge <?= $ad['active']?'badge-green':'badge-gray' ?>" style="cursor:pointer;border:none">
            <?= $ad['active'] ? 'सक्रिय' : 'बन्द' ?>
          </button>
        </form>
      </td>
      <td>
        <div class="flex gap-1 flex-wrap">
          <a href="/admin/advertisements?edit=<?= $ad['id'] ?>" class="btn btn-secondary btn-sm gap-1">
            <i data-lucide="pencil" class="w-3 h-3"></i>
          </a>
          <form method="POST" action="/admin/advertisements" onsubmit="return confirm('विज्ञापन मेटाउने?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $ad['id'] ?>">
            <button class="btn btn-danger btn-sm"><i data-lucide="trash-2" class="w-3 h-3"></i></button>
          </form>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="stat-card text-center py-10" style="color:var(--c-muted)">
  <i data-lucide="megaphone" class="w-10 h-10 mx-auto mb-3 opacity-30"></i>
  <p class="mb-3">कुनै विज्ञापन छैन।</p>
  <a href="/admin/advertisements?new" class="btn btn-primary gap-1">
    <i data-lucide="plus" class="w-4 h-4"></i> पहिलो विज्ञापन थप्नुस्
  </a>
</div>
<?php endif; ?>

<!-- Position reference guide -->
<div class="mt-8 rounded-lg" style="border:1px solid var(--c-admin-border);background:var(--c-admin-surface)">
  <div class="px-5 py-3 flex items-center gap-2" style="border-bottom:1px solid var(--c-admin-border)">
    <i data-lucide="map" class="w-4 h-4"></i>
    <h3 class="font-bold text-sm">Ad Position Guide</h3>
  </div>
  <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
    <?php foreach ($positions as $pos_key => $pos_info): ?>
    <div class="p-3 rounded" style="border:1px solid var(--c-border);background:var(--c-surface2)">
      <code class="text-xs font-mono" style="color:var(--c-primary-lt)"><?= h($pos_key) ?></code>
      <p class="text-xs mt-0.5" style="color:var(--c-muted)"><?= h($pos_info[0]) ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</div>

</div>
</div>
</body></html>
