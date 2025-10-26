<?php
require_once __DIR__.'/../../partials/ui.php';
$title = 'Manage filings';
$csrf = $_SESSION['csrf'] ?? '';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Admin dashboard', 'href' => '/dashboard/admin'],
    ['label' => 'Filings', 'href' => '/dashboard/admin/filings'],
];
ob_start();
?>
<section class="grid gap-6 rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900 lg:grid-cols-[1fr_360px]">
  <form method="post" action="/dashboard/admin/filings" class="space-y-4">
    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
    <h1 class="text-2xl font-semibold text-surface-900 dark:text-white">Upload filing</h1>
    <p class="text-sm text-surface-600 dark:text-surface-300">Paste totals or import from CSV to trigger new screenings.</p>
    <?php echo ui_input('ticker', 'Ticker'); ?>
    <?php echo ui_input('period', 'Period', 'text', '2025-Q2'); ?>
    <?php echo ui_input('total_assets', 'Total assets', 'number'); ?>
    <?php echo ui_input('total_debt', 'Total debt', 'number'); ?>
    <?php echo ui_input('cash', 'Cash', 'number'); ?>
    <?php echo ui_input('receivables', 'Receivables', 'number'); ?>
    <?php echo ui_input('revenue', 'Revenue', 'number'); ?>
    <?php echo ui_input('interest_income', 'Interest income', 'number'); ?>
    <?php echo ui_input('non_shariah_income', 'Non-Shari\'ah income', 'number'); ?>
    <div class="flex gap-2">
      <?php echo ui_button('Queue screening'); ?>
      <?php echo ui_button('Cancel', 'ghost', ['href' => '/dashboard/admin']); ?>
    </div>
  </form>
  <aside class="space-y-4 rounded-2xl border border-surface-200 bg-surface-100/70 p-6 text-sm dark:border-surface-700 dark:bg-surface-900/70">
    <h2 class="text-base font-semibold text-surface-900 dark:text-white">Import tips</h2>
    <p class="text-surface-600 dark:text-surface-300">Upload CSV with columns <code>ticker, period, assets, debt, cash, receivables, revenue, interest, nonsh, filing_date</code>.</p>
    <a class="inline-flex rounded-full border border-surface-200 px-4 py-2 text-xs font-semibold hover:bg-surface-100 dark:border-surface-700 dark:text-surface-200 dark:hover:bg-surface-800" href="#">Download template</a>
  </aside>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../../layout.php';
