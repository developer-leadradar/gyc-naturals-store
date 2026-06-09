/* GYC Naturals — Admin Panel JavaScript */

document.addEventListener('DOMContentLoaded', function () {
  // Lucide
  if (typeof lucide !== 'undefined') lucide.createIcons();

  // Mobile sidebar toggle
  const sidebarToggle = document.getElementById('sidebar-toggle');
  const sidebar       = document.getElementById('admin-sidebar');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', function () {
      sidebar.classList.toggle('open');
    });
  }

  // Admin tab bars
  document.querySelectorAll('.admin-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {
      const container = tab.closest('[data-admin-tabs]') || tab.parentElement;
      container.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      const target = tab.dataset.tab;
      document.querySelectorAll('.admin-tab-panel').forEach(function (p) {
        p.classList.toggle('active', p.id === target);
      });
    });
  });

  // Image preview on file input
  document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
    input.addEventListener('change', function () {
      const previewId = input.dataset.preview;
      const preview   = document.getElementById(previewId);
      if (!preview || !input.files[0]) return;
      const reader = new FileReader();
      reader.onload = function (e) {
        if (preview.tagName === 'IMG') preview.src = e.target.result;
        else preview.style.backgroundImage = 'url(' + e.target.result + ')';
      };
      reader.readAsDataURL(input.files[0]);
    });
  });

  // Delete confirmation
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      const msg = el.dataset.confirm || 'Are you sure?';
      if (!confirm(msg)) e.preventDefault();
    });
  });

  // Toggle switch (active/inactive)
  document.querySelectorAll('.toggle-active').forEach(function (toggle) {
    toggle.addEventListener('change', function () {
      const id    = toggle.dataset.id;
      const table = toggle.dataset.table;
      const field = toggle.dataset.field || 'is_active';
      const value = toggle.checked ? 1 : 0;
      fetch(window.ADMIN_URL + '/api/toggle-status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id + '&table=' + table + '&field=' + field + '&value=' + value
      })
      .then(r => r.json())
      .then(function (data) {
        if (!data.success) {
          toggle.checked = !toggle.checked;
          alert('Update failed.');
        }
      });
    });
  });

  // Slug auto-generation from title
  const titleInput = document.getElementById('admin-title');
  const slugInput  = document.getElementById('admin-slug');
  if (titleInput && slugInput) {
    titleInput.addEventListener('input', function () {
      if (!slugInput.dataset.custom) {
        slugInput.value = titleInput.value
          .toLowerCase()
          .replace(/[^a-z0-9\s-]/g, '')
          .replace(/\s+/g, '-')
          .replace(/-+/g, '-')
          .trim();
      }
    });
    slugInput.addEventListener('input', function () {
      slugInput.dataset.custom = '1';
    });
  }

  // Word count for body textarea
  const bodyTextarea = document.getElementById('admin-body');
  const wordCountEl  = document.getElementById('word-count');
  if (bodyTextarea && wordCountEl) {
    function updateWordCount() {
      const words = bodyTextarea.value.trim().split(/\s+/).filter(w => w).length;
      wordCountEl.textContent = words + ' words';
      wordCountEl.style.color = words < 600 ? '#C1440E' : '#2D6A4F';
    }
    bodyTextarea.addEventListener('input', updateWordCount);
    updateWordCount();
  }

  // Product checkbox list for bundles
  const productSearch = document.getElementById('product-search');
  if (productSearch) {
    productSearch.addEventListener('input', function () {
      const q = productSearch.value.toLowerCase();
      document.querySelectorAll('.product-checkbox-item').forEach(function (item) {
        item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }
});

// Set admin URL
window.ADMIN_URL = (function () {
  const scripts = document.querySelectorAll('script[src*="admin.js"]');
  if (scripts.length) return scripts[scripts.length - 1].src.replace('/assets/js/admin.js', '');
  return '';
})();
