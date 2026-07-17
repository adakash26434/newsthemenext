<?php
admin_check();
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$filter_status   = $_GET['status'] ?? '';
$filter_cat      = $_GET['cat']    ?? '';
$filter_search   = $_GET['q']      ?? '';

$opts = ['limit' => $per_page, 'offset' => ($page-1)*$per_page];
if ($filter_status) $opts['status']       = $filter_status;
if ($filter_cat)    $opts['category_slug'] = $filter_cat;
if ($filter_search) $opts['search']        = $filter_search;

$total    = count_articles($opts);
$articles = get_articles($opts);
$pag      = paginate($total, $per_page, $page, '/admin/articles?'.http_build_query(['status'=>$filter_status,'cat'=>$filter_cat,'q'=>$filter_search,'page'=>'{page}']));
$cats     = get_categories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action']??'') === 'delete') {
        delete_article((int)($_POST['id'] ?? 0));
        flash_set('success', 'लेख मेटाइयो।');
        redirect('admin/articles');
    }
    if (($_POST['action']??'') === 'toggle_status') {
        $a = get_article_by_id((int)($_POST['id'] ?? 0));
        if ($a) {
            $new_status = $a['status'] === 'published' ? 'draft' : 'published';
            db_query("UPDATE articles SET status=? WHERE id=?", [$new_status, $a['id']]);
            flash_set('success', 'स्थिति परिवर्तन गरियो।');
        }
        redirect('admin/articles');
    }
    // ── Bulk actions ──────────────────────────────────────
    if (($_POST['action']??'') === 'bulk') {
        $bulk_op  = $_POST['bulk_op'] ?? '';
        $bulk_ids = array_map('intval', (array)($_POST['bulk_ids'] ?? []));
        $bulk_ids = array_filter($bulk_ids);
        if ($bulk_ids && in_array($bulk_op, ['publish','draft','delete'])) {
            foreach ($bulk_ids as $bid) {
                if ($bulk_op === 'delete') {
                    delete_article($bid);
                } else {
                    db_query("UPDATE articles SET status=? WHERE id=?", [$bulk_op === 'publish' ? 'published' : 'draft', $bid]);
                }
            }
            $n = count($bulk_ids);
            flash_set('success', np_number($n) . ' लेखहरूमा कार्य सम्पन्न भयो।');
        }
        redirect('admin/articles');
    }
}

admin_html_start('लेखहरू');
admin_sidebar('articles');
?>
<div class="admin-content">
<?php admin_topbar('लेखहरू'); ?>
<div class="p-6">
<?php admin_flash(); ?>

<!-- Filters + New button -->
<div class="flex items-center justify-between mb-4 flex-wrap gap-3">
  <form method="GET" action="/admin/articles" class="flex flex-wrap items-center gap-2">
    <select name="status" class="form-control" style="width:auto" onchange="this.form.submit()">
      <option value="">सबै स्थिति</option>
      <option value="published" <?= $filter_status==='published'?'selected':'' ?>>प्रकाशित</option>
      <option value="draft"     <?= $filter_status==='draft'    ?'selected':'' ?>>ड्राफ्ट</option>
    </select>
    <select name="cat" class="form-control" style="width:auto" onchange="this.form.submit()">
      <option value="">सबै श्रेणी</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?= h($c['slug']) ?>" <?= $filter_cat===$c['slug']?'selected':'' ?>><?= h($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <div class="flex gap-1">
      <input type="search" name="q" value="<?= h($filter_search) ?>" class="form-control" style="width:180px" placeholder="खोज्नुस्...">
      <button type="submit" class="btn btn-secondary btn-sm"><?= icon('search','w-3.5 h-3.5') ?></button>
    </div>
  </form>
  <div class="flex items-center gap-2">
    <span class="text-sm" style="color:var(--c-muted)">जम्मा: <?= np_number($total) ?></span>
    <a href="/admin/articles?action=new" class="btn btn-primary gap-1">
      <?= icon('plus','w-3.5 h-3.5') ?> नयाँ लेख
    </a>
  </div>
</div>

<?php if (empty($articles)): ?>
<div class="stat-card text-center py-10" style="color:var(--c-muted)">
  <?= icon('newspaper','w-10 h-10 mx-auto mb-3 opacity-30') ?>
  <p class="mb-3">कुनै लेख फेला परेन।</p>
  <a href="/admin/articles?action=new" class="btn btn-primary gap-1"><?= icon('plus','w-4 h-4') ?> नयाँ लेख थप्नुस्</a>
