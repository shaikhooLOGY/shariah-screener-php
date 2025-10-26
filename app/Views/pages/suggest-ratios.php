<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Suggest ratios';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Community', 'href' => '/discussions'],
    ['label' => 'Suggest ratios', 'href' => '/suggest-ratios'],
];
$csrf = $_SESSION['csrf'] ?? '';
ob_start();
?>
<section class="grid gap-8 rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900 lg:grid-cols-[1.2fr_1fr]">
  <form method="post" action="/suggest-ratios" class="space-y-4">
    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
    <h1 class="text-2xl font-semibold text-surface-900 dark:text-white">Propose alternative ratio interpretation</h1>
    <p class="text-sm text-surface-600 dark:text-surface-300">Submit your calculation, attach evidence, and our scholars will review it.</p>
    <?php echo ui_input('symbol', 'Ticker symbol'); ?>
    <?php echo ui_input('period', 'Reporting period', 'text', '2025-Q2'); ?>
    <?php echo ui_textarea('rationale', 'Rationale', '', 'Explain why the ratio should be interpreted differently, including citations.'); ?>
    <?php echo ui_input('evidence_url', 'Evidence URL', 'url'); ?>
    <?php echo ui_textarea('note', 'Additional notes'); ?>
    <div class="flex gap-2">
      <?php echo ui_button('Submit suggestion', 'primary'); ?>
      <?php echo ui_button('Cancel', 'ghost', ['href' => '/discussions']); ?>
    </div>
  </form>
  <aside class="space-y-4 rounded-2xl border border-surface-200 bg-surface-100/70 p-6 text-sm dark:border-surface-700 dark:bg-surface-900/70">
    <h2 class="text-base font-semibold text-surface-900 dark:text-white">Review SLAs</h2>
    <p class="text-surface-600 dark:text-surface-300">Analyst suggestions are triaged daily. Expect a response within 48 hours.</p>
    <ul class="space-y-2 text-xs text-surface-500">
      <li>• Include page numbers or extracts for evidence.</li>
      <li>• Scholars may request clarification via discussions.</li>
      <li>• Approved ratios are versioned and visible to investors.</li>
    </ul>
  </aside>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
