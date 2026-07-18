<?php
$page_title = 'ई-पेपर — ' . site_name();
$page_desc  = 'नेपालको ताजा ई-पेपर — दैनिक अखबारको डिजिटल संस्करण।';

$year  = (int)($_GET['year']  ?? date('Y'));
$month = (int)($_GET['month'] ?? date('n'));
if ($month < 1 || $month > 12) $month = (int)date('n');
if ($year  < 2000 || $year > 2100) $year = (int)date('Y');

$epapers = get_epapers_by_month($year, $month);
$latest  = get_epapers(['limit' => 1]);
$recent  = get_epapers(['limit' => 12]);

require SRC_DIR . '/layout/header.php';
?>

<div class="max-w-5xl mx-auto">

  <!-- Page heading -->
  <div class="section-heading mb-6">
    <span class="flex items-center gap-2">
      <?= icon('newspaper', 'w-5 h-5') ?> ई-पेपर संग्रह
    </span>
  </div>

  <!-- Latest epaper featured -->
  <?php if ($latest): $ep = $latest[0]; ?>
  <div class="bg-white rounded-2xl border overflow-hidden mb-8 flex flex-col md:flex-row gap-0"
       style="border-color:var(--c-border);background:var(--c-surface)">
    <!-- Cover -->
    <div class="md:w-48 flex-shrink-0 bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
         style="min-height:200px">
      <?php if ($ep['cover_image']): ?>
        <img src="<?= h($ep['cover_image']) ?>" alt="ई-पेपर कभर"
             class="w-full h-full object-cover md:h-64">
      <?php else: ?>
        <div class="text-center p-6" style="color:var(--c-muted)">
          <?= icon('newspaper','w-12 h-12') ?>
        </div>
      <?php endif; ?>
    </div>
    <!-- Info -->
    <div class="p-6 flex flex-col justify-between flex-1">
      <div>
        <div class="text-xs font-semibold mb-1" style="color:var(--c-primary)">
          <?= icon('calendar','w-3 h-3') ?> आजको अंक
        </div>
        <h2 class="text-2xl font-extrabold mb-1" style="color:var(--c-text)">
          <?= h($ep['edition_date'] ? date('F j, Y', strtotime($ep['edition_date'])) : '') ?>
        </h2>
        <div class="text-sm mb-3" style="color:var(--c-muted)">
          <?= $ep['edition_date'] ? \BsDate::formatShort($ep['edition_date']) : '' ?>
        </div>
        <?php if ($ep['headline']): ?>
          <p class="text-sm mb-4" style="color:var(--c-text2)"><?= h($ep['headline']) ?></p>
        <?php endif; ?>
      </div>
      <?php if ($ep['pdf_path']): ?>
      <div class="flex gap-3 flex-wrap">
        <a href="<?= h($ep['pdf_path']) ?>" target="_blank"
           class="btn-primary inline-flex items-center gap-2 text-sm">
          <?= icon('download','w-4 h-4') ?> PDF डाउनलोड
        </a>
        <a href="<?= h($ep['pdf_path']) ?>" target="_blank"
           class="btn-outline inline-flex items-center gap-2 text-sm">
          <?= icon('eye','w-4 h-4') ?> अनलाइन पढ्नुहोस्
        </a>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Month/Year filter -->
  <form method="get" class="flex flex-wrap items-center gap-3 mb-6">
    <select name="year" class="form-input w-auto text-sm">
      <?php for ($y = (int)date('Y'); $y >= 2020; $y--): ?>
        <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
      <?php endfor; ?>
    </select>
    <select name="month" class="form-input w-auto text-sm">
      <?php
      $months_en = ['January','February','March','April','May','June',
                    'July','August','September','October','November','December'];
      for ($mn = 1; $mn <= 12; $mn++):
      ?>
        <option value="<?= $mn ?>" <?= $mn === $month ? 'selected' : '' ?>>
          <?= $months_en[$mn-1] ?>
        </option>
      <?php endfor; ?>
    </select>
    <button type="submit" class="btn-primary text-sm"><?= icon('search','w-4 h-4') ?> खोज्नुहोस्</button>
  </form>

  <!-- Archive grid -->
  <?php if ($epapers): ?>
  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mb-8">
    <?php foreach ($epapers as $ep): ?>
    <div class="bg-white rounded-xl border overflow-hidden group"
         style="border-color:var(--c-border);background:var(--c-surface)">
      <!-- Cover -->
      <div class="aspect-[3/4] overflow-hidden bg-gray-100 dark:bg-gray-800">
        <?php if ($ep['cover_image']): ?>
          <img src="<?= h($ep['cover_image']) ?>" alt=""
               class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
               loading="lazy">
        <?php else: ?>
          <div class="w-full h-full flex items-center justify-center" style="color:var(--c-muted)">
            <?= icon('file-text','w-8 h-8') ?>
          </div>
        <?php endif; ?>
      </div>
      <!-- Info -->
      <div class="p-3">
        <div class="text-xs font-semibold mb-0.5" style="color:var(--c-text)">
          <?= h($ep['edition_date'] ? date('M j', strtotime($ep['edition_date'])) : '') ?>
        </div>
        <div class="text-xs mb-2" style="color:var(--c-muted)">
          <?= $ep['edition_date'] ? \BsDate::formatShort($ep['edition_date']) : '' ?>
        </div>
        <?php if ($ep['pdf_path']): ?>
        <a href="<?= h($ep['pdf_path']) ?>" target="_blank"
           class="inline-flex items-center gap-1 text-xs font-semibold hover:underline"
           style="color:var(--c-primary)">
          <?= icon('download','w-3 h-3') ?> PDF
        </a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="text-center py-12" style="color:var(--c-muted)">
    <?= icon('inbox','w-12 h-12') ?>
    <p class="mt-3">यस महिनाको ई-पेपर उपलब्ध छैन।</p>
  </div>
  <?php endif; ?>

  <!-- Recent sidebar hint -->
  <?php if (!empty($recent)): ?>
  <div class="section-heading mb-4">
    <span class="flex items-center gap-2"><?= icon('archive','w-4 h-4') ?> हालसालका अंकहरू</span>
  </div>
  <div class="flex flex-wrap gap-2 mb-8">
    <?php foreach ($recent as $ep): ?>
    <?php if ($ep['pdf_path']): ?>
    <a href="<?= h($ep['pdf_path']) ?>" target="_blank"
       class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-full border hover:border-transparent transition-colors"
       style="border-color:var(--c-border);color:var(--c-text2)"
       title="<?= h($ep['headline'] ?? '') ?>">
      <?= icon('file-text','w-3 h-3') ?>
      <?= $ep['edition_date'] ? date('M j', strtotime($ep['edition_date'])) : 'ई-पेपर' ?>
    </a>
    <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
