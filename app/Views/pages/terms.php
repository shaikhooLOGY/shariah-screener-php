<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Terms of Use';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Terms', 'href' => '/terms'],
];
$sections = [
    ['title' => 'Acceptance', 'body' => 'By accessing Shaikhoology you agree to these Terms and our Privacy Policy. If you are acting on behalf of an organisation you confirm you have authority to accept.'],
    ['title' => 'Usage', 'body' => 'Use the platform responsibly, keep credentials secure, and respect Shari\'ah review confidentiality. Automated scraping is prohibited.'],
    ['title' => 'Liability', 'body' => 'We provide tooling to support decision making but responsibility for final rulings and disclosures remains with your governance board.'],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <header class="space-y-3">
    <h1 class="text-3xl font-semibold tracking-tight text-surface-900 dark:text-white">Terms of Use</h1>
    <p class="max-w-3xl text-sm text-surface-600 dark:text-surface-300">Effective <?php echo date('F j, Y'); ?>. These terms govern your access to Shaikhoology services and content.</p>
  </header>

  <div class="mt-8 space-y-5 text-sm text-surface-600 dark:text-surface-300">
    <?php foreach ($sections as $section): ?>
      <article class="rounded-2xl border border-surface-200 bg-white p-5 shadow-sm dark:border-surface-800 dark:bg-surface-900">
        <h2 class="text-base font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($section['title']); ?></h2>
        <p class="mt-2 leading-relaxed"><?php echo htmlspecialchars($section['body']); ?></p>
      </article>
    <?php endforeach; ?>
  </div>

  <p class="mt-8 text-xs text-surface-500">Need a signed agreement? <a href="/contact">Contact us</a> for enterprise terms.</p>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
