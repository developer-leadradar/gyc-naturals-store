<?php
define('GYC_ACCESS', true);
$editId = (int)($_GET['id'] ?? 0);
$isEdit = $editId > 0;
$adminPageTitle = $isEdit ? 'Edit Style Image' : 'Add Style Image';
require_once __DIR__ . '/includes/header.php';

$db   = getDB();
$img  = $isEdit ? $db->fetchOne("SELECT * FROM gallery_images WHERE id=?", [$editId]) : null;
if ($isEdit && !$img) {
    redirect(SITE_URL . '/admin/gallery.php');
    exit;
}
$galCats = $db->fetchAll("SELECT * FROM gallery_categories WHERE is_active=1 ORDER BY name");
$error   = '';

function resizeToDataUrl($tmpFile, $mime, $maxDim = 800, $quality = 82) {
    if (!extension_loaded('gd') || !function_exists('imagecreatefromjpeg')) {
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($tmpFile));
    }
    switch ($mime) {
        case 'image/jpeg': case 'image/jpg': $src = @imagecreatefromjpeg($tmpFile); break;
        case 'image/png':  $src = @imagecreatefrompng($tmpFile); break;
        case 'image/gif':  $src = @imagecreatefromgif($tmpFile); break;
        case 'image/webp': $src = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($tmpFile) : false; break;
        default: $src = false;
    }
    if (!$src) {
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($tmpFile));
    }
    $origW = imagesx($src); $origH = imagesy($src);
    $ratio = min($maxDim / $origW, $maxDim / $origH, 1.0);
    $newW = max(1, (int)round($origW * $ratio));
    $newH = max(1, (int)round($origH * $ratio));
    $dst = imagecreatetruecolor($newW, $newH);
    if ($mime === 'image/png') {
        imagealphablending($dst, false); imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 255, 255, 255, 127));
    } else {
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
    imagedestroy($src);
    ob_start();
    if ($mime === 'image/png') { imagepng($dst, null, 8); $outMime = 'image/png'; }
    else                       { imagejpeg($dst, null, $quality); $outMime = 'image/jpeg'; }
    $data = ob_get_clean();
    imagedestroy($dst);
    return 'data:' . $outMime . ';base64,' . base64_encode($data);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title'           => trim(sanitize($_POST['title']           ?? '')),
        'slug'            => trim(sanitize($_POST['slug']            ?? '')),
        'description'     => trim(sanitize($_POST['description']    ?? '')),
        'category_id'     => (int)($_POST['category_id'] ?? 0) ?: null,
        'hair_type'       => trim(sanitize($_POST['hair_type']      ?? '')),
        'duration_hours'  => trim(sanitize($_POST['duration_hours'] ?? '')),
        'price_from'      => $_POST['price_from'] ? (float)$_POST['price_from'] : null,
        'tags'            => trim(sanitize($_POST['tags']           ?? '')),
        'is_active'       => isset($_POST['is_active']) ? 1 : 0,
        'is_featured'     => isset($_POST['is_featured']) ? 1 : 0,
        'allow_moodboard' => isset($_POST['allow_moodboard']) ? 1 : 0,
        'display_order'   => (int)($_POST['display_order'] ?? 99),
    ];
    if (!$data['slug'] && $data['title']) {
        $data['slug'] = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $data['title']));
    }

    // Handle image upload or URL (resize + base64 data-URL — Vercel has no writable filesystem)
    $imageUrl = $img['image_url'] ?? '';
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $mime     = mime_content_type($_FILES['image']['tmp_name']) ?: 'image/jpeg';
        $imageUrl = resizeToDataUrl($_FILES['image']['tmp_name'], $mime);
    } elseif (!empty($_POST['image_url'])) {
        $imageUrl = trim($_POST['image_url']);
    } elseif (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Image upload failed (error code: ' . $_FILES['image']['error'] . '). Try a smaller file or use an image URL.';
    }
    $data['image_url'] = $imageUrl;

    // Handle before/after
    $beforeUrl = $img['before_image'] ?? '';
    $afterUrl  = $img['after_image']  ?? '';
    if (!empty($_FILES['before_image']['name']) && $_FILES['before_image']['error'] === UPLOAD_ERR_OK) {
        $mime      = mime_content_type($_FILES['before_image']['tmp_name']) ?: 'image/jpeg';
        $beforeUrl = resizeToDataUrl($_FILES['before_image']['tmp_name'], $mime);
    } elseif (!empty($_POST['before_url'])) { $beforeUrl = trim($_POST['before_url']); }
    if (!empty($_FILES['after_image']['name']) && $_FILES['after_image']['error'] === UPLOAD_ERR_OK) {
        $mime     = mime_content_type($_FILES['after_image']['tmp_name']) ?: 'image/jpeg';
        $afterUrl = resizeToDataUrl($_FILES['after_image']['tmp_name'], $mime);
    } elseif (!empty($_POST['after_url'])) { $afterUrl = trim($_POST['after_url']); }
    $data['before_image'] = $beforeUrl ?: null;
    $data['after_image']  = $afterUrl  ?: null;

    if (!$data['title']) {
        $error = 'Title is required.';
    } elseif ($error) {
        // upload error already set above
    } elseif (!$imageUrl) {
        $error = 'Please provide a main image (upload a file or paste a URL).';
    } else {
        if ($isEdit) {
            $db->update('gallery_images', $data, 'id=?', [$editId]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Style image updated.'];
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $db->insert('gallery_images', $data);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Style image added.'];
        }
        redirect(SITE_URL . '/admin/gallery.php');
        exit;
    }
}
?>

