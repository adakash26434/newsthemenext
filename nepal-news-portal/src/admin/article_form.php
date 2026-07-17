<?php
admin_check();
$id       = (int)($_GET['id'] ?? 0);
$article  = $id ? get_article_by_id($id) : null;
$cats     = get_categories();
$authors  = get_authors();
$all_tags = get_tags();
$sel_tags = $article ? array_column($article['tags'], 'id') : [];
$is_edit  = $article !== null;
$form_title = $is_edit ? 'लेख सम्पादन' : 'नयाँ लेख';

$d = [
    'title'        => $article['title']        ?? '',
    'title_np'     => $article['title_np']     ?? '',
    'slug'         => $article['slug']         ?? '',
    'summary'      => $article['summary']      ?? '',
    'summary_np'   => $article['summary_np']   ?? '',
    'content'      => $article['content']      ?? '',
    'content_np'   => $article['content_np']   ?? '',
    'language'     => $article['language']     ?? 'np',
    'status'       => $article['status']       ?? 'draft',
    'featured'     => $article['featured']     ?? 0,
    'is_breaking'  => $article['is_breaking']  ?? 0,
    'image_url'    => $article['image_url']    ?? '',
    'image_credit' => $article['image_credit'] ?? '',
    'category_id'  => $article['category_id']  ?? '',
    'author_id'    => $article['author_id']    ?? '',
    'published_at' => $article['published_at'] ?? date('Y-m-d H:i:s'),
    'type'         => $article['type']         ?? 'news',
    'seo_title'    => $article['seo_title']    ?? '',
    'seo_desc'     => $article['seo_desc']     ?? '',
];

