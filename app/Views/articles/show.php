<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Article';
$slug = $slug ?? 'insight';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Articles', 'href' => '/articles'],
    ['label' => ucfirst(str_replace('-', ' ', $slug)), 'href' => '/articles/'.$slug],
];
ob_start();
?>
<article class="prose prose-indigo max-w-3xl rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:prose-invert dark:border-surface-800 dark:bg-surface-900">
  <h1 class="mb-2 text-3xl font-semibold tracking-tight text-surface-900 dark:text-white"><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $slug))); ?></h1>
  <p class="text-sm text-surface-500">Published <?php echo date('F j, Y'); ?></p>
  <p class="mt-6 text-base leading-relaxed text-surface-600 dark:text-surface-300">Use this template to render CMS-backed articles later. Share your methodology insights, regulatory updates, or scholar interviews using clear, accessible language.</p>
  <p>Combine ratio charts, filing excerpts, and purification walkthroughs to bring transparency to your Shari'ah governance programme.</p>
  <blockquote>“Investors trust Shari'ah boards who publish timely, well-explained verdicts with primary sources attached.”</blockquote>
  <p>Have an article idea? Send it to salaam@shaikhoology.com and we will feature it.</p>
</article>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
