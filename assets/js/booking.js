/* GYC Naturals — Booking 3-Step Form */

document.addEventListener('DOMContentLoaded', function () {
  const panels   = document.querySelectorAll('.booking-panel');
  const steps    = document.querySelectorAll('.booking-step-item');
  const dateInput = document.getElementById('booking-date');
  const slotsBox  = document.getElementById('time-slots-box');
  let   currentStep = 0;
  let   selectedSlotId = null;

  function goToStep(n) {
    panels.forEach(function (p, i) { p.classList.toggle('active', i === n); });
    steps.forEach(function (s, i) {
      s.classList.remove('active', 'done');
      if (i < n)  s.classList.add('done');
      if (i === n) s.classList.add('active');
    });
    // Step lines
    document.querySelectorAll('.booking-step-line').forEach(function (l, i) {
      l.classList.toggle('done', i < n);
    });
    currentStep = n;
    window.scrollTo({ top: 0, behavior: 'smooth' });
    updateStepSummary();
  }

  // Next / Back buttons
  document.querySelectorAll('[data-booking-next]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (!validateStep(currentStep)) return;
      goToStep(currentStep + 1);
    });
  });

  document.querySelectorAll('[data-booking-back]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (currentStep > 0) goToStep(currentStep - 1);
    });
  });

  // Date change → load slots via AJAX
  if (dateInput) {
    dateInput.addEventListener('change', function () {
      const date = dateInput.value;
      if (!date) return;
      slotsBox.innerHTML = '<div style="padding:1rem;color:#888;font-size:0.88rem;">Loading available times…</div>';
      selectedSlotId = null;

      fetch(window.GYC_URL + '/api/check-availability.php?date=' + encodeURIComponent(date))
        .then(r => r.json())
        .then(function (data) {
          if (!data.slots || !data.slots.length) {
            slotsBox.innerHTML = buildNoSlots(date);
          } else {
            slotsBox.innerHTML = buildSlots(data.slots);
            slotsBox.querySelectorAll('.time-slot').forEach(function (chip) {
              chip.addEventListener('click', function () {
                slotsBox.querySelectorAll('.time-slot').forEach(c => c.classList.remove('selected'));
                chip.classList.add('selected');
                selectedSlotId = chip.dataset.slotId;
                document.getElementById('booking-slot-id').value = selectedSlotId;
                document.getElementById('booking-time').value    = chip.dataset.time;
              });
            });
          }
        })
        .catch(function () {
          slotsBox.innerHTML = '<div style="color:var(--gyc-terra);font-size:0.88rem;padding:0.75rem 0;">Could not load availability. Please try again.</div>';
        });
    });
  }

  function buildSlots(slots) {
    return '<p style="font-size:0.84rem;color:#666;margin-bottom:0.75rem;">Available times for this date:</p>'
      + '<div class="time-slots">'
      + slots.map(function (s) {
          return '<button type="button" class="time-slot" data-slot-id="' + s.id + '" data-time="' + s.start_time + '">'
                 + formatTime(s.start_time) + '</button>';
        }).join('')
      + '</div>';
  }

  function buildNoSlots(date) {
    return '<div style="background:#FEF3C7;border-radius:8px;padding:1rem;font-size:0.88rem;">'
      + '<strong>No slots available for this date.</strong><br>'
      + 'Join the waiting list and we\'ll WhatsApp you when a slot opens.'
      + '</div>'
      + '<div id="waitlist-form" style="margin-top:1rem;">'
      + '<h4 style="font-size:0.92rem;margin-bottom:0.75rem;">Join Waiting List</h4>'
      + '<div class="form-group"><label class="form-label">Your Name <span class="required">*</span></label>'
      + '<input type="text" class="form-control" id="wl-name" placeholder="Full name"></div>'
      + '<div class="form-group"><label class="form-label">WhatsApp Number <span class="required">*</span></label>'
      + '<input type="tel" class="form-control" id="wl-phone" placeholder="+234 xxx xxx xxxx"></div>'
      + '<input type="hidden" id="wl-date" value="' + date + '">'
      + '<button type="button" class="btn btn-green" onclick="joinWaitlist()">Join Waiting List</button>'
      + '</div>';
  }

  function formatTime(t) {
    const parts = t.split(':');
    let h = parseInt(parts[0]);
    const m = parts[1];
    const ampm = h >= 12 ? 'PM' : 'AM';
    if (h > 12) h -= 12;
    if (h === 0) h = 12;
    return h + ':' + m + ' ' + ampm;
  }

  function validateStep(step) {
    if (step === 0) {
      const styleChoice = document.querySelector('input[name="gallery_image_id"]:checked');
      if (!styleChoice) { alert('Please choose a style (or select "I\'ll decide in person").'); return false; }
      return true;
    }
    if (step === 1) {
      if (!dateInput || !dateInput.value) { alert('Please select a date.'); return false; }
      if (!selectedSlotId) {
        // Allow continuing even without slot if it's a waitlist scenario
        const wlForm = document.getElementById('waitlist-form');
        if (!wlForm) { alert('Please select a time slot.'); return false; }
      }
      return true;
    }
    return true;
  }

  function updateStepSummary() {
    const summaryStyle = document.getElementById('summary-style');
    const summaryDate  = document.getElementById('summary-date');
    const summaryTime  = document.getElementById('summary-time');
    if (summaryStyle) {
      const checked = document.querySelector('input[name="gallery_image_id"]:checked');
      if (checked) {
        const label = document.querySelector('label[for="' + checked.id + '"] span');
        if (label) summaryStyle.textContent = label.textContent;
      }
    }
    if (summaryDate && dateInput) summaryDate.textContent = dateInput.value || '—';
    if (summaryTime) {
      const timeField = document.getElementById('booking-time');
      summaryTime.textContent = timeField ? (timeField.value ? formatTime(timeField.value) : '—') : '—';
    }
  }

  // Init
  goToStep(0);
});

// Waitlist join
window.joinWaitlist = function () {
  const name  = document.getElementById('wl-name')?.value?.trim();
  const phone = document.getElementById('wl-phone')?.value?.trim();
  const date  = document.getElementById('wl-date')?.value;
  const styleId = document.querySelector('input[name="gallery_image_id"]:checked')?.value || '';
  if (!name || !phone) { alert('Please enter your name and phone.'); return; }

  fetch(window.GYC_URL + '/api/join-waitlist.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'name=' + encodeURIComponent(name) + '&phone=' + encodeURIComponent(phone)
        + '&date=' + encodeURIComponent(date) + '&gallery_image_id=' + encodeURIComponent(styleId)
  })
  .then(r => r.json())
  .then(function (data) {
    const form = document.getElementById('waitlist-form');
    if (form) {
      form.innerHTML = '<div class="alert alert-success">✓ You\'re on the list! We\'ll WhatsApp you at <strong>' + phone + '</strong> if a slot opens.</div>';
    }
  });
};
