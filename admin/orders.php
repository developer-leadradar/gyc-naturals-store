<?php
define('GYC_ACCESS', true);
$adminPageTitle = 'Orders';
require_once __DIR__ . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/email-templates.php';

$db = getDB();

// ── Update order status ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = sanitize($_POST['action'] ?? '');
    $orderId = (int)($_POST['order_id'] ?? 0);

    if ($action === 'update_status' && $orderId) {
        $newStatus = sanitize($_POST['status'] ?? '');
        $adminNote = trim(sanitize($_POST['admin_note'] ?? ''));
        $notify    = !empty($_POST['notify_customer']);
        $allowed   = ['pending','processing','shipped','delivered','cancelled','refunded'];
        if (in_array($newStatus, $allowed)) {
            $db->update('orders', ['status' => $newStatus], 'id=?', [$orderId]);
            // Send customer email notification if requested
            if ($notify) {
                $ord = $db->fetchOne(
                    "SELECT o.*, CONCAT(o.shipping_first_name,' ',o.shipping_last_name) as billing_name,
                            COALESCE(o.customer_email, u.email, '') as customer_email
                     FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id=?",
                    [$orderId]
                );
                if ($ord && !empty($ord['customer_email']) && in_array($newStatus, ['processing','shipped','delivered','cancelled'])) {
                    $emailHtml = emailOrderStatusUpdate($ord, $newStatus, $adminNote);
                    sendEmail($ord['customer_email'], 'Your GYC Naturals Order Update: ' . ucfirst($newStatus), $emailHtml);
                }
            }
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Order status updated.' . ($notify ? ' Customer notified.' : '')];
        }
    } elseif ($action === 'update_payment' && $orderId) {
        $newPay  = sanitize($_POST['payment_status'] ?? '');
        $allowed = ['pending','paid','failed','refunded'];
        if (in_array($newPay, $allowed)) {
            $db->update('orders', ['payment_status' => $newPay], 'id=?', [$orderId]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Payment status updated.'];
        }
    } elseif ($action === 'add_tracking' && $orderId) {
        $tracking = trim(sanitize($_POST['tracking_number'] ?? ''));
        $carrier  = trim(sanitize($_POST['carrier'] ?? ''));
        $db->update('orders', [
            'tracking_number' => $tracking,
            'shipped_at'      => date('Y-m-d H:i:s'),
        ], 'id=?', [$orderId]);
        if (!empty($tracking)) {
            $db->update('orders', ['status' => 'shipped'], 'id=?', [$orderId]);
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Tracking number saved.'];
    }
    redirect(SITE_URL . '/admin/orders.php' . (isset($_POST['view_id']) ? '?view=' . (int)$_POST['view_id'] : ''));
    exit;
}

// ── View single order ──
$viewId    = (int)($_GET['view'] ?? 0);
$viewOrder = $viewId ? $db->fetchOne(
    "SELECT o.*,
            CONCAT(o.shipping_first_name,' ',o.shipping_last_name) as billing_name,
            o.shipping_phone as billing_phone,
            COALESCE(o.customer_email, u.email, '') as customer_email
     FROM orders o
     LEFT JOIN users u ON o.user_id = u.id
     WHERE o.id = ?",
    [$viewId]
) : null;
$viewItems = $viewOrder ? getOrderItems($viewId) : [];

// ── List filters ──
$statusFilter = sanitize($_GET['status'] ?? '');
$payFilter    = sanitize($_GET['pay']    ?? '');
$search       = sanitize($_GET['q']      ?? '');
$limit  = 20;
$page   = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$sql    = "SELECT o.*, CONCAT(u.first_name,' ',u.last_name) as customer_name
           FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE 1=1";
$params = [];
if ($statusFilter) { $sql .= " AND o.status = ?"; $params[] = $statusFilter; }
if ($payFilter)    { $sql .= " AND o.payment_status = ?"; $params[] = $payFilter; }
if ($search)       { $sql .= " AND (o.order_number LIKE ? OR o.shipping_first_name LIKE ? OR o.shipping_last_name LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
$total = (int)($db->fetchOne(str_replace("SELECT o.*", "SELECT COUNT(*) AS total", $sql), $params)['total'] ?? 0);
$sql  .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit; $params[] = $offset;
$orders   = $db->fetchAll($sql, $params);
$totalPages = (int)ceil($total / $limit);

$statusColors = ['pending'=>'#F59E0B','processing'=>'#3B82F6','shipped'=>'#8B5CF6','delivered'=>'#10B981','cancelled'=>'#EF4444','refunded'=>'#9CA3AF'];
$payColors    = ['pending'=>'#F59E0B','paid'=>'#10B981','failed'=>'#EF4444','refunded'=>'#9CA3AF'];
?>

<?php if ($viewOrder): ?>
<!-- ── SINGLE ORDER VIEW ── -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
  <div style="display:flex;align-items:center;gap:1rem;">
    <a href="<?= SITE_URL ?>/admin/orders.php" style="color:#9CA3AF;text-decoration:none;font-size:.82rem;">← Orders</a>
    <h2 style="font-size:1rem;font-weight:700;"><?= htmlspecialchars($viewOrder['order_number']) ?></h2>
    <span style="font-size:.78rem;font-weight:700;padding:.2rem .6rem;border-radius:20px;background:<?= $payColors[$viewOrder['payment_status']] ?? '#9CA3AF' ?>20;color:<?= $payColors[$viewOrder['payment_status']] ?? '#9CA3AF' ?>;"><?= ucfirst($viewOrder['payment_status']) ?></span>
  </div>
  <div style="font-size:.8rem;color:#9CA3AF;"><?= date('jS M Y, g:i A', strtotime($viewOrder['created_at'])) ?></div>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;">
  <!-- Order items & details -->
  <div style="display:flex;flex-direction:column;gap:1.5rem;">

    <!-- Items -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;">
      <div style="padding:1.1rem 1.5rem;border-bottom:1px solid #E5E7EB;font-weight:700;font-size:.88rem;">Order Items</div>
      <table style="width:100%;border-collapse:collapse;">
        <tbody>
          <?php foreach ($viewItems as $item): ?>
          <tr style="border-bottom:1px solid #F0F0F0;">
            <td style="padding:.85rem 1.25rem;">
              <div style="display:flex;align-items:center;gap:.75rem;">
                <img src="<?= htmlspecialchars($item['image'] ?? '') ?>" alt="" style="width:44px;height:44px;border-radius:8px;object-fit:cover;border:1px solid #E5E7EB;">
                <div>
                  <div style="font-weight:600;font-size:.85rem;"><?= htmlspecialchars($item['product_name']) ?></div>
                  <?php if (!empty($item['variant'])): ?>
                  <div style="font-size:.75rem;color:#9CA3AF;"><?= htmlspecialchars($item['variant']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td style="padding:.85rem 1.25rem;text-align:center;font-size:.82rem;color:#9CA3AF;">×<?= $item['quantity'] ?></td>
            <td style="padding:.85rem 1.25rem;text-align:right;font-size:.85rem;font-weight:600;"><?= formatPrice($item['price'] * $item['quantity']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot style="background:#F8FAF9;">
          <tr><td colspan="2" style="padding:.6rem 1.25rem;font-size:.8rem;color:#9CA3AF;">Subtotal</td><td style="padding:.6rem 1.25rem;text-align:right;font-size:.82rem;"><?= formatPrice($viewOrder['subtotal']) ?></td></tr>
          <tr><td colspan="2" style="padding:.6rem 1.25rem;font-size:.8rem;color:#9CA3AF;">Shipping</td><td style="padding:.6rem 1.25rem;text-align:right;font-size:.82rem;"><?= $viewOrder['shipping'] > 0 ? formatPrice($viewOrder['shipping']) : 'Free' ?></td></tr>
          <tr><td colspan="2" style="padding:.75rem 1.25rem;font-weight:700;">Total</td><td style="padding:.75rem 1.25rem;text-align:right;font-weight:700;color:var(--gyc-green-700);font-size:.95rem;"><?= formatPrice($viewOrder['total']) ?></td></tr>
        </tfoot>
      </table>
    </div>

    <!-- Shipping address -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
      <div style="font-weight:700;font-size:.88rem;margin-bottom:.85rem;">Shipping Address</div>
      <p style="font-size:.85rem;line-height:1.7;color:#374151;">
        <?= htmlspecialchars($viewOrder['billing_name']) ?><br>
        <?= htmlspecialchars($viewOrder['shipping_address'] ?? '') ?><br>
        <?= htmlspecialchars($viewOrder['shipping_city'] ?? '') ?>, <?= htmlspecialchars($viewOrder['shipping_state'] ?? '') ?><br>
        <?= htmlspecialchars($viewOrder['shipping_country'] ?? 'Nigeria') ?>
      </p>
      <?php if ($viewOrder['billing_phone']): ?>
      <p style="font-size:.82rem;margin-top:.6rem;color:#374151;">📞 <?= htmlspecialchars($viewOrder['billing_phone']) ?></p>
      <?php endif; ?>
      <?php if ($viewOrder['customer_email'] ?? $viewOrder['billing_email']): ?>
      <p style="font-size:.82rem;color:#374151;">✉ <?= htmlspecialchars($viewOrder['customer_email'] ?? $viewOrder['billing_email']) ?></p>
      <?php endif; ?>
      <?php if ($viewOrder['notes']): ?>
      <div style="margin-top:.85rem;padding:.75rem;background:#FFFBEB;border-radius:8px;font-size:.82rem;color:#92400E;">
        <strong>Notes:</strong> <?= htmlspecialchars($viewOrder['notes']) ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Tracking -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
      <div style="font-weight:700;font-size:.88rem;margin-bottom:.85rem;">Tracking Number</div>
      <form method="POST">
        <input type="hidden" name="action" value="add_tracking">
        <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
        <input type="hidden" name="view_id" value="<?= $viewOrder['id'] ?>">
        <div style="display:flex;gap:.75rem;">
          <input type="text" name="tracking_number" class="form-control" placeholder="e.g. GIG12345678"
                 value="<?= htmlspecialchars($viewOrder['tracking_number'] ?? '') ?>">
          <button type="submit" class="btn btn-outline-green btn-sm">Save</button>
        </div>
      </form>
      <?php if ($viewOrder['tracking_number']): ?>
      <p style="font-size:.78rem;color:var(--gyc-green-600);margin-top:.5rem;">Shipped: <?= $viewOrder['shipped_at'] ? date('j M Y', strtotime($viewOrder['shipped_at'])) : 'date unknown' ?></p>
      <?php endif; ?>
    </div>

  </div>

  <!-- Status sidebar -->
  <div style="display:flex;flex-direction:column;gap:1.25rem;position:sticky;top:80px;">

    <!-- Update order status -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
      <div style="font-weight:700;font-size:.88rem;margin-bottom:.85rem;">Order Status</div>
      <form method="POST" style="display:flex;flex-direction:column;gap:.75rem;">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
        <input type="hidden" name="view_id" value="<?= $viewOrder['id'] ?>">
        <select name="status" class="form-control">
          <?php foreach (['pending','processing','shipped','delivered','cancelled','refunded'] as $s): ?>
          <option value="<?= $s ?>" <?= $viewOrder['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
        <textarea name="admin_note" class="form-control" rows="2" placeholder="Optional note to customer…" style="font-size:.82rem;resize:vertical;"></textarea>
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.83rem;cursor:pointer;color:#374151;">
          <input type="checkbox" name="notify_customer" value="1" checked style="width:14px;height:14px;">
          Email customer about this update
        </label>
        <button type="submit" class="btn btn-green btn-sm">Update Status</button>
      </form>
    </div>

    <!-- Update payment status -->
    <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
      <div style="font-weight:700;font-size:.88rem;margin-bottom:.85rem;">Payment Status</div>
      <form method="POST" style="display:flex;flex-direction:column;gap:.75rem;">
        <input type="hidden" name="action" value="update_payment">
        <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
        <input type="hidden" name="view_id" value="<?= $viewOrder['id'] ?>">
        <select name="payment_status" class="form-control">
          <?php foreach (['pending','paid','failed','refunded'] as $s): ?>
          <option value="<?= $s ?>" <?= $viewOrder['payment_status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-outline-green btn-sm">Update Payment</button>
      </form>
    </div>

    <!-- WhatsApp customer -->
    <?php
    $custPhone = $viewOrder['billing_phone'] ?? '';
    $custPhone = preg_replace('/[^0-9]/', '', $custPhone);
    if ($custPhone) {
        $waMsg = "Hi {$viewOrder['billing_name']}! 👋 Your GYC Naturals order {$viewOrder['order_number']} is now " . strtoupper($viewOrder['status']) . ". Thank you for shopping with us! 🌿";
        $waUrl  = whatsappMessage($viewOrder['billing_phone'], $waMsg);
    }
    ?>
    <?php if ($custPhone): ?>
    <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" rel="noopener"
       style="display:flex;align-items:center;gap:.75rem;background:#25D366;color:#fff;border-radius:12px;padding:1.1rem 1.25rem;text-decoration:none;font-weight:600;font-size:.85rem;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
      WhatsApp Customer
    </a>
    <?php endif; ?>

    <!-- Paystack ref -->
    <?php if (!empty($viewOrder['paystack_ref'])): ?>
    <div style="background:#F0FFF4;border:1px solid #BBF7D0;border-radius:12px;padding:1rem;">
      <div style="font-size:.72rem;color:#065F46;font-weight:700;margin-bottom:.3rem;">Paystack Reference</div>
      <div style="font-family:monospace;font-size:.78rem;color:#374151;"><?= htmlspecialchars($viewOrder['paystack_ref']) ?></div>
    </div>
    <?php endif; ?>

  </div>
</div>

<?php else: ?>
<!-- ── ORDERS LIST ── -->
<div class="admin-filter-bar">
  <div class="admin-filter-summary">
    <i data-lucide="shopping-bag"></i>
    <strong><?= $total ?></strong> order<?= $total !== 1 ? 's' : '' ?>
    <?php if ($statusFilter || $payFilter || $search): ?>
    <span class="admin-filter-active-chip">filtered</span>
    <?php endif; ?>
  </div>
  <form method="GET" class="admin-filter-form">
    <div class="admin-filter-field admin-filter-field-search">
      <i data-lucide="search"></i>
      <input type="text" name="q" placeholder="Order # or customer name…" value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="admin-filter-field">
      <i data-lucide="package"></i>
      <select name="status">
        <option value="">All statuses</option>
        <?php foreach (['pending','processing','shipped','delivered','cancelled','refunded'] as $s): ?>
        <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="admin-filter-field">
      <i data-lucide="credit-card"></i>
      <select name="pay">
        <option value="">All payments</option>
        <?php foreach (['pending','paid','failed','refunded'] as $s): ?>
        <option value="<?= $s ?>" <?= $payFilter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="admin-filter-actions">
      <button type="submit" class="btn btn-green btn-sm">Apply</button>
      <?php if ($search || $statusFilter || $payFilter): ?>
      <a href="<?= SITE_URL ?>/admin/orders.php" class="admin-filter-clear">Clear</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;">
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="background:#F8FAF9;border-bottom:1px solid #E5E7EB;">
        <th style="padding:.65rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Order</th>
        <th style="padding:.65rem 1.25rem;text-align:left;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Customer</th>
        <th style="padding:.65rem 1.25rem;text-align:right;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Total</th>
        <th style="padding:.65rem 1.25rem;text-align:center;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Payment</th>
        <th style="padding:.65rem 1.25rem;text-align:center;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;">Status</th>
        <th style="padding:.65rem 1.25rem;text-align:right;font-size:.72rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $ord): ?>
      <tr style="border-bottom:1px solid #F0F0F0;cursor:pointer;" onclick="window.location='?view=<?= $ord['id'] ?>'" onmouseover="this.style.background='#FAFAFA'" onmouseout="this.style.background=''">
        <td style="padding:.8rem 1.25rem;">
          <div style="font-weight:700;font-size:.85rem;color:var(--gyc-green-700);"><?= htmlspecialchars($ord['order_number']) ?></div>
          <div style="font-size:.72rem;color:#9CA3AF;"><?= date('j M Y', strtotime($ord['created_at'])) ?></div>
        </td>
        <td style="padding:.8rem 1.25rem;font-size:.83rem;color:#374151;"><?= htmlspecialchars($ord['customer_name'] ?? $ord['billing_name']) ?></td>
        <td style="padding:.8rem 1.25rem;text-align:right;font-weight:700;font-size:.85rem;"><?= formatPrice($ord['total']) ?></td>
        <td style="padding:.8rem 1.25rem;text-align:center;">
          <span style="font-size:.72rem;font-weight:700;padding:.2rem .55rem;border-radius:20px;background:<?= $payColors[$ord['payment_status']] ?? '#9CA3AF' ?>20;color:<?= $payColors[$ord['payment_status']] ?? '#9CA3AF' ?>;">
            <?= ucfirst($ord['payment_status']) ?>
          </span>
        </td>
        <td style="padding:.8rem 1.25rem;text-align:center;">
          <span style="font-size:.72rem;font-weight:700;padding:.2rem .55rem;border-radius:20px;background:<?= $statusColors[$ord['status']] ?? '#9CA3AF' ?>20;color:<?= $statusColors[$ord['status']] ?? '#9CA3AF' ?>;">
            <?= ucfirst($ord['status']) ?>
          </span>
        </td>
        <td style="padding:.8rem 1.25rem;text-align:right;">
          <a href="?view=<?= $ord['id'] ?>" onclick="event.stopPropagation();"
             style="font-size:.75rem;color:var(--gyc-green-600);text-decoration:none;">View →</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($orders)): ?>
      <tr><td colspan="6" style="padding:3rem;text-align:center;color:#9CA3AF;">No orders found.</td></tr>
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
