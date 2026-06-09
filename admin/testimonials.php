<?php
define('GYC_ACCESS', true);
$adminPageTitle = 'Testimonials';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'approve' && $id) {
        $db->update('testimonials', ['is_approved' => 1], 'id=?', [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Testimonial approved.'];
    } elseif ($action === 'unapprove' && $id) {
        $db->update('testimonials', ['is_approved' => 0], 'id=?', [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Testimonial unapproved.'];
    } elseif ($action === 'feature' && $id) {
        $cur = (int)($db->fetchOne("SELECT is_featured FROM testimonials WHERE id=?", [$id])['is_featured'] ?? 0);
        $db->update('testimonials', ['is_featured' => $cur ? 0 : 1], 'id=?', [$id]);
    } elseif ($action === 'delete' && $id) {
        $db->query("DELETE FROM testimonials WHERE id=?", [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Testimonial deleted.'];
    } elseif ($action === 'save') {
        // Manual add / edit
        $data = [
            'author_name'    => trim(sanitize($_POST['author_name'] ?? '')),
            'author_location'=> trim(sanitize($_POST['author_location'] ?? '')),
            'service'        => trim(sanitize($_POST['service'] ?? '')),
            'rating'         => (int)($_POST['rating'] ?? 5),
            'content'        => trim(sanitize($_POST['content'] ?? '')),
            'is_approved'    => isset($_POST['is_approved']) ? 1 : 0,
            'is_featured'    => isset($_POST['is_featured']) ? 1 : 0,
        ];
        if ($id) {
            $db->update('testimonials', $data, 'id=?', [$id]);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $db->insert('testimonials', $data);
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Testimonial saved.'];
    }
    redirect(SITE_URL . '/admin/testimonials.php');
    exit;
}

// Filters
$filterStatus = sanitize($_GET['status'] ?? 'pending');
$search       = sanitize($_GET['q'] ?? '');
$editId       = (int)($_GET['edit'] ?? 0);
$editT        = $editId ? $db->fetchOne("SELECT * FROM testimonials WHERE id=?", [$editId]) : null;

$sql    = "SELECT * FROM testimonials WHERE 1=1";
$params = [];
if ($filterStatus === 'pending')  { $sql .= " AND is_approved=0"; }
elseif ($filterStatus === 'approved') { $sql .= " AND is_approved=1"; }
elseif ($filterStatus === 'featured') { $sql .= " AND is_featured=1 AND is_approved=1"; }
if ($search) { $sql .= " AND (author_name LIKE ? OR content LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$sql .= " ORDER BY created_at DESC";
$items = $db->fetchAll($sql, $params);

$counts = [
    'all'      => (int)($db->fetchOne("SELECT COUNT(*) c FROM testimonials")['c'] ?? 0),
    'pending'  => (int)($db->fetchOne("SELECT COUNT(*) c FROM testimonials WHERE is_approved=0")['c'] ?? 0),
    'approved' => (int)($db->fetchOne("SELECT COUNT(*) c FROM testimonials WHERE is_approved=1")['c'] ?? 0),
    'featured' => (int)($db->fetchOne("SELECT COUNT(*) c FROM testimonials WHERE is_featured=1 AND is_approved=1")['c'] ?? 0),
];
?>

<?php if ($editId !== false && ($editId > 0 || isset($_GET['edit']))): ?>
<!-- ── EDIT / ADD ── -->
<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
  <a href="<?= SITE_URL ?>/admin/testimonials.php" style="color:#9CA3AF;font-size:.82rem;text-decoration:none;">← Testimonials</a>
  <h2 style="font-size:1rem;font-weight:700;"><?= $editT ? 'Edit Testimonial' : 'Add Testimonial' ?></h2>
</div>
<div style="display:grid;grid-template-columns:1fr 280px;gap:1.5rem;align-items:start;">
  <form method="POST">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editId ?>">
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;display:flex;flex-direction:column;gap:1rem;">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="form-group"><label class="form-label">Author Name *</label><input type="text" name="author_name" class="form-control" required value="<?= htmlspecialchars($editT['author_name']??'') ?>"></div>
        <div class="form-group"><label class="form-label">Location</label><input type="text" name="author_location" class="form-control" value="<?= htmlspecialchars($editT['author_location']??'') ?>" placeholder="e.g. Victoria Island, Lagos"></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="form-group">
          <label class="form-label">Service / Product</label>
          <input type="text" name="service" class="form-control" value="<?= htmlspecialchars($editT['service']??'') ?>" placeholder="e.g. Knotless Braids">
        </div>
        <div class="form-group">
          <label class="form-label">Rating (1–5)</label>
          <select name="rating" class="form-control">
            <?php for ($r=5;$r>=1;$r--): ?>
            <option value="<?= $r ?>" <?= ($editT['rating']??5)==$r?'selected':'' ?>><?= $r ?> ★</option>
            <?php endfor; ?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Testimonial Text *</label>
        <textarea name="content" class="form-control" rows="5" required><?= htmlspecialchars($editT['content']??'') ?></textarea>
      </div>
      <div style="display:flex;gap:1.5rem;">
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.84rem;cursor:pointer;"><input type="checkbox" name="is_approved" <?= ($editT['is_approved']??1)?'checked':'' ?>> Approved</label>
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.84rem;cursor:pointer;"><input type="checkbox" name="is_featured" <?= ($editT['is_featured']??0)?'checked':'' ?>> Featured</label>
      </div>
      <button type="submit" class="btn btn-green" style="align-self:flex-start;padding:.6rem 1.5rem;">Save Testimonial</button>
    </div>
  </form>
  <!-- Preview hint -->
  <div style="background:#F8FAF9;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.25rem;position:sticky;top:80px;">
    <div style="font-size:.8rem;font-weight:700;color:#6B7280;text-transform:uppercase;margin-bottom:.75rem;">Quick Stats</div>
    <?php foreach ([['Pending','pending','#F59E0B'],['Approved','approved','#10B981'],['Featured','featured','var(--gyc-gold)']] as $s): ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px solid #F0F0F0;">
      <span style="font-size:.82rem;"><?= $s[0] ?></span>
      <span style="font-size:.82rem;font-weight:700;color:<?= $s[2] ?>;"><?= $counts[$s[1]] ?></span>
    </div>
    <?php endforeach; ?>
    <a href="<?= SITE_URL ?>/testimonials.php" target="_blank" style="display:block;margin-top:.75rem;font-size:.8rem;color:var(--gyc-green-600);text-decoration:none;">View public page →</a>
  </div>
</div>

<?php else: ?>
<!-- ── LIST ── -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
  <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
    <?php foreach (['all'=>'All','pending'=>'Pending','approved'=>'Approved','featured'=>'Featured'] as $sv=>$sl): ?>
    <a href="?status=<?= $sv ?>" class="btn btn-sm <?= $filterStatus===$sv?'btn-green':'btn-outline-green' ?>"><?= $sl ?><?= $counts[$sv]?' ('.$counts[$sv].')':'' ?></a>
    <?php endforeach; ?>
  </div>
  <div style="display:flex;gap:.75rem;align-items:center;">
    <form method="GET" style="display:flex;gap:.5rem;">
      <input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>">
      <input type="text" name="q" class="form-control" style="width:180px;" placeholder="Search…" value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn btn-outline-green btn-sm">Go</button>
    </form>
    <a href="?edit=0" class="btn btn-green btn-sm"><i data-lucide="plus" style="width:14px;height:14px;"></i> Add</a>
  </div>
</div>

<div style="display:flex;flex-direction:column;gap:.75rem;">
  <?php foreach ($items as $t):
    $initials = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$t['author_name']),0,2))));
  ?>
  <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.25rem 1.5rem;display:flex;align-items:flex-start;gap:1rem;">
    <!-- Avatar -->
    <div style="width:42px;height:42px;border-radius:50%;background:var(--gyc-green-700);color:#fff;font-size:.85rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><?= $initials ?></div>
    <!-- Content -->
    <div style="flex:1;min-width:0;">
      <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:.3rem;">
        <span style="font-weight:700;font-size:.88rem;"><?= htmlspecialchars($t['author_name']) ?></span>
        <?php if ($t['author_location']): ?><span style="font-size:.75rem;color:#9CA3AF;"><?= htmlspecialchars($t['author_location']) ?></span><?php endif; ?>
        <?php if ($t['service']): ?><span style="font-size:.72rem;background:var(--gyc-green-100);color:var(--gyc-green-700);padding:.1rem .35rem;border-radius:4px;"><?= htmlspecialchars($t['service']) ?></span><?php endif; ?>
        <!-- Stars -->
        <span style="color:var(--gyc-gold);font-size:.8rem;"><?= str_repeat('★', (int)$t['rating']) ?><?= str_repeat('☆', 5-(int)$t['rating']) ?></span>
        <!-- Status badges -->
        <?php if (!$t['is_approved']): ?><span style="font-size:.68rem;font-weight:700;background:#FEF2F2;color:#EF4444;padding:.1rem .35rem;border-radius:4px;">Pending</span><?php endif; ?>
        <?php if ($t['is_featured']): ?><span style="font-size:.68rem;font-weight:700;background:#FEF9EC;color:var(--gyc-gold);padding:.1rem .35rem;border-radius:4px;">Featured</span><?php endif; ?>
      </div>
      <p style="font-size:.82rem;color:#374151;line-height:1.55;margin:0;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">"<?= htmlspecialchars($t['content']) ?>"</p>
      <div style="font-size:.72rem;color:#9CA3AF;margin-top:.4rem;"><?= date('j M Y', strtotime($t['created_at'])) ?></div>
    </div>
    <!-- Actions -->
    <div style="display:flex;gap:.4rem;flex-shrink:0;align-items:center;flex-wrap:wrap;justify-content:flex-end;">
      <?php if (!$t['is_approved']): ?>
      <form method="POST" style="display:inline;"><input type="hidden" name="action" value="approve"><input type="hidden" name="id" value="<?= $t['id'] ?>"><button type="submit" style="padding:.3rem .6rem;border-radius:6px;background:#ECFDF5;color:#065F46;border:none;cursor:pointer;font-size:.72rem;">✓ Approve</button></form>
      <?php else: ?>
      <form method="POST" style="display:inline;"><input type="hidden" name="action" value="unapprove"><input type="hidden" name="id" value="<?= $t['id'] ?>"><button type="submit" style="padding:.3rem .6rem;border-radius:6px;background:#F3F4F6;color:#6B7280;border:none;cursor:pointer;font-size:.72rem;">Unapprove</button></form>
      <?php endif; ?>
      <form method="POST" style="display:inline;"><input type="hidden" name="action" value="feature"><input type="hidden" name="id" value="<?= $t['id'] ?>"><button type="submit" style="padding:.3rem .6rem;border-radius:6px;background:<?= $t['is_featured']?'#FEF9EC':'#F3F4F6' ?>;color:<?= $t['is_featured']?'var(--gyc-gold)':'#6B7280' ?>;border:none;cursor:pointer;font-size:.72rem;"><?= $t['is_featured']?'★ Unfeature':'☆ Feature' ?></button></form>
      <a href="?edit=<?= $t['id'] ?>" style="padding:.3rem .6rem;border-radius:6px;background:#EFF6FF;color:#3B82F6;font-size:.72rem;text-decoration:none;">Edit</a>
      <form method="POST" onsubmit="return confirm('Delete testimonial?');" style="display:inline;"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $t['id'] ?>"><button type="submit" style="padding:.3rem .6rem;border-radius:6px;background:#FEF2F2;color:#EF4444;border:none;cursor:pointer;font-size:.72rem;">✕</button></form>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (empty($items)): ?>
  <div style="text-align:center;padding:4rem;background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;color:#9CA3AF;">
    No testimonials found. <?php if ($filterStatus==='pending'): ?>All caught up! 🎉<?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
