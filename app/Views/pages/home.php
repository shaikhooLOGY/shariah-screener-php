<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Home';
$breadcrumbs = [];
ob_start();
?>
<section class="relative overflow-hidden rounded-3xl border border-surface-200 bg-white px-6 py-16 text-center shadow-sm theme-dark:border-surface-700 theme-dark:bg-surface-900">
  <div class="absolute inset-0 -z-10 bg-gradient-to-br from-indigo-500/10 via-transparent to-emerald-500/10 dark:from-indigo-500/20 dark:to-emerald-500/20"></div>
  <div class="mx-auto flex max-w-4xl flex-col items-center gap-6">
    <span class="badge-soft">Search-first Shari'ah console</span>
    <h1 class="text-4xl font-semibold tracking-tight text-surface-900 dark:text-white sm:text-5xl">
      Find halal verdicts, evidence, and scholar guidance instantly.
    </h1>
    <p class="max-w-2xl text-base text-surface-600 dark:text-surface-300">
      Type a ticker, sector, or keyword. Shaikhoology surfaces the latest filings, computed ratios, overrides, and discussion threads – all audit-ready.
    </p>
    <form action="/go" method="get" class="w-full max-w-3xl">
      <div class="flex flex-col gap-4">
        <label class="sr-only" for="hero-search">Search tickers or sectors</label>
        <div class="relative flex items-center gap-3 rounded-full border border-surface-300 bg-white px-4 py-2 shadow-sm focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500 dark:border-surface-700 dark:bg-surface-900">
          <span class="text-surface-400 dark:text-surface-500">🔎</span>
          <input id="hero-search" type="search" name="q" placeholder="Enter ticker symbol or search query…" required class="w-full bg-transparent text-base text-surface-900 placeholder-surface-400 focus:outline-none dark:text-surface-100 dark:placeholder-surface-500">
          <button type="submit" class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400">Search</button>
        </div>
        <p class="text-xs text-surface-500 dark:text-surface-400">Enter a ticker like "TCS" to view company details, or search for sectors/keywords.</p>
      </div>
    </form>
    <div class="flex flex-wrap items-center justify-center gap-3 text-xs text-surface-500 dark:text-surface-400">
      <span class="inline-flex items-center gap-1 rounded-full border border-surface-300 px-3 py-1">⚡ Published CMV data</span>
      <span class="inline-flex items-center gap-1 rounded-full border border-surface-300 px-3 py-1">🧾 Full audit trails</span>
      <span class="inline-flex items-center gap-1 rounded-full border border-surface-300 px-3 py-1">👥 Scholar-verified</span>
    </div>
  </div>
</section>

<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
