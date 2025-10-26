<?php
require_once __DIR__.'/../partials/ui.php';
$title = ($company['name'] ?? $symbol ?? 'Company').' profile';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Companies', 'href' => '/companies'],
    ['label' => $symbol ?? 'Company', 'href' => '/company/'.urlencode($symbol ?? '')],
];
$hasFiling = !empty($filing);
$verdictTone = ($verdict ?? '') === 'Pass' ? 'success' : (($verdict ?? '') === 'Fail' ? 'danger' : 'info');
$capLegend = [];
foreach (($caps ?? []) as $key => $value) {
    $capLegend[] = strtoupper($key).' ≤ '.number_format($value * 100, 2).'%';
}

if ($hasFiling) {
    $ratioValues = [
        'Debt / Assets' => $ratios['debt_pct'] ?? 0,
        'Interest / Revenue' => $ratios['interest_pct'] ?? 0,
        'Liquid / Assets' => $ratios['liquid_pct'] ?? 0,
        'Non-Shariah / Revenue' => $ratios['nonsh_pct'] ?? 0,
    ];

    $ratioChart = json_encode([
        'type' => 'bar',
        'data' => [
            'labels' => array_keys($ratioValues),
            'datasets' => [[
                'label' => 'Current',
                'data' => array_values(array_map(fn($v) => round($v * 100, 2), $ratioValues)),
                'backgroundColor' => ['#6366f1', '#22d3ee', '#a855f7', '#f97316'],
            ]],
        ],
        'options' => [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                ],
            ],
            'plugins' => ['legend' => ['display' => false]],
        ],
    ], JSON_UNESCAPED_SLASHES);

    $assetsMix = [
        'Cash' => (float)($filing['cash'] ?? 0),
        'Receivables' => (float)($filing['receivables'] ?? 0),
    ];
    $otherAssets = max(0, (float)($filing['total_assets'] ?? 0) - array_sum($assetsMix));
    $assetsMix['Other assets'] = $otherAssets;

    $assetsChart = json_encode([
        'type' => 'doughnut',
        'data' => [
            'labels' => array_keys($assetsMix),
            'datasets' => [[
                'data' => array_values($assetsMix),
                'backgroundColor' => ['#6366f1', '#22d3ee', '#475569'],
            ]],
        ],
        'options' => [
            'plugins' => ['legend' => ['position' => 'bottom']],
        ],
    ]);

    $incomeMix = [
        'Operating income' => max(0, (float)($filing['revenue'] ?? 0) - (float)($filing['interest_income'] ?? 0) - (float)($filing['non_shariah_income'] ?? 0)),
        'Interest income' => (float)($filing['interest_income'] ?? 0),
        'Non-Shari\'ah income' => (float)($filing['non_shariah_income'] ?? 0),
    ];

    $incomeChart = json_encode([
        'type' => 'polarArea',
        'data' => [
            'labels' => array_keys($incomeMix),
            'datasets' => [[
                'data' => array_values($incomeMix),
                'backgroundColor' => ['#818cf8', '#f97316', '#ef4444'],
            ]],
        ],
        'options' => [
            'plugins' => ['legend' => ['position' => 'bottom']],
        ],
    ]);
}