</div>
<?php else: ?>
<!-- Bulk action bar -->
<form method="POST" action="/admin/articles" id="bulk-form">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="bulk">
  <div class="flex items-center gap-2 mb-3 p-2 rounded-lg" style="background:var(--c-surface2);border:1px solid var(--c-border)">
    <input type="checkbox" id="bulk-all" onchange="document.querySelectorAll('.bulk-chk').forEach(c=>c.checked=this.checked)"
           class="w-4 h-4 cursor-pointer">
    <label for="bulk-all" class="text-xs cursor-pointer" style="color:var(--c-muted)">सबै छान्नुस्</label>
    <select name="bulk_op" class="form-control" style="width:auto;font-size:12px">
      <option value="">-- कार्य छान्नुस् --</option>
      <option value="publish">प्रकाशित गर्नुस्</option>
      <option value="draft">ड्राफ्ट गर्नुस्</option>
      <option value="delete">मेटाउनुस्</option>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"
            onclick="var s=document.querySelector('[name=bulk_op]').value;if(s==='delete')return confirm('चयनित लेखहरू मेटाउने?');return s!==''"><?= icon('check','w-3.5 h-3.5') ?> लागू गर्नुस्</button>
  </div>

<div class="table-wrap">
  <table class="admin-table">
    <thead><tr>
      <th style="width:30px"></th>
      <th>शीर्षक</th><th>श्रेणी</th><th>लेखक</th><th>स्थिति</th><th>दृश्य</th><th>मिति</th><th>कार्यहरू</th>
    </tr></thead>
    <tbody>
    <?php foreach ($articles as $a): ?>
    <tr>
      <td><input type="checkbox" name="bulk_ids[]" value="<?= $a['id'] ?>" class="bulk-chk w-4 h-4"></td>
      <td>
        <div class="font-semibold text-sm" style="max-width:280px">
          <?= h(mb_substr($a['title'],0,55)) ?><?= mb_strlen($a['title'])>55?'…':'' ?>
        </div>
        <div class="flex gap-1 mt-1">
          <?php if ($a['featured']): ?><span class="badge badge-yellow" style="font-size:9px"><?= icon('star','w-2.5 h-2.5') ?> Featured</span><?php endif; ?>
          <?php if ($a['is_breaking']): ?><span class="badge badge-red" style="font-size:9px"><?= icon('zap','w-2.5 h-2.5') ?> Breaking</span><?php endif; ?>
        </div>
      </td>
      <td>
        <span class="badge" style="background:<?= h(category_color($a['category_color'])) ?>;color:#fff;font-size:10px">
          <?= h($a['category_name_np']?:$a['category_name']) ?>
        </span>
      </td>
      <td class="text-xs"><?= h($a['author_name']) ?></td>
      <td>
        <form method="POST" action="/admin/articles">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="toggle_status">
          <input type="hidden" name="id" value="<?= $a['id'] ?>">
          <button type="submit" class="badge <?= $a['status']==='published'?'badge-green':'badge-gray' ?>" style="cursor:pointer;border:none">
            <?= $a['status']==='published'?'प्रकाशित':'ड्राफ्ट' ?>
          </button>
        </form>
      </td>
      <td class="text-xs"><?= np_number((int)$a['views']) ?></td>
      <td class="text-xs" style="color:var(--c-muted)">
        <?= format_date($a['published_at']??$a['created_at']) ?>
      </td>
      <td>
        <div class="flex gap-1 flex-wrap">
          <a href="/admin/articles?action=edit&id=<?= $a['id'] ?>" class="btn btn-secondary btn-sm gap-1">
            <?= icon('pencil','w-3 h-3') ?>
          </a>
          <a href="/article/<?= h($a['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm gap-1">
            <?= icon('eye','w-3 h-3') ?>
          </a>
          <form method="POST" action="/admin/articles" onsubmit="return confirm('लेख मेटाउने?')">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $a['id'] ?>">
            <button class="btn btn-danger btn-sm"><?= icon('trash-2','w-3 h-3') ?></button>
          </form>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php render_pagination($pag); ?>
</form>
<?php endif; ?>
</div>
</div>
</body></html>