<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
  <a href="<?= SITE_URL ?>/admin/gallery.php" style="color:#9CA3AF;text-decoration:none;font-size:.82rem;">← Gallery</a>
</div>

<?php if ($error): ?>
<div class="alert alert-danger" style="margin-bottom:1.5rem;"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;">

  <!-- Main -->
  <div style="display:flex;flex-direction:column;gap:1.5rem;">
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
      <h2 style="font-size:.92rem;font-weight:700;margin-bottom:1.25rem;">Style Details</h2>
      <div class="form-group">
        <label class="form-label">Title <span style="color:var(--gyc-terra);">*</span></label>
        <input type="text" name="title" class="form-control" required
               value="<?= htmlspecialchars($img['title'] ?? '') ?>"
               placeholder="e.g. Knotless Box Braids — Medium Length"
               oninput="if(!document.getElementById('gal-slug').dataset.manual) document.getElementById('gal-slug').value = this.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'')">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="form-group">
          <label class="form-label">Slug</label>
          <input type="text" name="slug" id="gal-slug" class="form-control"
                 value="<?= htmlspecialchars($img['slug'] ?? '') ?>"
                 oninput="this.dataset.manual='true'">
        </div>
        <div class="form-group">
          <label class="form-label">Category</label>
          <select name="category_id" class="form-control">
            <option value="">— Uncategorised —</option>
            <?php foreach ($galCats as $gc): ?>
            <option value="<?= $gc['id'] ?>" <?= ($img['category_id'] ?? 0) == $gc['id'] ? 'selected' : '' ?>><?= htmlspecialchars($gc['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Hair Type</label>
          <input type="text" name="hair_type" class="form-control"
                 value="<?= htmlspecialchars($img['hair_type'] ?? '') ?>" placeholder="e.g. 4C Natural">
        </div>
        <div class="form-group">
          <label class="form-label">Duration</label>
          <input type="text" name="duration_hours" class="form-control"
                 value="<?= htmlspecialchars($img['duration_hours'] ?? '') ?>" placeholder="e.g. 5–7 hours">
        </div>
        <div class="form-group">
          <label class="form-label">Price From (₦)</label>
          <input type="number" name="price_from" class="form-control" min="0"
                 value="<?= htmlspecialchars($img['price_from'] ?? '') ?>" placeholder="25000">
        </div>
        <div class="form-group">
          <label class="form-label">Display Order</label>
          <input type="number" name="display_order" class="form-control" min="0"
                 value="<?= htmlspecialchars($img['display_order'] ?? 99) ?>">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3" placeholder="Style description…"><?= htmlspecialchars($img['description'] ?? '') ?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Tags <span style="font-size:.72rem;color:#9CA3AF;">(comma-separated)</span></label>
        <input type="text" name="tags" class="form-control"
               value="<?= htmlspecialchars($img['tags'] ?? '') ?>"
               placeholder="box braids, knotless, medium, brown">
      </div>
    </div>

    <!-- Before/After -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
      <h2 style="font-size:.92rem;font-weight:700;margin-bottom:1.25rem;">Before / After Images (optional)</h2>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div>
          <label class="form-label">Before Image</label>
          <?php if (!empty($img['before_image'])): ?><img src="<?= htmlspecialchars($img['before_image']) ?>" style="width:100%;height:120px;object-fit:cover;border-radius:8px;margin-bottom:.5rem;"><?php endif; ?>
          <input type="file" name="before_image" class="form-control" accept="image/*">
          <input type="url" name="before_url" class="form-control" style="margin-top:.5rem;" placeholder="Or paste URL…" value="<?= htmlspecialchars($img['before_image'] ?? '') ?>">
        </div>
        <div>
          <label class="form-label">After Image</label>
          <?php if (!empty($img['after_image'])): ?><img src="<?= htmlspecialchars($img['after_image']) ?>" style="width:100%;height:120px;object-fit:cover;border-radius:8px;margin-bottom:.5rem;"><?php endif; ?>
          <input type="file" name="after_image" class="form-control" accept="image/*">
          <input type="url" name="after_url" class="form-control" style="margin-top:.5rem;" placeholder="Or paste URL…" value="<?= htmlspecialchars($img['after_image'] ?? '') ?>">
        </div>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <div style="display:flex;flex-direction:column;gap:1.5rem;">
    <!-- Publish -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
      <h2 style="font-size:.92rem;font-weight:700;margin-bottom:1.25rem;">Visibility</h2>
      <label style="display:flex;align-items:center;gap:.5rem;font-size:.84rem;cursor:pointer;margin-bottom:.6rem;">
        <input type="checkbox" name="is_active" <?= ($img['is_active'] ?? 1) ? 'checked' : '' ?>> Visible in gallery
      </label>
      <label style="display:flex;align-items:center;gap:.5rem;font-size:.84rem;cursor:pointer;margin-bottom:.6rem;">
        <input type="checkbox" name="is_featured" <?= ($img['is_featured'] ?? 0) ? 'checked' : '' ?>> Featured (homepage)
      </label>
      <label style="display:flex;align-items:center;gap:.5rem;font-size:.84rem;cursor:pointer;">
        <input type="checkbox" name="allow_moodboard" <?= ($img['allow_moodboard'] ?? 1) ? 'checked' : '' ?>> Allow in moodboard
      </label>
      <hr style="margin:1.25rem 0;border-color:#E5E7EB;">
      <button type="submit" class="btn btn-green w-full"><?= $isEdit ? 'Save Changes' : 'Add to Gallery' ?></button>
    </div>
    <!-- Main image -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
      <h2 style="font-size:.92rem;font-weight:700;margin-bottom:1.25rem;">Main Image <span style="color:var(--gyc-terra);">*</span></h2>
      <?php if (!empty($img['image_url'])): ?>
      <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="" style="width:100%;aspect-ratio:3/4;object-fit:cover;border-radius:8px;margin-bottom:.75rem;">
      <?php endif; ?>
      <input type="file" name="image" class="form-control" accept="image/*" style="margin-bottom:.5rem;">
      <input type="url" name="image_url" class="form-control" placeholder="Or paste URL…" value="<?= htmlspecialchars($img['image_url'] ?? '') ?>">
    </div>
  </div>

</div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
