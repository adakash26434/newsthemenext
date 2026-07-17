<?php
$page      = max(1, (int)($_GET['page'] ?? 1));
$per_page  = 12;
$opts      = ['status'=>'upcoming'];
$total     = count_events($opts);
$pag       = paginate($total, $per_page, $page, '/events?page={page}');
$events    = get_events(array_merge($opts, ['limit'=>$per_page,'offset'=>$pag['offset'],'order'=>'start_datetime ASC']));
$past      = get_events(['status'=>'completed','limit'=>6,'order'=>'start_datetime DESC']);

$page_title = 'कार्यक्रमहरू — ' . site_name();
$page_desc  = 'नेपालका आगामी र विगत कार्यक्रमहरू।';

require SRC_DIR . '/layout/header.php';
?>

<div class="section-heading mb-5">
  <span class="flex items-center gap-2"><?= icon('calendar-days','w-4 h-4') ?> आगामी कार्यक्रमहरू</span>
</div>

<?php if (empty($events)): ?>
<div class="stat-card text-center py-10" style="color:var(--c-muted)">
  <?= icon('calendar-x','w-10 h-10 mx-auto mb-3 opacity-30') ?>
  <p>कुनै आगामी कार्यक्रम छैन।</p>
</div>
<?php else: ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
  <?php foreach ($events as $ev): ?>
  <a href="/event/<?= h($ev['slug']) ?>" class="event-card block">
    <div class="cover">
      <?php if ($ev['cover_image']): ?>
        <img src="<?= h($ev['cover_image']) ?>" alt="<?= h($ev['title']) ?>" loading="lazy">
      <?php else: ?>
        <div class="w-full h-full flex items-center justify-center" style="background:linear-gradient(135deg,var(--c-primary),var(--c-primary-lt))">
          <?= icon('calendar-days','w-12 h-12','w-12 h-12 inline-block align-middle flex-shrink-0') ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="p-4">
      <div class="flex items-center gap-2 mb-2 flex-wrap">
        <span class="badge <?= $ev['status']==='ongoing'?'badge-green':'badge-blue' ?>">
          <?= $ev['status']==='ongoing' ? 'भइरहेको' : 'आगामी' ?>
        </span>
        <?php if ($ev['registration_open']): ?>
          <span class="badge badge-yellow flex items-center gap-1"><?= icon('user-plus','w-2.5 h-2.5') ?> दर्ता खुला</span>
        <?php endif; ?>
      </div>
      <h2 class="font-bold text-sm leading-snug mb-2" style="color:var(--c-text)"><?= h($ev['title']) ?></h2>
      <?php if ($ev['start_datetime']): ?>
      <p class="text-xs flex items-center gap-1" style="color:var(--c-muted)">
        <?= icon('calendar','w-3 h-3') ?>
        <?= format_date($ev['start_datetime'], true) ?>
      </p>
      <?php endif; ?>
      <?php if ($ev['venue']): ?>
      <p class="text-xs flex items-center gap-1 mt-1" style="color:var(--c-muted)">
        <?= icon('map-pin','w-3 h-3') ?> <?= h($ev['venue']) ?>
      </p>
      <?php endif; ?>
    </div>
  </a>
  <?php endforeach; ?>
</div>
<?php render_pagination($pag); ?>
<?php endif; ?>

<?php if (!empty($past)): ?>
<div class="mt-8">
  <div class="section-heading mb-5">
    <span class="flex items-center gap-2"><?= icon('calendar-check','w-4 h-4') ?> विगत कार्यक्रमहरू</span>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php foreach ($past as $ev): ?>
    <a href="/event/<?= h($ev['slug']) ?>" class="event-card block opacity-75 hover:opacity-100">
      <div class="cover">
        <?php if ($ev['cover_image']): ?>
          <img src="<?= h($ev['cover_image']) ?>" alt="" loading="lazy">
        <?php endif; ?>
      </div>
      <div class="p-4">
        <span class="badge badge-gray mb-2">सम्पन्न</span>
        <h2 class="font-bold text-sm leading-snug mb-1" style="color:var(--c-text)"><?= h($ev['title']) ?></h2>
        <?php if ($ev['start_datetime']): ?>
        <p class="text-xs flex items-center gap-1" style="color:var(--c-muted)">
          <?= icon('calendar','w-3 h-3') ?> <?= format_date($ev['start_datetime']) ?>
        </p>
        <?php endif; ?>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php require SRC_DIR . '/layout/footer.php'; ?>
