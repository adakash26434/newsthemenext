<?php
admin_check();
$s = get_all_settings();
admin_html_start('साइट सेटिङ्स');
admin_sidebar('settings');
?>
<div class="admin-content">
<?php admin_topbar('साइट सेटिङ्स'); ?>
<div class="p-6">
<?php admin_flash(); ?>

<form method="POST" action="/admin/settings/save" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Site Identity -->
    <div class="stat-card space-y-4">
      <h2 class="font-bold text-sm pb-2" style="border-bottom:1px solid var(--c-admin-border)">🏠 साइट पहिचान</h2>
      <div class="form-group">
        <label class="form-label">साइट नाम (नेपाली) *</label>
        <input type="text" name="site_name" class="form-control" value="<?= h($s['site_name']??'') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Site Name (English)</label>
        <input type="text" name="site_name_en" class="form-control" value="<?= h($s['site_name_en']??'') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">ट्याग लाइन / Tagline</label>
        <input type="text" name="site_tagline" class="form-control" value="<?= h($s['site_tagline']??'') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Logo Text (Navbar)</label>
        <input type="text" name="site_logo_text" class="form-control" value="<?= h($s['site_logo_text']??'') ?>">
        <p class="form-hint">Logo image नभएमा यो text देखिन्छ।</p>
      </div>
      <div class="form-group">
        <label class="form-label">Logo Image Upload</label>
        <?php if (!empty($s['site_logo_url'])): ?>
          <div class="mb-2">
            <img src="<?= h($s['site_logo_url']) ?>" alt="Current Logo" style="max-height:60px;max-width:200px;border:1px solid var(--c-border);border-radius:4px;padding:4px">
            <p class="text-xs mt-1" style="color:var(--c-muted)">हालको logo</p>
          </div>
        <?php endif; ?>
        <input type="file" name="logo_file" class="form-control mb-2" accept="image/*">
        <p class="form-hint">अथवा URL दिनुस्:</p>
        <input type="url" name="site_logo_url" class="form-control" value="<?= h($s['site_logo_url']??'') ?>" placeholder="https://example.com/logo.png">
      </div>
      <div class="form-group">
        <label class="form-label">Favicon Upload</label>
        <?php if (!empty($s['favicon_url'])): ?>
          <div class="mb-2"><img src="<?= h($s['favicon_url']) ?>" alt="Favicon" style="width:32px;height:32px"></div>
        <?php endif; ?>
        <input type="file" name="favicon_file" class="form-control" accept="image/*">
      </div>
      <div class="form-group">
        <label class="form-label">Breaking News Ticker Label</label>
        <input type="text" name="ticker_label" class="form-control" value="<?= h($s['ticker_label']??'ताजा खबर') ?>">
        <p class="form-hint">Breaking news bar को label (जस्तै "ताजा खबर" वा "Breaking News")</p>
      </div>
      <div class="form-group">
        <label class="form-label">Default Language</label>
        <select name="default_lang" class="form-control">
          <option value="np" <?= ($s['default_lang']??'np')==='np'?'selected':'' ?>>नेपाली (NP)</option>
          <option value="en" <?= ($s['default_lang']??'np')==='en'?'selected':'' ?>>English (EN)</option>
        </select>
      </div>
    </div>

    <!-- Colors & Theme -->
    <div class="stat-card space-y-4">
      <h2 class="font-bold text-sm pb-2" style="border-bottom:1px solid var(--c-admin-border)">🎨 थीम रंगहरू</h2>
      <?php foreach ([
        ['primary_color', 'Primary Color', '#7F1D1D', 'Logo, Category badge, heading color'],
        ['nav_color',     'Nav/Footer Color', '#7F1D1D', 'Navigation र Footer background'],
        ['accent_color',  'Accent Color', '#991B1B', 'Hover, link, secondary accent'],
      ] as [$key, $label, $def, $hint]): ?>
      <div class="form-group">
        <label class="form-label"><?= $label ?></label>
        <div class="flex gap-3 items-center">
          <input type="color" name="<?= $key ?>" class="rounded cursor-pointer" style="width:44px;height:44px;border:1px solid var(--c-border);padding:2px"
                 value="<?= h($s[$key]??$def) ?>">
          <input type="text" class="form-control" style="max-width:120px"
                 value="<?= h($s[$key]??$def) ?>" placeholder="<?= $def ?>"
                 oninput="this.previousElementSibling.value=this.value">
        </div>
        <p class="form-hint"><?= $hint ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Social Media -->
    <div class="stat-card space-y-4">
      <h2 class="font-bold text-sm pb-2" style="border-bottom:1px solid var(--c-admin-border)">📱 सोशल मिडिया</h2>
      <?php foreach ([
        ['social_facebook','Facebook Page URL','https://facebook.com/...'],
        ['social_twitter', 'Twitter/X URL',    'https://twitter.com/...'],
        ['social_youtube', 'YouTube Channel URL','https://youtube.com/c/...'],
        ['social_instagram','Instagram URL',   'https://instagram.com/...'],
        ['social_tiktok',  'TikTok URL',       'https://tiktok.com/@...'],
        ['youtube_channel','YouTube Channel URL (for Footer button)','https://youtube.com/...'],
        ['youtube_embed',  'YouTube Embed URL (Homepage Video Block)','https://www.youtube.com/embed/PLAYLIST_ID?list=...'],
      ] as [$key,$label,$ph]): ?>
      <div class="form-group">
        <label class="form-label"><?= $label ?></label>
        <input type="url" name="<?= $key ?>" class="form-control" value="<?= h($s[$key]??'') ?>" placeholder="<?= $ph ?>">
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Contact Info -->
    <div class="stat-card space-y-4">
      <h2 class="font-bold text-sm pb-2" style="border-bottom:1px solid var(--c-admin-border)">📞 सम्पर्क जानकारी</h2>
      <div class="form-group">
        <label class="form-label">इमेल</label>
        <input type="email" name="contact_email" class="form-control" value="<?= h($s['contact_email']??'') ?>" placeholder="info@example.com.np">
      </div>
      <div class="form-group">
        <label class="form-label">फोन</label>
        <input type="text" name="contact_phone" class="form-control" value="<?= h($s['contact_phone']??'') ?>" placeholder="+977-1-XXXXXX">
      </div>
      <div class="form-group">
        <label class="form-label">ठेगाना</label>
        <input type="text" name="contact_address" class="form-control" value="<?= h($s['contact_address']??'') ?>" placeholder="काठमाडौं, नेपाल">
      </div>
      <div class="form-group">
        <label class="form-label">Footer About (नेपाली)</label>
        <textarea name="footer_about" class="form-control" rows="3"><?= h($s['footer_about']??'') ?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Footer About (English)</label>
        <textarea name="footer_about_en" class="form-control" rows="3"><?= h($s['footer_about_en']??'') ?></textarea>
      </div>
    </div>

    <!-- Organization info -->
    <div class="stat-card space-y-4">
      <h2 class="font-bold text-sm pb-2" style="border-bottom:1px solid var(--c-admin-border)">🏢 संस्था जानकारी</h2>
      <div class="form-group">
        <label class="form-label">दर्ता नम्बर</label>
        <input type="text" name="registration_no" class="form-control" value="<?= h($s['registration_no']??'') ?>" placeholder="दर्ता नं: XXXX">
      </div>
      <div class="form-group">
        <label class="form-label">स्थापना वर्ष</label>
        <input type="text" name="founded_year" class="form-control" value="<?= h($s['founded_year']??'') ?>" placeholder="२०७५">
      </div>
      <div class="form-group">
        <label class="form-label">Copyright Text</label>
        <input type="text" name="copyright_text" class="form-control" value="<?= h($s['copyright_text']??'') ?>" placeholder="सर्वाधिकार सुरक्षित।">
      </div>
    </div>

    <!-- SEO -->
    <div class="stat-card space-y-4">
      <h2 class="font-bold text-sm pb-2" style="border-bottom:1px solid var(--c-admin-border)">🔍 SEO</h2>
      <div class="form-group">
        <label class="form-label">Meta Keywords</label>
        <input type="text" name="meta_keywords" class="form-control" value="<?= h($s['meta_keywords']??'') ?>" placeholder="नेपाल समाचार, ताजा खबर, nepal news">
        <p class="form-hint">कमाले छुट्याएर लेख्नुस्</p>
      </div>
      <div class="form-group">
        <label class="form-label">Google Analytics ID</label>
        <input type="text" name="google_analytics" class="form-control" value="<?= h($s['google_analytics']??'') ?>" placeholder="G-XXXXXXXXXX">
      </div>
    </div>

    <!-- Admin credentials -->
    <div class="stat-card space-y-4">
      <h2 class="font-bold text-sm pb-2" style="border-bottom:1px solid var(--c-admin-border)">🔐 Admin Credentials</h2>
      <div class="p-3 rounded text-xs" style="background:#FEF3C7;color:#92400E;border:1px solid #FCD34D">
        ⚠️ Password परिवर्तन गरेपछि नयाँ credentials याद राख्नुस्!
      </div>
      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" name="admin_username" class="form-control" value="<?= h($s['admin_username']??'admin') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">नयाँ Password</label>
        <input type="password" name="admin_password_new" class="form-control" placeholder="खाली राख्नुस् भने परिवर्तन हुँदैन">
      </div>
      <div class="form-group">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="admin_password_confirm" class="form-control" placeholder="माथिको password दोहोर्याउनुस्">
      </div>
    </div>

  </div>

  <div class="mt-6 flex gap-3 flex-wrap">
    <button type="submit" class="btn btn-primary px-8">💾 सेटिङ्स सेभ गर्नुस्</button>
    <a href="/admin" class="btn btn-secondary">रद्द</a>
  </div>
</form>
</div>
</div>
</body></html>
