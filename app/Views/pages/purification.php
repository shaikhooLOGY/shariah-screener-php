<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Purification guide';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Purification', 'href' => '/purification'],
];
$steps = [
    ['label' => 'Identify non-compliant income', 'content' => 'Flag any interest or prohibited revenue components in the filings. Shaikhoology keeps track of the amounts per company.'],
    ['label' => 'Calculate per-unit adjustment', 'content' => 'Divide the non-compliant income by outstanding shares or investor units to get the purification amount.'],
    ['label' => 'Donate and record', 'content' => 'Distribute purification donations to approved charities and keep the receipt trail for auditors.'],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <header class="space-y-3">
    <h1 class="text-3xl font-semibold tracking-tight text-surface-900 dark:text-white">Purification guide</h1>
    <p class="max-w-3xl text-sm text-surface-600 dark:text-surface-300">Calculate and record purification amounts with confidence. Scholars can annotate reasoning and upload receipts for investor transparency.</p>
  </header>

  <div class="mt-8 grid gap-6 lg:grid-cols-3">
    <?php foreach ($steps as $index => $step): ?>
      <article class="rounded-2xl border border-surface-200 bg-white p-5 shadow-sm dark:border-surface-800 dark:bg-surface-900">
        <span class="tag-muted">Step <?php echo $index + 1; ?></span>
        <h2 class="mt-3 text-base font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($step['label']); ?></h2>
        <p class="mt-2 text-sm text-surface-600 dark:text-surface-300"><?php echo htmlspecialchars($step['content']); ?></p>
      </article>
    <?php endforeach; ?>
  </div>

  <div class="mt-10 rounded-2xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
    <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Need scholar validation?</h2>
    <p class="mt-2 text-sm text-surface-600 dark:text-surface-300">Invite your Shari'ah board to review purification flows directly in the platform and sign off with comments.</p>
    <div class="mt-4 flex gap-3">
      <?php echo ui_button('Open ulama dashboard', 'primary', ['href' => '/dashboard/ulama']); ?>
      <?php echo ui_button('Download template', 'ghost', ['href' => '#']); ?>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
