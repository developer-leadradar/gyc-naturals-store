<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();
$user = getCurrentUser();
$apts = getDB()->fetchAll(
    "SELECT a.*, gi.title as style_name, gi.image_url as style_image
     FROM appointments a
     LEFT JOIN gallery_images gi ON a.gallery_image_id = gi.id
     WHERE a.user_id = ?
     ORDER BY a.requested_date DESC",
    [$user['id']]
);

$pageTitle = 'My Appointments — GYC Naturals';
require_once __DIR__ . '/includes/header.php';
?>
<div style="min-height:72px;"></div>
<section style="padding:2.5rem 0 5rem;background:#F8FAF9;">
  <div class="container">
    <div class="dash-grid">
      <?php require __DIR__ . '/includes/dash-sidebar.php'; ?>
      <div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
          <h1 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin:0;">My Appointments</h1>
          <a href="<?= SITE_URL ?>/book-appointment.php" class="btn btn-gold">
            <i data-lucide="plus" style="width:16px;height:16px;"></i>
            Book New Appointment
          </a>
        </div>

        <?php if (empty($apts)): ?>
        <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:3rem;text-align:center;">
          <i data-lucide="calendar-x" style="width:48px;height:48px;opacity:.3;margin-bottom:1rem;"></i>
          <h3 style="font-family:'Playfair Display',serif;margin-bottom:.5rem;">No appointments yet</h3>
          <p style="color:#888;font-size:.9rem;margin-bottom:1.25rem;">Book your first appointment and let us transform your crown.</p>
          <a href="<?= SITE_URL ?>/book-appointment.php" class="btn btn-green">Book Appointment</a>
        </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:1rem;">
          <?php
          $statusColors = ['pending'=>'#F59E0B','confirmed'=>'var(--gyc-green-600)','cancelled'=>'#EF4444','completed'=>'#10B981'];
          $statusLabels = ['pending'=>'Pending Confirmation','confirmed'=>'Confirmed','cancelled'=>'Cancelled','completed'=>'Completed'];
          foreach ($apts as $apt):
          $isPast = strtotime($apt['requested_date']) < strtotime('today');
          ?>
          <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);overflow:hidden;<?= $isPast && $apt['status'] !== 'completed' ? 'opacity:.7;' : '' ?>">
            <div style="display:grid;grid-template-columns:auto 1fr auto;gap:1.25rem;padding:1.25rem 1.5rem;align-items:center;">
              <!-- Date block -->
              <div style="text-align:center;background:var(--gyc-green-100);border-radius:var(--gyc-radius);padding:.6rem .9rem;min-width:54px;">
                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:var(--gyc-green-500);letter-spacing:.08em;"><?= date('M', strtotime($apt['requested_date'])) ?></div>
                <div style="font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;color:var(--gyc-dark);line-height:1;"><?= date('d', strtotime($apt['requested_date'])) ?></div>
                <div style="font-size:.72rem;color:#888;"><?= date('Y', strtotime($apt['requested_date'])) ?></div>
              </div>
              <!-- Style info -->
              <div>
                <div style="font-weight:600;font-size:.95rem;color:var(--gyc-dark);margin-bottom:.2rem;">
                  <?= htmlspecialchars($apt['style_name'] ?? 'Style to be decided') ?>
                </div>
                <div style="font-size:.8rem;color:#888;margin-bottom:.35rem;">
                  <?= $apt['requested_time'] ? date('g:i A', strtotime($apt['requested_time'])) . ' · ' : '' ?>
                  Ref: <strong><?= htmlspecialchars($apt['appointment_number']) ?></strong>
                </div>
                <?php if ($apt['deposit_paid']): ?>
                <span style="font-size:.72rem;background:#ECFDF5;color:#065F46;font-weight:700;padding:.2rem .55rem;border-radius:20px;">
                  ✓ Deposit Paid <?= $apt['deposit_amount'] ? '— ' . formatPrice($apt['deposit_amount']) : '' ?>
                </span>
                <?php else: ?>
                <span style="font-size:.72rem;background:#FFFBEB;color:#92400E;font-weight:600;padding:.2rem .55rem;border-radius:20px;">
                  Deposit pending
                </span>
                <?php endif; ?>
              </div>
              <!-- Status -->
              <div style="text-align:right;">
                <span style="font-size:.82rem;font-weight:700;color:<?= $statusColors[$apt['status']] ?? '#888' ?>;">
                  <?= $statusLabels[$apt['status']] ?? ucfirst($apt['status']) ?>
                </span>
                <?php if ($apt['status'] === 'confirmed' && !$isPast):
                  $waPhone = getSetting('site_whatsapp') ?: SITE_WHATSAPP;
                  $waMsg   = 'Hi! I would like to confirm my appointment ' . $apt['appointment_number'] . ' on ' . date('D jS M Y', strtotime($apt['requested_date']));
                  $waUrl   = whatsappMessage($waPhone, $waMsg);
                ?>
                <div style="margin-top:.35rem;">
                  <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" rel="noopener"
                     class="btn btn-whatsapp btn-sm" style="font-size:.72rem;padding:.3rem .7rem;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WhatsApp
                  </a>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
