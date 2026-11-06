<?php
require __DIR__ . '/config/config.php';

$filters = [
    'q'        => trim($_GET['q'] ?? ''),
    'location' => $_GET['location'] ?? '',
    'category' => $_GET['category'] ?? '',
    'type'     => $_GET['type'] ?? '',
    'salary'   => $_GET['salary'] ?? '',
    'saved'    => !empty($_GET['saved']) ? '1' : '',
    'page'     => max(1, (int)($_GET['page'] ?? 1)),
];

$uid = Auth::role() === 'candidate' ? Auth::id() : null;
$result = Job::search($filters, $uid);
$filters['page'] = '';   // pagination links set their own page

// AJAX partial: only the grid markup
if (isset($_GET['partial'])) {
    require __DIR__ . '/includes/job_grid.php';
    exit;
}

$categories = Job::categories();
$locations  = Job::locations();
$types = ['Full-time', 'Part-time', 'Contract', 'Internship', 'Remote'];

$pageTitle = 'Find jobs';
require __DIR__ . '/includes/header.php';
?>
<section class="hero-search">
  <div class="container-xl">
    <h1 class="hero-title">Find the job that fits your life</h1>
    <form id="filterFormTop" class="hero-form" action="<?= url('jobs') ?>" method="get" role="search">
      <div class="input-group input-group-lg">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
        <input type="search" name="q" form="filterForm" class="form-control border-start-0" placeholder="Job title, keyword or company" value="<?= e($filters['q']) ?>" id="qInput" autocomplete="off">
        <button class="btn btn-primary px-4 fw-semibold" type="submit" form="filterForm">Search</button>
      </div>
    </form>
  </div>
</section>

<main class="container-xl py-4">
  <div class="page-layout">
    <aside class="filter-sidebar">
      <form id="filterForm" action="<?= url('jobs') ?>" method="get">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h2 class="h6 fw-bold mb-0"><i class="bi bi-sliders"></i> Filters</h2>
          <a href="<?= url('jobs') ?>" class="small js-reset">Reset</a>
        </div>

        <label class="form-label small fw-semibold">Location</label>
        <select name="location" class="form-select mb-3 js-filter">
          <option value="">All locations</option>
          <?php foreach ($locations as $l): ?>
            <option value="<?= e($l) ?>" <?= $filters['location'] === $l ? 'selected' : '' ?>><?= e($l) ?></option>
          <?php endforeach; ?>
        </select>

        <label class="form-label small fw-semibold">Category</label>
        <select name="category" class="form-select mb-3 js-filter">
          <option value="">All categories</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= e($c) ?>" <?= $filters['category'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
          <?php endforeach; ?>
        </select>

        <label class="form-label small fw-semibold">Salary range (<?= e(CURRENCY) ?>)</label>
        <select name="salary" class="form-select mb-3 js-filter">
          <option value="">Any salary</option>
          <?php foreach (Job::SALARY_RANGES as $val => $label): ?>
            <option value="<?= e($val) ?>" <?= $filters['salary'] === $val ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>

        <label class="form-label small fw-semibold">Job type</label>
        <select name="type" class="form-select mb-3 js-filter">
          <option value="">All types</option>
          <?php foreach ($types as $t): ?>
            <option value="<?= e($t) ?>" <?= $filters['type'] === $t ? 'selected' : '' ?>><?= e($t) ?></option>
          <?php endforeach; ?>
        </select>

        <?php if (Auth::role() === 'candidate'): ?>
          <div class="form-check form-switch">
            <input class="form-check-input js-filter" type="checkbox" name="saved" value="1" id="savedOnly" <?= $filters['saved'] ? 'checked' : '' ?>>
            <label class="form-check-label small" for="savedOnly">Saved jobs only</label>
          </div>
        <?php endif; ?>
        <noscript><button class="btn btn-primary w-100 mt-3">Apply filters</button></noscript>
      </form>
    </aside>

    <section id="jobResults" aria-live="polite">
      <?php require __DIR__ . '/includes/job_grid.php'; ?>
    </section>
  </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
