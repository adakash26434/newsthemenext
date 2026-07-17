<?php
// Used by events.php and standalone
if (!isset($edit)) {
    admin_check();
    $id   = (int)($_GET['id'] ?? 0) ?: null;
    $edit = $id ? get_event_by_id($id) : null;
}
$d = [
    'title'                 => $edit['title']                 ?? '',
    'title_en'              => $edit['title_en']              ?? '',
    'slug'                  => $edit['slug']                  ?? '',
    'description'           => $edit['description']           ?? '',
    'description_en'        => $edit['description_en']        ?? '',
    'cover_image'           => $edit['cover_image']           ?? '',
    'venue'                 => $edit['venue']                 ?? '',
    'venue_en'              => $edit['venue_en']              ?? '',
    'start_datetime'        => $edit['start_datetime']        ?? '',
    'end_datetime'          => $edit['end_datetime']          ?? '',
    'registration_open'     => $edit['registration_open']     ?? 1,
    'registration_deadline' => $edit['registration_deadline'] ?? '',
    'capacity'              => $edit['capacity']              ?? '',
    'status'                => $edit['status']                ?? 'upcoming',
    'show_in_menu'          => $edit['show_in_menu']          ?? 1,
];
?>
<form method="POST" action="/admin/events/save" enctype="multipart/form-data"
      x-data="{
        title: <?= json_encode($d['title']) ?>,
        slug: <?= json_encode($d['slug']) ?>,
        autoSlug: <?= $edit?'false':'true' ?>,
        makeSlug(t){ return t.toLowerCase().replace(/[^\w\s-]/g,'').replace(/[\s_]+/g,'-').replace(/^-+|-+$/g,'')||'event'; }
      }">
  <?= csrf_field() ?>
  <?php if ($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <!-- Title NP -->
    <div class="form-group">
      <label class="form-label">शीर्षक (नेपाली) *</label>
      <input type="text" name="title" class="form-control" required
             x-model="title" @input="if(autoSlug) slug=makeSlug(title)"
             value="<?= h($d['title']) ?>">
    </div>
    <!-- Title EN -->
    <div class="form-group">
      <label class="form-label">Title (English)</label>
      <input type="text" name="title_en" class="form-control" value="<?= h($d['title_en']) ?>">
    </div>
    <!-- Slug -->
    <div class="form-group">
      <label class="form-label">URL Slug *</label>
      <div class="flex gap-2">
        <input type="text" name="slug" class="form-control flex-1"
               x-model="slug" @focus="autoSlug=false">
        <button type="button" class="btn btn-secondary btn-sm" @click="slug=makeSlug(title);autoSlug=false">Auto</button>
      </div>
      <p class="form-hint">URL: /event/<span x-text="slug||'slug'"></span></p>
    </div>
    <!-- Status -->
    <div class="form-group">
      <label class="form-label">स्थिति</label>
      <select name="status" class="form-control">
        <?php foreach (['upcoming'=>'आगामी','ongoing'=>'जारी','completed'=>'समाप्त','cancelled'=>'रद्द'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= $d['status']===$v?'selected':'' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <!-- Cover image -->
    <div class="form-group lg:col-span-2">
      <label class="form-label">Cover Image</label>
      <div class="flex gap-3 items-start flex-wrap">
        <?php if ($d['cover_image']): ?>
          <img src="<?= h($d['cover_image']) ?>" alt="Cover" class="rounded" style="max-height:80px;max-width:160px;object-fit:cover">
        <?php endif; ?>
        <div class="flex-1">
          <input type="file" name="cover_file" class="form-control" accept="image/*">
          <p class="form-hint">अथवा URL दिनुस्:</p>
          <input type="url" name="cover_image" class="form-control" value="<?= h($d['cover_image']) ?>" placeholder="https://...">
        </div>
      </div>
    </div>
    <!-- Venue NP -->
    <div class="form-group">
      <label class="form-label">स्थान (नेपाली)</label>
      <input type="text" name="venue" class="form-control" value="<?= h($d['venue']) ?>" placeholder="काठमाडौं, नेपाल">
    </div>
    <!-- Venue EN -->
    <div class="form-group">
      <label class="form-label">Venue (English)</label>
      <input type="text" name="venue_en" class="form-control" value="<?= h($d['venue_en']) ?>" placeholder="Kathmandu, Nepal">
    </div>
    <!-- Start -->
    <div class="form-group">
      <label class="form-label">शुरू मिति/समय</label>
      <input type="datetime-local" name="start_datetime" class="form-control"
             value="<?= h($d['start_datetime'] ? date('Y-m-d\TH:i', strtotime($d['start_datetime'])) : '') ?>">
    </div>
    <!-- End -->
    <div class="form-group">
      <label class="form-label">समाप्ति मिति/समय</label>
      <input type="datetime-local" name="end_datetime" class="form-control"
             value="<?= h($d['end_datetime'] ? date('Y-m-d\TH:i', strtotime($d['end_datetime'])) : '') ?>">
    </div>
    <!-- Description NP -->
    <div class="form-group lg:col-span-2">
      <label class="form-label">विवरण (नेपाली)</label>
      <textarea name="description" class="form-control" rows="5"><?= h($d['description']) ?></textarea>
    </div>
    <!-- Description EN -->
    <div class="form-group lg:col-span-2">
      <label class="form-label">Description (English)</label>
      <textarea name="description_en" class="form-control" rows="4"><?= h($d['description_en']) ?></textarea>
    </div>
    <!-- Registration deadline -->
    <div class="form-group">
      <label class="form-label">दर्ता समयसीमा</label>
      <input type="datetime-local" name="registration_deadline" class="form-control"
             value="<?= h($d['registration_deadline'] ? date('Y-m-d\TH:i', strtotime($d['registration_deadline'])) : '') ?>">
    </div>
    <!-- Capacity -->
    <div class="form-group">
      <label class="form-label">क्षमता (खाली = असीमित)</label>
      <input type="number" name="capacity" class="form-control" min="0" value="<?= h($d['capacity']) ?>" placeholder="100">
    </div>
    <!-- Checkboxes -->
    <div class="form-group lg:col-span-2 flex gap-6 flex-wrap">
      <label class="flex items-center gap-2 cursor-pointer font-medium">
        <input type="checkbox" name="registration_open" <?= $d['registration_open']?'checked':'' ?> class="rounded">
        दर्ता खुला छ
      </label>
      <label class="flex items-center gap-2 cursor-pointer font-medium">
        <input type="checkbox" name="show_in_menu" <?= $d['show_in_menu']?'checked':'' ?> class="rounded">
        Navigation menu मा देखाउनुस्
      </label>
    </div>
  </div>

  <div class="mt-5 flex gap-3 flex-wrap">
    <button type="submit" class="btn btn-primary px-8 flex items-center gap-2">
      <?= icon('save','icon-sm') ?> सेभ गर्नुस्
    </button>
    <a href="/admin/events" class="btn btn-secondary">रद्द</a>
    <?php if ($edit): ?>
    <a href="/admin/events/registrations?event_id=<?= $edit['id'] ?>" class="btn btn-secondary flex items-center gap-1">
      <?= icon('clipboard-list','icon-sm') ?> दर्ताहरू
    </a>
    <a href="/admin/events/gallery?event_id=<?= $edit['id'] ?>" class="btn btn-secondary flex items-center gap-1">
      <?= icon('image','icon-sm') ?> Gallery
    </a>
    <a href="/event/<?= h($edit['slug']) ?>" target="_blank" class="btn btn-secondary flex items-center gap-1">
      <?= icon('eye','icon-sm') ?> Preview
    </a>
    <?php endif; ?>
  </div>
</form>