ob_start();
?>
<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
  <section class="space-y-6">
    <div class="rounded-3xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
      <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div class="space-y-2">
          <div class="flex items-center gap-3">
            <?php echo ui_badge($verdict ?? 'Pending', $verdictTone); ?>
            <span class="rounded-full border border-surface-200 px-3 py-1 text-xs uppercase tracking-wide text-surface-500 dark:border-surface-700">Sector: <?php echo htmlspecialchars($company['sector'] ?? 'N/A'); ?></span>
          </div>
          <h1 class="text-3xl font-semibold tracking-tight text-surface-900 dark:text-white"><?php echo htmlspecialchars($company['ticker'] ?? $symbol); ?> · <?php echo htmlspecialchars($company['name'] ?? 'Unknown company'); ?></h1>
          <p class="text-sm text-surface-600 dark:text-surface-300"><?php echo htmlspecialchars($company['description'] ?? 'Screening summary powered by Shaikhoology.'); ?></p>
        </div>
        <div class="flex flex-wrap justify-end gap-2">
          <?php echo ui_button('Follow', 'soft', ['href' => '#']); ?>
          <?php echo ui_button('Discussion', 'ghost', ['href' => '/company/'.urlencode($symbol ?? '').'/discussion']); ?>
          <?php echo ui_button('Suggest ratios', 'ghost', ['href' => '/company/'.urlencode($symbol ?? '').'/suggest']); ?>
        </div>
      </div>
    </div>

    <?php if ($hasFiling): ?>
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <?php echo ui_kpi('Debt / Assets', number_format(($ratios['debt_pct'] ?? 0) * 100, 2).'%', ($ratios['debt_pct'] ?? 0) <= ($caps['debt'] ?? 0.33) ? '+Cap OK' : '-Cap exceeded'); ?>
        <?php echo ui_kpi('Interest / Revenue', number_format(($ratios['interest_pct'] ?? 0) * 100, 2).'%', ($ratios['interest_pct'] ?? 0) <= ($caps['interest'] ?? 0.05) ? '+Within cap' : '-Review'); ?>
        <?php echo ui_kpi('(Cash + Receivables) / Assets', number_format(($ratios['liquid_pct'] ?? 0) * 100, 2).'%', ($ratios['liquid_pct'] ?? 0) <= ($caps['liquid'] ?? 0.7) ? '+Balanced' : '-High'); ?>
        <?php echo ui_kpi('Non-Shari\'ah / Revenue', number_format(($ratios['nonsh_pct'] ?? 0) * 100, 2).'%', ($ratios['nonsh_pct'] ?? 0) <= ($caps['nonsh'] ?? 0.05) ? '+Within tolerance' : '-Purify'); ?>
      </div>

      <div class="rounded-3xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900" x-data="{tab: 'ratios'}">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Visual analytics</h2>
          <div class="chart-tabs">
            <button type="button" :class="{ 'is-active': tab === 'ratios' }" x-on:click="tab='ratios'">Ratios</button>
            <button type="button" :class="{ 'is-active': tab === 'assets' }" x-on:click="tab='assets'">Assets mix</button>
            <button type="button" :class="{ 'is-active': tab === 'income' }" x-on:click="tab='income'">Income mix</button>
          </div>
        </div>
        <div class="mt-6" x-show="tab==='ratios'">
          <div class="chart-card" data-chart='<?php echo $ratioChart; ?>'>
            <canvas aria-label="Ratio chart" role="img"></canvas>
          </div>
        </div>
        <div class="mt-6" x-show="tab==='assets'" x-cloak>
          <div class="chart-card" data-chart='<?php echo $assetsChart; ?>'>
            <canvas aria-label="Asset mix" role="img"></canvas>
          </div>
        </div>
        <div class="mt-6" x-show="tab==='income'" x-cloak>
          <div class="chart-card" data-chart='<?php echo $incomeChart; ?>'>
            <canvas aria-label="Income mix" role="img"></canvas>
          </div>
        </div>
      </div>

      <div class="rounded-3xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
        <div class="table-toolbar">
          <div>
            <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Latest filing · <?php echo htmlspecialchars($filing['period'] ?? 'N/A'); ?></h2>
            <p class="text-xs text-surface-500">Filed on <?php echo htmlspecialchars($filing['filing_date'] ?? 'Unknown'); ?></p>
          </div>
          <div class="toolbar-actions">
            <button type="button" class="rounded-full border border-surface-200 px-3 py-1 text-xs font-semibold hover:bg-surface-100 dark:border-surface-700 dark:hover:bg-surface-800" data-csv-target="#filing-table" data-csv-name="<?php echo htmlspecialchars(($company['ticker'] ?? 'company').'-filing.csv'); ?>">Download CSV</button>
          </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-surface-200 shadow-sm dark:border-surface-800">
          <table id="filing-table" class="min-w-full text-sm text-surface-600 dark:text-surface-200">
            <thead class="bg-surface-100 text-xs uppercase tracking-wide text-surface-500 dark:bg-surface-800 dark:text-surface-300">
              <tr>
                <th class="px-4 py-3 text-left">Metric</th>
                <th class="px-4 py-3 text-right">Value</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-surface-200 bg-white dark:divide-surface-800 dark:bg-surface-900">
              <tr><td class="px-4 py-3">Total assets</td><td class="px-4 py-3 text-right"><?php echo number_format((float)$filing['total_assets'], 2); ?></td></tr>
              <tr><td class="px-4 py-3">Total debt</td><td class="px-4 py-3 text-right"><?php echo number_format((float)$filing['total_debt'], 2); ?></td></tr>
              <tr><td class="px-4 py-3">Cash</td><td class="px-4 py-3 text-right"><?php echo number_format((float)$filing['cash'], 2); ?></td></tr>
              <tr><td class="px-4 py-3">Receivables</td><td class="px-4 py-3 text-right"><?php echo number_format((float)$filing['receivables'], 2); ?></td></tr>
              <tr><td class="px-4 py-3">Revenue</td><td class="px-4 py-3 text-right"><?php echo number_format((float)$filing['revenue'], 2); ?></td></tr>
              <tr><td class="px-4 py-3">Interest income</td><td class="px-4 py-3 text-right"><?php echo number_format((float)$filing['interest_income'], 2); ?></td></tr>
              <tr><td class="px-4 py-3">Non-Shari'ah income</td><td class="px-4 py-3 text-right"><?php echo number_format((float)$filing['non_shariah_income'], 2); ?></td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
          <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Why this verdict?</h2>
          <?php if (!empty($why)): ?>
            <div class="mt-4 space-y-3">
              <?php foreach ($why as $reason): ?>
                <div class="rounded-2xl border border-rose-200 bg-rose-500/10 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/15 dark:text-rose-200">
                  <?php echo htmlspecialchars($reason); ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <?php echo ui_alert('success', 'All ratios within configured caps.'); ?>
          <?php endif; ?>
        </div>
        <div class="rounded-3xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
          <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Caps legend</h2>
          <ul class="mt-3 space-y-2 text-sm text-surface-600 dark:text-surface-300">
            <?php foreach ($capLegend as $cap): ?>
              <li>• <?php echo htmlspecialchars($cap); ?></li>
            <?php endforeach; ?>
          </ul>
          <a class="mt-4 inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-300" href="/methodology">View methodology →</a>
        </div>
      </div>
    <?php else: ?>
      <div class="mt-6">
        <?php echo ui_empty('No filings yet', 'We have not ingested filings for this company. Seed local data or import filings from the admin console.', ui_button('Import filings', 'primary', ['href' => '/dashboard/admin/filings'])); ?>
      </div>
    <?php endif; ?>
  </section>

  <aside class="space-y-6">
    <div class="rounded-3xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
      <h2 class="text-base font-semibold text-surface-900 dark:text-white">Next actions</h2>
      <ul class="mt-3 space-y-2 text-sm text-surface-600 dark:text-surface-300">
        <li>• Invite scholars via the Ulama dashboard</li>
        <li>• Attach supporting evidence and minutes</li>
        <li>• Publish investor memo once verdict finalises</li>
      </ul>
      <div class="mt-4 flex flex-col gap-2">
        <?php echo ui_button('Open Ulama queue', 'primary', ['href' => '/dashboard/ulama/reviews']); ?>
        <?php echo ui_button('Add evidence', 'ghost', ['href' => '#']); ?>
      </div>
    </div>
    <div class="rounded-3xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
      <h2 class="text-base font-semibold text-surface-900 dark:text-white">Need help?</h2>
      <p class="text-sm text-surface-600 dark:text-surface-300">Contact our Shari'ah support team for bespoke configurations or data imports.</p>
      <div class="mt-4 flex flex-col gap-2 text-sm">
        <?php echo ui_button('Contact support', 'soft', ['href' => '/contact']); ?>
        <?php echo ui_button('Open documentation', 'link', ['href' => '/methodology']); ?>
      </div>
    </div>
  </aside>
</div>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
