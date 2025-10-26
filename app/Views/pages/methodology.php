<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Screening Methodology';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Methodology', 'href' => '/methodology'],
];
$steps = [
    ['title' => 'Collect filings', 'body' => 'Gather quarterly or annual statements. Each filing includes total assets, debt, cash, receivables, and income breakdowns.'],
    ['title' => 'Evaluate ratios', 'body' => 'Apply caps for debt, interest income, liquidity, and non-compliant revenue. All thresholds are configurable per standard.'],
    ['title' => 'Document verdict', 'body' => 'Publish a Pass, Monitor, or Review verdict with reasoning, evidence links, and scholar comments.'],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <header class="space-y-3">
    <h1 class="text-3xl font-semibold tracking-tight text-surface-900 dark:text-white">Methodology overview</h1>
    <p class="max-w-3xl text-sm text-surface-600 dark:text-surface-300">Our screening framework aligns with AAOIFI standards while remaining configurable for regional boards. Ratios always point back to evidence and include commentary for investors.</p>
  </header>

  <div class="mt-10 grid gap-6 md:grid-cols-3">
    <?php foreach ($steps as $index => $step): ?>
      <div class="rounded-2xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
        <span class="tag-muted">Step <?php echo $index + 1; ?></span>
        <h2 class="mt-3 text-lg font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($step['title']); ?></h2>
        <p class="mt-2 text-sm text-surface-600 dark:text-surface-300"><?php echo htmlspecialchars($step['body']); ?></p>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="mt-10 grid gap-6 lg:grid-cols-2">
    <div class="rounded-2xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
      <h3 class="text-base font-semibold text-surface-900 dark:text-white">Ratio caps</h3>
      <ul class="mt-3 space-y-3 text-sm text-surface-600 dark:text-surface-300">
        <li>• Debt / Total Assets ≤ 33%</li>
        <li>• Interest-bearing revenue ≤ 5%</li>
        <li>• Cash + receivables / Assets ≤ 70%</li>
        <li>• Non-Shari'ah income ≤ 5%</li>
      </ul>
      <p class="mt-4 text-xs text-surface-500">Configure caps via <code>config/screening.php</code> and override per jurisdiction.</p>
    </div>
    <div class="rounded-2xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
      <h3 class="text-base font-semibold text-surface-900 dark:text-white">Evidence workflow</h3>
      <p class="mt-2 text-sm text-surface-600 dark:text-surface-300">Upload filing extracts, attach scholar notes, and track revisions in the activity log. Every verdict shows its audit trail for regulators and LPs.</p>
      <div class="mt-4 flex gap-3">
        <?php echo ui_button('Download template', 'soft', ['href' => '#']); ?>
        <?php echo ui_button('See case study', 'link', ['href' => '/case-studies']); ?>
      </div>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
