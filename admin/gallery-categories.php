<?php
define('GYC_ACCESS', true);
$adminPageTitle = 'Gallery Categories';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $id     = (int)($_POST['id'] ?? 0);
    if ($action === 'save') {
        $name    = trim(sanitize($_POST['name'] ?? ''));
        $slug    = trim(sanitize($_POST['slug'] ?? ''));
        $svcType = sanitize($_POST['service_type'] ?? '');
        if (!in_array($svcType, ['braiding','kids','natural','treatment'], true)) $svcType = 'braiding';
        if (!$slug && $name) $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-',$name));
        $data = [
            'name'         => $name,
            'slug'         => $slug,
            'is_active'    => isset($_POST['is_active']) ? 1 : 0,
            'description'  => trim(sanitize($_POST['description'] ?? '')),
            'service_type' => $svcType,
        ];
        if ($id) { $db->update('gallery_categories',$data,'id=?',[$id]); }
        else     { $data['created_at'] = date('Y-m-d H:i:s'); $db->insert('gallery_categories',$data); }
        $_SESSION['flash'] = ['type'=>'success','message'=>'Saved.'];
    } elseif ($action === 'delete' && $id) {
        $db->query("UPDATE gallery_images SET category_id=NULL WHERE category_id=?",[$id]);
        $db->query("DELETE FROM gallery_categories WHERE id=?",[$id]);
        $_SESSION['flash'] = ['type'=>'success','message'=>'Category deleted.'];
    }
    redirect(SITE_URL.'/admin/gallery-categories.php');
    exit;
}

$cats   = $db->fetchAll("SELECT gc.*, COUNT(gi.id) as img_count FROM gallery_categories gc LEFT JOIN gallery_images gi ON gi.category_id=gc.id GROUP BY gc.id ORDER BY gc.service_type ASC, gc.display_order ASC, gc.name");
$editId = (int)($_GET['edit'] ?? 0);
$editCat= $editId ? $db->fetchOne("SELECT * FROM gallery_categories WHERE id=?",[$editId]) : null;

$svcLabels = [
    'braiding'  => 'Braiding & Protective',
    'kids'      => "Kids' Hair",
    'natural'   => 'Natural Styles',
    'treatment' => 'Scalp & Treatments',
];
// Group categories by service
$grouped = ['braiding'=>[], 'kids'=>[], 'natural'=>[], 'treatment'=>[], '_unset'=>[]];
foreach ($cats as $c) {
    $key = $c['service_type'] ?: '_unset';
    if (!isset($grouped[$key])) $grouped[$key] = [];
    $grouped[$key][] = $c;
}
?>
<div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem;align-items:start;">
  <div style="display:flex;flex-direction:column;gap:1rem;">
    <?php foreach (['braiding','kids','natural','treatment'] as $svKey):
      $svCats = $grouped[$svKey] ?? [];
    ?>
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;">
      <div style="padding:1.1rem 1.5rem;border-bottom:1px solid #E5E7EB;display:flex;align-items:center;justify-content:space-between;gap:1rem;">
        <div>
          <div style="font-weight:700;font-size:.95rem;color:var(--gyc-green-700);"><?= htmlspecialchars($svLabels[$svKey]) ?></div>
          <div style="font-size:.74rem;color:#9CA3AF;margin-top:2px;"><?= count($svCats) ?> categor<?= count($svCats) === 1 ? 'y' : 'ies' ?></div>
        </div>
      </div>
      <?php if (empty($svCats)): ?>
      <div style="padding:1.25rem 1.5rem;color:#9CA3AF;font-size:.85rem;">No categories under this service yet.</div>
      <?php else: ?>
      <table style="width:100%;border-collapse:collapse;">
        <thead><tr style="background:#F8FAF9;border-bottom:1px solid #E5E7EB;">
          <th style="padding:.5rem 1.25rem;text-align:left;font-size:.7rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Name</th>
          <th style="padding:.5rem 1.25rem;text-align:center;font-size:.7rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Images</th>
          <th style="padding:.5rem 1.25rem;text-align:center;font-size:.7rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Active</th>
          <th style="padding:.5rem 1.25rem;text-align:right;"></th>
        </tr></thead>
        <tbody>
          <?php foreach ($svCats as $c): ?>
          <tr style="border-bottom:1px solid #F0F0F0;">
            <td style="padding:.65rem 1.25rem;font-weight:600;font-size:.85rem;"><?= htmlspecialchars($c['name']) ?></td>
            <td style="padding:.65rem 1.25rem;text-align:center;font-size:.82rem;"><?= $c['img_count'] ?></td>
            <td style="padding:.65rem 1.25rem;text-align:center;font-size:.76rem;font-weight:700;color:<?= $c['is_active']?'#065F46':'#9CA3AF' ?>;"><?= $c['is_active']?'Yes':'No' ?></td>
            <td style="padding:.65rem 1.25rem;text-align:right;">
              <div style="display:flex;gap:.4rem;justify-content:flex-end;">
                <a href="?edit=<?= $c['id'] ?>" style="padding:.3rem .55rem;border-radius:6px;background:#EFF6FF;color:#3B82F6;font-size:.72rem;text-decoration:none;">Edit</a>
                <?php if ($c['img_count'] == 0): ?>
                <form method="POST" onsubmit="return confirm('Delete?');" style="display:inline;">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $c['id'] ?>">
                  <button type="submit" style="padding:.3rem .55rem;border-radius:6px;background:#FEF2F2;color:#EF4444;border:none;cursor:pointer;font-size:.72rem;">Del</button>
                </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;position:sticky;top:80px;">
    <h2 style="font-size:.92rem;font-weight:700;margin-bottom:1.25rem;"><?= $editCat?'Edit':'New' ?> Category</h2>
    <form method="POST">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= $editId ?>">
      <div class="form-group">
        <label class="form-label">Service <span style="color:var(--gyc-terra);">*</span></label>
        <select name="service_type" class="form-control" required>
          <?php foreach ($svcLabels as $svKey => $svLabel): ?>
          <option value="<?= $svKey ?>" <?= ($editCat['service_type'] ?? 'braiding') === $svKey ? 'selected' : '' ?>><?= htmlspecialchars($svLabel) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editCat['name']??'') ?>"
               oninput="if(!document.getElementById('gc-slug').dataset.manual)document.getElementById('gc-slug').value=this.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'')">
      </div>
      <div class="form-group">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" id="gc-slug" class="form-control" value="<?= htmlspecialchars($editCat['slug']??'') ?>" oninput="this.dataset.manual='true'">
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($editCat['description']??'') ?></textarea>
      </div>
      <label style="display:flex;align-items:center;gap:.5rem;font-size:.84rem;cursor:pointer;margin-bottom:1.25rem;">
        <input type="checkbox" name="is_active" <?= ($editCat['is_active']??1)?'checked':'' ?>> Active
      </label>
      <div style="display:flex;gap:.75rem;">
        <button type="submit" class="btn btn-green" style="flex:1;"><?= $editCat?'Save':'Create' ?></button>
        <?php if ($editCat): ?><a href="<?= SITE_URL ?>/admin/gallery-categories.php" class="btn btn-outline-green">Cancel</a><?php endif; ?>
      </div>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
