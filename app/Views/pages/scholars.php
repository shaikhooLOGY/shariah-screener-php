<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Scholar board';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Scholars', 'href' => '/scholars'],
];
$scholars = [
    ['name' => 'Dr. Fatima Al-Faruqi', 'expertise' => 'AAOIFI, GCC', 'bio' => 'Former chair of multiple GCC Shari\'ah boards with focus on equities and REITs.'],
    ['name' => 'Sheikh Yusuf Rahman', 'expertise' => 'ASEAN', 'bio' => 'Focus on fintech, sukuk, and digital assets in South East Asia.'],
    ['name' => 'Dr. Ahmed Idris', 'expertise' => 'North Africa', 'bio' => 'Guides purification methodologies and hybrid financing structures.'],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-3xl font-semibold tracking-tight text-surface-900 dark:text-white">Meet the scholar board</h1>
      <p class="mt-2 max-w-2xl text-sm text-surface-600 dark:text-surface-300">Invite trusted ulama or onboard your own board. Scholars receive contextual screens, ratio drill-downs, and audit trails.</p>
    </div>
    <div class="flex gap-2">
      <?php echo ui_button('Request invite', 'primary', ['href' => '/contact']); ?>
      <?php echo ui_button('Open reviews', 'ghost', ['href' => '/dashboard/ulama/reviews']); ?>
    </div>
  </header>

  <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
    <?php foreach ($scholars as $scholar): ?>
      <article class="rounded-2xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
        <div class="flex items-center gap-3">
          <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-500 text-sm font-semibold text-white"><?php echo substr($scholar['name'], 0, 1); ?></span>
          <div>
            <h2 class="text-base font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($scholar['name']); ?></h2>
            <p class="text-xs uppercase tracking-wide text-surface-500 dark:text-surface-400"><?php echo htmlspecialchars($scholar['expertise']); ?></p>
          </div>
        </div>
        <p class="mt-3 text-sm text-surface-600 dark:text-surface-300"><?php echo htmlspecialchars($scholar['bio']); ?></p>
        <div class="mt-4 flex gap-2 text-xs">
          <?php echo ui_button('View profile', 'soft', ['href' => '/scholar/example-scholar']); ?>
          <?php echo ui_button('Message', 'link', ['href' => '/discussions']); ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
