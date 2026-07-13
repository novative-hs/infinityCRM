<?php

$genders = $genders ?? ['Male', 'Female', 'Other'];


$tests = $tests ?? [
    ['id' => 1, 'test_code' => '1207', 'test_name' => 'Semen Analysis', 'rate' => 3000],
    ['id' => 2, 'test_code' => '2352', 'test_name' => '5 HIAA (24 Hrs Urine)', 'rate' => 4800],
    ['id' => 3, 'test_code' => '5050', 'test_name' => 'Complete Blood Count (CBC)', 'rate' => 1200],
    ['id' => 4, 'test_code' => '1090', 'test_name' => 'Fasting Blood Glucose', 'rate' => 600],
];

$cities = $cities ?? [
    ['id' => 1, 'name' => 'Rawalpindi'],
    ['id' => 2, 'name' => 'Islamabad'],
    ['id' => 3, 'name' => 'Lahore'],
    ['id' => 4, 'name' => 'Karachi'],
    ['id' => 5, 'name' => 'Peshawar'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>New Lab Booking</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<style>
  .tooltip-danger .tooltip-inner {
    background-color: #dc3545;
    color: #fff;
    font-size: 0.75rem;
    font-weight: 500;
  }
  .tooltip-danger .tooltip-arrow::before {
    border-top-color: #dc3545;
  }
  .tooltip-danger.bs-tooltip-top .tooltip-arrow::before {
    border-top-color: #dc3545;
  }
</style>
<body class="bg-light">
<?= view('templates/header', ['pageTitle' => 'Booking', 'activePage' => 'bookingform']) ?>

<div class="container py-4" style="max-width: 860px;">

  
  <div class="mb-4">
    <h2 class="fw-bold mb-1" style="color:#154c80;">New Lab Booking</h2>
    <p class="text-muted mb-0">Fill in patient details and test information</p>
  </div>

<form method="post" action="<?= site_url('booking/add') ?>" id="booking_form">
      <?= csrf_field() ?>


    <div class="card2 shadow-sm mb-4">
      <div class="card-body p-4">
        <h5 class="fw-bold mb-4">Patient Details</h5>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label for="booking_person_name" class="form-label2 fw-semibold">
              Booking Person Name <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="booking_person_name" name="booking_person_name"
                   placeholder="Full name" pattern="[A-Za-z\s]+" title="Letters and spaces only"
                   required>
          </div>
           <div class="col-md-6">
            <label for="patient_name" class="form-label2 fw-semibold">
              Patient Name <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control" id="patient_name" name="patient_name"
                   placeholder="Full name" pattern="[A-Za-z\s]+" title="Letters and spaces only"
                   required>
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-5">
  <label for="phone_number" class="form-label2 fw-semibold">
    Phone Number <span class="text-danger">*</span>
  </label>
  <input type="text" class="form-control" id="phone_number" name="phone_number"
         inputmode="tel" placeholder="03XX-XXXXXXX or +92XXXXXXXXXX" maxlength="13" required>
  <small class="text-danger d-none" id="phone-error">Enter a valid Pakistani number — mobile (03XX-XXXXXXX), landline (0XX-XXXXXXX), or international (+92XXXXXXXXXX).</small>
</div>
          <div class="col-md-3">
            <label for="age" class="form-label2 fw-semibold">
              Age <span class="text-muted fw-normal">(optional)</span>
            </label>
            <input type="number" class="form-control" id="age" name="age" placeholder="e.g. 35" min="0" max="300">
          </div>
          <div class="col-md-4">
            <label for="gender" class="form-label2 fw-semibold">
              Gender <span class="text-muted fw-normal">(optional)</span>
            </label>
            <select class="form-select" id="gender" name="gender">
              <option selected disabled>Select gender</option>
              <?php foreach ($genders as $g): ?>
                <option value="<?= esc($g) ?>"><?= esc($g) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
        </div>

        <div class="mb-3">
          <label for="home_address" class="form-label2 fw-semibold">
            Home Address <span class="text-danger">*</span>
          </label>
          <textarea class="form-control" id="home_address" name="home_address" rows="2"
                    placeholder="Full home address for sample collection" required></textarea>
        </div>
 
        <!-- <div class="mb-3">
          <label for="pin_location" class="form-label2 fw-semibold">
            <i class="bi bi-geo-alt me-1"></i>Pin Location
            <span class="text-muted fw-normal">(optional — paste Google Maps link)</span>
          </label>
          <input type="url" class="form-control" id="pin_location" name="pin_location"
                 placeholder="https://maps.google.com/...">
        </div> -->

        <div class="mb-3 position-relative">
  <label for="pin_location_search" class="form-label2 fw-semibold">
    <i class="bi bi-geo-alt me-1"></i>Pin Location
    <span class="text-muted fw-normal">(optional — click the box to open the map)</span>
  </label>
  <input type="text" class="form-control mb-2" id="pin_location_search" autocomplete="off"
         placeholder="Search address, area, or landmark…">
  <div class="position-absolute w-100 bg-white border rounded-3 shadow-lg d-none"
       id="pin_search_dropdown" style="z-index: 1050; max-height: 250px; overflow-y: auto; top: 58px;"></div>

  <div class="position-relative d-none" id="pin_map_wrapper">
    <button type="button"
            class="btn btn-sm btn-light border rounded-circle position-absolute d-flex align-items-center justify-content-center"
            id="pin_map_close" title="Close map"
            style="top: 8px; right: 8px; z-index: 1000; width: 32px; height: 32px; padding: 0;">
      <i class="bi bi-x-lg"></i>
    </button>
    <div id="pin_map" style="height: 280px; border-radius: 0.5rem;"></div>
  </div>

  <input type="hidden" name="pin_lat" id="pin_lat">
  <input type="hidden" name="pin_lng" id="pin_lng">
  <input type="hidden" name="pin_address" id="pin_address">
</div>

<div class="row g-3 mb-3">
  <div class="col-12">
    <label for="franchise" class="form-label2 fw-semibold">
      Franchise <span class="text-danger">*</span>
    </label>
    <select class="form-select" id="franchise" name="franchise">
      <option value="" selected disabled>Select franchise</option>
      <?php foreach ($franchises as $f): ?>
        <option value="<?= esc($f['id']) ?>" data-discount="<?= esc($f['discount']) ?>"
                data-city="<?= esc($f['city_name'] ?? '') ?>">
          <?= esc($f['franchise_name'] ?? ('Franchise #' . $f['id'])) ?>
          <?= !empty($f['city_name']) ? ' — ' . esc($f['city_name']) : '' ?>
        </option>
      <?php endforeach; ?>
    </select>
              <small class="d-none" id="home_address_match_note"></small>

    <small class="text-success d-none" id="franchise_match_note"></small>
  </div>
</div>

<div class="mb-0">
          <label for="instructions" class="form-label2 fw-semibold">
            <i class="bi bi-file-text me-1"></i>Instructions / Notes 
            <span class="text-muted fw-normal">(optional — visible to lab team)</span>
          </label>
          <textarea class="form-control" id="instructions" name="instructions" rows="3"
                    placeholder="e.g. Patient is fasting, morning slots preferred, special handling instructions..."></textarea>
        </div>

        <div class="mb-0">
          <label for="medical_history" class="form-label2 fw-semibold">
            <i class="bi bi-file-text me-1"></i>Medical History
            <span class="text-muted fw-normal">(optional — visible to lab team)</span>
          </label>
          <textarea class="form-control" id="medical_history" name="medical_history" rows="3"
                    placeholder="e.g. Patient has diabetes, pollen allergy, shortness of breathe..."></textarea>
        </div>
      </div>
    </div>

    
    <div class="card2 shadow-sm mb-4">
      <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
          <h5 class="fw-bold mb-0">Tests Ordered</h5>
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-tag text-muted"></i>
            <span class="text-nowrap">Default discount:</span>
            <input type="number" class="form-control form-control-sm" id="default_discount"
                   style="width: 70px;" value="0" min="0" max="100">
            <span>%</span>
            <span id="apply_to_all_wrapper" class="d-none">
              <button type="button" id="apply_to_all" class="btn btn-sm btn-outline-primary text-nowrap">
                Apply to all
              </button>
            </span>
          </div>
        </div>

        <div class="mb-3 position-relative">
          <label for="test_search" class="form-label2 fw-semibold">
            Search &amp; Add Tests <span class="text-danger">*</span>
          </label>
          <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
            <input type="text" class="form-control" id="test_search" autocomplete="off"
                   placeholder="Type test name or code (e.g. CBC, Glucose, 5050)…">
          </div>
          <div class="position-absolute w-100 bg-white border rounded-3 shadow-lg mt-1 d-none"
               id="search_dropdown" style="z-index: 1050; max-height: 320px; overflow-y: auto; top: 100%;"></div>
        </div>

        <div class="rounded-3" id="tests_list" style="border: 1px dashed #ced4da;">
  <div class="p-5 text-center" id="no_tests_row">
    <i class="bi bi-flask fs-1 text-muted d-block mb-2"></i>
    <span class="text-muted">No tests added yet. Search above to add tests.</span>
  </div>
</div>
<small class="text-danger d-none mt-2 d-block" id="tests-error">Please add at least one test before submitting.</small>
<small class="text-danger d-none mt-2 d-block" id="discount-limit-error"></small>
      </div>
    </div>

   
    <div class="rounded-3 p-4 mb-4 d-none" id="financial_summary_panel" style="background-color:#edeeee;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold mb-0" style="color:#154c80;">
      <i class="bi bi-flask me-2"></i>Financial Summary
    </h6>
    <span class="small" style="color:#154c80;">Live preview</span>
  </div>

  <div id="summary_line_items" class="border-bottom pb-2 mb-2" style="border-color: rgba(32, 132, 226, 0.15) !important;"></div>

  <div class="d-flex justify-content-between mb-1">
    <span style="color:#154c80;">Original Total (Rack Rate)</span>
    <span style="color:#154c80;" id="summary_original">PKR 0</span>
  </div>
  <div class="d-flex justify-content-between border-bottom pb-2 mb-2" style="border-color: rgba(21,76,128,0.15) !important;">
    <span style="color:#154c80;">Total Discount</span>
    <span style="color:#FF8A80;" id="summary_discount">- PKR 0</span>
  </div>
  <div class="d-flex justify-content-between mb-2">
    <span class="small" style="color:#154c80;">Discount % Used <span class="text-muted">(sum across tests)</span></span>
    <span class="small fw-semibold" id="summary_discount_pct">0%</span>
  </div>
  <div class="d-flex justify-content-between">
    <span class="fw-bold fs-5" style="color:#154c80;">Patient Pays</span>
    <span class="fw-bold fs-5" style="color:#154c80;" id="summary_patient_pays">PKR 0</span>
  </div>

  <div class="rounded-2 p-2 mt-3 text-center fw-semibold d-none" id="prepaid_banner"
       style="background-color: rgba(34,197,94,0.15); color:#0c7a43;"></div>
</div>

   
    <div class="d-flex justify-content-start gap-2 mb-5">
      <a href="<?= site_url('labDashboard/dashboard') ?>" class="btn btn-outline-secondary px-4">
        <i class="bi bi-arrow-left me-1"></i>Cancel
      </a>
         <button type="submit" class="btn px-4 fw-semibold" style="background-color:#154c80;color:#fff;">
        <i class="bi bi-plus-lg me-1"></i>Create Booking
      </button>
    </div>
  </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

//patient's name 
document.getElementById('patient_name').addEventListener('input', function () {
    const pos = this.selectionStart;
    this.value = this.value
        .replace(/[^A-Za-z\s]/g, '')
        .replace(/\b\w/g, c => c.toUpperCase());
    this.setSelectionRange(pos, pos);
});
//booking person name
 document.getElementById('booking_person_name').addEventListener('input', function () {
    const pos = this.selectionStart;
    this.value = this.value
        .replace(/[^A-Za-z\s]/g, '')
        .replace(/\b\w/g, c => c.toUpperCase());
    this.setSelectionRange(pos, pos);

});
document.getElementById('age').addEventListener('input', function () {
    let value = this.value.replace(/\D/g, ''); // digits only, no negatives/decimals
    if (value.length > 3) {
        value = value.slice(0, 3);
    }
    this.value = value;
});

 const phoneField = document.getElementById('phone_number');
const phoneError = document.getElementById('phone-error');
const testsError = document.getElementById('tests-error');
const discountLimitError = document.getElementById('discount-limit-error');
const bookingForm = document.getElementById('booking_form');
  const testsList = document.getElementById('tests_list');
  const noTestsRow = document.getElementById('no_tests_row');
  const searchInput = document.getElementById('test_search');
  const searchDropdown = document.getElementById('search_dropdown');
  function updateEmptyState() {
  const hasTests = !!testsList.querySelector('[data-test-row]');
  noTestsRow.classList.toggle('d-none', hasTests);
  testsList.style.border = hasTests ? '1px solid #dee2e6' : '1px dashed #ced4da';
  applyToAllWrapper.classList.toggle('d-none', !hasTests);
  financialSummaryPanel.classList.toggle('d-none', !hasTests);
  if (hasTests) testsError.classList.add('d-none');
}
bookingForm.addEventListener('submit', function (e) {
  let valid = true;

  if (!phonePattern.test(phoneField.value)) {
    phoneError.classList.remove('d-none');
    valid = false;
  }

  if (!testsList.querySelector('[data-test-row]')) {
    testsError.classList.remove('d-none');
    valid = false;
  }

  // Final safety-net check: recompute the discount-percent SUM from scratch
  // right before submitting, independent of whatever happened while the user
  // was typing. Per-row typing guards can, in principle, be bypassed (fast
  // paste, browser autofill, devtools) — this is the check that actually
  // gates whether the booking can go through.
  const rowsAtSubmit = [...testsList.querySelectorAll('[data-test-row]')];
  if (rowsAtSubmit.length) {
    const totalDiscountUsed = sumOfDiscountPercents(rowsAtSubmit, null, 0);
    if (totalDiscountUsed > maxAllowedDiscount + 0.01) {
      discountLimitError.textContent =
        `Max discount limit is ${maxAllowedDiscount}% for this franchise. You've used ${totalDiscountUsed.toFixed(1)}% across all tests — please reduce before submitting.`;
      discountLimitError.classList.remove('d-none');
      valid = false;
    } else {
      discountLimitError.classList.add('d-none');
    }
  }

  if (!valid) e.preventDefault();
});
  const allTests = <?= json_encode(array_map(fn($t) => [
      'id'    => $t['id'],
      'test_code'  => $t['test_code'],
      'name'  => $t['test_name'],
      'price' => (float) $t['rate'],
  ], $tests), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  const defaultDiscountInput = document.getElementById('default_discount');
  const applyToAllBtn = document.getElementById('apply_to_all');
  const applyToAllWrapper = document.getElementById('apply_to_all_wrapper');
  const financialSummaryPanel = document.getElementById('financial_summary_panel');
  const summaryOriginal = document.getElementById('summary_original');
  const summaryDiscount = document.getElementById('summary_discount');
  const summaryDiscountPct = document.getElementById('summary_discount_pct');
  const summaryPatientPays = document.getElementById('summary_patient_pays');
  const summaryLineItems = document.getElementById('summary_line_items');
  const prepaidBanner = document.getElementById('prepaid_banner');

  let rowCounter = 0;

  function updateEmptyState() {
    const hasTests = !!testsList.querySelector('[data-test-row]');
    noTestsRow.classList.toggle('d-none', hasTests);
    testsList.style.border = hasTests ? '1px solid #dee2e6' : '1px dashed #ced4da';
    applyToAllWrapper.classList.toggle('d-none', !hasTests);
    financialSummaryPanel.classList.toggle('d-none', !hasTests);
  }

  function fmt(n) {
    return 'PKR ' + Number(n).toLocaleString(undefined, {maximumFractionDigits: 0});
  }

  function recalcTotals() {
    let original = 0, payable = 0;
    const paymentCounts = { cash: 0, online: 0, card: 0 };
    const rows = [...testsList.querySelectorAll('[data-test-row]')];

    summaryLineItems.innerHTML = rows.map(row => {
      const price = parseFloat(row.dataset.price || 0);
      const final = parseFloat(row.dataset.final) || parseFloat(row.dataset.price) || 0;
      const discount = parseFloat(row.dataset.discount || 0);
      const name = row.dataset.name || '';
      original += price;
      payable += final;
      if (row.dataset.payment in paymentCounts) paymentCounts[row.dataset.payment]++;

      return `
        <div class="d-flex justify-content-between small mb-1">
          <span style="color:#154c80;">${name} (${discount}% off)</span>
          <span>
            <span class="text-decoration-line-through" style="color:#154c80;">${fmt(price)}</span>
            <span class="text-black fw-semibold">${fmt(final)}</span>
          </span>
        </div>`;
    }).join('');

    summaryOriginal.textContent = fmt(original);
    summaryDiscount.textContent = '- ' + fmt(original - payable);
    summaryPatientPays.textContent = fmt(payable);

    const discountSumUsed = sumOfDiscountPercents(rows, null, 0);
    summaryDiscountPct.textContent = `${discountSumUsed.toFixed(1)}% / ${maxAllowedDiscount}%`;
    summaryDiscountPct.classList.toggle('text-danger', discountSumUsed > maxAllowedDiscount + 0.01);
    summaryDiscountPct.classList.toggle('text-success', discountSumUsed <= maxAllowedDiscount + 0.01);

    const parts = [];
    if (paymentCounts.cash)   parts.push(`${paymentCounts.cash} Cash`);
    if (paymentCounts.online) parts.push(`${paymentCounts.online} Online`);
    if (paymentCounts.card)   parts.push(`${paymentCounts.card} Card`);

    if (parts.length) {
      prepaidBanner.textContent = '✓ ' + parts.join(' · ');
      prepaidBanner.classList.remove('d-none');
    } else {
      prepaidBanner.classList.add('d-none');
    }
  }

  // Sums up the discount % entered on every test row — a straight addition,
  // NOT a price-weighted average. This matches how the franchise's discount
  // limit actually works: it's a percentage budget that each test's discount
  // draws from directly (15% on test A + 7% on test B = 22% used), regardless
  // of how expensive each individual test is.
  function sumOfDiscountPercents(rows, overrideRow, overrideDiscount) {
    let total = 0;
    rows.forEach(r => {
      const discount = (r === overrideRow)
        ? overrideDiscount
        : parseFloat(r.dataset.discount || r.querySelector('.discount-input').value || 0);
      total += discount;
    });
    return total;
  }

  function recalcRow(row) {
    const price = parseFloat(row.dataset.price || 0);
    const discount = parseFloat(row.querySelector('.discount-input').value || 0);
    const final = price - (price * discount / 100);
    const savings = price - final;
    row.dataset.final = final;
    row.dataset.discount = discount;
    row.querySelector('.final-cell').textContent = fmt(final);

    const saveBadge = row.querySelector('.save-badge');
    if (savings > 0) {
      saveBadge.textContent = 'save ' + fmt(savings);
      saveBadge.classList.remove('d-none');
    } else {
      saveBadge.classList.add('d-none');
    }

    recalcTotals();
  }

  function setPaymentState(row, method) {
  row.dataset.payment = method;

  const buttons = {
    cash:   { el: row.querySelector('.payment-cash'),   bg: '#fff3cd', color: '#856404' },
    online: { el: row.querySelector('.payment-online'), bg: '#cfe2ff', color: '#0a58ca' },
    card:   { el: row.querySelector('.payment-card'),   bg: '#d1f5e0', color: '#0c7a43' },
  };

  Object.entries(buttons).forEach(([key, btn]) => {
    if (key === method) {
      btn.el.className = `btn btn-sm rounded-pill payment-${key} fw-semibold border-0`;
      btn.el.style.cssText = `background-color:${btn.bg}; color:${btn.color};`;
    } else {
      btn.el.className = `btn btn-sm rounded-pill payment-${key} border`;
      btn.el.style.cssText = 'background-color:#fff; color:#6c757d; border-color:#ced4da;';
    }
  });

  recalcTotals();
}

  function getAddedTestIds() {
  return new Set(
    [...testsList.querySelectorAll('[data-test-row]')].map(r => r.dataset.testId)
  );
}

  function addTestRow(name, test_code, price, testId) {
    if (!name) return;
    rowCounter++;

    const row = document.createElement('div');
    row.dataset.testRow = 'true';
    row.dataset.price = price;
    row.dataset.name = name;
    row.dataset.testId = String(testId);
    row.className = 'p-3 border-bottom';
    row.innerHTML = `
    
      <div class="d-flex justify-content-between align-items-start" >
        <div>
          <span class="text-muted small me-2">${test_code || ''}</span>
          <span class="fw-semibold">${name}</span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="text-muted text-decoration-line-through">${fmt(price)}</span>
          <button type="button" class="btn btn-sm btn-link text-secondary p-0 remove-row" title="Remove">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
      </div>
      <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-tag text-muted"></i>
          <input type="number" class="form-control form-control-sm discount-input"
                 name="tests[${rowCounter}][discount]"
                 style="width: 70px;" value="${defaultDiscountInput.value || 0}" min="0" max="100">
          <span>%</span>
          <i class="bi bi-arrow-right text-muted"></i>
          <span class="fw-bold final-cell">${fmt(price)}</span>
          <span class="badge bg-success-subtle text-success-emphasis rounded-pill save-badge d-none"></span>
        </div>
        <div class="d-flex gap-2">
  <button type="button" class="btn btn-sm rounded-pill payment-cash border" style="background-color:#fff; color:#6c757d; border-color:#ced4da;">Cash</button>
  <button type="button" class="btn btn-sm rounded-pill payment-online border" style="background-color:#fff; color:#6c757d; border-color:#ced4da;">Online</button>
  <button type="button" class="btn btn-sm rounded-pill payment-card border" style="background-color:#fff; color:#6c757d; border-color:#ced4da;">Card</button>
</div>
      </div>
      <input type="hidden" name="tests[${rowCounter}][test_id]" value="${testId || ''}">
      <input type="hidden" name="tests[${rowCounter}][test_code]" value="${test_code || ''}">
      <input type="hidden" name="tests[${rowCounter}][name]" value="${name}">
      <input type="hidden" name="tests[${rowCounter}][price]" value="${price}">
      <input type="hidden" class="payment-input" name="tests[${rowCounter}][payment]" value="cash">
    
    `;
    testsList.appendChild(row);
    updateEmptyState();

const discInput = row.querySelector('.discount-input');
discInput.dataset.lastValid = discInput.value || 0;
discInput.addEventListener('input', () => {
  // Basic sanity bounds only (0-100) — the franchise's actual limit is now
  // enforced as a SUM across every test row's discount %, not on this single input.
  let proposed = parseFloat(discInput.value || 0);
  if (isNaN(proposed)) proposed = 0;
  proposed = Math.min(Math.max(proposed, 0), 100);

  const rows = [...testsList.querySelectorAll('[data-test-row]')];
  const projectedSum = sumOfDiscountPercents(rows, row, proposed);

  if (projectedSum > maxAllowedDiscount + 0.01) {
    // Reject this edit — the SUM of all tests' discounts would exceed the
    // franchise's limit — and restore the last value that was compliant.
    discInput.value = discInput.dataset.lastValid;
    showDiscountLimitWarning(discInput,
      `Max discount limit is ${maxAllowedDiscount}% for this franchise (${projectedSum.toFixed(1)}% would be used across all tests)`);
  } else {
    discInput.value = proposed;
    discInput.dataset.lastValid = proposed;
  }
  recalcRow(row);
});    row.querySelector('.remove-row').addEventListener('click', () => {
      row.remove();
      updateEmptyState();
      recalcTotals();
    });
   row.querySelector('.payment-cash').addEventListener('click', () => {
  setPaymentState(row, 'cash');
  row.querySelector('.payment-input').value = 'cash';
});
row.querySelector('.payment-online').addEventListener('click', () => {
  setPaymentState(row, 'online');
  row.querySelector('.payment-input').value = 'online';
});
row.querySelector('.payment-card').addEventListener('click', () => {
  setPaymentState(row, 'card');
  row.querySelector('.payment-input').value = 'card';
});

   setPaymentState(row, 'cash');
row.querySelector('.payment-input').value = 'cash';
  }

 function renderSearchDropdown(query) {
  const q = query.trim().toLowerCase();
  if (!q) {
    searchDropdown.classList.add('d-none');
    searchDropdown.innerHTML = '';
    return;
  }

  const discount = parseFloat(defaultDiscountInput.value || 0);
  const addedIds = getAddedTestIds();   // <-- track added tests
  const matches = allTests
    .filter(t => t.name.toLowerCase().includes(q) || String(t.test_code).toLowerCase().includes(q))
    .slice(0, 30);

  if (matches.length === 0) {
    searchDropdown.innerHTML = '<div class="p-3 text-muted text-center small">No matching tests found.</div>';
    searchDropdown.classList.remove('d-none');
    return;
  }

  searchDropdown.innerHTML = matches.map(t => {
    const isAdded = addedIds.has(String(t.id));
    const final = t.price - (t.price * discount / 100);
    const savings = t.price - final;

    if (isAdded) {
      return `
        <div class="d-flex justify-content-between align-items-start p-3 border-bottom"
             style="opacity:0.6; cursor:not-allowed;" data-id="${t.id}">
          <div>
            <span class="fw-semibold text-muted">${t.name}</span>
            <span class="text-muted small ms-2">${t.test_code}</span>
            <span class="text-success small ms-2"><i class="bi bi-check-lg"></i> Added</span>
          </div>
          <div class="text-end">
            <div class="fw-bold text-muted">${fmt(t.price)}</div>
          </div>
        </div>`;
    }

    return `
      <div class="d-flex justify-content-between align-items-start p-3 border-bottom search-result-item"
           role="button" data-id="${t.id}" data-code="${t.test_code}" data-name="${t.name}" data-price="${t.price}">
        <div>
          <span class="fw-semibold">${t.name}</span>
          <span class="text-muted small ms-2">${t.test_code}</span>
        </div>
        <div class="text-end">
          ${savings > 0 ? `<div class="text-muted text-decoration-line-through small">${fmt(t.price)}</div>` : ''}
          <div class="fw-bold ${savings > 0 ? 'text-success' : 'text-dark'}">${fmt(final)}</div>
          ${savings > 0 ? `<div class="small text-success">save ${fmt(savings)}</div>` : ''}
        </div>
      </div>`;
  }).join('');

  searchDropdown.classList.remove('d-none');

  // only attach click/hover handlers to the still-addable items
  searchDropdown.querySelectorAll('.search-result-item').forEach(item => {
    item.addEventListener('mouseenter', () => item.classList.add('bg-light'));
    item.addEventListener('mouseleave', () => item.classList.remove('bg-light'));
    item.addEventListener('click', () => {
      addTestRow(item.dataset.name, item.dataset.code, item.dataset.price, item.dataset.id);
      searchDropdown.classList.add('d-none');
      searchDropdown.innerHTML = '';
    });
  });
}
  searchInput.addEventListener('input', () => renderSearchDropdown(searchInput.value));
  searchInput.addEventListener('focus', () => {
    if (searchInput.value) renderSearchDropdown(searchInput.value);
  });
  searchInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      const first = searchDropdown.querySelector('.search-result-item');
      if (first) first.click();
    }
  });
  document.addEventListener('click', (e) => {
    if (!e.target.closest('#test_search') && !e.target.closest('#search_dropdown')) {
      searchDropdown.classList.add('d-none');
    }
  });

  
  defaultDiscountInput.addEventListener('input', () => {
    if (!searchDropdown.classList.contains('d-none')) {
      renderSearchDropdown(searchInput.value);
    }
  });

  applyToAllBtn.addEventListener('click', () => {
  const rows = [...testsList.querySelectorAll('[data-test-row]')];
  const rowCount = rows.length;
  // Applying the SAME % to every row means the sum = value × rowCount, so with
  // more than one test the per-row value must be capped at max ÷ rowCount to
  // keep the total within the franchise's limit (e.g. 20% limit ÷ 2 tests = 10% each).
  const perRowCap = rowCount > 0 ? maxAllowedDiscount / rowCount : maxAllowedDiscount;
  const requested = parseFloat(defaultDiscountInput.value || 0);
  const capped = Math.min(requested, perRowCap);

  if (requested > perRowCap && rowCount > 1) {
    showDiscountLimitWarning(defaultDiscountInput,
      `With ${rowCount} tests, each can only get up to ${perRowCap.toFixed(1)}% to stay within the ${maxAllowedDiscount}% total limit`);
  }

  rows.forEach(row => {
    const discInput = row.querySelector('.discount-input');
    discInput.value = capped;
    discInput.dataset.lastValid = capped;
    recalcRow(row);
  });
});
  const phonePattern = /^(03\d{2}-\d{7}|0(?!3)\d{2}-\d{7}|\+92\d{9,10})$/;

