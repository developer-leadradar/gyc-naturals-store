/* GYC Naturals — Moodboard localStorage Logic */

(function () {
  'use strict';

  function getMoodboard()       { try { return JSON.parse(localStorage.getItem('gyc_moodboard') || '[]'); } catch(e) { return []; } }
  function saveMoodboard(items) { localStorage.setItem('gyc_moodboard', JSON.stringify(items)); }
  function isSaved(slug)        { return getMoodboard().indexOf(slug) !== -1; }

  window.GYC_MOODBOARD = {
    getAll:  getMoodboard,
    isSaved: isSaved,
    add: function (slug) {
      const items = getMoodboard();
      if (!items.includes(slug)) { items.push(slug); saveMoodboard(items); }
    },
    remove: function (slug) {
      const items = getMoodboard().filter(function (s) { return s !== slug; });
      saveMoodboard(items);
    },
    toggle: function (slug) {
      if (isSaved(slug)) { this.remove(slug); return false; }
      else { this.add(slug); return true; }
    },
    clear: function () { localStorage.removeItem('gyc_moodboard'); },
    count: function () { return getMoodboard().length; }
  };

  // Auto-render moodboard page grid if #moodboard-grid exists
  document.addEventListener('DOMContentLoaded', function () {
    const grid  = document.getElementById('moodboard-grid');
    const empty = document.getElementById('moodboard-empty');
    const countEl = document.getElementById('moodboard-count');
    if (!grid) return;

    // Check for ?styles= param (shared moodboard)
    const params = new URLSearchParams(window.location.search);
    const shared = params.get('styles');
    const slugs  = shared ? shared.split(',').filter(Boolean) : getMoodboard();

    if (countEl) countEl.textContent = slugs.length;

    if (!slugs.length) {
      if (empty)  empty.style.display  = 'block';
      grid.style.display = 'none';
      return;
    }

    // Load slugs from server
    fetch(window.GYC_URL + '/api/moodboard-items.php?slugs=' + encodeURIComponent(slugs.join(',')))
      .then(r => r.json())
      .then(function (items) {
        if (!items.length) {
          if (empty) empty.style.display = 'block';
          grid.style.display = 'none';
          return;
        }
        grid.innerHTML = items.map(function (item) {
          return '<div class="moodboard-item">'
            + '<a href="' + window.GYC_URL + '/style-detail.php?slug=' + encodeURIComponent(item.slug) + '">'
            + '<img src="' + item.image_url + '" alt="' + item.title + '" loading="lazy">'
            + '</a>'
            + '<div class="moodboard-item-actions">'
            + '<span style="font-size:0.88rem;font-weight:600;">' + item.title + '</span>'
            + '<div style="display:flex;gap:0.5rem;">'
            + '<a href="' + window.GYC_URL + '/book-appointment.php?style_id=' + item.id + '" class="btn btn-gold btn-sm">Book</a>'
            + (!shared ? '<button class="btn btn-sm" style="border:1.5px solid #ddd;" onclick="removeMoodboardItem(\'' + item.slug + '\', this)">Remove</button>' : '')
            + '</div></div></div>';
        }).join('');

        // Share URL button
        const shareBtn = document.getElementById('moodboard-share-btn');
        if (shareBtn && !shared) {
          shareBtn.style.display = 'inline-flex';
          shareBtn.addEventListener('click', function () {
            const url = window.location.origin + window.location.pathname + '?styles=' + slugs.join(',');
            navigator.clipboard.writeText(url).then(function () {
              if (typeof showToast === 'function') showToast('Moodboard link copied!', 'success');
              else alert('Link copied: ' + url);
            });
          });
        }
      });
  });

  window.removeMoodboardItem = function (slug, btn) {
    window.GYC_MOODBOARD.remove(slug);
    const item = btn.closest('.moodboard-item');
    if (item) { item.style.opacity = '0'; item.style.transition = '300ms'; setTimeout(function () { item.remove(); }, 300); }
    const remaining = getMoodboard().length;
    const countEl = document.getElementById('moodboard-count');
    if (countEl) countEl.textContent = remaining;
    if (!remaining) {
      const empty = document.getElementById('moodboard-empty');
      const grid  = document.getElementById('moodboard-grid');
      if (empty) empty.style.display = 'block';
      if (grid)  grid.style.display  = 'none';
    }
  };
})();
