<?php
// Expects: $pageTitle (optional). config.php must already be loaded.
$user = Auth::user();
$flash = flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? 'JobPortal') ?> | JobPortal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
  <script>window.APP = { base: <?= json_encode(BASE_URL) ?>, csrf: <?= json_encode(csrf_token()) ?>, loggedIn: <?= json_encode(Auth::check()) ?>, role: <?= json_encode(Auth::role()) ?> };</script>
</head>
<body>
<nav class="navbar navbar-expand-lg jp-navbar sticky-top">
  <div class="container-xl">
    <a class="navbar-brand fw-bold" href="<?= url() ?>"><i class="bi bi-briefcase-fill me-1"></i>Job<span>Portal</span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?= url('jobs') ?>">Find jobs</a></li>
        <?php if (Auth::role() === 'candidate'): ?>
          <li class="nav-item"><a class="nav-link" href="<?= url('jobs?saved=1') ?>">Saved</a></li>
        <?php endif; ?>
      </ul>
      <div class="d-flex gap-2 align-items-center">
        <?php if ($user): ?>
          <span class="small text-muted d-none d-lg-inline"><?= e($user['name']) ?> <span class="badge text-bg-light border text-capitalize"><?= e($user['role']) ?></span></span>
          <a class="btn btn-outline-secondary btn-sm" href="<?= url('logout') ?>">Sign out</a>
        <?php else: ?>
          <a class="btn btn-link text-decoration-none fw-semibold" href="<?= url('login') ?>">Sign in</a>
          <a class="btn btn-outline-primary btn-sm" href="<?= url('register/candidate') ?>">Job seeker</a>
          <a class="btn btn-primary btn-sm" href="<?= url('register/employer') ?>">Post a job</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<?php if ($flash): ?>
  <div class="container-xl mt-3"><div class="alert alert-<?= e($flash['type']) ?> mb-0"><?= e($flash['msg']) ?></div></div>
<?php endif; ?>
