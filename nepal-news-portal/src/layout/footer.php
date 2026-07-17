<?php
$_f_cats   = get_categories();
$_f_fb     = setting('social_facebook','');
$_f_tw     = setting('social_twitter','');
$_f_yt     = setting('social_youtube','');
$_f_ig     = setting('social_instagram','');
$_f_tk     = setting('social_tiktok','');
$_f_about  = current_lang()==='en' ? setting('footer_about_en', setting('footer_about','')) : setting('footer_about','');
$_f_email  = setting('contact_email','');
$_f_phone  = setting('contact_phone','');
$_f_addr   = setting('contact_address','काठमाडौं, नेपाल');
$_f_recent = get_articles(['status'=>'published','limit'=>4,'order'=>'a.published_at DESC']);
$_f_pages  = get_static_pages(true);
$_f_events = get_upcoming_events(4);
$_reg_no   = setting('registration_no','');
$_founded  = setting('founded_year','');
$_copy     = setting('copyright_text','');
$_cur_lang = current_lang();
?>
</main>

<!-- Reading Progress Bar -->
<div class="reading-progress" id="reading-progress">
  <div class="reading-progress-bar" id="reading-progress-bar"></div>
</div>
<script>
(function(){
  var progress = document.getElementById('reading-progress-bar');
  if (!progress) return;
  
  function updateProgress() {
    var article = document.querySelector('.article-content');
    if (!article) {
      progress.style.display = 'none';
      return;
    }
    
    var rect = article.getBoundingClientRect();
    var totalHeight = rect.height;
    var scrolled = Math.max(0, -rect.top);
    var progress_pct = Math.min(100, (scrolled / totalHeight) * 100);
    progress.style.width = progress_pct + '%';
  }
  
  window.addEventListener('scroll', updateProgress, {passive: true});
  updateProgress();
})();
</script>


<!-- Footer banner ad -->
<?php render_ads('footer-banner', false); ?>

<!-- YouTube embed block -->
<?php if (setting('youtube_embed','')): ?>
<section class="yt-block">
  <div class="max-w-7xl mx-auto px-4">
    <div class="section-heading mb-4">
      <span class="flex items-center gap-2">
        <?= icon('tv-2','w-4 h-4') ?> भिडियो समाचार
      </span>
    </div>
    <div class="yt-embed-wrap">
      <iframe src="<?= h(setting('youtube_embed')) ?>" width="100%" height="100%"
              style="border:none" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    </div>
    <?php if (setting('youtube_channel','')): ?>
    <div class="text-center mt-3">
      <a href="<?= h(setting('youtube_channel')) ?>" target="_blank" class="btn btn-primary gap-2">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.4a2.78 2.78 0 001.95-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg>
        YouTube च्यानल हेर्नुस्
      </a>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- Newsletter bar — Enhanced professional look -->
<div class="newsletter-bar" style="background:linear-gradient(135deg, var(--c-primary) 0%, var(--c-primary-dk, var(--c-primary)) 100%)">
  <div class="max-w-7xl mx-auto px-4 text-center py-8">
    <h3 class="text-2xl font-bold text-white mb-2 flex items-center justify-center gap-3">
      <?= icon('mail','w-6 h-6') ?> न्यूजलेटर सदस्यता लिनुस्
    </h3>
    <p class="text-sm mb-5" style="color:rgba(255,255,255,0.85)">ताजा समाचार सिधै तपाईंको इमेलमा पाउनुस् — निःशुल्क सदस्यता</p>
    <?php $flash_nl_s = flash_get('nl_success'); $flash_nl_e = flash_get('nl_error'); ?>
    <?php if ($flash_nl_s): ?><div class="mb-3 text-sm font-semibold" style="color:#86EFAC"><?= h($flash_nl_s) ?></div><?php endif; ?>
    <?php if ($flash_nl_e): ?><div class="mb-3 text-sm font-semibold" style="color:#FCA5A5"><?= h($flash_nl_e) ?></div><?php endif; ?>
    <form method="POST" action="/newsletter/subscribe" class="flex flex-col sm:flex-row gap-3 justify-center max-w-lg mx-auto">
      <?= csrf_field() ?>
      <input type="text"  name="name"  placeholder="तपाईंको नाम" class="newsletter-input" style="flex:0 0 auto;max-width:200px">
      <input type="email" name="email" placeholder="इमेल ठेगाना *" class="newsletter-input flex-1" required>
      <button type="submit" class="newsletter-btn" style="background:#fff;color:var(--c-primary);font-weight:700;padding:10px 24px">
        सदस्य बन्नुस् <?= icon('arrow-right','w-4 h-4 inline') ?>
      </button>
    </form>
  </div>
