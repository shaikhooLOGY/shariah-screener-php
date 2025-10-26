<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Home';
$breadcrumbs = [];
ob_start();
?>
<section class="hero-gradient rounded-3xl border border-surface-200 px-6 py-12 shadow-sm dark:border-surface-800">
  <div class="mx-auto flex max-w-4xl flex-col items-start gap-6 text-left">
    <span class="badge-soft">Shari'ah Intelligence Platform</span>
    <h1 class="text-4xl font-semibold tracking-tight text-surface-900 dark:text-white sm:text-5xl">
      Build trust with transparent, auditable Shari'ah screening workflows.
    </h1>
    <p class="max-w-2xl text-base text-surface-600 dark:text-surface-300">
      Shaikhoology helps compliance teams, scholars, and investors collaborate on the latest filings. Capture evidence, compare standards, and ship verdict-ready disclosures in hours.
    </p>
    <div class="flex flex-wrap items-center gap-3">
      <?php echo ui_button('Explore Companies', 'primary', ['href' => '/companies']); ?>
      <?php echo ui_button('See Methodology', 'soft', ['href' => '/methodology']); ?>
    </div>
  </div>
</section>

<section class="mt-12 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
  <?php
  $cards = [
    [
      'title' => 'Evidence-first reviews',
      'desc'  => 'Link every ratio to its source, apply caps per standard, and roll forward quarterly audits with version control.'
    ],
    [
      'title' => 'Scholar collaboration',
      'desc'  => 'Give ulama a focused dashboard for rulings, discussion threads, and purification guidance.'
    ],
    [
      'title' => 'Publish confidently',
      'desc'  => 'Generate verdict-ready summaries, compliance memos, and exportable CSV snapshots in one click.'
    ],
  ];
  foreach ($cards as $card): ?>
    <div class="rounded-2xl border border-surface-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg dark:border-surface-800 dark:bg-surface-900">
      <h2 class="text-lg font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($card['title']); ?></h2>
      <p class="mt-3 text-sm text-surface-600 dark:text-surface-300"><?php echo htmlspecialchars($card['desc']); ?></p>
    </div>
  <?php endforeach; ?>
</section>

<section class="mt-16 grid gap-6 lg:grid-cols-2">
  <div class="rounded-2xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
    <h2 class="text-xl font-semibold text-surface-900 dark:text-white">Trusted by markets and scholars</h2>
    <p class="mt-3 text-sm text-surface-600 dark:text-surface-300">
      From listing reviews to periodic audits, Shaikhoology keeps analysts, Shari'ah boards, and regulators aligned. Monitor caps in real-time, surface breaches early, and attach reasoning that investors can understand.
    </p>
    <ul class="mt-4 space-y-3 text-sm text-surface-600 dark:text-surface-300">
      <li>• Supports AAOIFI, OJK, DJP, and regional screening standards</li>
      <li>• Works with both SQLite (local dev) and MySQL (production)</li>
      <li>• API-ready JSON verdicts for downstream investor portals</li>
    </ul>
  </div>
  <div class="rounded-2xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
    <h2 class="text-xl font-semibold text-surface-900 dark:text-white">Get started in minutes</h2>
    <ol class="mt-4 space-y-3 text-sm text-surface-600 dark:text-surface-300">
      <li><strong>Seed locally.</strong> Run <code>composer seed:sqlite</code> to load sample records.</li>
      <li><strong>Review the dashboard.</strong> Explore ulama and admin views, validate caps, and submit notes.</li>
      <li><strong>Deploy to Hostinger.</strong> Upload the build zip, configure the .env, and verify /prod-health.</li>
    </ol>
    <div class="mt-5 flex flex-wrap gap-3">
      <?php echo ui_button('View Dashboard', 'soft', ['href' => '/dashboard/admin']); ?>
      <?php echo ui_button('Read Docs', 'link', ['href' => '/methodology']); ?>
    </div>
  </div>
</section>

<section class="mt-16 rounded-3xl border border-dashed border-surface-300 bg-white/60 p-6 text-center shadow-sm dark:border-surface-700 dark:bg-surface-900/70">
  <h2 class="text-xl font-semibold text-surface-900 dark:text-white">Ready to evolve your screening workflow?</h2>
  <p class="mt-2 text-sm text-surface-600 dark:text-surface-300">Invite your Shari'ah board, upload supporting evidence, and publish reliable verdicts every quarter.</p>
  <div class="mt-4 flex justify-center gap-3">
    <?php echo ui_button('Request a walkthrough', 'primary', ['href' => '/contact']); ?>
    <?php echo ui_button('See standards comparison', 'ghost', ['href' => '/standards']); ?>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
