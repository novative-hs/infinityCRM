<?= view('templates/header', ['pageTitle' => 'My Phlebotomists', 'activePage' => 'dashboard']) ?>

<div class="container py-4 flex-grow-1">

  <div class="d-flex align-items-start gap-3 mb-4">
    <a href="<?= base_url('franchiseDashboard/dashboard') ?>" class="btn btn-link btn-sm mt-1 text-secondary" style="text-decoration:none;">
      <i class="ti ti-arrow-left fs-3"></i>
    </a>
    <h2 class="fw-semibold mb-0" style="color:#961914;">My Phlebotomists</h2>
  </div>

  <?php if ($count > 0): ?>
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead style="background:#1a3a6b; color:#fff;">
            <tr>
              <th class="py-3 px-4">#</th>
              <th class="py-3">Name</th>
              <th class="py-3">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($phlebotomists as $i => $p): ?>
              <tr>
                <td class="px-4 py-3"><?= $i + 1 ?></td>
                <td class="py-3"><?= esc($p['name']) ?></td>
                <td class="py-3">
                  <?php if ($p['status'] === 'active'): ?>
                    <span class="badge bg-success">Active</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Inactive</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php else: ?>
    <div class="text-center text-muted py-5">
      <i class="ti ti-users d-block mb-2" style="font-size:32px;"></i>
      No phlebotomists assigned yet.
    </div>
  <?php endif; ?>

</div>

<?= view('templates/footer') ?>