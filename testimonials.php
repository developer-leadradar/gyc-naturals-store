<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$testimonials = getAllTestimonials();
$success      = '';
$error        = '';

// Handle submission from logged-in users
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    verifyCsrf();
    $user    = getCurrentUser();
    $rating  = (int)($_POST['rating'] ?? 0);
    $content = trim(sanitize($_POST['content'] ?? ''));
    $service = trim(sanitize($_POST['service'] ?? ''));

    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating between 1 and 5 stars.';
    } elseif (strlen($content) < 20) {
        $error = 'Your review must be at least 20 characters.';
    } else {
        // Check for duplicate in same 30-day window
        $existing = getDB()->fetchOne(
            "SELECT id FROM testimonials WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)",
            [$user['id']]
        );
        if ($existing) {
            $error = 'You have already submitted a testimonial recently. Thank you!';
        } else {
            getDB()->insert('testimonials', [
                'user_id'      => $user['id'],
                'author_name'  => $user['first_name'] . ' ' . $user['last_name'],
                'service'      => $service ?: null,
                'rating'       => $rating,
                'content'      => $content,
                'is_approved'  => 0,
                'is_featured'  => 0,
            ]);
            $success = 'Thank you! Your review has been submitted and will appear after approval.';
        }
    }
}

$pageTitle       = 'Client Testimonials — GYC Naturals';
$pageDescription = 'Read what our clients say about GYC Naturals hair braiding salon and natural hair products in Calabar, Cross River State.';
require_once __DIR__ . '/includes/header.php';
?>


<!-- ── HERO ── -->
<section style="background:linear-gradient(135deg,var(--gyc-green-900) 0%,var(--gyc-green-700) 100%);color:#fff;padding:5rem 0 4rem;text-align:center;">
  <div class="container" style="max-width:680px;">
    <p style="font-size:.8rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--gyc-gold);margin-bottom:.75rem;">Real Reviews</p>
    <h1 style="font-family:'Playfair Display',serif;font-size:clamp(2rem,5vw,3rem);margin-bottom:1.25rem;line-height:1.2;">What Our Clients Say</h1>
    <p style="font-size:1.05rem;opacity:.85;line-height:1.7;">Every braid, every drop, every stitch — made with love. Here's how our community feels about GYC Naturals.</p>
    <div style="display:flex;align-items:center;justify-content:center;gap:.75rem;margin-top:2rem;">
      <div style="display:flex;gap:.2rem;">
        <?php for ($s=1; $s<=5; $s++): ?>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="var(--gyc-gold)" xmlns="http://www.w3.org/2000/svg"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        <?php endfor; ?>
      </div>
      <span style="font-size:.95rem;font-weight:600;">Trusted by 100+ Calabar clients</span>
    </div>
  </div>
</section>

<!-- ── STATS STRIP ── -->
<section style="background:var(--gyc-gold);padding:1.5rem 0;">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;text-align:center;">
      <div>
        <div style="font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:700;color:#fff;">500+</div>
        <div style="font-size:.78rem;font-weight:600;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.1em;">Happy Clients</div>
      </div>
      <div>
        <div style="font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:700;color:#fff;">4.9★</div>
        <div style="font-size:.78rem;font-weight:600;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.1em;">Average Rating</div>
      </div>
      <div>
        <div style="font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:700;color:#fff;">2024</div>
        <div style="font-size:.78rem;font-weight:600;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.1em;">Est. in Calabar</div>
      </div>
      <div>
        <div style="font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:700;color:#fff;">100%</div>
        <div style="font-size:.78rem;font-weight:600;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.1em;">Natural Ingredients</div>
      </div>
    </div>
  </div>
</section>

