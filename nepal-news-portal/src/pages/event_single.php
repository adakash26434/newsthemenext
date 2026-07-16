<?php
$event = get_event_by_slug($_slug ?? '');
if (!$event) { http_response_code(404); require SRC_DIR . '/pages/404.php'; exit; }

$lang       = current_lang();
$ev_title   = $lang==='en' ? ($event['title_en'] ?: $event['title']) : $event['title'];
$ev_desc    = $lang==='en' ? ($event['description_en'] ?: $event['description']) : $event['description'];
$ev_venue   = $lang==='en' ? ($event['venue_en'] ?: $event['venue']) : $event['venue'];
$page_title = $ev_title . ' — ' . site_name();
$page_desc  = excerpt($ev_desc, 25);
$og_image   = $event['cover_image'] ?? '';

// Registration info
$reg_count    = db_count("SELECT COUNT(*) FROM event_registrations WHERE event_id=? AND status!='cancelled'", [$event['id']]);
$cap_left     = $event['capacity'] ? max(0, $event['capacity'] - $reg_count) : null;
$reg_open     = $event['registration_open'] && $event['status'] !== 'completed' && $event['status'] !== 'cancelled';
if ($event['registration_deadline']) $reg_open = $reg_open && strtotime($event['registration_deadline']) > time();
if ($cap_left !== null) $reg_open = $reg_open && $cap_left > 0;

require SRC_DIR . '/layout/header.php';
?>

<!-- Breadcrumb -->
<nav class="breadcrumb mb-4" aria-label="Breadcrumb">
  <a href="/"><?= lang_label('गृहपृष्ठ','Home') ?></a>
  <span>›</span>
  <a href="/events"><?= lang_label('कार्यक्रम','Events') ?></a>
  <span>›</span>
  <span><?= h($ev_title) ?></span>
