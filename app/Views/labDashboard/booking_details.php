<?= view('templates/header', ['pageTitle' => 'Booking Details', 'activePage' => 'lablist']) ?>


<div class="detail-wrap">

  <!-- Back bar -->
  <div class="back-bar">
    <div>
      <a href="<?= base_url('labDashboard/dashboard') ?>" class="back-btn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="19" y1="12" x2="5" y2="12" />
          <polyline points="12 19 5 12 12 5" />
        </svg>
        Back
      </a>
      <div class="page-title">Booking Details</div>
      <div class="booking-ref">Patient #<?= esc($patient['id']) ?></div>
    </div>
    <?php
    $chipClass = match ($currentStatus) {
      'In Process'            => 'chip-in-process',
      'Phlebotomist Assigned' => 'chip-phleb',
      'Arrived'               => 'chip-arrived',
      'Sample Collected'      => 'chip-collected',
      'Report Ready'          => 'chip-report',
      'Patient Refused'       => 'chip-refused',
      default                 => 'chip-in-process',
    };
    ?>
    <span class="status-chip <?= $chipClass ?>"><?= esc($currentStatus) ?></span>
  </div>

  <!-- Status Progress -->
  <div class="d-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
      <span class="d-card-title" style="margin-bottom:0;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
        </svg>
        Status Progress
      </span>
      <?php if (!empty($latestBooking['eta'])): ?>
        <div class="eta-chip">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10" />
            <polyline points="12 6 12 12 16 14" />
          </svg>
          ETA &nbsp;|&nbsp; <?= date('d-M-y, g:i A', strtotime($latestBooking['eta'])) ?>

        </div>
      <?php endif; ?>
    </div>

    <div class="progress-bar-wrap">
      <?php foreach ($statusSteps as $i => $step): ?>
        <div class="step-dot <?= $i <= $currentStepIdx ? 'done' : '' ?>">
          <svg width="13" height="13" viewBox="0 0 12 12" fill="none">
            <path d="M2 6l3 3 5-5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </div>
        <?php if ($i < count($statusSteps) - 1): ?>
          <div class="step-connector <?= $i < $currentStepIdx ? 'done' : '' ?>"></div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <div class="steps-labels">
      <?php foreach ($statusSteps as $i => $step): ?>
        <div class="step-lbl <?= $i === $currentStepIdx ? 'active' : ($i < $currentStepIdx ? 'done-lbl' : '') ?>">
          <?= esc($step) ?>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Action Buttons -->
    <?php if ($currentStatus === 'In Process'): ?>
      <button class="action-btn blue" id="assignBtn" onclick="document.getElementById('assignForm').style.display='block'; this.style.display='none'; loadPhlebSchedule();">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
          <circle cx="12" cy="7" r="4" />
        </svg>
        Assign Phlebotomist
      </button>

      <div id="assignForm" style="display:none; margin-top:16px; background:#f0f7ff; border:1px solid #bfdbfe; border-radius:12px; padding:20px;">
        <div style="font-size:.88rem; font-weight:700; color:#1e40af; margin-bottom:14px;">Phlebotomist Details</div>
        <!-- phelobotomist graph -->
        <div id="phlebScheduleCard" style="margin-bottom:14px; background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:14px 16px; display:none;">
  <div style="font-size:.78rem; font-weight:700; color:#374151; margin-bottom:2px; display:flex; align-items:center; gap:6px;">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
      <line x1="16" y1="2" x2="16" y2="6" />
      <line x1="8" y1="2" x2="8" y2="6" />
      <line x1="3" y1="10" x2="21" y2="10" />
    </svg>
    Phlebotomist Schedule
  </div>
  <div style="font-size:.7rem; color:#9ca3af; margin-bottom:10px;">
    Shows existing assigned/arrived visits — use this to avoid double-booking a busy phlebotomist.
  </div>
  <div id="phlebScheduleChart"></div>
  <div id="phlebScheduleEmpty" style="font-size:.78rem; color:#9ca3af; display:none;">
    No upcoming assignments for this franchise's phlebotomists.
  </div>
  <div id="phlebScheduleLoading" style="font-size:.78rem; color:#9ca3af;">Loading schedule…</div>
</div>

        <form action="<?= base_url('booking/assignPhlebotomist/' . $bookingId) ?>" method="post">
          <?= csrf_field() ?>
          <!-- Phlebotomist -->
<div style="margin-bottom:14px;">
    <label style="font-size:.75rem; font-weight:600; color:#374151; display:block; margin-bottom:4px;">
        Phlebotomist Name <span style="color:red">*</span>
    </label>

    <select name="phleb_id" id="phleb_id" required
        style="width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:.85rem; color:#111827; background:#fff;">
        <option value="" selected disabled>Select phlebotomist</option>

        <?php foreach ($phlebotomists as $p): ?>
            <option value="<?= esc($p['id']) ?>">
                <?= esc($p['name']) ?>
            </option>
        <?php endforeach; ?>

        <?php if (empty($phlebotomists)): ?>
            <option value="" disabled>No phlebotomists available for this franchise</option>
        <?php endif; ?>
    </select>
</div>

<!-- ETA Date & Time -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">

    <div>
        <label style="font-size:.75rem; font-weight:600; color:#374151; display:block; margin-bottom:4px;">
            ETA Date <span style="color:red">*</span>
        </label>

        <input type="date"
               id="eta_date"
               required
               style="width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:.85rem; color:#111827;">
    </div>

    <div>
        <label style="font-size:.75rem; font-weight:600; color:#374151; display:block; margin-bottom:4px;">
            ETA Time <span style="color:red">*</span>
        </label>

        <select id="eta_time"
                required
                style="width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:.85rem; color:#111827; background:#fff;">
        </select>
    </div>

</div>

<!-- Patient Preferred Date & Time -->
<div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">

    <div>
        <label style="font-size:.75rem; font-weight:600; color:#374151; display:block; margin-bottom:4px;">
            Patient Preferred Date
        </label>

        <input type="date"
               id="preferred_eta_date"
               style="width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:.85rem; color:#111827;">
    </div>

    <div>
        <label style="font-size:.75rem; font-weight:600; color:#374151; display:block; margin-bottom:4px;">
            Patient Preferred Time
        </label>

        <select id="preferred_eta_time"
                style="width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:.85rem; color:#111827; background:#fff;">
        </select>
    </div>

