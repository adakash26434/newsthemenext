<?php
admin_check();
$status_filter = $_GET['status'] ?? '';
$search_q      = trim($_GET['q'] ?? '');
$page_num      = max(1,(int)($_GET['page']??1));

$opts = ['limit'=>20,'offset'=>($page_num-1)*20];
if ($status_filter) $opts['status'] = $status_filter;
if ($search_q)      $opts['search'] = $search_q;

$total    = count_articles($opts);
$articles = get_articles($opts + ['order'=>'a.created_at DESC']);

admin_html_start('लेखहरू');
admin_sidebar('articles');
?>
<div class="admin-content">
<?php admin_topbar('लेखहरू व्यवस्थापन'); ?>
<div class="p-6">
<?php admin_flash(); ?>
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
  <div class="flex items-center gap-2 flex-wrap">
    <a href="/admin/articles" class="badge <?= !$status_filter?'badge-blue':'badge-gray' ?>">सबै</a>
    <a href="/admin/articles?status=published" class="badge <?= $status_filter==='published'?'badge-green':'badge-gray' ?>">प्रकाशित</a>
    <a href="/admin/articles?status=draft" class="badge <?= $status_filter==='draft'?'badge-gray':'' ?>">ड्राफ्ट</a>
  </div>
  <div class="flex gap-2">
    <form method="GET" action="/admin/articles" class="flex gap-2">
      <?php if ($status_filter): ?><input type="hidden" name="status" value="<?= h($status_filter) ?>"><?php endif; ?>
      <input type="search" name="q" value="<?= h($search_q) ?>" class="form-control text-sm" style="width:220px" placeholder="खोज्नुस्...">
      <button type="submit" class="btn btn-secondary btn-sm">खोज</button>
    </form>
    <a href="/admin/articles?action=new" class="btn btn-primary btn-sm">+ नयाँ लेख</a>
  </div>
</div>
<p class="text-xs mb-3" style="color:var(--c-muted)">कुल: <?= np_number($total) ?> लेख</p>
<div class="overflow-x-auto">
<table class="data-table">
  <thead>
    <tr>
      <th>शीर्षक</th>
      <th>श्रेणी</th>
      <th>लेखक</th>
      <th>भाषा</th>
      <th>स्थिति</th>
      <th>दृश्य</th>
      <th>मिति</th>
      <th>कार्य</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($articles)): ?>
    <tr><td colspan="8" class="text-center py-8" style="color:var(--c-muted)">कुनै लेख फेला परेन।</td></tr>
    <?php endif; ?>
    <?php foreach ($articles as $a): ?>
    <tr>
      <td style="max-width:240px">
        <div class="font-semibold text-sm leading-snug" style="white-space:normal">
          <?= h(mb_substr($a['title'],0,50)) ?><?= mb_strlen($a['title'])>50?'…':'' ?>
        </div>
        <?php if ($a['featured']): ?><span class="badge badge-blue" style="font-size:0.6rem">⭐ Featured</span><?php endif; ?>
      </td>
      <td><span class="badge" style="background:<?= h(category_color($a['category_color'])) ?>;color:#fff;font-size:0.65rem"><?= h($a['category_name_np']?:$a['category_name']) ?></span></td>
      <td class="text-xs"><?= h($a['author_name']) ?></td>
      <td><span class="lang-badge lang-<?= h($a['language']??'np') ?>"><?= ($a['language']??'np')==='en'?'EN':'NP' ?></span></td>
      <td><span class="badge <?= $a['status']==='published'?'badge-green':'badge-gray' ?>"><?= $a['status']==='published'?'प्रकाशित':'ड्राफ्ट' ?></span></td>
      <td class="text-xs"><?= np_number((int)$a['views']) ?></td>
      <td class="text-xs" style="white-space:nowrap"><?= format_date($a['created_at'],true) ?></td>
      <td>
        <div class="actions">
          <a href="/admin/articles?action=edit&id=<?= $a['id'] ?>" class="btn btn-secondary btn-sm">सम्पादन</a>
          <a href="/article/<?= h($a['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm">हेर्नुस्</a>
          <form method="POST" action="/admin/articles/delete" onsubmit="return confirm('यो लेख मेटाउने?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $a['id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm">मेट्नुस्</button>
          </form>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php if (ceil($total/20)>1): ?>
<div class="pagination mt-4">
  <?php $total_pages=ceil($total/20); $qs=http_build_query(['status'=>$status_filter,'q'=>$search_q]); ?>
  <?php for($p=max(1,$page_num-3);$p<=min($total_pages,$page_num+3);$p++): ?>
    <?php if($p===$page_num): ?><span class="current"><?=$p?></span>
    <?php else: ?><a href="/admin/articles?page=<?=$p?>&<?=$qs?>"><?=$p?></a><?php endif; ?>
  <?php endfor; ?>
</div>
<?php endif; ?>
</div></div>
</body></html>
