<?php
/** Partial: expects $result (from Job::search) and $filters (current GET filters). */
$jobs = $result['jobs'];
$canSave = Auth::role() === 'candidate';
$qs = fn(array $over) => http_build_query(array_filter(array_merge($filters, $over), fn($v) => $v !== '' && $v !== null));
?>
<div class="results-head d-flex justify-content-between align-items-center mb-3">
  <h2 class="h6 mb-0 text-muted"><strong class="text-dark"><?= number_format($result['total']) ?></strong> job<?= $result['total'] === 1 ? '' : 's' ?> found</h2>
</div>

<?php if (!$jobs): ?>
  <div class="empty-state text-center py-5">
    <i class="bi bi-search display-5 text-muted"></i>
    <h3 class="h5 mt-3">No jobs match your filters</h3>
    <p class="text-muted">Try removing a filter or searching a different keyword.</p>
    <a href="<?= url('jobs') ?>" class="btn btn-outline-primary btn-sm js-reset">Clear filters</a>
  </div>
<?php else: ?>
  <div class="job-grid">
    <?php foreach ($jobs as $j): $logo = logo_url($j['company_logo']); ?>
      <article class="job-card">
        <button type="button" class="save-btn <?= $j['saved'] ? 'saved' : '' ?>" data-job="<?= (int)$j['id'] ?>" aria-label="Save job" title="<?= $canSave ? 'Save job' : 'Sign in as a job seeker to save' ?>">
          <i class="bi <?= $j['saved'] ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
        </button>
        <div class="d-flex gap-3 align-items-center mb-3">
          <?php if ($logo): ?>
            <img src="<?= e($logo) ?>" alt="" class="company-logo">
          <?php else: ?>
            <div class="company-logo logo-fallback"><?= e(mb_strtoupper(mb_substr($j['company_name'], 0, 1))) ?></div>
          <?php endif; ?>
          <div class="min-w-0">
            <h3 class="job-title"><?= e($j['title']) ?></h3>
            <div class="company-name"><?= e($j['company_name']) ?></div>
          </div>
        </div>
        <div class="job-meta">
          <span><i class="bi bi-geo-alt"></i> <?= e($j['location']) ?></span>
          <span><i class="bi bi-tag"></i> <?= e($j['category']) ?></span>
          <span><i class="bi bi-clock"></i> <?= e(time_ago($j['created_at'])) ?></span>
        </div>
        <div class="d-flex flex-wrap gap-2 mb-3">
          <span class="salary-badge"><i class="bi bi-cash-stack"></i> <?= e(format_salary($j['salary_min'] ? (int)$j['salary_min'] : null, $j['salary_max'] ? (int)$j['salary_max'] : null)) ?></span>
          <span class="type-badge"><?= e($j['job_type']) ?></span>
        </div>
        <a href="<?= url('apply?job=' . (int)$j['id']) ?>" class="btn btn-primary apply-btn">Apply now</a>
      </article>
    <?php endforeach; ?>
  </div>

  <?php if ($result['pages'] > 1): $p = $result['page']; ?>
    <nav class="mt-4" aria-label="Pages">
      <ul class="pagination justify-content-center">
        <li class="page-item <?= $p <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="?<?= e($qs(['page' => $p - 1])) ?>">&laquo;</a></li>
        <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
          <li class="page-item <?= $i === $p ? 'active' : '' ?>"><a class="page-link" href="?<?= e($qs(['page' => $i])) ?>"><?= $i ?></a></li>
        <?php endfor; ?>
        <li class="page-item <?= $p >= $result['pages'] ? 'disabled' : '' ?>"><a class="page-link" href="?<?= e($qs(['page' => $p + 1])) ?>">&raquo;</a></li>
      </ul>
    </nav>
  <?php endif; ?>
<?php endif; ?>
