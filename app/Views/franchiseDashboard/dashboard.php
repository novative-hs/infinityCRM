<?= view('templates/header', ['pageTitle' => 'Franchise Dashboard', 'activePage' => 'dashboard']) ?>

<div class="flex-grow-1 py-5" style="background:#f0f4f8;">
  <div class="container">

    <!-- Welcome Section -->
    <div class="text-center mb-5">
      <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle"
           style="width:70px; height:70px; background:linear-gradient(135deg, #c9140e, #8B0000);">
        <i class="ti ti-building-store text-white" style="font-size:32px;"></i>
      </div>
      <h1 class="fw-bold mb-1" style="color:#1a3a6b; font-family:'Poppins',sans-serif; font-size:28px;">
        Welcome back, <span style="color:#c9140e;"><?= esc($franchise['name']) ?></span> 👋
      </h1>
      <p style="color:#6b7280; font-size:14px;">
        <?= esc($franchise['lab_name']) ?> · <?= esc($franchise['city_name']) ?>
      </p>
    </div>

    <!-- Stat Cards -->
    <style>
      .stat-card {
        min-height: 90px;
        margin: 0 70px;
      }
      .stat-card .card1-body {
        height: 100%;
      }
      .stat-card .stat-label {
        line-height: 1.3;
      }
    </style>
    <div class="row g-4 mb-5">

      <div class="col-md-2 col-6 d-flex">
        <div class="card1 stat-card border-0 shadow-sm rounded-4 p-3 w-100">
          <div class="card1-body d-flex align-items-center gap-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:50px; height:50px; background:#eef2f7;">
              <i class="ti ti-clipboard-list" style="font-size:22px; color:#1a3a6b;"></i>
            </div>
            <div>
              <div class="fw-bold" style="font-size:24px; color:#1a3a6b;"><?= $totalBookings ?></div>
              <div class="stat-label" style="color:#6b7280; font-size:13px;">Total Bookings</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-2 col-6 d-flex">
        <div class="card1 stat-card border-0 shadow-sm rounded-4 p-3 w-100">
          <div class="card1-body d-flex align-items-center gap-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:50px; height:50px; background:#fff8e6;">
              <i class="ti ti-clock" style="font-size:22px; color:#b8860b;"></i>
            </div>
            <div>
              <div class="fw-bold" style="font-size:24px; color:#b8860b;"><?= $pendingBookings ?></div>
              <div class="stat-label" style="color:#6b7280; font-size:13px;">Pending</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-2 col-6 d-flex">
        <div class="card1 stat-card border-0 shadow-sm rounded-4 p-3 w-100">
          <div class="card1-body d-flex align-items-center gap-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:50px; height:50px; background:#e8f8ee;">
              <i class="ti ti-check" style="font-size:22px; color:#0c7a43;"></i>
            </div>
            <div>
              <div class="fw-bold" style="font-size:24px; color:#0c7a43;"><?= $completedBookings ?></div>
              <div class="stat-label" style="color:#6b7280; font-size:13px;">Sample Collected</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-2 col-6 d-flex">
        <div class="card1 stat-card border-0 shadow-sm rounded-4 p-3 w-100">
          <div class="card1-body d-flex align-items-center gap-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:50px; height:50px; background:#dcfce7;">
              <i class="ti ti-file-check" style="font-size:22px; color:#15803d;"></i>
            </div>
            <div>
              <div class="fw-bold" style="font-size:24px; color:#15803d;"><?= $reportReadyBookings ?></div>
              <div class="stat-label" style="color:#6b7280; font-size:13px;">Report Ready</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-2 col-6 d-flex">
        <a href="<?= base_url('franchise/myPhlebotomists') ?>" class="text-decoration-none w-100">
          <div class="card1 stat-card border-0 shadow-sm rounded-4 p-3 h-100">
            <div class="card1-body d-flex align-items-center gap-3">
              <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                   style="width:50px; height:50px; background:#e8f0fb;">
                <i class="ti ti-users" style="font-size:22px; color:#2463c2;"></i>
              </div>
              <div>
                <div class="fw-bold" style="font-size:24px; color:#2463c2;"><?= $phlebCount ?></div>
                <div class="stat-label" style="color:#6b7280; font-size:13px;">Phlebotomists</div>
              </div>
            </div>
          </div>
        </a>
      </div>

    </div>

    <!-- Franchise Info + Discount -->
    <div class="border-0 shadow-sm rounded-4 p-4 mb-4" style="background:#ffffff;">
      <div class="row">
        <div class="col-md-3 mb-2">
          <small class="text-muted d-block">Max Discount Allowed</small>
          <span class="fw-bold" style="color:#c9140e; font-size:18px;"><?= esc($franchise['discount']) ?>%</span>
        </div>
        <div class="col-md-3 mb-2">
          <small class="text-muted d-block">Lab Partner</small>
          <span class="fw-semibold" style="color:#1a3a6b;"><?= esc($franchise['lab_name']) ?></span>
        </div>
        <div class="col-md-3 mb-2">
          <small class="text-muted d-block">City</small>
          <span class="fw-semibold" style="color:#1a3a6b;"><?= esc($franchise['city_name']) ?></span>
        </div>
        <div class="col-md-3 mb-2">
          <small class="text-muted d-block">Status</small>
          <?php if ($franchise['status'] === 'active'): ?>
            <span class="badge rounded-pill px-3 py-2" style="background:#e8f8ee; color:#0c7a43;">Active</span>
          <?php else: ?>
            <span class="badge rounded-pill px-3 py-2 bg-secondary">Inactive</span>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Heading -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-bold mb-0" style="color:#1a3a6b;">Sample Collection Requests</h5>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-3" style="background:#eef2f7;">
      <form method="GET" action="" id="filterForm">
        <div class="d-flex flex-wrap align-items-center gap-3">

          <div class="d-flex align-items-center gap-2">
            <i class="ti ti-calendar text-muted"></i>
            <input type="date" name="date_from" class="form-control form-control-sm" style="width:145px;"
                   value="<?= esc($filters['dateFrom'] ?? '') ?>"
                   onchange="this.form.submit()"/>
            <span class="text-muted small">to</span>
            <input type="date" name="date_to" class="form-control form-control-sm" style="width:145px;"
                   value="<?= esc($filters['dateTo'] ?? '') ?>"
                   onchange="this.form.submit()"/>
          </div>

        </div>

        <!-- Status Pills -->
        <div class="d-flex flex-wrap gap-2 mt-3">
          <?php
          $statuses     = ['All', 'Phlebotomist Assigned', 'Arrived', 'Sample Collected', 'Report Ready'];
          $labels       = ['All', 'Phleb. Assigned', 'Arrived', 'Collected', 'Report Ready'];
          $activeStatus = $filters['status'] ?? '';
          foreach ($statuses as $i => $s):
            $isActive = ($s === 'All' && empty($activeStatus)) || ($activeStatus === $s);
          ?>
            <button type="submit" name="status" value="<?= esc($s) ?>"
                    class="btn btn-sm <?= $isActive ? 'btn-primary' : 'btn-outline-secondary' ?>"
                    style="border-radius:20px; font-size:12px; padding:4px 14px;">
              <?= esc($labels[$i]) ?>
            </button>
          <?php endforeach; ?>
        </div>

      </form>
    </div>

    <!-- Bookings Table -->
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr style="background:#1a3a6b; color:#fff;">
                <th class="py-3 px-4">Patient</th>
                <th class="py-3">Phone</th>
                <th class="py-3">Tests</th>
                <th class="py-3">Financials</th>
                <th class="py-3">Status</th>
                <th class="py-3">ETA</th>
                <th class="py-3">Date</th>
                <th class="py-3">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($bookings)): ?>
                <?php
                $statusColors = [
                  'Phlebotomist Assigned' => 'background:#dbeafe; color:#1d4ed8;',
                  'Sample Collected'      => 'background:#fde8cc; color:#c76a15;',
                  'Report Ready'          => 'background:#dcfce7; color:#15803d;',
                  'Arrived'               => 'background:#e0f2fe; color:#0369a1;',
                  'In Process'            => 'background:#fef9c3; color:#854d0e;',
                  'Refused'               => 'background:#fee2e2; color:#dc2626;',
                ];
                foreach ($bookings as $b):
                  $testNames  = implode(', ', array_column($b['tests'], 'test_name'));
                  $testCodes  = implode(', ', array_column($b['tests'], 'test_code'));
                  $reporting  = $b['tests'][0]['reporting_time'] ?? '—';
                  $testCount  = count($b['tests']);
                  $etaTs      = strtotime($b['eta'] ?? '');
                  $etaRed     = $etaTs && $etaTs < time();
                  $chipStyle  = $statusColors[$b['status']] ?? 'background:#f3f4f6; color:#6b7280;';

                $bj = htmlspecialchars(json_encode([
                    'bookingId'     => $b['booking_id'] ?? null,
                    'patient'       => $b['patient_name'] ?? '',
                    'phone'         => $b['phone_number'] ?? '',
                    'age'           => $b['age'] ?? '',
                    'gender'        => $b['gender'] ?? '',
                    'address'       => $b['home_address'] ?? '',
                    'pin'           => $b['pin_location'] ?? '',
                    'instructions'  => $b['instructions'] ?? '',
                    'medical'       => $b['medical_history'] ?? '',
                    'bookingPerson' => $b['booking_person_name'] ?? '',
                    'test'          => $testNames,
                    'testCode'      => $testCodes,
                    'rate'          => $b['total'] ?? 0,
                    'reporting'     => $reporting,
                    'showReporting' => isset($b['show_reporting_time']) ? (int) $b['show_reporting_time'] : 1, // NEW
                    'discount'      => $b['discount_percent'] ?? 0,
                    'phleb'         => $b['phlebotomist_name'] ?? '',
                    'status'        => $b['status'] ?? '',
                    'eta'           => $etaTs ? date('d M Y, h:i A', $etaTs) : '-',
                    'payment'       => $b['payment_method'] ?? '',
                    'paymentStatus' => $b['payment_status'] ?? '',
                    'hasPaymentProof'        => !empty($b['payment_proof_file']) ? 1 : 0,   
                    'paymentReceivedBy'      => $b['payment_received_by'] ?? '',
                    'proofFileUrl'      => !empty($b['payment_proof_file']) ? base_url('franchise/viewPaymentProof/' . $b['booking_id']) : '',
                ]), ENT_QUOTES, 'UTF-8');
                ?>
                <tr style="border-bottom:1px solid #e5e7eb;">
                  <td class="px-4 py-3">
                    <div class="fw-semibold" style="color:#111827;"><?= esc($b['patient_name']) ?></div>
                    <div class="text-muted small"><?= esc($b['gender']) ?></div>
                    <div class="text-muted small"><?= esc($b['home_address']) ?></div>
                  </td>
                  <td class="py-3 small text-muted"><?= esc($b['phone_number']) ?></td>
                  <td class="py-3">
                    <div class="fw-medium" style="font-size:13px;">
                      <?= $testCount ?> test<?= $testCount > 1 ? 's' : '' ?>
                    </div>
                    <div class="text-muted small"
                         style="max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"
                         title="<?= esc($testNames) ?>">
                      <?= esc($testNames ?: '—') ?>
                    </div>
                    <div class="text-muted small"><?= esc($reporting) ?></div>
                  </td>
                  <td class="py-3">
                    <div class="fw-semibold" style="color:#111827;">PKR <?= number_format($b['payable']) ?></div>
                    <?php if ($b['total'] > $b['payable']): ?>
                      <div class="text-muted small" style="text-decoration:line-through;">
                        PKR <?= number_format($b['total']) ?>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td class="py-3">
                    <span class="badge rounded-pill px-3 py-2"
                          style="<?= $chipStyle ?> font-size:12px;">
                      <?= esc($b['status']) ?>
                    </span>
                  </td>
                  <td class="py-3 small" style="color:<?= $etaRed ? '#dc2626' : '#6b7280' ?>;">
                    <?= $etaTs ? date('d-M-y, g:i A', $etaTs) : '—' ?>
                  </td>
                  <td class="py-3 small text-muted">
                    <?= date('d-M-y, g:i A', strtotime($b['date_created'])) ?>
                  </td>
                  <td class="py-3">
                    <button type="button"
                            class="btn btn-sm open-booking-modal me-1"
                            style="background:#eef2f7; color:#1a3a6b; border-radius:8px;"
                            data-booking='<?= $bj ?>'>
                      <i class="ti ti-eye"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center text-muted py-5">
                    <i class="ti ti-clipboard-x d-block mb-2" style="font-size:32px;"></i>
                    No sample collection requests found.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Booking Detail Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow rounded-4">
      <div class="modal-header border-0" style="background:#1a3a6b;">
        <h5 class="modal-title text-white fw-semibold">
          <i class="ti ti-clipboard-list me-2"></i> Booking Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4" style="color:#212529;">

        <h6 class="fw-bold mb-3" style="color:#1a3a6b;">
          <i class="ti ti-user me-1"></i> Patient Information
        </h6>
        <table class="table table-borderless table-sm mb-0">
          <tbody>
            <tr><td class="text-muted py-1" style="width:40%;">Patient Name</td><td class="fw-medium py-1" id="m-patient"></td></tr>
            <tr><td class="text-muted py-1">Booked By</td><td class="py-1" id="m-booking-person"></td></tr>
            <tr><td class="text-muted py-1">Phone</td><td class="py-1" id="m-phone"></td></tr>
            <tr><td class="text-muted py-1">Age</td><td class="py-1" id="m-age"></td></tr>
            <tr><td class="text-muted py-1">Gender</td><td class="py-1" id="m-gender"></td></tr>
            <tr><td class="text-muted py-1">Home Address</td><td class="py-1" id="m-address"></td></tr>
            <tr><td class="text-muted py-1">Pin Location</td><td class="py-1" id="m-pin"></td></tr>
            <tr><td class="text-muted py-1">Instructions</td><td class="py-1" id="m-instructions"></td></tr>
            <tr><td class="text-muted py-1">Medical History / Notes</td><td class="py-1" id="m-medical"></td></tr>
          </tbody>
        </table>

        <hr class="my-3">

        <h6 class="fw-bold mb-3" style="color:#1a3a6b;">
          <i class="ti ti-test-pipe me-1"></i> Test Information
        </h6>
        <table class="table table-borderless table-sm mb-0">
          <tbody>
            <tr><td class="text-muted py-1" style="width:40%;">Test Name</td><td class="fw-medium py-1" id="m-test"></td></tr>
            <tr><td class="text-muted py-1">Test Code</td><td class="py-1" id="m-test-code"></td></tr>
            <tr id="reporting-row">
              <td class="text-muted py-1">Reporting Time</td>
              <td class="py-1" id="m-reporting"></td>
            </tr>
            <tr><td class="text-muted py-1">Original Price</td><td class="py-1" id="m-rate"></td></tr>
            <tr><td class="text-muted py-1">Discount</td><td class="py-1" id="m-discount"></td></tr>
            <tr><td class="text-muted py-1">Patient Pays</td><td class="fw-bold py-1" style="color:#0c7a43;" id="m-final-price"></td></tr>
          </tbody>
        </table>

        <hr class="my-3">

        <h6 class="fw-bold mb-3" style="color:#1a3a6b;">
          <i class="ti ti-calendar me-1"></i> Booking Information
        </h6>
        <table class="table table-borderless table-sm mb-0">
          <tbody>
            <tr><td class="text-muted py-1" style="width:40%;">Phlebotomist</td><td class="py-1" id="m-phleb"></td></tr>
            <tr><td class="text-muted py-1">ETA</td><td class="py-1" id="m-eta"></td></tr>
            <tr><td class="text-muted py-1">Status</td><td class="py-1" id="m-status"></td></tr>
            <tr><td class="text-muted py-1">Payment Method</td><td class="py-1" id="m-payment"></td></tr>
            <tr><td class="text-muted py-1">Payment Status</td><td class="py-1" id="m-payment-status"></td></tr>
          </tbody>
        </table>

        <hr class="my-3">

        <h6 class="fw-bold mb-3" style="color:#1a3a6b;">
          <i class="ti ti-receipt me-1"></i> Proof of Payment
        </h6>

        <!-- Already uploaded -->
        <div id="proofStatusBlock" style="display:none; align-items:center; justify-content:space-between; gap:8px; padding:10px 14px; background:#dcfce7; border-radius:8px;">
          <div style="display:flex; align-items:center; gap:8px;">
            <i class="ti ti-check text-success"></i>
            <span style="color:#15803d; font-weight:600; font-size:.85rem;">
              Proof of payment uploaded — received by <span id="m-proof-receiver"></span>
            </span>
          </div>
          <a id="m-proof-view-link" href="#" target="_blank" style="color:#1d4ed8; font-size:.82rem; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:4px;">
            <i class="ti ti-eye"></i> View
          </a>
        </div>

        <!-- Upload form: only visible when status = Sample Collected / Report Ready and no proof yet -->
        <form id="proofUploadForm" method="post" enctype="multipart/form-data" style="display:none;">
          <?= csrf_field() ?>
          <div class="mb-2">
            <label class="form-label small text-muted mb-1" style="background:none;">Payment Method <span class="text-danger">*</span></label>
            <select name="payment_method" class="form-select form-select-sm" required>
              <option value="cash">Cash</option>
              <option value="online">Online</option>
              <option value="card">Card</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label small text-muted mb-1" style="background:none;">Received By <span class="text-danger">*</span></label>
            <select name="payment_received_by" class="form-select form-select-sm" required>
              <option value="franchise">Franchise</option>
              <option value="main_branch">Main Branch</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label small text-muted mb-1" style="background:none;">Upload Proof (PDF/Image) <span class="text-danger">*</span></label>
            <input type="file" name="proof_file" accept=".pdf,.jpg,.jpeg,.png" class="form-control form-control-sm" required>
          </div>
          <button type="submit" class="btn btn-sm" style="background:#1a3a6b; color:#fff;">
            <i class="ti ti-upload me-1"></i> Upload Proof
          </button>
        </form>

        <!-- Not eligible yet -->
        <div id="proofNotYetBlock" style="display:none; color:#9ca3af; font-size:.82rem;">
          Proof of payment can be uploaded once the sample has been collected.
        </div>

      </div>
    </div>
  </div>