</div>

<!-- ── Footer — Professional Karobar Style ── -->
<footer class="site-footer" style="background:var(--c-footer-bg, var(--c-nav-bg))">
  
  <!-- Footer Logo Section with Company Info -->
  <div style="background:rgba(0,0,0,0.15);border-bottom:1px solid rgba(255,255,255,0.08)">
    <div class="max-w-7xl mx-auto px-4 py-6">
      <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Logo and Name -->
        <div class="flex items-center gap-4">
          <?php if (site_logo_url()): ?>
            <img src="<?= h(site_logo_url()) ?>" alt="<?= h(site_name_np()) ?>" class="h-12" style="filter:brightness(0) invert(1)">
          <?php else: ?>
            <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background:rgba(255,255,255,0.15)">
              <span class="text-2xl font-extrabold text-white"><?= mb_substr(site_logo_text(), 0, 1) ?></span>
            </div>
          <?php endif; ?>
          <div>
            <div class="text-xl font-extrabold text-white"><?= h(site_name_np()) ?></div>
            <div class="text-xs" style="color:rgba(255,255,255,0.5)"><?= h(site_tagline()) ?></div>
          </div>
        </div>
        
        <!-- Registration Info -->
        <div class="flex flex-wrap items-center gap-4 text-xs" style="color:rgba(255,255,255,0.6)">
          <?php if ($_reg_no): ?>
          <div class="flex items-center gap-1.5">
            <?= icon('stamp','w-4 h-4') ?>
            <span>सूचना विभाग दर्ता नं.: <?= h($_reg_no) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($_founded): ?>
          <div class="flex items-center gap-1.5">
            <?= icon('calendar','w-4 h-4') ?>
            <span>स्थापना: <?= h($_founded) ?></span>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Main Footer Content -->
  <div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

      <!-- About + Contact -->
      <div>
        <h4 class="text-sm font-bold text-white mb-4 flex items-center gap-2 pb-2" style="border-bottom:2px solid rgba(255,255,255,0.2)">
          <?= icon('building-2','w-4 h-4') ?> <?= $_cur_lang==='en'?'About Us':'हाम्रोबारे' ?>
        </h4>
        <p class="text-sm leading-relaxed mb-4" style="color:rgba(255,255,255,0.65)"><?= h($_f_about ?: excerpt(site_tagline(), 20)) ?></p>
        
        <!-- Contact Info -->
        <div class="space-y-2 text-xs mb-4" style="color:rgba(255,255,255,0.55)">
          <?php if ($_f_addr): ?>
          <div class="flex items-start gap-2">
            <?= icon('map-pin','w-3.5 h-3.5 mt-0.5 flex-shrink-0') ?>
            <span><?= h($_f_addr) ?></span>
          </div>
          <?php endif; ?>
          <?php if ($_f_phone): ?>
          <div class="flex items-center gap-2">
            <?= icon('phone','w-3.5 h-3.5 flex-shrink-0') ?>
            <a href="tel:<?= h($_f_phone) ?>" class="hover:text-white transition-colors"><?= h($_f_phone) ?></a>
          </div>
          <?php endif; ?>
          <?php if ($_f_email): ?>
          <div class="flex items-center gap-2">
            <?= icon('mail','w-3.5 h-3.5 flex-shrink-0') ?>
            <a href="mailto:<?= h($_f_email) ?>" class="hover:text-white transition-colors"><?= h($_f_email) ?></a>
          </div>
          <?php endif; ?>
        </div>
        
        <!-- Social links -->
        <div class="flex gap-2 flex-wrap">
          <?php if ($_f_fb): ?>
          <a href="<?= h($_f_fb) ?>" class="social-link" target="_blank" rel="noopener" title="Facebook">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
          </a>
          <?php endif; ?>
          <?php if ($_f_tw): ?>
          <a href="<?= h($_f_tw) ?>" class="social-link" target="_blank" rel="noopener" title="Twitter/X">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
          </a>
          <?php endif; ?>
          <?php if ($_f_yt): ?>
          <a href="<?= h($_f_yt) ?>" class="social-link" target="_blank" rel="noopener" title="YouTube">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.4a2.78 2.78 0 001.95-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg>
          </a>
          <?php endif; ?>
          <?php if ($_f_ig): ?>
          <a href="<?= h($_f_ig) ?>" class="social-link" target="_blank" rel="noopener" title="Instagram">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path fill="rgba(255,255,255,0.8)" d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><circle cx="17.5" cy="6.5" r="1.5" fill="rgba(255,255,255,0.8)"/></svg>
          </a>
          <?php endif; ?>
          <?php if ($_f_tk): ?>
          <a href="<?= h($_f_tk) ?>" class="social-link" target="_blank" rel="noopener" title="TikTok">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.27 6.27 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.16 8.16 0 004.77 1.52V6.75a4.85 4.85 0 01-1-.06z"/></svg>
          </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Categories -->
      <div>
        <h4 class="text-sm font-bold text-white mb-4 flex items-center gap-2 pb-2" style="border-bottom:2px solid rgba(255,255,255,0.2)">
          <?= icon('grid','w-4 h-4') ?> <?= $_cur_lang==='en'?'Categories':'श्रेणीहरू' ?>
        </h4>
        <ul class="space-y-2">
          <?php foreach (array_slice($_f_cats, 0, 8) as $_fc): ?>
          <li>
            <a href="/category/<?= h($_fc['slug']) ?>" class="footer-link flex items-center gap-2 text-sm">
              <?php if ($_fc['icon']): ?><i data-lucide="<?= h($_fc['icon']) ?>" class="w-3.5 h-3.5 flex-shrink-0"></i><?php else: ?><span class="w-2 h-2 rounded-full flex-shrink-0" style="background:<?= h($_fc['color']?:accent_color()) ?>"></span><?php endif; ?>
              <span class="flex-1"><?= h($_cur_lang==='en'?($_fc['name_np']?:$_fc['name']):($_fc['name']?:$_fc['name_np'])) ?></span>
              <?php if ((int)($_fc['article_count']??0)>0): ?>
                <span class="text-xs px-1.5 py-0.5 rounded" style="background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.5)"><?= np_number((int)$_fc['article_count']) ?></span>
              <?php endif; ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Recent News -->
      <div>
        <h4 class="text-sm font-bold text-white mb-4 flex items-center gap-2 pb-2" style="border-bottom:2px solid rgba(255,255,255,0.2)">
          <?= icon('newspaper','w-4 h-4') ?> <?= $_cur_lang==='en'?'Latest News':'ताजा समाचार' ?>
        </h4>
        <div class="space-y-3">
          <?php foreach ($_f_recent as $_fr): ?>
          <a href="/article/<?= h($_fr['slug']) ?>" class="footer-link text-sm font-medium leading-snug block group">
            <span class="flex items-start gap-2">
              <span class="w-1.5 h-1.5 rounded-full mt-1.5 flex-shrink-0" style="background:var(--c-primary-lt)"></span>
              <span class="flex-1 group-hover:text-white transition-colors"><?= h(excerpt($_fr['title'], 8)) ?></span>
            </span>
            <span class="text-xs flex items-center gap-1 mt-0.5 ml-3.5" style="color:rgba(255,255,255,0.35)">
              <?= icon('clock','w-2.5 h-2.5') ?> <?= time_ago($_fr['published_at']??$_fr['created_at']) ?>
            </span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Quick Links -->
      <div>
        <h4 class="text-sm font-bold text-white mb-4 flex items-center gap-2 pb-2" style="border-bottom:2px solid rgba(255,255,255,0.2)">
          <?= icon('link','w-4 h-4') ?> <?= $_cur_lang==='en'?'Quick Links':'छिटो लिंकहरू' ?>
        </h4>
        <ul class="space-y-2">
          <?php foreach ($_f_pages as $_fp): ?>
          <li>
            <a href="/page/<?= h($_fp['slug']) ?>" class="footer-link flex items-center gap-2 text-sm">
              <?= icon('chevron-right','w-3 h-3') ?>
              <?= h($_cur_lang==='en'?($_fp['title_en']?:$_fp['title']):$_fp['title']) ?>
            </a>
          </li>
          <?php endforeach; ?>
          <li><a href="/trending" class="footer-link flex items-center gap-2 text-sm"><?= icon('trending-up','w-3 h-3') ?> <?= $_cur_lang==='en'?'Trending':'ट्रेन्डिङ' ?></a></li>
          <li><a href="/breaking" class="footer-link flex items-center gap-2 text-sm"><?= icon('zap','w-3 h-3') ?> <?= $_cur_lang==='en'?'Breaking News':'ब्रेकिङ न्यूज' ?></a></li>
          <li><a href="/events" class="footer-link flex items-center gap-2 text-sm"><?= icon('calendar','w-3 h-3') ?> <?= $_cur_lang==='en'?'Events':'कार्यक्रम' ?></a></li>
          <li><a href="/epaper" class="footer-link flex items-center gap-2 text-sm"><?= icon('newspaper','w-3 h-3') ?> <?= $_cur_lang==='en'?'e-Paper':'ई-पेपर' ?></a></li>
          <li><a href="/contact" class="footer-link flex items-center gap-2 text-sm"><?= icon('phone','w-3 h-3') ?> <?= $_cur_lang==='en'?'Contact':'सम्पर्क' ?></a></li>
          <li><a href="/sitemap.xml" class="footer-link flex items-center gap-2 text-sm" target="_blank"><?= icon('map','w-3 h-3') ?> Sitemap</a></li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Footer Bottom — Copyright -->
  <div style="background:rgba(0,0,0,0.2);border-top:1px solid rgba(255,255,255,0.08)">
    <div class="max-w-7xl mx-auto px-4 py-4">
      <div class="flex flex-col md:flex-row items-center justify-between gap-3 text-xs" style="color:rgba(255,255,255,0.4)">
        <div class="flex flex-wrap items-center gap-2 text-center md:text-left">
          <span>&copy; <?= date('Y') ?> <?= h(site_name_np()) ?>. <?= h($_copy) ?: 'सर्वाधिकार सुरक्षित' ?></span>
        </div>
        <div class="flex items-center gap-4 flex-wrap justify-center">
          <a href="/privacy" class="hover:text-white transition-colors"><?= $_cur_lang==='en'?'Privacy Policy':'गोपनीयता नीति' ?></a>
          <a href="/terms" class="hover:text-white transition-colors"><?= $_cur_lang==='en'?'Terms of Service':'सेवा शर्त' ?></a>
          <a href="/about" class="hover:text-white transition-colors"><?= $_cur_lang==='en'?'About':'हाम्रोबारे' ?></a>
          <a href="/contact" class="hover:text-white transition-colors"><?= $_cur_lang==='en'?'Contact':'सम्पर्क' ?></a>
        </div>
      </div>
    </div>
  </div>
