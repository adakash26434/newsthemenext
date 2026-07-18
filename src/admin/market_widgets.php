<?php
admin_check();
$widgets = get_market_widgets();
// Group by type
$by_type = [];
foreach ($widgets as $w) $by_type[$w['widget_type']][] = $w;

$types = [
    'forex'     => ['label' => 'विदेशी मुद्रा (Forex)', 'icon' => 'dollar-sign'],
    'gold'      => ['label' => 'सुन/चाँदी दर',          'icon' => 'gem'],
    'nepse'     => ['label' => 'नेप्से सूचकाङ्क',       'icon' => 'trending-up'],
    'fuel'      => ['label' => 'इन्धन मूल्य',           'icon' => 'fuel'],
    'interest'  => ['label' => 'ब्याजदर',               'icon' => 'percent'],
];

admin_html_start('बजार तथ्याङ्क');
admin_sidebar('market');
?>
<div class="admin-content">
<?php admin_topbar('बजार तथ्याङ्क व्यवस्थापन'); ?>
<div class="p-6">

<div class="flex items-center justify-between mb-6">
  <h1 class="admin-page-title"><?= icon('bar-chart-2','w-6 h-6') ?> बजार तथ्याङ्क व्यवस्थापन</h1>
</div>

<?php if (($msg = flash_get('success'))): ?>
  <div class="alert alert-success mb-4"><?= h($msg) ?></div>
<?php endif; ?>

<!-- Quick Update Form -->
<div class="grid gap-6 md:grid-cols-2">
  <?php foreach ($types as $type => $info): ?>
  <div class="admin-card">
    <div class="admin-card-header">
      <?= icon($info['icon'], 'w-4 h-4') ?> <?= $info['label'] ?>
      <span class="ml-auto text-xs" style="color:var(--c-muted)">होमपेजमा देखिन्छ</span>
    </div>
    <div class="admin-card-body">

      <!-- Existing rows for this type -->
      <?php if (!empty($by_type[$type])): ?>
      <div class="mb-4 divide-y" style="border-color:var(--c-border)">
        <?php foreach ($by_type[$type] as $w): ?>
        <form method="post" action="/admin/market/save" class="flex items-center gap-2 py-2">
          <?= csrf_field() ?>
          <input type="hidden" name="id"          value="<?= (int)$w['id'] ?>">
          <input type="hidden" name="widget_type" value="<?= h($type) ?>">
          <input type="text"   name="label"       value="<?= h($w['label']) ?>"
                 class="form-input text-sm flex-1" placeholder="नाम (जस्तो: USD/NPR)">
          <input type="text"   name="value"       value="<?= h($w['value']) ?>"
                 class="form-input text-sm w-28" placeholder="मान">
          <input type="text"   name="change_pct"  value="<?= h($w['change_pct'] ?? '') ?>"
                 class="form-input text-sm w-20" placeholder="±%">
          <button type="submit" class="btn-primary text-xs py-1.5 px-3">
            <?= icon('save','w-3 h-3') ?>
          </button>
          <form method="post" action="/admin/market/delete" class="inline"
                onsubmit="return confirm('मेटाउने?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$w['id'] ?>">
            <button type="submit" class="admin-action-btn text-red-600">
              <?= icon('trash-2','w-3 h-3') ?>
            </button>
          </form>
        </form>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Add new row -->
      <form method="post" action="/admin/market/save" class="flex items-center gap-2">
        <?= csrf_field() ?>
        <input type="hidden" name="widget_type" value="<?= h($type) ?>">
        <input type="text"   name="label"       class="form-input text-sm flex-1" placeholder="नाम थप्नुहोस्">
        <input type="text"   name="value"       class="form-input text-sm w-28"   placeholder="मान">
        <input type="text"   name="change_pct"  class="form-input text-sm w-20"   placeholder="±%">
        <button type="submit" class="btn-primary text-xs py-1.5 px-3">
          <?= icon('plus','w-3 h-3') ?>
        </button>
      </form>
      <p class="text-xs mt-2" style="color:var(--c-muted)">
        परिवर्तन प्रतिशत: सकारात्मक (+2.5) वा नकारात्मक (-1.3) दुवै
      </p>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Homepage Preview Note -->
<div class="admin-card mt-6">
  <div class="admin-card-body text-sm" style="color:var(--c-muted)">
    <?= icon('info','w-4 h-4') ?>
    यी तथ्याङ्कहरू होमपेजको साइडबार र हेडरमा ticker रूपमा देखिन्छन्।
    मान अद्यावधिक गर्दा तुरुन्तै साइटमा लागू हुन्छ।
  </div>
</div>

<?php admin_html_end(); ?>
