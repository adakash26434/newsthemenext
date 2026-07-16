<?php
admin_check();
$events = get_events(['limit' => 50]);
$edit   = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit = get_event_by_id((int)$_GET['id']);
}

admin_html_start('कार्यक्रम व्यवस्थापन');
admin_sidebar('events');
?>
<div class="admin-content">
<?php admin_topbar('कार्यक्रम व्यवस्थापन'); ?>
<div class="p-6">
<?php admin_flash(); ?>

<?php if ($edit): ?>
<!-- Edit form -->
<div class="stat-card mb-6">
  <div class="flex items-center justify-between mb-4">
    <h2 class="font-bold">कार्यक्रम सम्पादन: <?= h($edit['title']) ?></h2>
    <a href="/admin/events" class="btn btn-secondary btn-sm">← सूचीमा फिर्ता</a>
  </div>
  <?php require __DIR__ . '/event_form.php'; ?>
</div>
<?php else: ?>

<div class="flex items-center justify-between mb-4 flex-wrap gap-2">
  <h2 class="font-bold">सबै कार्यक्रमहरू (<?= count($events) ?>)</h2>
  <a href="/admin/events?action=edit" class="btn btn-primary">+ नयाँ कार्यक्रम</a>
</div>

<?php if (empty($events)): ?>
<div class="stat-card text-center py-10" style="color:var(--c-muted)">
  <div class="text-4xl mb-3">📅</div>
  <p>कुनै कार्यक्रम छैन।</p>
  <a href="/admin/events?action=edit" class="btn btn-primary mt-3">पहिलो कार्यक्रम थप्नुस्</a>
</div>
<?php else: ?>
<div class="table-wrap">
  <table class="admin-table">
    <thead><tr>
      <th>शीर्षक</th>
      <th>मिति</th>
      <th>स्थान</th>
      <th>स्थिति</th>
      <th>दर्ता</th>
      <th>कार्यहरू</th>
    </tr></thead>
    <tbody>
    <?php foreach ($events as $ev): ?>
    <tr>
      <td>
        <div class="font-semibold text-sm"><?= h($ev['title']) ?></div>
        <?php if ($ev['title_en']): ?><div class="text-xs" style="color:var(--c-muted)"><?= h($ev['title_en']) ?></div><?php endif; ?>
      </td>
      <td class="text-xs"><?= $ev['start_datetime'] ? format_date($ev['start_datetime']) : '—' ?></td>
      <td class="text-xs"><?= h($ev['venue'] ?: '—') ?></td>
      <td>
        <span class="badge <?= match($ev['status']) {
          'upcoming' => 'badge-blue',
          'ongoing'  => 'badge-green',
          'completed'=> 'badge-gray',
          default    => 'badge-gray'
        } ?>"><?= match($ev['status']) {
          'upcoming' => 'आगामी', 'ongoing' => 'जारी',
          'completed'=> 'समाप्त', 'cancelled' => 'रद्द', default => h($ev['status'])
        } ?></span>
      </td>
      <td>
        <a href="/admin/events/registrations?event_id=<?= $ev['id'] ?>" class="text-xs underline" style="color:var(--c-primary-lt)">
          दर्ता हेर्नुस्
        </a>
      </td>
      <td>
        <div class="flex gap-1 flex-wrap">
          <a href="/admin/events?action=edit&id=<?= $ev['id'] ?>" class="btn btn-secondary btn-sm">✏️ सम्पादन</a>
          <a href="/admin/events/gallery?event_id=<?= $ev['id'] ?>" class="btn btn-secondary btn-sm">🖼️ Gallery</a>
          <a href="/event/<?= h($ev['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm">👁️</a>
          <form method="POST" action="/admin/events/delete" onsubmit="return confirm('मेटाउने?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $ev['id'] ?>">
            <button class="btn btn-danger btn-sm">🗑️</button>
          </form>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
<?php endif; ?>
</div>
</div>
</body></html>
