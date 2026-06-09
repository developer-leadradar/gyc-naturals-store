/* GYC Naturals — Quiz Step Transitions */

document.addEventListener('DOMContentLoaded', function () {
  const steps     = document.querySelectorAll('.quiz-step');
  const nextBtns  = document.querySelectorAll('[data-quiz-next]');
  const backBtns  = document.querySelectorAll('[data-quiz-back]');
  const progress  = document.getElementById('quiz-progress-bar');
  const stepCount = steps.length;
  let   current   = 0;

  function showStep(n) {
    steps.forEach(function (s, i) {
      s.classList.toggle('active', i === n);
    });
    if (progress) {
      progress.style.width = (((n + 1) / stepCount) * 100) + '%';
    }
    // Update step count label
    const label = document.getElementById('quiz-step-count');
    if (label) label.textContent = 'Step ' + (n + 1) + ' of ' + stepCount;
  }

  function getSelectedValue(stepIndex) {
    const step  = steps[stepIndex];
    const radio = step ? step.querySelector('input[type="radio"]:checked') : null;
    return radio ? radio.value : null;
  }

  nextBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      const selected = getSelectedValue(current);
      if (!selected) {
        // Shake the options to indicate selection required
        const opts = steps[current].querySelector('.quiz-options');
        if (opts) {
          opts.style.animation = 'none';
          setTimeout(function () { opts.style.animation = ''; }, 10);
          opts.style.outline = '2px solid var(--gyc-terra)';
          setTimeout(function () { opts.style.outline = ''; }, 800);
        }
        showToastQuiz('Please select an option to continue.', 'warning');
        return;
      }
      if (current < stepCount - 1) {
        current++;
        showStep(current);
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    });
  });

  backBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (current > 0) { current--; showStep(current); }
    });
  });

  // Init
  if (steps.length) showStep(0);

  // Keyboard shortcut: Enter to advance
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && steps[current]) {
      const nextBtn = steps[current].querySelector('[data-quiz-next]');
      if (nextBtn) nextBtn.click();
    }
  });
});

function showToastQuiz(msg, type) {
  if (typeof showToast === 'function') { showToast(msg, type); return; }
  const el = document.createElement('div');
  el.style.cssText = 'position:fixed;top:80px;right:20px;background:#1C1F1A;color:#fff;padding:0.75rem 1.25rem;border-radius:10px;z-index:9999;font-size:0.88rem;border-left:4px solid #C1440E;';
  el.textContent = msg;
  document.body.appendChild(el);
  setTimeout(function () { el.remove(); }, 2500);
}