phoneField.addEventListener('input', function () {
  let value = this.value;

  if (value.startsWith('+')) {
    // International — keep the + and digits only, no dash formatting
    value = '+' + value.slice(1).replace(/\D/g, '');
    this.value = value.slice(0, 13); // +92 plus up to 10 digits
  } else {
    // Local — existing mobile/landline auto-dash logic
    let digits = value.replace(/\D/g, '');
    const isMobile = digits.length < 2 || digits[1] === '3';
    const maxLen = isMobile ? 11 : 10;
    digits = digits.slice(0, maxLen);

    const splitAt = isMobile ? 4 : 3;
    this.value = digits.length > splitAt
      ? digits.slice(0, splitAt) + '-' + digits.slice(splitAt)
      : digits;
  }

  if (phonePattern.test(this.value)) phoneError.classList.add('d-none');
});

phoneField.addEventListener('blur', function () {
  phoneError.classList.toggle('d-none', phonePattern.test(this.value));
});


  // ---- Pin location: map only opens when the search box is focused, closes via the × button ----

  const defaultCenter = [33.6844, 73.0479]; // Rawalpindi/Islamabad fallback

  const pinSearchInput = document.getElementById('pin_location_search');
  const pinDropdown     = document.getElementById('pin_search_dropdown');
  const pinMapWrapper   = document.getElementById('pin_map_wrapper');
  const pinMapCloseBtn  = document.getElementById('pin_map_close');
  const franchiseSelect = document.getElementById('franchise');
  const franchiseMatchNote = document.getElementById('franchise_match_note');
  let searchDebounce;
  let pinMap, pinMarker;
  let pinMapInitialized = false;

  function updatePinFields(lat, lng, address) {
    document.getElementById('pin_lat').value = lat;
    document.getElementById('pin_lng').value = lng;
    document.getElementById('pin_address').value = address || '';
  }

  function movePin(lat, lng, address, recenter = true) {
    const latLng = [lat, lng];
    pinMarker.setLatLng(latLng);
    if (recenter) { pinMap.setView(latLng, 16); }
    updatePinFields(lat, lng, address);
  }

  // ---- City → franchise matching ----
  // Nominatim's addressdetails=1 gives structured fields (city/town/village/county/state)
  // instead of us having to regex-parse the free-text display_name, which is far less reliable.
  function extractCity(addr) {
    if (!addr) return '';
    return addr.city || addr.town || addr.village || addr.municipality ||
           addr.county || addr.state_district || addr.state || '';
  }

  function normalize(str) {
    return (str || '').trim().toLowerCase();
  }

  // Tries the structured city field first. If that doesn't line up with any
  // franchise's city, falls back to scanning the FULL address string for any
  // franchise's city name — covers cases where Nominatim tags the real city
  // as a suburb/neighbourhood/state_district instead of "city".
  // noteEl lets callers show the result next to whichever field triggered the lookup
  // (Pin Location vs. Home Address) — defaults to the Pin Location note.
  function matchFranchiseFromLocation(addr, fullAddress, noteEl = franchiseMatchNote) {
    const options = [...franchiseSelect.options].filter(o => o.value);
    const cityGuess = extractCity(addr);

    let match = null;
    let matchedOn = '';

    if (cityGuess) {
      const norm = normalize(cityGuess);
      match = options.find(o => normalize(o.dataset.city) === norm);
      if (!match) {
        match = options.find(o => o.dataset.city &&
          (norm.includes(normalize(o.dataset.city)) || normalize(o.dataset.city).includes(norm)));
      }
      if (match) matchedOn = cityGuess;
    }

    // Fallback: does any franchise's city name literally appear anywhere
    // in the full address string?
    if (!match && fullAddress) {
      const normFull = normalize(fullAddress);
      match = options.find(o => o.dataset.city && normFull.includes(normalize(o.dataset.city)));
      if (match) matchedOn = match.dataset.city;
    }

    if (match) {
      franchiseSelect.value = match.value;
      franchiseSelect.dispatchEvent(new Event('change'));
      noteEl.textContent = `✓ Auto-selected based on address (${matchedOn})`;
      noteEl.classList.remove('d-none', 'text-warning');
      noteEl.classList.add('text-success');
    } else {
      noteEl.textContent = cityGuess
        ? `No franchise found for "${cityGuess}" — please select manually`
        : `Couldn't detect a city from this address — please select manually`;
      noteEl.classList.remove('d-none', 'text-success');
      noteEl.classList.add('text-warning');
    }
  }

  function reverseGeocode(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&addressdetails=1&lat=${lat}&lon=${lng}`)
      .then(res => res.json())
      .then(data => {
        const address = data.display_name || '';
        pinSearchInput.value = address;
        updatePinFields(lat, lng, address);
        matchFranchiseFromLocation(data.address, data.display_name, franchiseMatchNote);
      })
      .catch(() => updatePinFields(lat, lng, ''));
  }

  // ---- Home Address field: geocode on pause-while-typing / blur, and auto-match ----
  const homeAddressInput = document.getElementById('home_address');
  const homeAddressMatchNote = document.getElementById('home_address_match_note');
  let homeAddressDebounce;

  function geocodeHomeAddress() {
    const q = homeAddressInput.value.trim();
    if (q.length < 4) return; // too short to be a real address yet

    fetch(`https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&q=${encodeURIComponent(q)}&countrycodes=pk&limit=1`)
      .then(res => res.json())
      .then(results => {
        if (!results.length) {
          homeAddressMatchNote.textContent = `Couldn't recognize that address — please select franchise manually`;
          homeAddressMatchNote.classList.remove('d-none', 'text-success');
          homeAddressMatchNote.classList.add('text-warning');
          return;
        }
        const r = results[0];
        matchFranchiseFromLocation(r.address, r.display_name, homeAddressMatchNote);
      })
      .catch(() => {
        homeAddressMatchNote.textContent = `Address lookup failed — please select franchise manually`;
        homeAddressMatchNote.classList.remove('d-none', 'text-success');
        homeAddressMatchNote.classList.add('text-warning');
      });
  }

  // Debounced as the user types (stops ~600ms after they pause) and also
  // on blur, so it fires even if they tab away immediately after pasting.
  homeAddressInput.addEventListener('input', () => {
    clearTimeout(homeAddressDebounce);
    homeAddressDebounce = setTimeout(geocodeHomeAddress, 600);
  });
  homeAddressInput.addEventListener('blur', () => {
    clearTimeout(homeAddressDebounce);
    geocodeHomeAddress();
  });

  // Map is created lazily (only the first time it's needed), since initializing
  // Leaflet inside a hidden container produces a blank/grey map.
  function initPinMap() {
    if (pinMapInitialized) return;

    pinMap = L.map('pin_map').setView(defaultCenter, 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors',
      maxZoom: 19,
    }).addTo(pinMap);

    pinMarker = L.marker(defaultCenter, { draggable: true }).addTo(pinMap);

    pinMarker.on('dragend', () => {
      const { lat, lng } = pinMarker.getLatLng();
      reverseGeocode(lat, lng);
    });

    pinMap.on('click', (e) => {
      pinMarker.setLatLng(e.latlng);
      reverseGeocode(e.latlng.lat, e.latlng.lng);
    });

    pinMapInitialized = true;
  }

  function showPinMap() {
    pinMapWrapper.classList.remove('d-none');
    initPinMap();
    // Nudge Leaflet to recalculate tile size now that the container is visible.
    setTimeout(() => pinMap.invalidateSize(), 80);
  }

  function hidePinMap() {
    pinMapWrapper.classList.add('d-none');
  }

  pinSearchInput.addEventListener('focus', () => {
    showPinMap();
  });

  pinMapCloseBtn.addEventListener('click', () => {
    hidePinMap();
  });

  pinSearchInput.addEventListener('input', function () {
    clearTimeout(searchDebounce);
    const q = this.value.trim();

    if (!q) {
      pinDropdown.classList.add('d-none');
      pinDropdown.innerHTML = '';
      return;
    }

    // Nominatim's usage policy caps free requests at ~1/sec — debounce keeps us well under that.
    searchDebounce = setTimeout(() => {
      fetch(`https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&q=${encodeURIComponent(q)}&countrycodes=pk&limit=5`)
        .then(res => res.json())
        .then(results => {
          if (!results.length) {
            pinDropdown.innerHTML = '<div class="p-3 text-muted text-center small">No matches found.</div>';
            pinDropdown.classList.remove('d-none');
            return;
          }

          pinDropdown.innerHTML = results.map((r, i) => `
            <div class="p-2 border-bottom pin-result-item" role="button" data-idx="${i}"
                 data-lat="${r.lat}" data-lon="${r.lon}" data-address="${r.display_name.replace(/"/g, '&quot;')}">
              <small>${r.display_name}</small>
            </div>`).join('');
          pinDropdown.classList.remove('d-none');

          pinDropdown.querySelectorAll('.pin-result-item').forEach(item => {
            item.addEventListener('mouseenter', () => item.classList.add('bg-light'));
            item.addEventListener('mouseleave', () => item.classList.remove('bg-light'));
            item.addEventListener('click', () => {
              const result = results[parseInt(item.dataset.idx, 10)];
              showPinMap();
              movePin(parseFloat(item.dataset.lat), parseFloat(item.dataset.lon), item.dataset.address);
              pinSearchInput.value = item.dataset.address;
              pinDropdown.classList.add('d-none');
              pinDropdown.innerHTML = '';
              matchFranchiseFromLocation(result.address, result.display_name, franchiseMatchNote);
            });
          });
        });
    }, 400);
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('#pin_location_search') && !e.target.closest('#pin_search_dropdown')) {
      pinDropdown.classList.add('d-none');
    }
  });

  //track the discount limit 
