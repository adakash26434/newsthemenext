<?php
$slug  = $_slug ?? '';
$event = get_event_by_slug($slug);
if (!$event) { http_response_code(404); require SRC_DIR . '/pages/404.php'; exit; }

$lang    = current_lang();
$title   = $lang==='en' ? ($event['title_en']?:$event['title']) : $event['title'];
$desc    = $lang==='en' ? ($event['description_en']?:$event['description']) : $event['description'];
$venue   = $lang==='en' ? ($event['venue_en']?:$event['venue']) : $event['venue'];

$page_title = h($title) . ' — ' . site_name();
$page_desc  = excerpt(strip_tags($desc ?? ''), 25);
$og_image   = $event['cover_image'] ?? '';

require SRC_DIR . '/layout/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <article class="lg:col-span-2">

    <!-- Cover image -->
    <?php if ($event['cover_image']): ?>
    <div class="mb-5 rounded-xl overflow-hidden" style="max-height:420px">
      <img src="<?= h($event['cover_image']) ?>" alt="<?= h($title) ?>" class="w-full object-cover">
    </div>
    <?php endif; ?>

    <div class="p-6 rounded-xl mb-5" style="background:var(--c-surface);border:1px solid var(--c-border)">
      <!-- Status badges -->
      <div class="flex flex-wrap gap-2 mb-3">
        <span class="badge <?= $event['status']==='ongoing'?'badge-green':($event['status']==='completed'?'badge-gray':'badge-blue') ?>">
          <?php
          echo match($event['status']){
              'ongoing'  => 'भइरहेको',
              'completed'=> 'सम्पन्न',
              default    => 'आगामी'
          }; ?>
        </span>
        <?php if ($event['registration_open'] && $event['status'] !== 'completed'): ?>
          <span class="badge badge-yellow flex items-center gap-1"><?= icon('user-plus','w-2.5 h-2.5') ?> दर्ता खुला</span>
        <?php endif; ?>
      </div>

      <h1 class="text-2xl font-extrabold mb-4 leading-tight" style="color:var(--c-text)"><?= h($title) ?></h1>

      <!-- Event details grid -->
      <div class="event-meta-grid mb-5">
        <?php if ($event['start_datetime']): ?>
        <div class="item">
          <?= icon('calendar','w-4 h-4 flex-shrink-0','w-4 h-4 inline-block align-middle flex-shrink-0') ?>
          <div>
            <div class="font-semibold text-xs" style="color:var(--c-muted)">शुरु</div>
            <div><?= format_date($event['start_datetime'], true) ?></div>
          </div>
        </div>
        <?php endif; ?>
        <?php if ($event['end_datetime']): ?>
        <div class="item">
          <?= icon('calendar-check','w-4 h-4 flex-shrink-0','w-4 h-4 inline-block align-middle flex-shrink-0') ?>
          <div>
            <div class="font-semibold text-xs" style="color:var(--c-muted)">अन्त्य</div>
            <div><?= format_date($event['end_datetime'], true) ?></div>
          </div>
        </div>
        <?php endif; ?>
        <?php if ($venue): ?>
        <div class="item">
          <?= icon('map-pin','w-4 h-4 flex-shrink-0','w-4 h-4 inline-block align-middle flex-shrink-0') ?>
          <div>
            <div class="font-semibold text-xs" style="color:var(--c-muted)">स्थान</div>
            <div><?= h($venue) ?></div>
          </div>
        </div>
        <?php endif; ?>
        <?php if ($event['capacity']): ?>
        <div class="item">
          <?= icon('users','w-4 h-4 flex-shrink-0','w-4 h-4 inline-block align-middle flex-shrink-0') ?>
          <div>
            <div class="font-semibold text-xs" style="color:var(--c-muted)">क्षमता</div>
            <div><?= np_number((int)$event['capacity']) ?> सिट &bull; <?= np_number((int)$event['registrations_count']) ?> दर्ता</div>
          </div>
        </div>
        <?php endif; ?>
        <?php if ($event['registration_deadline']): ?>
        <div class="item">
          <?= icon('clock','w-4 h-4 flex-shrink-0','w-4 h-4 inline-block align-middle flex-shrink-0') ?>
          <div>
            <div class="font-semibold text-xs" style="color:var(--c-muted)">दर्ता अन्तिम</div>
            <div><?= format_date($event['registration_deadline'], true) ?></div>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Description -->
      <?php if ($desc): ?>
      <div class="article-content mb-5"><?= $desc ?></div>
      <?php endif; ?>

      <!-- Registration button -->
      <?php if ($event['registration_open'] && $event['status'] !== 'completed'): ?>
      <a href="/event/<?= h($event['slug']) ?>/register" class="btn btn-primary gap-2">
        <?= icon('user-plus','w-4 h-4') ?> दर्ता गर्नुस्
      </a>
      <?php endif; ?>
    </div>

    <!-- Gallery -->
    <?php if (!empty($event['gallery'])): ?>
    <div class="p-5 rounded-xl" style="background:var(--c-surface);border:1px solid var(--c-border)">
      <div class="section-heading mb-4"><span class="flex items-center gap-2"><?= icon('images','w-4 h-4') ?> फोटो ग्यालरी</span></div>
      <div class="gallery-grid" x-data="{light:null}">
        <?php foreach ($event['gallery'] as $media): ?>
          <?php if ($media['media_type']==='photo' && $media['file_path']): ?>
          <img src="<?= h($media['file_path']) ?>" alt="<?= h($media['caption']) ?>" loading="lazy"
               @click="light='<?= h($media['file_path']) ?>'">
          <?php elseif ($media['media_type']==='video' && $media['video_url']): ?>
          <div class="aspect-video rounded overflow-hidden">
            <iframe src="<?= h($media['video_url']) ?>" class="w-full h-full" allowfullscreen></iframe>
          </div>
          <?php endif; ?>
        <?php endforeach; ?>
        <!-- Lightbox -->
        <div class="lightbox-overlay" x-show="light" x-cloak @click="light=null" @keydown.escape.window="light=null">
          <img :src="light" alt="Gallery" @click.stop>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </article>

  <aside class="space-y-5">
    <?php render_ads('sidebar-top'); ?>
    <div class="sidebar-card">
      <div class="section-heading mb-3"><span class="flex items-center gap-2"><?= icon('calendar','w-4 h-4') ?> अन्य कार्यक्रम</span></div>
      <?php foreach (get_upcoming_events(5) as $ue): ?>
      <?php if ($ue['slug'] !== $event['slug']): ?>
      <a href="/event/<?= h($ue['slug']) ?>" class="event-widget-item block">
        <div class="event-widget-date flex-shrink-0">
          <?php if ($ue['start_datetime']): ?>
            <div class="day"><?= np_number((int)date('j',strtotime($ue['start_datetime']))) ?></div>
            <div><?= NP_MONTHS[(int)date('n',strtotime($ue['start_datetime']))] ?? '' ?></div>
          <?php endif; ?>
        </div>
        <div class="event-widget-title"><?= h($ue['title']) ?></div>
      </a>
      <?php endif; ?>
      <?php endforeach; ?>
      <a href="/events" class="btn btn-secondary w-full justify-center mt-3 gap-1">
        <?= icon('calendar-days','w-3.5 h-3.5') ?> सबै कार्यक्रम
      </a>
    </div>
  </aside>
</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
