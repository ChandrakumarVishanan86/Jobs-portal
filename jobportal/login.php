<?php
require __DIR__ . '/config/config.php';

if (Auth::check()) redirect(Auth::home());

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $error = 'Session expired. Please try again.';
    } elseif (Auth::attempt($email, $_POST['password'] ?? '')) {
        redirect(Auth::home());
    } else {
        $error = 'Incorrect email or password.';
    }
}

$pageTitle = 'Sign in';
require __DIR__ . '/includes/header.php';
?>
<main class="container-xl auth-wrap">
  <div class="auth-card">
    <h1 class="h4 fw-bold mb-1">Sign in</h1>
    <p class="text-muted small mb-4">Employers, job seekers and admins sign in here.</p>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= url('login') ?>">
      <?= csrf_field() ?>
      <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($email) ?>" required autofocus></div>
      <div class="mb-4"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
      <button class="btn btn-primary w-100 py-2 fw-semibold">Sign in</button>
    </form>
    <p class="text-center small text-muted mt-3 mb-0">New here? <a href="<?= url('register/candidate') ?>">Job seeker</a> &middot; <a href="<?= url('register/employer') ?>">Employer</a></p>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