</footer>

<script>
// Lucide icons init + re-run after Alpine mutations
document.addEventListener('DOMContentLoaded', function() {
  if (window.lucide) lucide.createIcons();
});
document.addEventListener('alpine:initialized', function() {
  if (window.lucide) lucide.createIcons();
});

// ── Service Worker ────────────────────────────────────────
if ('serviceWorker' in navigator) {
  window.addEventListener('load', function() {
    navigator.serviceWorker.register('/assets/sw.js').catch(function(){});
  });
}

// ── Reaction component (must be global before Alpine init) ─
window.reactionBox = function(articleId, initial) {
  return {
    counts: Object.assign({like:0,love:0,wow:0,sad:0,helpful:0}, initial || {}),
    voted: localStorage.getItem('react_' + articleId),
    react: function(type) {
      if (this.voted) return;
      var self = this;
      var form = document.getElementById('csrf-form');
      var token = form ? form.querySelector('[name=csrf_token]') : document.querySelector('[name=csrf_token]');
      fetch('/react/' + articleId, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'type=' + encodeURIComponent(type) + (token ? '&csrf_token=' + encodeURIComponent(token.value) : '')
      })
      .then(function(r){ return r.json(); })
      .then(function(d){
        if (d.counts) {
          self.counts = d.counts;
          self.voted  = type;
          localStorage.setItem('react_' + articleId, type);
        }
      })
      .catch(function(){});
    }
  };
};