</div>
<!-- Reporting Time Visibility -->
<div style="margin-bottom:14px;">
    <label style="font-size:.75rem; font-weight:600; color:#374151; display:block; margin-bottom:8px;">
        Reporting Time Visibility <span style="color:red">*</span>
    </label>
    <div style="display:flex; gap:16px;">
        <label style="display:flex; align-items:center; gap:6px; font-size:.85rem; color:#111827; cursor:pointer;">
            <input type="radio" name="show_reporting_time" value="1" checked>
            Show Reporting Time
        </label>
        <label style="display:flex; align-items:center; gap:6px; font-size:.85rem; color:#111827; cursor:pointer;">
            <input type="radio" name="show_reporting_time" value="0">
            Do Not Show Reporting Time
        </label>
    </div>
</div>
  <!-- Hidden combined fields that actually get submitted -->
<input type="hidden" name="eta" id="eta_hidden">
<input type="hidden" name="preferred_eta" id="preferred_eta">
          <div style="display:flex; gap:10px;">
            <button type="submit" class="action-btn blue" style="margin-top:0;">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
              </svg>

              
              Confirm Assignment
            </button>
            <button type="button" class="action-btn" style="margin-top:0; background:#e5e7eb; color:#374151;"
              onclick="document.getElementById('assignForm').style.display='none'; document.getElementById('assignBtn').style.display='inline-flex';">
              Cancel
            </button>
          </div>
        </form>
      </div>
    <?php endif; ?>
    <?php if ($currentStatus === 'Phlebotomist Assigned'): ?>
      <a href="<?= base_url('booking/status/' . $latestBooking['id'] . '/arrived') ?>" class="action-btn blue">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
          <polyline points="22 4 12 14.01 9 11.01" />
        </svg>
        Mark Phlebotomist Arrived
      </a>
   <?php elseif ($currentStatus === 'Arrived'): ?>
  <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:16px;">
    
    <a href="<?= base_url('booking/status/' . $latestBooking['id'] . '/collected') ?>" class="action-btn green">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
      </svg>
      Sample Collected
    </a>

    <a href="<?= base_url('booking/status/' . $latestBooking['id'] . '/refused') ?>" 
       class="action-btn" style="background:#ef4444; color:#fff;"
       onclick="return confirm('Mark patient as Refused?')">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
      </svg>
      Patient Refused
    </a>

    <button class="action-btn" style="background:#8b5cf6; color:#fff;" 
            onclick="document.getElementById('revisitForm').style.display='block'; this.style.display='none';">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/>
      </svg>
      Request Re-visit
    </button>

  </div>

  <!-- Re-visit form (hidden by default) -->
  <div id="revisitForm" style="display:none; margin-top:16px; background:#f5f3ff; border:1px solid #ddd6fe; border-radius:12px; padding:20px;">
    <div style="font-size:.88rem; font-weight:700; color:#5b21b6; margin-bottom:14px;">Re-visit Request Details</div>
    <form action="<?= base_url('booking/requestRevisit/' . $latestBooking['id']) ?>" method="post">
      <?= csrf_field() ?>
<div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; margin-bottom:14px;">        <!-- NEW -->
<div>
  <label style="font-size:.75rem; font-weight:600; color:#374151; display:block; margin-bottom:4px;">
    Preferred Date <span style="color:red">*</span>
  </label>
  <input type="date" id="revisit_date" required
    style="width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:.85rem; color:#111827;">
</div>
<div>
  <label style="font-size:.75rem; font-weight:600; color:#374151; display:block; margin-bottom:4px;">
    Preferred Time <span style="color:red">*</span>
  </label>
  <select id="revisit_time" required
    style="width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:.85rem; color:#111827; background:#fff;">
  </select>
