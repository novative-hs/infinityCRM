<?= view('templates/header', ['pageTitle' => 'Lab List', 'activePage' => 'lablist']) ?>

<div class="page-wrap p-4">

  <!-- Page Header -->
  <div class="page-header mb-4">

    <a href="<?= base_url('booking/new') ?>" class="btn-new-booking text-end">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <line x1="12" y1="5" x2="12" y2="19" />
        <line x1="5" y1="12" x2="19" y2="12" />
      </svg>
      New Booking
    </a>
  </div>

  <!-- Stat Cards -->
  <div class="stats-row">
    <div class="stat-card total">
      <div class="stat-val c-total"><?= $counts['total'] ?></div>
      <div class="stat-label">Total</div>
    </div>
    <div class="stat-card process">
      <div class="stat-val c-orange"><?= $counts['in_process'] ?></div>
      <div class="stat-label">In Process</div>
    </div>
    <div class="stat-card assigned">
      <div class="stat-val c-blue"><?= $counts['assigned'] ?></div>
      <div class="stat-label">Assigned</div>
    </div>
    <div class="stat-card arrived">
      <div class="stat-val c-purple"><?= $counts['arrived'] ?></div>
      <div class="stat-label">Arrived</div>
    </div>
    <div class="stat-card collected">
      <div class="stat-val c-amber"><?= $counts['collected'] ?></div>
      <div class="stat-label">Collected</div>
    </div>
    <div class="stat-card report">
      <div class="stat-val c-green"><?= $counts['report_ready'] ?></div>
      <div class="stat-label">Report Ready</div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="tab-row">
    <a href="#" class="tab-btn active" onclick="switchTab(event,'individual')">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
        <circle cx="12" cy="7" r="4" />
      </svg>
      Individual Leads
      <span class="tab-badge" id="tab-count-individual"><?= $counts['total'] ?></span>
    </a>

  </div>

  <!-- Filter Bar — inputs now submit via GET -->
  <div class="filter-bar">
    <form method="GET" action="" id="filterForm">
      <div class="filter-inner">
        <div class="date-group">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" />
            <line x1="16" y1="2" x2="16" y2="6" />
            <line x1="8" y1="2" x2="8" y2="6" />
            <line x1="3" y1="10" x2="21" y2="10" />
          </svg>
          <input type="date" name="date_from" class="date-input" id="dateFrom"
            value="<?= esc($filters['date_from'] ?? '') ?>"
            onchange="this.form.submit()" />
          <span class="date-sep">to</span>
          <input type="date" name="date_to" class="date-input" id="dateTo"
            value="<?= esc($filters['date_to'] ?? '') ?>"
            onchange="this.form.submit()" />
        </div>
 <div class="franchise-group">
        <select name="franchise_id" class="form-select" id="franchiseFilter" onchange="this.form.submit()">
          <option value="">All Franchises</option>
          <?php foreach ($franchises ?? [] as $f): ?>
            <option value="<?= $f['id'] ?>" <?= ($filters['franchise_id'] ?? '') == $f['id'] ? 'selected' : '' ?>>
              <?= esc($f['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
       <div class="search-wrap" style="position:relative; display:flex; align-items:center;">
  <span class="search-icon" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); display:flex; align-items:center; color:#9ca3af; pointer-events:none;">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="11" cy="11" r="8" />
      <line x1="21" y1="21" x2="16.65" y2="16.65" />
    </svg>
  </span>
  <input type="text" name="search" class="search-input" id="searchInput"
    placeholder="Search patient, phone…"
    value="<?= esc($filters['search'] ?? '') ?>"
    oninput="debounceSubmit()"
    style="padding-left:34px;" />
</div>

        <div class="pills-group">
          <?php
          $statuses = ['All', 'In Process', 'Phlebotomist Assigned', 'Arrived', 'Sample Collected', 'Report Ready', 'Refused'];
          $labels   = ['All', 'In Process', 'Phleb. Assigned', 'Arrived', 'Collected', 'Report Ready', 'Refused'];
          $activeStatus = $filters['status'] ?? 'All';
          foreach ($statuses as $i => $s):
            $isActive = ($activeStatus === $s || ($s === 'All' && empty($activeStatus)));
          ?>
            <button type="submit" name="status" value="<?= esc($s) ?>"
              class="pill <?= $isActive ? 'active' : '' ?>">
              <?= esc($labels[$i]) ?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    </form>
  </div>

  <!-- Table -->
  <div class="table-wrap">
    <table id="bookingsTable">
      <thead>
        <tr>
          <th>Patient</th>
          <th>Phone</th>
          <th>Tests</th>
          <th>Financials</th>
          <th>Status</th>
          <th>ETA</th>
          <th>Date</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($bookings)): ?>
          <tr>
            <td colspan="8" class="empty-state">
              <p>No bookings match your filters.</p>
            </td>
          </tr>
        <?php else: ?>
          <?php
          $statusClassMap = [
            'Phlebotomist Assigned' => 'phleb',
            'Sample Collected'      => 'collected',
            'Report Ready'          => 'report',
            'Arrived'               => 'arrived',
            'In Process'            => 'in-process',
            'Refused'               => 'refused',
          ];
          foreach ($bookings as $b):
            $sc        = $statusClassMap[$b['status']] ?? 'in-process';
            $total     = $b['total'] ?? 0;
            $payable   = $b['payable'] ?? 0;
            $testCount = $b['test_count'] ?? 0;
            $tests     = $b['tests'] ?? [];
            $testNames = implode(', ', array_column($tests, 'test_name'));
            $etaTs     = strtotime($b['eta'] ?? '');
            $etaRed    = $etaTs && $etaTs < time();
            $etaLabel  = $etaTs ? date('d-M-y, g:i A', $etaTs) : '—';
            $dateLabel = date('d-M-y, g:i A', strtotime($b['date_created']));
          ?>
            <tr>
              <td>
                <div class="patient-name"><?= esc($b['patient_name']) ?></div>
                <div class="patient-meta">
                  <?= esc($b['gender']) ?>
                </div>
                <div class="patient-meta"><?= esc($b['home_address']) ?></div>
              </td>
              <td style="padding-top:20px;">
                <span class="patient-meta"><?= esc($b['phone_number']) ?></span>
              </td>
              <td>
                <div class="test-count"><?= $testCount ?> test<?= $testCount !== 1 ? 's' : '' ?></div>
                <div class="test-desc" title="<?= esc($testNames) ?>"><?= esc($testNames ?: '—') ?></div>
                <?php $reportingTime = !empty($tests) ? $tests[0]['reporting_time'] ?? '' : ''; ?>
                <div class="test-desc" title="<?= esc($reportingTime) ?>"><?= esc($reportingTime ?: '—') ?></div>

              </td>
              <td>
                <div class="price-main">PKR <?= number_format($payable) ?></div>
                <?php if ($total > $payable): ?>
                  <div class="price-strike">PKR <?= number_format($total) ?></div>
                <?php endif; ?>
              </td>
              <td>
                <span class="status-badge <?= $sc ?>"><?= esc($b['status']) ?></span>
              </td>
              <td>
                <div class="eta-val" style="color:<?= $etaRed ? '#dc2626' : '#6b7280' ?>">
                  <?= esc($etaLabel) ?>
                </div>
              </td>
              <td>
                <div class="date-val"><?= esc($dateLabel) ?></div>
              </td>
              <td>
               
  <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-start;">
    <a href="<?= base_url('booking/view/' . $b['fk_patient_id']) ?>" class="btn-view">
      View &amp; Update
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="5" y1="12" x2="19" y2="12" />
        <polyline points="12 5 19 12 12 19" />
      </svg>
    </a>

    <?php if ($b['status'] === 'Phlebotomist Assigned'): ?>
      <button type="button" class="btn-view" style="background:#0891b2; border-color:#0891b2; color:#fff; border-radius:10px;"
    onclick="openLiveLocationPopup(<?= (int) ($b['id'] ?? $b['booking_id'] ?? 0) ?>)">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z" />
      <circle cx="12" cy="10" r="3" />
    </svg>
    Enter Live Location
  </button>
    <?php endif; ?>
  </div>

              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
       <dialog id="liveLocationDialog" style="padding:0; border:none; border-radius:16px; width:480px; max-width:95%; height:760px; max-height:95vh; overflow:hidden;">
  <button type="button" onclick="document.getElementById('liveLocationDialog').close()"
    style="position:absolute; top:8px; right:12px; border:none; background:#134557; color:#fff; width:28px; height:28px; border-radius:50%; font-size:16px; cursor:pointer; z-index:2;">&times;</button>
  <iframe id="liveLocationFrame" src="" style="width:100%; height:100%; border:none;"></iframe>
