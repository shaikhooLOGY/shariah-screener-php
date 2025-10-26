<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Articles & Insights';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Learn', 'href' => '/learn'],
    ['label' => 'Articles', 'href' => '/articles'],
];
$articles = [
    ['slug' => 'why-ratios-matter', 'title' => 'Why ratios matter beyond compliance', 'excerpt' => 'Ratios act as audit-ready signals for your Shari\'ah board and investors. Here is how to interpret them smartly.', 'date' => 'Jun 3, 2025'],
    ['slug' => 'crafting-mufti-notes', 'title' => 'Crafting mufti notes teams will read', 'excerpt' => 'Structure scholar commentary using our note framework to ensure analysts can implement rulings fast.', 'date' => 'May 18, 2025'],
    ['slug' => 'rolling-out-purification', 'title' => 'Rolling out purification at scale', 'excerpt' => 'Coordinate donations, produce investor statements, and automate reports with Shaikhoology.', 'date' => 'Apr 29, 2025'],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-3xl font-semibold tracking-tight text-surface-900 dark:text-white">Articles &amp; insights</h1>
      <p class="mt-2 max-w-2xl text-sm text-surface-600 dark:text-surface-300">Latest thinking on Shari'ah screening, scholar collaboration, and investor relations.</p>
    </div>
    <div class="flex gap-2">
      <?php echo ui_button('Subscribe', 'primary', ['href' => '#']); ?>
      <?php echo ui_button('RSS', 'ghost', ['href' => '#']); ?>
    </div>
  </header>

  <div class="mt-8 space-y-4">
    <?php foreach ($articles as $article): ?>
      <article class="rounded-2xl border border-surface-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-surface-800 dark:bg-surface-900">
        <div class="flex items-center justify-between text-xs text-surface-500 dark:text-surface-400">
          <span><?php echo htmlspecialchars($article['date']); ?></span>
          <span>Article</span>
        </div>
        <h2 class="mt-2 text-xl font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($article['title']); ?></h2>
        <p class="mt-2 text-sm text-surface-600 dark:text-surface-300"><?php echo htmlspecialchars($article['excerpt']); ?></p>
        <div class="mt-4 flex gap-3">
          <?php echo ui_button('Read article', 'soft', ['href' => '/articles/'.$article['slug']]); ?>
          <?php echo ui_button('Share', 'link', ['href' => '#']); ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
