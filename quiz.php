<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Hair Consultation Quiz — Find Your Perfect Style | GYC Naturals';
$pageDescription = 'Answer 4 quick questions and get a personalised hair care and style recommendation from GYC Naturals — completely free.';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Quiz hero -->
<div style="background:linear-gradient(135deg,var(--gyc-green-900),var(--gyc-green-700));padding:4rem 0 2rem;text-align:center;color:#fff;">
  <span class="section-eyebrow" style="color:var(--gyc-gold-300);">Free Consultation</span>
  <h1 style="font-family:'Playfair Display',serif;font-size:clamp(2rem,4vw,3rem);color:#fff;margin:0.5rem 0 0.75rem;">Find Your Crown Style</h1>
  <p style="color:rgba(255,255,255,0.75);max-width:480px;margin:0 auto;font-size:0.95rem;">4 quick questions. Personalised product recommendations and style advice — delivered instantly.</p>
</div>

<!-- Quiz form -->
<section style="padding:3rem 0 5rem;background:#F8FAF9;">
  <div class="quiz-container">

    <!-- Progress bar -->
    <div class="quiz-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="25" id="quiz-progress-wrap">
      <div class="quiz-progress-bar" id="quiz-progress-bar" style="width:25%;"></div>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:0.75rem;color:#888;margin-bottom:2rem;">
      <span>Question <span id="quiz-step-label">1</span> of 4</span>
      <span id="quiz-percent-label">25% complete</span>
    </div>

    <form method="POST" action="<?= SITE_URL ?>/quiz-result.php" id="quiz-form" novalidate>
      <?= csrfInput() ?>

      <!-- ── STEP 1: Hair Type ── -->
      <div class="quiz-step active" id="quiz-step-0" data-step="0">
        <p class="quiz-question">What is your natural hair type?</p>
        <div class="quiz-options">
          <div class="quiz-option">
            <input type="radio" name="hair_type" id="ht1" value="4C">
            <label for="ht1">
              <img src="https://images.pexels.com/photos/11215202/pexels-photo-11215202.jpeg?auto=compress&cs=tinysrgb&w=120&h=120&fit=crop" alt="4C Coily hair" style="width:64px;height:64px;border-radius:50%;object-fit:cover;display:block;margin:0 auto 0.5rem;">
              <span>4C Coily</span>
              <small style="font-size:0.7rem;color:#888;display:block;">Tight zigzag coils, highly shrinks</small>
            </label>
          </div>
          <div class="quiz-option">
            <input type="radio" name="hair_type" id="ht2" value="4B">
            <label for="ht2">
              <img src="https://images.pexels.com/photos/2520446/pexels-photo-2520446.jpeg?auto=compress&cs=tinysrgb&w=120&h=120&fit=crop" alt="4B Coily hair" style="width:64px;height:64px;border-radius:50%;object-fit:cover;display:block;margin:0 auto 0.5rem;">
              <span>4B Coily</span>
              <small style="font-size:0.7rem;color:#888;display:block;">Sharp Z-pattern, fluffy texture</small>
            </label>
          </div>
          <div class="quiz-option">
            <input type="radio" name="hair_type" id="ht3" value="4A">
            <label for="ht3">
              <img src="https://images.pexels.com/photos/5085560/pexels-photo-5085560.jpeg?auto=compress&cs=tinysrgb&w=120&h=120&fit=crop" alt="4A Curly hair" style="width:64px;height:64px;border-radius:50%;object-fit:cover;display:block;margin:0 auto 0.5rem;">
              <span>4A Curly-Coily</span>
              <small style="font-size:0.7rem;color:#888;display:block;">S-shaped coils, defined pattern</small>
            </label>
          </div>
          <div class="quiz-option">
            <input type="radio" name="hair_type" id="ht4" value="relaxed">
            <label for="ht4">
              <img src="https://images.pexels.com/photos/18790491/pexels-photo-18790491.jpeg?auto=compress&cs=tinysrgb&w=120&h=120&fit=crop" alt="Relaxed hair" style="width:64px;height:64px;border-radius:50%;object-fit:cover;display:block;margin:0 auto 0.5rem;">
              <span>Relaxed / Transitioning</span>
              <small style="font-size:0.7rem;color:#888;display:block;">Chemically processed or growing out</small>
            </label>
          </div>
        </div>
        <div class="quiz-nav quiz-nav--single" style="display:flex;justify-content:center;">
          <button type="button" class="btn btn-green btn-lg" id="quiz-next-0" data-quiz-next="0">
            Next <i data-lucide="arrow-right" style="width:18px;height:18px;"></i>
          </button>
        </div>
      </div>

      <!-- ── STEP 2: Main Concern ── -->
      <div class="quiz-step" id="quiz-step-1" data-step="1">
        <p class="quiz-question">What is your biggest hair concern?</p>
        <div class="quiz-options">
          <div class="quiz-option">
            <input type="radio" name="concern" id="c1" value="growth">
            <label for="c1">
              <span style="font-size:2rem;">🌱</span>
              <span>Hair Growth</span>
              <small style="font-size:0.7rem;color:#888;display:block;">Grow longer, fuller hair</small>
            </label>
          </div>
          <div class="quiz-option">
            <input type="radio" name="concern" id="c2" value="moisture">
            <label for="c2">
              <span style="font-size:2rem;">💧</span>
              <span>Moisture &amp; Hydration</span>
              <small style="font-size:0.7rem;color:#888;display:block;">Dry, thirsty, brittle hair</small>
            </label>
          </div>
          <div class="quiz-option">
            <input type="radio" name="concern" id="c3" value="breakage">
            <label for="c3">
              <span style="font-size:2rem;">💪</span>
              <span>Breakage &amp; Strength</span>
              <small style="font-size:0.7rem;color:#888;display:block;">Snapping, weak, damaged hair</small>
            </label>
          </div>
          <div class="quiz-option">
            <input type="radio" name="concern" id="c4" value="definition">
            <label for="c4">
              <span style="font-size:2rem;">🎯</span>
              <span>Curl Definition</span>
              <small style="font-size:0.7rem;color:#888;display:block;">Better-defined curls and coils</small>
            </label>
          </div>
        </div>
        <div class="quiz-nav" style="display:flex;gap:0.75rem;">
          <button type="button" class="btn btn-outline-green btn-lg" data-quiz-back="1">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i> Back
          </button>
          <button type="button" class="btn btn-green btn-lg" data-quiz-next="1">
            Next <i data-lucide="arrow-right" style="width:18px;height:18px;"></i>
          </button>
        </div>
      </div>

      <!-- ── STEP 3: Lifestyle ── -->
      <div class="quiz-step" id="quiz-step-2" data-step="2">
        <p class="quiz-question">How would you describe your hair care routine?</p>
        <div class="quiz-options">
          <div class="quiz-option">
            <input type="radio" name="lifestyle" id="l1" value="minimal">
            <label for="l1">
              <span style="font-size:2rem;">⚡</span>
              <span>Minimal Effort</span>
              <small style="font-size:0.7rem;color:#888;display:block;">Quick, low-maintenance styles</small>
            </label>
          </div>
          <div class="quiz-option">
            <input type="radio" name="lifestyle" id="l2" value="protective">
            <label for="l2">
              <span style="font-size:2rem;">🛡️</span>
              <span>Protective First</span>
              <small style="font-size:0.7rem;color:#888;display:block;">Focus on protecting hair health</small>
            </label>
          </div>
          <div class="quiz-option">
            <input type="radio" name="lifestyle" id="l3" value="styling">
            <label for="l3">
              <span style="font-size:2rem;">💄</span>
              <span>Love to Style</span>
              <small style="font-size:0.7rem;color:#888;display:block;">Enjoy experimenting with looks</small>
            </label>
          </div>
          <div class="quiz-option">
            <input type="radio" name="lifestyle" id="l4" value="natural">
            <label for="l4">
              <span style="font-size:2rem;">🌿</span>
              <span>All Natural</span>
              <small style="font-size:0.7rem;color:#888;display:block;">Natural ingredients, minimal chemicals</small>
            </label>
          </div>
        </div>
        <div class="quiz-nav" style="display:flex;gap:0.75rem;">
          <button type="button" class="btn btn-outline-green btn-lg" data-quiz-back="2">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i> Back
          </button>
          <button type="button" class="btn btn-green btn-lg" data-quiz-next="2">
            Next <i data-lucide="arrow-right" style="width:18px;height:18px;"></i>
          </button>
        </div>
      </div>

      <!-- ── STEP 4: Goal ── -->
      <div class="quiz-step" id="quiz-step-3" data-step="3">
        <p class="quiz-question">What is your main hair goal right now?</p>
        <div class="quiz-options">
          <div class="quiz-option">
            <input type="radio" name="goal" id="g1" value="length">
            <label for="g1">
              <span style="font-size:2rem;">📏</span>
              <span>Grow Length</span>
              <small style="font-size:0.7rem;color:#888;display:block;">Hip length? Waist length? Let's go.</small>
            </label>
          </div>
          <div class="quiz-option">
            <input type="radio" name="goal" id="g2" value="thickness">
            <label for="g2">
              <span style="font-size:2rem;">🪢</span>
              <span>Increase Thickness</span>
              <small style="font-size:0.7rem;color:#888;display:block;">Fuller, denser, more voluminous hair</small>
            </label>
          </div>
          <div class="quiz-option">
            <input type="radio" name="goal" id="g3" value="edges">
            <label for="g3">
              <span style="font-size:2rem;">✂️</span>
              <span>Restore Edges</span>
              <small style="font-size:0.7rem;color:#888;display:block;">Regrow thinning hairline</small>
            </label>
          </div>
          <div class="quiz-option">
            <input type="radio" name="goal" id="g4" value="overall">
            <label for="g4">
              <span style="font-size:2rem;">👑</span>
              <span>Overall Health</span>
              <small style="font-size:0.7rem;color:#888;display:block;">General hair wellness upgrade</small>
            </label>
          </div>
        </div>

        <!-- Optional name/email -->
        <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius);padding:1.25rem;margin-top:1.5rem;">
          <p style="font-size:0.85rem;color:#555;margin-bottom:1rem;">Optional: Save your results and get product recommendations by email</p>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
            <div class="form-group" style="margin:0;">
              <input type="text" name="name" class="form-control" placeholder="Your name (optional)">
            </div>
            <div class="form-group" style="margin:0;">
              <input type="email" name="email" class="form-control" placeholder="Email (optional)">
            </div>
          </div>
        </div>

        <div class="quiz-nav" style="display:flex;gap:0.75rem;margin-top:1.5rem;">
          <button type="button" class="btn btn-outline-green btn-lg" data-quiz-back="3">
            <i data-lucide="arrow-left" style="width:18px;height:18px;"></i> Back
          </button>
          <button type="submit" class="btn btn-gold btn-lg" style="flex:1;justify-content:center;">
            <i data-lucide="sparkles" style="width:18px;height:18px;"></i>
            Get My Results
          </button>
        </div>
      </div>

    </form>
  </div>