let maxAllowedDiscount = 100; // no cap until a franchise is chosen

function getSelectedFranchiseDiscount() {
  const opt = franchiseSelect.options[franchiseSelect.selectedIndex];
  const val = opt ? parseFloat(opt.dataset.discount) : NaN;
  return isNaN(val) ? 100 : val;
}

// Shows a temporary inline warning next to whichever input triggered it
// Gets (or creates) a Bootstrap Tooltip instance attached to this input
function getOrCreateTooltip(inputEl) {
  let tooltip = bootstrap.Tooltip.getInstance(inputEl);
  if (!tooltip) {
    tooltip = new bootstrap.Tooltip(inputEl, {
      title: '',
      trigger: 'manual',
      placement: 'top',
      customClass: 'tooltip-danger',
    });
  }
  return tooltip;
}

function showDiscountLimitWarning(inputEl, message) {
  const tooltip = getOrCreateTooltip(inputEl);

  inputEl.setAttribute('data-bs-original-title', message || `Max ${maxAllowedDiscount}% for this franchise`);
  inputEl.classList.add('is-invalid');

  tooltip.show();

  clearTimeout(inputEl._tooltipHideTimer);
  inputEl._tooltipHideTimer = setTimeout(() => {
    tooltip.hide();
    inputEl.classList.remove('is-invalid');
  }, 1800);
}