admin_html_start($form_title);
admin_sidebar('articles');
?>
<div class="admin-content">
<?php admin_topbar($form_title); ?>
<div class="p-6">
<?php admin_flash(); ?>
<form method="POST" action="/admin/articles/save" enctype="multipart/form-data"
      x-data="{
        title: <?= json_encode($d['title']) ?>,
        slug: <?= json_encode($d['slug']) ?>,
        autoSlug: <?= $is_edit ? 'false' : 'true' ?>,
        makeSlug(t){ return t.toLowerCase().replace(/[^\w\s-]/g,'').replace(/[\s_]+/g,'-').replace(/^-+|-+$/g,'')||'article'; }
      }">
  <?= csrf_field() ?>
  <?php if ($is_edit): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main (2 cols) -->
    <div class="lg:col-span-2 space-y-4">
      <div class="form-group">
        <label class="form-label">शीर्षक (Primary) <span style="color:#EF4444">*</span></label>
        <input type="text" name="title" class="form-control" required
               placeholder="लेखको मुख्य शीर्षक"
               x-model="title" @input="if(autoSlug) slug=makeSlug(title)"
               value="<?= h($d['title']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">शीर्षक (अर्को भाषा — optional)</label>
        <input type="text" name="title_np" class="form-control"
               placeholder="Secondary/translation title"
               value="<?= h($d['title_np']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">URL Slug</label>
        <div class="flex gap-2">
          <input type="text" name="slug" class="form-control flex-1" required
                 x-model="slug" @focus="autoSlug=false"
                 placeholder="url-friendly-slug">
          <button type="button" class="btn btn-secondary btn-sm"
                  @click="slug=makeSlug(title);autoSlug=false">Auto</button>
        </div>
        <p class="form-hint">URL: /article/<span x-text="slug||'slug'"></span></p>
      </div>

      <!-- Summary tabs -->
      <div x-data="{tab:'np'}" class="stat-card p-0 overflow-hidden">
        <div class="flex border-b" style="border-color:var(--c-admin-border)">
          <button type="button" @click="tab='np'" class="px-4 py-2 text-sm font-medium transition-colors"
                  :class="tab==='np' ? 'border-b-2 border-current' : ''"
                  :style="tab==='np' ? 'color:var(--c-primary-lt);border-color:var(--c-primary-lt)' : 'color:var(--c-muted)'">नेपाली</button>
          <button type="button" @click="tab='en'" class="px-4 py-2 text-sm font-medium transition-colors"
                  :class="tab==='en' ? 'border-b-2 border-current' : ''"
                  :style="tab==='en' ? 'color:var(--c-primary-lt);border-color:var(--c-primary-lt)' : 'color:var(--c-muted)'">English</button>
        </div>
        <div class="p-4 space-y-4">
          <div x-show="tab==='np'">
            <div class="form-group">
              <label class="form-label">सारांश (नेपाली) <span style="color:#EF4444">*</span></label>
              <textarea name="summary" class="form-control" rows="3" required placeholder="संक्षिप्त विवरण..."><?= h($d['summary']) ?></textarea>
            </div>
            <div class="form-group">
              <label class="form-label flex items-center justify-between">
                <span>विषयवस्तु (नेपाली) <span style="color:#EF4444">*</span></span>
                <span id="wc-np" class="text-xs font-normal" style="color:var(--c-muted)"></span>
              </label>
              <textarea name="content" id="content-np" class="form-control" rows="14" required placeholder="लेखको पूरा विषयवस्तु..."><?= h($d['content']) ?></textarea>
              <p class="form-hint">HTML tags प्रयोग गर्न मिल्छ। <kbd style="padding:1px 5px;border-radius:3px;font-size:11px;border:1px solid var(--admin-border)">Ctrl+S</kbd> — तुरुन्त सेभ</p>
            </div>
          </div>
          <div x-show="tab==='en'" x-cloak>
            <div class="form-group">
              <label class="form-label">Summary (English)</label>
              <textarea name="summary_np" class="form-control" rows="3" placeholder="Brief summary..."><?= h($d['summary_np']) ?></textarea>
            </div>
            <div class="form-group">
              <label class="form-label flex items-center justify-between">
                <span>Content (English)</span>
                <span id="wc-en" class="text-xs font-normal" style="color:var(--c-muted)"></span>
              </label>
              <textarea name="content_np" id="content-en" class="form-control" rows="14" placeholder="Full article content..."><?= h($d['content_np']) ?></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sidebar (1 col) -->
    <div class="space-y-4">

      <!-- Publish box -->
      <div class="stat-card space-y-3">
        <h3 class="font-bold text-sm flex items-center gap-2">
          <?= icon('send','icon-sm') ?> प्रकाशन
        </h3>
        <div class="form-group">
          <label class="form-label">स्थिति</label>
          <select name="status" class="form-control">
            <option value="draft"     <?= $d['status']==='draft'?'selected':'' ?>>
              ड्राफ्ट
            </option>
            <option value="published" <?= $d['status']==='published'?'selected':'' ?>>
              प्रकाशित
            </option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">प्रकाशन मिति/समय</label>
          <input type="datetime-local" name="published_at" class="form-control"
                 value="<?= h(date('Y-m-d\TH:i', strtotime($d['published_at']))) ?>">
        </div>
        <div class="space-y-2">
          <label class="flex items-center gap-2 cursor-pointer font-medium">
            <input type="checkbox" name="featured" <?= $d['featured']?'checked':'' ?> class="rounded">
            <?= icon('star','icon-sm') ?> Featured (मुख्य पृष्ठमा)
          </label>
          <label class="flex items-center gap-2 cursor-pointer font-medium">
            <input type="checkbox" name="is_breaking" <?= $d['is_breaking']?'checked':'' ?> class="rounded">
            <?= icon('radio','icon-sm') ?> Breaking News (Ticker मा)
          </label>
        </div>
        <div class="form-group">
          <label class="form-label">भाषा</label>
          <select name="language" class="form-control">
            <option value="np" <?= $d['language']==='np'?'selected':'' ?>>नेपाली (NP)</option>
            <option value="en" <?= $d['language']==='en'?'selected':'' ?>>English (EN)</option>
          </select>
        </div>
        <div class="flex gap-2 pt-2">
          <button type="submit" class="btn btn-primary flex-1 justify-center flex items-center gap-1">
            <?= icon('save','icon-sm') ?> सेभ गर्नुस्
          </button>
          <?php if ($is_edit): ?>
          <a href="/article/<?= h($article['slug']) ?>" target="_blank" class="btn btn-secondary flex items-center" title="Preview">
            <?= icon('eye','icon-sm') ?>
          </a>
          <?php endif; ?>
        </div>
        <?php if ($is_edit): ?>
        <form method="POST" action="/admin/articles/delete" onsubmit="return confirm('यो लेख मेटाउने?')">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= $id ?>">
          <button class="btn btn-danger w-full justify-center btn-sm flex items-center gap-1">
            <?= icon('trash-2','icon-sm') ?> लेख मेटाउनुस्
          </button>
        </form>
        <?php endif; ?>
      </div>

      <!-- Category & Author -->
      <div class="stat-card space-y-3">
        <h3 class="font-bold text-sm flex items-center gap-2">
          <?= icon('folder','icon-sm') ?> श्रेणी र लेखक
        </h3>
        <div class="form-group">
          <label class="form-label">श्रेणी <span style="color:#EF4444">*</span></label>
          <select name="category_id" class="form-control" required>
            <option value="">— श्रेणी छान्नुस् —</option>
            <?php foreach ($cats as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= $d['category_id']==$cat['id']?'selected':'' ?>>
                <?= h($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">लेखक <span style="color:#EF4444">*</span></label>
          <select name="author_id" class="form-control" required>
            <option value="">— लेखक छान्नुस् —</option>
            <?php foreach ($authors as $au): ?>
              <option value="<?= $au['id'] ?>" <?= $d['author_id']==$au['id']?'selected':'' ?>>
                <?= h($au['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Article Type -->
      <div class="stat-card">
        <h3 class="font-bold text-sm mb-3 flex items-center gap-2"><?= icon('tag','icon-sm') ?> प्रकार</h3>
        <select name="type" class="form-control">
          <?php foreach (['news'=>'समाचार (News)','video'=>'भिडिओ (Video)','photo-gallery'=>'फोटो ग्यालेरी','opinion'=>'विचार (Opinion)'] as $tv=>$tl): ?>
          <option value="<?= $tv ?>" <?= $d['type']===$tv?'selected':'' ?>><?= h($tl) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- SEO fields -->
      <div class="stat-card space-y-3">
        <h3 class="font-bold text-sm flex items-center gap-2"><?= icon('search','icon-sm') ?> SEO / मेटाडाटा</h3>
        <div class="form-group">
          <label class="form-label">SEO शीर्षक <span class="text-xs" style="color:var(--c-muted)">(खाली छाड्न सक्नुहुन्छ — मुख्य शीर्षक प्रयोग हुनेछ)</span></label>
          <input type="text" name="seo_title" class="form-control" maxlength="300"
                 value="<?= h($d['seo_title']) ?>" placeholder="Google/Facebook मा देखिने शीर्षक">
        </div>
        <div class="form-group">
          <label class="form-label">Meta Description <span class="text-xs" style="color:var(--c-muted)">(१५०-१६० अक्षर)</span></label>
          <textarea name="seo_desc" class="form-control" rows="2" maxlength="500"
                    placeholder="Google search परिणाममा देखिने विवरण"><?= h($d['seo_desc']) ?></textarea>
        </div>
      </div>

      <!-- Featured image -->
      <div class="stat-card space-y-3">
        <h3 class="font-bold text-sm flex items-center gap-2">
          <?= icon('image','icon-sm') ?> Featured Image
        </h3>
        <?php if ($d['image_url']): ?>
          <div>
            <img src="<?= h($d['image_url']) ?>" alt="Current" style="width:100%;max-height:160px;object-fit:cover;border-radius:6px">
          </div>
        <?php endif; ?>
        <div class="form-group">
          <label class="form-label">Image Upload</label>
          <input type="file" name="image_file" class="form-control" accept="image/*">
        </div>
        <div class="form-group">
          <label class="form-label">अथवा Image URL</label>
          <div class="flex gap-2">
            <input type="url" name="image_url" id="image_url_field" class="form-control flex-1" value="<?= h($d['image_url']) ?>" placeholder="https://...">
            <button type="button" class="btn btn-secondary btn-sm whitespace-nowrap" onclick="openMediaPicker()">
              <?= icon('images','w-3.5 h-3.5') ?> मिडिया
            </button>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Image Credit / स्रोत</label>
          <input type="text" name="image_credit" class="form-control" value="<?= h($d['image_credit']) ?>" placeholder="फोटो स्रोत (जस्तै: AP, Reuters)">
        </div>
      </div>

      <!-- Correction Note -->
      <?php if ($is_edit): ?>
      <div class="stat-card space-y-2">
        <h3 class="font-bold text-sm flex items-center gap-2"><?= icon('alert-triangle','icon-sm') ?> सुधार / Correction Notice</h3>
        <textarea name="correction_note" class="form-control" rows="2"
                  placeholder="यदि सुधार भएको छ भने यहाँ उल्लेख गर्नुस्..."><?= h($article['correction_note'] ?? '') ?></textarea>
        <p class="form-hint">लेखमा अन्तमा बोक्स भित्र देखिन्छ। खाली राख्नुस् भने देखिँदैन।</p>
      </div>
      <?php endif; ?>

      <!-- Tags -->
      <div class="stat-card">
        <h3 class="font-bold text-sm mb-3 flex items-center gap-2">
          <?= icon('tag','icon-sm') ?> ट्यागहरू
        </h3>
        <div class="max-h-48 overflow-y-auto space-y-1">
          <?php foreach ($all_tags as $tag): ?>
          <label class="flex items-center gap-2 cursor-pointer text-sm">
            <input type="checkbox" name="tag_ids[]" value="<?= $tag['id'] ?>"
                   <?= in_array($tag['id'], $sel_tags)?'checked':'' ?> class="rounded">
            <?= h($tag['name']) ?>
          </label>
          <?php endforeach; ?>
        </div>
        <?php if (empty($all_tags)): ?>
          <p class="text-xs" style="color:var(--c-muted)">ट्याग छैन। <a href="/admin/tags" class="underline">थप्नुस्</a></p>
        <?php endif; ?>
      </div>

    </div>
  </div>
</form>
</div>
</div>

<!-- Media Library Picker Modal -->
<div id="media-picker-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.6);align-items:center;justify-content:center">
  <div style="background:var(--admin-card);border-radius:10px;width:min(700px,95vw);max-height:80vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.4)">
    <div style="padding:14px 18px;border-bottom:1px solid var(--admin-border);display:flex;align-items:center;justify-content:between;gap:2px">
      <span style="font-weight:700;font-size:14px">मिडिया लाइब्रेरी</span>
      <button onclick="closeMediaPicker()" style="margin-left:auto;background:none;border:none;font-size:22px;cursor:pointer;color:var(--admin-muted)">&times;</button>
    </div>
    <div id="media-picker-grid" style="overflow-y:auto;padding:14px;display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px">
      <div style="color:var(--admin-muted);grid-column:1/-1;text-align:center;padding:20px">लोड हुँदैछ...</div>
    </div>
    <div style="padding:10px 18px;border-top:1px solid var(--admin-border);font-size:12px;color:var(--admin-muted)">
      छवि क्लिक गर्नुस् — URL field मा सेट हुन्छ
    </div>
  </div>
</div>
<script>
function openMediaPicker() {
  var m = document.getElementById('media-picker-modal');
  m.style.display = 'flex';
  var g = document.getElementById('media-picker-grid');
  g.innerHTML = '<div style="color:var(--admin-muted);grid-column:1/-1;text-align:center;padding:20px">लोड हुँदैछ...</div>';
  fetch('/admin/media?ajax=list')
    .then(function(r){ return r.json(); })
    .then(function(files){
      if (!files.length) { g.innerHTML='<div style="color:var(--admin-muted);grid-column:1/-1;text-align:center;padding:20px">मिडिया छैन।</div>'; return; }
      g.innerHTML = files.map(function(f){
        return '<div onclick="pickMedia(\''+f.url+'\')" style="cursor:pointer;border-radius:6px;overflow:hidden;background:#111;aspect-ratio:1;border:2px solid transparent;transition:border-color .15s" onmouseover="this.style.borderColor=\'var(--admin-primary)\'" onmouseout="this.style.borderColor=\'transparent\'">'
          + '<img src="'+f.url+'" alt="" style="width:100%;height:100%;object-fit:cover">'
          + '</div>';
      }).join('');
    })
    .catch(function(){ g.innerHTML='<div style="color:red;grid-column:1/-1;text-align:center;padding:20px">लोड गर्न सकिएन।</div>'; });
}
function closeMediaPicker() { document.getElementById('media-picker-modal').style.display='none'; }
function pickMedia(url) {
  document.getElementById('image_url_field').value = url;
  closeMediaPicker();
  // show preview
  var previews = document.querySelectorAll('[data-img-preview]');
  if (previews.length) previews[0].src = url;
}
document.getElementById('media-picker-modal').addEventListener('click', function(e){ if(e.target===this) closeMediaPicker(); });

// ── Word count ───────────────────────────────────────────
function countWords(text) {
  return text.replace(/<[^>]+>/g,'').trim().split(/\s+/).filter(Boolean).length;
}
function updateWC(taId, wcId) {
  var ta = document.getElementById(taId), wc = document.getElementById(wcId);
  if (!ta || !wc) return;
  function update() {
    var n = countWords(ta.value);
    wc.textContent = n + ' शब्द · ~' + Math.ceil(n/200) + ' मि पठन';
  }
  ta.addEventListener('input', update);
  update();
}
updateWC('content-np','wc-np');
updateWC('content-en','wc-en');

// ── Ctrl+S to save ──────────────────────────────────────
document.addEventListener('keydown', function(e){
  if ((e.ctrlKey||e.metaKey) && e.key==='s') {
    e.preventDefault();
    var form = document.querySelector('form[action="/admin/articles/save"]');
    if (form) form.submit();
  }
});
</script>
</body></html>
