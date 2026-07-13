<?= view('templates/header', ['pageTitle' => 'Lab List', 'activePage' => 'lablist']) ?>

<div class="container-fluid px-3 px-md-4 py-4 flex-grow-1" style="background:#f0f4f8; min-height:100vh;">

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h2 class="fw-bold mb-0" style="color:#c9140e; font-family:'Poppins',sans-serif;">Lab List</h2>
      <small class="text-muted">Manage registered lab partners</small>
    </div>
    <a href="<?= base_url('registerform') ?>" class="btn text-white fw-semibold px-4"
       style="background:#c9140e; border-radius:10px;">
      <i class="ti ti-plus me-1"></i> Register New Lab
    </a>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show py-2 small">
      <?= session()->getFlashdata('success') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Desktop Table -->
  <div class="d-none d-md-block card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table mb-0" id="labTable" style="border-collapse:separate; border-spacing:0;">
          <thead>

          <tr style="background:#1a3a6b;">

              <!-- <th class="text-center" style= "font-size: 12px;">
                  #
              </th> -->

              <th class="text-center" style= "font-size: 12px; padding-left: 30px;">
                  Lab Name
                  <input type="text"
                        id="filterName"
                        class="text-center form-control form-control-sm mt-2"
                        placeholder="Search">
              </th>

              <th class="text-center" style= "font-size: 12px;">
                  Email
                  <input type="text"
                        id="filterEmail"
                        class="text-center form-control form-control-sm mt-2"
                        placeholder="Search">
              </th>

              <th class="text-center" style= "font-size: 12px;">
                  Password
                  <input type="text"
                        id="filterPassword"
                        class="text-center form-control form-control-sm mt-2"
                        placeholder="Search">
              </th>

              <th class="text-center" style= "font-size: 12px;">
                  Status
                  <select id="filterStatus"
                          class="form-select form-select-sm mt-2">
                      <option value="">All</option>
                      <option>Active</option>
                      <option>Inactive</option>
                  </select>
              </th>

              <th class="text-center" style= "font-size: 12px;">
                  Actions
              </th>

          </tr>

          </thead>
          <tbody>
            <?php if (!empty($labs)): ?>
              <?php foreach ($labs as $i => $lab): ?>
                <tr style="border-bottom:1px solid #e5e7eb;">
                  <!-- <td class="px-4 text-center text-muted py-3"><?= $i + 1 ?></td> -->
                  <td class="text-center py-3">
                    <a href="javascript:void(0)"
                       class="fw-semibold text-decoration-none lab-name-link"
                       style="color:#1a3a6b;"
                       data-bs-toggle="modal"
                       data-bs-target="#labDetailsModal"
                       data-name="<?= esc($lab['name']) ?>"
                       data-person="<?= esc($lab['contact_person']) ?>"
                       data-license="<?= esc($lab['license_number']) ?>"
                       data-address="<?= esc($lab['address']) ?>"
                       data-email="<?= esc($lab['email']) ?>"
                       data-password="<?= esc($lab['password_hint'] ?? '') ?>">
                      <?= esc($lab['name']) ?>
                    </a>
                  </td>
                  <td class="text-center text-muted small py-3"><?= esc($lab['email']) ?></td>
                  <td class="text-center py-3"><?= esc($lab['password_hint'] ?? '-') ?></td>
                  <td class="text-center py-3">
                    <?php if ($lab['status'] === 'active'): ?>
                      <span class="badge rounded-pill px-3 py-2" style="background:#e8f8ee; color:#0c7a43;">Active</span>
                    <?php else: ?>
                      <span class="badge rounded-pill px-3 py-2 bg-secondary">Inactive</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center py-3">
                    <?php if ($lab['test_count'] > 0): ?>
                      <a href="<?= base_url('labs/' . $lab['id'] . '/pricelist?mode=view') ?>"
                         class="btn btn-sm me-1" style="background:#eef2f7; color:#1a3a6b; border-radius:8px;"
                         title="View List" data-bs-toggle="tooltip" data-bs-placement="top">
                        <i class="ti ti-list"></i>
                      </a>
                      <a href="<?= base_url('labs/' . $lab['id'] . '/pricelist?mode=update') ?>"
                         class="btn btn-sm text-white me-1" style="background:#1a3a6b; border-radius:8px;"
                         title="Update Price List" data-bs-toggle="tooltip" data-bs-placement="top">
                        <i class="ti ti-refresh"></i>
                      </a>
                    <?php else: ?>
                      <a href="<?= base_url('labs/' . $lab['id'] . '/pricelist') ?>"
                         class="btn btn-sm text-white me-1" style="background:#c9140e; border-radius:8px;"
                         title="Import Price List" data-bs-toggle="tooltip" data-bs-placement="top">
                        <i class="ti ti-upload"></i>
                      </a>
                    <?php endif; ?>
                    <!-- <a href="<?= base_url('labs/' . $lab['id'] . '/phlebotomist') ?>"
                       class="btn btn-sm text-white me-1" style="background:#2463c2; border-radius:8px;"
                       title="Phlebotomist List" data-bs-toggle="tooltip" data-bs-placement="top">
                      <i class="ti ti-user-plus"></i>
                    </a> -->
                    <a href="<?= base_url('labs/' . $lab['id'] . '/edit') ?>"
                       class="btn btn-sm" style="background:#eef2f7; color:#6b7280; border-radius:8px;"
                       title="Edit Lab" data-bs-toggle="tooltip" data-bs-placement="top">
                      <i class="ti ti-edit"></i>
                    </a>

                    <button type="button" class="btn btn-sm me-1" style="background:#fff3cd; color:#92610a; border-radius:8px;"
                        title="View History" data-bs-toggle="modal" data-bs-target="#historyModal"
                        data-history-url="<?= base_url('labs/' . $lab['id'] . '/history') ?>"
                        data-history-title="<?= esc($lab['name']) ?>">
                        <i class="ti ti-history"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center text-muted py-5">
                  <i class="ti ti-flask d-block mb-2" style="font-size:32px;"></i>
                  No labs registered yet.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Mobile Cards -->
  <div class="d-md-none">
    <?php if (!empty($labs)): ?>
      <?php foreach ($labs as $i => $lab): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-3">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <a href="javascript:void(0)"
                   class="fw-bold text-decoration-none lab-name-link d-block"
                   style="color:#1a3a6b; font-size:16px;"
                   data-bs-toggle="modal"
                   data-bs-target="#labDetailsModal"
                   data-name="<?= esc($lab['name']) ?>"
                   data-person="<?= esc($lab['contact_person']) ?>"
                   data-license="<?= esc($lab['license_number']) ?>"
                   data-address="<?= esc($lab['address']) ?>"
                   data-email="<?= esc($lab['email']) ?>"
                   data-password="<?= esc($lab['password_hint'] ?? '') ?>">
                  <?= esc($lab['name']) ?>
                </a>
                <small class="text-muted"><?= esc($lab['email']) ?></small>
              </div>
              <?php if ($lab['status'] === 'active'): ?>
                <span class="badge rounded-pill px-3 py-2" style="background:#e8f8ee; color:#0c7a43;">Active</span>
              <?php else: ?>
                <span class="badge rounded-pill px-3 py-2 bg-secondary">Inactive</span>
              <?php endif; ?>
            </div>
            <div class="d-flex gap-2 flex-wrap mt-2">
              <?php if ($lab['test_count'] > 0): ?>
                <a href="<?= base_url('labs/' . $lab['id'] . '/pricelist?mode=view') ?>"
                   class="btn btn-sm" style="background:#eef2f7; color:#1a3a6b; border-radius:8px;">
                  <i class="ti ti-list me-1"></i> View
                </a>
                <a href="<?= base_url('labs/' . $lab['id'] . '/pricelist?mode=update') ?>"
                   class="btn btn-sm text-white" style="background:#1a3a6b; border-radius:8px;">
                  <i class="ti ti-refresh me-1"></i> Update
                </a>
              <?php else: ?>
                <a href="<?= base_url('labs/' . $lab['id'] . '/pricelist') ?>"
                   class="btn btn-sm text-white" style="background:#c9140e; border-radius:8px;">
                  <i class="ti ti-upload me-1"></i> Import
                </a>
              <?php endif; ?>
              <a href="<?= base_url('labs/' . $lab['id'] . '/phlebotomist') ?>"
                 class="btn btn-sm text-white" style="background:#2463c2; border-radius:8px;">
                <i class="ti ti-user-plus me-1"></i> Phlebotomist
              </a>
              <a href="<?= base_url('labs/' . $lab['id'] . '/edit') ?>"
                 class="btn btn-sm" style="background:#eef2f7; color:#6b7280; border-radius:8px;">
                <i class="ti ti-edit me-1"></i> Edit
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="text-center text-muted py-5">
        <i class="ti ti-flask d-block mb-2" style="font-size:32px;"></i>
        No labs registered yet.
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- Lab Details Modal -->
<div class="modal fade" id="labDetailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow rounded-4">
      <div class="modal-header border-0" style="background:#1a3a6b;">
        <h5 class="modal-title text-white fw-semibold">
          <i class="ti ti-flask me-2"></i><span id="modalLabName">Lab Details</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <table class="table table-borderless mb-0">
          <tbody>
            <tr><th class="text-muted small" style="width:40%;">Lab Name</th><td class="fw-medium" id="modalName"></td></tr>
            <tr><th class="text-muted small">Contact Person</th><td id="modalPerson"></td></tr>
            <tr><th class="text-muted small">License Number</th><td id="modalLicense"></td></tr>
            <tr><th class="text-muted small">Address</th><td id="modalAddress"></td></tr>
            <tr><th class="text-muted small">Email</th><td id="modalEmail"></td></tr>
            <tr><th class="text-muted small">Password</th><td id="modalPassword"></td></tr>
          </tbody>
        </table>
      </div>
      
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.getElementById('labDetailsModal').addEventListener('show.bs.modal', function (event) {
    const link = event.relatedTarget;
    document.getElementById('modalLabName').textContent  = link.dataset.name || '';
    document.getElementById('modalName').textContent     = link.dataset.name || '';
    document.getElementById('modalPerson').textContent   = link.dataset.person || '';
    document.getElementById('modalLicense').textContent  = link.dataset.license || '';
    document.getElementById('modalAddress').textContent  = link.dataset.address || '';
    document.getElementById('modalEmail').textContent    = link.dataset.email || '';
    document.getElementById('modalPassword').textContent = link.dataset.password || '';
  });

  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el);
  });
});

