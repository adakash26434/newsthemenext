<?php
$page_title = lang_label('कार्यक्रमहरू', 'Events') . ' — ' . site_name();
$page_desc  = lang_label('नेपालका आगामी र भूत कार्यक्रमहरू।', 'Upcoming and past events in Nepal.');

$filter  = $_GET['status'] ?? 'upcoming';
$page_no = max(1, (int)($_GET['page'] ?? 1));
$per_pg  = 12;

if ($filter === 'all') {
    $all_evts = get_events(['limit'=>$per_pg,'offset'=>($page_no-1)*$per_pg]);
    $total    = db_count("SELECT COUNT(*) FROM events");
} else {
    $all_evts = get_events(['status'=>$filter,'limit'=>$per_pg,'offset'=>($page_no-1)*$per_pg]);
    $total    = db_count("SELECT COUNT(*) FROM events WHERE status=?", [$filter]);
}
$pag = paginate($total, $per_pg, $page_no, '/events?status=' . urlencode($filter) . '&page={page}');

require SRC_DIR . '/layout/header.php';
?>

<div class="mb-4">
  <h1 class="text-2xl font-extrabold mb-2"><?= lang_label('कार्यक्रमहरू', 'Events') ?></h1>
  <!-- Filter tabs -->
  <div class="flex gap-2 flex-wrap">
    <?php foreach (['upcoming'=>lang_label('आगामी','Upcoming'),'ongoing'=>lang_label('जारी','Ongoing'),'completed'=>lang_label('समाप्त','Completed'),'all'=>lang_label('सबै','All')] as $s=>$l): ?>
    <a href="/events?status=<?= $s ?>" class="btn <?= $filter===$s?'btn-primary':'btn-secondary' ?> btn-sm">
      <?= $l ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<?php if (empty($all_evts)): ?>
<div class="stat-card text-center py-16" style="color:var(--c-muted)">
  <div class="text-5xl mb-4">📅</div>
  <p class="text-lg font-semibold"><?= lang_label('कुनै कार्यक्रम छैन।', 'No events found.') ?></p>
</div>
<?php else: ?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-6">
  <?php foreach ($all_evts as $ev): ?>
  <article class="event-card group">
    <!-- Cover image -->
    <a href="/event/<?= h($ev['slug']) ?>" class="block">
      <div class="event-card-img">
        <?php if ($ev['cover_image']): ?>
          <img src="<?= h($ev['cover_image']) ?>" alt="<?= h($ev['title']) ?>" loading="lazy" class="w-full h-full object-cover transition-transform group-hover:scale-105">
        <?php else: ?>
          <div class="flex items-center justify-center h-full text-4xl">📅</div>
        <?php endif; ?>
        <!-- Status badge -->
        <span class="event-status-badge event-status-<?= h($ev['status']) ?>">
          <?= match($ev['status']) {'upcoming'=>lang_label('आगामी','Upcoming'),'ongoing'=>lang_label('जारी','Ongoing'),'completed'=>lang_label('समाप्त','Completed'),'cancelled'=>lang_label('रद्द','Cancelled'),default=>h($ev['status'])} ?>
        </span>
      </div>
    </a>
    <div class="p-4">
      <h2 class="text-base font-bold leading-snug mb-2 group-hover:underline">
        <a href="/event/<?= h($ev['slug']) ?>">
          <?= h(current_lang()==='en'?($ev['title_en']?:$ev['title']):$ev['title']) ?>
        </a>
      </h2>
      <div class="space-y-1 text-xs mb-3" style="color:var(--c-muted)">
        <?php if ($ev['start_datetime']): ?>
        <div class="flex items-center gap-1">
          <span>📅</span>
          <span><?= format_date($ev['start_datetime'], true) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($ev['venue']): ?>
        <div class="flex items-center gap-1">
          <span>📍</span>
          <span><?= h(current_lang()==='en'?($ev['venue_en']?:$ev['venue']):$ev['venue']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($ev['registration_open']): ?>
        <div class="flex items-center gap-1 font-semibold" style="color:var(--c-primary-lt)">
          <span>✅</span>
          <span><?= lang_label('दर्ता खुला छ', 'Registration Open') ?></span>
        </div>
        <?php endif; ?>
      </div>
      <a href="/event/<?= h($ev['slug']) ?>" class="btn btn-primary btn-sm w-full justify-center">
        <?= lang_label('थप जानकारी', 'Learn More') ?> →
      </a>
    </div>
  </article>
  <?php endforeach; ?>
</div>
<?php render_pagination($pag); ?>
<?php endif; ?>

<?php require SRC_DIR . '/layout/footer.php'; ?>
