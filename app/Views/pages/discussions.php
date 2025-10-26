<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Discussions';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Community', 'href' => '/discussions'],
];
$threads = [
    ['title' => 'AAOIFI vs local cap adjustments', 'replies' => 8, 'updated' => '2h ago'],
    ['title' => 'Purification audit best practices', 'replies' => 14, 'updated' => '1d ago'],
    ['title' => 'Sharing investor memos securely', 'replies' => 5, 'updated' => '3d ago'],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-3xl font-semibold tracking-tight text-surface-900 dark:text-white">Community discussions</h1>
      <p class="mt-2 max-w-2xl text-sm text-surface-600 dark:text-surface-300">Collaborate with analysts and scholars. Share best practices, ask questions, and capture rulings.</p>
    </div>
    <div class="flex gap-2">
      <?php echo ui_button('Start thread', 'primary', ['href' => '/company/TCS/discussion']); ?>
      <?php echo ui_button('Suggest ratios', 'ghost', ['href' => '/suggest-ratios']); ?>
    </div>
  </header>

  <div class="mt-8 space-y-4">
    <?php foreach ($threads as $thread): ?>
      <article class="flex items-center justify-between rounded-2xl border border-surface-200 bg-white px-5 py-4 shadow-sm hover:bg-surface-50 dark:border-surface-800 dark:bg-surface-900 dark:hover:bg-surface-800/70">
        <div>
          <h2 class="text-base font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($thread['title']); ?></h2>
          <p class="text-xs text-surface-500">Updated <?php echo htmlspecialchars($thread['updated']); ?></p>
        </div>
        <div class="text-xs text-surface-500 dark:text-surface-400">Replies: <?php echo htmlspecialchars($thread['replies']); ?></div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
