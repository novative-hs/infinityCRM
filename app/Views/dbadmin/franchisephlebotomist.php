<?= view('templates/header', ['pageTitle' => 'Phlebotomist List', 'activePage' => 'franchiselist']) ?>

<div class="container py-4 flex-grow-1">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
    <div class="d-flex align-items-start gap-3 mb-4">

       <a href="<?= base_url('franchiselist') ?>" class="btn btn-link btn-sm mt-1 text-secondary" style="text-decoration:none;">
            <i class="ti ti-arrow-left fs-3"></i>
       </a>
             <h2 class="fw-semibold mb-0" style="color:#961914;">Phlebotomist List</h2>

    <div>
</div>
</div>
  </div>
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

  <?php if ($count === 0): ?>

    <div class="mb-4">
      <button type="button" class="btn text-white" style="background:#134557;"
              data-bs-toggle="modal" data-bs-target="#importListModal">
        <i class="ti ti-upload me-1"></i> Import List
      </button>
      <button type="button" class="btn btn-outline-secondary"
              data-bs-toggle="modal" data-bs-target="#addPhlebotomistModal">
        <i class="ti ti-user-plus me-1"></i> Add Phlebotomist
      </button>
    </div>

  <?php else: ?>

    <div class="d-flex gap-2 mb-4">
      <button type="button" class="btn text-white" style="background:#134557;"
              data-bs-toggle="modal" data-bs-target="#updateListModal">
        <i class="ti ti-refresh me-1"></i> Update List
      </button>
      <button type="button" class="btn btn-outline-secondary"
              data-bs-toggle="modal" data-bs-target="#addPhlebotomistModal">
        <i class="ti ti-user-plus me-1"></i> Add Phlebotomist
      </button>
    </div>

  <?php endif; ?>

  <!-- Import Modal -->
  <div class="modal fade" id="importListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header border-0" style="background:#134557;">
          <h5 class="modal-title text-white fw-semibold">
            <i class="ti ti-upload me-2"></i> Import Phlebotomist List
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="<?= base_url('franchise/' . $franchise['id'] . '/phlebotomist/import') ?>"
              method="POST" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <div class="modal-body pt-4">
            <p class="text-muted small mb-3">Excel column: <strong>Name</strong></p>
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

  <!-- Update List Modal -->
  <div class="modal fade" id="updateListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header border-0" style="background:#134557;">
          <h5 class="modal-title text-white fw-semibold">
            <i class="ti ti-refresh me-2"></i> Update Phlebotomist List
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="<?= base_url('franchise/' . $franchise['id'] . '/phlebotomist/import') ?>"
              method="POST" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <div class="modal-body pt-4">
            <p class="text-muted small mb-3">Excel column: <strong>Name</strong></p>
            <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls,.csv" required/>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn text-white" style="background:#134557;">
              <i class="ti ti-upload me-1"></i> Update
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Add Phlebotomist Modal -->
  <div class="modal fade" id="addPhlebotomistModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header border-0" style="background:#134557;">
          <h5 class="modal-title text-white fw-semibold">
            <i class="ti ti-user-plus me-2"></i> Add Phlebotomist
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="<?= base_url('franchise/' . $franchise['id'] . '/phlebotomist/add') ?>" method="POST">
          <?= csrf_field() ?>
          <div class="modal-body pt-4">
            <div class="mb-3">
              <label class="form-label fw-medium" style="color:#134557;background:none">Full Name</label>
              <div class="input-group">
                <span class="input-group-text"><i class="ti ti-user"></i></span>
                <input type="text" name="name" id="phlebNameInput" class="form-control" placeholder="e.g. Ali Khan" required/>
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
      <th class="py-3">Name</th>
      <th class="py-3">Status</th>
      <th class="py-3 text-center">Action</th>
    </tr>
    </thead>
    <tbody>
        <?php foreach ($phlebotomists as $i => $p): ?>
          <tr>
            <td class="px-4"><?= $i + 1 ?></td>
            <td><?= esc($p['name']) ?></td>
            <td>
              <?php if ($p['status'] === 'active'): ?>
                <span class="badge bg-success">Active</span>
              <?php else: ?>
                <span class="badge bg-secondary">Inactive</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <form action="<?= base_url('franchise/' . $franchise['id'] . '/phlebotomist/' . $p['id'] . '/toggleStatus') ?>"
                    method="POST" class="d-inline m-0">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm"
                    style="background:<?= $p['status'] === 'active' ? '#fdeaea' : '#e8f8ee' ?>; color:<?= $p['status'] === 'active' ? '#c9140e' : '#0c7a43' ?>; border-radius:8px;">
                  <?= $p['status'] === 'active' ? 'Mark Inactive' : 'Mark Active' ?>
                </button>
              </form>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
  const nameInput = document.getElementById('phlebNameInput');
  if (nameInput) {
    nameInput.addEventListener('input', function (e) {
      const cursorPos = this.selectionStart;
      const original = this.value;

      const capitalized = original.replace(/(^|\s)([a-z])/g, function (match, sep, letter) {
        return sep + letter.toUpperCase();
      });

      if (capitalized !== original) {
        this.value = capitalized;
        this.setSelectionRange(cursorPos, cursorPos);
      }
    });
  }
});
</script>

<?= view('templates/footer') ?>