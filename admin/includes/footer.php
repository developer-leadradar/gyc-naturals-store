  </div><!-- /.admin-content -->
</div><!-- /.admin-main -->
</div><!-- /.admin-layout -->

<script src="<?= SITE_URL ?>/assets/js/admin.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof lucide !== 'undefined') lucide.createIcons();

  // Sidebar instant-nav: prefetch on hover, soft swap on click (preserves sidebar state)
  const sidebar = document.getElementById('admin-sidebar');
  const main    = document.querySelector('.admin-content');
  const topbar  = document.querySelector('.admin-topbar span');
  if (!sidebar || !main) return;

  const cache = new Map();
  function fetchPage(href) {
    if (cache.has(href)) return Promise.resolve(cache.get(href));
    const p = fetch(href, { credentials: 'same-origin' }).then(r => r.text());
    cache.set(href, p);
    return p;
  }

  // Prefetch on hover
  sidebar.querySelectorAll('a.sidebar-link').forEach(function(a) {
    a.addEventListener('mouseenter', function() { fetchPage(a.href); });
  });

  // Soft swap on click
  sidebar.addEventListener('click', function(e) {
    const a = e.target.closest('a.sidebar-link');
    if (!a) return;
    if (e.ctrlKey || e.metaKey || e.shiftKey || a.target === '_blank') return;
    if (a.href === window.location.href) { e.preventDefault(); return; }
    e.preventDefault();

    document.body.classList.add('admin-loading');
    fetchPage(a.href).then(function(html) {
      const doc      = new DOMParser().parseFromString(html, 'text/html');
      const newMain  = doc.querySelector('.admin-content');
      const newTitle = doc.querySelector('.admin-topbar span')?.textContent;
      if (newMain) main.innerHTML = newMain.innerHTML;
      if (topbar && newTitle) topbar.textContent = newTitle;
      document.title = doc.title;
      window.history.pushState({ swapped: true }, '', a.href);
      // Update active link
      sidebar.querySelectorAll('a.sidebar-link.active').forEach(function(el) { el.classList.remove('active'); });
      a.classList.add('active');
      // Re-init Lucide icons and re-run any inline scripts inside swapped content
      if (typeof lucide !== 'undefined') lucide.createIcons();
      main.querySelectorAll('script').forEach(function(old) {
        const s = document.createElement('script');
        if (old.src) s.src = old.src; else s.textContent = old.textContent;
        old.parentNode.replaceChild(s, old);
      });
      window.scrollTo(0, 0);
    }).catch(function() {
      window.location.href = a.href;
    }).finally(function() {
      document.body.classList.remove('admin-loading');
    });
  });

  // Browser back/forward should reload normally (state is server-rendered)
  window.addEventListener('popstate', function(e) {
    if (e.state && e.state.swapped) window.location.reload();
  });
});
</script>
<style>
  body.admin-loading { cursor: progress; }
  body.admin-loading .admin-content { opacity: 0.6; transition: opacity .15s; pointer-events: none; }
</style>
</body>
</html>
