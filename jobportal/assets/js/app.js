(function () {
  'use strict';
  const form = document.getElementById('filterForm');
  const results = document.getElementById('jobResults');
  let timer = null;
  let controller = null;

  // ---- Filtering: build GET query, fetch partial, update grid + URL ----
  function buildQuery(extra) {
    const params = new URLSearchParams();
    new FormData(form).forEach((v, k) => { if (String(v).trim() !== '') params.set(k, v); });
    if (extra) Object.entries(extra).forEach(([k, v]) => params.set(k, v));
    return params;
  }

  async function load(params, push = true) {
    if (!results) return;
    if (controller) controller.abort();
    controller = new AbortController();
    results.classList.add('loading');
    try {
      const res = await fetch(`${window.APP.base}/jobs?${params.toString()}&partial=1`, { signal: controller.signal });
      results.innerHTML = await res.text();
      const qs = params.toString();
      if (push) history.pushState(null, '', `${window.APP.base}/jobs${qs ? '?' + qs : ''}`);
    } catch (e) {
      if (e.name !== 'AbortError') results.innerHTML = '<div class="alert alert-danger">Could not load jobs. Please try again.</div>';
    } finally {
      results.classList.remove('loading');
    }
  }

  if (form) {
    form.querySelectorAll('.js-filter').forEach(el => el.addEventListener('change', () => load(buildQuery())));
    const q = form.elements['q'] || document.getElementById('qInput');
    if (q) {
      q.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(() => load(buildQuery()), 350); });
    }
    form.addEventListener('submit', e => { e.preventDefault(); load(buildQuery()); });
    const topForm = document.getElementById('filterFormTop');
    if (topForm) topForm.addEventListener('submit', e => { e.preventDefault(); load(buildQuery()); });

    document.addEventListener('click', e => {
      const reset = e.target.closest('.js-reset');
      if (reset) {
        e.preventDefault();
        form.reset();
        form.querySelectorAll('select').forEach(s => (s.value = ''));
        form.querySelectorAll('input[type=checkbox]').forEach(c => (c.checked = false));
        const q2 = document.getElementById('qInput'); if (q2) q2.value = '';
        load(new URLSearchParams());
        return;
      }
      const pg = e.target.closest('.pagination a.page-link');
      if (pg && results && results.contains(pg)) {
        e.preventDefault();
        const href = pg.getAttribute('href');
        if (!href || pg.closest('.disabled')) return;
        load(new URLSearchParams(href.replace(/^\?/, '')));
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    });

    window.addEventListener('popstate', () => {
      const params = new URLSearchParams(location.search);
      for (const el of form.elements) {
        if (!el.name) continue;
        if (el.type === 'checkbox') el.checked = params.has(el.name);
        else el.value = params.get(el.name) || '';
      }
      load(params, false);
    });
  }

  // ---- Saved jobs heart toggle (event delegation so it survives grid reloads) ----
  document.addEventListener('click', async e => {
    const btn = e.target.closest('.save-btn');
    if (!btn) return;
    e.preventDefault();

    if (!window.APP.loggedIn) { location.href = `${window.APP.base}/login`; return; }
    if (window.APP.role !== 'candidate') { alert('Only job seeker accounts can save jobs.'); return; }
    if (btn.disabled) return;
    btn.disabled = true;

    try {
      const body = new URLSearchParams({ job_id: btn.dataset.job });
      const res = await fetch(`${window.APP.base}/api/toggle_save.php`, {
        method: 'POST',
        headers: { 'X-CSRF-Token': window.APP.csrf, 'Content-Type': 'application/x-www-form-urlencoded' },
        body
      });
      const data = await res.json();
      if (data.ok) {
        btn.classList.toggle('saved', data.saved);
        const icon = btn.querySelector('i');
        icon.className = 'bi ' + (data.saved ? 'bi-heart-fill' : 'bi-heart');
        btn.classList.add('pop'); setTimeout(() => btn.classList.remove('pop'), 300);
      } else if (data.error === 'login_required') {
        location.href = `${window.APP.base}/login`;
      } else {
        alert(data.error || 'Something went wrong.');
      }
    } catch (err) {
      alert('Network error. Please try again.');
    } finally {
      btn.disabled = false;
    }
  });
})();
