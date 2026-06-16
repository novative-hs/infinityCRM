<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>HealthCRM - Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh;
      background: #0f1923;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Segoe UI', sans-serif;
    }
    .login-card {
      background: #1a2535;
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 14px;
      padding: 2.5rem 2rem;
      width: 100%;
      max-width: 420px;
    }
    .logo-wrap {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-bottom: 2rem;
    }
    .logo-icon {
      width: 40px;
      height: 40px;
      background: #1d9e75;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 20px;
    }
    .logo-text { font-size: 20px; font-weight: 600; color: #fff; }
    .logo-text span { color: #1d9e75; }
    h2 { color: #fff; font-size: 20px; font-weight: 500; text-align: center; margin-bottom: 4px; }
    .sub { color: rgba(255,255,255,0.4); font-size: 13px; text-align: center; margin-bottom: 2rem; }
    .form-label { color: rgba(255,255,255,0.55); font-size: 13px; margin-bottom: 6px; }
    .input-wrap { position: relative; }
    .input-wrap i.icon-left {
      position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
      color: rgba(255,255,255,0.3); font-size: 16px; pointer-events: none;
    }
    .input-wrap input {
      width: 100%;
      background: #0f1923;
      border: 1px solid rgba(255,255,255,0.12);
      border-radius: 8px;
      color: #fff;
      font-size: 14px;
      padding: 10px 40px 10px 38px;
      outline: none;
      transition: border-color 0.15s;
    }
    .input-wrap input:focus { border-color: #1d9e75; }
    .input-wrap input::placeholder { color: rgba(255,255,255,0.2); }
    .eye-btn {
      position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
      background: none; border: none; color: rgba(255,255,255,0.3);
      cursor: pointer; font-size: 16px; padding: 0;
    }
    .forgot { text-align: right; }
    .forgot a { color: #1d9e75; font-size: 12px; text-decoration: none; }
    .btn-login {
      width: 100%;
      background: #1d9e75;
      border: none;
      border-radius: 8px;
      color: #fff;
      font-size: 14px;
      font-weight: 500;
      padding: 11px;
      cursor: pointer;
      transition: background 0.15s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }
    .btn-login:hover { background: #0f6e56; }
    .divider {
      display: flex; align-items: center; gap: 10px; margin: 1.5rem 0;
    }
    .divider hr { flex: 1; border-color: rgba(255,255,255,0.08); }
    .divider span { color: rgba(255,255,255,0.25); font-size: 12px; }
    .footer-note { text-align: center; font-size: 12px; color: rgba(255,255,255,0.2); margin-top: 1.5rem; }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="logo-wrap">
      <div class="logo-icon"><i class="ti ti-heart-plus"></i></div>
      <span class="logo-text">Health<span>CRM</span></span>
    </div>

    <h2>Welcome back</h2>
    <p class="sub">Home Sample Collection Management</p>
<?php if (session()->getFlashdata('error')): ?>
  <div style="background:#ff4444; color:#fff; padding:10px 14px; border-radius:8px; font-size:13px; margin-bottom:1rem;">
    <?= session()->getFlashdata('error') ?>
  </div>
<?php endif; ?>
    <form action="<?= base_url('auth/login') ?>" method="POST">
      <?= csrf_field() ?>

      <div class="mb-3">
        <label class="form-label">Email address</label>
        <div class="input-wrap">
          <i class="ti ti-mail icon-left"></i>
          <input type="email" name="email" placeholder="admin@healthcrm.com" required />
        </div>
      </div>

      <div class="mb-2">
        <label class="form-label">Password</label>
        <div class="input-wrap">
          <i class="ti ti-lock icon-left"></i>
          <input type="password" name="password" id="password" placeholder="••••••••" required />
          <button type="button" class="eye-btn" onclick="togglePw()">
            <i class="ti ti-eye" id="eye-icon"></i>
          </button>
        </div>
      </div>

     

      <button type="submit" class="btn-login mt-4">
        <i class="ti ti-login"></i> Sign in
      </button>
    </form>

    <div class="divider"><hr/><span>secured portal</span><hr/></div>
    <p class="footer-note">© 2026 HealthCRM · All rights reserved</p>
  </div>

  <script>
    function togglePw() {
      const inp = document.getElementById('password');
      const icon = document.getElementById('eye-icon');
      inp.type = inp.type === 'password' ? 'text' : 'password';
      icon.className = inp.type === 'password' ? 'ti ti-eye' : 'ti ti-eye-off';
    }
  </script>
</body>
</html>