<?php
define('GYC_ACCESS', true);
$adminPageTitle = 'Products';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// ── Handle actions ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'toggle_status') {
        $id  = (int)($_POST['id'] ?? 0);
        $cur = (int)($db->fetchOne("SELECT is_active FROM products WHERE id=?", [$id])['is_active'] ?? 0);
        $db->update('products', ['is_active' => $cur ? 0 : 1], 'id=?', [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Product status updated.'];
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $db->update('products', ['is_active' => 0], 'id=?', [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Product deactivated.'];
    }
    redirect(SITE_URL . '/admin/products.php');
    exit;
}

// ── Filters ──
$search   = sanitize($_GET['q']        ?? '');
$catFilter= sanitize($_GET['category'] ?? '');
$stock    = sanitize($_GET['stock']    ?? '');
$limit    = 20;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $limit;

$sql    = "SELECT p.*, c.name as category_name
           FROM products p
           LEFT JOIN categories c ON p.category_id = c.id
           WHERE 1=1";
$params = [];
if ($search) { $sql .= " AND (p.name LIKE ? OR p.sku LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($catFilter) { $sql .= " AND p.category_id = ?"; $params[] = (int)$catFilter; }
if ($stock === 'low')  { $sql .= " AND p.stock_quantity <= 5 AND p.is_active = 1"; }
if ($stock === 'zero') { $sql .= " AND p.stock_quantity = 0"; }

$total     = (int)($db->fetchOne(str_replace("SELECT p.*, c.name as category_name", "SELECT COUNT(*) c", $sql), $params)['c'] ?? 0);
$sql      .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$params[]  = $limit;
$params[]  = $offset;
$products  = $db->fetchAll($sql, $params);

$totalPages = (int)ceil($total / $limit);
$categories = getAllCategories();
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
  <div>
    <span style="font-size:.85rem;color:#9CA3AF;"><?= $total ?> product<?= $total !== 1 ? 's' : '' ?></span>
  </div>
  <a href="<?= SITE_URL ?>/admin/add-product.php" class="btn btn-green btn-sm">
    <i data-lucide="plus" style="width:15px;height:15px;"></i>
    Add Product
  </a>
</div>

<!-- Filters -->
<div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.25rem;margin-bottom:1.5rem;display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;">
  <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;width:100%;">
    <div style="flex:1;min-width:200px;">
      <label style="display:block;font-size:.75rem;font-weight:600;color:#9CA3AF;margin-bottom:.3rem;text-transform:uppercase;">Search</label>
      <input type="text" name="q" class="form-control" placeholder="Product name or SKU…" value="<?= htmlspecialchars($search) ?>" style="height:36px;padding:.4rem .75rem;">
    </div>
    <div style="min-width:160px;">
      <label style="display:block;font-size:.75rem;font-weight:600;color:#9CA3AF;margin-bottom:.3rem;text-transform:uppercase;">Category</label>
      <select name="category" class="form-control" style="height:36px;padding:.4rem .75rem;">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>" <?= $catFilter == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="min-width:140px;">
      <label style="display:block;font-size:.75rem;font-weight:600;color:#9CA3AF;margin-bottom:.3rem;text-transform:uppercase;">Stock</label>
      <select name="stock" class="form-control" style="height:36px;padding:.4rem .75rem;">
        <option value="">All</option>
        <option value="low"  <?= $stock === 'low'  ? 'selected' : '' ?>>Low Stock (≤5)</option>
        <option value="zero" <?= $stock === 'zero' ? 'selected' : '' ?>>Out of Stock</option>
      </select>
    </div>
    <button type="submit" class="btn btn-outline-green btn-sm" style="height:36px;">Filter</button>
    <?php if ($search || $catFilter || $stock): ?>
    <a href="<?= SITE_URL ?>/admin/products.php" class="btn btn-sm" style="height:36px;background:#F3F4F6;color:#374151;">Clear</a>
    <?php endif; ?>
  </form>
</div>

<!-- Products table -->
<div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;">
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="background:#F8FAF9;border-bottom:1px solid #E5E7EB;">
        <th style="padding:.75rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;width:50%;">Product</th>
        <th style="padding:.75rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Category</th>
        <th style="padding:.75rem 1.25rem;text-align:right;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Price</th>
        <th style="padding:.75rem 1.25rem;text-align:center;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Stock</th>
        <th style="padding:.75rem 1.25rem;text-align:center;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Status</th>
        <th style="padding:.75rem 1.25rem;text-align:right;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $prod): ?>
      <tr style="border-bottom:1px solid #F0F0F0;" onmouseover="this.style.background='#FAFAFA'" onmouseout="this.style.background=''">
        <td style="padding:.85rem 1.25rem;">
          <div style="display:flex;align-items:center;gap:.85rem;">
            <img src="<?= htmlspecialchars($prod['image']) ?>" alt="" style="width:44px;height:44px;border-radius:8px;object-fit:cover;border:1px solid #E5E7EB;">
            <div>
              <div style="font-weight:600;font-size:.85rem;color:#1C1F1A;"><?= htmlspecialchars($prod['name']) ?></div>
              <div style="font-size:.72rem;color:#9CA3AF;">SKU: <?= htmlspecialchars($prod['sku'] ?? '—') ?></div>
            </div>
          </div>
        </td>
        <td style="padding:.85rem 1.25rem;font-size:.82rem;color:#374151;"><?= htmlspecialchars($prod['category_name'] ?? '—') ?></td>
        <td style="padding:.85rem 1.25rem;text-align:right;font-size:.85rem;font-weight:600;color:#1C1F1A;"><?= formatPrice($prod['price']) ?></td>
        <td style="padding:.85rem 1.25rem;text-align:center;">
          <?php
          $stockQty = (int)$prod['stock_quantity'];
          $stockColor = $stockQty === 0 ? '#EF4444' : ($stockQty <= 5 ? '#F59E0B' : '#10B981');
          ?>
          <span style="font-size:.78rem;font-weight:700;color:<?= $stockColor ?>;"><?= $stockQty ?></span>
        </td>
        <td style="padding:.85rem 1.25rem;text-align:center;">
          <form method="POST" style="display:inline;">
            <input type="hidden" name="action" value="toggle_status">
            <input type="hidden" name="id" value="<?= $prod['id'] ?>">
            <button type="submit" style="background:none;border:none;cursor:pointer;font-size:.72rem;font-weight:700;padding:.2rem .5rem;border-radius:20px;background:<?= $prod['is_active'] ? '#ECFDF5' : '#F9FAFB' ?>;color:<?= $prod['is_active'] ? '#065F46' : '#9CA3AF' ?>;">
              <?= $prod['is_active'] ? 'Active' : 'Hidden' ?>
            </button>
          </form>
        </td>
        <td style="padding:.85rem 1.25rem;text-align:right;">
          <div style="display:flex;gap:.5rem;justify-content:flex-end;">
            <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($prod['slug']) ?>" target="_blank"
               style="padding:.3rem .6rem;border-radius:6px;background:#F3F4F6;color:#374151;font-size:.75rem;text-decoration:none;" title="Preview">
              <i data-lucide="eye" style="width:14px;height:14px;"></i>
            </a>
            <a href="<?= SITE_URL ?>/admin/add-product.php?id=<?= $prod['id'] ?>"
               style="padding:.3rem .6rem;border-radius:6px;background:#EFF6FF;color:#3B82F6;font-size:.75rem;text-decoration:none;" title="Edit">
              <i data-lucide="pencil" style="width:14px;height:14px;"></i>
            </a>
            <form method="POST" onsubmit="return confirm('Deactivate this product?');" style="display:inline;">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $prod['id'] ?>">
              <button type="submit" style="padding:.3rem .6rem;border-radius:6px;background:#FEF2F2;color:#EF4444;border:none;cursor:pointer;" title="Deactivate">
                <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
              </button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($products)): ?>
      <tr><td colspan="6" style="padding:3rem;text-align:center;color:#9CA3AF;">
        No products found. <a href="<?= SITE_URL ?>/admin/add-product.php" style="color:var(--gyc-green-600);">Add one →</a>
      </td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div style="display:flex;justify-content:center;gap:.5rem;margin-top:1.5rem;">
  <?php for ($p = 1; $p <= $totalPages; $p++): ?>
  <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"
     class="btn btn-sm <?= $p === $page ? 'btn-green' : 'btn-outline-green' ?>"><?= $p ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
