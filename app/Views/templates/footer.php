<!-- Page content ends -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <?php
  $waPhone   = session()->getFlashdata('wa_phone');
  $waMessage = session()->getFlashdata('wa_message');
  ?>
  <?php if (!empty($waPhone) && !empty($waMessage)): ?>
  <script>
  (function () {
    const phone   = <?= json_encode($waPhone) ?>;
    const message = <?= json_encode($waMessage) ?>;
    const url = 'https://wa.me/' + phone + '?text=' + encodeURIComponent(message);

    const win = window.open(url, '_blank');

    if (!win || win.closed || typeof win.closed === 'undefined') {
      const banner = document.createElement('div');
      banner.style.cssText = 'position:fixed; bottom:20px; right:20px; z-index:9999; background:#1a3a6b; color:#fff; padding:14px 20px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,.2); font-size:14px; display:flex; align-items:center; gap:12px;';
      banner.innerHTML = '⚠️ Popup blocked — Click to send WhatsApp message ' +
        '<button style="background:#25D366; color:#fff; border:none; padding:8px 14px; border-radius:8px; cursor:pointer; font-weight:600;">Open WhatsApp</button>';
      banner.querySelector('button').addEventListener('click', function () {
        window.open(url, '_blank');
        banner.remove();
      });
      document.body.appendChild(banner);

      setTimeout(() => banner.remove(), 10000);
    }
  })();
  </script>
  <?php endif; ?>

<?php
  $waFranchisePhone   = session()->getFlashdata('wa_franchise_phone');
  $waFranchiseMessage = session()->getFlashdata('wa_franchise_message');
?>
<?php if (!empty($waFranchisePhone) && !empty($waFranchiseMessage)): ?>
<script>
(function () {
  const phone   = <?= json_encode($waFranchisePhone) ?>;
  const message = <?= json_encode($waFranchiseMessage) ?>;
  const url = 'https://wa.me/' + phone + '?text=' + encodeURIComponent(message);

  const win = window.open(url, '_blank');

  if (!win || win.closed || typeof win.closed === 'undefined') {
    const banner = document.createElement('div');
    banner.style.cssText = 'position:fixed; bottom:90px; right:20px; z-index:9999; background:#961914; color:#fff; padding:14px 20px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,.2); font-size:14px; display:flex; align-items:center; gap:12px;';
    banner.innerHTML = '🏢 Click to notify franchise on WhatsApp ' +
      '<button style="background:#25D366; color:#fff; border:none; padding:8px 14px; border-radius:8px; cursor:pointer; font-weight:600;">Open WhatsApp</button>';
    banner.querySelector('button').addEventListener('click', function () {
      window.open(url, '_blank');
      banner.remove();
    });
    document.body.appendChild(banner);

    setTimeout(() => banner.remove(), 10000);
  }
})();
</script>
<?php endif; ?>
</body>
</html>