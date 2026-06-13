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
$galCats = $db->fetchAll("SELECT * FROM gallery_categories WHERE is_active=1 ORDER BY service_type ASC, display_order ASC, name ASC");

// Service-type labels (shared with gallery.php and book-appointment.php)
$svcLabels = [
    'braiding'  => 'Braiding & Protective',
    'kids'      => "Kids' Hair",
    'natural'   => 'Natural Styles',
    'treatment' => 'Scalp & Treatments',
];

// Determine current service from existing category (when editing) or default to braiding
$currentSvc = 'braiding';
if ($isEdit && !empty($img['category_id'])) {
    foreach ($galCats as $gc) {
        if ((int)$gc['id'] === (int)$img['category_id']) {
            $currentSvc = $gc['service_type'] ?: 'braiding';
            break;
        }
    }
}
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
        'title'          => trim(sanitize($_POST['title']           ?? '')),
        'slug'           => trim(sanitize($_POST['slug']            ?? '')),
        'description'    => trim(sanitize($_POST['description']    ?? '')),
        'category_id'    => (int)($_POST['category_id'] ?? 0) ?: null,
        'duration_hours' => $_POST['duration_hours'] !== '' ? (float)$_POST['duration_hours'] : null,
        'price_from'     => $_POST['price_from'] !== '' ? (float)$_POST['price_from'] : null,
        'is_active'      => isset($_POST['is_active']) ? 1 : 0,
        'is_featured'    => isset($_POST['is_featured']) ? 1 : 0,
        'display_order'  => (int)($_POST['display_order'] ?? 99),
    ];
    if (!$data['slug'] && $data['title']) {
        $data['slug'] = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $data['title']));
    }

    // Handle image upload or URL (resize + base64 data-URL — Vercel has no writable filesystem)
    $imageUrl    = $img['image_url'] ?? '';
    $uploadErrs  = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds PHP upload_max_filesize.',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds form MAX_FILE_SIZE.',
        UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server has no temp directory.',
        UPLOAD_ERR_CANT_WRITE => 'Server could not write the upload.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the upload.',
    ];
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $mime     = mime_content_type($_FILES['image']['tmp_name']) ?: 'image/jpeg';
        $imageUrl = resizeToDataUrl($_FILES['image']['tmp_name'], $mime);
    } elseif (!empty($_FILES['image']['name']) && $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $msg   = $uploadErrs[$_FILES['image']['error']] ?? ('Unknown error #' . $_FILES['image']['error']);
        $error = 'Image upload failed: ' . $msg . ' Try a smaller file or use an image URL.';
    } elseif (!empty($_POST['image_url'])) {
        $imageUrl = trim($_POST['image_url']);
    }
    $data['image_url'] = $imageUrl;

    // Handle before/after image
    $beforeUrl = $img['before_image_url'] ?? '';
    if (!empty($_FILES['before_image']['name']) && $_FILES['before_image']['error'] === UPLOAD_ERR_OK) {
        $mime      = mime_content_type($_FILES['before_image']['tmp_name']) ?: 'image/jpeg';
        $beforeUrl = resizeToDataUrl($_FILES['before_image']['tmp_name'], $mime);
    } elseif (!empty($_POST['before_url'])) { $beforeUrl = trim($_POST['before_url']); }
    $data['before_image_url'] = $beforeUrl ?: null;

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
          <label class="form-label">Service <span style="color:var(--gyc-terra);">*</span></label>
          <select id="svc-select" class="form-control" required onchange="filterCategoriesBySvc(this.value)">
            <?php foreach ($svcLabels as $svKey => $svLabel): ?>
            <option value="<?= $svKey ?>" <?= $currentSvc === $svKey ? 'selected' : '' ?>><?= htmlspecialchars($svLabel) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Category <span style="color:var(--gyc-terra);">*</span></label>
          <select name="category_id" id="cat-select" class="form-control" required>
            <option value="">— Pick a category —</option>
            <?php foreach ($galCats as $gc): ?>
            <option value="<?= $gc['id'] ?>" data-svc="<?= htmlspecialchars($gc['service_type'] ?? '') ?>"
                    <?= ($img['category_id'] ?? 0) == $gc['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($gc['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Slug</label>
          <input type="text" name="slug" id="gal-slug" class="form-control"
                 value="<?= htmlspecialchars($img['slug'] ?? '') ?>"
                 oninput="this.dataset.manual='true'">
        </div>
        <div class="form-group">
          <label class="form-label">Duration (hours)</label>
          <input type="number" step="0.5" min="0" name="duration_hours" class="form-control"
                 value="<?= htmlspecialchars($img['duration_hours'] ?? '') ?>" placeholder="e.g. 5">
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
    </div>
    <script>
    function filterCategoriesBySvc(svc) {
      var sel = document.getElementById('cat-select');
      var picked = sel.value;
      var keep = false;
      Array.from(sel.options).forEach(function(opt) {
        if (!opt.value) { opt.hidden = false; return; }
        var match = opt.getAttribute('data-svc') === svc;
        opt.hidden = !match;
        if (opt.value === picked && match) keep = true;
      });
      if (!keep) sel.value = '';
    }
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      filterCategoriesBySvc(document.getElementById('svc-select').value);
    });
    </script>

    <!-- Before image -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
      <h2 style="font-size:.92rem;font-weight:700;margin-bottom:1.25rem;">"Before" Image (optional)</h2>
      <?php $beforeIsData = strpos($img['before_image_url'] ?? '', 'data:') === 0; ?>
      <?php if (!empty($img['before_image_url'])): ?>
      <img src="<?= htmlspecialchars($img['before_image_url']) ?>" style="width:100%;max-width:220px;aspect-ratio:1;object-fit:cover;border-radius:8px;margin-bottom:.5rem;display:block;">
      <?php endif; ?>
      <input type="file" name="before_image" id="before-file-input" class="form-control" accept="image/*">
      <div id="before-resize-status" style="font-size:.72rem;color:#6B7280;margin-top:.3rem;display:none;"></div>
      <input type="url" name="before_url" class="form-control" style="margin-top:.5rem;" placeholder="Or paste URL…" value="<?= $beforeIsData ? '' : htmlspecialchars($img['before_image_url'] ?? '') ?>">
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
      <hr style="margin:1.25rem 0;border-color:#E5E7EB;">
      <button type="submit" class="btn btn-green w-full"><?= $isEdit ? 'Save Changes' : 'Add to Gallery' ?></button>
    </div>
    <!-- Main image -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
      <h2 style="font-size:.92rem;font-weight:700;margin-bottom:1.25rem;">Main Image <span style="color:var(--gyc-terra);">*</span></h2>
      <?php
      $existingImg     = $img['image_url'] ?? '';
      $existingIsData  = strpos($existingImg, 'data:') === 0;
      ?>
      <?php if (!empty($existingImg)): ?>
      <img src="<?= htmlspecialchars($existingImg) ?>" alt="" style="width:100%;aspect-ratio:3/4;object-fit:cover;border-radius:8px;margin-bottom:.75rem;" id="current-image-preview">
      <?php endif; ?>
      <input type="file" name="image" id="image-file-input" class="form-control" accept="image/*" style="margin-bottom:.5rem;">
      <div id="image-resize-status" style="font-size:.78rem;color:#6B7280;margin-bottom:.5rem;display:none;"></div>
      <input type="url" name="image_url" class="form-control" placeholder="Or paste URL…"
             value="<?= $existingIsData ? '' : htmlspecialchars($existingImg) ?>">
      <p style="font-size:.72rem;color:#9CA3AF;margin-top:.4rem;">Large photos are auto-resized to 1200px in your browser before upload.</p>
    </div>
  </div>

