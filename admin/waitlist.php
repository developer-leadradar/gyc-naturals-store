<?php
define('GYC_ACCESS', true);
$adminPageTitle = 'Waiting List';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// ── Actions ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'notify' && $id) {
        $entry = $db->fetchOne("SELECT wl.*, gi.title as style_name FROM waitlist wl LEFT JOIN gallery_images gi ON wl.gallery_image_id = gi.id WHERE wl.id=?", [$id]);
        if ($entry) {
            $waPhone  = getSetting('site_whatsapp') ?: SITE_WHATSAPP;
            $custPhone= $entry['phone'];
            $msg      = "Hi {$entry['name']}! 🌿 Great news — a slot has opened up for {$entry['style_name']}! Book now: " . SITE_URL . "/book-appointment.php — Ref: " . $entry['ref_code'];
            $waUrl    = whatsappMessage($custPhone, $msg);
            $db->update('waitlist', ['notified_at' => date('Y-m-d H:i:s'), 'status' => 'notified'], 'id=?', [$id]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Marked as notified. Open WhatsApp to send the message.'];
            // Redirect to WA URL via JS so we can also set flash
            header('Location: ' . SITE_URL . '/admin/waitlist.php?wa_url=' . urlencode($waUrl));
            exit;
        }
    } elseif ($action === 'dismiss' && $id) {
        $db->update('waitlist', ['status' => 'expired'], 'id=?', [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Entry dismissed.'];
    } elseif ($action === 'delete' && $id) {
        $db->query("DELETE FROM waitlist WHERE id=?", [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Entry deleted.'];
    }
    redirect(SITE_URL . '/admin/waitlist.php');
    exit;
}

// Open WhatsApp if redirected from notify
$waUrl = $_GET['wa_url'] ?? '';

// Fetch waitlist
$statusFilter = sanitize($_GET['status'] ?? 'active');
$sql    = "SELECT wl.*, gi.title as style_name FROM waitlist wl
           LEFT JOIN gallery_images gi ON wl.gallery_image_id = gi.id
           WHERE 1=1";
$params = [];
if ($statusFilter && $statusFilter !== 'all') {
    $sql .= " AND wl.status = ?"; $params[] = $statusFilter;
}
$sql .= " ORDER BY wl.created_at ASC";
$entries = $db->fetchAll($sql, $params);

$total   = count($entries);
$active  = (int)($db->fetchOne("SELECT COUNT(*) c FROM waitlist WHERE status='active'")['c'] ?? 0);
?>

<?php if ($waUrl): ?>
<div class="alert alert-success" style="margin-bottom:1.25rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;">
  <span>✓ Entry marked as notified.</span>
  <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    Open WhatsApp Now
  </a>
</div>
<?php endif; ?>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;margin-bottom:1.5rem;">
  <?php foreach ([
    [$active,'Active Waiters','clock','#F59E0B','#FFFBEB'],
    [(int)($db->fetchOne("SELECT COUNT(*) c FROM waitlist WHERE status='notified'")['c']??0),'Notified','bell','#3B82F6','#EFF6FF'],
    [(int)($db->fetchOne("SELECT COUNT(*) c FROM waitlist")['c']??0),'Total All Time','users','var(--gyc-green-700)','var(--gyc-green-100)'],
  ] as $k): ?>
  <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.25rem;display:flex;align-items:center;gap:1rem;">
    <div style="width:38px;height:38px;border-radius:8px;background:<?= $k[4] ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i data-lucide="<?= $k[2] ?>" style="width:18px;height:18px;color:<?= $k[3] ?>;"></i>
    </div>
    <div>
      <div style="font-family:'Playfair Display',serif;font-size:1.35rem;font-weight:700;"><?= $k[0] ?></div>
      <div style="font-size:.75rem;color:#9CA3AF;"><?= $k[1] ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Filter tabs -->
<div style="display:flex;gap:.5rem;margin-bottom:1.25rem;">
  <?php foreach (['active'=>'Active','notified'=>'Notified','expired'=>'Expired','all'=>'All'] as $sv => $sl): ?>
  <a href="?status=<?= $sv ?>" class="btn btn-sm <?= $statusFilter===$sv?'btn-green':'btn-outline-green' ?>"><?= $sl ?></a>
  <?php endforeach; ?>
</div>

<!-- Table -->
<div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;">
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="background:#F8FAF9;border-bottom:1px solid #E5E7EB;">
        <th style="padding:.65rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Customer</th>
        <th style="padding:.65rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Style</th>
        <th style="padding:.65rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Pref. Date</th>
        <th style="padding:.65rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Joined</th>
        <th style="padding:.65rem 1.25rem;text-align:center;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Status</th>
        <th style="padding:.65rem 1.25rem;text-align:right;"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($entries as $e): ?>
      <tr style="border-bottom:1px solid #F0F0F0;" onmouseover="this.style.background='#FAFAFA'" onmouseout="this.style.background=''">
        <td style="padding:.85rem 1.25rem;">
          <div style="font-weight:600;font-size:.85rem;"><?= htmlspecialchars($e['name']) ?></div>
          <div style="font-size:.75rem;color:#9CA3AF;"><?= htmlspecialchars($e['phone']) ?></div>
        </td>
        <td style="padding:.85rem 1.25rem;font-size:.83rem;color:#374151;"><?= htmlspecialchars($e['style_name'] ?? '—') ?></td>
        <td style="padding:.85rem 1.25rem;font-size:.82rem;color:#374151;"><?= $e['preferred_date'] ? date('j M Y', strtotime($e['preferred_date'])) : '—' ?></td>
        <td style="padding:.85rem 1.25rem;font-size:.78rem;color:#9CA3AF;"><?= date('j M Y', strtotime($e['created_at'])) ?></td>
        <td style="padding:.85rem 1.25rem;text-align:center;">
          <?php $sc=['active'=>'#F59E0B','notified'=>'#3B82F6','expired'=>'#9CA3AF']; ?>
          <span style="font-size:.72rem;font-weight:700;color:<?= $sc[$e['status']]??'#9CA3AF' ?>;"><?= ucfirst($e['status']) ?></span>
        </td>
        <td style="padding:.85rem 1.25rem;text-align:right;">
          <div style="display:flex;gap:.4rem;justify-content:flex-end;">
            <?php if ($e['status'] === 'active'): ?>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="action" value="notify">
              <input type="hidden" name="id" value="<?= $e['id'] ?>">
              <button type="submit" class="btn btn-whatsapp btn-sm" style="font-size:.72rem;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Notify
              </button>
            </form>
            <?php endif; ?>
            <form method="POST" onsubmit="return confirm('Remove entry?');" style="display:inline;">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $e['id'] ?>">
              <button type="submit" style="padding:.3rem .45rem;border-radius:6px;background:#FEF2F2;color:#EF4444;font-size:.72rem;border:none;cursor:pointer;">✕</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($entries)): ?>
      <tr><td colspan="6" style="padding:3rem;text-align:center;color:#9CA3AF;">No entries in this status.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
