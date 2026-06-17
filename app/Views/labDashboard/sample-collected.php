<?= view('templates/header', ['pageTitle' => 'Sample Collected', 'activePage' => 'lablist']) ?>

<div class="booking-wrapper">

  <!-- Back Button -->
  <div class="mb-3">
    <a href="/lab-list" class="text-decoration-none">
      <i class="bi bi-arrow-left"></i> Back to Lab List
    </a>
  </div>

  <!-- ===== STATUS PROGRESS (exactly as screenshot) ===== -->
  <div class="status-progress">
    <div class="step-pill done"><span class="dot"></span> In Process</div>
    <div class="step-pill done"><span class="dot"></span> Phlebotomist Assigned</div>
    <div class="step-pill done"><span class="dot"></span> Phlebotomist Arrived</div>
    <div class="step-pill active"><span class="dot"></span> Sample Collected</div>
    <div class="step-pill"><span class="dot"></span> Report Ready</div>
  </div>

  <!-- ===== UPLOAD LAB REPORTS ===== -->
  <div class="upload-box">
    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
      <span class="fw-bold" style="color:#1a3552;">Upload Lab Reports</span>
      <span class="badge bg-light text-dark border ms-2">LFTs (T-Bili, ALT, AST, ALP, ALB, GGT, T-Prot, Globulins, A/G) 6668</span>
      <span class="badge bg-light text-dark border">Beta HCG 4303</span>
    </div>
    <div class="text-muted small mb-2">
      <i class="bi bi-info-circle"></i> Check the tests covered by your report, then upload one PDF. One PDF can cover multiple tests.
    </div>
    <div class="d-flex flex-wrap align-items-center gap-3">
      <span class="badge bg-primary text-white px-3 py-2 rounded-pill">Select tests above first</span>
      <span class="text-muted small"><i class="bi bi-file-pdf"></i> PDF only, max 10MB. Booking auto-completes when all tests are reported.</span>
    </div>
  </div>

  <!-- ===== PATIENT INFORMATION ===== -->
  <div class="patient-info-card">
    <div class="row g-2 align-items-center">
      <div class="col-md-8">
        <div class="fw-bold fs-5" style="color:#0b2b4a;">Nadia Tariq</div>
        <div class="d-flex flex-wrap gap-3 text-muted small">
          <span><i class="bi bi-phone"></i> +923228117733</span>
          <span><i class="bi bi-geo-alt"></i> House 90 Dream Villas Society</span>
          <span><i class="bi bi-gender-female"></i> Female</span>
        </div>
      </div>
      <div class="col-md-4 text-md-end">
        <span class="badge bg-light border rounded-pill px-3 py-2 me-1"><i class="bi bi-pin-map"></i> PIN LOCATION</span>
        <span class="badge bg-light border rounded-pill px-3 py-2"><i class="bi bi-map"></i> View on Map</span>
      </div>
    </div>
    <div class="mt-2 text-muted small border-top pt-2">
      <i class="bi bi-sticky"></i> NOTES / INSTRUCTIONS: 16 june 3 pm
    </div>
  </div>

  <!-- ===== PHLEBOTOMIST ===== -->
  <div class="phlebo-row d-flex flex-wrap align-items-center justify-content-between">
    <div>
      <span class="text-muted small">Phlebotomist</span>
      <span class="fw-bold ms-2" style="color:#1a3552;">Khawar</span>
    </div>
    <div>
      <span class="text-muted small">ETA:</span>
      <span class="fw-semibold ms-1">16 Jun 2026, 3:00 PM</span>
    </div>
  </div>

  <!-- ===== TESTS ORDERED TABLE ===== -->
  <div class="table-responsive mt-3">
    <table class="table table-test align-middle">
      <thead>
        <tr>
          <th>CODE</th>
          <th>TEST NAME</th>
          <th>REPORTING TIME</th>
          <th>PATIENT PRICE</th>
          <th>PAYMENT</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><span class="badge bg-light text-dark fw-bold">6668</span></td>
          <td>LFTs (T-Bili, ALT, AST, ALP, ALB, GGT, T-Prot, Globulins, A/G)</td>
          <td><span class="text-muted">—</span></td>
          <td><span class="fw-bold">PKR 1,365</span></td>
          <td><span class="badge bg-success-subtle text-success border-0 rounded-pill px-3">Cash</span></td>
        </tr>
        <tr>
          <td><span class="badge bg-light text-dark fw-bold">4303</span></td>
          <td>Beta HCG</td>
          <td><span class="badge bg-warning-subtle text-warning-emphasis border-0 rounded-pill">Same Day After 3 Hour</span></td>
          <td><span class="fw-bold">PKR 1,575</span></td>
          <td><span class="badge bg-secondary-subtle text-secondary border-0 rounded-pill px-3">Pending</span></td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- ===== FINANCIAL BREAKDOWN ===== -->
  <div class="financial-row row g-3 align-items-center">
    <div class="col-md-6">
      <div class="d-flex flex-wrap gap-3">
        <div><span class="text-muted small">Original Total (Rack Rate):</span> <span class="fw-semibold">4,200</span></div>
        <div><span class="text-muted small">Discount (30%):</span> <span class="fw-semibold text-success">1,260</span></div>
        <div><span class="text-muted small">Patient Pays:</span> <span class="fw-bold fs-5" style="color:#0b2b4a;">PKR 2,940</span></div>
      </div>
    </div>
    <div class="col-md-6 text-md-end">
      <div class="d-flex flex-wrap justify-content-md-end gap-2">
        <span class="badge bg-light border rounded-pill px-3 py-2">LFTs (T-Bili, ALT, AST, ALP, ALB, GGT, T-Prot, Globulins, A/G) 4,200</span>
        <span class="badge bg-light border rounded-pill px-3 py-2">Beta HCG 4,200</span>
      </div>
    </div>
  </div>

  <!-- ===== ACTIVITY LOG & STATUS HISTORY ===== -->
  <div class="row mt-4 g-4">
    <div class="col-md-7">
      <h6 class="fw-bold mb-3" style="color:#1a3552;"><i class="bi bi-clock-history me-2"></i>Activity Log</h6>
      <div class="log-item">
        <div class="d-flex justify-content-between">
          <span class="fw-semibold">INFINITY Lab Lab</span>
          <span class="log-time">Jun 15, 7:30 PM</span>
        </div>
        <div class="text-muted small">Status: Phlebotomist Arrived → Sample Collected</div>
      </div>
      <div class="log-item">
        <div class="d-flex justify-content-between">
          <span class="fw-semibold">INFINITY Lab Lab</span>
          <span class="log-time">Jun 15, 7:20 PM</span>
        </div>
        <div class="text-muted small">Added: Beta HCG</div>
      </div>
      <div class="log-item">
        <div class="d-flex justify-content-between">
          <span class="fw-semibold">INFINITY Lab Lab</span>
          <span class="log-time">Jun 15, 7:10 PM</span>
        </div>
        <div class="text-muted small">Status: Phlebotomist Assigned → Phlebotomist Arrived</div>
      </div>
      <div class="log-item">
        <div class="d-flex justify-content-between">
          <span class="fw-semibold">INFINITY Lab Lab</span>
          <span class="log-time">Jun 15, 7:05 PM</span>
        </div>
        <div class="text-muted small">Status: In Process → Phlebotomist Assigned (Khawar)</div>
      </div>
      <div class="log-item">
        <div class="d-flex justify-content-between">
          <span class="fw-semibold">Marham Agent Agent</span>
          <span class="log-time">Jun 15, 7:13 PM</span>
        </div>
        <div class="text-muted small">Booking created → 1 test: LFTs (T-Bili, ALT, AST, ALP, ALB, GGT, T-Prot, Globulins, A/G)</div>
      </div>
    </div>
    <div class="col-md-5">
      <h6 class="fw-bold mb-3" style="color:#1a3552;"><i class="bi bi-list-ul me-2"></i>Status History</h6>
      <div class="d-flex flex-wrap gap-2">
        <span class="status-badge bg-light border">In Process</span>
        <span class="status-badge bg-light border">Phlebotomist Assigned</span>
        <span class="status-badge bg-light border">Phlebotomist Arrived</span>
        <span class="status-badge collected">Sample Collected</span>
      </div>
      <hr class="my-3">
      <div class="text-muted small">
        <div><i class="bi bi-calendar-plus"></i> Created: Jun 15, 2026 7:13 PM</div>
        <div><i class="bi bi-person"></i> By: Marham Agent</div>
        <div><i class="bi bi-building"></i> Assigned to: INFINITY Lab</div>
      </div>
    </div>
  </div>

  <!-- ===== FOOTER / "View & Update" BUTTON (exactly like screenshot) ===== -->
  <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap">
    <div class="text-muted small">
      <i class="bi bi-info-circle"></i> Sample Collected · Booking ID: cmqfan2e2000zt5083orfxtd1j
    </div>
    <a href="#" class="btn-view-update">
      <i class="bi bi-eye"></i> View &amp; Update <i class="bi bi-arrow-right"></i>
    </a>
  </div>

