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
    <form action="/companies" method="get" class="w-full max-w-3xl">
      <div class="flex flex-col gap-4">
        <label class="sr-only" for="hero-search">Search tickers or sectors</label>
        <div class="relative flex items-center gap-3 rounded-full border border-surface-300 bg-white px-4 py-2 shadow-sm focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500 dark:border-surface-700 dark:bg-surface-900">
          <span class="text-surface-400 dark:text-surface-500">🔎</span>
          <input id="hero-search" type="search" name="q" placeholder="Search tickers, sectors, or halal verdicts…" required class="w-full bg-transparent text-base text-surface-900 placeholder-surface-400 focus:outline-none dark:text-surface-100 dark:placeholder-surface-500">
          <button type="submit" class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-400">Search</button>
        </div>
        <p class="text-xs text-surface-500 dark:text-surface-400">Press <kbd class="rounded border border-surface-300 bg-surface-100 px-2 py-0.5 text-[10px] dark:border-surface-700 dark:bg-surface-800">⌘K</kbd> / <kbd class="rounded border border-surface-300 bg-surface-100 px-2 py-0.5 text-[10px] dark:border-surface-700 dark:bg-surface-800">Ctrl K</kbd> to open the command palette.</p>
      </div>
    </form>
    <div class="flex flex-wrap items-center justify-center gap-3">
      <?php
      $chips = [
          ['href' => '/companies?status=pass', 'label' => 'Halal Pass'],
          ['href' => '/companies?status=watch', 'label' => 'Watchlist'],
          ['href' => '/companies?status=fail', 'label' => 'Flagged'],
          ['href' => '/companies?sector=it', 'label' => 'Tech / IT'],
          ['href' => '/companies?sector=finance', 'label' => 'Finance'],
          ['href' => '/companies?exchange=nse', 'label' => 'NSE India'],
      ];
      foreach ($chips as $chip): ?>
        <a href="<?php echo htmlspecialchars($chip['href']); ?>" class="inline-flex items-center rounded-full border border-surface-200 px-3 py-1 text-xs font-semibold text-surface-600 hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 dark:border-surface-700 dark:text-surface-300 dark:hover:border-indigo-500 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-200">
          <?php echo htmlspecialchars($chip['label']); ?>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="flex flex-wrap items-center justify-center gap-3 text-xs text-surface-500 dark:text-surface-400">
      <span class="inline-flex items-center gap-1 rounded-full border border-surface-300 px-3 py-1">⚡ Near real-time caps</span>
      <span class="inline-flex items-center gap-1 rounded-full border border-surface-300 px-3 py-1">🧾 Evidence snapshots</span>
      <span class="inline-flex items-center gap-1 rounded-full border border-surface-300 px-3 py-1">👥 Scholar collaboration</span>
    </div>
  </div>
</section>

<section class="mt-14 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
  <?php
  $cards = [
      [
          'title' => 'Muftis get clarity',
          'desc'  => 'Review suggested ratios, add rulings, and lock verdicts with full audit trails. Everything is optimized for fatwa committees.',
      ],
      [
          'title' => 'Admins stay in control',
          'desc'  => 'Manage company universes, override buckets, and trigger screening engine runs from one compact console.',
      ],
      [
          'title' => 'Investors stay aligned',
          'desc'  => 'Share lightweight profiles, CSV exports, and Shari\'ah memos with compliance desks and fund distributors.',
      ],
  ];
  foreach ($cards as $card): ?>
    <article class="rounded-2xl border border-surface-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg dark:border-surface-800 dark:bg-surface-900">
      <h2 class="text-lg font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($card['title']); ?></h2>
      <p class="mt-3 text-sm text-surface-600 dark:text-surface-300"><?php echo htmlspecialchars($card['desc']); ?></p>
    </article>
  <?php endforeach; ?>
</section>

<section class="mt-16 grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
  <article class="rounded-2xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
    <header class="flex items-center justify-between gap-3">
      <h2 class="text-xl font-semibold text-surface-900 dark:text-white">How Shaikhoology fits your day</h2>
      <?php echo ui_button('See methodology', 'link', ['href' => '/methodology']); ?>
    </header>
    <dl class="mt-6 space-y-4 text-sm text-surface-600 dark:text-surface-300">
      <div class="flex gap-3">
        <dt class="w-32 shrink-0 text-xs font-semibold uppercase tracking-wide text-surface-500 dark:text-surface-400">Morning</dt>
        <dd class="flex-1">Track overnight filings, highlight cap breaches, and assign reviews to your ulama bench.</dd>
      </div>
      <div class="flex gap-3">
        <dt class="w-32 shrink-0 text-xs font-semibold uppercase tracking-wide text-surface-500 dark:text-surface-400">During day</dt>
        <dd class="flex-1">Attach evidence scans, discuss rulings inline, and update overrides with reasoning in Hinglish or Arabic.</dd>
      </div>
      <div class="flex gap-3">
        <dt class="w-32 shrink-0 text-xs font-semibold uppercase tracking-wide text-surface-500 dark:text-surface-400">Evening</dt>
        <dd class="flex-1">Publish investor-ready summaries, export CSVs, and push updates to investor portals or regulators.</dd>
      </div>
    </dl>
  </article>
  <article class="rounded-2xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
    <h2 class="text-xl font-semibold text-surface-900 dark:text-white">Deploy in minutes</h2>
    <ol class="mt-4 space-y-3 text-sm text-surface-600 dark:text-surface-300">
      <li><strong>Seed locally.</strong> Run <code>composer seed:sqlite</code> to get demo data (users: secret).</li>
      <li><strong>Smoke test.</strong> Execute <code>composer smoke</code> to verify health, company pages, and guards.</li>
      <li><strong>Package for Hostinger.</strong> <code>composer build:zip</code> outputs a ready-to-upload bundle.</li>
    </ol>
    <div class="mt-5 flex flex-wrap gap-3">
      <?php echo ui_button('View admin console', 'soft', ['href' => '/dashboard/admin']); ?>
      <?php echo ui_button('Chat with team', 'ghost', ['href' => '/contact']); ?>
    </div>
  </article>
</section>

<section class="mt-16 rounded-3xl border border-dashed border-surface-300 bg-white/60 p-8 text-center shadow-sm dark:border-surface-700 dark:bg-surface-900/70">
  <h2 class="text-xl font-semibold text-surface-900 dark:text-white">Ready to audit your universe?</h2>
  <p class="mt-2 text-sm text-surface-600 dark:text-surface-300">Invite your Shari'ah board, capture rulings in Hinglish, and deliver confident disclosures every quarter.</p>
  <div class="mt-4 flex flex-wrap justify-center gap-3">
    <?php echo ui_button('Request walkthrough', 'primary', ['href' => '/contact']); ?>
    <?php echo ui_button('Compare standards', 'ghost', ['href' => '/standards']); ?>
    <?php echo ui_button('See latest verdict', 'link', ['href' => '/company/TCS']); ?>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
