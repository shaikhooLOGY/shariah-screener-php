<?php
require_once __DIR__.'/../../partials/ui.php';
$title = 'Admin dashboard';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Admin dashboard', 'href' => '/dashboard/admin'],
];
ob_start();
?>
<section class="space-y-6">
  <div class="grid gap-4 md:grid-cols-3">
    <?php echo ui_kpi('Companies monitored', '128', '+6 vs last month'); ?>
    <?php echo ui_kpi('Filings ingested', '482', '+12'); ?>
    <?php echo ui_kpi('Pending verdicts', '9', '-3'); ?>
  </div>

  <div class="grid gap-6 lg:grid-cols-2">
    <?php
    echo ui_card('Quick actions',
        '<div class="grid gap-3 text-sm text-surface-600 dark:text-surface-300">
            <a class="rounded-2xl border border-surface-200 px-4 py-3 hover:bg-surface-100 dark:border-surface-700 dark:hover:bg-surface-800" href="/dashboard/admin/filings">Upload filings</a>
            <a class="rounded-2xl border border-surface-200 px-4 py-3 hover:bg-surface-100 dark:border-surface-700 dark:hover:bg-surface-800" href="/dashboard/admin/companies">Add company</a>
            <a class="rounded-2xl border border-surface-200 px-4 py-3 hover:bg-surface-100 dark:border-surface-700 dark:hover:bg-surface-800" href="/dashboard/admin/users">Invite teammates</a>
        </div>'
    );

    echo ui_card('System status',
        '<ul class="space-y-2 text-sm text-surface-600 dark:text-surface-300">
            <li>• API latency <strong>82ms</strong></li>
            <li>• Queue jobs <strong>3 pending</strong></li>
            <li>• Database replication <strong>healthy</strong></li>
        </ul>',
        [ui_button('Open system', 'ghost', ['href' => '/dashboard/admin/settings#system'])]
    );
    ?>
  </div>

  <?php
    $columns = [
        ['label' => 'Ticker', 'key' => 'ticker', 'sortable' => true],
        ['label' => 'Sector', 'key' => 'sector', 'sortable' => true],
        ['label' => 'Verdict', 'key' => 'verdict'],
        ['label' => 'Updated', 'key' => 'updated', 'sortable' => true],
    ];
    $rows = [
        ['ticker' => 'TCS', 'sector' => 'IT', 'verdict' => ui_badge('Pass', 'success'), 'updated' => '1h ago'],
        ['ticker' => 'RELI', 'sector' => 'Energy', 'verdict' => ui_badge('Monitor', 'warn'), 'updated' => '4h ago'],
        ['ticker' => 'ADNOC', 'sector' => 'Energy', 'verdict' => ui_badge('Review', 'danger'), 'updated' => '1d ago'],
    ];
    echo ui_table($columns, $rows, ['id' => 'admin_companies']);
  ?>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../../layout.php';
