<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Explore';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Explore', 'href' => '/explore'],
];
$datasets = [
    ['title' => 'Top screened sectors', 'badge' => 'Updated weekly', 'desc' => 'Filter by Shari\'ah compliance trends across IT, Energy, Healthcare, and Consumer Goods.'],
    ['title' => 'Watchlists & shortlists', 'badge' => 'Custom views', 'desc' => 'Save and share curated watchlists with analysts, scholars, and compliance partners.'],
    ['title' => 'Regional verdicts', 'badge' => 'Geo coverage', 'desc' => 'Compare rulings across GCC, ASEAN, UK, and North American markets instantly.'],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-3xl font-semibold tracking-tight text-surface-900 dark:text-white">Explore the coverage universe</h1>
      <p class="mt-2 max-w-2xl text-sm text-surface-600 dark:text-surface-300">Search across sectors, countries, and compliance status. Every record connects back to a filing, verdict, or scholar note so you can trust the data.</p>
    </div>
    <div class="flex gap-2">
      <?php echo ui_button('Browse Companies', 'primary', ['href' => '/companies']); ?>
      <?php echo ui_button('Download Methodology', 'ghost', ['href' => '/methodology']); ?>
    </div>
  </header>

  <div x-data="filterList()" class="mt-8 space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div class="relative w-full max-w-md">
        <input type="search" x-model="query" placeholder="Quick filter by keyword" class="w-full rounded-full border border-surface-200 bg-white px-5 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-800 dark:text-surface-100" />
        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-surface-400">🔍</span>
      </div>
      <div class="flex gap-2 text-xs text-surface-500 dark:text-surface-400">
        <span class="rounded-full border border-surface-200 px-3 py-1 dark:border-surface-700">220+ tickers</span>
        <span class="rounded-full border border-surface-200 px-3 py-1 dark:border-surface-700">14 jurisdictions</span>
      </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
      <?php foreach ($datasets as $index => $dataset): ?>
        <article class="rounded-2xl border border-surface-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-surface-800 dark:bg-surface-900" x-show="matches(<?php echo $index; ?>)">
          <div class="flex items-center justify-between text-xs text-surface-500 dark:text-surface-400">
            <span><?php echo htmlspecialchars($dataset['badge']); ?></span>
            <span>Dataset</span>
          </div>
          <h2 class="mt-3 text-lg font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($dataset['title']); ?></h2>
          <p class="mt-2 text-sm text-surface-600 dark:text-surface-300"><?php echo htmlspecialchars($dataset['desc']); ?></p>
          <div class="mt-4 flex gap-2">
            <?php echo ui_button('Open view', 'soft', ['href' => '/companies']); ?>
            <?php echo ui_button('Share', 'link', ['href' => '#']); ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<script>
  function filterList(){
    return {
      query: '',
      matches(index){
        if (!this.query) return true;
        const items = <?php echo json_encode(array_column($datasets, 'title')); ?>;
        return items[index].toLowerCase().includes(this.query.toLowerCase());
      }
    };
  }
</script>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