</div>
<input type="hidden" name="revisit_datetime" id="revisit_datetime_hidden">
        <div>
          <label style="font-size:.75rem; font-weight:600; color:#374151; display:block; margin-bottom:4px;">
            Assign Phlebotomist (optional)
          </label>
          <select name="phleb_id" style="width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:.85rem; color:#111827; background:#fff;">
            <option value="">— Keep same —</option>
            <?php foreach ($phlebotomists as $p): ?>
              <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div style="margin-bottom:14px;">
        <label style="font-size:.75rem; font-weight:600; color:#374151; display:block; margin-bottom:4px;">Reason / Notes</label>
        <textarea name="revisit_notes" rows="2"
          placeholder="e.g. Patient was unavailable, requested morning slot..."
          style="width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:.85rem; color:#111827; resize:vertical;"></textarea>
      </div>
      <div style="display:flex; gap:10px;">
        <button type="submit" class="action-btn" style="background:#7c3aed; color:#fff; margin-top:0;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/>
          </svg>
          Confirm Re-visit
        </button>
        <button type="button" class="action-btn" style="margin-top:0; background:#e5e7eb; color:#374151;"
          onclick="document.getElementById('revisitForm').style.display='none'; document.querySelector('.action-btn[onclick*=revisitForm]').style.display='inline-flex';">
          Cancel
        </button>
      </div>
    </form>
  </div>
    <?php elseif ($currentStatus === 'Sample Collected'): ?>
      <div style="margin-top:16px; font-size:.85rem; color:#0369a1; background:#e0f2fe; padding:12px 16px; border-radius:8px; display:flex; align-items:center; gap:8px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10" />
          <line x1="12" y1="16" x2="12" y2="12" />
          <line x1="12" y1="8" x2="12.01" y2="8" />
        </svg>
        Upload lab reports to complete the booking
      </div>
    <?php endif; ?>
  </div>

  <!-- Proof of Payment - relevant from Sample Collected onward -->
  <?php if (in_array($currentStatus, ['Sample Collected', 'Report Ready'])): ?>
    <div class="d-card" style="<?= empty($latestBooking['payment_proof_file']) ? 'border:2px dashed #f59e0b; background:#fffbeb;' : '' ?>">
      <div class="d-card-title" style="<?= empty($latestBooking['payment_proof_file']) ? 'color:#92400e;' : '' ?>">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
          <polyline points="14 2 14 8 20 8" />
          <line x1="16" y1="13" x2="8" y2="13" />
          <line x1="16" y1="17" x2="8" y2="17" />
        </svg>
        Proof of Payment
      </div>

      <?php if (!empty($latestBooking['payment_proof_file'])): ?>
        <div style="display:flex; justify-content:space-between; align-items:center; background:#dcfce7; border-radius:8px; padding:12px 16px;">
          <div style="display:flex; align-items:center; gap:8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
              <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
            <span style="color:#15803d; font-weight:600; font-size:.85rem;">
              Proof uploaded — received by
              <?= ($latestBooking['payment_received_by'] ?? '') === 'main_branch' ? 'Main Branch' : 'Franchise' ?>
            </span>
          </div>
          <a href="<?= base_url('booking/viewPaymentProof/' . $bookingId) ?>" target="_blank"
            style="color:#1d4ed8; font-size:.82rem; font-weight:600; text-decoration:none; display:flex; align-items:center; gap:4px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
              <circle cx="12" cy="12" r="3" />
            </svg>
            View
          </a>
        </div>
      <?php else: ?>
        <p style="color:#92400e; font-size:.85rem; margin-bottom:14px;">
          Upload proof of payment, select the payment method, then confirm below — this will upload the proof
          <strong>and</strong> mark all tests for this booking as paid in one step.
        </p>
        <form action="<?= base_url('booking/uploadProofAndMarkPaid/' . $bookingId) ?>" method="post"
          enctype="multipart/form-data"
          onsubmit="return confirm('Upload proof and mark all tests as PAID?');">
          <?= csrf_field() ?>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
            <div>
              <label style="font-size:.75rem; font-weight:600; color:#374151; display:block; margin-bottom:4px;">
                Payment Method <span style="color:red">*</span>
              </label>
              <select name="payment_method" required
                style="width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:.85rem; color:#111827; background:#fff;">
                <option value="cash">Cash</option>
                <option value="online">Online</option>
                <option value="card">Card</option>
              </select>
            </div>
            <div>
              <label style="font-size:.75rem; font-weight:600; color:#374151; display:block; margin-bottom:4px;">
                Received By <span style="color:red">*</span>
              </label>
              <select name="payment_received_by" required
                style="width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:.85rem; color:#111827; background:#fff;">
                <option value="main_branch">Main Branch</option>
                <option value="franchise">Franchise</option>
              </select>
            </div>
          </div>
          <div style="margin-bottom:14px;">
            <label style="font-size:.75rem; font-weight:600; color:#374151; display:block; margin-bottom:4px;">
              Upload Proof (PDF/Image) <span style="color:red">*</span>
            </label>
            <input type="file" id="proof_file_input" name="proof_file" accept=".pdf,.jpg,.jpeg,.png" required
              style="width:100%; padding:8px; border:1px solid #d1d5db; border-radius:6px; font-size:.85rem; color:#111827;">
          </div>
          <button type="submit" id="uploadMarkPaidBtn" class="action-btn" style="background:#16a34a; color:#fff; margin-top:0;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
              <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
            Upload &amp; Mark Payment Collected
          </button>
        </form>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Upload Lab Reports - Only show when status is Sample Collected -->
  <?php
  if ($currentStatus === 'Sample Collected'):
    $bookingId = $bookingId ?? $latestBooking['id'] ?? 0;

    if (empty($latestBooking['payment_proof_file'])):
  ?>
      <div class="d-card" style="border:2px dashed #d1d5db; background:#f9fafb;">
        <div class="d-card-title" style="color:#6b7280;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
            <polyline points="14 2 14 8 20 8" />
          </svg>
          Upload Lab Reports
        </div>
        <p style="color:#6b7280; font-size:.85rem; margin:0;">
          Upload proof of payment first — the report upload will unlock once payment proof is attached.
        </p>
      </div>
    <?php else: ?>
      <div class="d-card upload-card">
        <div class="d-card-title">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
            <polyline points="14 2 14 8 20 8" />
            <line x1="12" y1="18" x2="12" y2="12" />
            <line x1="9" y1="15" x2="15" y2="15" />
          </svg>
          Upload Lab Reports
        </div>

        <p style="color:#4b5563; font-size:.85rem; margin-bottom:16px;">
          Check the tests covered by your report, then upload one PDF. One PDF can cover multiple tests.
        </p>

        <form action="<?= base_url('booking/uploadReport/' . $bookingId) ?>" method="post" enctype="multipart/form-data" id="uploadReportForm">
          <?= csrf_field() ?>

          <div style="margin-bottom:16px;">
            <div style="font-size:.78rem; font-weight:600; color:#374151; margin-bottom:8px;">Select Tests for this Report:</div>
            <div class="test-checkbox-grid">
              <?php
              $uploadedCount = 0;
              $totalTests = count($testsOrdered);
              foreach ($testsOrdered as $test):
                $hasReport = isset($test['has_report']) && $test['has_report'];
                if ($hasReport) $uploadedCount++;
              ?>
                <label class="test-checkbox-item <?= $hasReport ? 'uploaded' : '' ?>">
                  <input type="checkbox" name="test_ids[]" value="<?= $test['booking']['fk_test_id'] ?>"
                    <?= $hasReport ? 'disabled checked' : '' ?>>
                  <span class="check-label">
                    <?= esc($test['test']['test_name']) ?> - <?= esc($test['test']['test_code']) ?>
                  </span>
                  <?php if ($hasReport): ?>
                    <span class="uploaded-badge">✓ Uploaded</span>
                  <?php endif; ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div style="margin-bottom:16px;">
            <label style="display:block; font-size:.78rem; font-weight:600; color:#374151; margin-bottom:4px;">Upload PDF Report</label>
            <input type="file" name="report_file" accept=".pdf" required class="file-upload-input" id="reportFile">
            <div class="file-upload-hint">PDF only, max 10MB. Booking auto-completes when all tests are reported.</div>
            <div id="fileError" style="color:#dc2626; font-size:.75rem; margin-top:4px; display:none;"></div>
          </div>

          <button type="submit" class="action-btn blue" id="uploadBtn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
              <polyline points="17 8 12 3 7 8" />
              <line x1="12" y1="3" x2="12" y2="15" />
            </svg>
            Upload Report
          </button>
        </form>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <!-- Patient Info -->
  <div class="d-card">
    <div class="d-card-title">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
        <circle cx="12" cy="7" r="4" />
      </svg>
      Patient Information
    </div>

    <div class="info-row">
      <svg class="info-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
        <circle cx="12" cy="7" r="4" />
      </svg>
      <div>
        <div class="info-label">Name</div>
        <div class="info-val"><?= esc($patient['patient_name']) ?></div>
      </div>
    </div>

    <div class="info-row">
      <svg class="info-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.7A2 2 0 012 .18h3a2 2 0 012 1.72c.13 1 .36 1.97.71 2.91a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1-1.07a2 2 0 012.11-.45c.94.35 1.91.58 2.91.71A2 2 0 0122 14.92z" />
      </svg>
      <div>
        <div class="info-label">Phone</div>
        <div class="info-val"><?= esc($patient['phone_number']) ?></div>
      </div>
    </div>

    <div class="info-row">
      <svg class="info-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z" />
        <circle cx="12" cy="10" r="3" />
      </svg>
      <div>
        <div class="info-label">Address</div>
        <div class="info-val"><?= esc($patient['home_address']) ?></div>
      </div>
    </div>

    <div class="info-row">
      <svg class="info-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10" />
        <line x1="12" y1="8" x2="12" y2="12" />
        <line x1="12" y1="16" x2="12.01" y2="16" />
      </svg>
      <div>
        <div class="info-label">Age / Gender</div>
        <div class="info-val">
          <?php
          $parts = [];
          if (!empty($patient['age']))    $parts[] = $patient['age'] . ' yrs';
          if (!empty($patient['gender'])) $parts[] = $patient['gender'];
          echo esc(implode(' / ', $parts) ?: '—');
          ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Instructions & Location -->
  <?php if (!empty($patient['pin_location']) || !empty($patient['instructions'])): ?>
    <div class="inst-card">
      <div class="inst-title">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2">
          <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z" />
          <circle cx="12" cy="10" r="3" />
        </svg>
        Instructions &amp; Location
      </div>
      <?php
  $pin = trim($patient['pin_location']);
  if (preg_match('#^https?://#i', $pin)) {
      // Already a full URL (e.g. saved from Google Maps "share link")
      $mapUrl = $pin;
  } else {
      // Plain address text — build a Google Maps search URL
      $mapUrl = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($pin);
  }
