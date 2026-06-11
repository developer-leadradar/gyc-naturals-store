<?php
define('GYC_ACCESS', true);
$adminPageTitle = 'Appointments';
require_once __DIR__ . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/email-templates.php';

$db = getDB();

// ── POST: update status ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize($_POST['action'] ?? '');
    $aptId  = (int)($_POST['apt_id'] ?? 0);
    if ($action === 'update_status' && $aptId) {
        $newStatus = sanitize($_POST['status'] ?? '');
        $adminNote = trim(sanitize($_POST['admin_note'] ?? ''));
        $notify    = !empty($_POST['notify_customer']);
        $allowed   = ['pending','confirmed','cancelled','completed','no_show'];
        if (in_array($newStatus, $allowed)) {
            $upd = ['status' => $newStatus];
            if ($newStatus === 'confirmed') $upd['confirmed_at'] = date('Y-m-d H:i:s');
            $db->update('appointments', $upd, 'id=?', [$aptId]);
            // Send email notification if requested and status maps to email
            if ($notify && in_array($newStatus, ['confirmed','cancelled','rescheduled'])) {
                $apt = $db->fetchOne(
                    "SELECT a.*, gi.title as style_name FROM appointments a
                     LEFT JOIN gallery_images gi ON a.gallery_image_id = gi.id WHERE a.id=?",
                    [$aptId]
                );
                if ($apt && !empty($apt['customer_email'])) {
                    $aptEmailData = [
                        'appointment_number' => $apt['appointment_number'],
                        'customer_name'      => $apt['customer_name'],
                        'requested_date'     => $apt['requested_date'],
                        'requested_time'     => $apt['requested_time'],
                        'style_name'         => $apt['style_name'] ?? 'Hair Appointment',
                    ];
                    $emailHtml = emailAppointmentUpdate($aptEmailData, $newStatus, $adminNote);
                    sendEmail($apt['customer_email'], 'Appointment ' . ucfirst($newStatus) . ' — GYC Naturals', $emailHtml);
                }
            }
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Appointment status updated.' . ($notify ? ' Customer notified.' : '')];
        }
    } elseif ($action === 'mark_deposit' && $aptId) {
        $amount = (float)($_POST['deposit_amount'] ?? 0);
        $db->update('appointments', [
            'deposit_paid'   => 1,
            'deposit_amount' => $amount,
        ], 'id=?', [$aptId]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Deposit marked as paid.'];
    }
    $viewId = (int)($_POST['view_id'] ?? 0);
    redirect(SITE_URL . '/admin/appointments.php' . ($viewId ? '?view=' . $viewId : ''));
    exit;
}

// ── Single appointment view ──
$viewId = (int)($_GET['view'] ?? 0);
$viewApt = $viewId ? $db->fetchOne(
    "SELECT a.*, gi.title as style_name, gi.image_url as style_image
     FROM appointments a LEFT JOIN gallery_images gi ON a.gallery_image_id = gi.id
     WHERE a.id = ?", [$viewId]
) : null;

// ── List filters ──
$statusFilter = sanitize($_GET['status'] ?? '');
$search       = sanitize($_GET['q']      ?? '');
$dateFilter   = sanitize($_GET['date']   ?? '');
$limit  = 25;
$page   = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$sql    = "SELECT a.*, gi.title as style_name FROM appointments a LEFT JOIN gallery_images gi ON a.gallery_image_id = gi.id WHERE 1=1";
$params = [];
if ($statusFilter) { $sql .= " AND a.status = ?"; $params[] = $statusFilter; }
if ($search)       { $sql .= " AND (a.customer_name LIKE ? OR a.customer_phone LIKE ? OR a.appointment_number LIKE ?)"; for($x=0;$x<3;$x++) $params[] = "%$search%"; }
if ($dateFilter)   { $sql .= " AND a.requested_date = ?"; $params[] = $dateFilter; }

$total = (int)($db->fetchOne(str_replace("SELECT a.*, gi.title as style_name","SELECT COUNT(*) AS total",$sql), $params)['total'] ?? 0);
$sql  .= " ORDER BY a.requested_date DESC, a.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit; $params[] = $offset;
$apts     = $db->fetchAll($sql, $params);
$totalPages = (int)ceil($total / $limit);

$statusColors = ['pending'=>'#F59E0B','confirmed'=>'#10B981','cancelled'=>'#EF4444','completed'=>'#3B82F6','no_show'=>'#9CA3AF'];
$waPhone = getSetting('site_whatsapp') ?: SITE_WHATSAPP;
?>