document.querySelectorAll("#filterName,#filterEmail,#filterPassword,#filterStatus")
.forEach(el => {
    el.addEventListener("keyup", filterTable);
    el.addEventListener("change", filterTable);
});

function filterTable() {

    const name = document.getElementById("filterName").value.toLowerCase();
    const email = document.getElementById("filterEmail").value.toLowerCase();
    const password = document.getElementById("filterPassword").value.toLowerCase();
    const status = document.getElementById("filterStatus").value.toLowerCase();

    document.querySelectorAll("#labTable tbody tr").forEach(row => {

        const cols = row.querySelectorAll("td");

        if(cols.length === 0) return;

        const labName = cols[1].innerText.toLowerCase();
        const labEmail = cols[2].innerText.toLowerCase();
        const labPassword = cols[3].innerText.toLowerCase();
        const labStatus = cols[4].innerText.toLowerCase();

        const show =
            labName.includes(name) &&
            labEmail.includes(email) &&
            labPassword.includes(password) &&
            (status === "" || labStatus.includes(status));

        row.style.display = show ? "" : "none";
    });

}
</script>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow rounded-4">
      <div class="modal-header border-0 pb-2" style="background:#1a3a6b;">
        <div>
          <h5 class="modal-title text-white fw-semibold mb-0">
            <i class="ti ti-history me-2"></i><span id="historyModalTitle">History</span>
          </h5>
          <small class="text-white-50" id="historyModalCount"></small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4" style="max-height:65vh; overflow-y:auto; background:#f8f9fb;">

        <div id="historyLoading" class="text-center text-muted py-5 d-none">
          <div class="spinner-border spinner-border-sm text-secondary mb-2" role="status"></div>
          <div class="small">Loading history...</div>
        </div>

        <div id="historyTimeline" class="d-none"></div>

        <div id="historyEmpty" class="text-center text-muted py-5 d-none">
          <i class="ti ti-history d-block mb-2" style="font-size:36px; opacity:.4;"></i>
          No history recorded yet.
        </div>

      </div>
    </div>
  </div>
