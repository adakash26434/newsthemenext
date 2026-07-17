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
    'category_id'  => $article['category_id']  ?? '',
    'author_id'    => $article['author_id']    ?? '',
    'published_at' => $article['published_at'] ?? date('Y-m-d H:i:s'),
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
              <label class="form-label">विषयवस्तु (नेपाली) <span style="color:#EF4444">*</span></label>
              <textarea name="content" class="form-control" rows="14" required placeholder="लेखको पूरा विषयवस्तु..."><?= h($d['content']) ?></textarea>
              <p class="form-hint">HTML tags प्रयोग गर्न मिल्छ।</p>
            </div>
          </div>
          <div x-show="tab==='en'" x-cloak>
            <div class="form-group">
              <label class="form-label">Summary (English)</label>
              <textarea name="summary_np" class="form-control" rows="3" placeholder="Brief summary..."><?= h($d['summary_np']) ?></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">Content (English)</label>
              <textarea name="content_np" class="form-control" rows="14" placeholder="Full article content..."><?= h($d['content_np']) ?></textarea>
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
          <input type="url" name="image_url" class="form-control" value="<?= h($d['image_url']) ?>" placeholder="https://...">
        </div>
      </div>

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
</body></html>
