<?php
define('GYC_ACCESS', true);
$adminPageTitle = 'Messages';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'mark_read' && $id) {
        $db->update('contact_messages', ['is_read' => 1], 'id=?', [$id]);
        redirect(SITE_URL . '/admin/messages.php?view=' . $id);
        exit;
    }
    if ($action === 'mark_unread' && $id) {
        $db->update('contact_messages', ['is_read' => 0], 'id=?', [$id]);
        redirect(SITE_URL . '/admin/messages.php');
        exit;
    }
    if ($action === 'delete' && $id) {
        $db->query("DELETE FROM contact_messages WHERE id=?", [$id]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Message deleted.'];
        redirect(SITE_URL . '/admin/messages.php');
        exit;
    }
    if ($action === 'reply' && $id) {
        $msg = $db->fetchOne("SELECT * FROM contact_messages WHERE id=?", [$id]);
        if ($msg) {
            $body = trim($_POST['reply_body'] ?? '');
            if ($body) {
                $subject = 'Re: ' . $msg['subject'];
                $html = '<p>Hi ' . htmlspecialchars($msg['name']) . ',</p>'
                      . '<p>' . nl2br(htmlspecialchars($body)) . '</p>'
                      . '<p style="margin-top:1.5rem;color:#6B7280;font-size:.85em;">—<br>GYC Naturals Team<br>Big Qua Mall, Calabar</p>';
                sendEmail($msg['email'], $subject, $html);
                $db->update('contact_messages', ['is_read' => 1], 'id=?', [$id]);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Reply sent to ' . $msg['email']];
            }
        }
        redirect(SITE_URL . '/admin/messages.php?view=' . $id);
        exit;
    }
    redirect(SITE_URL . '/admin/messages.php');
    exit;
}

// Single message view
$viewId  = (int)($_GET['view'] ?? 0);
$viewMsg = $viewId ? $db->fetchOne("SELECT * FROM contact_messages WHERE id=?", [$viewId]) : null;
if ($viewMsg && !$viewMsg['is_read']) {
    $db->update('contact_messages', ['is_read' => 1], 'id=?', [$viewId]);
    $viewMsg['is_read'] = 1;
}

// List filters
$filterRead = sanitize($_GET['read'] ?? 'all');
$search     = sanitize($_GET['q'] ?? '');
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 25;

$sql    = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];
if ($filterRead === 'unread') { $sql .= " AND is_read=0"; }
elseif ($filterRead === 'read') { $sql .= " AND is_read=1"; }
if ($search) { $sql .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)"; $params = array_fill(0, 4, "%$search%"); }
$total = count($db->fetchAll($sql, $params));
$sql .= " ORDER BY created_at DESC LIMIT $perPage OFFSET " . (($page-1)*$perPage);
$messages = $db->fetchAll($sql, $params);

$unreadCount = (int)($db->fetchOne("SELECT COUNT(*) c FROM contact_messages WHERE is_read=0")['c'] ?? 0);
$totalCount  = (int)($db->fetchOne("SELECT COUNT(*) c FROM contact_messages")['c'] ?? 0);

// Subject icon map
$subjectIcons = [
    'Appointment Enquiry' => 'calendar',
    'Product Question'    => 'package',
    'Order Support'       => 'shopping-bag',
    'Refund/Return'       => 'refresh-cw',
    'Partnership'         => 'handshake',
    'Press'               => 'mic',
    'General'             => 'mail',
];
?>

