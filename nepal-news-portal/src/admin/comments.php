<?php
admin_check();

// Bulk / single actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);
    $ids    = array_map('intval', $_POST['ids'] ?? []);

    if ($action === 'approve' && $id)  {
        approve_comment($id);
        // Email commenter if contact_email is configured
        $cmt = db_fetch("SELECT * FROM comments WHERE id=?", [$id]);
        if ($cmt && !empty($cmt['email']) && setting('contact_email','')) {
            $art = db_fetch("SELECT title, slug FROM articles WHERE id=?", [(int)$cmt['article_id']]);
            if ($art) {
                $site   = site_name();
                $art_url = rtrim(setting('site_url','https://localhost'),'/') . '/article/' . $art['slug'];
                $msg    = h($cmt['name']) . " जी,\n\nतपाईंको टिप्पणी स्वीकृत भयो।\n\nलेख: " . $art['title'] . "\nहेर्नुस्: $art_url\n\n— {$site} टिम";
                $hdrs   = "From: {$site} <" . setting('contact_email','') . ">\r\nContent-Type: text/plain; charset=UTF-8\r\n";
                @mail($cmt['email'], "तपाईंको टिप्पणी स्वीकृत भयो — {$site}", $msg, $hdrs);
            }
        }
        flash_set('success', 'टिप्पणी स्वीकृत गरियो।');
    }
    if ($action === 'spam'    && $id)  { spam_comment($id);    flash_set('success', 'स्प्याम चिह्नित गरियो।'); }
    if ($action === 'delete'  && $id)  { delete_comment($id);  flash_set('success', 'टिप्पणी मेटाइयो।'); }

    // Bulk
    if ($action === 'bulk_approve' && $ids) { foreach ($ids as $bid) approve_comment($bid); flash_set('success', count($ids).' टिप्पणीहरू स्वीकृत।'); }
    if ($action === 'bulk_delete'  && $ids) { foreach ($ids as $bid) delete_comment($bid);  flash_set('success', count($ids).' टिप्पणीहरू मेटाइयो।'); }

    $qs = $_GET['status'] ? '?status='.$_GET['status'] : '';
    redirect('admin/comments'.$qs);
}

$status   = in_array($_GET['status'] ?? '', ['pending','approved','spam']) ? $_GET['status'] : 'pending';
$page     = max(1, (int)($_GET['page'] ?? 1));
$per_page = 30;
$total    = count_comments($status);
$pag      = paginate($total, $per_page, $page, '/admin/comments?status='.$status.'&page={page}');
$comments = get_all_comments(['status'=>$status,'limit'=>$per_page,'offset'=>$pag['offset']]);

$counts = [
    'pending'  => count_comments('pending'),
    'approved' => count_comments('approved'),
    'spam'     => count_comments('spam'),
];

admin_html_start('टिप्पणी व्यवस्थापन');
admin_sidebar('comments');
?>
<div class="admin-content">
<?php admin_topbar('टिप्पणी व्यवस्थापन'); ?>
<div class="p-6">
<?php admin_flash(); ?>

<!-- Status tabs -->
<div class="flex gap-2 mb-5 flex-wrap">
  <?php foreach (['pending'=>'पेन्डिङ','approved'=>'स्वीकृत','spam'=>'स्प्याम'] as $s=>$label): ?>
  <a href="/admin/comments?status=<?= $s ?>"
     class="px-4 py-1.5 rounded-full text-sm font-semibold border transition-colors
            <?= $status===$s ? 'text-white border-transparent' : 'hover:border-gray-400' ?>"
     style="<?= $status===$s ? 'background:var(--c-primary);border-color:var(--c-primary)' : 'background:var(--c-surface2);color:var(--c-text2);border-color:var(--c-border)' ?>">
    <?= $label ?> <span class="ml-1 text-xs opacity-75">(<?= np_number($counts[$s]) ?>)</span>
  </a>
  <?php endforeach; ?>
</div>

<?php if (empty($comments)): ?>
<div class="stat-card text-center py-10" style="color:var(--c-muted)">
  <i data-lucide="message-circle-off" class="w-10 h-10 mx-auto mb-3 opacity-40"></i>
  <p>कुनै टिप्पणी छैन।</p>
