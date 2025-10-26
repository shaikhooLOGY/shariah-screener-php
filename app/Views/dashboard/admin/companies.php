<?php
require_once __DIR__.'/../../partials/ui.php';
$title = 'Manage companies';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Admin dashboard', 'href' => '/dashboard/admin'],
    ['label' => 'Companies', 'href' => '/dashboard/admin/companies'],
];
$columns = [
    ['label' => 'Ticker', 'key' => 'ticker', 'sortable' => true],
    ['label' => 'Name', 'key' => 'name', 'sortable' => true],
    ['label' => 'Sector', 'key' => 'sector', 'sortable' => true],
    ['label' => 'Verdict', 'key' => 'verdict'],
];
$rows = [
    ['ticker' => 'TCS', 'name' => 'Tata Consultancy Services', 'sector' => 'IT', 'verdict' => ui_badge('Pass', 'success')],
    ['ticker' => 'RELI', 'name' => 'Reliance Industries', 'sector' => 'Energy', 'verdict' => ui_badge('Monitor', 'warn')],
    ['ticker' => 'ADNOC', 'name' => 'ADNOC Drilling', 'sector' => 'Energy', 'verdict' => ui_badge('Review', 'danger')],
];
ob_start();
?>
<section class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <h1 class="text-2xl font-semibold text-surface-900 dark:text-white">Companies</h1>
      <p class="text-sm text-surface-600 dark:text-surface-300">Add new tickers, update sector mappings, or archive retired entities.</p>
    </div>
    <div class="flex gap-2">
      <?php echo ui_button('Add company'); ?>
      <?php echo ui_button('Import CSV', 'ghost'); ?>
    </div>
  </div>
  <?php echo ui_table($columns, $rows, ['id' => 'admin_company_table']); ?>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../../layout.php';
