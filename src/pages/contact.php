<?php
$page_title = 'सम्पर्क — ' . site_name();
$page_desc  = 'नेपाल न्यूज पोर्टलसँग सम्पर्क गर्नुस् — ठेगाना, फोन, र इमेल।';

$sent = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $honey   = $_POST['website'] ?? ''; // honeypot

    if ($honey) { $sent = true; // silently drop
    } elseif (!$name || !$email || !$message) {
        $error = 'नाम, इमेल र सन्देश अनिवार्य छ।';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'मान्य इमेल ठेगाना राख्नुस्।';
    } else {
        // Save to DB (using comments table as a contact log if exists, or just log)
        // We send email if mail() is configured
        $to = setting('contact_email', '');
        if ($to) {
            $headers  = "From: " . $name . " <" . $email . ">\r\n";
            $headers .= "Reply-To: " . $email . "\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            @mail($to, '[सम्पर्क] ' . ($subject ?: 'नयाँ सन्देश'), $message . "\n\n— " . $name . " (" . $email . ")", $headers);
        }
        $sent = true;
    }
}

require SRC_DIR . '/layout/header.php';
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2">
    <div class="section-heading mb-5">
      <span class="flex items-center gap-2"><?= icon('mail','w-4 h-4') ?> सम्पर्क गर्नुस्</span>
    </div>

    <?php if ($sent): ?>
    <div class="p-6 rounded-xl text-center mb-6" style="background:rgba(22,163,74,.08);border:1px solid rgba(22,163,74,.3)">
      <?= icon('circle-check','w-10 h-10 mx-auto mb-3','w-10 h-10 inline-block align-middle') ?>
      <h2 class="text-base font-bold mb-1" style="color:#16A34A">सन्देश पठाइयो!</h2>
      <p class="text-sm" style="color:var(--c-text2)">हामी चाँडै तपाईंसँग सम्पर्क गर्नेछौं।</p>
      <a href="/contact" class="btn btn-primary mt-4 inline-flex gap-2"><?= icon('refresh-cw','w-4 h-4') ?> फेरि पठाउनुस्</a>
    </div>
    <?php else: ?>

    <?php if ($error): ?>
    <div class="p-3 mb-4 rounded-lg text-sm" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#DC2626"><?= h($error) ?></div>
    <?php endif; ?>

    <div class="stat-card mb-6">
      <form method="POST" class="space-y-4">
        <?= csrf_field() ?>
        <!-- Honeypot -->
        <div style="display:none"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="admin-label">तपाईंको नाम *</label>
            <input type="text" name="name" required placeholder="पूरा नाम"
                   value="<?= h($_POST['name'] ?? '') ?>"
                   class="admin-input w-full">
          </div>
          <div>
            <label class="admin-label">इमेल ठेगाना *</label>
            <input type="email" name="email" required placeholder="email@example.com"
                   value="<?= h($_POST['email'] ?? '') ?>"
                   class="admin-input w-full">
          </div>
        </div>

        <div>
          <label class="admin-label">विषय</label>
          <input type="text" name="subject" placeholder="सन्देशको विषय"
                 value="<?= h($_POST['subject'] ?? '') ?>"
                 class="admin-input w-full">
        </div>

        <div>
          <label class="admin-label">सन्देश *</label>
          <textarea name="message" required rows="6"
                    placeholder="तपाईंको सन्देश यहाँ लेख्नुस्..."
                    class="admin-input w-full resize-y"><?= h($_POST['message'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary flex items-center gap-2">
          <?= icon('send','w-4 h-4') ?> सन्देश पठाउनुस्
        </button>
      </form>
    </div>
    <?php endif; ?>
  </div>

  <!-- Sidebar — contact info -->
  <aside class="space-y-5">
    <?php render_ads('sidebar-top'); ?>

    <!-- Contact info card -->
    <div class="sidebar-card">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('map-pin','w-4 h-4') ?> सम्पर्क विवरण</span>
      </div>
      <div class="space-y-3 text-sm">
        <?php if (setting('contact_address','')): ?>
        <div class="flex items-start gap-2">
          <?= icon('map-pin','w-4 h-4 mt-0.5 flex-shrink-0') ?>
          <span><?= h(setting('contact_address','')) ?></span>
        </div>
        <?php endif; ?>
        <?php if (setting('contact_phone','')): ?>
        <div class="flex items-center gap-2">
          <?= icon('phone','w-4 h-4 flex-shrink-0') ?>
          <a href="tel:<?= h(setting('contact_phone','')) ?>" class="hover:underline">
            <?= h(setting('contact_phone','')) ?>
          </a>
        </div>
        <?php endif; ?>
        <?php if (setting('contact_email','')): ?>
        <div class="flex items-center gap-2">
          <?= icon('mail','w-4 h-4 flex-shrink-0') ?>
          <a href="mailto:<?= h(setting('contact_email','')) ?>" class="hover:underline">
            <?= h(setting('contact_email','')) ?>
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Social links -->
    <?php $socials = [
      ['social_facebook','facebook','Facebook',  'text-blue-600'],
      ['social_twitter','twitter','Twitter / X', 'text-sky-500'],
      ['social_youtube','youtube','YouTube',     'text-red-600'],
      ['social_instagram','instagram','Instagram','text-pink-600'],
    ]; ?>
    <?php $has_social = array_filter($socials, fn($s) => setting($s[0],'')); ?>
    <?php if (!empty($has_social)): ?>
    <div class="sidebar-card">
      <div class="section-heading mb-3">
        <span class="flex items-center gap-2"><?= icon('share-2','w-4 h-4') ?> सामाजिक सञ्जाल</span>
      </div>
      <div class="flex flex-wrap gap-2">
        <?php foreach ($socials as [$key,$ic,$label,$cls]): ?>
        <?php if (setting($key,'')): ?>
        <a href="<?= h(setting($key,'')) ?>" target="_blank" rel="noopener noreferrer"
           class="btn btn-sm flex items-center gap-1.5">
          <?= icon($ic,'w-3.5 h-3.5') ?> <?= $label ?>
        </a>
        <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Popular news -->
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
  </aside>
</div>

<?php require SRC_DIR . '/layout/footer.php'; ?>