?>
<a href="<?= esc($mapUrl) ?>" target="_blank" rel="noopener" class="pin-link">
  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
    <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" />
    <polyline points="15 3 21 3 21 9" />
    <line x1="10" y1="14" x2="21" y2="3" />
  </svg>
  View on Map
</a>
      
      <div style="margin-top:10px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
          <div class="notes-label">NOTES / INSTRUCTIONS</div>
          <a href="#" class="edit-link" id="editNotesBtn" onclick="document.getElementById('notesDisplay').style.display='none'; document.getElementById('notesForm').style.display='block'; this.style.display='none'; return false;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
              <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
            </svg>
            Edit
          </a>
        </div>
        <div id="notesDisplay">
          <div class="notes-val"><?= esc($patient['instructions']) ?></div>
        </div>
        <div id="notesForm" style="display:none;">
          <form action="<?= base_url('booking/saveNotes/' . $patient['id']) ?>" method="post">
            <?= csrf_field() ?>
            <textarea name="instructions" style="width:100%; padding:10px; border:1px solid #fde68a; border-radius:8px; font-size:.88rem; color:#374151; background:#fff; min-height:80px; resize:vertical;"><?= esc($patient['instructions']) ?></textarea>
            <div style="display:flex; gap:8px; margin-top:8px;">
              <button type="submit" style="background:#d97706; color:#fff; border:none; padding:8px 18px; border-radius:8px; font-size:.82rem; font-weight:600; cursor:pointer;">Save Notes</button>
              <button type="button" style="background:#e5e7eb; color:#374151; border:none; padding:8px 14px; border-radius:8px; font-size:.82rem; cursor:pointer;"
                onclick="document.getElementById('notesForm').style.display='none'; document.getElementById('notesDisplay').style.display='block'; document.getElementById('editNotesBtn').style.display='flex';">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Phlebotomist (show only if assigned) -->
  <?php if (!empty($latestBooking['phleb_id'])): ?>
    <div class="phleb-card">
      <div class="phleb-title">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#1e40af" stroke-width="2">
          <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
          <circle cx="12" cy="7" r="4" />
        </svg>
        Phlebotomist
      </div>
      <div class="phleb-grid">
        <div>
          <div class="phleb-label">Name</div>
          <div class="phleb-val"><?= esc($latestBooking['phleb_name'] ?? '—') ?></div>
        </div>
        <?php if (!empty($latestBooking['eta'])): ?>
          <div>
            <div class="phleb-label">ETA</div>
            <div class="phleb-val blue"><?= date('d-M-y, g:i A', strtotime($latestBooking['eta'])) ?></div>

          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Tests Ordered -->
  <div class="d-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
      <span class="d-card-title" style="margin-bottom:0;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2v-4M9 21H5a2 2 0 01-2-2v-4m0 0h18" />
        </svg>
        Tests Ordered
      </span>
      <a href="<?= base_url('booking/editTests/' . $patient['id']) ?>" class="view-invoice">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19" />
          <line x1="5" y1="12" x2="19" y2="12" />
        </svg>
        Edit Tests
      </a>
    </div>

    <table class="tests-table">
      <thead>
        <tr>
          <th>Code</th>
          <th>Test Name</th>
          <th>Reporting Time</th>
          <th>Patient Price</th>
          <th>Payment</th>
          <?php if ($currentStatus === 'Sample Collected'): ?>
            <th>Report</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($testsOrdered as $item): ?>
          <tr>
            <td><?= esc($item['test']['test_code'] ?? '—') ?></td>
            <td><?= esc($item['test']['test_name']) ?></td>
            <td><?= esc($item['test']['reporting_time'] ?? '—') ?></td>
            <td>
              <div class="price-txt">PKR <?= number_format($item['patient_price']) ?></div>
              <?php if ($item['discount_amt'] > 0): ?>
                <div class="save-txt">
                  save <?= $item['booking']['discount_percent'] ?>%
                  (PKR <?= number_format($item['discount_amt']) ?>)
                </div>
              <?php endif; ?>
            </td>
            <td>
              <?php if (strtolower($item['booking']['payment_status'] ?? '') === 'paid'): ?>
                <span style="color:#16a34a; font-size:.8rem; display:flex; align-items:center; gap:3px;">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12" />
                  </svg>
                  Paid
                </span>
              <?php else: ?>
                <?= ucfirst(esc($item['booking']['payment_method'])) ?>
              <?php endif; ?>
            </td>
            <?php if ($currentStatus === 'Sample Collected'): ?>
              <td>
                <?php if (isset($item['has_report']) && $item['has_report']): ?>
                  <span style="color:#16a34a; font-size:.75rem; display:flex; align-items:center; gap:4px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                      <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                    Uploaded
                  </span>
                <?php else: ?>
                  <span style="color:#9ca3af; font-size:.75rem;">Pending</span>
                <?php endif; ?>
              </td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Financial Breakdown -->
  <div class="d-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
      <span class="d-card-title" style="margin-bottom:0;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="1" x2="12" y2="23" />
          <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
        </svg>
        Financial Breakdown
      </span>
      <a href="<?= base_url('booking/invoice/' . $latestBooking['id']) ?>" class="view-invoice" target="_blank">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
          <polyline points="14 2 14 8 20 8" />
        </svg>
        View Invoice
      </a>
    </div>

    <div class="fin-row">
      <span>Original Total (Rack Rate)</span>
      <span>PKR <?= number_format($originalTotal) ?></span>
    </div>
    <?php if ($discountTotal > 0): ?>
  <?php foreach ($testsOrdered as $item): ?>
    <?php if (($item['discount_amt'] ?? 0) > 0): ?>
      <div class="fin-row">
        <span>
          Discount — <?= esc($item['test']['test_name']) ?>
          (<?= esc($item['booking']['discount_percent']) ?>%)
        </span>
        <span class="disc">− PKR <?= number_format($item['discount_amt']) ?></span>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>

  <div class="fin-row" style="border-top:1px dashed #e5e7eb; padding-top:8px; font-weight:600;">
    <span>Total Discount</span>
    <span class="disc">− PKR <?= number_format($discountTotal) ?></span>
  </div>
