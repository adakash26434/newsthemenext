<?php
admin_check();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $keys = [
        'site_name','site_name_en','site_tagline','site_logo_url','site_logo_text',
        'primary_color','nav_color','accent_color',
        'ticker_label','default_lang','registration_no','founded_year','copyright_text',
        'social_facebook','social_twitter','social_youtube','social_instagram','social_tiktok',
        'contact_email','contact_phone','contact_address',
        'footer_about','footer_about_en',
        'meta_keywords','google_analytics',
        'youtube_channel','youtube_embed',
        'announcement_text','announcement_url','announcement_type',
    ];
    foreach ($keys as $k) {
        save_setting($k, trim($_POST[$k] ?? ''));
    }

    // Logo upload
    $logo = handle_upload('logo_file', 'brand');
    if ($logo) save_setting('site_logo_url', $logo);

    // Favicon upload
    $fav = handle_upload('favicon_file', 'brand');
    if ($fav) save_setting('favicon_url', $fav);

    // Password change
    $new_user = trim($_POST['admin_username'] ?? '');
    $new_pass = $_POST['admin_password_new'] ?? '';
    $cur_pass = $_POST['admin_password_cur'] ?? '';
    if ($new_user && $new_pass && admin_login($new_user, $cur_pass)) {
        save_setting('admin_username', $new_user);
        save_setting('admin_password', password_hash($new_pass, PASSWORD_DEFAULT));
        flash_set('success', 'Admin credentials updated.');
    } elseif ($new_user && $new_user !== setting('admin_username')) {
        save_setting('admin_username', $new_user);
    }

    // Clear settings cache
    flash_set('success', 'सेटिङ्स सेभ गरियो।');
    redirect('admin/settings');
}

admin_html_start('सेटिङ्स');
admin_sidebar('settings');
?>
<div class="admin-content">
<?php admin_topbar('सेटिङ्स'); ?>
<div class="p-6">
<?php admin_flash(); ?>

<form method="POST" action="/admin/settings" enctype="multipart/form-data">
<?= csrf_field() ?>

<!-- Site Identity -->
<div class="stat-card mb-5">
  <h2 class="font-bold text-sm mb-4 flex items-center gap-2"><i data-lucide="globe" class="w-4 h-4"></i> साइट परिचय</h2>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="form-label">साइट नाम (नेपाली)</label>
      <input type="text" name="site_name" class="form-control" value="<?= h(setting('site_name')) ?>">
    </div>
    <div>
      <label class="form-label">Site Name (English)</label>
      <input type="text" name="site_name_en" class="form-control" value="<?= h(setting('site_name_en')) ?>">
    </div>
    <div>
      <label class="form-label">ट्यागलाइन</label>
      <input type="text" name="site_tagline" class="form-control" value="<?= h(setting('site_tagline')) ?>">
    </div>
    <div>
      <label class="form-label">Logo Text</label>
      <input type="text" name="site_logo_text" class="form-control" value="<?= h(setting('site_logo_text')) ?>">
    </div>
    <div>
      <label class="form-label">Logo Upload</label>
      <?php if (setting('site_logo_url')): ?><img src="<?= h(setting('site_logo_url')) ?>" alt="" class="h-10 mb-1 rounded"><?php endif; ?>
      <input type="file" name="logo_file" class="form-control" accept="image/*">
    </div>
    <div>
      <label class="form-label">Logo URL (manual)</label>
      <input type="url" name="site_logo_url" class="form-control" value="<?= h(setting('site_logo_url')) ?>" placeholder="https://...">
    </div>
    <div>
      <label class="form-label">Favicon Upload (SVG/PNG)</label>
      <input type="file" name="favicon_file" class="form-control" accept="image/*,.svg">
    </div>
    <div>
      <label class="form-label">Default Language</label>
      <select name="default_lang" class="form-control">
        <option value="np" <?= setting('default_lang')==='np'?'selected':'' ?>>नेपाली (NP)</option>
        <option value="en" <?= setting('default_lang')==='en'?'selected':'' ?>>English (EN)</option>
      </select>
    </div>
    <div>
      <label class="form-label">दर्ता नं</label>
      <input type="text" name="registration_no" class="form-control" value="<?= h(setting('registration_no')) ?>">
    </div>
    <div>
      <label class="form-label">स्थापना वर्ष</label>
      <input type="text" name="founded_year" class="form-control" value="<?= h(setting('founded_year')) ?>" placeholder="२०७५">
    </div>
    <div class="md:col-span-2">
      <label class="form-label">Copyright Text</label>
      <input type="text" name="copyright_text" class="form-control" value="<?= h(setting('copyright_text')) ?>">
    </div>
  </div>
