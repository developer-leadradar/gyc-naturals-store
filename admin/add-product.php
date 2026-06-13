<?php
define('GYC_ACCESS', true);
$editId = (int)($_GET['id'] ?? 0);
$isEdit = $editId > 0;
$adminPageTitle = $isEdit ? 'Edit Product' : 'Add Product';
require_once __DIR__ . '/includes/header.php';

$db   = getDB();
$prod = $isEdit ? $db->fetchOne("SELECT * FROM products WHERE id = ?", [$editId]) : null;
if ($isEdit && !$prod) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Product not found.'];
    redirect(SITE_URL . '/admin/products.php');
    exit;
}

$categories = getAllCategories();
$error      = '';
$success    = '';

function resizeToDataUrl($tmpFile, $mime, $maxDim = 1200, $quality = 82) {
    if (!extension_loaded('gd') || !function_exists('imagecreatefromjpeg')) {
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($tmpFile));
    }
    switch ($mime) {
        case 'image/jpeg': case 'image/jpg': $src = @imagecreatefromjpeg($tmpFile); break;
        case 'image/png':  $src = @imagecreatefrompng($tmpFile); break;
        case 'image/webp': $src = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmpFile) : false; break;
        default: $src = false;
    }
    if (!$src) return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($tmpFile));
    $origW = imagesx($src); $origH = imagesy($src);
    $ratio = min($maxDim / $origW, $maxDim / $origH, 1.0);
    $newW = max(1,(int)round($origW*$ratio)); $newH = max(1,(int)round($origH*$ratio));
    $dst = imagecreatetruecolor($newW, $newH);
    $white = imagecolorallocate($dst,255,255,255); imagefill($dst,0,0,$white);
    imagecopyresampled($dst,$src,0,0,0,0,$newW,$newH,$origW,$origH);
    imagedestroy($src);
    ob_start(); imagejpeg($dst, null, $quality); $data = ob_get_clean();
    imagedestroy($dst);
    return 'data:image/jpeg;base64,' . base64_encode($data);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'           => trim(sanitize($_POST['name']           ?? '')),
        'slug'           => trim(sanitize($_POST['slug']           ?? '')),
        'sku'            => trim(sanitize($_POST['sku']            ?? '')),
        'description'    => trim($_POST['description']             ?? ''),
        'short_desc'     => trim(sanitize($_POST['short_desc']     ?? '')),
        'price'          => (float)($_POST['price']                ?? 0),
        'compare_price'  => $_POST['compare_price'] !== '' ? (float)$_POST['compare_price'] : null,
        'category_id'    => (int)($_POST['category_id']            ?? 0) ?: null,
        'hair_type'      => trim(sanitize($_POST['hair_type']      ?? '')),
        'concern'        => trim(sanitize($_POST['concern']        ?? '')),
        'product_type'   => trim(sanitize($_POST['product_type']   ?? '')),
        'key_ingredient' => trim(sanitize($_POST['key_ingredient'] ?? '')),
        'volume_ml'      => $_POST['volume_ml'] !== '' ? (int)$_POST['volume_ml'] : null,
        'stock_quantity' => (int)($_POST['stock_quantity']         ?? 0),
        'is_active'      => isset($_POST['is_active']) ? 1 : 0,
        'is_featured'    => isset($_POST['is_featured']) ? 1 : 0,
    ];

    if (!$data['slug'] && $data['name']) {
        $data['slug'] = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $data['name']));
        $data['slug'] = trim($data['slug'], '-');
    }

    // Handle image upload (resize + base64 — Vercel has no writable filesystem)
    $imageUrl = $prod['image'] ?? '';
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $mime     = mime_content_type($_FILES['image']['tmp_name']) ?: 'image/jpeg';
        $imageUrl = resizeToDataUrl($_FILES['image']['tmp_name'], $mime);
    } elseif (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Image upload failed (code ' . $_FILES['image']['error'] . '). Try a smaller file or paste a URL.';
    } elseif (!empty($_POST['image_url'])) {
        $imageUrl = trim($_POST['image_url']);
    }
    $data['image'] = $imageUrl;

    if (!$data['name']) {
        $error = 'Product name is required.';
    } elseif ($data['price'] <= 0) {
        $error = 'Price must be greater than zero.';
    } else {
        if ($isEdit) {
            $db->update('products', $data, 'id=?', [$editId]);
            $success = 'Product updated successfully.';
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $newId = $db->insert('products', $data);
            $success = 'Product created successfully.';
            $editId  = $newId;
            $isEdit  = true;
            $prod    = $db->fetchOne("SELECT * FROM products WHERE id=?", [$editId]);
        }
    }
}
?>

<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
  <a href="<?= SITE_URL ?>/admin/products.php" style="color:#9CA3AF;text-decoration:none;font-size:.82rem;">← Products</a>
</div>