<?php endif; ?>
    <div class="fin-row total-row">
      <span>Patient Pays</span>
      <span>PKR <?= number_format($patientPays) ?></span>
    </div>
  </div>

  <!-- Payment Status -->
  <div class="d-card">
    <div class="d-card-title">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="12" y1="1" x2="12" y2="23" />
        <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" />
      </svg>
      Payment
    </div>

    <?php
    $totalTests = count($testsOrdered);
    $paidTests  = 0;
    foreach ($testsOrdered as $item) {
      if (($item['booking']['payment_status'] ?? '') === 'paid') {
        $paidTests++;
      }
    }
    $isPaid = $paidTests === $totalTests && $totalTests > 0;
    ?>

    <div style="display:flex; justify-content:flex-end; margin-bottom:12px;">
      <?php if ($currentStatus === 'Refused'): ?>
        <span style="background:#fef2f2; color:#dc2626; border-radius:20px; padding:4px 14px; font-size:.82rem; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <circle cx="12" cy="12" r="10"/>
            <line x1="15" y1="9" x2="9" y2="15"/>
            <line x1="9" y1="9" x2="15" y2="15"/>
          </svg>
          Refused
        </span>
      <?php elseif ($isPaid): ?>
        <span style="background:#dcfce7; color:#15803d; border-radius:20px; padding:4px 14px; font-size:.82rem; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
            <polyline points="22 4 12 14.01 9 11.01" />
          </svg>
          Fully Paid
        </span>
      <?php else: ?>
        <span style="background:#fef9c3; color:#a16207; border-radius:20px; padding:4px 14px; font-size:.82rem; font-weight:600;">
          Pending
        </span>
      <?php endif; ?>
    </div>

    <div style="margin-bottom:16px;">
      <?php foreach ($testsOrdered as $index => $item):
        $isTestPaid = ($item['booking']['payment_status'] ?? '') === 'paid';
      ?>
        <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #f3f4f6; <?= $index === $totalTests - 1 ? 'border-bottom:none;' : '' ?>">
          <span style="font-size:.88rem; color:#111827; font-weight:500;">
            <?= esc($item['test']['test_name']) ?>
          </span>
          <span style="display:flex; align-items:center; gap:20px;">
            <span style="font-size:.82rem; color:#6b7280;">
              <?= ucfirst(esc($item['booking']['payment_method'] ?? 'Cash')) ?>
            </span>
            <?php if ($isTestPaid): ?>
              <span style="font-size:.82rem; color:#16a34a; font-weight:600;">✓ Paid</span>
            <?php else: ?>
              <span style="font-size:.82rem; color:#dc2626; font-weight:500;">Pending</span>
            <?php endif; ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>

   <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; padding-top:12px; border-top:1px solid #e5e7eb;">
    <?php if ($currentStatus === 'Refused'): ?>
      <div style="display:flex; align-items:center; gap:8px; color:#dc2626; font-size:.85rem; font-weight:500;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <line x1="15" y1="9" x2="9" y2="15"/>
          <line x1="9" y1="9" x2="15" y2="15"/>
        </svg>
        Patient Refused - No payment collected
      </div>
    <?php elseif (!$isPaid): ?>
      <div style="display:flex; align-items:center; gap:8px; color:#92400e; font-size:.85rem; font-weight:500; background:#fffbeb; padding:10px 16px; border-radius:10px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10" />
          <line x1="12" y1="8" x2="12" y2="12" />
          <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
        Upload proof of payment above to mark this booking as paid
      </div>
    <?php else: ?>
      <div style="display:flex; align-items:center; gap:8px; color:#15803d; font-size:.85rem; font-weight:500;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
          <polyline points="22 4 12 14.01 9 11.01" />
        </svg>
        Payment Completed
      </div>
    <?php endif; ?>
  </div>

    <?php if ($isPaid && !empty($latestBooking['payment_date'])): ?>
      <div style="margin-top:12px; padding-top:12px; border-top:1px solid #e5e7eb; font-size:.78rem; color:#6b7280;">
        Paid on: <?= date('d-M-y, g:i A', strtotime($latestBooking['payment_date'])) ?>

      </div>
    <?php endif; ?>
  </div>

  <!-- ============================================================ -->
  <!-- ACTIVITY LOG — added before Status History                     -->
  <!-- ============================================================ -->
  <div class="d-card">
    <div class="d-card-title">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
        <line x1="16" y1="2" x2="16" y2="6" />
        <line x1="8" y1="2" x2="8" y2="6" />
        <line x1="3" y1="10" x2="21" y2="10" />
      </svg>
      Activity Log
    </div>

    <?php
    // Build activity log entries from status history
    $activityLog = [];
    foreach ($statusHistory as $h) {
      $entry = [
        'title' => $h['status'],
        'description' => $h['notes'] ?: '',
        'time' => $h['changed_at'],
        'type' => 'lab', // default
      ];

      // Determine type based on status
      if (strpos(strtolower($h['status']), 'phlebotomist') !== false) {
        $entry['type'] = 'lab';
      } elseif (strpos(strtolower($h['status']), 'booking') !== false || strpos(strtolower($h['status']), 'created') !== false) {
        $entry['type'] = 'agent';
      } else {
        $entry['type'] = 'system';
      }

      $activityLog[] = $entry;
    }

   // Add report upload entries — group by report_file so one PDF covering
// multiple tests shows as a SINGLE activity entry, not one per test.
$reportGroups = [];
foreach ($testsOrdered as $item) {
    if (!empty($item['has_report'])) {
        $fileKey = $item['report_file'] ?? ('unknown_' . $item['test']['test_name']);

        if (!isset($reportGroups[$fileKey])) {
            $reportGroups[$fileKey] = [
                'test_names' => [],
                'time'       => $item['booking']['date_updated'] ?? date('Y-m-d H:i:s'),
            ];
        }

        $reportGroups[$fileKey]['test_names'][] = $item['test']['test_name'];
    }
}

foreach ($reportGroups as $group) {
    $activityLog[] = [
        'title'       => 'Report uploaded',
        'description' => 'Report uploaded for ' . esc(implode(', ', $group['test_names'])),
        'time'        => $group['time'],
        'type'        => 'lab',
    ];
}

   // Add ONE payment entry (all tests get paid together, so show a single log line)
$isFullyPaid = !empty($testsOrdered);
foreach ($testsOrdered as $item) {
    if (($item['booking']['payment_status'] ?? '') !== 'paid') {
        $isFullyPaid = false;
        break;
    }
}

