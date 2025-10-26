<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Scholar profile';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Scholars', 'href' => '/scholars'],
    ['label' => ucfirst(str_replace('-', ' ', $slug ?? 'profile')), 'href' => '#'],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
    <div class="flex items-center gap-4">
      <span class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-500 text-xl font-semibold text-white"><?php echo strtoupper(substr($slug ?? 'S', 0, 1)); ?></span>
      <div>
        <h1 class="text-3xl font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $slug ?? 'Scholar'))); ?></h1>
        <p class="text-sm text-surface-600 dark:text-surface-300">Add biography, credentials, and rulings once scholars are synced from the CMS.</p>
      </div>
    </div>
    <div class="flex gap-2">
      <?php echo ui_button('Message', 'soft', ['href' => '/discussions']); ?>
      <?php echo ui_button('Assign review', 'ghost', ['href' => '/dashboard/ulama/reviews']); ?>
    </div>
  </div>

  <div class="mt-8 grid gap-6 lg:grid-cols-2">
    <div class="rounded-2xl border border-surface-200 bg-white p-5 shadow-sm dark:border-surface-800 dark:bg-surface-900">
      <h2 class="text-base font-semibold text-surface-900 dark:text-white">Biography</h2>
      <p class="mt-2 text-sm text-surface-600 dark:text-surface-300">Populate this section with scholar background, certifications, published fatwas, and board appointments.</p>
    </div>
    <div class="rounded-2xl border border-surface-200 bg-white p-5 shadow-sm dark:border-surface-800 dark:bg-surface-900">
      <h2 class="text-base font-semibold text-surface-900 dark:text-white">Recent rulings</h2>
      <p class="mt-2 text-sm text-surface-600 dark:text-surface-300">List verdicts or notes recently issued by this scholar. Link to discussion threads for richer context.</p>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