// ── AJAX Load More ────────────────────────────────────────
window.loadMore = function(btn) {
  var url    = btn.dataset.url;
  var offset = parseInt(btn.dataset.offset || 0, 10);
  var grid   = document.getElementById('more-articles-grid') ||
               document.getElementById('cat-more-grid');
  if (!grid) return;
  btn.disabled = true;
  btn.textContent = 'लोड हुँदैछ…';
  fetch(url + '&offset=' + offset)
    .then(function(r){ return r.json(); })
    .then(function(d){
      if (d.html) {
        var tmp = document.createElement('div');
        tmp.innerHTML = d.html;
        while (tmp.firstChild) grid.appendChild(tmp.firstChild);
        if (window.lucide) lucide.createIcons();
      }
      if (d.has_more) {
        btn.dataset.offset = d.next_offset;
        btn.disabled = false;
        btn.textContent = '+ थप समाचार';
      } else {
        btn.parentElement.remove();
      }
    })
    .catch(function(){
      btn.disabled = false;
      btn.textContent = '⚠ पुनः प्रयास गर्नुस्';
    });
};

// ── Back to Top ──────────────────────────────────────────
(function(){
  var btn = document.createElement('button');
  btn.id = 'back-top';
  btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><polyline points="18 15 12 9 6 15"></polyline></svg>';
  btn.title = 'माथि जानुस्';
  btn.setAttribute('aria-label','Back to top');
  document.body.appendChild(btn);
  window.addEventListener('scroll', function(){
    btn.classList.toggle('visible', window.scrollY > 400);
  }, {passive:true});
  btn.addEventListener('click', function(){
    window.scrollTo({top:0, behavior:'smooth'});
  });
})();