if ($isFullyPaid) {
    $paymentDate = $testsOrdered[0]['booking']['payment_date']
        ?? $testsOrdered[0]['booking']['date_updated']
        ?? date('Y-m-d H:i:s');

    $paymentMethod = ucfirst($testsOrdered[0]['booking']['payment_method'] ?? 'Cash');

    $activityLog[] = [
        'title'       => 'Payment collected',
        'description' => 'Payment collected (' . esc($paymentMethod) . ') for all tests in this booking',
        'time'        => $paymentDate,
        'type'        => 'lab',
    ];
}

    // Sort by time descending (newest first)
    usort($activityLog, function($a, $b) {
      return strtotime($b['time']) - strtotime($a['time']);
    });

    // Remove duplicates (same title + description close in time)
    $seen = [];
    $uniqueLog = [];
    foreach ($activityLog as $entry) {
      $key = $entry['title'] . '|' . substr($entry['description'], 0, 50);
      if (!in_array($key, $seen)) {
        $seen[] = $key;
        $uniqueLog[] = $entry;
      }
    }
    $activityLog = $uniqueLog;
    ?>

    <ul class="activity-list">
      <?php if (!empty($activityLog)): ?>
        <?php foreach ($activityLog as $log): ?>
          <li>
            <div class="activity-icon <?= esc($log['type']) ?>">
              <?php if ($log['type'] === 'lab'): ?>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                  <circle cx="12" cy="7" r="4" />
                </svg>
              <?php elseif ($log['type'] === 'agent'): ?>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <path d="M12 2L2 7l10 5 10-5-10-5z" />
                  <path d="M2 17l10 5 10-5" />
                  <path d="M2 12l10 5 10-5" />
                </svg>
              <?php else: ?>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <circle cx="12" cy="12" r="10" />
                  <line x1="12" y1="8" x2="12" y2="12" />
                  <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
              <?php endif; ?>
            </div>
            <div class="activity-content">
              <div class="activity-title">
                <?= esc($log['title']) ?>
                <span class="activity-badge <?= esc($log['type']) ?>">
                  <?= ucfirst(esc($log['type'])) ?>
                </span>
              </div>
              <?php if (!empty($log['description'])): ?>
                <div class="activity-desc"><?= esc($log['description']) ?></div>
              <?php endif; ?>
              <div class="activity-time">
                <?= date('d-M-y — g:i A', strtotime($log['time'])) ?>

              </div>
            </div>
          </li>
        <?php endforeach; ?>
      <?php else: ?>
        <li style="padding: 12px 0; color: #9ca3af; font-size:.85rem;">
          No activity recorded yet.
        </li>
      <?php endif; ?>
    </ul>
  </div>
  <!-- ============================================================ -->
  <!-- END ACTIVITY LOG                                               -->
  <!-- ============================================================ -->

  <!-- Status History -->
  <div class="d-card">
    <div class="d-card-title">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10" />
        <polyline points="12 6 12 12 16 14" />
      </svg>
      Status History
    </div>
    <ul class="history-list">
      <?php foreach (array_reverse($statusHistory) as $h):
        $slug = strtolower(str_replace(' ', '-', $h['status']));
      ?>
        <li>
          <div class="h-dot <?= esc($slug) ?>"></div>
          <span class="h-badge <?= esc($slug) ?>"><?= esc($h['status']) ?></span>
          <?php if (!empty($h['changed_by'])): ?>
            <span style="font-size:.72rem; color:#6b7280; background:#f3f4f6; padding:2px 8px; border-radius:8px;">
              <?= esc($h['changed_by']) ?>
            </span>
          <?php endif; ?>
          <?php if (!empty($h['notes'])): ?>
            <span style="color:#6b7280;font-size:.75rem;">— <?= nl2br(esc($h['notes'])) ?></span>
          <?php endif; ?>
          <span class="h-time"><?= date('d-M-y — g:i A', strtotime($h['changed_at'])) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <!-- Lab Reports Section - Only show when status is Report Ready -->
  <?php
  $reports = [];
  foreach ($testsOrdered as $item) {
    if (isset($item['has_report']) && $item['has_report']) {
      $reports[] = $item;
    }
  }
  if (!empty($reports)):
?>
    <div class="d-card" style="border: 1px solid #e5e7eb; padding: 22px; margin-bottom: 16px; background: #fff; border-radius: 14px;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 8px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#111827" stroke-width="2">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
            <polyline points="14 2 14 8 20 8" />
            <line x1="16" y1="13" x2="8" y2="13" />
            <line x1="16" y1="17" x2="8" y2="17" />
            <polyline points="10 9 9 9 8 9" />
          </svg>
          <span style="font-size: .9rem; font-weight: 700; color: #111827;">Lab Reports</span>
        </div>
       <span style="font-size: .75rem; color: <?= $currentStatus === 'Report Ready' ? '#15803d' : '#a16207' ?>; background: <?= $currentStatus === 'Report Ready' ? '#dcfce7' : '#fef9c3' ?>; padding: 4px 12px; border-radius: 12px; font-weight: 600;">
  <?= $currentStatus === 'Report Ready' ? '✓ All reports uploaded' : count($reports) . ' of ' . count($testsOrdered) . ' uploaded' ?>
</span>
      </div>

    <?php if ($currentStatus === 'Report Ready'): ?>
  <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2">
      <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
      <polyline points="22 4 12 14.01 9 11.01" />
    </svg>
    <span style="font-size: .85rem; color: #166534; font-weight: 500;">
      All reports uploaded.
    </span>
  </div>
<?php else: ?>
  <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="2">
      <circle cx="12" cy="12" r="10" />
      <line x1="12" y1="16" x2="12" y2="12" />
      <line x1="12" y1="8" x2="12.01" y2="8" />
    </svg>
    <span style="font-size: .85rem; color: #92400e; font-weight: 500;">
      <?= count($testsOrdered) - count($reports) ?> report(s) still pending.
    </span>
  </div>
