<?= view('templates/header', ['pageTitle' => 'Cities List', 'activePage' => 'cities']) ?>

<div class="container py-4 flex-grow-1">

  <div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= base_url('lablist') ?>" class="btn btn-link btn-sm mt-1 text-secondary" style="text-decoration:none;">
      <i class="ti ti-arrow-left fs-3"></i>
    </a>
    <h2 class="fw-semibold mb-0" style="color:#961914;">Cities List</h2>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show py-2 small">
      <?= session()->getFlashdata('success') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show py-2 small">
      <?= session()->getFlashdata('error') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="d-flex gap-2 mb-4">
    <button type="button" class="btn text-white" style="background:#134557;"
            data-bs-toggle="modal" data-bs-target="#importCitiesModal">
      <i class="ti ti-upload me-1"></i> <?= $count === 0 ? 'Import List' : 'Update List' ?>
    </button>
    <button type="button" class="btn btn-outline-secondary"
            data-bs-toggle="modal" data-bs-target="#addCityModal">
      <i class="ti ti-map-pin me-1"></i> Add City
    </button>
  </div>

  <!-- Import Modal -->
  <div class="modal fade" id="importCitiesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header border-0" style="background:#134557;">
          <h5 class="modal-title text-white fw-semibold">
            <i class="ti ti-upload me-2"></i> Import Cities
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="<?= base_url('cities/import') ?>" method="POST" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <div class="modal-body pt-4">
            <p class="text-muted small mb-3">Excel/CSV column: <strong>City Name</strong> (first column, with header row)</p>
            <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls,.csv" required/>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn text-white" style="background:#134557;">
              <i class="ti ti-upload me-1"></i> Import
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add City Modal -->
  <div class="modal fade" id="addCityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header border-0" style="background:#134557;">
          <h5 class="modal-title text-white fw-semibold">
            <i class="ti ti-map-pin me-2"></i> Add City
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="<?= base_url('cities/add') ?>" method="POST">
          <?= csrf_field() ?>
          <div class="modal-body pt-4">
            <div class="mb-3">
              <label class="form-label fw-medium" style="color:black;background-color:white">City Name</label>
              <div class="input-group">
                <span class="input-group-text"><i class="ti ti-map-pin"></i></span>
                <input type="text" name="name" class="form-control" placeholder="e.g. Lahore" required/>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn text-white" style="background:#134557;">
              <i class="ti ti-plus me-1"></i> Add
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Table -->
  <?php if ($count > 0): ?>
  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead style="background:#134557; color:#fff;">
            <tr>
              <th class="py-3 px-4">#</th>
              <th class="py-3">City Name</th>
              <th class="py-3">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cities as $i => $city): ?>
              <tr>
                <td class="px-4"><?= $i + 1 ?></td>
                <td><?= esc($city['name']) ?></td>
                <td>
                  <?php if ($city['status'] === 'active'): ?>
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
  <?php endif; ?>

</div>

<?= view('templates/footer') ?>