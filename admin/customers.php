<?php
define('GYC_ACCESS', true);
$adminPageTitle = 'Customers';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// ── View single customer ──
$viewId   = (int)($_GET['view'] ?? 0);
$viewUser = $viewId ? $db->fetchOne("SELECT * FROM users WHERE id = ? AND role = 'customer'", [$viewId]) : null;

if ($viewUser) {
    $userOrders = getOrdersByUser($viewId);
    $userApts   = $db->fetchAll(
        "SELECT a.*, gi.title as style_name FROM appointments a
         LEFT JOIN gallery_images gi ON a.gallery_image_id = gi.id
         WHERE a.user_id = ? ORDER BY a.requested_date DESC LIMIT 10",
        [$viewId]
    );
    $totalSpent = (float)($db->fetchOne(
        "SELECT COALESCE(SUM(total),0) s FROM orders WHERE user_id = ? AND payment_status = 'paid'",
        [$viewId]
    )['s'] ?? 0);
}

// ── Filters ──
$search = sanitize($_GET['q'] ?? '');
$limit  = 25;
$page   = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$sql    = "SELECT u.*,
            (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,
            (SELECT COALESCE(SUM(total),0) FROM orders WHERE user_id = u.id AND payment_status='paid') as total_spent
           FROM users u
           WHERE u.role = 'customer'";
$params = [];
if ($search) {
    $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params = array_fill(0, 4, "%$search%");
}
$total      = (int)($db->fetchOne(str_replace("SELECT u.*,\n            (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as order_count,\n            (SELECT COALESCE(SUM(total),0) FROM orders WHERE user_id = u.id AND payment_status='paid') as total_spent", "SELECT COUNT(*) AS total", $sql), $params)['total'] ?? 0);
$sql       .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
$params[]   = $limit;
$params[]   = $offset;
$customers  = $db->fetchAll($sql, $params);
$totalPages = (int)ceil($total / $limit);
?>

<?php if ($viewUser): ?>
<!-- ── SINGLE CUSTOMER ── -->
<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
  <a href="<?= SITE_URL ?>/admin/customers.php" style="color:#9CA3AF;text-decoration:none;font-size:.82rem;">← Customers</a>
  <h2 style="font-size:1rem;font-weight:700;"><?= htmlspecialchars($viewUser['first_name'] . ' ' . $viewUser['last_name']) ?></h2>
</div>

<div style="display:grid;grid-template-columns:280px 1fr;gap:1.5rem;align-items:start;">

  <!-- Profile card -->
  <div style="display:flex;flex-direction:column;gap:1.25rem;">
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.75rem;text-align:center;">
      <div style="width:72px;height:72px;border-radius:50%;background:var(--gyc-green-100);color:var(--gyc-green-700);display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;margin:0 auto 1rem;">
        <?= strtoupper(substr($viewUser['first_name'],0,1) . substr($viewUser['last_name'],0,1)) ?>
      </div>
      <div style="font-weight:700;font-size:1rem;"><?= htmlspecialchars($viewUser['first_name'] . ' ' . $viewUser['last_name']) ?></div>
      <div style="font-size:.82rem;color:#9CA3AF;margin:.2rem 0 1rem;"><?= htmlspecialchars($viewUser['email']) ?></div>
      <div style="display:flex;flex-direction:column;gap:.5rem;text-align:left;font-size:.83rem;color:#374151;">
        <?php if ($viewUser['phone']): ?>
        <div style="display:flex;gap:.6rem;">
          <i data-lucide="phone" style="width:14px;height:14px;color:#9CA3AF;flex-shrink:0;margin-top:.1rem;"></i>
          <?= htmlspecialchars($viewUser['phone']) ?>
        </div>
        <?php endif; ?>
        <div style="display:flex;gap:.6rem;">
          <i data-lucide="calendar" style="width:14px;height:14px;color:#9CA3AF;flex-shrink:0;margin-top:.1rem;"></i>
          Joined <?= date('j M Y', strtotime($viewUser['created_at'])) ?>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
      <div style="display:flex;flex-direction:column;gap:.85rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-size:.82rem;color:#9CA3AF;">Total Orders</span>
          <span style="font-weight:700;"><?= count($userOrders) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-size:.82rem;color:#9CA3AF;">Total Spent</span>
          <span style="font-weight:700;color:var(--gyc-green-700);"><?= formatPrice($totalSpent) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-size:.82rem;color:#9CA3AF;">Appointments</span>
          <span style="font-weight:700;"><?= count($userApts) ?></span>
        </div>
      </div>
    </div>

    <!-- WhatsApp -->
    <?php if ($viewUser['phone']): ?>
    <a href="<?= htmlspecialchars(whatsappMessage($viewUser['phone'], 'Hi ' . $viewUser['first_name'] . '! This is GYC Naturals reaching out.')) ?>"
       target="_blank" rel="noopener"
       style="display:flex;align-items:center;justify-content:center;gap:.6rem;background:#25D366;color:#fff;border-radius:12px;padding:.9rem;text-decoration:none;font-weight:600;font-size:.85rem;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
      WhatsApp Customer
    </a>
    <?php endif; ?>
  </div>

  <!-- Orders & appointments -->
  <div style="display:flex;flex-direction:column;gap:1.5rem;">

    <!-- Orders -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;">
      <div style="padding:1.1rem 1.5rem;border-bottom:1px solid #E5E7EB;font-weight:700;font-size:.88rem;">Orders</div>
      <?php if (empty($userOrders)): ?>
      <p style="padding:1.5rem;color:#9CA3AF;font-size:.85rem;">No orders yet.</p>
      <?php else: ?>
      <table style="width:100%;border-collapse:collapse;">
        <tbody>
          <?php foreach ($userOrders as $ord): ?>
          <tr style="border-bottom:1px solid #F0F0F0;">
            <td style="padding:.7rem 1.25rem;">
              <a href="<?= SITE_URL ?>/admin/orders.php?view=<?= $ord['id'] ?>" style="font-weight:600;font-size:.83rem;color:var(--gyc-green-700);text-decoration:none;"><?= htmlspecialchars($ord['order_number']) ?></a>
              <div style="font-size:.72rem;color:#9CA3AF;"><?= date('j M Y', strtotime($ord['created_at'])) ?></div>
            </td>
            <td style="padding:.7rem 1.25rem;font-size:.82rem;font-weight:600;"><?= formatPrice($ord['total']) ?></td>
            <td style="padding:.7rem 1.25rem;">
              <span style="font-size:.72rem;font-weight:700;padding:.2rem .5rem;border-radius:20px;background:#ECFDF5;color:#065F46;"><?= ucfirst($ord['payment_status']) ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

    <!-- Appointments -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;">
      <div style="padding:1.1rem 1.5rem;border-bottom:1px solid #E5E7EB;font-weight:700;font-size:.88rem;">Appointments</div>
      <?php if (empty($userApts)): ?>
      <p style="padding:1.5rem;color:#9CA3AF;font-size:.85rem;">No appointments yet.</p>
      <?php else: ?>
      <div style="display:flex;flex-direction:column;">
        <?php foreach ($userApts as $apt): ?>
        <div style="padding:.85rem 1.25rem;border-bottom:1px solid #F0F0F0;display:flex;justify-content:space-between;align-items:center;">
          <div>
            <div style="font-weight:600;font-size:.83rem;"><?= htmlspecialchars($apt['style_name'] ?? 'Style TBD') ?></div>
            <div style="font-size:.75rem;color:#9CA3AF;"><?= date('j M Y', strtotime($apt['requested_date'])) ?> · <?= htmlspecialchars($apt['appointment_number']) ?></div>
          </div>
          <span style="font-size:.72rem;font-weight:700;color:<?= ['pending'=>'#F59E0B','confirmed'=>'#10B981','cancelled'=>'#EF4444','completed'=>'#3B82F6'][$apt['status']] ?? '#9CA3AF' ?>;"><?= ucfirst($apt['status']) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php else: ?>
<!-- ── CUSTOMER LIST ── -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
  <span style="font-size:.85rem;color:#9CA3AF;"><?= $total ?> customer<?= $total !== 1 ? 's' : '' ?></span>
  <form method="GET" style="display:flex;gap:.5rem;">
    <input type="text" name="q" class="form-control" placeholder="Name, email or phone…"
           value="<?= htmlspecialchars($search) ?>" style="height:34px;padding:.35rem .7rem;width:220px;">
    <button type="submit" class="btn btn-outline-green btn-sm" style="height:34px;">Search</button>
    <?php if ($search): ?>
    <a href="<?= SITE_URL ?>/admin/customers.php" class="btn btn-sm" style="height:34px;background:#F3F4F6;color:#374151;">Clear</a>
    <?php endif; ?>
  </form>
</div>

<div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;">
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="background:#F8FAF9;border-bottom:1px solid #E5E7EB;">
        <th style="padding:.65rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Customer</th>
        <th style="padding:.65rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Phone</th>
        <th style="padding:.65rem 1.25rem;text-align:center;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Orders</th>
        <th style="padding:.65rem 1.25rem;text-align:right;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Total Spent</th>
        <th style="padding:.65rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Joined</th>
        <th style="padding:.65rem 1.25rem;text-align:right;"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($customers as $cust): ?>
      <tr style="border-bottom:1px solid #F0F0F0;cursor:pointer;" onclick="window.location='?view=<?= $cust['id'] ?>'"
          onmouseover="this.style.background='#FAFAFA'" onmouseout="this.style.background=''">
        <td style="padding:.85rem 1.25rem;">
          <div style="display:flex;align-items:center;gap:.75rem;">
            <div style="width:36px;height:36px;border-radius:50%;background:var(--gyc-green-100);color:var(--gyc-green-700);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;flex-shrink:0;">
              <?= strtoupper(substr($cust['first_name'],0,1) . substr($cust['last_name'],0,1)) ?>
            </div>
            <div>
              <div style="font-weight:600;font-size:.85rem;"><?= htmlspecialchars($cust['first_name'] . ' ' . $cust['last_name']) ?></div>
              <div style="font-size:.75rem;color:#9CA3AF;"><?= htmlspecialchars($cust['email']) ?></div>
            </div>
          </div>
        </td>
        <td style="padding:.85rem 1.25rem;font-size:.82rem;color:#374151;"><?= htmlspecialchars($cust['phone'] ?? '—') ?></td>
        <td style="padding:.85rem 1.25rem;text-align:center;font-size:.85rem;font-weight:600;"><?= $cust['order_count'] ?></td>
        <td style="padding:.85rem 1.25rem;text-align:right;font-size:.85rem;font-weight:600;color:var(--gyc-green-700);"><?= formatPrice($cust['total_spent']) ?></td>
        <td style="padding:.85rem 1.25rem;font-size:.78rem;color:#9CA3AF;"><?= date('j M Y', strtotime($cust['created_at'])) ?></td>
        <td style="padding:.85rem 1.25rem;text-align:right;">
          <a href="?view=<?= $cust['id'] ?>" onclick="event.stopPropagation();"
             style="font-size:.75rem;color:var(--gyc-green-600);text-decoration:none;">View →</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($customers)): ?>
      <tr><td colspan="6" style="padding:3rem;text-align:center;color:#9CA3AF;">No customers found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($totalPages > 1): ?>
<div style="display:flex;justify-content:center;gap:.5rem;margin-top:1.5rem;">
  <?php for ($p = 1; $p <= $totalPages; $p++): ?>
  <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"
     class="btn btn-sm <?= $p === $page ? 'btn-green' : 'btn-outline-green' ?>"><?= $p ?></a>
  <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
