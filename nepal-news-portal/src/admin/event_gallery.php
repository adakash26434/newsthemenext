<?php
admin_check();
$eid   = (int)($_GET['event_id'] ?? 0);
$event = get_event_by_id($eid);
if (!$event) { flash_set('error','कार्यक्रम भेटिएन।'); redirect('admin/events'); }

admin_html_start('Gallery — ' . $event['title']);
admin_sidebar('events');
?>
<div class="admin-content">
<?php admin_topbar('Gallery: ' . h($event['title'])); ?>
<div class="p-6">
<?php admin_flash(); ?>

<div class="flex items-center gap-3 mb-5 flex-wrap">
  <a href="/admin/events?action=edit&id=<?= $event['id'] ?>" class="btn btn-secondary btn-sm">← कार्यक्रममा फिर्ता</a>
  <a href="/event/<?= h($event['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm">👁️ Public View</a>
</div>

<!-- Add media form -->
<div class="stat-card mb-6" x-data="{type:'photo'}">
  <h2 class="font-bold text-sm mb-4" style="border-bottom:1px solid var(--c-admin-border);padding-bottom:8px">📸 नयाँ मिडिया थप्नुस्</h2>
  <form method="POST" action="/admin/events/gallery/add" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="form-group">
        <label class="form-label">प्रकार</label>
        <select name="media_type" class="form-control" x-model="type">
          <option value="photo">📷 फोटो</option>
          <option value="video">🎬 भिडियो (YouTube/Vimeo)</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Caption</label>
        <input type="text" name="caption" class="form-control" placeholder="फोटोको विवरण...">
      </div>
      <div class="form-group" x-show="type==='photo'">
        <label class="form-label">फोटो Upload</label>
        <input type="file" name="photo_file" class="form-control" accept="image/*">
      </div>
      <div class="form-group" x-show="type==='video'" x-cloak>
        <label class="form-label">Video URL (YouTube embed URL)</label>
        <input type="url" name="video_url" class="form-control" placeholder="https://www.youtube.com/embed/...">
      </div>
      <div class="form-group">
        <label class="form-label">क्रम (Sort Order)</label>
        <input type="number" name="sort_order" class="form-control" value="0" min="0">
      </div>
    </div>
    <button type="submit" class="btn btn-primary mt-2">+ थप्नुस्</button>
  </form>
</div>

<!-- Gallery grid -->
<?php if (empty($event['gallery'])): ?>
<div class="stat-card text-center py-10" style="color:var(--c-muted)">
  <div class="text-4xl mb-3">🖼️</div>
  <p>कुनै फोटो/भिडियो थपिएको छैन।</p>
</div>
<?php else: ?>
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
  <?php foreach ($event['gallery'] as $m): ?>
  <div class="stat-card p-0 overflow-hidden group">
    <?php if ($m['media_type'] === 'photo' && $m['file_path']): ?>
      <div style="aspect-ratio:4/3;background:var(--c-border2)">
        <img src="<?= h($m['file_path']) ?>" alt="<?= h($m['caption']) ?>"
             class="w-full h-full object-cover">
      </div>
    <?php elseif ($m['media_type'] === 'video' && $m['video_url']): ?>
      <div style="aspect-ratio:16/9;background:#000">
        <iframe src="<?= h($m['video_url']) ?>" class="w-full h-full" style="border:none" allowfullscreen></iframe>
      </div>
    <?php endif; ?>
    <div class="p-2">
      <?php if ($m['caption']): ?><p class="text-xs mb-2 truncate"><?= h($m['caption']) ?></p><?php endif; ?>
      <form method="POST" action="/admin/events/gallery/delete" onsubmit="return confirm('मेटाउने?')">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $m['id'] ?>">
        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
        <button class="btn btn-danger btn-sm w-full">🗑️ मेटाउनुस्</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
</div>
</div>
</body></html>