function enforceDiscountLimit(inputEl, previousValidValue) {
  const val = parseFloat(inputEl.value || 0);
  if (val > maxAllowedDiscount) {
    inputEl.value = previousValidValue;
    showDiscountLimitWarning(inputEl);
    return previousValidValue;
  }
  if (val < 0) {
    inputEl.value = 0;
    return 0;
  }
  return val;
}

franchiseSelect.addEventListener('change', function () {
  maxAllowedDiscount = getSelectedFranchiseDiscount();

  // Re-check default discount field silently against the new max
  if (parseFloat(defaultDiscountInput.value || 0) > maxAllowedDiscount) {
    defaultDiscountInput.value = maxAllowedDiscount;
  }

  // Discount limit is a SUM across every test's discount %, not per-row. If the
  // tests already added add up to more than the new franchise's allowed %,
  // scale every row's discount down proportionally so the sum lands exactly
  // at the new limit — this preserves the relative discount weighting between
  // tests instead of just flattening every row to the same capped value.
  const rows = [...testsList.querySelectorAll('[data-test-row]')];
  if (rows.length) {
    const currentSum = sumOfDiscountPercents(rows, null, 0);
    if (currentSum > maxAllowedDiscount + 0.01) {
      const scale = maxAllowedDiscount / currentSum;
      rows.forEach(row => {
        const discInput = row.querySelector('.discount-input');
        const scaledVal = Math.round(parseFloat(discInput.value || 0) * scale * 100) / 100;
        discInput.value = scaledVal;
        discInput.dataset.lastValid = scaledVal;
        recalcRow(row);
      });
      showDiscountLimitWarning(defaultDiscountInput,
        `Discounts scaled down to fit this franchise's ${maxAllowedDiscount}% limit`);
    }
  }
});

let lastValidDefaultDiscount = defaultDiscountInput.value || 0;

defaultDiscountInput.addEventListener('input', () => {
  lastValidDefaultDiscount = enforceDiscountLimit(defaultDiscountInput, lastValidDefaultDiscount);
  if (!searchDropdown.classList.contains('d-none')) {
    renderSearchDropdown(searchInput.value);
  }
});
</script>

</body>
</html>

<?= view('templates/footer') ?>