<?php if ($viewApt): ?>
<!-- ── SINGLE APPOINTMENT VIEW ── -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
  <div style="display:flex;align-items:center;gap:1rem;">
    <a href="<?= SITE_URL ?>/admin/appointments.php" style="color:#9CA3AF;text-decoration:none;font-size:.82rem;">← Appointments</a>
    <h2 style="font-size:1rem;font-weight:700;"><?= htmlspecialchars($viewApt['appointment_number']) ?></h2>
    <span style="font-size:.78rem;font-weight:700;padding:.2rem .6rem;border-radius:20px;background:<?= $statusColors[$viewApt['status']] ?? '#9CA3AF' ?>20;color:<?= $statusColors[$viewApt['status']] ?? '#9CA3AF' ?>;"><?= ucfirst(str_replace('_',' ',$viewApt['status'])) ?></span>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;">
  <!-- Details -->
  <div style="display:flex;flex-direction:column;gap:1.5rem;">
    <!-- Style + date -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;">
      <div style="display:flex;gap:1.5rem;padding:1.5rem;align-items:center;">
        <?php if ($viewApt['style_image']): ?>
        <img src="<?= htmlspecialchars($viewApt['style_image']) ?>" alt="" style="width:100px;height:130px;object-fit:cover;border-radius:8px;flex-shrink:0;">
        <?php endif; ?>
        <div>
          <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.1em;color:var(--gyc-green-500);font-weight:700;margin-bottom:.3rem;">Requested Style</div>
          <div style="font-family:'Playfair Display',serif;font-size:1.2rem;margin-bottom:.5rem;"><?= htmlspecialchars($viewApt['style_name'] ?? 'Style to be decided') ?></div>
          <div style="font-size:.88rem;color:#374151;">
            📅 <?= date('l jS F Y', strtotime($viewApt['requested_date'])) ?>
            <?php if ($viewApt['requested_time']): ?>— <?= date('g:i A', strtotime($viewApt['requested_time'])) ?><?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <!-- Customer info -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
      <div style="font-weight:700;font-size:.88rem;margin-bottom:1rem;">Customer Details</div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;font-size:.85rem;color:#374151;">
        <div><span style="color:#9CA3AF;font-size:.75rem;display:block;">Name</span><?= htmlspecialchars($viewApt['customer_name']) ?></div>
        <div><span style="color:#9CA3AF;font-size:.75rem;display:block;">Phone</span><?= htmlspecialchars($viewApt['customer_phone']) ?></div>
        <?php if ($viewApt['customer_email']): ?>
        <div><span style="color:#9CA3AF;font-size:.75rem;display:block;">Email</span><?= htmlspecialchars($viewApt['customer_email']) ?></div>
        <?php endif; ?>
        <?php if ($viewApt['notes']): ?>
        <div style="grid-column:1/-1;"><span style="color:#9CA3AF;font-size:.75rem;display:block;">Notes</span><?= htmlspecialchars($viewApt['notes']) ?></div>
        <?php endif; ?>
      </div>
    </div>
    <!-- Deposit -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
      <div style="font-weight:700;font-size:.88rem;margin-bottom:1rem;">Deposit</div>
      <?php if ($viewApt['deposit_paid']): ?>
      <div style="color:#065F46;font-weight:600;font-size:.9rem;">✓ Paid — <?= $viewApt['deposit_amount'] ? formatPrice($viewApt['deposit_amount']) : 'Amount not recorded' ?></div>
      <?php if ($viewApt['paystack_ref']): ?>
      <div style="font-size:.75rem;color:#9CA3AF;margin-top:.35rem;">Ref: <?= htmlspecialchars($viewApt['paystack_ref']) ?></div>
      <?php endif; ?>
      <?php else: ?>
      <div style="color:#F59E0B;font-weight:600;font-size:.88rem;margin-bottom:.85rem;">⏳ Pending</div>
      <form method="POST" style="display:flex;gap:.75rem;align-items:center;">
        <input type="hidden" name="action" value="mark_deposit">
        <input type="hidden" name="apt_id" value="<?= $viewApt['id'] ?>">
        <input type="hidden" name="view_id" value="<?= $viewApt['id'] ?>">
        <input type="number" name="deposit_amount" class="form-control" placeholder="Amount (₦)" min="0" style="height:34px;padding:.35rem .7rem;">
        <button type="submit" class="btn btn-outline-green btn-sm" style="height:34px;white-space:nowrap;">Mark as Paid</button>
      </form>
      <?php endif; ?>
    </div>
  </div>

  <!-- Actions sidebar -->
  <div style="display:flex;flex-direction:column;gap:1.25rem;position:sticky;top:80px;">
    <!-- Status update -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
      <div style="font-weight:700;font-size:.88rem;margin-bottom:.85rem;">Update Status</div>
      <form method="POST" style="display:flex;flex-direction:column;gap:.75rem;">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="apt_id" value="<?= $viewApt['id'] ?>">
        <input type="hidden" name="view_id" value="<?= $viewApt['id'] ?>">
        <select name="status" class="form-control">
          <?php foreach (['pending','confirmed','completed','cancelled','no_show'] as $s): ?>
          <option value="<?= $s ?>" <?= $viewApt['status'] === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
          <?php endforeach; ?>
        </select>
        <textarea name="admin_note" class="form-control" rows="2" placeholder="Optional note to client…" style="font-size:.82rem;resize:vertical;"></textarea>
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.83rem;cursor:pointer;color:#374151;">
          <input type="checkbox" name="notify_customer" value="1" checked style="width:14px;height:14px;">
          Email client about this update
        </label>
        <button type="submit" class="btn btn-green btn-sm">Update</button>
      </form>
    </div>
    <!-- WhatsApp -->
    <?php
    $confirmMsg = "Hi {$viewApt['customer_name']}! 🌿 Your GYC Naturals appointment ({$viewApt['appointment_number']}) on " . date('D jS M', strtotime($viewApt['requested_date'])) . " is CONFIRMED. We can't wait to see you! Any questions? Reply here.";
    $waUrl = whatsappMessage($viewApt['customer_phone'], $confirmMsg);
    ?>
    <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" rel="noopener"
       style="display:flex;align-items:center;gap:.6rem;background:#25D366;color:#fff;border-radius:12px;padding:1rem 1.25rem;text-decoration:none;font-weight:600;font-size:.85rem;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
      Send Confirmation
    </a>
  </div>