</div>

<!-- Announcement Banner -->
<div class="stat-card mb-5">
  <h2 class="font-bold text-sm mb-4 flex items-center gap-2"><i data-lucide="megaphone" class="w-4 h-4"></i> साइटव्यापी घोषणा ब्यानर</h2>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
      <label class="form-label">घोषणा सन्देश <span class="text-xs" style="color:var(--c-muted)">(खाली राख्नुभयो भने ब्यानर देखिँदैन)</span></label>
      <input type="text" name="announcement_text" class="form-control" value="<?= h(setting('announcement_text')) ?>" placeholder="महत्त्वपूर्ण सूचना वा घोषणा...">
    </div>
    <div>
      <label class="form-label">Link URL (वैकल्पिक)</label>
      <input type="url" name="announcement_url" class="form-control" value="<?= h(setting('announcement_url')) ?>" placeholder="https://...">
    </div>
    <div>
      <label class="form-label">प्रकार</label>
      <select name="announcement_type" class="form-control">
        <?php foreach (['info'=>'Info (नीलो)','warning'=>'Warning (पहेँलो)','success'=>'Success (हरियो)','danger'=>'Danger (रातो)'] as $v=>$l): ?>
        <option value="<?= $v ?>" <?= setting('announcement_type')===$v?'selected':'' ?>><?= h($l) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
</div>

<!-- Theme Colors -->
<div class="stat-card mb-5">
  <h2 class="font-bold text-sm mb-4 flex items-center gap-2"><i data-lucide="palette" class="w-4 h-4"></i> थिम रंग</h2>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
      <label class="form-label">Primary Color</label>
      <div class="flex gap-2 items-center">
        <input type="color" name="primary_color" class="h-10 w-16 p-1 rounded border cursor-pointer" style="border-color:var(--c-border)" value="<?= h(setting('primary_color','#7F1D1D')) ?>">
        <input type="text" name="primary_color" class="form-control flex-1" value="<?= h(setting('primary_color','#7F1D1D')) ?>">
      </div>
    </div>
    <div>
      <label class="form-label">Navigation Color</label>
      <div class="flex gap-2 items-center">
        <input type="color" class="h-10 w-16 p-1 rounded border cursor-pointer" style="border-color:var(--c-border)" value="<?= h(setting('nav_color','#7F1D1D')) ?>">
        <input type="text" name="nav_color" class="form-control flex-1" value="<?= h(setting('nav_color','#7F1D1D')) ?>">
      </div>
    </div>
    <div>
      <label class="form-label">Accent Color</label>
      <div class="flex gap-2 items-center">
        <input type="color" class="h-10 w-16 p-1 rounded border cursor-pointer" style="border-color:var(--c-border)" value="<?= h(setting('accent_color','#991B1B')) ?>">
        <input type="text" name="accent_color" class="form-control flex-1" value="<?= h(setting('accent_color','#991B1B')) ?>">
      </div>
    </div>
  </div>
</div>

<!-- Social Media -->
<div class="stat-card mb-5">
  <h2 class="font-bold text-sm mb-4 flex items-center gap-2"><i data-lucide="share-2" class="w-4 h-4"></i> सामाजिक सञ्जाल</h2>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <?php $socials = [['social_facebook','facebook','Facebook URL'],['social_twitter','twitter','Twitter/X URL'],['social_youtube','youtube','YouTube Channel URL'],['social_instagram','instagram','Instagram URL'],['social_tiktok','zap','TikTok URL']]; ?>
    <?php foreach ($socials as [$k,$ic,$label]): ?>
    <div>
      <label class="form-label flex items-center gap-1"><i data-lucide="<?= h($ic) ?>" class="w-3.5 h-3.5"></i> <?= h($label) ?></label>
      <input type="url" name="<?= h($k) ?>" class="form-control" value="<?= h(setting($k)) ?>" placeholder="https://...">
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Contact -->
<div class="stat-card mb-5">
  <h2 class="font-bold text-sm mb-4 flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4"></i> सम्पर्क विवरण</h2>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="form-label flex items-center gap-1"><i data-lucide="mail" class="w-3.5 h-3.5"></i> इमेल</label>
      <input type="email" name="contact_email" class="form-control" value="<?= h(setting('contact_email')) ?>">
    </div>
    <div>
      <label class="form-label flex items-center gap-1"><i data-lucide="phone" class="w-3.5 h-3.5"></i> फोन</label>
      <input type="text" name="contact_phone" class="form-control" value="<?= h(setting('contact_phone')) ?>">
    </div>
    <div class="md:col-span-2">
      <label class="form-label flex items-center gap-1"><i data-lucide="map-pin" class="w-3.5 h-3.5"></i> ठेगाना</label>
      <input type="text" name="contact_address" class="form-control" value="<?= h(setting('contact_address')) ?>">
    </div>
  </div>
