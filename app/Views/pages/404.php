<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Page not found';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => '404', 'href' => '#'],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-12 text-center shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <div class="mx-auto flex max-w-lg flex-col items-center gap-4">
    <span class="badge-soft">404</span>
    <h1 class="text-3xl font-semibold text-surface-900 dark:text-white">We can't find that page</h1>
    <p class="text-sm text-surface-600 dark:text-surface-300">The URL you requested may have moved or been removed. Check the address, use search, or head back to safety.</p>
    <div class="mt-2 flex flex-wrap justify-center gap-3">
      <?php echo ui_button('Go home', 'primary', ['href' => '/']); ?>
      <?php echo ui_button('Contact support', 'ghost', ['href' => '/contact']); ?>
    </div>
    <?php if (isset($_SERVER['HTTP_X_REQUEST_ID'])): ?>
      <p class="text-xs text-surface-500 mt-4">Request ID: <?php echo htmlspecialchars($_SERVER['HTTP_X_REQUEST_ID']); ?></p>
    <?php endif; ?>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
