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

  <!-- Category breakdown with better chart -->
  <div class="rounded-lg" style="border:1px solid var(--c-admin-border);background:var(--c-admin-surface)">
    <div class="px-5 py-3 flex items-center gap-2" style="border-bottom:1px solid var(--c-admin-border)">
      <i data-lucide="pie-chart" class="w-4 h-4"></i>
      <h2 class="font-bold text-sm">श्रेणी अनुसार</h2>
    </div>
    <div class="p-4">
      <?php if (!empty($stats['bycat'])): ?>
      <div class="flex items-center gap-6">
        <!-- Simple doughnut chart -->
        <div class="relative flex-shrink-0" style="width:100px;height:100px">
          <svg viewBox="0 0 100 100" style="transform:rotate(-90deg)">
            <?php 
              $total = array_sum(array_column($stats['bycat'], 'cnt'));
              $start = 0;
              foreach ($stats['bycat'] as $i => $bc):
                $pct = $total > 0 ? ($bc['cnt'] / $total) * 100 : 0;
                $dash = ($pct / 100) * 251.2;
                $color = $bc['color'] ?: sprintf('#%06X', mt_rand(0x333333, 0xFFFFFF));
            ?>
            <circle cx="50" cy="50" r="40" fill="none" stroke="<?= h($color) ?>" stroke-width="12"
                    stroke-dasharray="<?= $dash ?> 251.2" stroke-dashoffset="<?= -$start ?>"
                    style="transition:stroke-dasharray 0.5s ease" />
            <?php $start += $dash; endforeach; ?>
          </svg>
          <div class="absolute inset-0 flex items-center justify-center">
            <span class="text-xs font-bold" style="color:var(--c-text)"><?= np_number((int)$total) ?></span>
          </div>
        </div>
        <!-- Legend -->
        <div class="flex-1 space-y-2 max-h-48 overflow-y-auto">
          <?php foreach ($stats['bycat'] as $bc): ?>
          <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:<?= h($bc['color']?:accent_color()) ?>"></span>
              <span class="text-xs truncate"><?= h($bc['name_np'] ?: $bc['name']) ?></span>
            </div>
            <span class="text-xs font-semibold flex-shrink-0" style="color:var(--c-muted)"><?= np_number((int)$bc['cnt']) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php else: ?>
      <p class="text-sm text-center" style="color:var(--c-muted)">कुनै श्रेणी छैन</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- View chart (last 14 days) - improved -->
<?php if (!empty($stats['view_chart'])): ?>
<div class="mt-6 rounded-lg p-5" style="border:1px solid var(--c-admin-border);background:var(--c-admin-surface)">
  <h2 class="font-bold text-sm mb-4 flex items-center gap-2">
    <i data-lucide="trending-up" class="w-4 h-4"></i> पछिल्लो १४ दिन — दैनिक दृश्य
  </h2>
  <?php
    $max_v = max(array_column($stats['view_chart'], 'views') ?: [1]);
    $max_v = max($max_v, 1);
    $total_views = array_sum(array_column($stats['view_chart'], 'views'));
    $avg_views = count($stats['view_chart']) > 0 ? round($total_views / count($stats['view_chart'])) : 0;
  ?>
  <div class="flex gap-4 mb-4 text-xs" style="color:var(--c-muted)">
    <span>जम्मा: <strong style="color:var(--c-text)"><?= np_number($total_views) ?></strong></span>
    <span>औसत: <strong style="color:var(--c-text)"><?= np_number($avg_views) ?>/दिन</strong></span>
  </div>
  <div class="flex items-end gap-1" style="height:120px">
    <?php foreach ($stats['view_chart'] as $vc): ?>
    <?php $pct = round(($vc['views'] / $max_v) * 100); $pct = max($pct, 2); ?>
    <div class="flex-1 flex flex-col items-center gap-1 group cursor-pointer" title="<?= $vc['date'] ?>: <?= $vc['views'] ?> दृश्य">
      <div class="text-center text-xs font-medium px-1 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity" 
           style="background:var(--c-admin-surface);color:var(--c-text);font-size:10px;white-space:nowrap">
        <?= np_number((int)$vc['views']) ?>
      </div>
      <div class="w-full rounded-t-sm" style="height:<?= $pct ?>%;background:linear-gradient(to top,var(--c-primary),var(--c-primary-lt));min-height:4px;transition:height 0.3s ease"></div>
      <div class="text-center" style="font-size:9px;color:var(--c-muted)"><?= date('d', strtotime($vc['date'])) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

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
