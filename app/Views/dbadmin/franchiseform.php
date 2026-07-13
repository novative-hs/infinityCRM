<?php $isEdit = isset($franchise); ?>
<?= view('templates/header', [
    'pageTitle'  => $isEdit ? 'Edit Franchise' : 'Franchise Registration',
    'activePage' => $isEdit ? 'franchiselist' : 'franchise'
]) ?>

<style>
  .register-card-body { background-color: #fff; }
</style>

<div class="flex-grow-1 d-flex align-items-center justify-content-center py-4">
  <div class="card register-card border-0 p-5" style="width:100%; max-width:900px;">

    <div class="d-flex flex-column text-black align-items-center text-center px-4 pt-2 pb-3">
      <div class="rounded-circle d-flex align-items-center justify-content-center mb-3"
           style="width:56px; height:56px; background:#fff;">
        <i class="ti ti-building-store text-danger fs-3"></i>
      </div>
      <span class="fs-4 fw-bold" style="color:#fff;">Infinity Healthcare</span>
      <small class="mt-1" style="color:#fff;"><?= $isEdit ? 'Edit Franchise' : 'Franchise Registration Form' ?></small>
    </div>

    <div class="register-card-body rounded-4 p-4 mt-2 p-md-5" style="margin-top:-1.5rem;">

      <h2 class="fw-bold fs-4 mb-4" style="color:#961914;">
        <?= $isEdit ? 'Edit Franchise' : 'Register a Franchise' ?>
      </h2>

      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2 small"><?= session()->getFlashdata('error') ?></div>
      <?php endif; ?>

      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2 small"><?= session()->getFlashdata('success') ?></div>
      <?php endif; ?>

      <form action="<?= $isEdit ? base_url('franchise/' . $franchise['id'] . '/update') : base_url('franchise/store') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="row">

          <div class="col-md-6 mb-3">
            <label class="form-label1 fw-medium">Franchise Name</label>
            <div class="input-group">
                <span class="input-group-text input-group-text-light"><i class="ti ti-building-store"></i></span>
                <input type="text" name="name" id="franchise_name" class="form-control input-light"
                    <?php if (!$isEdit): ?>placeholder="ABC Diagnostics - Lahore"<?php endif; ?>
                    value="<?= old('name', $isEdit ? $franchise['name'] : '') ?>" required/>
            </div>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label1 fw-medium">Lab</label>
            <div class="input-group">
              <span class="input-group-text input-group-text-light"><i class="ti ti-flask"></i></span>
              <select name="lab_id" class="form-select input-light" required>
                <option value="">Select Lab</option>
                <?php foreach ($labs as $lab): ?>
                  <option value="<?= $lab['id'] ?>" <?= old('lab_id', $isEdit ? $franchise['lab_id'] : '') == $lab['id'] ? 'selected' : '' ?>>
                    <?= esc($lab['lab_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label1 fw-medium">City</label>
            <div class="input-group">
              <span class="input-group-text input-group-text-light"><i class="ti ti-map-pin"></i></span>
              <select name="city_id" class="form-select input-light" required>
                <option value="">Select City</option>
                <?php foreach ($cities as $city): ?>
                  <option value="<?= $city['id'] ?>" <?= old('city_id', $isEdit ? $franchise['city_id'] : '') == $city['id'] ? 'selected' : '' ?>>
                    <?= esc($city['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label1 fw-medium">Contact Number</label>
            <div class="input-group">
              <span class="input-group-text input-group-text-light"><i class="ti ti-phone"></i></span>
              <input type="text" name="contact_number" id="phone" class="form-control input-light"
                     <?php if (!$isEdit): ?>placeholder="03XX-XXXXXXX"<?php endif; ?>
                     value="<?= old('contact_number', $isEdit ? $franchise['contact_number'] : '') ?>" maxlength="12"/>
            </div>
            <small class="text-danger d-none" id="phone-error">Enter a valid Pakistani mobile (03XX-XXXXXXX) or landline (0XX-XXXXXXX) number.</small>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label1 fw-medium">Max Discount %</label>
            <div class="input-group">
              <span class="input-group-text input-group-text-light"><i class="ti ti-discount-2"></i></span>
              <input type="number" name="discount" class="form-control input-light"
                     <?php if (!$isEdit): ?>placeholder="15"<?php endif; ?>
                     min="0" max="100" value="<?= old('discount', $isEdit ? $franchise['discount'] : '') ?>" required/>
            </div>
          </div>

          <?php if ($isEdit): ?>
          <div class="col-md-6 mb-3">
            <label class="form-label1 fw-medium">Status</label>
            <div class="input-group">
              <span class="input-group-text input-group-text-light"><i class="ti ti-toggle-right"></i></span>
              <select name="status" class="form-select input-light">
                <option value="active" <?= $franchise['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $franchise['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
              </select>
            </div>
          </div>
          <?php endif; ?>

          <div class="col-md-6 mb-3">
            <label class="form-label1 fw-medium">Email Address</label>
            <div class="input-group">
              <span class="input-group-text input-group-text-light"><i class="ti ti-mail"></i></span>
              <input type="email" name="email" id="email" class="form-control input-light"
                     <?php if (!$isEdit): ?>placeholder="franchise@healthcrm.com"<?php endif; ?>
                     value="<?= old('email', $isEdit ? $franchise['email'] : '') ?>" required/>
            </div>
            <small class="text-danger d-none" id="email-error">Enter a valid email address.</small>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label1 fw-medium">
              <?= $isEdit ? 'New Password <span class="text-muted small">(leave blank to keep current)</span>' : 'Password' ?>
            </label>
            <div class="input-group">
              <span class="input-group-text input-group-text-light"><i class="ti ti-lock"></i></span>
              <input type="password" name="password" id="password" class="form-control input-light"
                     placeholder="••••••••" <?= $isEdit ? '' : 'required' ?>/>
              <button type="button" class="btn input-group-text-light" onclick="togglePw('password','eye-icon-1')">
                <i class="ti ti-eye" id="eye-icon-1"></i>
              </button>
            </div>
          </div>

          <?php if (!$isEdit): ?>
          <div class="col-md-6 mb-3">
            <label class="form-label1 fw-medium">Confirm Password</label>
            <div class="input-group">
              <span class="input-group-text input-group-text-light"><i class="ti ti-lock"></i></span>
              <input type="password" name="confirm_password" id="confirm_password" class="form-control input-light"
                     placeholder="••••••••" required/>
              <button type="button" class="btn input-group-text-light" onclick="togglePw('confirm_password','eye-icon-2')">
                <i class="ti ti-eye" id="eye-icon-2"></i>
              </button>
            </div>
            <small class="text-danger d-none" id="pw-mismatch">Passwords do not match</small>
          </div>
          <?php endif; ?>

        </div>

        <?php if (!$isEdit): ?>
          <input type="hidden" name="role" value="franchise"/>
        <?php endif; ?>

        <button type="submit" class="btn text-white w-100 fw-semibold py-2 mt-2" style="background:#961914">
          <?= $isEdit ? 'Update Franchise' : 'Register Franchise' ?>
        </button>

      </form>

      <p class="text-center text-muted mt-4 mb-0" style="font-size:11px;">
        © 2026 HealthCRM · All rights reserved
      </p>

    </div>
  </div>
</div>

<script>
 const nameField = document.getElementById('franchise_name');

nameField.addEventListener('input', function () {
  const cursorPos = this.selectionStart;
  const val = this.value;

  // Capitalize the first letter after the start of the string AND after every space
  const fixed = val.replace(/(^|\s)([a-zA-Z])/g, (match, boundary, letter) =>
    boundary + letter.toUpperCase()
  );

  if (fixed !== val) {
    this.value = fixed;
    this.setSelectionRange(cursorPos, cursorPos); // preserve cursor position
  }
});

  function togglePw(inputId, iconId) {
    const inp  = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    inp.type   = inp.type === 'password' ? 'text' : 'password';
    icon.className = inp.type === 'password' ? 'ti ti-eye' : 'ti ti-eye-off';
  }

  const phoneField   = document.getElementById('phone');
  const phoneError   = document.getElementById('phone-error');
  const phonePattern = /^(03\d{2}-\d{7}|0(?!3)\d{2}-\d{7})$/;

  phoneField.addEventListener('input', function () {
    let digits = this.value.replace(/\D/g, '');
    const isMobile = digits.length < 2 || digits[1] === '3';
    const maxLen = isMobile ? 11 : 10;
    digits = digits.slice(0, maxLen);
    const splitAt = isMobile ? 4 : 3;
    this.value = digits.length > splitAt
      ? digits.slice(0, splitAt) + '-' + digits.slice(splitAt)
      : digits;
    if (phonePattern.test(this.value)) phoneError.classList.add('d-none');
  });

  phoneField.addEventListener('blur', function () {
    const valid = this.value === '' || phonePattern.test(this.value);
    phoneError.classList.toggle('d-none', valid);
  });

  const emailField = document.getElementById('email');
  const emailError = document.getElementById('email-error');
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  emailField.addEventListener('input', function () {
    if (emailPattern.test(this.value)) emailError.classList.add('d-none');
  });

  emailField.addEventListener('blur', function () {
    const valid = this.value === '' || emailPattern.test(this.value);
    emailError.classList.toggle('d-none', valid);
  });

  <?php if (!$isEdit): ?>
  const pwField      = document.getElementById('password');
  const confirmField = document.getElementById('confirm_password');

  function checkMatch() {
    const mismatch = confirmField.value.length > 0 && confirmField.value !== pwField.value;
    document.getElementById('pw-mismatch').classList.toggle('d-none', !mismatch);
  }
  confirmField.addEventListener('input', checkMatch);
  pwField.addEventListener('input', checkMatch);
  <?php endif; ?>
</script>

<?= view('templates/footer') ?>