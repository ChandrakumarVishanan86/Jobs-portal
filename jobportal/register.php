<?php
require __DIR__ . '/config/config.php';

if (Auth::check()) redirect(Auth::home());

$role = $_GET['role'] ?? $_POST['role'] ?? 'candidate';
if (!in_array($role, Auth::ROLES, true)) $role = 'candidate';

$errors = [];
$old = ['name' => '', 'email' => '', 'company' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $errors[] = 'Session expired. Please try again.';
    } else {
        $old = ['name' => $_POST['name'] ?? '', 'email' => $_POST['email'] ?? '', 'company' => $_POST['company'] ?? ''];
        [$ok, $res] = Auth::register($_POST + ['role' => $role]);
        if ($ok) {
            flash('Welcome to JobPortal, ' . Auth::user()['name'] . '!');
            redirect(Auth::home());
        }
        $errors = $res;
    }
}

$pageTitle = 'Create account';
require __DIR__ . '/includes/header.php';
?>
<main class="container-xl auth-wrap">
  <div class="auth-card">
    <ul class="nav nav-pills nav-fill mb-4 role-tabs">
      <li class="nav-item"><a class="nav-link <?= $role === 'candidate' ? 'active' : '' ?>" href="<?= url('register/candidate') ?>"><i class="bi bi-person"></i> Job seeker</a></li>
      <li class="nav-item"><a class="nav-link <?= $role === 'employer' ? 'active' : '' ?>" href="<?= url('register/employer') ?>"><i class="bi bi-building"></i> Employer</a></li>
    </ul>
    <h1 class="h4 fw-bold mb-1"><?= $role === 'employer' ? 'Hire great people' : 'Find your next job' ?></h1>
    <p class="text-muted small mb-4">Create your <?= e($role) ?> account.</p>

    <?php if ($errors): ?>
      <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post" action="<?= url('register/' . $role) ?>" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="role" value="<?= e($role) ?>">
      <div class="mb-3">
        <label class="form-label">Full name</label>
        <input type="text" name="name" class="form-control" value="<?= e($old['name']) ?>" required>
      </div>
      <?php if ($role === 'employer'): ?>
      <div class="mb-3">
        <label class="form-label">Company name</label>
        <input type="text" name="company" class="form-control" value="<?= e($old['company']) ?>" required>
      </div>
      <?php endif; ?>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= e($old['email']) ?>" required>
      </div>
      <div class="row g-3 mb-4">
        <div class="col-sm-6"><label class="form-label">Password</label><input type="password" name="password" class="form-control" minlength="8" required></div>
        <div class="col-sm-6"><label class="form-label">Confirm</label><input type="password" name="password2" class="form-control" minlength="8" required></div>
      </div>
      <button class="btn btn-primary w-100 py-2 fw-semibold">Create account</button>
    </form>
    <p class="text-center small text-muted mt-3 mb-0">Already registered? <a href="<?= url('login') ?>">Sign in</a></p>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
