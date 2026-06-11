<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

// Tab redirect — ?tab=wishlist etc. forward to dedicated pages
$tab = sanitize($_GET['tab'] ?? '');
$tabMap = [
    'wishlist'     => '/my-wishlist.php',
    'orders'       => '/my-orders.php',
    'appointments' => '/my-appointments.php',
    'profile'      => '/my-profile.php',
];
if (isset($tabMap[$tab])) {
    redirect(SITE_URL . $tabMap[$tab]);
}

$user       = getCurrentUser();
$orders     = getOrdersByUser($user['id']);
$recentOrds = array_slice($orders, 0, 3);
$appointments = getDB()->fetchAll(
    "SELECT a.*, gi.title as style_name FROM appointments a
     LEFT JOIN gallery_images gi ON a.gallery_image_id = gi.id
     WHERE a.user_id = ? ORDER BY a.requested_date DESC LIMIT 3",
    [$user['id']]
);
$wishlistCount = getDB()->fetchOne("SELECT COUNT(*) as c FROM wishlist WHERE user_id = ?", [$user['id']]);
$orderCount    = count($orders);

$pageTitle = 'My Account — GYC Naturals';
require_once __DIR__ . '/includes/header.php';
?>

<div style="min-height:72px;"></div>

<section style="padding:2.5rem 0 5rem;background:#F8FAF9;">
  <div class="container" style="max-width:960px;">

    <!-- Welcome banner -->
    <div style="background:linear-gradient(135deg,var(--gyc-green-900),var(--gyc-green-700));border-radius:var(--gyc-radius-lg);padding:2rem 2rem 1.75rem;color:#fff;margin-bottom:2rem;display:flex;align-items:center;justify-content:space-between;gap:1.5rem;flex-wrap:wrap;">
      <div>
        <div style="font-size:.82rem;color:rgba(255,255,255,.65);margin-bottom:.25rem;">Welcome back</div>
        <h1 style="font-family:'Playfair Display',serif;font-size:1.6rem;color:#fff;margin:0 0 .3rem;">
          <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
        </h1>
        <p style="font-size:.85rem;color:rgba(255,255,255,.75);margin:0;"><?= htmlspecialchars($user['email']) ?></p>
      </div>
      <div style="display:flex;gap:1rem;flex-wrap:wrap;">
        <div style="text-align:center;background:rgba(255,255,255,.12);border-radius:var(--gyc-radius);padding:.75rem 1.25rem;">
          <div style="font-family:'Playfair Display',serif;font-size:1.5rem;color:#fff;font-weight:700;"><?= $orderCount ?></div>
          <div style="font-size:.72rem;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:.08em;">Orders</div>
        </div>
        <div style="text-align:center;background:rgba(255,255,255,.12);border-radius:var(--gyc-radius);padding:.75rem 1.25rem;">
          <div style="font-family:'Playfair Display',serif;font-size:1.5rem;color:#fff;font-weight:700;"><?= count($appointments) ?></div>
          <div style="font-size:.72rem;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:.08em;">Bookings</div>
        </div>
        <div style="text-align:center;background:rgba(255,255,255,.12);border-radius:var(--gyc-radius);padding:.75rem 1.25rem;">
          <div style="font-family:'Playfair Display',serif;font-size:1.5rem;color:#fff;font-weight:700;"><?= $wishlistCount['c'] ?? 0 ?></div>
          <div style="font-size:.72rem;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:.08em;">Wishlist</div>
        </div>
      </div>
    </div>

    <!-- Quick action cards -->
    <h2 style="font-family:'Playfair Display',serif;font-size:1.05rem;color:var(--gyc-dark);margin:0 0 1rem;">Quick Actions</h2>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem;">
      <?php
      $quickActions = [
        ['icon' => 'shopping-bag',   'label' => 'Shop Products',   'url' => SITE_URL . '/shop.php',               'color' => 'var(--gyc-green-700)'],
        ['icon' => 'calendar-check', 'label' => 'Book GYC',        'url' => SITE_URL . '/book-appointment.php',   'color' => 'var(--gyc-gold-600)'],
        ['icon' => 'sparkles',       'label' => 'Hair Quiz',        'url' => SITE_URL . '/quiz.php',               'color' => 'var(--gyc-terra)'],
        ['icon' => 'layout-grid',    'label' => 'Moodboard',        'url' => SITE_URL . '/moodboard.php',          'color' => '#6366F1'],
        ['icon' => 'package',        'label' => 'My Orders',        'url' => SITE_URL . '/my-orders.php',          'color' => '#0EA5E9'],
        ['icon' => 'scissors',       'label' => 'Appointments',     'url' => SITE_URL . '/my-appointments.php',    'color' => '#8B5CF6'],
        ['icon' => 'heart',          'label' => 'Wishlist',         'url' => SITE_URL . '/my-wishlist.php',        'color' => '#E53E3E'],
        ['icon' => 'user-circle',    'label' => 'My Profile',       'url' => SITE_URL . '/my-profile.php',         'color' => '#6B7280'],
      ];
      foreach ($quickActions as $qa):
      ?>
      <a href="<?= $qa['url'] ?>"
         style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.5rem;padding:1.25rem 1rem;background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);text-decoration:none;font-size:.8rem;font-weight:600;color:var(--gyc-dark);transition:border-color .15s,box-shadow .15s;text-align:center;"
         onmouseover="this.style.borderColor='var(--gyc-green-300)';this.style.boxShadow='0 2px 8px rgba(0,0,0,.06)';"
         onmouseout="this.style.borderColor='var(--gyc-green-100)';this.style.boxShadow='';">
        <i data-lucide="<?= $qa['icon'] ?>" style="width:22px;height:22px;color:<?= $qa['color'] ?>;"></i>
        <?= $qa['label'] ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Recent orders -->
    <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);overflow:hidden;margin-bottom:1.75rem;">
      <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--gyc-green-100);display:flex;align-items:center;justify-content:space-between;">
        <h2 style="font-family:'Playfair Display',serif;font-size:1.05rem;margin:0;">Recent Orders</h2>
        <a href="<?= SITE_URL ?>/my-orders.php" style="font-size:.82rem;color:var(--gyc-green-600);">View all →</a>
      </div>
      <?php if (empty($orders)): ?>
      <div style="padding:2rem;text-align:center;color:#888;font-size:.88rem;">
        No orders yet. <a href="<?= SITE_URL ?>/shop.php" style="color:var(--gyc-green-600);">Start shopping →</a>
      </div>
      <?php else: ?>
      <?php foreach ($recentOrds as $ord): ?>
      <?php
      $osMap = ['pending' => '#F59E0B', 'processing' => 'var(--gyc-green-600)', 'shipped' => '#3B82F6', 'delivered' => '#10B981', 'cancelled' => '#EF4444'];
      $psMap = ['pending' => '#F59E0B', 'paid' => 'var(--gyc-green-600)', 'failed' => '#EF4444', 'refunded' => '#9CA3AF'];
      ?>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;border-bottom:1px solid var(--gyc-green-100);gap:1rem;flex-wrap:wrap;">
        <div>
          <a href="<?= SITE_URL ?>/order-details.php?order=<?= urlencode($ord['order_number']) ?>"
             style="font-weight:600;font-size:.9rem;color:var(--gyc-dark);"><?= htmlspecialchars($ord['order_number']) ?></a>
          <div style="font-size:.75rem;color:#888;margin-top:2px;"><?= date('D jS M Y', strtotime($ord['created_at'])) ?></div>
        </div>
        <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;">
          <span style="font-size:.8rem;font-weight:700;color:<?= $osMap[$ord['status']] ?? '#888' ?>;"><?= ucfirst($ord['status']) ?></span>
          <span style="font-size:.8rem;font-weight:700;color:<?= $psMap[$ord['payment_status']] ?? '#888' ?>;"><?= ucfirst($ord['payment_status']) ?></span>
          <span style="font-weight:700;color:var(--gyc-green-700);"><?= formatPrice($ord['total']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Upcoming appointments -->
    <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);overflow:hidden;margin-bottom:1.75rem;">
      <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--gyc-green-100);display:flex;align-items:center;justify-content:space-between;">
        <h2 style="font-family:'Playfair Display',serif;font-size:1.05rem;margin:0;">My Appointments</h2>
        <a href="<?= SITE_URL ?>/book-appointment.php" class="btn btn-green btn-sm">
          <i data-lucide="plus" style="width:14px;height:14px;"></i>
          Book New
        </a>
      </div>
      <?php if (empty($appointments)): ?>
      <div style="padding:2rem;text-align:center;color:#888;font-size:.88rem;">
        No bookings yet.
        <a href="<?= SITE_URL ?>/book-appointment.php" style="color:var(--gyc-green-600);">Book your first appointment →</a>
      </div>
      <?php else: ?>
      <?php
      $aptStatusColors = ['pending' => '#F59E0B', 'confirmed' => 'var(--gyc-green-600)', 'cancelled' => '#EF4444', 'completed' => '#10B981'];
      foreach ($appointments as $apt):
      ?>
      <div style="display:flex;align-items:center;gap:1rem;padding:1rem 1.5rem;border-bottom:1px solid var(--gyc-green-100);flex-wrap:wrap;">
        <div style="width:44px;height:44px;border-radius:var(--gyc-radius);background:var(--gyc-green-100);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i data-lucide="scissors" style="width:20px;height:20px;color:var(--gyc-green-600);"></i>
        </div>
        <div style="flex:1;min-width:140px;">
          <div style="font-weight:600;font-size:.9rem;"><?= htmlspecialchars($apt['style_name'] ?? 'Appointment') ?></div>
          <div style="font-size:.78rem;color:#888;">
            <?= date('D jS M Y', strtotime($apt['requested_date'])) ?>
            <?= $apt['requested_time'] ? ' · ' . date('g:i A', strtotime($apt['requested_time'])) : '' ?>
          </div>
        </div>
        <div>
          <span style="font-size:.8rem;font-weight:700;color:<?= $aptStatusColors[$apt['status']] ?? '#888' ?>;">
            <?= ucfirst($apt['status']) ?>
          </span>
          <div style="font-size:.72rem;color:#bbb;margin-top:2px;"><?= htmlspecialchars($apt['appointment_number']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Sign out card -->
    <div style="text-align:right;">
      <a href="<?= SITE_URL ?>/logout.php"
         style="display:inline-flex;align-items:center;gap:.5rem;font-size:.85rem;color:#888;text-decoration:none;"
         onmouseover="this.style.color='#EF4444';" onmouseout="this.style.color='#888';">
        <i data-lucide="log-out" style="width:16px;height:16px;"></i>
        Sign out
      </a>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
