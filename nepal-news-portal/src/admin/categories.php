<?php
admin_check();
$cats = get_categories();
admin_html_start('श्रेणीहरू');
admin_sidebar('categories');
?>
<div class="admin-content">
<?php admin_topbar('श्रेणी व्यवस्थापन'); ?>
<div class="p-6">
<?php admin_flash(); ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <!-- Category list -->
  <div class="lg:col-span-2">
    <table class="data-table">
      <thead><tr><th>रंग</th><th>श्रेणी (Nepali)</th><th>श्रेणी (English)</th><th>Slug</th><th>लेख</th><th>क्रम</th><th>कार्य</th></tr></thead>
      <tbody>
        <?php if (empty($cats)): ?>
          <tr><td colspan="7" class="text-center py-8" style="color:var(--c-muted)">कुनै श्रेणी छैन।</td></tr>
        <?php endif; ?>
        <?php foreach ($cats as $cat): ?>
        <tr x-data="{edit:false,name:<?=json_encode($cat['name_np']?:$cat['name'])?>,nameEn:<?=json_encode($cat['name'])?>,slug:<?=json_encode($cat['slug'])?>,color:<?=json_encode($cat['color'])?>,sort:<?=json_encode($cat['sort_order'])?>}">
          <td>
            <div class="w-6 h-6 rounded-full border" :style="'background:'+color" x-bind:title="color"></div>
          </td>
          <td>
            <span x-show="!edit" class="font-semibold text-sm"><?= h($cat['name_np'] ?: $cat['name']) ?></span>
            <input x-show="edit" x-cloak type="text" x-model="name" class="form-control text-sm" style="max-width:150px">
          </td>
          <td>
            <span x-show="!edit" class="text-sm"><?= h($cat['name']) ?></span>
            <input x-show="edit" x-cloak type="text" x-model="nameEn" class="form-control text-sm" style="max-width:150px">
          </td>
          <td>
            <span x-show="!edit" class="text-xs font-mono" style="color:var(--c-muted)"><?= h($cat['slug']) ?></span>
            <input x-show="edit" x-cloak type="text" x-model="slug" class="form-control text-xs" style="max-width:120px">
          </td>
          <td class="text-sm"><?= np_number((int)($cat['article_count']??0)) ?></td>
          <td>
            <span x-show="!edit" class="text-sm"><?= h($cat['sort_order']) ?></span>
            <input x-show="edit" x-cloak type="number" x-model="sort" class="form-control text-sm" style="max-width:70px">
          </td>
          <td>
            <div class="actions">
              <template x-if="!edit">
                <button @click="edit=true" class="btn btn-secondary btn-sm">सम्पादन</button>
              </template>
              <template x-if="edit">
                <form method="POST" action="/admin/categories/save">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                  <input type="hidden" name="name_np" :value="name">
                  <input type="hidden" name="name"    :value="nameEn">
                  <input type="hidden" name="slug"    :value="slug">
                  <input type="hidden" name="color"   :value="color">
                  <input type="hidden" name="sort_order" :value="sort">
                  <div class="flex items-center gap-1 mb-1">
                    <input type="color" x-model="color" class="rounded cursor-pointer" style="width:28px;height:28px;padding:0;border:1px solid var(--c-border)">
                    <button type="submit" class="btn btn-success btn-sm">सेभ</button>
                    <button type="button" @click="edit=false" class="btn btn-secondary btn-sm">रद्द</button>
                  </div>
                </form>
              </template>
              <form method="POST" action="/admin/categories/delete"
                    onsubmit="return confirm('यो श्रेणी मेटाउने?')">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">मेट्नुस्</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Add new category -->
  <div class="stat-card">
    <h2 class="font-bold text-sm mb-4" style="color:var(--c-text)">नयाँ श्रेणी थप्नुस्</h2>
    <form method="POST" action="/admin/categories/save">
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label">श्रेणी नाम (नेपाली) *</label>
        <input type="text" name="name_np" class="form-control" required placeholder="जस्तै: अर्थतन्त्र">
      </div>
      <div class="form-group">
        <label class="form-label">श्रेणी नाम (English) *</label>
        <input type="text" name="name" class="form-control" required placeholder="e.g. Economy">
      </div>
      <div class="form-group">
        <label class="form-label">Slug (URL) *</label>
        <input type="text" name="slug" class="form-control" required placeholder="economy">
      </div>
      <div class="form-group">
        <label class="form-label">रंग</label>
        <div class="flex gap-2 items-center">
          <input type="color" name="color" value="#991B1B" class="rounded cursor-pointer" style="width:36px;height:36px;padding:0;border:1px solid var(--c-border)">
          <span class="text-xs" style="color:var(--c-muted)">श्रेणीको रंग</span>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">क्रम</label>
        <input type="number" name="sort_order" class="form-control" value="0" min="0">
      </div>
      <button type="submit" class="btn btn-primary w-full justify-center">थप्नुस्</button>
    </form>
  </div>
</div>
</div>
</div>
</body></html>
