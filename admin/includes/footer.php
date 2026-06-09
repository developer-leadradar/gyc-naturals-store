  </div><!-- /.admin-content -->
</div><!-- /.admin-main -->
</div><!-- /.admin-layout -->

<script src="<?= SITE_URL ?>/assets/js/admin.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof lucide !== 'undefined') lucide.createIcons();
  // Show sidebar toggle on mobile
  var toggle = document.getElementById('sidebar-toggle');
  if (toggle) toggle.style.display = 'flex';
});
</script>
</body>
</html>