<?php if ($viewMsg): ?>
<!-- ── SINGLE MESSAGE VIEW ── -->
<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
  <a href="<?= SITE_URL ?>/admin/messages.php" style="color:#9CA3AF;font-size:.82rem;text-decoration:none;">← Messages</a>
  <h2 style="font-size:1rem;font-weight:700;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($viewMsg['subject']) ?></h2>
  <div style="display:flex;gap:.5rem;">
    <form method="POST" style="display:inline;">
      <input type="hidden" name="action" value="mark_unread"><input type="hidden" name="id" value="<?= $viewId ?>">
      <button type="submit" style="padding:.35rem .7rem;border-radius:6px;background:#F3F4F6;color:#6B7280;border:none;cursor:pointer;font-size:.75rem;">Mark Unread</button>
    </form>
    <form method="POST" onsubmit="return confirm('Delete message?');" style="display:inline;">
      <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $viewId ?>">
      <button type="submit" style="padding:.35rem .7rem;border-radius:6px;background:#FEF2F2;color:#EF4444;border:none;cursor:pointer;font-size:.75rem;">Delete</button>
    </form>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;">
  <div style="display:flex;flex-direction:column;gap:1.25rem;">
    <!-- Message card -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
      <div style="display:flex;align-items:flex-start;gap:1rem;margin-bottom:1.25rem;padding-bottom:1.25rem;border-bottom:1px solid #F0F0F0;">
        <div style="width:44px;height:44px;border-radius:50%;background:var(--gyc-green-700);color:#fff;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.9rem;">
          <?= strtoupper(substr($viewMsg['name'],0,1)) ?>
        </div>
        <div>
          <div style="font-weight:700;font-size:.9rem;"><?= htmlspecialchars($viewMsg['name']) ?></div>
          <div style="font-size:.8rem;color:#9CA3AF;"><?= htmlspecialchars($viewMsg['email']) ?></div>
          <div style="font-size:.75rem;color:#9CA3AF;margin-top:.2rem;"><?= date('l, j F Y \a\t g:i A', strtotime($viewMsg['created_at'])) ?></div>
        </div>
      </div>
      <div style="font-size:.92rem;line-height:1.75;color:#374151;white-space:pre-wrap;"><?= htmlspecialchars($viewMsg['message']) ?></div>
    </div>

    <!-- Reply form -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
      <h3 style="font-size:.9rem;font-weight:700;margin-bottom:1rem;">Reply to <?= htmlspecialchars($viewMsg['name']) ?></h3>
      <form method="POST">
        <input type="hidden" name="action" value="reply">
        <input type="hidden" name="id" value="<?= $viewId ?>">
        <div class="form-group">
          <label class="form-label">To</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($viewMsg['name']) ?> &lt;<?= htmlspecialchars($viewMsg['email']) ?>&gt;" disabled style="background:#F8FAF9;color:#9CA3AF;">
        </div>
        <div class="form-group">
          <label class="form-label">Subject</label>
          <input type="text" class="form-control" value="Re: <?= htmlspecialchars($viewMsg['subject']) ?>" disabled style="background:#F8FAF9;color:#9CA3AF;">
        </div>
        <div class="form-group">
          <label class="form-label">Your Reply *</label>
          <textarea name="reply_body" class="form-control" rows="6" required placeholder="Type your response here…"></textarea>
        </div>
        <div style="display:flex;gap:.75rem;align-items:center;">
          <button type="submit" class="btn btn-green">Send Reply</button>
          <a href="mailto:<?= htmlspecialchars($viewMsg['email']) ?>?subject=<?= urlencode('Re: '.$viewMsg['subject']) ?>" style="font-size:.8rem;color:var(--gyc-green-600);text-decoration:none;">Open in email client</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Sidebar: sender info + actions -->
  <div style="display:flex;flex-direction:column;gap:1rem;position:sticky;top:80px;">
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.25rem;">
      <h3 style="font-size:.84rem;font-weight:700;text-transform:uppercase;color:#9CA3AF;margin-bottom:.9rem;">Sender Details</h3>
      <div style="display:flex;flex-direction:column;gap:.65rem;">
        <div>
          <div style="font-size:.7rem;color:#9CA3AF;text-transform:uppercase;">Name</div>
          <div style="font-size:.85rem;font-weight:600;"><?= htmlspecialchars($viewMsg['name']) ?></div>
        </div>
        <div>
          <div style="font-size:.7rem;color:#9CA3AF;text-transform:uppercase;">Email</div>
          <a href="mailto:<?= htmlspecialchars($viewMsg['email']) ?>" style="font-size:.85rem;color:var(--gyc-green-600);text-decoration:none;"><?= htmlspecialchars($viewMsg['email']) ?></a>
        </div>
        <div>
          <div style="font-size:.7rem;color:#9CA3AF;text-transform:uppercase;">Subject</div>
          <div style="font-size:.85rem;"><?= htmlspecialchars($viewMsg['subject']) ?></div>
        </div>
        <div>
          <div style="font-size:.7rem;color:#9CA3AF;text-transform:uppercase;">Received</div>
          <div style="font-size:.82rem;"><?= date('j M Y, g:i A', strtotime($viewMsg['created_at'])) ?></div>
        </div>
      </div>
    </div>

    <!-- WhatsApp CTA if has phone -->
    <?php
    // Attempt to find a customer by email for WA link
    $cust = $db->fetchOne("SELECT phone FROM users WHERE email=? LIMIT 1", [$viewMsg['email']]);
    if ($cust && $cust['phone']):
        $waUrl = whatsappMessage($cust['phone'], "Hi {$viewMsg['name']}! Thanks for reaching out to GYC Naturals. We've received your message about \"{$viewMsg['subject']}\" and we'll get back to you shortly. 🌿");
    ?>
    <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp w-full">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
      WhatsApp Acknowledgement
    </a>
    <?php endif; ?>

    <!-- Quick actions -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.25rem;">
      <h3 style="font-size:.84rem;font-weight:700;text-transform:uppercase;color:#9CA3AF;margin-bottom:.75rem;">Quick Links</h3>
      <?php
      $custProfile = $db->fetchOne("SELECT id FROM users WHERE email=?", [$viewMsg['email']]);
      if ($custProfile): ?>
      <a href="<?= SITE_URL ?>/admin/customers.php?view=<?= $custProfile['id'] ?>" style="display:block;font-size:.82rem;color:var(--gyc-green-600);text-decoration:none;margin-bottom:.4rem;">View customer profile →</a>
      <?php endif; ?>
      <a href="<?= SITE_URL ?>/admin/messages.php" style="display:block;font-size:.82rem;color:#9CA3AF;text-decoration:none;">← All messages</a>
    </div>
  </div>
