<?php
$page_title = 'हाम्रोबारे — ' . site_name();
$page_desc  = 'नेपाल न्यूज पोर्टलको बारेमा जानकारी — हाम्रो उद्देश्य, टिम र सम्पर्क।';

require SRC_DIR . '/layout/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2">

    <!-- Hero -->
    <div class="p-6 rounded-xl mb-6" style="background:linear-gradient(135deg,var(--c-primary),#991B1B);color:#fff">
      <h1 class="text-2xl font-extrabold mb-2"><?= h(site_name()) ?></h1>
      <p class="text-sm opacity-90 leading-relaxed"><?= h(setting('site_tagline','नेपालको विश्वसनीय समाचार पोर्टल')) ?></p>
    </div>

    <!-- About content -->
    <div class="stat-card mb-6">
      <div class="section-heading mb-4">
        <span class="flex items-center gap-2"><?= icon('info','w-4 h-4') ?> हाम्रोबारे</span>
      </div>
      <div class="prose-nepali text-sm leading-7" style="color:var(--c-text2)">
        <?php $about = setting('footer_about',''); ?>
        <?php if ($about): ?>
          <p><?= nl2br(h($about)) ?></p>
        <?php else: ?>
          <p><?= h(site_name()) ?> नेपालको एक विश्वसनीय डिजिटल समाचार पोर्टल हो। हाम्रो लक्ष्य पाठकहरूलाई सटीक, निष्पक्ष र समयमै समाचार उपलब्ध गराउनु हो।</p>
          <p class="mt-3">हामी अर्थतन्त्र, राजनीति, खेलकुद, प्रविधि, र समाजसँग सम्बन्धित महत्त्वपूर्ण समाचारहरू कभर गर्छौं।</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Team — authors -->
    <?php $authors = get_authors(); ?>
    <?php if (!empty($authors)): ?>
    <div class="stat-card mb-6">
      <div class="section-heading mb-4">
        <span class="flex items-center gap-2"><?= icon('users','w-4 h-4') ?> हाम्रो टिम</span>
      </div>
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <?php foreach ($authors as $au): ?>
        <?php $cnt = db_count("SELECT COUNT(*) FROM articles WHERE author_id=? AND status='published'", [$au['id']]); ?>
        <a href="/author/<?= h($au['slug']) ?>" class="flex flex-col items-center gap-2 p-4 rounded-xl hover:shadow-md transition-all text-center group"
           style="background:var(--c-surface2);border:1px solid var(--c-border)">
          <?php if ($au['avatar_url']): ?>
          <img src="<?= h($au['avatar_url']) ?>" alt="<?= h($au['name']) ?>"
               class="w-16 h-16 rounded-full object-cover ring-2 ring-offset-2"
               style="ring-color:var(--c-border)">
          <?php else: ?>
          <div class="w-16 h-16 rounded-full flex items-center justify-center text-xl font-black text-white"
               style="background:var(--c-primary)">
            <?= mb_strtoupper(mb_substr($au['name'],0,1)) ?>
          </div>
          <?php endif; ?>
          <div>
            <div class="font-semibold text-sm group-hover:underline" style="color:var(--c-text)"><?= h($au['name_np']?:$au['name']) ?></div>
            <?php if ($au['name_np']): ?>
            <div class="text-xs" style="color:var(--c-muted)"><?= h($au['name']) ?></div>
            <?php endif; ?>
            <div class="text-xs mt-1 flex items-center gap-1 justify-center" style="color:var(--c-muted)">
              <?= icon('newspaper','w-3 h-3') ?> <?= np_number($cnt) ?> समाचार
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Contact info -->
    <div class="stat-card">
      <div class="section-heading mb-4">
        <span class="flex items-center gap-2"><?= icon('map-pin','w-4 h-4') ?> सम्पर्क विवरण</span>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <?php if (setting('contact_address','')): ?>
        <div class="flex items-start gap-3 p-3 rounded-lg" style="background:var(--c-surface2)">
          <?= icon('map-pin','w-4 h-4 mt-0.5 flex-shrink-0') ?>
          <div><?= nl2br(h(setting('contact_address',''))) ?></div>
        </div>
        <?php endif; ?>
        <?php if (setting('contact_phone','')): ?>
        <div class="flex items-center gap-3 p-3 rounded-lg" style="background:var(--c-surface2)">
          <?= icon('phone','w-4 h-4 flex-shrink-0') ?>
          <a href="tel:<?= h(setting('contact_phone','')) ?>" class="hover:underline"><?= h(setting('contact_phone','')) ?></a>
        </div>
        <?php endif; ?>
        <?php if (setting('contact_email','')): ?>
        <div class="flex items-center gap-3 p-3 rounded-lg" style="background:var(--c-surface2)">
          <?= icon('mail','w-4 h-4 flex-shrink-0') ?>
          <a href="mailto:<?= h(setting('contact_email','')) ?>" class="hover:underline"><?= h(setting('contact_email','')) ?></a>
        </div>
        <?php endif; ?>
        <div class="flex items-center gap-3 p-3 rounded-lg" style="background:var(--c-surface2)">
          <?= icon('message-circle','w-4 h-4 flex-shrink-0') ?>
          <a href="/contact" class="hover:underline btn btn-primary btn-sm">सन्देश पठाउनुस्</a>
        </div>
      </div>
    </div>

  </div>

  <!-- Sidebar -->
  <aside class="space-y-5">
    <?php render_ads('sidebar-top'); ?>
    <div class="sidebar-card">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('flame','w-4 h-4') ?> सर्वाधिक पढिएका</span>
      </div>
      <?php foreach (get_popular_articles(5) as $i => $pop): ?>
      <div class="popular-item">
        <span class="popular-num"><?= $i+1 ?></span>
        <div><a href="/article/<?= h($pop['slug']) ?>" class="ptitle hover:underline"><?= h($pop['title']) ?></a></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Quick links -->
    <div class="sidebar-card">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('link','w-4 h-4') ?> उपयोगी लिंकहरू</span>
      </div>
      <div class="space-y-1.5">
        <?php $qlinks = [['trending','ट्रेन्डिङ'],['breaking','ब्रेकिङ'],['contact','सम्पर्क'],['epaper','ई-पेपर'],['events','कार्यक्रम']]; ?>
        <?php foreach ($qlinks as [$path,$label]): ?>
        <a href="/<?= $path ?>" class="footer-link flex items-center gap-2">
          <?= icon('chevron-right','w-3 h-3') ?> <?= $label ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </aside>
</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