<?php endif; ?>
      <?php foreach ($reports as $report): ?>
  <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f3f4f6;">
    <div>
      <div style="font-size: .9rem; font-weight: 600; color: #111827;">
        <?= esc($report['test']['test_name']) ?>
      </div>
      <?php if (!empty($report['test']['test_code'])): ?>
        <div style="font-size: .75rem; color: #9ca3af; margin-top: 2px;">
          <?= esc($report['test']['test_code']) ?>
        </div>
      <?php endif; ?>
    </div>
    <div style="display: flex; align-items: center; gap: 12px;">
      <span style="font-size: .85rem; color: #059669; font-weight: 500; background: #d1fae5; padding: 2px 10px; border-radius: 12px;">
        Report
      </span>
      <a href="<?= base_url('booking/downloadReport/' . ($report['booking']['id'] ?? '')) ?>"
        style="display: inline-flex; align-items: center; gap: 4px; color: #1d4ed8; text-decoration: none; font-size: .82rem; font-weight: 500;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
          <polyline points="7 10 12 15 17 10" />
          <line x1="12" y1="15" x2="12" y2="3" />
        </svg>
        Download
      </a>

      <form action="<?= base_url('booking/deleteReport/' . ($report['booking']['id'] ?? '')) ?>" method="post"
        onsubmit="return confirm('Delete this report? You will need to upload a new one for this test.');"
        style="margin:0;">
        <?= csrf_field() ?>
        <button type="submit" style="display: inline-flex; align-items: center; gap: 4px; color: #dc2626; background:none; border:none; cursor:pointer; text-decoration: none; font-size: .82rem; font-weight: 500; padding:0;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6" />
            <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" />
            <path d="M10 11v6" />
            <path d="M14 11v6" />
            <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2" />
          </svg>
          Delete
        </button>
      </form>
    </div>
  </div>
<?php endforeach; ?>

      <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 14px; border-top: 1px solid #f3f4f6;">
        <div>
          <div style="font-size: .75rem; color: #9ca3af;">
            Created: <?= date('d-M-y, g:i A', strtotime($latestBooking['date_created'])) ?>
          </div>
          <div style="font-size: .75rem; color: #9ca3af; margin-top: 2px;">
            By: <?= esc($latestBooking['lab_name'] ?? 'INFINITY Lab') ?>
          </div>
        </div>
        <div style="text-align: right;">
          <div style="font-size: .75rem; color: #9ca3af;">
            Assigned to: <?= esc($latestBooking['lab_name'] ?? 'INFINITY Lab') ?>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- JavaScript for file upload validation -->
<script>
  //revisit
  populateTimeSelect(document.getElementById('revisit_time')); 
  const revisitForm = document.querySelector('#revisitForm form');
if (revisitForm) {
  revisitForm.addEventListener('submit', function (e) {
    const dateVal = document.getElementById('revisit_date')?.value;
    const timeVal = document.getElementById('revisit_time')?.value;
    const hidden  = document.getElementById('revisit_datetime_hidden');

    if (!dateVal || !timeVal) {
      e.preventDefault();
      alert('Please select both preferred date and time.');
      return false;
    }
    hidden.value = `${dateVal} ${timeVal}:00`;
  });
}
// Build a 15-min interval <select> (00:00 -> 23:45), 12-hr display, 24-hr value
function populateTimeSelect(selectEl) {
  if (!selectEl) return;
  selectEl.innerHTML = '<option value="" disabled selected>Select time</option>';
  for (let h = 0; h < 24; h++) {
    for (let m = 0; m < 60; m += 15) {
      const hh = String(h).padStart(2, '0');
      const mm = String(m).padStart(2, '0');
      const value = `${hh}:${mm}`;

      const period = h < 12 ? 'AM' : 'PM';
      const displayHour = h % 12 === 0 ? 12 : h % 12;
      const label = `${displayHour}:${mm} ${period}`;

      const opt = document.createElement('option');
      opt.value = value;
      opt.textContent = label;
      selectEl.appendChild(opt);
    }
  }
}

populateTimeSelect(document.getElementById('eta_time'));
populateTimeSelect(document.getElementById('preferred_eta_time'));

const assignForm = document.querySelector('#assignForm form');
if (assignForm) {
  assignForm.addEventListener('submit', function (e) {
    const etaDateVal = document.getElementById('eta_date')?.value;
    const etaTimeVal = document.getElementById('eta_time')?.value;
    const etaHidden  = document.getElementById('eta_hidden');

    if (!etaDateVal || !etaTimeVal) {
      e.preventDefault();
      alert('Please select both ETA date and time.');
      return false;
    }
    etaHidden.value = `${etaDateVal} ${etaTimeVal}:00`;

    const prefDateVal = document.getElementById('preferred_eta_date')?.value;
    const prefTimeVal = document.getElementById('preferred_eta_time')?.value;
    const prefHidden  = document.getElementById('preferred_eta');

    if (prefDateVal && prefTimeVal) {
      prefHidden.value = `${prefDateVal} ${prefTimeVal}:00`;
    } else if (prefDateVal || prefTimeVal) {
      e.preventDefault();
      alert('Please fill in both the preferred date and time, or leave both empty.');
      return false;
    } else {
      prefHidden.value = '';
    }
  });
}
  // patients eta above
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('uploadReportForm');
    const fileInput = document.getElementById('reportFile');
    const fileError = document.getElementById('fileError');
    const uploadBtn = document.getElementById('uploadBtn');

    if (form) {
      form.addEventListener('submit', function(e) {
        const checkboxes = document.querySelectorAll('input[name="test_ids[]"]:checked');
        const file = fileInput.files[0];
        let isValid = true;
        let errorMsg = '';

        if (checkboxes.length === 0) {
          errorMsg = 'Please select at least one test for this report.';
          isValid = false;
        }

        if (!file) {
          errorMsg = 'Please select a PDF file to upload.';
          isValid = false;
        } else if (file.type !== 'application/pdf') {
          errorMsg = 'Only PDF files are allowed.';
          isValid = false;
        } else if (file.size > 10 * 1024 * 1024) {
          errorMsg = 'File size exceeds 10MB limit.';
          isValid = false;
        }

        if (!isValid) {
          e.preventDefault();
          fileError.textContent = errorMsg;
          fileError.style.display = 'block';
          return false;
        }

        uploadBtn.innerHTML = `
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite;">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 2a10 10 0 0110 10"/>
          </svg>
          Uploading...
        `;
        uploadBtn.disabled = true;
      });
    }

    if (fileInput) {
      fileInput.addEventListener('change', function() {
        fileError.style.display = 'none';
      });
    }

    const checkboxes = document.querySelectorAll('input[name="test_ids[]"]');
    checkboxes.forEach(cb => {
      cb.addEventListener('change', function() {
        const checked = document.querySelectorAll('input[name="test_ids[]"]:checked').length;
        const total = <?= count($testsOrdered) ?>;
      });
    });
  });

  const style = document.createElement('style');
  style.textContent = `
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
  `;
  document.head.appendChild(style);


// graph code
// ---- Phlebotomist ETA schedule (shown while assigning) ----
const FRANCHISE_ID = <?= (int) ($franchiseId ?? 0) ?>;

function fmtTimeLabel(dateStr) {
  const d = new Date(dateStr.replace(' ', 'T'));
  let h = d.getHours();
  const m = String(d.getMinutes()).padStart(2, '0');
  const period = h < 12 ? 'AM' : 'PM';
  h = h % 12 === 0 ? 12 : h % 12;
  return `${h}:${m} ${period}`;
}