<!-- ── TESTIMONIALS GRID ── -->
<section style="padding:5rem 0;background:#F8FAF9;">
  <div class="container">

    <?php if (empty($testimonials)): ?>
    <div style="text-align:center;padding:5rem 0;color:#888;">
      <i data-lucide="message-circle" style="width:48px;height:48px;opacity:.3;margin-bottom:1rem;"></i>
      <p>No reviews yet — be the first to share your experience!</p>
    </div>
    <?php else: ?>

    <!-- Masonry-style grid -->
    <div style="column-count:3;column-gap:1.5rem;" id="testimonials-masonry">
      <?php foreach ($testimonials as $t):
        $stars = (int)($t['rating'] ?? 5);
        // Pick a soft accent based on id mod
        $accents = ['var(--gyc-green-100)','#FEF9EC','#FFF0F0','#EFF6FF','#F5F3FF'];
        $bg = $accents[$t['id'] % count($accents)];
      ?>
      <div style="break-inside:avoid;margin-bottom:1.5rem;background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.6rem;position:relative;overflow:hidden;">
        <!-- Decorative quote mark -->
        <div style="position:absolute;top:-8px;right:1rem;font-size:5rem;line-height:1;color:var(--gyc-green-100);font-family:'Playfair Display',serif;pointer-events:none;">"</div>

        <!-- Stars -->
        <div style="display:flex;gap:.2rem;margin-bottom:.85rem;">
          <?php for ($s=1; $s<=5; $s++): ?>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="<?= $s <= $stars ? 'var(--gyc-gold)' : '#E5E7EB' ?>" xmlns="http://www.w3.org/2000/svg"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <?php endfor; ?>
        </div>

        <!-- Content -->
        <blockquote style="font-size:.92rem;line-height:1.75;color:#374151;margin:0 0 1.25rem;font-style:italic;">
          "<?= htmlspecialchars($t['content']) ?>"
        </blockquote>

        <!-- Author row -->
        <div style="display:flex;align-items:center;gap:.75rem;">
          <?php if (!empty($t['photo_url'])): ?>
          <img src="<?= htmlspecialchars($t['photo_url']) ?>" alt="<?= htmlspecialchars($t['author_name']) ?>"
               style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid var(--gyc-gold);">
          <?php else:
            // Initials avatar
            $initials = strtoupper(substr($t['author_name'], 0, 1));
            $initials .= strtoupper(strpos($t['author_name'], ' ') !== false ? substr(strrchr($t['author_name'], ' '), 1, 1) : '');
          ?>
          <div style="width:40px;height:40px;border-radius:50%;background:var(--gyc-green-700);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0;">
            <?= $initials ?>
          </div>
          <?php endif; ?>

          <div>
            <div style="font-weight:700;font-size:.88rem;color:var(--gyc-dark);"><?= htmlspecialchars($t['author_name']) ?></div>
            <?php if (!empty($t['service'])): ?>
            <div style="font-size:.75rem;color:var(--gyc-green-600);"><?= htmlspecialchars($t['service']) ?></div>
            <?php elseif (!empty($t['location'])): ?>
            <div style="font-size:.75rem;color:#9CA3AF;"><?= htmlspecialchars($t['location']) ?></div>
            <?php endif; ?>
          </div>

          <?php if (!empty($t['is_verified'])): ?>
          <div style="margin-left:auto;" title="Verified client">
            <i data-lucide="badge-check" style="width:18px;height:18px;color:var(--gyc-green-600);"></i>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php endif; ?>

    <!-- ── SUBMIT A REVIEW ── -->
    <div id="leave-review" style="margin-top:4rem;background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-xl);padding:2.5rem;max-width:640px;margin-left:auto;margin-right:auto;">
      <h2 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:.5rem;text-align:center;">Share Your Experience</h2>
      <p style="font-size:.875rem;color:#6B7280;text-align:center;margin-bottom:2rem;">Your review helps other naturals in Calabar find us.</p>

      <?php if ($success): ?>
      <div class="alert alert-success" style="margin-bottom:1.5rem;">
        <i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
        <?= htmlspecialchars($success) ?>
      </div>
      <?php elseif ($error): ?>
      <div class="alert alert-danger" style="margin-bottom:1.5rem;">
        <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <?php if (!isLoggedIn()): ?>
      <div style="text-align:center;padding:1.5rem;background:var(--gyc-green-100);border-radius:var(--gyc-radius-lg);">
        <p style="margin-bottom:1rem;font-size:.9rem;color:var(--gyc-dark);">Please sign in to leave a review.</p>
        <a href="<?= SITE_URL ?>/login.php?redirect=<?= urlencode(SITE_URL . '/testimonials.php#leave-review') ?>" class="btn btn-green">Sign In to Review</a>
        <span style="margin:0 .75rem;color:#9CA3AF;">or</span>
        <a href="<?= SITE_URL ?>/register.php" class="btn btn-outline-green">Create Account</a>
      </div>

      <?php else: ?>
      <form method="POST" id="review-form">
        <?= csrfInput() ?>

        <!-- Star picker -->
        <div class="form-group" style="text-align:center;">
          <label class="form-label" style="display:block;margin-bottom:.6rem;">Your Rating</label>
          <div id="star-picker" style="display:inline-flex;gap:.4rem;cursor:pointer;">
            <?php for ($s=1; $s<=5; $s++): ?>
            <svg data-val="<?= $s ?>" width="32" height="32" viewBox="0 0 24 24" fill="#E5E7EB" xmlns="http://www.w3.org/2000/svg"
                 style="transition:transform .15s;" class="star-btn">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            <?php endfor; ?>
          </div>
          <input type="hidden" name="rating" id="rating-value" value="0">
          <p class="form-hint" id="rating-label" style="margin-top:.4rem;">Click to rate</p>
        </div>

        <div class="form-group">
          <label class="form-label">Service / Product (optional)</label>
          <input type="text" name="service" class="form-control" placeholder="e.g. Knotless Box Braids, Scalp Serum…" maxlength="100">
        </div>

        <div class="form-group">
          <label class="form-label">Your Review <span style="color:var(--gyc-terra);">*</span></label>
          <textarea name="content" class="form-control" rows="4" required minlength="20"
                    placeholder="Tell us about your experience — the more detail the better!"></textarea>
          <p class="form-hint">Minimum 20 characters</p>
        </div>

        <button type="submit" class="btn btn-green w-full" id="review-submit-btn">Submit Review</button>
      </form>
      <?php endif; ?>
    </div>

  </div>
