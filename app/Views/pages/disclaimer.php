<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Disclaimers';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Disclaimers', 'href' => '/disclaimer'],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <header class="space-y-3">
    <h1 class="text-3xl font-semibold tracking-tight text-surface-900 dark:text-white">Disclaimers</h1>
    <p class="max-w-3xl text-sm text-surface-600 dark:text-surface-300">Shaikhoology provides tooling and collaboration features; final rulings remain with your appointed Shari'ah board.</p>
  </header>

  <div class="mt-8 space-y-4 text-sm text-surface-600 dark:text-surface-300">
    <div class="rounded-2xl border border-surface-200 bg-white p-5 shadow-sm dark:border-surface-800 dark:bg-surface-900">
      <strong class="block text-surface-900 dark:text-white">Not investment advice</strong>
      <p class="mt-2">Outputs are informational. Investors should consult their own advisers before making decisions.</p>
    </div>
    <div class="rounded-2xl border border-surface-200 bg-white p-5 shadow-sm dark:border-surface-800 dark:bg-surface-900">
      <strong class="block text-surface-900 dark:text-white">Data sources</strong>
      <p class="mt-2">Filings are sourced from public disclosures. Accuracy depends on filing completeness and your team\'s review.</p>
    </div>
    <div class="rounded-2xl border border-surface-200 bg-white p-5 shadow-sm dark:border-surface-800 dark:bg-surface-900">
      <strong class="block text-surface-900 dark:text-white">Limitation of liability</strong>
      <p class="mt-2">Shaikhoology is not liable for losses arising from reliance on screening outputs.</p>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