</div>

<!-- Footer / About -->
<div class="stat-card mb-5">
  <h2 class="font-bold text-sm mb-4 flex items-center gap-2"><i data-lucide="align-left" class="w-4 h-4"></i> Footer About</h2>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="form-label">हाम्रो बारेमा (NP)</label>
      <textarea name="footer_about" class="form-control" rows="3"><?= h(setting('footer_about')) ?></textarea>
    </div>
    <div>
      <label class="form-label">About Us (EN)</label>
      <textarea name="footer_about_en" class="form-control" rows="3"><?= h(setting('footer_about_en')) ?></textarea>
    </div>
  </div>
</div>

<!-- SEO / Analytics -->
<div class="stat-card mb-5">
  <h2 class="font-bold text-sm mb-4 flex items-center gap-2"><i data-lucide="bar-chart-2" class="w-4 h-4"></i> SEO / Analytics</h2>
  <div class="space-y-4">
    <div>
      <label class="form-label">Meta Keywords</label>
      <input type="text" name="meta_keywords" class="form-control" value="<?= h(setting('meta_keywords')) ?>" placeholder="नेपाल,news,समाचार">
    </div>
    <div>
      <label class="form-label">Google Analytics ID (GA4)</label>
      <input type="text" name="google_analytics" class="form-control" value="<?= h(setting('google_analytics')) ?>" placeholder="G-XXXXXXXXXX">
    </div>
    <div>
      <label class="form-label">Breaking News Label</label>
      <input type="text" name="ticker_label" class="form-control" value="<?= h(setting('ticker_label','ताजा खबर')) ?>">
    </div>
  </div>
</div>

<!-- YouTube -->
<div class="stat-card mb-5">
  <h2 class="font-bold text-sm mb-4 flex items-center gap-2"><i data-lucide="tv-2" class="w-4 h-4"></i> YouTube भिडियो</h2>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
      <label class="form-label">YouTube Channel URL</label>
      <input type="url" name="youtube_channel" class="form-control" value="<?= h(setting('youtube_channel')) ?>" placeholder="https://youtube.com/@channel">
    </div>
    <div>
      <label class="form-label">YouTube Embed URL (footer मा देखिन्छ)</label>
      <input type="url" name="youtube_embed" class="form-control" value="<?= h(setting('youtube_embed')) ?>" placeholder="https://www.youtube.com/embed/...">
    </div>
  </div>
</div>

<!-- Admin credentials -->
<div class="stat-card mb-5">
  <h2 class="font-bold text-sm mb-4 flex items-center gap-2"><i data-lucide="key-round" class="w-4 h-4"></i> Admin प्रमाणहरू परिवर्तन</h2>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
      <label class="form-label">प्रयोगकर्ता नाम</label>
      <input type="text" name="admin_username" class="form-control" value="<?= h(setting('admin_username', DEFAULT_ADMIN_USERNAME)) ?>">
    </div>
    <div>
      <label class="form-label">हालको पासवर्ड (verification)</label>
      <input type="password" name="admin_password_cur" class="form-control" placeholder="•••••••">
    </div>
    <div>
      <label class="form-label">नयाँ पासवर्ड</label>
      <input type="password" name="admin_password_new" class="form-control" placeholder="नयाँ पासवर्ड">
    </div>
  </div>
  <p class="form-help mt-2">पासवर्ड परिवर्तन गर्न हालको पासवर्ड र नयाँ पासवर्ड दुवै भर्नुस्।</p>
</div>

<button type="submit" class="btn btn-primary gap-2">
  <i data-lucide="save" class="w-4 h-4"></i> सबै सेटिङ्स सेभ गर्नुस्
</button>
</form>
</div>
</div>
</body></html>
