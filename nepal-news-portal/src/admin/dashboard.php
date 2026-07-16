<?php
admin_check();
$stats = get_dashboard_stats();
admin_html_start('Dashboard');
admin_sidebar('dashboard');
?>
<div class="admin-content">
<?php admin_topbar('ड्यासबोर्ड'); ?>
<div class="p-6">
<?php admin_flash(); ?>

<!-- Stat cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
  <div class="stat-card">
    <div class="value"><?= np_number($stats['total']) ?></div>
    <div class="label">कुल लेख</div>
  </div>
  <div class="stat-card">
    <div class="value" style="color:#16A34A"><?= np_number($stats['published']) ?></div>
    <div class="label">प्रकाशित</div>
  </div>
  <div class="stat-card">
    <div class="value" style="color:#D97706"><?= np_number($stats['draft']) ?></div>
    <div class="label">ड्राफ्ट</div>
  </div>
  <div class="stat-card">
    <div class="value" style="color:#6D28D9"><?= np_number($stats['views']) ?></div>
    <div class="label">कुल दृश्य</div>
  </div>
  <div class="stat-card">
    <div class="value"><?= np_number($stats['cats']) ?></div>
    <div class="label">श्रेणीहरू</div>
  </div>
  <div class="stat-card">
    <div class="value"><?= np_number($stats['auths']) ?></div>
    <div class="label">लेखकहरू</div>
  </div>
  <div class="stat-card">
    <div class="value" style="color:#0891B2"><?= np_number($stats['ads_active']) ?></div>
    <div class="label">सक्रिय विज्ञापन</div>
  </div>
  <div class="stat-card">
    <div class="value" style="color:#BE185D"><?= np_number($stats['ads_total']) ?></div>
    <div class="label">कुल विज्ञापन</div>
  </div>
  <div class="stat-card">
    <div class="value" style="color:#059669"><?= np_number($stats['events_total']) ?></div>
    <div class="label">कार्यक्रमहरू</div>
  </div>
  <div class="stat-card">
    <div class="value" style="color:#7C3AED"><?= np_number($stats['events_reg']) ?></div>
    <div class="label">Event दर्ता</div>
  </div>
  <div class="stat-card">
    <div class="value" style="color:#D97706"><?= np_number($stats['subscribers']) ?></div>
    <div class="label">न्यूजलेटर सदस्य</div>
  </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <!-- Recent articles -->
  <div class="lg:col-span-2 rounded-lg overflow-hidden" style="border:1px solid var(--c-admin-border);background:var(--c-admin-surface)">
    <div class="px-5 py-3 flex items-center justify-between" style="border-bottom:1px solid var(--c-admin-border)">
      <h2 class="font-bold text-sm">ताजा लेखहरू</h2>
      <a href="/admin/articles" class="text-xs" style="color:var(--c-primary-lt)">सबै हेर्नुस् &rarr;</a>
    </div>
    <table class="data-table" style="border:none;border-radius:0">
      <thead>
        <tr>
          <th>शीर्षक</th>
          <th>श्रेणी</th>
          <th>स्थिति</th>
          <th>दृश्य</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($stats['recent'] as $a): ?>
        <tr>
          <td>
            <a href="/article/<?= h($a['slug']) ?>" class="font-semibold hover:underline" style="color:var(--c-primary-lt)" target="_blank">
              <?= h(mb_substr($a['title'],0,40)) ?>…
            </a>
          </td>
          <td>
            <span class="badge" style="background:<?= h(category_color($a['category_color'])) ?>;color:#fff;font-size:0.65rem">
              <?= h($a['category_name_np'] ?: $a['category_name']) ?>
            </span>
          </td>
          <td>
            <span class="badge <?= $a['status']==='published'?'badge-green':'badge-gray' ?>">
              <?= $a['status']==='published'?'प्रकाशित':'ड्राफ्ट' ?>
            </span>
          </td>
          <td><?= np_number((int)$a['views']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Category breakdown -->
  <div class="rounded-lg" style="border:1px solid var(--c-admin-border);background:var(--c-admin-surface)">
    <div class="px-5 py-3" style="border-bottom:1px solid var(--c-admin-border)">
      <h2 class="font-bold text-sm">श्रेणी अनुसार लेख</h2>
    </div>
    <div class="p-4 space-y-3">
      <?php $max_cnt = max(array_column($stats['bycat'], 'cnt') ?: [1]); ?>
      <?php foreach ($stats['bycat'] as $bc): ?>
      <div>
        <div class="flex justify-between text-xs mb-1">
          <span class="font-semibold"><?= h($bc['name_np'] ?: $bc['name']) ?></span>
          <span style="color:var(--c-muted)"><?= np_number((int)$bc['cnt']) ?></span>
        </div>
        <div class="rounded-full h-1.5" style="background:var(--c-border2)">
          <div class="rounded-full h-1.5" style="background:var(--c-primary-lt);width:<?= $max_cnt>0?round(($bc['cnt']/$max_cnt)*100):0 ?>%"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Quick actions -->
<div class="mt-6">
  <h2 class="font-bold text-sm mb-3">द्रुत कार्यहरू</h2>
  <div class="flex flex-wrap gap-3">
    <a href="/admin/articles?action=new" class="btn btn-primary">+ नयाँ लेख</a>
    <a href="/admin/categories" class="btn btn-secondary">श्रेणी व्यवस्थापन</a>
    <a href="/admin/advertisements" class="btn btn-secondary">विज्ञापन व्यवस्थापन</a>
    <a href="/admin/settings" class="btn btn-secondary">साइट सेटिङ्स</a>
    <a href="/" target="_blank" class="btn btn-secondary">साइट हेर्नुस् ↗</a>
  </div>
</div>
</div>
</div>
</body></html>
