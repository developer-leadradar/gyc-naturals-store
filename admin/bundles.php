<?php
define('GYC_ACCESS', true);
$adminPageTitle = 'Product Bundles';
require_once __DIR__ . '/includes/header.php';

$db    = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'save') {
        $name = trim(sanitize($_POST['name'] ?? ''));
        $slug = trim(sanitize($_POST['slug'] ?? ''));
        if (!$slug && $name) $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-',$name));
        $data = [
            'name'                => $name,
            'slug'                => $slug,
            'description'         => trim(sanitize($_POST['description'] ?? '')),
            'discount_percentage' => (float)($_POST['discount_percentage'] ?? 0),
            'is_active'           => isset($_POST['is_active']) ? 1 : 0,
            'is_featured'         => isset($_POST['is_featured']) ? 1 : 0,
            'display_order'       => (int)($_POST['display_order'] ?? 99),
        ];
        if (!empty($_POST['image_url'])) $data['image'] = trim($_POST['image_url']);
        if ($id) {
            $db->update('bundles', $data, 'id=?', [$id]);
            // Update items
            $db->query("DELETE FROM bundle_items WHERE bundle_id=?", [$id]);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $id = $db->insert('bundles', $data);
        }
        // Insert items
        $productIds = $_POST['product_ids'] ?? [];
        $quantities = $_POST['quantities']  ?? [];
        foreach ($productIds as $pi => $pid) {
            $pid = (int)$pid;
            $qty = (int)($quantities[$pi] ?? 1);
            if ($pid > 0 && $qty > 0) {
                $db->insert('bundle_items', ['bundle_id'=>$id,'product_id'=>$pid,'quantity'=>$qty]);
            }
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Bundle saved.'];
        redirect(SITE_URL . '/admin/bundles.php');
        exit;
    } elseif ($action === 'delete' && $id) {
        $db->query("DELETE FROM bundle_items WHERE bundle_id=?", [$id]);
        $db->query("DELETE FROM bundles WHERE id=?", [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Bundle deleted.'];
        redirect(SITE_URL . '/admin/bundles.php');
        exit;
    } elseif ($action === 'toggle' && $id) {
        $cur = (int)($db->fetchOne("SELECT is_active FROM bundles WHERE id=?",[$id])['is_active']??0);
        $db->update('bundles',['is_active'=>$cur?0:1],'id=?',[$id]);
        redirect(SITE_URL . '/admin/bundles.php');
        exit;
    }
}

$showForm   = isset($_GET['edit']);
$editId     = (int)($_GET['edit'] ?? 0);
$editBundle = $editId > 0 ? $db->fetchOne("SELECT * FROM bundles WHERE id=?", [$editId]) : null;
$editItems  = $editId > 0 ? getBundleItems($editId) : [];

$bundles  = $db->fetchAll("SELECT b.*, COUNT(bi.id) as item_count FROM bundles b LEFT JOIN bundle_items bi ON bi.bundle_id=b.id GROUP BY b.id ORDER BY b.display_order ASC, b.created_at DESC");
$products = $db->fetchAll("SELECT id, name, price FROM products WHERE is_active=1 ORDER BY name ASC");
?>

<?php if ($showForm): ?>
<!-- ── EDIT / CREATE FORM ── -->
<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
  <a href="<?= SITE_URL ?>/admin/bundles.php" style="color:#9CA3AF;font-size:.82rem;text-decoration:none;">← Bundles</a>
  <h2 style="font-size:1rem;font-weight:700;"><?= $editBundle ? 'Edit Bundle' : 'New Bundle' ?></h2>
</div>

<form method="POST">
  <input type="hidden" name="action" value="save">
  <input type="hidden" name="id" value="<?= $editId ?>">
  <div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;">
    <div style="display:flex;flex-direction:column;gap:1.5rem;">
      <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
        <h2 style="font-size:.92rem;font-weight:700;margin-bottom:1.25rem;">Bundle Details</h2>
        <div class="form-group"><label class="form-label">Bundle Name <span style="color:var(--gyc-terra);">*</span></label><input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editBundle['name']??'') ?>" placeholder="e.g. Starter Natural Hair Kit"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
          <div class="form-group"><label class="form-label">Slug</label><input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($editBundle['slug']??'') ?>"></div>
          <div class="form-group"><label class="form-label">Discount %</label><input type="number" name="discount_percentage" class="form-control" min="0" max="90" step="0.5" value="<?= htmlspecialchars($editBundle['discount_percentage']??10) ?>"></div>
        </div>
        <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($editBundle['description']??'') ?></textarea></div>
        <div class="form-group"><label class="form-label">Image URL</label><input type="url" name="image_url" class="form-control" value="<?= htmlspecialchars($editBundle['image']??'') ?>" placeholder="https://…"></div>
      </div>
      <!-- Bundle items -->
      <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
          <h2 style="font-size:.92rem;font-weight:700;">Bundle Items</h2>
          <button type="button" onclick="addBundleRow()" class="btn btn-outline-green btn-sm">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> Add Item
          </button>
        </div>
        <div id="bundle-items">
          <?php foreach ($editItems as $bi): ?>
          <div class="bundle-row" style="display:flex;gap:.75rem;align-items:center;margin-bottom:.75rem;">
            <select name="product_ids[]" class="form-control" style="flex:1;">
              <option value="">— Select Product —</option>
              <?php foreach ($products as $p): ?>
              <option value="<?= $p['id'] ?>" <?= $bi['product_id']==$p['id']?'selected':'' ?>><?= htmlspecialchars($p['name']) ?> — <?= formatPrice($p['price']) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="number" name="quantities[]" class="form-control" min="1" value="<?= $bi['quantity'] ?>" style="width:70px;">
            <button type="button" onclick="this.closest('.bundle-row').remove()" style="background:none;border:none;cursor:pointer;color:#EF4444;padding:.25rem;">✕</button>
          </div>
          <?php endforeach; ?>
          <?php if (empty($editItems)): ?>
          <div class="bundle-row" style="display:flex;gap:.75rem;align-items:center;margin-bottom:.75rem;">
            <select name="product_ids[]" class="form-control" style="flex:1;">
              <option value="">— Select Product —</option>
              <?php foreach ($products as $p): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> — <?= formatPrice($p['price']) ?></option>
              <?php endforeach; ?>
            </select>
            <input type="number" name="quantities[]" class="form-control" min="1" value="1" style="width:70px;">
            <button type="button" onclick="this.closest('.bundle-row').remove()" style="background:none;border:none;cursor:pointer;color:#EF4444;padding:.25rem;">✕</button>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <!-- Sidebar -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;position:sticky;top:80px;">
      <h2 style="font-size:.92rem;font-weight:700;margin-bottom:1.25rem;">Publish</h2>
      <label style="display:flex;align-items:center;gap:.5rem;font-size:.84rem;cursor:pointer;margin-bottom:.6rem;"><input type="checkbox" name="is_active" <?= ($editBundle['is_active']??1)?'checked':'' ?>> Active</label>
      <label style="display:flex;align-items:center;gap:.5rem;font-size:.84rem;cursor:pointer;margin-bottom:.6rem;"><input type="checkbox" name="is_featured" <?= ($editBundle['is_featured']??0)?'checked':'' ?>> Featured</label>
      <div class="form-group" style="margin-top:.75rem;"><label class="form-label">Display Order</label><input type="number" name="display_order" class="form-control" value="<?= $editBundle['display_order']??99 ?>"></div>
      <button type="submit" class="btn btn-green w-full" style="margin-top:.75rem;">Save Bundle</button>
    </div>
  </div>
</form>

<script>
var productOptions = `<?php foreach($products as $p) echo '<option value="'.$p['id'].'">'.htmlspecialchars($p['name']).' — '.formatPrice($p['price']).'</option>'; ?>`;
function addBundleRow() {
  var row = document.createElement('div');
  row.className = 'bundle-row';
  row.style.cssText = 'display:flex;gap:.75rem;align-items:center;margin-bottom:.75rem;';
  row.innerHTML = '<select name="product_ids[]" class="form-control" style="flex:1;"><option value="">— Select Product —</option>' + productOptions + '</select>'
    + '<input type="number" name="quantities[]" class="form-control" min="1" value="1" style="width:70px;">'
    + '<button type="button" onclick="this.closest(\'.bundle-row\').remove()" style="background:none;border:none;cursor:pointer;color:#EF4444;padding:.25rem;">✕</button>';
  document.getElementById('bundle-items').appendChild(row);
}
</script>

<?php else: ?>
<!-- ── LIST ── -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
  <span style="font-size:.85rem;color:#9CA3AF;"><?= count($bundles) ?> bundle<?= count($bundles)!==1?'s':'' ?></span>
  <a href="?edit=0" class="btn btn-green btn-sm"><i data-lucide="plus" style="width:15px;height:15px;"></i> New Bundle</a>
</div>
<div style="display:flex;flex-direction:column;gap:1rem;">
  <?php foreach ($bundles as $b):
    $bItems = getBundleItems($b['id']);
    $bPrice = getBundlePrice($b['id']);
  ?>
  <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;">
    <div style="display:flex;align-items:center;gap:1.25rem;padding:1.25rem 1.5rem;">
      <?php if ($b['image']): ?>
      <img src="<?= htmlspecialchars($b['image']) ?>" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:8px;flex-shrink:0;border:1px solid #E5E7EB;">
      <?php endif; ?>
      <div style="flex:1;">
        <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.2rem;">
          <span style="font-weight:700;font-size:.92rem;"><?= htmlspecialchars($b['name']) ?></span>
          <?php if (!$b['is_active']): ?><span style="font-size:.7rem;font-weight:700;background:#F3F4F6;color:#9CA3AF;padding:.15rem .4rem;border-radius:4px;">Hidden</span><?php endif; ?>
          <?php if ($b['is_featured']): ?><span style="font-size:.7rem;font-weight:700;background:#FEF9EC;color:var(--gyc-gold);padding:.15rem .4rem;border-radius:4px;">Featured</span><?php endif; ?>
        </div>
        <div style="font-size:.78rem;color:#9CA3AF;"><?= count($bItems) ?> items · <?= $b['discount_percentage'] ?>% discount · Save <?= formatPrice($bPrice['discount']) ?></div>
        <div style="display:flex;gap:.35rem;flex-wrap:wrap;margin-top:.4rem;">
          <?php foreach (array_slice($bItems,0,4) as $bi): ?>
          <span style="font-size:.72rem;background:var(--gyc-green-100);color:var(--gyc-green-700);padding:.15rem .4rem;border-radius:4px;"><?= htmlspecialchars(substr($bi['name'],0,20)) ?></span>
          <?php endforeach; ?>
          <?php if (count($bItems)>4): ?><span style="font-size:.72rem;color:#9CA3AF;">+<?= count($bItems)-4 ?> more</span><?php endif; ?>
        </div>
      </div>
      <div style="display:flex;gap:.5rem;flex-shrink:0;">
        <a href="?edit=<?= $b['id'] ?>" style="padding:.4rem .75rem;border-radius:6px;background:#EFF6FF;color:#3B82F6;font-size:.78rem;text-decoration:none;">Edit</a>
        <form method="POST" style="display:inline;">
          <input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $b['id'] ?>">
          <button type="submit" style="padding:.4rem .75rem;border-radius:6px;background:<?= $b['is_active']?'#ECFDF5':'#F9FAFB' ?>;color:<?= $b['is_active']?'#065F46':'#9CA3AF' ?>;border:none;cursor:pointer;font-size:.78rem;"><?= $b['is_active']?'Hide':'Show' ?></button>
        </form>
        <form method="POST" onsubmit="return confirm('Delete bundle?');" style="display:inline;">
          <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $b['id'] ?>">
          <button type="submit" style="padding:.4rem .75rem;border-radius:6px;background:#FEF2F2;color:#EF4444;border:none;cursor:pointer;font-size:.78rem;">Delete</button>
        </form>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (empty($bundles)): ?>
  <div style="text-align:center;padding:4rem;background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;color:#9CA3AF;">
    No bundles yet. <a href="?edit=0" style="color:var(--gyc-green-600);">Create one →</a>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
