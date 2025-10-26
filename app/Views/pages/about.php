<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'About';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'About', 'href' => '/about'],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <div class="grid gap-8 lg:grid-cols-2">
    <div class="space-y-4">
      <span class="badge-soft">Our mission</span>
      <h1 class="text-3xl font-semibold tracking-tight text-surface-900 dark:text-white">Connecting scholars, analysts, and investors with clarity.</h1>
      <p class="text-sm text-surface-600 dark:text-surface-300">Shaikhoology gives Shari'ah governance teams a shared workspace to evaluate filings, record rulings, and publish transparent verdicts. Our team blends Islamic finance expertise with modern product craft.</p>
      <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-surface-200 bg-white p-4 text-sm dark:border-surface-800 dark:bg-surface-900">
          <p class="text-xs uppercase tracking-wide text-surface-500">HQ</p>
          <p class="mt-2 font-semibold text-surface-900 dark:text-white">Dubai &amp; Kuala Lumpur</p>
        </div>
        <div class="rounded-2xl border border-surface-200 bg-white p-4 text-sm dark:border-surface-800 dark:bg-surface-900">
          <p class="text-xs uppercase tracking-wide text-surface-500">Coverage</p>
          <p class="mt-2 font-semibold text-surface-900 dark:text-white">14 jurisdictions • 220+ tickers</p>
        </div>
      </div>
    </div>
    <div class="space-y-6">
      <div class="rounded-2xl border border-surface-200 bg-white p-5 shadow-sm dark:border-surface-800 dark:bg-surface-900">
        <h2 class="text-base font-semibold text-surface-900 dark:text-white">Principles</h2>
        <ul class="mt-3 space-y-3 text-sm text-surface-600 dark:text-surface-300">
          <li>• Evidence-first. Every decision links back to filings and scholar commentary.</li>
          <li>• Collaboration-first. Ulama, analysts, and investors align through clear workflows.</li>
          <li>• Open. SQLite for dev, MySQL for production, and API ready from day one.</li>
        </ul>
      </div>
      <div class="rounded-2xl border border-surface-200 bg-white p-5 shadow-sm dark:border-surface-800 dark:bg-surface-900">
        <h2 class="text-base font-semibold text-surface-900 dark:text-white">Contact</h2>
        <p class="mt-2 text-sm text-surface-600 dark:text-surface-300">Media or partnership requests? Drop us a note and we will respond within one business day.</p>
        <div class="mt-4 flex gap-2">
          <?php echo ui_button('Email team', 'primary', ['href' => '/contact']); ?>
          <?php echo ui_button('Download press kit', 'ghost', ['href' => '#']); ?>
        </div>
      </div>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
