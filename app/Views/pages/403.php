<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Forbidden';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => '403', 'href' => '#'],
];
$flash = take_flash();
ob_start();
?>
<section class="mx-auto flex max-w-lg flex-col items-center gap-4 rounded-3xl border border-surface-200 bg-white px-6 py-12 text-center shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <span class="badge-soft">403</span>
  <h1 class="text-3xl font-semibold text-surface-900 dark:text-white">Yahan aapka access nahin hai</h1>
  <p class="text-sm text-surface-600 dark:text-surface-300">Role ya permissions sufficient nahi. Agar aapko yahan ana hai to apne Shari'ah admin se baat karein.</p>
  <div class="mt-3 flex flex-wrap justify-center gap-3">
    <?php echo ui_button('Go home', 'primary', ['href' => '/']); ?>
    <?php echo ui_button('Login dusra account', 'ghost', ['href' => '/login']); ?>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