</div>

<style>
#historyTimeline {
  position: relative;
  padding-left: 36px;
}
#historyTimeline::before {
  content: '';
  position: absolute;
  left: 15px;
  top: 6px;
  bottom: 6px;
  width: 2px;
  background: #e2e6ed;
}
.history-item {
  position: relative;
  margin-bottom: 20px;
}
.history-item:last-child {
  margin-bottom: 0;
}
.history-dot {
  position: absolute;
  left: -36px;
  top: 2px;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  color: #fff;
  box-shadow: 0 0 0 3px #f8f9fb;
}
.history-card {
  background: #fff;
  border: 1px solid #edf0f4;
  border-radius: 12px;
  padding: 12px 16px;
}
.history-action-label {
  font-weight: 600;
  font-size: 13.5px;
  color: #1a3a6b;
  text-transform: capitalize;
}
.history-desc {
  font-size: 12.5px;
  color: #6b7280;
  margin-top: 2px;
}
.history-meta {
  font-size: 11.5px;
  color: #9aa2b1;
  margin-top: 6px;
  display: flex;
  gap: 14px;
  flex-wrap: wrap;
}
.history-meta i {
  margin-right: 3px;
}
</style>

<script>
const HISTORY_STYLES = {
  registered:              { icon: 'ti-flag',            color: '#0c7a43' },
  updated:                 { icon: 'ti-edit',             color: '#2463c2' },
  activated:               { icon: 'ti-toggle-right',     color: '#0c7a43' },
  deactivated:             { icon: 'ti-toggle-left',      color: '#6b7280' },
  deleted:                 { icon: 'ti-trash',            color: '#dc2626' },
  phlebotomist_added:      { icon: 'ti-user-plus',        color: '#2463c2' },
  phlebotomist_imported:   { icon: 'ti-file-upload',      color: '#2463c2' },
  phlebotomist_activated:  { icon: 'ti-user-check',       color: '#0c7a43' },
  phlebotomist_deactivated:{ icon: 'ti-user-off',         color: '#6b7280' },
  price_list_imported:     { icon: 'ti-file-spreadsheet', color: '#c9140e' },
  price_list_updated:      { icon: 'ti-refresh',          color: '#c9140e' },
};
const HISTORY_DEFAULT_STYLE = { icon: 'ti-point', color: '#8b93a3' };

