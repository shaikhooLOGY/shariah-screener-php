<?php
require_once __DIR__.'/../../partials/ui.php';
$title = 'Review queue';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Ulama dashboard', 'href' => '/dashboard/ulama'],
    ['label' => 'Reviews', 'href' => '/dashboard/ulama/reviews'],
];
$filters = ['All sectors', 'IT', 'Energy', 'Financial'];
$reviews = [
    ['company' => 'TCS', 'period' => '2025-Q2', 'analyst' => 'Aaliyah', 'action' => ui_button('Open', 'soft', ['href' => '/company/TCS/discussion'])],
    ['company' => 'RELI', 'period' => '2025-Q1', 'analyst' => 'Muhammad', 'action' => ui_button('Open', 'soft', ['href' => '#'])],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-semibold text-surface-900 dark:text-white">Review queue</h1>
      <p class="text-sm text-surface-600 dark:text-surface-300">Filter by sector and status to triage filings quickly.</p>
    </div>
    <div class="flex gap-2">
      <label class="hidden text-xs font-semibold text-surface-500 md:block">Sector</label>
      <select class="rounded-full border border-surface-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-800 dark:text-surface-100">
        <?php foreach ($filters as $filter): ?>
          <option><?php echo htmlspecialchars($filter); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </header>

  <div class="mt-6 space-y-3">
    <?php foreach ($reviews as $review): ?>
      <article class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-surface-200 bg-white px-4 py-4 shadow-sm hover:bg-surface-50 dark:border-surface-800 dark:bg-surface-900 dark:hover:bg-surface-800/70">
        <div>
          <p class="text-sm font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($review['company']); ?> · <?php echo htmlspecialchars($review['period']); ?></p>
          <p class="text-xs text-surface-500">Analyst: <?php echo htmlspecialchars($review['analyst']); ?></p>
        </div>
        <div><?php echo $review['action']; ?></div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../../layout.php';
