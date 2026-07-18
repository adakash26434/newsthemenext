<?php
admin_check();
$eid   = (int)($_GET['event_id'] ?? 0);
$event = get_event_by_id($eid);
if (!$event) { flash_set('error','कार्यक्रम भेटिएन।'); redirect('admin/events'); }
$regs  = get_event_registrations($eid, ['limit'=>500]);

admin_html_start('दर्ताहरू — ' . $event['title']);
admin_sidebar('events');
?>
<div class="admin-content">
<?php admin_topbar('दर्ताहरू: ' . h($event['title'])); ?>
<div class="p-6">
<?php admin_flash(); ?>

<div class="flex items-center gap-3 mb-5 flex-wrap">
  <a href="/admin/events?action=edit&id=<?= $event['id'] ?>" class="btn btn-secondary btn-sm flex items-center gap-1">
    <?= icon('arrow-left','icon-sm') ?> कार्यक्रममा फिर्ता
  </a>
  <a href="/admin/events/registrations/export?event_id=<?= $event['id'] ?>" class="btn btn-primary btn-sm flex items-center gap-1">
    <?= icon('download','icon-sm') ?> CSV Export
  </a>
  <span class="text-sm" style="color:var(--c-muted)">जम्मा दर्ता: <strong><?= count($regs) ?></strong>
    <?php if ($event['capacity']): ?> / <?= $event['capacity'] ?><?php endif; ?>
  </span>
</div>

<!-- Stats strip -->
<?php
$pending   = count(array_filter($regs, fn($r)=>$r['status']==='pending'));
$confirmed = count(array_filter($regs, fn($r)=>$r['status']==='confirmed'));
$attended  = count(array_filter($regs, fn($r)=>$r['status']==='attended'));
?>
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
  <div class="stat-card text-center py-3">
    <div class="flex justify-center mb-1"><?= icon('users','w-5 h-5 opacity-60') ?></div>
    <div class="text-2xl font-bold"><?= count($regs) ?></div>
    <div class="text-xs" style="color:var(--c-muted)">जम्मा</div>
  </div>
  <div class="stat-card text-center py-3">
    <div class="flex justify-center mb-1"><?= icon('clock','w-5 h-5 opacity-60') ?></div>
    <div class="text-2xl font-bold" style="color:#D97706"><?= $pending ?></div>
    <div class="text-xs" style="color:var(--c-muted)">Pending</div>
  </div>
  <div class="stat-card text-center py-3">
    <div class="flex justify-center mb-1"><?= icon('check-circle','w-5 h-5 opacity-60') ?></div>
    <div class="text-2xl font-bold" style="color:#059669"><?= $confirmed ?></div>
    <div class="text-xs" style="color:var(--c-muted)">Confirmed</div>
  </div>
  <div class="stat-card text-center py-3">
    <div class="flex justify-center mb-1"><?= icon('user-check','w-5 h-5 opacity-60') ?></div>
    <div class="text-2xl font-bold" style="color:#0891B2"><?= $attended ?></div>
    <div class="text-xs" style="color:var(--c-muted)">उपस्थित</div>
  </div>
</div>

<?php if (empty($regs)): ?>
<div class="stat-card text-center py-10" style="color:var(--c-muted)">
  <div class="flex justify-center mb-3"><?= icon('clipboard-list','w-12 h-12 opacity-30') ?></div>
  <p>कुनै दर्ता छैन।</p>
</div>
<?php else: ?>
<div class="table-wrap">
  <table class="admin-table">
    <thead><tr>
      <th>#</th>
      <th>नाम</th>
      <th>Email</th>
      <th>फोन</th>
      <th>संस्था</th>
      <th>सन्देश</th>
      <th>स्थिति</th>
      <th>मिति</th>
      <th>कार्य</th>
    </tr></thead>
    <tbody>
    <?php foreach ($regs as $i => $r): ?>
    <tr>
      <td><?= np_number($i+1) ?></td>
      <td class="font-semibold"><?= h($r['full_name']) ?></td>
      <td><a href="mailto:<?= h($r['email']) ?>" class="underline" style="color:var(--c-primary-lt)"><?= h($r['email']) ?></a></td>
      <td><?= h($r['phone'] ?: '—') ?></td>
      <td><?= h($r['organization'] ?: '—') ?></td>
      <td class="text-xs" style="max-width:150px;white-space:normal"><?= h($r['message'] ?: '—') ?></td>
      <td>
        <span class="badge <?= match($r['status']) {
          'confirmed'=>'badge-green','attended'=>'badge-blue','cancelled'=>'badge-gray', default=>'badge-yellow'
        } ?>"><?= match($r['status']) {
          'pending'=>'Pending','confirmed'=>'Confirmed','attended'=>'उपस्थित','cancelled'=>'रद्द', default=>h($r['status'])
        } ?></span>
      </td>
      <td class="text-xs"><?= format_date($r['registered_at']) ?></td>
      <td>
        <form method="POST" action="/admin/events/registrations/update" class="flex gap-1">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= $r['id'] ?>">
          <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
          <select name="status" class="form-control" style="padding:2px 6px;font-size:0.72rem">
            <?php foreach (['pending','confirmed','attended','cancelled'] as $st): ?>
              <option value="<?= $st ?>" <?= $r['status']===$st?'selected':'' ?>><?= $st ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn btn-primary btn-sm flex items-center" title="Update">
            <?= icon('check','icon-sm') ?>
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
</body></html>