<?php if ($error): ?>
<div class="alert alert-danger" style="margin-bottom:1.5rem;"><?= htmlspecialchars($error) ?></div>
<?php elseif ($success): ?>
<div class="alert alert-success" style="margin-bottom:1.5rem;"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
  <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start;">

    <!-- Main column -->
    <div style="display:flex;flex-direction:column;gap:1.5rem;">

      <!-- Basic info -->
      <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
        <h2 style="font-size:.92rem;font-weight:700;margin-bottom:1.25rem;color:#1C1F1A;">Basic Information</h2>
        <div class="form-group">
          <label class="form-label">Product Name <span style="color:var(--gyc-terra);">*</span></label>
          <input type="text" name="name" class="form-control" required
                 value="<?= htmlspecialchars($prod['name'] ?? '') ?>"
                 placeholder="e.g. Baobab Hair Butter 200ml"
                 oninput="autoSlug(this.value)">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
          <div class="form-group">
            <label class="form-label">URL Slug</label>
            <input type="text" name="slug" id="slug-input" class="form-control"
                   value="<?= htmlspecialchars($prod['slug'] ?? '') ?>"
                   placeholder="baobab-hair-butter">
          </div>
          <div class="form-group">
            <label class="form-label">SKU</label>
            <input type="text" name="sku" class="form-control"
                   value="<?= htmlspecialchars($prod['sku'] ?? '') ?>"
                   placeholder="GYC-001">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Short Description</label>
          <input type="text" name="short_desc" class="form-control" maxlength="180"
                 value="<?= htmlspecialchars($prod['short_desc'] ?? '') ?>"
                 placeholder="One sentence summary shown on product cards">
        </div>
        <div class="form-group">
          <label class="form-label">Full Description (HTML allowed)</label>
          <textarea name="description" class="form-control" rows="8" placeholder="Detailed product description…"><?= htmlspecialchars($prod['description'] ?? '') ?></textarea>
        </div>
      </div>

      <!-- Hair-specific details -->
      <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
        <h2 style="font-size:.92rem;font-weight:700;margin-bottom:1.25rem;color:#1C1F1A;">Hair Product Details</h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
          <div class="form-group">
            <label class="form-label">Hair Type</label>
            <input type="text" name="hair_type" class="form-control"
                   value="<?= htmlspecialchars($prod['hair_type'] ?? '') ?>"
                   placeholder="e.g. 4C, 4B, All Types">
          </div>
          <div class="form-group">
            <label class="form-label">Concern</label>
            <input type="text" name="concern" class="form-control"
                   value="<?= htmlspecialchars($prod['concern'] ?? '') ?>"
                   placeholder="e.g. Dryness, Breakage, Growth">
          </div>
          <div class="form-group">
            <label class="form-label">Product Type</label>
            <select name="product_type" class="form-control">
              <?php foreach (['','Butter','Oil','Serum','Shampoo','Conditioner','Leave-In','Spray','Mask','Bar','Blend','Clothing'] as $pt): ?>
              <option value="<?= $pt ?>" <?= ($prod['product_type'] ?? '') === $pt ? 'selected' : '' ?>><?= $pt ?: '— Select —' ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Key Ingredient</label>
            <input type="text" name="key_ingredient" class="form-control"
                   value="<?= htmlspecialchars($prod['key_ingredient'] ?? '') ?>"
                   placeholder="e.g. Baobab Oil, Black Seed">
          </div>
          <div class="form-group">
            <label class="form-label">Volume (ml)</label>
            <input type="number" name="volume_ml" class="form-control" min="0"
                   value="<?= htmlspecialchars($prod['volume_ml'] ?? '') ?>"
                   placeholder="200">
          </div>
        </div>
      </div>

      <!-- SEO -->
      <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
        <h2 style="font-size:.92rem;font-weight:700;margin-bottom:1.25rem;color:#1C1F1A;">SEO</h2>
        <div class="form-group">
          <label class="form-label">Meta Title</label>
          <input type="text" name="meta_title" class="form-control" maxlength="70"
                 value="<?= htmlspecialchars($prod['meta_title'] ?? '') ?>"
                 placeholder="Leave blank to use product name">
        </div>
        <div class="form-group">
          <label class="form-label">Meta Description</label>
          <textarea name="meta_description" class="form-control" rows="2" maxlength="160"
                    placeholder="Leave blank to use short description"><?= htmlspecialchars($prod['meta_description'] ?? '') ?></textarea>
        </div>
      </div>

    </div>

    <!-- Sidebar column -->
    <div style="display:flex;flex-direction:column;gap:1.5rem;">

      <!-- Publish -->
      <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
        <h2 style="font-size:.92rem;font-weight:700;margin-bottom:1.25rem;">Publish</h2>
        <label style="display:flex;align-items:center;gap:.6rem;font-size:.85rem;cursor:pointer;margin-bottom:.75rem;">
          <input type="checkbox" name="is_active" <?= ($prod['is_active'] ?? 1) ? 'checked' : '' ?>> Active (visible in shop)
        </label>
        <label style="display:flex;align-items:center;gap:.6rem;font-size:.85rem;cursor:pointer;">
          <input type="checkbox" name="is_featured" <?= ($prod['is_featured'] ?? 0) ? 'checked' : '' ?>> Featured (shown on homepage)
        </label>
        <hr style="margin:1.25rem 0;border-color:#E5E7EB;">
        <button type="submit" class="btn btn-green w-full"><?= $isEdit ? 'Save Changes' : 'Create Product' ?></button>
        <?php if ($isEdit): ?>
        <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($prod['slug']) ?>" target="_blank"
           class="btn btn-outline-green w-full" style="margin-top:.6rem;font-size:.82rem;">Preview in Store</a>
        <?php endif; ?>
      </div>

      <!-- Pricing -->
      <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
        <h2 style="font-size:.92rem;font-weight:700;margin-bottom:1.25rem;">Pricing</h2>
        <div class="form-group">
          <label class="form-label">Price (₦) <span style="color:var(--gyc-terra);">*</span></label>
          <input type="number" name="price" class="form-control" min="0" step="0.01" required
                 value="<?= htmlspecialchars($prod['price'] ?? '') ?>" placeholder="5000">
        </div>
        <div class="form-group">
          <label class="form-label">Compare Price (₦) <span style="font-size:.72rem;color:#9CA3AF;">strikethrough</span></label>
          <input type="number" name="compare_price" class="form-control" min="0" step="0.01"
                 value="<?= htmlspecialchars($prod['compare_price'] ?? '') ?>" placeholder="7000">
        </div>
      </div>

      <!-- Category -->
      <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
        <h2 style="font-size:.92rem;font-weight:700;margin-bottom:1.25rem;">Category</h2>
        <select name="category_id" class="form-control">
          <option value="">Uncategorised</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= ($prod['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Stock -->
      <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
        <h2 style="font-size:.92rem;font-weight:700;margin-bottom:1.25rem;">Inventory</h2>
        <div class="form-group">
          <label class="form-label">Stock Quantity</label>
          <input type="number" name="stock_quantity" class="form-control" min="0"
                 value="<?= htmlspecialchars($prod['stock_quantity'] ?? 0) ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Low Stock Alert at</label>
          <input type="number" name="low_stock_alert" class="form-control" min="1"
                 value="<?= htmlspecialchars($prod['low_stock_alert'] ?? 5) ?>">
        </div>
      </div>

      <!-- Image -->
      <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
        <h2 style="font-size:.92rem;font-weight:700;margin-bottom:1.25rem;">Product Image</h2>
        <?php if (!empty($prod['image'])): ?>
        <img src="<?= htmlspecialchars($prod['image']) ?>" alt="" style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:8px;margin-bottom:.75rem;">
        <?php endif; ?>
        <?php $prodImgIsData = strpos($prod['image'] ?? '', 'data:') === 0; ?>
        <div class="form-group">
          <label class="form-label">Upload Image</label>
          <input type="file" name="image" id="prod-image-input" class="form-control" accept="image/*">
          <div id="prod-image-status" style="font-size:.74rem;color:#6B7280;margin-top:.3rem;display:none;"></div>
        </div>
        <div class="form-group" style="margin-top:.5rem;">
          <label class="form-label">Or paste URL</label>
          <input type="url" name="image_url" class="form-control" placeholder="https://…"
                 value="<?= $prodImgIsData ? '' : htmlspecialchars($prod['image'] ?? '') ?>">
        </div>
        <p style="font-size:.72rem;color:#9CA3AF;margin-top:.4rem;">Large photos auto-resize to 1200px before upload.</p>
      </div>

    </div>
  </div>
</form>

<script>
function autoSlug(val) {
  const slug = document.getElementById('slug-input');
  if (slug && !slug.dataset.manual) {
    slug.value = val.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
  }
}
document.getElementById('slug-input').addEventListener('input', function() {
  this.dataset.manual = 'true';
});

// Client-side image resize to keep under Vercel's 4.5MB body limit
(function() {
  var input = document.getElementById('prod-image-input');
  if (!input) return;
  input.addEventListener('change', function() {
    var file = input.files[0];
    if (!file || !file.type.startsWith('image/')) return;
    var status = document.getElementById('prod-image-status');
    if (status) { status.style.display = 'block'; status.textContent = 'Processing image…'; status.style.color = '#6B7280'; }
    var reader = new FileReader();
    reader.onload = function(ev) {
      var img = new Image();
      img.onload = function() {
        var maxDim = 1200, w = img.width, h = img.height;
        var ratio = Math.min(maxDim / w, maxDim / h, 1);
        w = Math.round(w * ratio); h = Math.round(h * ratio);
        var canvas = document.createElement('canvas');
        canvas.width = w; canvas.height = h;
        canvas.getContext('2d').drawImage(img, 0, 0, w, h);
        canvas.toBlob(function(blob) {
          if (!blob) { if (status) { status.textContent = 'Could not process image.'; status.style.color = '#DC2626'; } return; }
          var resized = new File([blob], (file.name.replace(/\.[^.]+$/, '') || 'product') + '.jpg', { type: 'image/jpeg' });
          var dt = new DataTransfer(); dt.items.add(resized); input.files = dt.files;
          if (status) { status.textContent = 'Ready (' + w + '×' + h + ', ' + Math.round(blob.size/1024) + ' KB).'; status.style.color = '#16A34A'; }
        }, 'image/jpeg', 0.82);
      };
      img.src = ev.target.result;
    };
    reader.readAsDataURL(file);
  });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