document.getElementById('historyModal').addEventListener('show.bs.modal', function (event) {
  const btn = event.relatedTarget;
  const url = btn.dataset.historyUrl;
  document.getElementById('historyModalTitle').textContent = btn.dataset.historyTitle + ' — History';

  const timeline = document.getElementById('historyTimeline');
  const loading  = document.getElementById('historyLoading');
  const empty    = document.getElementById('historyEmpty');
  const countEl  = document.getElementById('historyModalCount');

  timeline.innerHTML = '';
  timeline.classList.add('d-none');
  empty.classList.add('d-none');
  countEl.textContent = '';
  loading.classList.remove('d-none');

  fetch(url)
    .then(res => res.json())
    .then(data => {
      loading.classList.add('d-none');
      const logs = data.logs || [];

      if (logs.length === 0) {
        empty.classList.remove('d-none');
        return;
      }

      countEl.textContent = logs.length + ' event' + (logs.length > 1 ? 's' : '');
      timeline.classList.remove('d-none');

      logs.forEach(log => {
        const style = HISTORY_STYLES[log.action] || HISTORY_DEFAULT_STYLE;
        const dt = new Date(log.created_at.replace(' ', 'T'));
        const formatted = dt.toLocaleString('en-GB', {
          day: '2-digit', month: 'short', year: 'numeric',
          hour: '2-digit', minute: '2-digit'
        });
        const actionLabel = log.action.replace(/_/g, ' ');

        const item = document.createElement('div');
        item.className = 'history-item';
        item.innerHTML = `
          <div class="history-dot" style="background:${style.color};">
            <i class="ti ${style.icon}"></i>
          </div>
          <div class="history-card">
            <div class="history-action-label">${actionLabel}</div>
            ${log.description ? `<div class="history-desc">${log.description}</div>` : ''}
            <div class="history-meta">
              <span><i class="ti ti-user"></i>${log.performed_by_name ?? 'System'}</span>
              <span><i class="ti ti-clock"></i>${formatted}</span>
            </div>
          </div>
        `;
        timeline.appendChild(item);
      });
    })
    .catch(() => {
      loading.classList.add('d-none');
      empty.classList.remove('d-none');
    });
});
</script>

<?= view('templates/footer') ?>