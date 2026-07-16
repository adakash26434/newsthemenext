<?php
admin_check();
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 50;
$offset   = ($page - 1) * $per_page;
$total    = count_subscribers();
$subs     = get_subscribers($per_page, $offset);
$pag      = paginate($total, $per_page, $page, '/admin/subscribers?page={page}');

admin_html_start('न्यूजलेटर सदस्यहरू');
admin_sidebar('subscribers');
?>
<div class="admin-content">
<?php admin_topbar('न्यूजलेटर सदस्यहरू'); ?>
<div class="p-6">
<?php admin_flash(); ?>

<div class="flex items-center justify-between mb-4 flex-wrap gap-2">
  <div>
    <h2 class="font-bold">सबै सदस्यहरू</h2>
    <p class="text-sm" style="color:var(--c-muted)">जम्मा: <strong><?= np_number($total) ?></strong> सदस्य</p>
  </div>
  <a href="/admin/subscribers?export=1" class="btn btn-primary btn-sm">📥 CSV Export</a>
</div>

<?php
// CSV Export
if (isset($_GET['export'])) {
    $all = get_subscribers(99999, 0);
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="subscribers-' . date('Y-m-d') . '.csv"');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output','w');
    fputcsv($out, ['ID','नाम','Email','मिति']);
    foreach ($all as $s) fputcsv($out,[$s['id'],$s['name'],$s['email'],$s['created_at']]);
    fclose($out); exit;
}
?>

<?php if (empty($subs)): ?>
<div class="stat-card text-center py-12" style="color:var(--c-muted)">
  <div class="text-4xl mb-3">📧</div>
  <p>कुनै सदस्य छैन।</p>
</div>
<?php else: ?>
<div class="table-wrap">
  <table class="admin-table">
    <thead><tr>
      <th>#</th>
      <th>नाम</th>
      <th>Email</th>
      <th>दर्ता मिति</th>
    </tr></thead>
    <tbody>
    <?php foreach ($subs as $i => $s): ?>
    <tr>
      <td><?= np_number($offset + $i + 1) ?></td>
      <td><?= h($s['name'] ?: '—') ?></td>
      <td><a href="mailto:<?= h($s['email']) ?>" style="color:var(--c-primary-lt)"><?= h($s['email']) ?></a></td>
      <td class="text-xs"><?= format_date($s['created_at']) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php render_pagination($pag); ?>
<?php endif; ?>
</div>
</div>
</body></html>