</div>

<?php else: ?>
<!-- ── LIST ── -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
  <span style="font-size:.85rem;color:#9CA3AF;"><?= $total ?> appointment<?= $total!==1?'s':'' ?></span>
  <form method="GET" style="display:flex;gap:.5rem;flex-wrap:wrap;">
    <input type="text" name="q" class="form-control" placeholder="Name, phone, ref…" value="<?= htmlspecialchars($search) ?>" style="height:34px;padding:.35rem .7rem;width:180px;">
    <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($dateFilter) ?>" style="height:34px;padding:.35rem .7rem;">
    <select name="status" class="form-control" style="height:34px;padding:.35rem .7rem;">
      <option value="">All Status</option>
      <?php foreach (['pending','confirmed','completed','cancelled','no_show'] as $s): ?>
      <option value="<?= $s ?>" <?= $statusFilter===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-outline-green btn-sm" style="height:34px;">Filter</button>
    <?php if ($search||$statusFilter||$dateFilter): ?>
    <a href="<?= SITE_URL ?>/admin/appointments.php" class="btn btn-sm" style="height:34px;background:#F3F4F6;color:#374151;">Clear</a>
    <?php endif; ?>
  </form>
</div>

<div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;">
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="background:#F8FAF9;border-bottom:1px solid #E5E7EB;">
        <th style="padding:.65rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Ref</th>
        <th style="padding:.65rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Customer</th>
        <th style="padding:.65rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Style</th>
        <th style="padding:.65rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Date</th>
        <th style="padding:.65rem 1.25rem;text-align:center;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Deposit</th>
        <th style="padding:.65rem 1.25rem;text-align:center;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Status</th>
        <th style="padding:.65rem 1.25rem;text-align:right;"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($apts as $apt): ?>
      <tr style="border-bottom:1px solid #F0F0F0;cursor:pointer;" onclick="window.location='?view=<?= $apt['id'] ?>'"
          onmouseover="this.style.background='#FAFAFA'" onmouseout="this.style.background=''">
        <td style="padding:.8rem 1.25rem;font-size:.8rem;font-weight:600;color:var(--gyc-green-700);"><?= htmlspecialchars($apt['appointment_number']) ?></td>
        <td style="padding:.8rem 1.25rem;">
          <div style="font-weight:600;font-size:.83rem;"><?= htmlspecialchars($apt['customer_name']) ?></div>
          <div style="font-size:.72rem;color:#9CA3AF;"><?= htmlspecialchars($apt['customer_phone']) ?></div>
        </td>
        <td style="padding:.8rem 1.25rem;font-size:.82rem;color:#374151;"><?= htmlspecialchars($apt['style_name'] ?? '—') ?></td>
        <td style="padding:.8rem 1.25rem;font-size:.82rem;color:#374151;"><?= date('j M Y', strtotime($apt['requested_date'])) ?></td>
        <td style="padding:.8rem 1.25rem;text-align:center;">
          <span style="font-size:.72rem;font-weight:700;color:<?= $apt['deposit_paid']?'#065F46':'#F59E0B' ?>;"><?= $apt['deposit_paid']?'✓ Paid':'Pending' ?></span>
        </td>
        <td style="padding:.8rem 1.25rem;text-align:center;">
          <span style="font-size:.72rem;font-weight:700;padding:.2rem .5rem;border-radius:20px;background:<?= $statusColors[$apt['status']]??'#9CA3AF' ?>20;color:<?= $statusColors[$apt['status']]??'#9CA3AF' ?>;">
            <?= ucfirst(str_replace('_',' ',$apt['status'])) ?>
          </span>
        </td>
        <td style="padding:.8rem 1.25rem;text-align:right;">
          <a href="?view=<?= $apt['id'] ?>" onclick="event.stopPropagation();" style="font-size:.75rem;color:var(--gyc-green-600);text-decoration:none;">View →</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($apts)): ?><tr><td colspan="7" style="padding:3rem;text-align:center;color:#9CA3AF;">No appointments found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php if ($totalPages > 1): ?>
<div style="display:flex;justify-content:center;gap:.5rem;margin-top:1.5rem;">
  <?php for ($p=1;$p<=$totalPages;$p++): ?>
  <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>" class="btn btn-sm <?= $p===$page?'btn-green':'btn-outline-green' ?>"><?= $p ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
