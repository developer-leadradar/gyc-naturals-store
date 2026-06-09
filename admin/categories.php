<?php
define('GYC_ACCESS', true);
$adminPageTitle = 'Product Categories';
require_once __DIR__ . '/includes/header.php';

$db    = getDB();
$error = '';

// ── POST actions ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'save') {
        $id   = (int)($_POST['id'] ?? 0);
        $name = trim(sanitize($_POST['name'] ?? ''));
        $slug = trim(sanitize($_POST['slug'] ?? ''));
        if (!$slug && $name) $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $data = [
            'name'        => $name,
            'slug'        => $slug,
            'description' => trim(sanitize($_POST['description'] ?? '')),
            'is_active'   => isset($_POST['is_active']) ? 1 : 0,
        ];
        if ($id) {
            $db->update('categories', $data, 'id=?', [$id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Category updated.'];
        } else {
            $data['parent_id']     = null;
            $data['display_order'] = 99;
            $db->insert('categories', $data);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Category created.'];
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        // Unlink products first
        $db->query("UPDATE products SET category_id = NULL WHERE category_id = ?", [$id]);
        $db->query("DELETE FROM categories WHERE id = ?", [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Category deleted.'];
    }
    redirect(SITE_URL . '/admin/categories.php');
    exit;
}

$categories = $db->fetchAll("SELECT c.*, COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
    GROUP BY c.id
    ORDER BY c.display_order ASC, c.name ASC");

// Edit mode
$editId  = (int)($_GET['edit'] ?? 0);
$editCat = $editId ? $db->fetchOne("SELECT * FROM categories WHERE id=?", [$editId]) : null;
?>

<div style="display:grid;grid-template-columns:1fr 380px;gap:1.5rem;align-items:start;">

  <!-- List -->
  <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;">
    <div style="padding:1.25rem 1.5rem;border-bottom:1px solid #E5E7EB;display:flex;align-items:center;justify-content:space-between;">
      <h2 style="font-size:.95rem;font-weight:700;"><?= count($categories) ?> Categories</h2>
    </div>
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr style="background:#F8FAF9;border-bottom:1px solid #E5E7EB;">
          <th style="padding:.65rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Name</th>
          <th style="padding:.65rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Slug</th>
          <th style="padding:.65rem 1.25rem;text-align:center;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Products</th>
          <th style="padding:.65rem 1.25rem;text-align:center;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Active</th>
          <th style="padding:.65rem 1.25rem;text-align:right;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $cat): ?>
        <tr style="border-bottom:1px solid #F0F0F0;" onmouseover="this.style.background='#FAFAFA'" onmouseout="this.style.background=''">
          <td style="padding:.8rem 1.25rem;font-weight:600;font-size:.85rem;"><?= htmlspecialchars($cat['name']) ?></td>
          <td style="padding:.8rem 1.25rem;font-size:.8rem;color:#9CA3AF;"><?= htmlspecialchars($cat['slug']) ?></td>
          <td style="padding:.8rem 1.25rem;text-align:center;font-size:.82rem;"><?= $cat['product_count'] ?></td>
          <td style="padding:.8rem 1.25rem;text-align:center;">
            <span style="font-size:.72rem;font-weight:700;color:<?= $cat['is_active'] ? '#065F46' : '#9CA3AF' ?>;">
              <?= $cat['is_active'] ? '✓ Yes' : 'No' ?>
            </span>
          </td>
          <td style="padding:.8rem 1.25rem;text-align:right;">
            <div style="display:flex;gap:.4rem;justify-content:flex-end;">
              <a href="?edit=<?= $cat['id'] ?>" style="padding:.3rem .6rem;border-radius:6px;background:#EFF6FF;color:#3B82F6;font-size:.75rem;text-decoration:none;">
                <i data-lucide="pencil" style="width:13px;height:13px;"></i>
              </a>
              <?php if ($cat['product_count'] == 0): ?>
              <form method="POST" onsubmit="return confirm('Delete this category?');" style="display:inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                <button type="submit" style="padding:.3rem .6rem;border-radius:6px;background:#FEF2F2;color:#EF4444;border:none;cursor:pointer;">
                  <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
                </button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($categories)): ?>
        <tr><td colspan="5" style="padding:2rem;text-align:center;color:#9CA3AF;">No categories yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Add/Edit form -->
  <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;position:sticky;top:80px;">
    <h2 style="font-size:.95rem;font-weight:700;margin-bottom:1.25rem;"><?= $editCat ? 'Edit Category' : 'New Category' ?></h2>
    <form method="POST">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= $editId ?>">
      <div class="form-group">
        <label class="form-label">Name <span style="color:var(--gyc-terra);">*</span></label>
        <input type="text" name="name" class="form-control" required
               value="<?= htmlspecialchars($editCat['name'] ?? '') ?>"
               placeholder="e.g. Hair Butters"
               oninput="if(!document.getElementById('cat-slug').dataset.manual) document.getElementById('cat-slug').value = this.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'')">
      </div>
      <div class="form-group">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" id="cat-slug" class="form-control"
               value="<?= htmlspecialchars($editCat['slug'] ?? '') ?>"
               placeholder="hair-butters" oninput="this.dataset.manual='true'">
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2" placeholder="Short category description"><?= htmlspecialchars($editCat['description'] ?? '') ?></textarea>
      </div>
      <label style="display:flex;align-items:center;gap:.5rem;font-size:.85rem;cursor:pointer;margin-bottom:1.25rem;">
        <input type="checkbox" name="is_active" <?= ($editCat['is_active'] ?? 1) ? 'checked' : '' ?>> Active
      </label>
      <div style="display:flex;gap:.75rem;">
        <button type="submit" class="btn btn-green" style="flex:1;"><?= $editCat ? 'Save Changes' : 'Create Category' ?></button>
        <?php if ($editCat): ?>
        <a href="<?= SITE_URL ?>/admin/categories.php" class="btn btn-outline-green">Cancel</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
