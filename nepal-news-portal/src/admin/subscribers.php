<?php
admin_check();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action']??'') === 'delete') {
        db_query("DELETE FROM newsletter_subscribers WHERE id=?", [(int)($_POST['id']??0)]);
        flash_set('success', 'सदस्य मेटाइयो।');
        redirect('admin/subscribers');
    }
    // ── Newsletter broadcast ───────────────────────────────
    if (($_POST['action']??'') === 'broadcast') {
        $subject = trim($_POST['subject'] ?? '');
        $body    = trim($_POST['body']    ?? '');
        if (!$subject || !$body) {
            flash_set('error', 'विषय र सन्देश अनिवार्य छ।');
            redirect('admin/subscribers');
        }
        $from_email = setting('contact_email', '');
        if (!$from_email) {
            flash_set('error', 'पहिले Settings → Contact Email सेट गर्नुस्।');
            redirect('admin/subscribers');
        }
        $all_subs = db_fetchAll("SELECT email, name, token FROM newsletter_subscribers");
        $sent = 0;
        $site = site_name();
        $base = rtrim(setting('site_url','https://localhost'),'/');
        foreach ($all_subs as $sub) {
            $unsub  = $base . '/newsletter/unsubscribe?token=' . urlencode($sub['token'] ?? '');
            $msg    = ($sub['name'] ? $sub['name'] . " जी,\n\n" : '') . $body
                    . "\n\n---\nसदस्यता रद्द गर्न: $unsub\n— {$site} टिम";
            $hdrs   = "From: {$site} <{$from_email}>\r\nContent-Type: text/plain; charset=UTF-8\r\n";
            if (@mail($sub['email'], $subject, $msg, $hdrs)) $sent++;
        }
        flash_set('success', np_number($sent) . ' सदस्यहरूलाई इमेल पठाइयो।');
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

<!-- Newsletter Broadcast Form -->
<?php if (count_subscribers() > 0): ?>
<div class="stat-card mt-6">
  <h2 class="font-bold text-sm mb-4 flex items-center gap-2">
    <?= icon('send','w-4 h-4') ?> सबै सदस्यलाई इमेल पठाउनुस् (Broadcast)
  </h2>
  <form method="POST" action="/admin/subscribers" onsubmit="return confirm('<?= np_number(count_subscribers()) ?> सदस्यलाई इमेल पठाउने?')">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="broadcast">
    <div class="space-y-3">
      <div>
        <label class="form-label">विषय (Subject) <span class="text-red-500">*</span></label>
        <input type="text" name="subject" class="form-control" required placeholder="इमेलको विषय...">
      </div>
      <div>
        <label class="form-label">सन्देश (Body) <span class="text-red-500">*</span></label>
        <textarea name="body" class="form-control" rows="5" required placeholder="इमेलको मूल सन्देश..."></textarea>
        <p class="form-help">Unsubscribe link स्वतः जोडिनेछ।</p>
      </div>
      <button type="submit" class="btn btn-primary gap-1">
        <?= icon('send','w-3.5 h-3.5') ?> <?= np_number(count_subscribers()) ?> सदस्यलाई पठाउनुस्
      </button>
    </div>
  </form>
</div>
<?php endif; ?>
</div>
</div>
</body></html>
