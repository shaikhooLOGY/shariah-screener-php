<?php
require_once __DIR__.'/../../partials/ui.php';
$title = 'Ulama dashboard';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Ulama dashboard', 'href' => '/dashboard/ulama'],
];
$queue = [
    ['company' => 'TCS', 'sector' => 'IT', 'status' => ui_badge('Awaiting ruling', 'warn'), 'updated' => '2h ago'],
    ['company' => 'RELI', 'sector' => 'Energy', 'status' => ui_badge('Clarification sent', 'info'), 'updated' => '5h ago'],
];
ob_start();
?>
<section class="space-y-6">
  <div class="grid gap-4 md:grid-cols-3">
    <?php echo ui_kpi('Pending reviews', '12', '+3 new'); ?>
    <?php echo ui_kpi('Turnaround time', '28h', '-6h vs last week'); ?>
    <?php echo ui_kpi('Scholar comments', '46', '+8'); ?>
  </div>

  <div class="rounded-3xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
    <div class="table-toolbar">
      <div>
        <h1 class="text-lg font-semibold text-surface-900 dark:text-white">Review queue</h1>
        <p class="text-xs text-surface-500">Filter by sector or status to focus on what matters.</p>
      </div>
      <div class="toolbar-actions">
        <?php echo ui_button('Bulk approve', 'soft', ['x-on:click' => "$dispatch('open-modal','bulk-approve')"]); ?>
        <?php echo ui_button('Export queue', 'ghost', ['href' => '#']); ?>
      </div>
    </div>
    <?php
      $columns = [
        ['label' => 'Company', 'key' => 'company', 'sortable' => true],
        ['label' => 'Sector', 'key' => 'sector', 'sortable' => true],
        ['label' => 'Status', 'key' => 'status'],
        ['label' => 'Updated', 'key' => 'updated', 'sortable' => true],
      ];
      echo ui_table($columns, $queue, ['id' => 'ulama_queue']);
    ?>
  </div>
</section>

<?php
$body = '<p class="text-sm text-surface-600 dark:text-surface-300">Approve selected filings and notify analysts in bulk?</p>';
$footer = ui_button('Cancel', 'ghost', ['x-on:click' => 'open=false']) . ui_button('Approve selected', 'primary');
echo ui_modal('bulk-approve', 'Bulk approve filings', $body, $footer);

$content = ob_get_clean();
include __DIR__.'/../../layout.php';
