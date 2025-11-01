<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Server error';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Error', 'href' => '#'],
];
$requestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-12 text-center shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <div class="mx-auto flex max-w-lg flex-col items-center gap-4">
    <span class="badge-soft">500</span>
    <h1 class="text-3xl font-semibold text-surface-900 dark:text-white">Something went wrong</h1>
    <p class="text-sm text-surface-600 dark:text-surface-300">An unexpected error occurred while loading this page. Our team has been notified.</p>
    <p class="text-xs text-surface-500">Request ID: <?php echo htmlspecialchars($requestId); ?></p>
    <div class="mt-2 flex flex-wrap justify-center gap-3">
      <?php echo ui_button('Try again', 'primary', ['href' => $currentPath ?? '/']); ?>
      <?php echo ui_button('Go home', 'ghost', ['href' => '/']); ?>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
