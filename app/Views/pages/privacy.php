<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Privacy Policy';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Privacy', 'href' => '/privacy'],
];
$sections = [
    ['title' => 'Data collected', 'body' => 'We log account details, activity metadata, and optional evidence uploads. Financial filings remain under your custodianship.'],
    ['title' => 'Storage & security', 'body' => 'Data is encrypted in transit and at rest. Access is audited with per-team API keys and SSO integrations.'],
    ['title' => 'Rights', 'body' => 'Request exports or deletion via salaam@shaikhoology.com. We respond within 30 days.'],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <header class="space-y-3">
    <h1 class="text-3xl font-semibold tracking-tight text-surface-900 dark:text-white">Privacy Policy</h1>
    <p class="max-w-3xl text-sm text-surface-600 dark:text-surface-300">We prioritise Shari'ah governance confidentiality. This notice describes how we process data.</p>
  </header>

  <div class="mt-8 space-y-5 text-sm text-surface-600 dark:text-surface-300">
    <?php foreach ($sections as $section): ?>
      <article class="rounded-2xl border border-surface-200 bg-white p-5 shadow-sm dark:border-surface-800 dark:bg-surface-900">
        <h2 class="text-base font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($section['title']); ?></h2>
        <p class="mt-2 leading-relaxed"><?php echo htmlspecialchars($section['body']); ?></p>
      </article>
    <?php endforeach; ?>
  </div>

  <p class="mt-8 text-xs text-surface-500">Last updated <?php echo date('F Y'); ?>. For DPA requests email <a href="mailto:salaam@shaikhoology.com">salaam@shaikhoology.com</a>.</p>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