// ── Article Bookmark (localStorage) ─────────────────────
window.toggleBookmark = function(articleId, title, slug) {
  var key = 'nnp_bookmarks';
  var saved = JSON.parse(localStorage.getItem(key) || '[]');
  var idx = saved.findIndex(function(b){ return b.id === articleId; });
  var btn = document.getElementById('bookmark-btn');
  if (idx >= 0) {
    saved.splice(idx, 1);
    if (btn) { btn.setAttribute('title','सुरक्षित गर्नुस्'); btn.classList.remove('bookmarked'); }
  } else {
    saved.push({id: articleId, title: title, slug: slug, saved_at: Date.now()});
    if (btn) { btn.setAttribute('title','सुरक्षित गरियो'); btn.classList.add('bookmarked'); }
  }
  localStorage.setItem(key, JSON.stringify(saved));
};
window.initBookmark = function(articleId) {
  var saved = JSON.parse(localStorage.getItem('nnp_bookmarks') || '[]');
  var btn = document.getElementById('bookmark-btn');
  if (btn && saved.some(function(b){ return b.id === articleId; })) {
    btn.classList.add('bookmarked');
    btn.setAttribute('title','सुरक्षित गरियो');
  }
};
</script>

<!-- AI Chat Widget -->
<?php require_once __DIR__ . '/../components/ai_chat_widget.php'; ?>
</body>
</html>