</div>
</form>

<script>
// Client-side resize for image uploads — keeps payloads under Vercel's 4.5MB body limit.
function attachResizer(inputId, statusId, maxDim, quality) {
  var input = document.getElementById(inputId);
  if (!input) return;
  input.addEventListener('change', function() {
    var file = input.files[0];
    if (!file || !file.type.startsWith('image/')) return;
    var status = document.getElementById(statusId);
    if (status) { status.style.display = 'block'; status.textContent = 'Processing image…'; status.style.color = '#6B7280'; }
    var reader = new FileReader();
    reader.onload = function(ev) {
      var img = new Image();
      img.onload = function() {
        var w = img.width, h = img.height;
        var ratio = Math.min(maxDim / w, maxDim / h, 1);
        w = Math.round(w * ratio); h = Math.round(h * ratio);
        var canvas = document.createElement('canvas');
        canvas.width = w; canvas.height = h;
        canvas.getContext('2d').drawImage(img, 0, 0, w, h);
        canvas.toBlob(function(blob) {
          if (!blob) {
            if (status) { status.textContent = 'Could not process image. Try a different file.'; status.style.color = '#DC2626'; }
            return;
          }
          var resized = new File([blob], (file.name.replace(/\.[^.]+$/, '') || 'image') + '.jpg', { type: 'image/jpeg' });
          var dt = new DataTransfer();
          dt.items.add(resized);
          input.files = dt.files;
          if (status) {
            var kb = Math.round(blob.size / 1024);
            status.textContent = 'Ready (' + w + '×' + h + ', ' + kb + ' KB).';
            status.style.color = '#16A34A';
          }
          // Live preview
          var preview = document.getElementById(inputId.replace('-file-input','') + '-preview') || document.getElementById('current-image-preview');
          if (preview) preview.src = URL.createObjectURL(blob);
        }, 'image/jpeg', quality);
      };
      img.onerror = function() {
        if (status) { status.textContent = 'Could not read image. Try a JPG or PNG.'; status.style.color = '#DC2626'; }
      };
      img.src = ev.target.result;
    };
    reader.readAsDataURL(file);
  });
}
attachResizer('image-file-input', 'image-resize-status', 1200, 0.82);
attachResizer('before-file-input', 'before-resize-status', 900, 0.8);
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
