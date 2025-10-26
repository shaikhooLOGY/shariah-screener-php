<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Glossary';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Glossary', 'href' => '/glossary'],
];
$terms = [
    ['term' => 'Purification', 'desc' => 'The process of cleansing non-compliant revenue by donating equivalent amounts to charity.'],
    ['term' => 'Cap', 'desc' => 'Threshold applied to a financial ratio to determine compliance (e.g., debt/assets).'],
    ['term' => 'Verdict', 'desc' => 'The Pass/Monitor/Review outcome assigned after evaluating all ratios and evidence.'],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <header class="space-y-3">
    <h1 class="text-3xl font-semibold tracking-tight text-surface-900 dark:text-white">Glossary</h1>
    <p class="max-w-3xl text-sm text-surface-600 dark:text-surface-300">Key terms across Shari'ah screening, purification, and investor communication.</p>
  </header>

  <dl class="mt-8 space-y-4">
    <?php foreach ($terms as $entry): ?>
      <div class="rounded-2xl border border-surface-200 bg-white p-5 shadow-sm dark:border-surface-800 dark:bg-surface-900">
        <dt class="text-sm font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($entry['term']); ?></dt>
        <dd class="mt-2 text-sm text-surface-600 dark:text-surface-300"><?php echo htmlspecialchars($entry['desc']); ?></dd>
      </div>
    <?php endforeach; ?>
  </dl>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