</div>

<script>
let searchTimer;
function debounceSubmit() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    document.getElementById('filterForm').submit();
  }, 500);
}

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.open-booking-modal').forEach(function(btn) {
    btn.addEventListener('click', function() {
      const b = JSON.parse(this.dataset.booking);

      document.getElementById('m-patient').textContent        = b.patient;
      document.getElementById('m-booking-person').textContent = b.bookingPerson;
      document.getElementById('m-phone').textContent          = b.phone;
      document.getElementById('m-age').textContent            = b.age;
      document.getElementById('m-gender').textContent         = b.gender;
      document.getElementById('m-address').textContent        = b.address;
      document.getElementById('m-pin').textContent            = b.pin || '-';
      document.getElementById('m-instructions').textContent   = b.instructions || '-';
      document.getElementById('m-medical').textContent        = b.medical || '-';

      document.getElementById('m-test').textContent           = b.test;
      document.getElementById('m-test-code').textContent      = b.testCode;

      const reportingRow = document.getElementById('reporting-row');
      if (b.showReporting == 1) {
        reportingRow.style.display = '';
        document.getElementById('m-reporting').textContent = b.reporting;
      } else {
        reportingRow.style.display = 'none';
      }

      const rate       = parseFloat(b.rate) || 0;
      const discount   = parseFloat(b.discount) || 0;
      const discAmt    = Math.round(rate * discount / 100);
      const finalPrice = rate - discAmt;

      document.getElementById('m-rate').textContent           = 'Rs. ' + rate.toLocaleString();
      document.getElementById('m-discount').textContent       = discount + '% (Rs. ' + discAmt.toLocaleString() + ')';
      document.getElementById('m-final-price').textContent    = 'Rs. ' + finalPrice.toLocaleString();

      document.getElementById('m-phleb').textContent          = b.phleb;
      document.getElementById('m-eta').textContent            = b.eta;
      document.getElementById('m-status').textContent         = b.status;
      document.getElementById('m-payment').textContent        = b.payment;
      document.getElementById('m-payment-status').textContent = b.paymentStatus;

      const proofStatusBlock = document.getElementById('proofStatusBlock');
      const proofUploadForm  = document.getElementById('proofUploadForm');
      const proofNotYetBlock = document.getElementById('proofNotYetBlock');

      proofStatusBlock.style.display = 'none';
      proofUploadForm.style.display  = 'none';
      proofNotYetBlock.style.display = 'none';

      const eligibleForProof = ['Sample Collected', 'Report Ready'].includes(b.status);

      if (b.hasPaymentProof == 1) {
        // Already uploaded
        proofStatusBlock.style.display = 'flex';
        document.getElementById('m-proof-receiver').textContent =
          b.paymentReceivedBy === 'main_branch' ? 'Main Branch' : 'Franchise';
          document.getElementById('m-proof-view-link').href = b.proofFileUrl;
      } else if (eligibleForProof && b.bookingId) {
        proofUploadForm.style.display = 'block';
        proofUploadForm.action = '<?= base_url('franchise/uploadPaymentProof/') ?>' + b.bookingId;
      } else {
        proofNotYetBlock.style.display = 'block';
      }

      const modal = new bootstrap.Modal(document.getElementById('bookingModal'));
      modal.show();
    });
  });
});
</script>

<?= view('templates/footer') ?>