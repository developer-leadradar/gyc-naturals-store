<?php
define('GYC_ACCESS', true);
$adminPageTitle = 'Gallery Images';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// ── Actions ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $db->query("DELETE FROM gallery_images WHERE id = ?", [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Image deleted.'];
    } elseif ($action === 'toggle' && $id) {
        $cur = (int)($db->fetchOne("SELECT is_active FROM gallery_images WHERE id=?", [$id])['is_active'] ?? 0);
        $db->update('gallery_images', ['is_active' => $cur ? 0 : 1], 'id=?', [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Status updated.'];
    } elseif ($action === 'toggle_featured' && $id) {
        $cur = (int)($db->fetchOne("SELECT is_featured FROM gallery_images WHERE id=?", [$id])['is_featured'] ?? 0);
        $db->update('gallery_images', ['is_featured' => $cur ? 0 : 1], 'id=?', [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Featured status updated.'];
    }
    redirect(SITE_URL . '/admin/gallery.php');
    exit;
}

// ── Filters ──
$search    = sanitize($_GET['q']        ?? '');
$catFilter = sanitize($_GET['category'] ?? '');
$limit     = 24;
$page      = max(1, (int)($_GET['page'] ?? 1));
$offset    = ($page - 1) * $limit;

$sql    = "SELECT gi.*, gc.name as category_name
           FROM gallery_images gi
           LEFT JOIN gallery_categories gc ON gi.category_id = gc.id
           WHERE 1=1";
$params = [];
if ($search)    { $sql .= " AND (gi.title LIKE ? OR gi.tags LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($catFilter) { $sql .= " AND gi.category_id = ?"; $params[] = (int)$catFilter; }

$total      = (int)($db->fetchOne(str_replace("SELECT gi.*, gc.name as category_name", "SELECT COUNT(*) AS total", $sql), $params)['total'] ?? 0);
$sql       .= " ORDER BY gi.created_at DESC LIMIT ? OFFSET ?";
$params[]   = $limit; $params[] = $offset;
$images     = $db->fetchAll($sql, $params);
$totalPages = (int)ceil($total / $limit);
$galCats    = $db->fetchAll("SELECT * FROM gallery_categories ORDER BY name");
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
  <span style="font-size:.85rem;color:#9CA3AF;"><?= $total ?> image<?= $total !== 1 ? 's' : '' ?></span>
  <a href="<?= SITE_URL ?>/admin/add-gallery.php" class="btn btn-green btn-sm">
    <i data-lucide="plus" style="width:15px;height:15px;"></i> Add Style Image
  </a>
</div>

<!-- Filters -->
<form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.5rem;">
  <input type="text" name="q" class="form-control" placeholder="Search title or tags…" value="<?= htmlspecialchars($search) ?>" style="height:34px;padding:.35rem .7rem;width:200px;">
  <select name="category" class="form-control" style="height:34px;padding:.35rem .7rem;">
    <option value="">All Categories</option>
    <?php foreach ($galCats as $gc): ?>
    <option value="<?= $gc['id'] ?>" <?= $catFilter == $gc['id'] ? 'selected' : '' ?>><?= htmlspecialchars($gc['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-outline-green btn-sm" style="height:34px;">Filter</button>
  <?php if ($search || $catFilter): ?>
  <a href="<?= SITE_URL ?>/admin/gallery.php" class="btn btn-sm" style="height:34px;background:#F3F4F6;color:#374151;">Clear</a>
  <?php endif; ?>
</form>

<!-- Grid -->
<?php if (empty($images)): ?>
<div style="text-align:center;padding:4rem;background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;color:#9CA3AF;">
  <i data-lucide="image" style="width:40px;height:40px;opacity:.3;margin-bottom:1rem;"></i>
  <p>No images yet. <a href="<?= SITE_URL ?>/admin/add-gallery.php" style="color:var(--gyc-green-600);">Add your first →</a></p>
</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1.25rem;">
  <?php foreach ($images as $img): ?>
  <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;position:relative;">
    <!-- Image -->
    <div style="aspect-ratio:3/4;overflow:hidden;position:relative;">
      <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="<?= htmlspecialchars($img['title']) ?>"
           style="width:100%;height:100%;object-fit:cover;">
      <!-- Badges -->
      <div style="position:absolute;top:.5rem;left:.5rem;display:flex;gap:.3rem;flex-wrap:wrap;">
        <?php if (!$img['is_active']): ?>
        <span style="font-size:.65rem;font-weight:700;background:rgba(0,0,0,.5);color:#fff;padding:.15rem .4rem;border-radius:4px;">Hidden</span>
        <?php endif; ?>
        <?php if ($img['is_featured']): ?>
        <span style="font-size:.65rem;font-weight:700;background:var(--gyc-gold);color:#fff;padding:.15rem .4rem;border-radius:4px;">Featured</span>
        <?php endif; ?>
      </div>
    </div>
    <!-- Info -->
    <div style="padding:.75rem;">
      <div style="font-weight:600;font-size:.82rem;color:#1C1F1A;margin-bottom:.15rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($img['title']) ?></div>
      <?php if ($img['category_name']): ?>
      <div style="font-size:.72rem;color:#9CA3AF;margin-bottom:.5rem;"><?= htmlspecialchars($img['category_name']) ?></div>
      <?php endif; ?>
      <?php if ($img['price_from']): ?>
      <div style="font-size:.78rem;font-weight:600;color:var(--gyc-green-700);margin-bottom:.5rem;">From <?= formatPrice($img['price_from']) ?></div>
      <?php endif; ?>
      <!-- Actions -->
      <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
        <a href="<?= SITE_URL ?>/admin/add-gallery.php?id=<?= $img['id'] ?>"
           style="flex:1;padding:.3rem;border-radius:6px;background:#EFF6FF;color:#3B82F6;font-size:.72rem;text-align:center;text-decoration:none;">Edit</a>
        <form method="POST" style="display:contents;">
          <input type="hidden" name="action" value="toggle">
          <input type="hidden" name="id" value="<?= $img['id'] ?>">
          <button type="submit" style="flex:1;padding:.3rem;border-radius:6px;background:<?= $img['is_active'] ? '#ECFDF5' : '#F9FAFB' ?>;color:<?= $img['is_active'] ? '#065F46' : '#9CA3AF' ?>;font-size:.72rem;border:none;cursor:pointer;">
            <?= $img['is_active'] ? 'Hide' : 'Show' ?>
          </button>
        </form>
        <form method="POST" onsubmit="return confirm('Delete this image?');" style="display:contents;">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= $img['id'] ?>">
          <button type="submit" style="padding:.3rem .45rem;border-radius:6px;background:#FEF2F2;color:#EF4444;font-size:.72rem;border:none;cursor:pointer;">✕</button>
        </form>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

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