</section>

<!-- ── CTA STRIP ── -->
<section style="background:var(--gyc-green-900);color:#fff;padding:4.5rem 0;text-align:center;">
  <div class="container" style="max-width:600px;">
    <h2 style="font-family:'Playfair Display',serif;font-size:2rem;margin-bottom:1rem;">Ready to become our next happy client?</h2>
    <p style="opacity:.8;margin-bottom:2rem;line-height:1.7;">Book an appointment or shop our curated natural hair products — delivered nationwide.</p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
      <a href="<?= SITE_URL ?>/book-appointment.php" class="btn btn-gold" style="padding:.85rem 2rem;">Book Appointment</a>
      <a href="<?= SITE_URL ?>/shop.php" class="btn" style="padding:.85rem 2rem;background:rgba(255,255,255,.12);color:#fff;border:1.5px solid rgba(255,255,255,.3);">Shop Products</a>
    </div>
  </div>
</section>

<script>
// ── Star rating picker ──
(function() {
  const stars = document.querySelectorAll('.star-btn');
  const input = document.getElementById('rating-value');
  const label = document.getElementById('rating-label');
  const labels = ['','Poor','Fair','Good','Great','Excellent!'];

  function paintStars(val) {
    stars.forEach(function(s) {
      s.setAttribute('fill', parseInt(s.dataset.val) <= val ? 'var(--gyc-gold)' : '#E5E7EB');
    });
  }

  stars.forEach(function(s) {
    s.addEventListener('mouseenter', function() { paintStars(parseInt(s.dataset.val)); });
    s.addEventListener('click', function() {
      const v = parseInt(s.dataset.val);
      input.value = v;
      label.textContent = labels[v];
      paintStars(v);
    });
  });

  const picker = document.getElementById('star-picker');
  if (picker) {
    picker.addEventListener('mouseleave', function() {
      paintStars(parseInt(input.value));
    });
  }
})();

// ── Responsive masonry fallback for narrow screens ──
(function() {
  function fixMasonry() {
    const grid = document.getElementById('testimonials-masonry');
    if (!grid) return;
    if (window.innerWidth < 768) {
      grid.style.columnCount = '1';
    } else if (window.innerWidth < 1024) {
      grid.style.columnCount = '2';
    } else {
      grid.style.columnCount = '3';
    }
  }
  fixMasonry();
  window.addEventListener('resize', fixMasonry);
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
