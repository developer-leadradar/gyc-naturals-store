<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();
$user   = getCurrentUser();
$db     = getDB();

// ── Handle review submission ──
$reviewMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'leave_review') {
    verifyCsrf();
    $productId = (int)($_POST['product_id'] ?? 0);
    $orderId   = (int)($_POST['order_id']   ?? 0);
    $rating    = (int)($_POST['rating']     ?? 0);
    $title     = trim(sanitize($_POST['title'] ?? ''));
    $body      = trim(sanitize($_POST['body']  ?? ''));

    // Verify the user actually owns a delivered order containing this product
    $owns = $db->fetchOne(
        "SELECT 1 FROM orders o
         JOIN order_items oi ON oi.order_id = o.id
         WHERE o.id = ? AND o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'",
        [$orderId, $user['id'], $productId]
    );
    $existing = $db->fetchOne(
        "SELECT id FROM reviews WHERE product_id = ? AND user_id = ?",
        [$productId, $user['id']]
    );

    if (!$owns)                              { $reviewMsg = "You can only review products from delivered orders."; }
    elseif ($existing)                       { $reviewMsg = "You've already reviewed this product."; }
    elseif ($rating < 1 || $rating > 5)      { $reviewMsg = "Pick a rating between 1 and 5 stars."; }
    elseif (strlen($body) < 5)               { $reviewMsg = "Tell us a little more — at least a sentence."; }
    else {
        $db->insert('reviews', [
            'product_id'  => $productId,
            'user_id'     => $user['id'],
            'rating'      => $rating,
            'title'       => $title ?: null,
            'body'        => $body,
            'is_approved' => 1,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
        // Update product's cached rating + count
        $agg = $db->fetchOne(
            "SELECT AVG(rating)::float AS avg, COUNT(*) AS cnt FROM reviews WHERE product_id = ? AND is_approved = 1",
            [$productId]
        );
        if ($agg) {
            $db->update('products', [
                'rating'       => round($agg['avg'], 2),
                'review_count' => (int)$agg['cnt'],
            ], 'id = ?', [$productId]);
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Thanks for your review!'];
        redirect(SITE_URL . '/my-orders.php#review-' . $productId);
        exit;
    }
}

$orders = getOrdersByUser($user['id']);

// Pre-load which (product_id, order_id) pairs the user has already reviewed
$reviewedIds = [];
if ($orders) {
    $rs = $db->fetchAll("SELECT product_id FROM reviews WHERE user_id = ?", [$user['id']]);
    foreach ($rs as $r) $reviewedIds[(int)$r['product_id']] = true;
}

$pageTitle = 'My Orders — GYC Naturals';
require_once __DIR__ . '/includes/header.php';
?>
<section style="padding:2.5rem 0 5rem;background:#F8FAF9;">
  <div class="container">
    <div style="max-width:960px;margin:0 auto;">
      <div>
        <h1 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:1.5rem;">My Orders</h1>
        <?php if ($reviewMsg): ?>
        <div class="alert alert-danger" style="margin-bottom:1.25rem;"><?= htmlspecialchars($reviewMsg) ?></div>
        <?php endif; ?>
        <?php if (empty($orders)): ?>
        <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:3rem;text-align:center;">
          <i data-lucide="package-open" style="width:48px;height:48px;opacity:.3;margin-bottom:1rem;"></i>
          <h3 style="font-family:'Playfair Display',serif;margin-bottom:.5rem;">No orders yet</h3>
          <p style="color:#888;font-size:.9rem;margin-bottom:1.25rem;">When you place an order it will appear here.</p>
          <a href="<?= SITE_URL ?>/shop.php" class="btn btn-green">Start Shopping</a>
        </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:1rem;">
          <?php foreach ($orders as $ord):
            $sc = ['pending'=>'#F59E0B','processing'=>'var(--gyc-green-600)','shipped'=>'#3B82F6','delivered'=>'#10B981','cancelled'=>'#EF4444','refunded'=>'#9CA3AF'];
            $pc = ['pending'=>'#F59E0B','paid'=>'var(--gyc-green-600)','failed'=>'#EF4444','refunded'=>'#9CA3AF'];
            $ois = getOrderItems($ord['id']);
          ?>
          <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);overflow:hidden;">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;border-bottom:1px solid var(--gyc-green-100);gap:1rem;flex-wrap:wrap;background:var(--gyc-green-100);">
              <div>
                <a href="<?= SITE_URL ?>/order-details.php?order=<?= urlencode($ord['order_number']) ?>" style="font-weight:700;font-size:.92rem;color:var(--gyc-dark);"><?= htmlspecialchars($ord['order_number']) ?></a>
                <span style="font-size:.75rem;color:#888;margin-left:.75rem;"><?= date('jS M Y', strtotime($ord['created_at'])) ?></span>
              </div>
              <div style="display:flex;gap:.75rem;align-items:center;">
                <span style="font-size:.78rem;font-weight:700;color:<?= $sc[$ord['status']] ?? '#888' ?>;"><?= ucfirst($ord['status']) ?></span>
                <span style="font-size:.78rem;font-weight:700;color:<?= $pc[$ord['payment_status']] ?? '#888' ?>;"><?= ucfirst($ord['payment_status']) ?></span>
                <strong style="color:var(--gyc-green-700);"><?= formatPrice($ord['total']) ?></strong>
              </div>
            </div>
            <div style="padding:1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
              <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                <?php foreach (array_slice($ois, 0, 4) as $oi): ?>
                <div title="<?= htmlspecialchars($oi['product_name']) ?>" style="position:relative;">
                  <img src="<?= htmlspecialchars($oi['image'] ?? '') ?>" alt="<?= htmlspecialchars($oi['product_name']) ?>"
                       style="width:44px;height:44px;object-fit:cover;border-radius:var(--gyc-radius);border:1px solid var(--gyc-green-100);">
                  <span style="position:absolute;top:-5px;right:-5px;width:16px;height:16px;border-radius:50%;background:var(--gyc-dark);color:#fff;font-size:.62rem;font-weight:700;display:flex;align-items:center;justify-content:center;"><?= $oi['quantity'] ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (count($ois) > 4): ?>
                <div style="width:44px;height:44px;border-radius:var(--gyc-radius);background:var(--gyc-green-100);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:600;color:#888;">+<?= count($ois)-4 ?></div>
                <?php endif; ?>
              </div>
              <a href="<?= SITE_URL ?>/order-details.php?order=<?= urlencode($ord['order_number']) ?>" class="btn btn-outline-green btn-sm">View Details</a>
            </div>

            <?php if ($ord['status'] === 'delivered'): ?>
            <div style="border-top:1px solid var(--gyc-green-100);background:#FBFDFB;padding:1rem 1.5rem;">
              <div style="font-size:.78rem;font-weight:700;color:var(--gyc-green-700);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.65rem;display:flex;align-items:center;gap:.4rem;">
                <i data-lucide="star" style="width:14px;height:14px;"></i> Rate &amp; review your products
              </div>
              <div style="display:flex;flex-direction:column;gap:.65rem;">
                <?php foreach ($ois as $oi):
                  $alreadyReviewed = isset($reviewedIds[(int)$oi['product_id']]);
                  $formId = 'review-form-' . $ord['id'] . '-' . $oi['product_id'];
                ?>
                <div id="review-<?= (int)$oi['product_id'] ?>" style="background:#fff;border:1px solid var(--gyc-green-100);border-radius:10px;padding:.7rem .9rem;">
                  <div style="display:flex;align-items:center;gap:.75rem;">
                    <img src="<?= htmlspecialchars($oi['image'] ?? '') ?>" alt="" style="width:38px;height:38px;object-fit:cover;border-radius:6px;flex-shrink:0;">
                    <div style="flex:1;min-width:0;">
                      <div style="font-size:.85rem;font-weight:600;color:var(--gyc-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($oi['product_name']) ?></div>
                      <?php if ($alreadyReviewed): ?>
                      <div style="font-size:.74rem;color:var(--gyc-green-700);font-weight:600;margin-top:2px;">✓ You've reviewed this product</div>
                      <?php else: ?>
                      <div style="font-size:.74rem;color:#9CA3AF;margin-top:2px;">Share your experience to help other shoppers.</div>
                      <?php endif; ?>
                    </div>
                    <?php if (!$alreadyReviewed): ?>
                    <button type="button" class="btn btn-outline-green btn-sm"
                            onclick="document.getElementById('<?= $formId ?>').style.display = (document.getElementById('<?= $formId ?>').style.display === 'block' ? 'none' : 'block');">
                      Write Review
                    </button>
                    <?php endif; ?>
                  </div>
                  <?php if (!$alreadyReviewed): ?>
                  <form id="<?= $formId ?>" method="POST" action="<?= SITE_URL ?>/my-orders.php" style="display:none;margin-top:.85rem;padding-top:.85rem;border-top:1px dashed var(--gyc-green-100);">
                    <?= csrfInput() ?>
                    <input type="hidden" name="action"     value="leave_review">
                    <input type="hidden" name="order_id"   value="<?= (int)$ord['id'] ?>">
                    <input type="hidden" name="product_id" value="<?= (int)$oi['product_id'] ?>">
                    <input type="hidden" name="rating"     id="rating-input-<?= $formId ?>" value="0" required>
                    <div style="display:flex;align-items:center;gap:.35rem;margin-bottom:.6rem;" class="star-picker" data-target="rating-input-<?= $formId ?>">
                      <?php for ($i = 1; $i <= 5; $i++): ?>
                      <button type="button" class="star-btn" data-rating="<?= $i ?>"
                              style="background:none;border:none;cursor:pointer;padding:.15rem;color:#E5E7EB;"
                              onclick="this.parentElement.querySelectorAll('.star-btn').forEach((b,i) => b.style.color = i < <?= $i ?> ? '#F59E0B' : '#E5E7EB'); document.getElementById(this.parentElement.dataset.target).value = <?= $i ?>;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                      </button>
                      <?php endfor; ?>
                      <span style="font-size:.78rem;color:#9CA3AF;margin-left:.5rem;">Tap a star to rate</span>
                    </div>
                    <input type="text" name="title" class="form-control" placeholder="Headline (optional) — e.g. 'Loved it!'" style="margin-bottom:.5rem;font-size:.85rem;" maxlength="80">
                    <textarea name="body" class="form-control" rows="3" placeholder="What did you like? How did it work for your hair?" required minlength="5" style="font-size:.85rem;"></textarea>
                    <div style="display:flex;gap:.5rem;margin-top:.6rem;justify-content:flex-end;">
                      <button type="button" class="btn btn-sm" style="background:#F3F4F6;color:#374151;"
                              onclick="document.getElementById('<?= $formId ?>').style.display='none';">Cancel</button>
                      <button type="submit" class="btn btn-green btn-sm">Submit Review</button>
                    </div>
                  </form>
                  <?php endif; ?>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