</dialog>
      </tbody>
    </table>
  </div>

  <script>
    // Debounce search so it doesn't fire on every keystroke
    let searchTimer;

    function debounceSubmit() {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(() => {
        document.getElementById('filterForm').submit();
      }, 500);
    }

    // Set today's date in header
    const now = new Date();
    document.getElementById('todayDate').textContent = now.toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });

    function switchTab(e, tab) {
      e.preventDefault();
      document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
      e.currentTarget.classList.add('active');
    }

    //live location popup
// function openLiveLocationPopup(bookingId) {
//   if (!bookingId) return;
//   const url = `<?= base_url('phlebotomist.html') ?>?booking_id=${bookingId}`;
//   const w = 480, h = 760;
//   const left = (window.screen.width  - w) / 2;
//   const top  = (window.screen.height - h) / 2;
//   window.open(
//     url,
//     'liveLocationPopup',
//     `width=${w},height=${h},left=${left},top=${top},resizable=yes,scrollbars=yes`
//   );
// }
function openLiveLocationPopup(bookingId) {
  if (!bookingId) return;
  const url = `<?= base_url('phlebotomist.html') ?>?booking_id=${bookingId}`;
  document.getElementById('liveLocationFrame').src = url;
  document.getElementById('liveLocationDialog').showModal();
}
const urlParams = new URLSearchParams(window.location.search);
const prefillBookingId = urlParams.get('booking_id');
// if (prefillBookingId) {
//   bookingInput.value = prefillBookingId;
// }
  </script>
  <?= view('templates/footer') ?>