<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Companies';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Explore', 'href' => '/explore'],
    ['label' => 'Companies', 'href' => '/companies'],
];
$companies = [
    ['ticker' => 'TCS', 'name' => 'Tata Consultancy Services', 'sector' => 'IT Services', 'status' => 'Pass'],
    ['ticker' => 'RELI', 'name' => 'Reliance Industries', 'sector' => 'Conglomerate', 'status' => 'Monitor'],
    ['ticker' => 'ADNOC', 'name' => 'ADNOC Drilling', 'sector' => 'Energy', 'status' => 'Review'],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-3xl font-semibold tracking-tight text-surface-900 dark:text-white">Companies directory</h1>
      <p class="mt-2 max-w-2xl text-sm text-surface-600 dark:text-surface-300">Browse Shari'ah screening verdicts per ticker. Each entry links to detailed ratios, evidence, and scholar notes. Use the filter to jump to a company instantly.</p>
    </div>
    <div class="flex gap-2">
      <?php echo ui_button('Add company', 'soft', ['href' => '/dashboard/admin/companies']); ?>
      <?php echo ui_button('Import filings', 'ghost', ['href' => '/dashboard/admin/filings']); ?>
    </div>
  </header>

  <div x-data="filterCompanies()" class="mt-8 space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="relative w-full max-w-md">
        <input type="search" x-model="query" placeholder="Search ticker or company" class="w-full rounded-full border border-surface-200 bg-white px-5 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-800 dark:text-surface-100" />
        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-surface-400">🔎</span>
      </div>
      <div class="text-xs text-surface-500 dark:text-surface-400">
        Showing <span x-text="filtered.length"></span> of <?php echo count($companies); ?> tracked companies
      </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-surface-200 shadow-sm dark:border-surface-800">
      <table class="min-w-full divide-y divide-surface-200 text-sm dark:divide-surface-800">
        <thead class="bg-surface-100 text-xs uppercase tracking-wide text-surface-500 dark:bg-surface-800 dark:text-surface-300">
          <tr>
            <th class="px-4 py-3 text-left">Ticker</th>
            <th class="px-4 py-3 text-left">Company</th>
            <th class="px-4 py-3 text-left">Sector</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-surface-200 bg-white dark:divide-surface-800 dark:bg-surface-900">
        <?php foreach ($companies as $company): ?>
          <tr x-show="matches('<?php echo $company['ticker']; ?>', '<?php echo $company['name']; ?>')" class="hover:bg-surface-50 dark:hover:bg-surface-800/60">
            <td class="px-4 py-3 font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($company['ticker']); ?></td>
            <td class="px-4 py-3 text-surface-600 dark:text-surface-300"><?php echo htmlspecialchars($company['name']); ?></td>
            <td class="px-4 py-3 text-surface-500 dark:text-surface-400"><?php echo htmlspecialchars($company['sector']); ?></td>
            <td class="px-4 py-3">
              <?php
                $tone = match ($company['status']) {
                    'Pass' => 'success',
                    'Monitor' => 'warn',
                    'Review' => 'info',
                    default => 'neutral',
                };
                echo ui_badge($company['status'], $tone);
              ?>
            </td>
            <td class="px-4 py-3 text-right">
              <div class="inline-flex gap-2">
                <?php echo ui_button('Open', 'link', ['href' => '/company/'.$company['ticker']]); ?>
                <?php echo ui_button('Discuss', 'link', ['href' => '/company/'.$company['ticker'].'/discussion']); ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<script>
  function filterCompanies(){
    const items = <?php echo json_encode($companies); ?>;
    return {
      query: '',
      get filtered(){
        const q = this.query.toLowerCase();
        return items.filter(item => !q || item.ticker.toLowerCase().includes(q) || item.name.toLowerCase().includes(q));
      },
      matches(ticker, name){
        if (!this.query) return true;
        const q = this.query.toLowerCase();
        return ticker.toLowerCase().includes(q) || name.toLowerCase().includes(q);
      }
    };
  }
</script>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