</div> <!-- /booking-wrapper -->

<style>
   
    /* main container – exactly like the screenshot design */
    .booking-wrapper {
      max-width: 1320px;
      margin: 0 auto;
      background: #ffffff;
      border-radius: 32px;
      box-shadow: 0 20px 40px -12px rgba(0,20,40,0.18);
      padding: 2rem 2rem 1.5rem;
    }

    
    .booking-id-badge {
      background: #eef3fc;
      padding: 6px 18px;
      border-radius: 40px;
      font-size: 0.85rem;
      font-weight: 600;
      color: #1a4f8b;
      letter-spacing: 0.2px;
    }

    /* status progress – pills */
    .status-progress {
      background: #f9fcff;
      border: 1px solid #e9eef6;
      border-radius: 60px;
      padding: 0.7rem 1.8rem;
      margin-bottom: 1.8rem;
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem 1.8rem;
    }
    .step-pill {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.9rem;
      font-weight: 500;
      color: #6b7a8f;
    }
    .step-pill .dot {
      width: 10px;
      height: 10px;
      border-radius: 10px;
      background: #cfd8e4;
    }
    .step-pill.active .dot { background: #1b7e6a; }
    .step-pill.active { color: #1d3b5b; font-weight: 600; }
    .step-pill.done .dot { background: #1b7e6a; }

    /* upload section */
    .upload-box {
      background: #f9fcff;
      border: 1px dashed #bcc9db;
      border-radius: 20px;
      padding: 1.2rem 1.5rem;
      margin-bottom: 2rem;
    }
    .upload-box .tests-pills .badge {
      background: #eef3fc;
      color: #1f3e5e;
      font-weight: 500;
      padding: 6px 14px;
      border-radius: 40px;
      font-size: 0.8rem;
      margin-right: 8px;
      margin-bottom: 4px;
      cursor: default;
    }
    .upload-box .tests-pills .badge.bg-primary { background: #1d5b9e !important; color: #fff; }

    /* patient card */
    .patient-info-card {
      background: #fafdff;
      border: 1px solid #e6edf6;
      border-radius: 18px;
      padding: 1.2rem 1.5rem;
      margin-bottom: 1.8rem;
    }

    /* phlebotomist row */
    .phlebo-row {
      background: #f2f8ff;
      border-radius: 18px;
      padding: 0.9rem 1.5rem;
      margin-bottom: 1.8rem;
      border-left: 4px solid #2a7f6e;
    }

    /* table */
    .table-test thead th {
      background: #f8fafd;
      color: #3c4f66;
      font-weight: 600;
      font-size: 0.8rem;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      padding: 0.8rem 0.5rem;
      border-bottom: 1px solid #dee7f0;
    }
    .table-test td {
      vertical-align: middle;
      padding: 0.9rem 0.5rem;
      border-bottom: 1px solid #eef3f9;
    }
    .badge-payment { background: #eef3fc; color: #1f3e5e; font-weight: 500; }
    .badge-cash { background: #e3f0e8; color: #1e6b4c; }

    /* financial breakdown */
    .financial-row {
      background: #fafdff;
      border-radius: 16px;
      padding: 1rem 1.5rem;
      border: 1px solid #e6edf6;
      margin-top: 1.5rem;
    }

    /* activity log */
    .log-item {
      border-left: 2px solid #d4deec;
      padding-left: 1rem;
      margin-bottom: 0.8rem;
    }
    .log-item .log-time { font-size: 0.75rem; color: #6f7e93; }

    /* status badge */
    .status-badge {
      background: #eef3fc;
      padding: 4px 12px;
      border-radius: 40px;
      font-size: 0.75rem;
      font-weight: 600;
      color: #1d4b7a;
    }
    .status-badge.collected { background: #e1f0e8; color: #1a6b4c; }

    .btn-view-update {
      background: #1d5b9e;
      color: white;
      border-radius: 40px;
      padding: 6px 20px;
      font-weight: 500;
      font-size: 0.85rem;
      text-decoration: none;
      transition: 0.2s;
    }
    .btn-view-update:hover { background: #14437a; color: white; }

    .price-strike { font-size: 0.8rem; color: #8b9aad; text-decoration: line-through; }

    /* small tweaks */
    .text-muted-custom { color: #6f7e93; }
    .fs-small { font-size: 0.85rem; }
    .fw-500 { font-weight: 500; }
    .bg-soft-blue { background: #f5f9ff; }
    .border-dash { border: 1px dashed #c9d5e6; }
  </style>

<?= view('templates/footer') ?>