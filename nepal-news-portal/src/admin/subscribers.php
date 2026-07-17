<?php
admin_check();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action']??'') === 'delete') {
        db_query("DELETE FROM newsletter_subscribers WHERE id=?", [(int)($_POST['id']??0)]);
        flash_set('success', 'सदस्य मेटाइयो।');
        redirect('admin/subscribers');
    }
}

// CSV export
if (isset($_GET['export'])) {
    $all = db_fetchAll("SELECT * FROM newsletter_subscribers ORDER BY created_at DESC");
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="subscribers-'.date('Y-m-d').'.csv"');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    echo "ID,Email,Name,Created\n";
    foreach ($all as $s) echo $s['id'].','.'"'.str_replace('"','""',$s['email']).'",'.$s['name'].','.$s['created_at']."\n";
    exit;
}

$page = max(1, (int)($_GET['page'] ?? 1));
$pag  = paginate(count_subscribers(), 50, $page, '/admin/subscribers?page={page}');
$subs = get_subscribers(50, $pag['offset']);

admin_html_start('न्यूजलेटर सदस्यहरू');
admin_sidebar('subscribers');
?>
<div class="admin-content">
<?php admin_topbar('न्यूजलेटर सदस्यहरू'); ?>
<div class="p-6">
<?php admin_flash(); ?>

<div class="flex items-center justify-between mb-4 flex-wrap gap-3">
  <div class="text-sm flex items-center gap-2" style="color:var(--c-muted)">
    <i data-lucide="users" class="w-4 h-4"></i>
    कुल सदस्यहरू: <strong><?= np_number($pag['total']) ?></strong>
  </div>
  <a href="/admin/subscribers?export=1" class="btn btn-secondary gap-1">
    <i data-lucide="download" class="w-3.5 h-3.5"></i> CSV Export
  </a>
</div>

<?php if (empty($subs)): ?>
<div class="stat-card text-center py-10" style="color:var(--c-muted)">
  <i data-lucide="mail" class="w-10 h-10 mx-auto mb-3 opacity-30"></i>
  <p>कुनै सदस्य छैन।</p>
</div>
<?php else: ?>
<div class="table-wrap">
  <table class="admin-table">
    <thead><tr><th>#</th><th>इमेल</th><th>नाम</th><th>मिति</th><th>कार्य</th></tr></thead>
    <tbody>
    <?php foreach ($subs as $s): ?>
    <tr>
      <td class="text-xs"><?= np_number((int)$s['id']) ?></td>
      <td class="font-semibold"><?= h($s['email']) ?></td>
      <td><?= h($s['name'] ?? '—') ?></td>
      <td class="text-xs" style="color:var(--c-muted)"><?= format_date($s['created_at']) ?></td>
      <td>
        <form method="POST" action="/admin/subscribers" onsubmit="return confirm('मेटाउने?')">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $s['id'] ?>">
          <button class="btn btn-danger btn-sm"><?= icon('trash-2','w-3 h-3') ?></button>
        </form>
      </td>
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
