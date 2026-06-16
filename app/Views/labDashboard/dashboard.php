<?= view('templates/header', ['pageTitle' => 'Lab List', 'activePage' => 'lablist']) ?>

<!DOCTYPE html>


<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Health+ Lab Partner Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <style>
    body {
      background: #f7f8fa;
      font-family: 'Segoe UI', Arial, sans-serif;
      font-size: 14px;
      color: #222;
    }

    /* ── Navbar ── */
    .topbar {
      background: #fff;
      border-bottom: 1px solid #e8eaed;
      padding: 10px 28px;
    }
    .topbar .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
    }
    .topbar .brand-icon {
      background: #1b3a5c;
      color: #fff;
      border-radius: 8px;
      width: 38px; height: 38px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
    }
    .topbar .brand-text { line-height: 1.1; }
    .topbar .brand-text strong { font-size: 15px; color: #1b3a5c; }
    .topbar .brand-text small { font-size: 11px; color: #888; display: block; }

    .topbar .nav-link {
      color: #444;
      font-size: 13px;
      display: flex; align-items: center; gap: 5px;
    }
    .topbar .nav-link:hover { color: #1b3a5c; }

    .partner-badge {
      font-size: 11px;
      color: #7c3aed;
      background: #f3eeff;
      border-radius: 4px;
      padding: 1px 7px;
      display: inline-block;
    }

    .btn-signout {
      color: #444;
      border: none;
      background: none;
      font-size: 13px;
      display: flex; align-items: center; gap: 5px;
      cursor: pointer;
    }
    .btn-signout:hover { color: #c00; }

    /* ── Main area ── */
    .main-content { padding: 32px 32px 20px; }

    /* ── New Booking btn ── */
    .btn-new-booking {
      background: #1b3a5c;
      color: #fff;
      border-radius: 8px;
      padding: 9px 18px;
      font-size: 14px;
      border: none;
      display: flex; align-items: center; gap: 6px;
    }
    .btn-new-booking:hover { background: #14304e; color: #fff; }

    /* ── Stat cards ── */
    .stat-card {
      background: #fff;
      border-radius: 12px;
      padding: 20px 22px;
      border: 1px solid #eaedf0;
      min-width: 120px;
    }
    .stat-card .stat-val {
      font-size: 28px;
      font-weight: 700;
      line-height: 1;
      margin-bottom: 4px;
    }
    .stat-card .stat-label { font-size: 12px; color: #888; }

    .stat-val.gray   { color: #555; }
    .stat-val.orange { color: #d97706; }
    .stat-val.blue   { color: #2563eb; }
    .stat-val.purple { color: #7c3aed; }
    .stat-val.red    { color: #dc2626; }
    .stat-val.green  { color: #16a34a; }

    /* ── Tabs ── */
    .tab-nav .nav-link {
      font-size: 13px;
      color: #444;
      border-radius: 6px;
      padding: 6px 14px;
      display: flex; align-items: center; gap: 6px;
    }
    .tab-nav .nav-link.active {
      background: #f0f4ff;
      color: #1b3a5c;
      font-weight: 600;
    }
    .tab-nav .badge-count {
      background: #e8eaed;
      color: #444;
      border-radius: 20px;
      padding: 1px 7px;
      font-size: 11px;
    }
    .tab-nav .nav-link.active .badge-count {
      background: #c7d7f5;
      color: #1b3a5c;
    }

    /* ── Filter bar ── */
    .filter-bar {
      background: #fff;
      border: 1px solid #e8eaed;
      border-radius: 10px;
      padding: 14px 18px;
    }
    .filter-bar input[type="date"] {
      border: 1px solid #dde0e5;
      border-radius: 6px;
      padding: 5px 10px;
      font-size: 13px;
      color: #555;
      outline: none;
    }
    .filter-bar input[type="date"]:focus { border-color: #2563eb; }

    .status-pill {
      border: 1px solid #dde0e5;
      border-radius: 20px;
      padding: 4px 13px;
      font-size: 12px;
      background: #fff;
      cursor: pointer;
      transition: all .15s;
      white-space: nowrap;
    }
    .status-pill.active,
    .status-pill:hover { background: #1b3a5c; color: #fff; border-color: #1b3a5c; }

    /* ── Table ── */
    .leads-table {
      background: #fff;
      border-radius: 10px;
      border: 1px solid #e8eaed;
      overflow: hidden;
    }
    .leads-table table { margin: 0; }
    .leads-table thead th {
      background: #fff;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: #8a8f9a;
      border-bottom: 1px solid #e8eaed;
      padding: 12px 16px;
    }
    .leads-table tbody td {
      padding: 14px 16px;
      vertical-align: top;
      border-bottom: 1px solid #f0f2f5;
    }
    .leads-table tbody tr:last-child td { border-bottom: none; }
    .leads-table tbody tr:hover { background: #fafbff; }

    .patient-name { font-weight: 600; font-size: 14px; margin-bottom: 1px; }
    .patient-meta { font-size: 12px; color: #999; }

    .test-name { font-size: 13px; color: #333; }
    .test-desc { font-size: 12px; color: #aaa; }

    .badge-tag {
      display: inline-block;
      border-radius: 20px;
      padding: 2px 10px;
      font-size: 11px;
      margin-top: 4px;
    }
    .badge-tag.same-day { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .badge-tag.timings  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-tag.reports  { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }

    .price-main    { font-size: 13px; font-weight: 600; color: #222; }
    .price-strike  { font-size: 12px; color: #bbb; text-decoration: line-through; }

    .status-badge {
      display: inline-block;
      border-radius: 20px;
      padding: 4px 12px;
      font-size: 12px;
      font-weight: 500;
      white-space: nowrap;
    }
    .status-badge.phlebotomist { background: #dbeafe; color: #1d4ed8; }
    .status-badge.collected    { background: #fef9c3; color: #a16207; }
    .status-badge.report-ready { background: #d1fae5; color: #065f46; }

    .eta-val { font-size: 13px; color: #dc2626; font-weight: 500; }

    .date-val { font-size: 12px; color: #888; }

    .btn-view {
      color: #1b3a5c;
      font-size: 13px;
      font-weight: 500;
      text-decoration: none;
      white-space: nowrap;
      display: flex; align-items: center; gap: 4px;
    }
    .btn-view:hover { color: #2563eb; }

    .star-badge {
      display: inline-flex; align-items: center; gap: 3px;
      background: #fffbeb;
      border: 1px solid #fde68a;
      border-radius: 20px;
      padding: 1px 8px;
      font-size: 11px;
      color: #b45309;
      margin-left: 4px;
    }
  </style>
</head>
<body>

<!-- ── Top Navigation ── -->
<nav class="topbar d-flex align-items-center justify-content-between">
  <a href="#" class="brand">
    <div class="brand-icon"><i class="bi bi-activity"></i></div>
    <div class="brand-text">
      <strong>Health+</strong>
      <small>by marham.pk</small>
    </div>
  </a>

  <div class="d-flex align-items-center gap-4">
    <a href="#" class="nav-link"><i class="bi bi-journal-text"></i> Price List</a>

    <div class="d-flex align-items-center gap-1">
      <i class="bi bi-person-circle fs-5 text-secondary"></i>
      <div>
        <div style="font-size:13px;font-weight:600;line-height:1.2">INFINITY Lab</div>
        <span class="partner-badge">Lab Partner</span>
      </div>
    </div>

    <button class="btn-signout"><i class="bi bi-box-arrow-right"></i> Sign Out</button>
  </div>
</nav>

<!-- ── Main Content ── -->
<div class="main-content">

  <!-- Header row -->
  <div class="d-flex align-items-start justify-content-between mb-4">
    <div>
      <h4 class="fw-bold mb-1" style="font-size:22px;">Lab Partner Dashboard</h4>
      <div style="font-size:13px;color:#888;">Tuesday, June 16, 2026</div>
    </div>
    <button class="btn-new-booking"><i class="bi bi-plus-lg"></i> New Booking</button>
  </div>

  <!-- Stat Cards -->
  <div class="d-flex gap-3 mb-4 flex-wrap">
    <div class="stat-card">
      <div class="stat-val gray">165</div>
      <div class="stat-label">Total</div>
    </div>
    <div class="stat-card" style="background:#fffdf5;">
      <div class="stat-val orange">0</div>
      <div class="stat-label">In Process</div>
    </div>
    <div class="stat-card" style="background:#f0f5ff;">
      <div class="stat-val blue">3</div>
      <div class="stat-label">Assigned</div>
    </div>
    <div class="stat-card" style="background:#f8f0ff;">
      <div class="stat-val purple">0</div>
      <div class="stat-label">Arrived</div>
    </div>
    <div class="stat-card" style="background:#fff5f5;">
      <div class="stat-val red">2</div>
      <div class="stat-label">Collected</div>
    </div>
    <div class="stat-card" style="background:#f0fff6;">
      <div class="stat-val green">147</div>
      <div class="stat-label">Report Ready</div>
    </div>
  </div>

  <!-- Tabs -->
  <ul class="nav tab-nav mb-3">
    <li class="nav-item">
      <a href="#" class="nav-link active">
        <i class="bi bi-person"></i> Individual Leads
        <span class="badge-count">165</span>
      </a>
    </li>
    <li class="nav-item">
      <a href="#" class="nav-link">
        <i class="bi bi-building"></i> B2B Activities
        <span class="badge-count">2</span>
      </a>
    </li>
  </ul>

  <!-- Filter Bar -->
  <div class="filter-bar mb-3">
    <div class="d-flex align-items-center gap-3 flex-wrap">
      <!-- Date range -->
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-calendar3 text-secondary"></i>
        <input type="date" placeholder="dd/mm/yyyy"/>
        <span style="color:#aaa;font-size:13px;">to</span>
        <input type="date" placeholder="dd/mm/yyyy"/>
      </div>

      <!-- Status pills -->
      <div class="d-flex align-items-center gap-2 flex-wrap">
        <i class="bi bi-funnel text-secondary"></i>
        <span class="status-pill active" onclick="setPill(this)">All</span>
        <span class="status-pill" onclick="setPill(this)">In Process</span>
        <span class="status-pill" onclick="setPill(this)">Phleb. Assigned</span>
        <span class="status-pill" onclick="setPill(this)">Arrived</span>
        <span class="status-pill" onclick="setPill(this)">Collected</span>
        <span class="status-pill" onclick="setPill(this)">Report Ready</span>
        <span class="status-pill" onclick="setPill(this)">Refused</span>
      </div>
    </div>
  </div>

  <!-- Leads Table -->
  <div class="leads-table">
    <table class="table table-borderless mb-0">
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

        <!-- Row 1 -->
        <tr>
          <td>
            <div class="patient-name">Nadia Tariq</div>
            <div class="patient-meta">Female</div>
            <div class="patient-meta">House 90 Dream Villas So...</div>
          </td>
          <td class="patient-meta" style="padding-top:17px;">+923228117733</td>
          <td>
            <div class="test-name">1 test</div>
            <div class="test-desc">LFTs (T-Bill, ALT, AST, ALP, ALB, ...</div>
          </td>
          <td>
            <div class="price-main">PKR 1,365</div>
            <div class="price-strike">PKR 1,950</div>
          </td>
          <td><span class="status-badge phlebotomist">Phlebotomist Assigned</span></td>
          <td><div class="eta-val">Jun 16, 2026 3:00 PM</div></td>
          <td><div class="date-val">Jun 15, 7:13 PM</div></td>
          <td><a href="#" class="btn-view">View &amp; Update <i class="bi bi-arrow-right"></i></a></td>
        </tr>

        <!-- Row 2 -->
        <tr>
          <td>
            <div class="patient-name">Asma</div>
            <div class="patient-meta">Female</div>
            <div class="patient-meta">4k plaza model town lah...</div>
          </td>
          <td class="patient-meta" style="padding-top:17px;">+92 334 1006561</td>
          <td>
            <div class="test-name">1 test</div>
            <div class="test-desc">Complete Blood Examinatio...</div>
            <div><span class="badge-tag same-day">Reports: Same Day After 2 Hour</span></div>
          </td>
          <td>
            <div class="price-main">PKR 560</div>
            <div class="price-strike">PKR 800</div>
          </td>
          <td><span class="status-badge phlebotomist">Phlebotomist Assigned</span></td>
          <td><div class="eta-val">Jun 13, 2026 9:00 AM</div></td>
          <td><div class="date-val">Jun 12, 11:59 AM</div></td>
          <td><a href="#" class="btn-view">View &amp; Update <i class="bi bi-arrow-right"></i></a></td>
        </tr>

        <!-- Row 3 -->
        <tr>
          <td>
            <div class="patient-name">muhammad ayub</div>
            <div class="patient-meta">58 golfers lane bedian ro...</div>
          </td>
          <td class="patient-meta" style="padding-top:17px;">+923028457799</td>
          <td>
            <div class="test-name">9 tests</div>
            <div class="test-desc">Complete Blood Examinatio...</div>
            <div><span class="badge-tag timings">Reports: 5 timings</span></div>
            <div><span class="badge-tag reports">8/9 reports</span></div>
          </td>
          <td>
            <div class="price-main">PKR 12,880</div>
            <div class="price-strike">PKR 18,400</div>
          </td>
          <td><span class="status-badge collected">Sample Collected</span></td>
          <td><div class="eta-val">Jun 12, 2026 3:00 PM</div></td>
          <td><div class="date-val">Jun 12, 1:37 PM</div></td>
          <td><a href="#" class="btn-view">View &amp; Update <i class="bi bi-arrow-right"></i></a></td>
        </tr>

        <!-- Row 4 -->
        <tr>
          <td>
            <div class="d-flex align-items-center">
              <div class="patient-name">Usman nabi</div>
              <span class="star-badge"><i class="bi bi-star-fill" style="font-size:10px;color:#f59e0b;"></i> 0</span>
            </div>
            <div class="patient-meta">24 yrs · Male</div>
            <div class="patient-meta">Flat no 4b state bank col...</div>
          </td>
          <td class="patient-meta" style="padding-top:17px;">+923054031039</td>
          <td>
            <div class="test-name">4 tests</div>
            <div class="test-desc">HbA1C, RFTs (Renal Function ...</div>
            <div><span class="badge-tag timings">Reports: 3 timings</span></div>
          </td>
          <td>
            <div class="price-main">PKR 4,680</div>
            <div class="price-strike">PKR 7,000</div>
          </td>
          <td><span class="status-badge phlebotomist">Phlebotomist Assigned</span></td>
          <td><div class="eta-val">May 8, 2026 1:30 PM</div></td>
          <td><div class="date-val">May 8, 12:41 PM</div></td>
          <td><a href="#" class="btn-view">View &amp; Update <i class="bi bi-arrow-right"></i></a></td>
        </tr>

      </tbody>
    </table>
  </div>

</div><!-- /main-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function setPill(el) {
    document.querySelectorAll('.status-pill').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
  }
</script>
</body>
</html>