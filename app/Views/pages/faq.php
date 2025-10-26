<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'FAQ';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'FAQ', 'href' => '/faq'],
];
$faqs = [
    ['q' => 'Does Shaikhoology support multiple standards?', 'a' => 'Yes. Configure caps per jurisdiction and enable experimental rule sets using the feature flags pane.'],
    ['q' => 'Is there an API?', 'a' => 'The REST API exposes verdicts, ratio history, and notes. Authenticate with API keys scoped per team.'],
    ['q' => 'How do scholars collaborate?', 'a' => 'Ulama receive a focused dashboard, discussion threads, and review queue. Each verdict captures their ruling and comments.'],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <header class="space-y-3">
    <h1 class="text-3xl font-semibold tracking-tight text-surface-900 dark:text-white">Frequently asked questions</h1>
    <p class="max-w-3xl text-sm text-surface-600 dark:text-surface-300">Quick answers to the most common product, methodology, and deployment questions.</p>
  </header>

  <div class="mt-8 space-y-4">
    <?php foreach ($faqs as $faq): ?>
      <details class="accordion">
        <summary>
          <span><?php echo htmlspecialchars($faq['q']); ?></span>
        </summary>
        <div class="accordion-body">
          <?php echo htmlspecialchars($faq['a']); ?>
        </div>
      </details>
    <?php endforeach; ?>
  </div>

  <div class="mt-10 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-surface-200 bg-surface-100/70 px-6 py-5 dark:border-surface-700 dark:bg-surface-900/70">
    <div>
      <p class="text-sm font-semibold text-surface-900 dark:text-white">Still looking for answers?</p>
      <p class="text-xs text-surface-500">Drop us a note or join the weekly onboarding demo.</p>
    </div>
    <div class="flex gap-2">
      <?php echo ui_button('Contact support', 'primary', ['href' => '/contact']); ?>
      <?php echo ui_button('Join live demo', 'ghost', ['href' => '#']); ?>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