</div>

<?php else: ?>
<!-- ── LIST ── -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
  <div style="display:flex;gap:.5rem;align-items:center;">
    <?php foreach (['all'=>'All','unread'=>'Unread','read'=>'Read'] as $sv=>$sl): ?>
    <a href="?read=<?= $sv ?>" class="btn btn-sm <?= $filterRead===$sv?'btn-green':'btn-outline-green' ?>"><?= $sl ?><?= $sv==='unread'&&$unreadCount?' ('.$unreadCount.')':'' ?></a>
    <?php endforeach; ?>
    <?php if ($unreadCount): ?>
    <span style="width:8px;height:8px;background:#EF4444;border-radius:50%;display:inline-block;margin-left:.25rem;"></span>
    <?php endif; ?>
  </div>
  <form method="GET" style="display:flex;gap:.5rem;">
    <input type="hidden" name="read" value="<?= htmlspecialchars($filterRead) ?>">
    <input type="text" name="q" class="form-control" style="width:200px;" placeholder="Search name, email, subject…" value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn btn-outline-green btn-sm">Search</button>
  </form>
</div>

<div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;">
  <?php foreach ($messages as $m):
    $icon = $subjectIcons[$m['subject']] ?? 'mail';
  ?>
  <div onclick="window.location='<?= SITE_URL ?>/admin/messages.php?view=<?= $m['id'] ?>'" style="display:flex;align-items:center;gap:1rem;padding:1rem 1.5rem;border-bottom:1px solid #F0F0F0;cursor:pointer;background:<?= $m['is_read']?'#fff':'#FAFFF7' ?>;" onmouseover="this.style.background='#F8FAF9'" onmouseout="this.style.background='<?= $m['is_read']?'#fff':'#FAFFF7' ?>'">
    <!-- Unread dot -->
    <div style="width:8px;height:8px;border-radius:50%;background:<?= $m['is_read']?'transparent':'var(--gyc-green-600)' ?>;flex-shrink:0;"></div>
    <!-- Icon -->
    <div style="width:36px;height:36px;border-radius:8px;background:var(--gyc-green-100);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i data-lucide="<?= $icon ?>" style="width:16px;height:16px;color:var(--gyc-green-700);"></i>
    </div>
    <!-- Content -->
    <div style="flex:1;min-width:0;">
      <div style="display:flex;align-items:center;gap:.75rem;justify-content:space-between;margin-bottom:.15rem;">
        <span style="font-weight:<?= $m['is_read']?'500':'700' ?>;font-size:.85rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($m['name']) ?></span>
        <span style="font-size:.72rem;color:#9CA3AF;flex-shrink:0;"><?= date('j M', strtotime($m['created_at'])) ?></span>
      </div>
      <div style="font-size:.82rem;font-weight:<?= $m['is_read']?'400':'600' ?>;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($m['subject']) ?></div>
      <div style="font-size:.75rem;color:#9CA3AF;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars(substr($m['message'],0,80)) ?>…</div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (empty($messages)): ?>
  <div style="padding:3.5rem;text-align:center;color:#9CA3AF;">
    <i data-lucide="inbox" style="width:40px;height:40px;stroke-width:1.5;display:block;margin:0 auto .75rem;"></i>
    <?= $filterRead==='unread' ? 'No unread messages. All caught up! 🎉' : 'No messages found.' ?>
  </div>
  <?php endif; ?>
</div>

<!-- Pagination -->
<?php $totalPages = (int)ceil($total / $perPage); if ($totalPages > 1): ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-top:1rem;">
  <span style="font-size:.78rem;color:#9CA3AF;"><?= $total ?> messages</span>
  <div style="display:flex;gap:.35rem;">
    <?php for ($p2=1;$p2<=$totalPages;$p2++): ?>
    <a href="?read=<?= $filterRead ?>&q=<?= urlencode($search) ?>&page=<?= $p2 ?>" style="padding:.35rem .6rem;border-radius:5px;font-size:.78rem;text-decoration:none;background:<?= $p2===$page?'var(--gyc-green-700)':'#F3F4F6' ?>;color:<?= $p2===$page?'#fff':'#374151' ?>;"><?= $p2 ?></a>
    <?php endfor; ?>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
