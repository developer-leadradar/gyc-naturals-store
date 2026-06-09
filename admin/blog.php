<?php
define('GYC_ACCESS', true);
$adminPageTitle = 'Blog Posts';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// ── Handle POST actions ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $db->query("DELETE FROM blog_posts WHERE id=?", [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Post deleted.'];
        redirect(SITE_URL . '/admin/blog.php');
        exit;
    }

    if ($action === 'toggle_status' && $id) {
        $cur = $db->fetchOne("SELECT status FROM blog_posts WHERE id=?", [$id])['status'] ?? 'draft';
        $new = $cur === 'published' ? 'draft' : 'published';
        $upd = ['status' => $new];
        if ($new === 'published') $upd['published_at'] = date('Y-m-d H:i:s');
        $db->update('blog_posts', $upd, 'id=?', [$id]);
        redirect(SITE_URL . '/admin/blog.php');
        exit;
    }

    if (in_array($action, ['save_draft', 'publish'])) {
        $title   = trim(sanitize($_POST['title'] ?? ''));
        $slug    = trim(sanitize($_POST['slug'] ?? ''));
        if (!$slug && $title) $slug = strtolower(preg_replace('/[^a-z0-9]+/','-',$title));

        // Check slug uniqueness
        $slugExists = $db->fetchOne("SELECT id FROM blog_posts WHERE slug=? AND id!=?", [$slug, $id]);
        if ($slugExists) $slug = $slug . '-' . time();

        $status  = $action === 'publish' ? 'published' : 'draft';
        $data = [
            'title'            => $title,
            'slug'             => $slug,
            'excerpt'          => trim(sanitize($_POST['excerpt'] ?? '')),
            'body'             => $_POST['body'] ?? '',   // Allow HTML
            'category'         => trim(sanitize($_POST['category'] ?? '')),
            'tags'             => trim(sanitize($_POST['tags'] ?? '')),
            'author'           => trim(sanitize($_POST['author'] ?? 'GYC Naturals Team')),
            'meta_title'       => trim(sanitize($_POST['meta_title'] ?? '')),
            'meta_description' => trim(sanitize($_POST['meta_description'] ?? '')),
            'read_time'        => max(1, (int)($_POST['read_time'] ?? 3)),
            'is_featured'      => isset($_POST['is_featured']) ? 1 : 0,
            'status'           => $status,
        ];
        if (!empty($_POST['featured_image'])) $data['featured_image'] = trim($_POST['featured_image']);
        if ($status === 'published') $data['published_at'] = date('Y-m-d H:i:s');

        // Handle file upload
        if (!empty($_FILES['featured_image_file']['name'])) {
            $uploadDir = __DIR__ . '/../uploads/blog/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext  = strtolower(pathinfo($_FILES['featured_image_file']['name'], PATHINFO_EXTENSION));
            $safe = in_array($ext, ['jpg','jpeg','png','webp','gif']) ? $ext : 'jpg';
            $fname = 'blog-' . time() . '-' . rand(1000,9999) . '.' . $safe;
            if (move_uploaded_file($_FILES['featured_image_file']['tmp_name'], $uploadDir . $fname)) {
                $data['featured_image'] = SITE_URL . '/uploads/blog/' . $fname;
            }
        }

        if ($id) {
            $db->update('blog_posts', $data, 'id=?', [$id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Post updated.'];
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['view_count'] = 0;
            $id = $db->insert('blog_posts', $data);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Post created.'];
        }
        redirect(SITE_URL . '/admin/blog.php?edit=' . $id);
        exit;
    }
}

$editId   = isset($_GET['edit']) ? (int)$_GET['edit'] : false;
$editPost = ($editId !== false && $editId > 0) ? $db->fetchOne("SELECT * FROM blog_posts WHERE id=?", [$editId]) : null;

// List filters
$filterStatus = sanitize($_GET['status'] ?? 'all');
$search       = sanitize($_GET['q'] ?? '');
$catFilter    = sanitize($_GET['category'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 20;

$sql    = "SELECT * FROM blog_posts WHERE 1=1";
$params = [];
if ($filterStatus === 'published') { $sql .= " AND status='published'"; }
elseif ($filterStatus === 'draft')  { $sql .= " AND status='draft'"; }
if ($catFilter)  { $sql .= " AND category=?"; $params[] = $catFilter; }
if ($search)     { $sql .= " AND (title LIKE ? OR excerpt LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$totalPosts = count($db->fetchAll($sql, $params));
$sql .= " ORDER BY created_at DESC LIMIT $perPage OFFSET " . (($page-1)*$perPage);
$posts = $db->fetchAll($sql, $params);

$counts = [
    'all'       => (int)($db->fetchOne("SELECT COUNT(*) c FROM blog_posts")['c'] ?? 0),
    'published' => (int)($db->fetchOne("SELECT COUNT(*) c FROM blog_posts WHERE status='published'")['c'] ?? 0),
    'draft'     => (int)($db->fetchOne("SELECT COUNT(*) c FROM blog_posts WHERE status='draft'")['c'] ?? 0),
];
$cats = $db->fetchAll("SELECT DISTINCT category FROM blog_posts WHERE category IS NOT NULL AND category!='' ORDER BY category");
$catNames = ['hair-care'=>'Hair Care Tips','protective-styles'=>'Protective Styles','natural-living'=>'Natural Living','product-guides'=>'Product Guides','salon-updates'=>'Salon Updates','style-inspiration'=>'Style Inspiration'];
?>

<?php if ($editId !== false): ?>
<!-- ── EDITOR ── -->
<style>
.ql-toolbar-custom { display:flex; flex-wrap:wrap; gap:.3rem; padding:.6rem .75rem; background:#F8FAF9; border:1.5px solid #E5E7EB; border-bottom:none; border-radius:8px 8px 0 0; }
.ql-toolbar-custom button { padding:.3rem .5rem; background:none; border:1px solid #D1D5DB; border-radius:4px; cursor:pointer; font-size:.75rem; color:#374151; }
.ql-toolbar-custom button:hover { background:#E5E7EB; }
#editor-body { min-height:400px; padding:1rem; border:1.5px solid #E5E7EB; border-radius:0 0 8px 8px; font-family:inherit; font-size:.9rem; line-height:1.7; outline:none; }
#editor-body:focus { border-color:var(--gyc-green-600); }
#editor-body h2 { font-size:1.25rem; font-weight:700; margin:1rem 0 .5rem; font-family:'Playfair Display',serif; }
#editor-body h3 { font-size:1.05rem; font-weight:700; margin:.75rem 0 .35rem; }
#editor-body p  { margin:0 0 .75rem; }
#editor-body ul, #editor-body ol { padding-left:1.5rem; margin:0 0 .75rem; }
#editor-body blockquote { border-left:3px solid var(--gyc-green-600); padding-left:1rem; color:#6B7280; font-style:italic; margin:.75rem 0; }
</style>
<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
  <a href="<?= SITE_URL ?>/admin/blog.php" style="color:#9CA3AF;font-size:.82rem;text-decoration:none;">← Blog Posts</a>
  <h2 style="font-size:1rem;font-weight:700;"><?= $editPost ? 'Edit: ' . htmlspecialchars(substr($editPost['title'],0,40)) : 'New Post' ?></h2>
  <?php if ($editPost && $editPost['status']==='published'): ?>
  <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= urlencode($editPost['slug']) ?>" target="_blank" style="font-size:.75rem;color:var(--gyc-green-600);text-decoration:none;">View live →</a>
  <?php endif; ?>
</div>

<form method="POST" enctype="multipart/form-data" id="blog-form">
  <input type="hidden" name="id" value="<?= $editId ?>">
  <input type="hidden" name="body" id="body-hidden" value="<?= htmlspecialchars($editPost['body']??'') ?>">

  <div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;">
    <div style="display:flex;flex-direction:column;gap:1.5rem;">
      <!-- Title & slug -->
      <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
        <div class="form-group">
          <label class="form-label">Post Title *</label>
          <input type="text" name="title" id="post-title" class="form-control" style="font-size:1.05rem;font-weight:600;" required value="<?= htmlspecialchars($editPost['title']??'') ?>" placeholder="Give this post a great title…">
        </div>
        <div class="form-group">
          <label class="form-label">Slug (URL)</label>
          <div style="display:flex;align-items:center;gap:.5rem;">
            <span style="font-size:.8rem;color:#9CA3AF;white-space:nowrap;">…/blog-post.php?slug=</span>
            <input type="text" name="slug" id="post-slug" class="form-control" value="<?= htmlspecialchars($editPost['slug']??'') ?>" placeholder="auto-generated" oninput="this.dataset.manual='true'">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Excerpt (shown in listing)</label>
          <textarea name="excerpt" class="form-control" rows="2" placeholder="A short summary, 1–2 sentences…"><?= htmlspecialchars($editPost['excerpt']??'') ?></textarea>
        </div>
      </div>

      <!-- Body editor -->
      <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
        <label class="form-label" style="margin-bottom:.75rem;display:block;">Post Body *</label>
        <div class="ql-toolbar-custom">
          <button type="button" onclick="execCmd('bold')"><b>B</b></button>
          <button type="button" onclick="execCmd('italic')"><i>I</i></button>
          <button type="button" onclick="execCmd('underline')"><u>U</u></button>
          <button type="button" onclick="execCmd('formatBlock','h2')">H2</button>
          <button type="button" onclick="execCmd('formatBlock','h3')">H3</button>
          <button type="button" onclick="execCmd('formatBlock','p')">P</button>
          <button type="button" onclick="execCmd('insertUnorderedList')">• List</button>
          <button type="button" onclick="execCmd('insertOrderedList')">1. List</button>
          <button type="button" onclick="execCmd('formatBlock','blockquote')">❝</button>
          <button type="button" onclick="insertLink()">Link</button>
          <button type="button" onclick="execCmd('removeFormat')">Clear</button>
        </div>
        <div id="editor-body" contenteditable="true"><?= $editPost['body'] ?? '' ?></div>
      </div>

      <!-- SEO -->
      <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
        <h3 style="font-size:.88rem;font-weight:700;margin-bottom:1rem;">SEO</h3>
        <div class="form-group"><label class="form-label">Meta Title</label><input type="text" name="meta_title" class="form-control" value="<?= htmlspecialchars($editPost['meta_title']??'') ?>" placeholder="Leave blank to use post title"></div>
        <div class="form-group"><label class="form-label">Meta Description</label><textarea name="meta_description" class="form-control" rows="2" maxlength="160" placeholder="155–160 characters"><?= htmlspecialchars($editPost['meta_description']??'') ?></textarea></div>
      </div>
    </div>

    <!-- Sidebar -->
    <div style="display:flex;flex-direction:column;gap:1rem;position:sticky;top:80px;">
      <!-- Publish -->
      <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.25rem;">
        <h3 style="font-size:.88rem;font-weight:700;margin-bottom:1rem;">Publish</h3>
        <?php if ($editPost): ?>
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;">
          <span style="font-size:.78rem;color:#9CA3AF;">Status:</span>
          <span style="font-size:.78rem;font-weight:700;color:<?= $editPost['status']==='published'?'#065F46':'#9CA3AF' ?>;"><?= ucfirst($editPost['status']) ?></span>
        </div>
        <?php endif; ?>
        <div style="display:flex;flex-direction:column;gap:.6rem;">
          <button type="submit" name="action" value="save_draft" class="btn btn-outline-green w-full">Save Draft</button>
          <button type="submit" name="action" value="publish" class="btn btn-green w-full">Publish</button>
        </div>
        <?php if ($editPost): ?>
        <div style="margin-top:.75rem;padding-top:.75rem;border-top:1px solid #F0F0F0;">
          <form method="POST" onsubmit="return confirm('Delete this post?');" style="display:inline;">
            <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $editId ?>">
            <button type="submit" style="font-size:.75rem;color:#EF4444;background:none;border:none;cursor:pointer;padding:0;">Delete post</button>
          </form>
        </div>
        <?php endif; ?>
      </div>

      <!-- Post details -->
      <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.25rem;">
        <h3 style="font-size:.88rem;font-weight:700;margin-bottom:1rem;">Post Details</h3>
        <div class="form-group">
          <label class="form-label">Category</label>
          <select name="category" class="form-control">
            <option value="">— Select Category —</option>
            <?php foreach ($catNames as $cv => $cn): ?>
            <option value="<?= $cv ?>" <?= ($editPost['category']??'')===$cv?'selected':'' ?>><?= $cn ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group"><label class="form-label">Author</label><input type="text" name="author" class="form-control" value="<?= htmlspecialchars($editPost['author']??'GYC Naturals Team') ?>"></div>
        <div class="form-group"><label class="form-label">Read Time (min)</label><input type="number" name="read_time" class="form-control" min="1" max="60" value="<?= $editPost['read_time']??3 ?>"></div>
        <div class="form-group"><label class="form-label">Tags (comma-separated)</label><input type="text" name="tags" class="form-control" value="<?= htmlspecialchars($editPost['tags']??'') ?>" placeholder="e.g. braids, natural hair, tips"></div>
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.84rem;cursor:pointer;"><input type="checkbox" name="is_featured" <?= ($editPost['is_featured']??0)?'checked':'' ?>> Featured Post</label>
      </div>

      <!-- Featured image -->
      <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.25rem;">
        <h3 style="font-size:.88rem;font-weight:700;margin-bottom:.75rem;">Featured Image</h3>
        <?php if (!empty($editPost['featured_image'])): ?>
        <img src="<?= htmlspecialchars($editPost['featured_image']) ?>" alt="" style="width:100%;aspect-ratio:16/9;object-fit:cover;border-radius:6px;margin-bottom:.75rem;border:1px solid #E5E7EB;">
        <?php endif; ?>
        <div class="form-group"><label class="form-label">Image URL</label><input type="url" name="featured_image" class="form-control" value="<?= htmlspecialchars($editPost['featured_image']??'') ?>" placeholder="https://…"></div>
        <div class="form-group" style="margin-top:.5rem;"><label class="form-label">Or Upload</label><input type="file" name="featured_image_file" class="form-control" accept="image/*" style="padding:.4rem;"></div>
      </div>

      <?php if ($editPost): ?>
      <div style="background:#F8FAF9;border:1.5px solid #E5E7EB;border-radius:12px;padding:1rem;font-size:.75rem;color:#9CA3AF;">
        <div>Created: <?= date('j M Y', strtotime($editPost['created_at'])) ?></div>
        <?php if ($editPost['published_at']): ?><div>Published: <?= date('j M Y', strtotime($editPost['published_at'])) ?></div><?php endif; ?>
        <div>Views: <?= number_format($editPost['view_count']??0) ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</form>

<script>
// Auto-slug
document.getElementById('post-title').addEventListener('input', function() {
  var slugField = document.getElementById('post-slug');
  if (!slugField.dataset.manual) {
    slugField.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
  }
});

// Rich text editor
function execCmd(cmd, val) {
  document.getElementById('editor-body').focus();
  document.execCommand(cmd, false, val || null);
}
function insertLink() {
  var url = prompt('Enter URL:');
  if (url) execCmd('createLink', url);
}

// Sync editor to hidden input before submit
document.getElementById('blog-form').addEventListener('submit', function() {
  document.getElementById('body-hidden').value = document.getElementById('editor-body').innerHTML;
});
</script>

<?php else: ?>
<!-- ── LIST VIEW ── -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
  <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
    <?php foreach (['all'=>'All','published'=>'Published','draft'=>'Drafts'] as $sv=>$sl): ?>
    <a href="?status=<?= $sv ?>" class="btn btn-sm <?= $filterStatus===$sv?'btn-green':'btn-outline-green' ?>"><?= $sl ?> (<?= $counts[$sv] ?>)</a>
    <?php endforeach; ?>
  </div>
  <div style="display:flex;gap:.75rem;align-items:center;">
    <form method="GET" style="display:flex;gap:.5rem;">
      <input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>">
      <input type="text" name="q" class="form-control" style="width:180px;" placeholder="Search posts…" value="<?= htmlspecialchars($search) ?>">
      <select name="category" class="form-control" style="width:160px;">
        <option value="">All categories</option>
        <?php foreach ($cats as $c): ?>
        <option value="<?= htmlspecialchars($c['category']) ?>" <?= $catFilter===$c['category']?'selected':'' ?>><?= htmlspecialchars($catNames[$c['category']] ?? ucwords(str_replace('-',' ',$c['category']))) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-outline-green btn-sm">Filter</button>
    </form>
    <a href="?edit=0" class="btn btn-green btn-sm"><i data-lucide="plus" style="width:14px;height:14px;"></i> New Post</a>
  </div>
</div>

<div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;">
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="background:#F8FAF9;border-bottom:1px solid #E5E7EB;">
        <th style="padding:.65rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Title</th>
        <th style="padding:.65rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Category</th>
        <th style="padding:.65rem 1.25rem;text-align:center;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Status</th>
        <th style="padding:.65rem 1.25rem;text-align:center;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Views</th>
        <th style="padding:.65rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Date</th>
        <th style="padding:.65rem 1.25rem;text-align:right;"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($posts as $p): ?>
      <tr style="border-bottom:1px solid #F0F0F0;" onmouseover="this.style.background='#FAFAFA'" onmouseout="this.style.background=''">
        <td style="padding:.85rem 1.25rem;">
          <div style="display:flex;align-items:center;gap:.75rem;">
            <?php if (!empty($p['featured_image'])): ?>
            <img src="<?= htmlspecialchars($p['featured_image']) ?>" alt="" style="width:36px;height:36px;border-radius:4px;object-fit:cover;flex-shrink:0;border:1px solid #E5E7EB;">
            <?php else: ?>
            <div style="width:36px;height:36px;border-radius:4px;background:var(--gyc-green-100);flex-shrink:0;display:flex;align-items:center;justify-content:center;"><i data-lucide="file-text" style="width:16px;height:16px;color:var(--gyc-green-600);"></i></div>
            <?php endif; ?>
            <div>
              <div style="font-weight:600;font-size:.85rem;max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($p['title']) ?></div>
              <?php if ($p['is_featured']): ?><span style="font-size:.68rem;background:#FEF9EC;color:var(--gyc-gold);padding:.1rem .3rem;border-radius:3px;">Featured</span><?php endif; ?>
            </div>
          </div>
        </td>
        <td style="padding:.85rem 1.25rem;font-size:.8rem;color:#6B7280;"><?= htmlspecialchars($catNames[$p['category']] ?? ucwords(str_replace('-',' ',$p['category']??'—'))) ?></td>
        <td style="padding:.85rem 1.25rem;text-align:center;">
          <form method="POST" style="display:inline;">
            <input type="hidden" name="action" value="toggle_status"><input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button type="submit" style="padding:.2rem .5rem;border-radius:4px;border:1px solid <?= $p['status']==='published'?'#D1FAE5':'#E5E7EB' ?>;background:<?= $p['status']==='published'?'#ECFDF5':'#F3F4F6' ?>;color:<?= $p['status']==='published'?'#065F46':'#9CA3AF' ?>;font-size:.7rem;font-weight:700;cursor:pointer;"><?= ucfirst($p['status']) ?></button>
          </form>
        </td>
        <td style="padding:.85rem 1.25rem;text-align:center;font-size:.82rem;"><?= number_format($p['view_count']??0) ?></td>
        <td style="padding:.85rem 1.25rem;font-size:.78rem;color:#9CA3AF;"><?= date('j M Y', strtotime($p['created_at'])) ?></td>
        <td style="padding:.85rem 1.25rem;text-align:right;">
          <div style="display:flex;gap:.4rem;justify-content:flex-end;">
            <a href="?edit=<?= $p['id'] ?>" style="padding:.3rem .6rem;border-radius:6px;background:#EFF6FF;color:#3B82F6;font-size:.72rem;text-decoration:none;">Edit</a>
            <?php if ($p['status']==='published'): ?>
            <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= urlencode($p['slug']) ?>" target="_blank" style="padding:.3rem .6rem;border-radius:6px;background:#F0FFF4;color:#065F46;font-size:.72rem;text-decoration:none;">View</a>
            <?php endif; ?>
            <form method="POST" onsubmit="return confirm('Delete post?');" style="display:inline;"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button type="submit" style="padding:.3rem .6rem;border-radius:6px;background:#FEF2F2;color:#EF4444;border:none;cursor:pointer;font-size:.72rem;">✕</button></form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($posts)): ?>
      <tr><td colspan="6" style="padding:3rem;text-align:center;color:#9CA3AF;">No posts found. <a href="?edit=0" style="color:var(--gyc-green-600);">Write the first one →</a></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  <!-- Pagination -->
  <?php $totalPages = (int)ceil($totalPosts / $perPage); if ($totalPages > 1): ?>
  <div style="padding:1rem 1.5rem;border-top:1px solid #E5E7EB;display:flex;justify-content:space-between;align-items:center;">
    <span style="font-size:.78rem;color:#9CA3AF;">Showing <?= (($page-1)*$perPage)+1 ?>–<?= min($page*$perPage,$totalPosts) ?> of <?= $totalPosts ?></span>
    <div style="display:flex;gap:.35rem;">
      <?php for ($p2=1;$p2<=$totalPages;$p2++): ?>
      <a href="?status=<?= $filterStatus ?>&q=<?= urlencode($search) ?>&category=<?= urlencode($catFilter) ?>&page=<?= $p2 ?>" style="padding:.35rem .6rem;border-radius:5px;font-size:.78rem;text-decoration:none;background:<?= $p2===$page?'var(--gyc-green-700)':'#F3F4F6' ?>;color:<?= $p2===$page?'#fff':'#374151' ?>;"><?= $p2 ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