</div>
<?php else: ?>
<form method="POST" id="bulk-form">
  <?= csrf_field() ?>
  <div class="flex items-center gap-3 mb-4 flex-wrap">
    <label class="flex items-center gap-2 text-sm cursor-pointer">
      <input type="checkbox" id="select-all" onchange="document.querySelectorAll('.row-cb').forEach(c=>c.checked=this.checked)">
      सबै छान्नुस्
    </label>
    <?php if ($status === 'pending'): ?>
    <button type="submit" name="action" value="bulk_approve"
            class="btn btn-sm btn-primary flex items-center gap-1.5"
            onclick="return confirm('सबै छानिएका स्वीकृत गर्ने?')">
      <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Bulk Approve
    </button>
    <?php endif; ?>
    <button type="submit" name="action" value="bulk_delete"
            class="btn btn-sm flex items-center gap-1.5"
            style="background:#ef4444;color:#fff;border:none"
            onclick="return confirm('सबै छानिएका मेटाउने?')">
      <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Bulk Delete
    </button>
  </div>

  <div class="space-y-3">
    <?php foreach ($comments as $c): ?>
    <div class="stat-card flex gap-4 items-start">
      <input type="checkbox" name="ids[]" value="<?= (int)$c['id'] ?>" class="row-cb mt-1 flex-shrink-0">
      <div class="flex-1 min-w-0">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mb-1">
          <span class="font-bold text-sm"><?= h($c['name']) ?></span>
          <?php if ($c['email']): ?>
          <a href="mailto:<?= h($c['email']) ?>" class="text-xs hover:underline" style="color:var(--c-primary-lt)"><?= h($c['email']) ?></a>
          <?php endif; ?>
          <?php if ($c['website']): ?>
          <a href="<?= h($c['website']) ?>" target="_blank" rel="noopener nofollow" class="text-xs hover:underline" style="color:var(--c-primary-lt)">
            <i data-lucide="external-link" class="w-3 h-3 inline"></i>
          </a>
          <?php endif; ?>
          <span class="text-xs" style="color:var(--c-muted)"><?= time_ago($c['created_at']) ?></span>
          <span class="text-xs" style="color:var(--c-muted)">IP: <?= h($c['ip']) ?></span>
        </div>
        <?php if ($c['article_title']): ?>
        <div class="text-xs mb-1.5" style="color:var(--c-muted)">
          <i data-lucide="newspaper" class="w-3 h-3 inline"></i>
          <a href="/article/<?= h($c['article_slug']) ?>" target="_blank" class="hover:underline"><?= h(excerpt($c['article_title'],10)) ?></a>
        </div>
        <?php endif; ?>
        <p class="text-sm leading-relaxed" style="color:var(--c-text)"><?= nl2br(h($c['content'])) ?></p>
      </div>
      <div class="flex flex-col gap-1.5 flex-shrink-0">
        <?php if ($c['status'] !== 'approved'): ?>
        <form method="POST">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
          <button name="action" value="approve" class="btn btn-sm btn-primary w-full text-xs">
            <i data-lucide="check" class="w-3 h-3 inline"></i> Approve
          </button>
        </form>
        <?php endif; ?>
        <?php if ($c['status'] !== 'spam'): ?>
        <form method="POST">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
          <button name="action" value="spam" class="btn btn-sm w-full text-xs" style="background:#f59e0b;color:#fff;border:none">
            <i data-lucide="alert-triangle" class="w-3 h-3 inline"></i> Spam
          </button>
        </form>
        <?php endif; ?>
        <form method="POST" onsubmit="return confirm('मेटाउने?')">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
          <button name="action" value="delete" class="btn btn-sm w-full text-xs" style="background:#ef4444;color:#fff;border:none">
            <i data-lucide="trash-2" class="w-3 h-3 inline"></i> Delete
          </button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</form>
<?php render_pagination($pag); ?>
<?php endif; ?>
</div>
</div>
<?php admin_html_end(); ?>