</nav>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
  <!-- Main -->
  <div class="lg:col-span-2">

    <!-- Cover image -->
    <?php if ($event['cover_image']): ?>
    <div class="mb-5 rounded-lg overflow-hidden" style="max-height:420px">
      <img src="<?= h($event['cover_image']) ?>" alt="<?= h($ev_title) ?>"
           class="w-full object-cover" style="max-height:420px">
    </div>
    <?php endif; ?>

    <!-- Status + badge -->
    <div class="flex items-center gap-3 mb-4 flex-wrap">
      <span class="event-status-badge-lg event-status-<?= h($event['status']) ?>">
        <?= match($event['status']) {
          'upcoming'=>lang_label('आगामी','Upcoming'),'ongoing'=>lang_label('जारी','Ongoing'),
          'completed'=>lang_label('समाप्त','Completed'),'cancelled'=>lang_label('रद्द','Cancelled'),
          default=>h($event['status'])
        } ?>
      </span>
      <?php if ($reg_open): ?>
        <span class="badge badge-green"><?= lang_label('दर्ता खुला','Registration Open') ?></span>
      <?php endif; ?>
    </div>

    <h1 class="text-2xl sm:text-3xl font-extrabold mb-4 leading-tight"><?= h($ev_title) ?></h1>

    <!-- Meta info -->
    <div class="event-meta-grid mb-6">
      <?php if ($event['start_datetime']): ?>
      <div class="event-meta-item">
        <span class="event-meta-icon">📅</span>
        <div>
          <div class="text-xs" style="color:var(--c-muted)"><?= lang_label('मिति','Date') ?></div>
          <div class="font-semibold text-sm"><?= format_date($event['start_datetime'], true) ?>
            <?php if ($event['end_datetime']): ?> — <?= format_date($event['end_datetime'], true) ?><?php endif; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
      <?php if ($ev_venue): ?>
      <div class="event-meta-item">
        <span class="event-meta-icon">📍</span>
        <div>
          <div class="text-xs" style="color:var(--c-muted)"><?= lang_label('स्थान','Venue') ?></div>
          <div class="font-semibold text-sm"><?= h($ev_venue) ?></div>
        </div>
      </div>
      <?php endif; ?>
      <?php if ($event['capacity']): ?>
      <div class="event-meta-item">
        <span class="event-meta-icon">👥</span>
        <div>
          <div class="text-xs" style="color:var(--c-muted)"><?= lang_label('क्षमता','Capacity') ?></div>
          <div class="font-semibold text-sm">
            <?= np_number($reg_count) ?> / <?= np_number($event['capacity']) ?>
            <?= lang_label('दर्ता','Registered') ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
      <?php if ($event['registration_deadline']): ?>
      <div class="event-meta-item">
        <span class="event-meta-icon">⏰</span>
        <div>
          <div class="text-xs" style="color:var(--c-muted)"><?= lang_label('दर्ता अन्तिम मिति','Reg. Deadline') ?></div>
          <div class="font-semibold text-sm"><?= format_date($event['registration_deadline']) ?></div>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Description -->
    <?php if ($ev_desc): ?>
    <div class="prose-content mb-6">
      <?= nl2br(h($ev_desc)) ?>
    </div>
    <?php endif; ?>

    <!-- Gallery -->
    <?php if (!empty($event['gallery'])): ?>
    <div class="mb-8" x-data="{lightbox: null, items: <?= json_encode(array_filter($event['gallery'], fn($m)=>$m['media_type']==='photo' && $m['file_path'])) ?>}">
      <h2 class="section-heading mb-4"><span><?= lang_label('फोटो Gallery','Photo Gallery') ?></span></h2>
      <div class="gallery-grid">
        <?php $photos = array_values(array_filter($event['gallery'], fn($m)=>$m['media_type']==='photo'&&$m['file_path'])); ?>
        <?php $videos = array_values(array_filter($event['gallery'], fn($m)=>$m['media_type']==='video'&&$m['video_url'])); ?>
        <?php foreach ($photos as $i => $m): ?>
        <div class="gallery-thumb" @click="lightbox=<?= $i ?>">
          <img src="<?= h($m['file_path']) ?>" alt="<?= h($m['caption']) ?>" loading="lazy">
          <div class="gallery-thumb-overlay"><span>🔍</span></div>
        </div>
        <?php endforeach; ?>
      </div>
      <!-- Videos -->
      <?php if (!empty($videos)): ?>
      <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <?php foreach ($videos as $v): ?>
        <div style="aspect-ratio:16/9;border-radius:8px;overflow:hidden">
          <iframe src="<?= h($v['video_url']) ?>" class="w-full h-full" style="border:none" allowfullscreen></iframe>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <!-- Lightbox -->
      <div class="lightbox-overlay" x-show="lightbox !== null" x-cloak @click.self="lightbox=null" @keydown.escape.window="lightbox=null">
        <button class="lightbox-close" @click="lightbox=null">✕</button>
        <button class="lightbox-prev" @click="lightbox=(lightbox-1+items.length)%items.length" x-show="items.length>1">‹</button>
        <div class="lightbox-inner">
          <template x-if="lightbox !== null && items[lightbox]">
            <img :src="items[lightbox].file_path" :alt="items[lightbox].caption||''" class="lightbox-img">
          </template>
          <template x-if="lightbox !== null && items[lightbox] && items[lightbox].caption">
            <p class="lightbox-caption" x-text="items[lightbox].caption"></p>
          </template>
        </div>
        <button class="lightbox-next" @click="lightbox=(lightbox+1)%items.length" x-show="items.length>1">›</button>
      </div>
    </div>
    <?php endif; ?>

    <!-- Share -->
    <div class="share-bar mt-4 mb-4">
      <span class="share-label"><?= lang_label('शेयर गर्नुस्','Share') ?></span>
      <?php $share_url = urlencode((isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']==='on'?'https':'http').'://'.$_SERVER['HTTP_HOST'].'/event/'.h($event['slug'])); $share_title = urlencode($ev_title); ?>
      <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $share_url ?>" target="_blank" class="share-btn share-fb" rel="noopener">Facebook</a>
      <a href="https://twitter.com/intent/tweet?text=<?= $share_title ?>&url=<?= $share_url ?>" target="_blank" class="share-btn share-tw" rel="noopener">X (Twitter)</a>
      <a href="https://wa.me/?text=<?= $share_title ?>%20<?= $share_url ?>" target="_blank" class="share-btn share-wa" rel="noopener">WhatsApp</a>
    </div>

  </div>

  <!-- Sidebar -->
  <div class="space-y-5">

    <!-- Registration form -->
    <?php if ($reg_open): ?>
    <div class="event-reg-box"
         x-data="{
           form: {name:'',email:'',phone:'',organization:'',message:''},
           submitted: false,
           loading: false,
           error: '',
           success: '',
           async submit() {
             this.loading = true; this.error = ''; this.success = '';
             const fd = new FormData();
             fd.append('csrf_token', document.querySelector('[name=csrf_token]')?.value||'');
             Object.entries(this.form).forEach(([k,v])=>fd.append(k,v));
             try {
               const r = await fetch('/event/<?= h($event['slug']) ?>/register',{method:'POST',body:fd});
               const j = await r.json();
               if(j.success){ this.success=j.message||'दर्ता सफल भयो!'; this.submitted=true; }
               else { this.error=j.message||'केही गडबडी भयो।'; }
             } catch(e){ this.error='Network error. पुनः प्रयास गर्नुस्।'; }
             this.loading=false;
           }
         }">
      <div x-show="!submitted">
        <h3 class="font-extrabold text-base mb-4">📝 <?= lang_label('दर्ता गर्नुस्','Register Now') ?></h3>
        <?= csrf_field() ?>
        <div x-show="error" class="flash flash-error" x-text="error"></div>
        <div class="form-group">
          <label class="form-label"><?= lang_label('पूरा नाम','Full Name') ?> *</label>
          <input type="text" class="form-control" x-model="form.name" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email *</label>
          <input type="email" class="form-control" x-model="form.email" required>
        </div>
        <div class="form-group">
          <label class="form-label"><?= lang_label('फोन नम्बर','Phone') ?></label>
          <input type="tel" class="form-control" x-model="form.phone">
        </div>
        <div class="form-group">
          <label class="form-label"><?= lang_label('संस्था/कम्पनी','Organization') ?></label>
          <input type="text" class="form-control" x-model="form.organization">
        </div>
        <div class="form-group">
          <label class="form-label"><?= lang_label('सन्देश','Message') ?></label>
          <textarea class="form-control" rows="3" x-model="form.message" placeholder="Optional..."></textarea>
        </div>
        <button @click="submit()" :disabled="loading" class="btn btn-primary w-full justify-center mt-2" type="button">
          <span x-show="!loading"><?= lang_label('दर्ता पठाउनुस्','Submit Registration') ?> →</span>
          <span x-show="loading" x-cloak>⏳ <?= lang_label('पठाउँदैछ...','Sending...') ?></span>
        </button>
      </div>
      <div x-show="submitted" x-cloak class="text-center py-6">
        <div class="text-4xl mb-3">✅</div>
        <p class="font-bold text-base mb-1" x-text="success"></p>
        <p class="text-sm" style="color:var(--c-muted)"><?= lang_label('तपाईंको इमेलमा पुष्टि पठाइनेछ।','Confirmation will be sent to your email.') ?></p>
      </div>
    </div>
    <?php else: ?>
    <div class="event-reg-box" style="border-color:var(--c-border2)">
      <p class="text-center text-sm font-semibold" style="color:var(--c-muted)">
        <?php if ($event['status'] === 'completed'): ?>
          <?= lang_label('यो कार्यक्रम समाप्त भइसक्यो।','This event has ended.') ?>
        <?php elseif ($cap_left === 0): ?>
          <?= lang_label('सिट सबै भरिसक्यो।','All seats are filled.') ?>
        <?php else: ?>
          <?= lang_label('दर्ता बन्द छ।','Registration is closed.') ?>
        <?php endif; ?>
      </p>
    </div>
    <?php endif; ?>

    <!-- Event quick info card -->
    <div class="stat-card">
      <h3 class="font-bold text-sm mb-3">📋 <?= lang_label('कार्यक्रम विवरण','Event Details') ?></h3>
      <dl class="space-y-2 text-sm">
        <?php if ($event['start_datetime']): ?>
        <div>
          <dt class="font-medium" style="color:var(--c-muted)"><?= lang_label('मिति','Date') ?></dt>
          <dd><?= format_date($event['start_datetime'], true) ?></dd>
        </div>
        <?php endif; ?>
        <?php if ($ev_venue): ?>
        <div>
          <dt class="font-medium" style="color:var(--c-muted)"><?= lang_label('स्थान','Venue') ?></dt>
          <dd><?= h($ev_venue) ?></dd>
        </div>
        <?php endif; ?>
        <div>
          <dt class="font-medium" style="color:var(--c-muted)"><?= lang_label('स्थिति','Status') ?></dt>
          <dd><span class="event-status-badge event-status-<?= h($event['status']) ?>"><?= lang_label('आगामी','Upcoming') ?></span></dd>
        </div>
        <?php if ($event['capacity']): ?>
        <div>
          <dt class="font-medium" style="color:var(--c-muted)"><?= lang_label('दर्ता','Registrations') ?></dt>
          <dd><?= np_number($reg_count) ?> / <?= np_number($event['capacity']) ?></dd>
        </div>
        <?php endif; ?>
      </dl>
    </div>

    <!-- Ads -->
    <?php render_ads('sidebar-top'); ?>
  </div>
</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