function fmtDateLabel(dateStr) {
  const d = new Date(dateStr.replace(' ', 'T'));
  return d.toLocaleDateString(undefined, { weekday: 'short', day: 'numeric', month: 'short' });
}

function dateKey(dateStr) {
  return dateStr.split(' ')[0]; // 'YYYY-MM-DD'
}

function minutesSinceMidnight(dateStr) {
  const d = new Date(dateStr.replace(' ', 'T'));
  return d.getHours() * 60 + d.getMinutes();
}

function renderPhlebSchedule(schedule) {
  const chartEl = document.getElementById('phlebScheduleChart');
  const emptyEl = document.getElementById('phlebScheduleEmpty');
  const loadingEl = document.getElementById('phlebScheduleLoading');
  loadingEl.style.display = 'none';

  let flat = [];
  schedule.forEach(p => {
    p.bookings.forEach(b => {
      flat.push({
        date: dateKey(b.eta),
        phleb_name: p.phleb_name,
        eta: b.eta,
        patient_name: b.patient_name,
        status: b.status,
      });
    });
  });

  if (flat.length === 0) {
    emptyEl.style.display = 'block';
    chartEl.innerHTML = '';
    return;
  }
  emptyEl.style.display = 'none';

  const byDate = {};
  flat.forEach(item => {
    if (!byDate[item.date]) byDate[item.date] = [];
    byDate[item.date].push(item);
  });
  const sortedDates = Object.keys(byDate).sort();

  const statusColor = {
    'Phlebotomist Assigned': '#1d4ed8',
    'Arrived': '#7c3aed',
  };

  const rowHeight = 36;
  const dateLabelHeight = 26;
  const topAxisHeight = 30;

  // ---- ONE global time range across ALL dates ----
  const allMinutesGlobal = flat.map(i => minutesSinceMidnight(i.eta));
  let minM = Math.max(0, Math.floor((Math.min(...allMinutesGlobal) - 60) / 60) * 60);
  let maxM = Math.min(24 * 60, Math.ceil((Math.max(...allMinutesGlobal) + 60) / 60) * 60);
  if (maxM - minM < 120) maxM = minM + 120;

  const rangeHours = (maxM - minM) / 60;
  const chartWidth = Math.max(500, rangeHours * 70); // scrollable area width only (no left label width now)
  const xFor = (mins) => ((mins - minM) / (maxM - minM)) * chartWidth;

  // ---- Total height ----
  let totalHeight = topAxisHeight;
  sortedDates.forEach(date => {
    const phlebNames = [...new Set(byDate[date].map(i => i.phleb_name))];
    totalHeight += dateLabelHeight + phlebNames.length * rowHeight;
  });
  totalHeight += 8;

  // ---- Top axis (drawn once, in the scrollable SVG) ----
  let axisSvg = '';
  for (let t = minM; t <= maxM; t += 60) {
    const x = xFor(t);
    const hour = Math.floor(t / 60);
    const period = hour < 12 ? 'AM' : 'PM';
    const displayHour = hour % 12 === 0 ? 12 : hour % 12;
    axisSvg += `
      <line x1="${x}" y1="${topAxisHeight}" x2="${x}" y2="${totalHeight - 4}" stroke="#f1f5f9" stroke-width="1"/>
      <text x="${x}" y="16" font-size="12" fill="#000000" text-anchor="middle">${displayHour}${period}</text>
    `;
  }

  // ---- Left labels (HTML, fixed, not scrolled) + dots (SVG, scrolled) ----
  let y = topAxisHeight;
  let leftLabelsHtml = `<div style="height:${topAxisHeight}px;"></div>`; // spacer to align with axis
  let dotsSvg = '';

  sortedDates.forEach(date => {
    const items = byDate[date];
    const phlebNames = [...new Set(items.map(i => i.phleb_name))];

    leftLabelsHtml += `
      <div style="height:${dateLabelHeight}px; display:flex; align-items:flex-end;">
        <span style="font-size:13px; font-weight:700; color:#1e40af;">${fmtDateLabel(items[0].eta)}</span>
      </div>
    `;
    y += dateLabelHeight;

    phlebNames.forEach(name => {
      const rowY = y + rowHeight / 2;

      leftLabelsHtml += `
        <div style="height:${rowHeight}px; display:flex; align-items:center;">
          <span style="font-size:14px; font-weight:600; color:#374151;">${name}</span>
        </div>
      `;

      dotsSvg += `<line x1="0" y1="${rowY}" x2="${chartWidth}" y2="${rowY}" stroke="#f3f4f6" stroke-width="1"/>`;

      items.filter(it => it.phleb_name === name).forEach(it => {
        const x = xFor(minutesSinceMidnight(it.eta));
        const color = statusColor[it.status] || '#6b7280';
        const title = `${it.patient_name} — ${fmtDateLabel(it.eta)}, ${fmtTimeLabel(it.eta)} (${it.status})`;
        dotsSvg += `
          <circle cx="${x}" cy="${rowY}" r="6" fill="${color}" stroke="#fff" stroke-width="2">
            <title>${title}</title>
          </circle>
        `;
      });
      y += rowHeight;
    });
  });

  chartEl.innerHTML = `
    <div style="display:flex;">
      <div style="flex-shrink:0; width:110px; padding-right:8px;">
        ${leftLabelsHtml}
      </div>
      <div style="overflow-x:auto; flex:1;">
        <svg viewBox="0 0 ${chartWidth} ${totalHeight}" style="width:${chartWidth}px; height:auto; overflow:visible;">
          ${axisSvg}
          ${dotsSvg}
        </svg>
      </div>
    </div>
    <div style="display:flex; gap:14px; margin-top:8px; font-size:.7rem; color:#6b7280;">
      <span style="display:inline-flex; align-items:center; gap:4px;">
        <span style="width:8px; height:8px; border-radius:50%; background:#1d4ed8; display:inline-block;"></span>
        Assigned
      </span>
      <span style="display:inline-flex; align-items:center; gap:4px;">
        <span style="width:8px; height:8px; border-radius:50%; background:#7c3aed; display:inline-block;"></span>
        Arrived
      </span>
    </div>
  `;
}

function loadPhlebSchedule() {
  if (!FRANCHISE_ID) return;
  const card = document.getElementById('phlebScheduleCard');
  if (!card) return;
  card.style.display = 'block';

  fetch(`<?= base_url('booking/phlebotomistSchedule') ?>/${FRANCHISE_ID}`)
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        renderPhlebSchedule(data.schedule);
      }
    })
    .catch(() => {
      document.getElementById('phlebScheduleLoading').textContent = 'Could not load schedule.';
    });
}
</script>

<?= view('templates/footer') ?>