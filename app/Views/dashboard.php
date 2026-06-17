<<<<<<< HEAD
<?= view('templates/header', ['pageTitle' => 'Dashboard', 'activePage' => 'dashboard']) ?>

  <div class="flex-grow-1 d-flex align-items-center justify-content-center">
    <div class="navbar text-center p-5 rounded-4 border border-secondary" style="max-width:500px; width:100%;">
      <div class="card-body rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width:64px; height:100px;">
        <i class="ti ti-home-2 text-white" style="font-size:48px;"></i>
      </div>
      <h1 class="fw-semibold text-white mb-2">
        Welcome, <?= session()->get('user_name') ?> 👋
      </h1>
      <p class="text-white mb-4">
        You are logged in as <span class="text-white fw-medium"><?= session()->get('user_role') ?></span>.
        Use the navigation above to get started.
      </p>
    </div>
  </div>

<?= view('templates/footer') ?>
=======
<!DOCTYPE html>
<html>
<head><title>Dashboard</title></head>
<body style="background:#0f1923; color:#fff; font-family:sans-serif; padding:2rem;">
  <h1>Welcome, <?= session()->get('user_name') ?> 👋</h1>
  <p>Role: <?= session()->get('user_role') ?></p>
  <a href="<?= base_url('auth/logout') ?>" style="color:#1d9e75;">Logout</a>
</body>
</html>
>>>>>>> 851e8f62f279c469df2a38736bcf344049c76941
