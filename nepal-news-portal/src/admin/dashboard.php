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
  <?php
  $cards = [
    ['कुल लेख',       $stats['total'],       'file-text',    ''],
    ['प्रकाशित',      $stats['published'],    'check-circle', '#16A34A'],
    ['ड्राफ्ट',       $stats['draft'],        'clock',        '#D97706'],
    ['कुल दृश्य',     $stats['views'],        'eye',          '#6D28D9'],
    ['श्रेणीहरू',     $stats['cats'],         'grid',         ''],
    ['लेखकहरू',       $stats['auths'],        'users',        ''],
    ['सक्रिय विज्ञापन',$stats['ads_active'],  'megaphone',    '#0891B2'],
    ['कुल विज्ञापन',  $stats['ads_total'],    'layers',       '#BE185D'],
    ['कार्यक्रम',     $stats['events_total'], 'calendar-days','#059669'],
    ['Event दर्ता',   $stats['events_reg'],   'user-check',   '#7C3AED'],
    ['न्यूजलेटर',     $stats['subscribers'],  'mail',         '#D97706'],
    ['टिप्पणी (पेन्डिङ)', $stats['comments_pending'] ?? 0, 'message-circle', '#EF4444'],
    ['कुल टिप्पणी',   $stats['comments_total'] ?? 0,    'messages-square',  '#0891B2'],
  ];
  foreach ($cards as [$label,$val,$ic,$color]): ?>
  <div class="stat-card">
    <div class="flex items-start justify-between mb-2">
      <div class="value" <?= $color?"style='color:$color'":'' ?>><?= np_number((int)$val) ?></div>
      <i data-lucide="<?= h($ic) ?>" class="w-5 h-5 opacity-40 flex-shrink-0" <?= $color?"style='color:$color'":'' ?>></i>
    </div>
    <div class="label"><?= h($label) ?></div>
  </div>
  <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <!-- Recent articles -->
  <div class="lg:col-span-2 rounded-lg overflow-hidden" style="border:1px solid var(--c-admin-border);background:var(--c-admin-surface)">
    <div class="px-5 py-3 flex items-center justify-between" style="border-bottom:1px solid var(--c-admin-border)">
      <h2 class="font-bold text-sm flex items-center gap-2">
        <i data-lucide="newspaper" class="w-4 h-4"></i> ताजा लेखहरू
      </h2>
      <a href="/admin/articles" class="text-xs flex items-center gap-1" style="color:var(--c-primary-lt)">
        सबै हेर्नुस् <i data-lucide="arrow-right" class="w-3 h-3"></i>
      </a>
    </div>
    <table class="data-table" style="border:none;border-radius:0">
      <thead>
        <tr><th>शीर्षक</th><th>श्रेणी</th><th>स्थिति</th><th>दृश्य</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($stats['recent'] as $a): ?>
        <tr>
          <td>
            <a href="/article/<?= h($a['slug']) ?>" class="font-semibold hover:underline" style="color:var(--c-primary-lt)" target="_blank">
              <?= h(mb_substr($a['title'],0,38)) ?>…
            </a>
          </td>
          <td>
            <span class="badge" style="background:<?= h(category_color($a['category_color'])) ?>;color:#fff;font-size:0.6rem">
              <?= h($a['category_name_np'] ?: $a['category_name']) ?>
            </span>
          </td>
          <td>
            <span class="badge <?= $a['status']==='published'?'badge-green':'badge-gray' ?>">
              <?= $a['status']==='published'?'प्रकाशित':'ड्राफ्ट' ?>
            </span>
          </td>
          <td class="text-xs"><?= np_number((int)$a['views']) ?></td>
          <td>
            <a href="/admin/articles?action=edit&id=<?= $a['id'] ?>" class="text-xs" style="color:var(--c-muted)">
              <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Category breakdown -->
  <div class="rounded-lg" style="border:1px solid var(--c-admin-border);background:var(--c-admin-surface)">
    <div class="px-5 py-3 flex items-center gap-2" style="border-bottom:1px solid var(--c-admin-border)">
      <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
      <h2 class="font-bold text-sm">श्रेणी अनुसार</h2>
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
          <div class="rounded-full h-1.5 transition-all" style="background:<?= h($bc['color']?:accent_color()) ?>;width:<?= $max_cnt>0?round(($bc['cnt']/$max_cnt)*100):0 ?>%"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Quick actions -->
<div class="mt-6">
  <h2 class="font-bold text-sm mb-3 flex items-center gap-2">
    <i data-lucide="zap" class="w-4 h-4"></i> द्रुत कार्यहरू
  </h2>
  <div class="flex flex-wrap gap-2">
    <a href="/admin/articles?action=new" class="btn btn-primary gap-1"><i data-lucide="plus" class="w-3.5 h-3.5"></i> नयाँ लेख</a>
    <a href="/admin/events?action=edit"  class="btn btn-primary gap-1"><i data-lucide="plus" class="w-3.5 h-3.5"></i> नयाँ कार्यक्रम</a>
    <a href="/admin/categories"     class="btn btn-secondary gap-1"><i data-lucide="grid" class="w-3.5 h-3.5"></i> श्रेणीहरू</a>
    <a href="/admin/authors"        class="btn btn-secondary gap-1"><i data-lucide="users" class="w-3.5 h-3.5"></i> लेखकहरू</a>
    <a href="/admin/advertisements" class="btn btn-secondary gap-1"><i data-lucide="megaphone" class="w-3.5 h-3.5"></i> विज्ञापन</a>
    <a href="/admin/pages"          class="btn btn-secondary gap-1"><i data-lucide="file-text" class="w-3.5 h-3.5"></i> पृष्ठहरू</a>
    <a href="/admin/subscribers"    class="btn btn-secondary gap-1"><i data-lucide="mail" class="w-3.5 h-3.5"></i> सदस्यहरू</a>
    <a href="/admin/settings"       class="btn btn-secondary gap-1"><i data-lucide="settings" class="w-3.5 h-3.5"></i> सेटिङ्स</a>
    <a href="/" target="_blank"     class="btn btn-secondary gap-1"><i data-lucide="external-link" class="w-3.5 h-3.5"></i> साइट हेर्नुस्</a>
  </div>
</div>
</div>
</div>
</body></html>