</section>

<script src="<?= SITE_URL ?>/assets/js/quiz.js"></script>
<script>
// Override quiz.js step management for this page
document.addEventListener('DOMContentLoaded', function () {
  let current = 0;
  const steps = document.querySelectorAll('.quiz-step');
  const bar   = document.getElementById('quiz-progress-bar');
  const lbl   = document.getElementById('quiz-step-label');
  const pct   = document.getElementById('quiz-percent-label');

  function goTo(n) {
    steps.forEach(function(s,i) { s.classList.toggle('active', i===n); });
    current = n;
    const p = Math.round(((n+1)/4)*100);
    if (bar) bar.style.width = p + '%';
    if (lbl) lbl.textContent = n+1;
    if (pct) pct.textContent = p + '% complete';
    window.scrollTo({top:0,behavior:'smooth'});
    if (typeof lucide !== 'undefined') lucide.createIcons();
  }

  document.querySelectorAll('[data-quiz-next]').forEach(function(btn) {
    btn.addEventListener('click', function() {
      const step = parseInt(btn.dataset.quizNext);
      const radios = document.querySelectorAll('#quiz-step-'+step+' input[type="radio"]');
      const checked = Array.from(radios).some(function(r) { return r.checked; });
      if (!checked) {
        const opts = document.querySelectorAll('#quiz-step-'+step+' .quiz-option label');
        opts.forEach(function(l) { l.style.borderColor='var(--gyc-terra)'; });
        setTimeout(function() { opts.forEach(function(l) { l.style.borderColor=''; }); }, 800);
        return;
      }
      goTo(step+1);
    });
  });

  document.querySelectorAll('[data-quiz-back]').forEach(function(btn) {
    btn.addEventListener('click', function() {
      goTo(parseInt(btn.dataset.quizBack)-1);
    });
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
