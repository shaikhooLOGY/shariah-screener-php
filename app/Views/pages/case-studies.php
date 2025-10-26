<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Case studies';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Case studies', 'href' => '/case-studies'],
];
$cases = [
    ['company' => 'GCC Logistics', 'result' => 'Accelerated compliance sign-off by 3 weeks', 'desc' => 'Tracked debt covenant breaches in real time and published justification memo for investors.'],
    ['company' => 'ASEAN Fintech', 'result' => 'Unified scholar review across 4 markets', 'desc' => 'Used unified workflow to capture evidences, assign rulings, and export regulator-ready summary.'],
    ['company' => 'North Africa REIT', 'result' => 'Onboarded Shari\'ah board in 48 hours', 'desc' => 'Auto-generated purification calculations and collected scholar sign-off via dashboard.'],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <header class="space-y-3">
    <h1 class="text-3xl font-semibold tracking-tight text-surface-900 dark:text-white">Case studies &amp; wins</h1>
    <p class="max-w-3xl text-sm text-surface-600 dark:text-surface-300">See how Shari'ah boards and compliance teams use Shaikhoology to streamline evidence capture, ratio checks, and investor reporting.</p>
  </header>

  <div class="mt-8 grid gap-6 lg:grid-cols-3">
    <?php foreach ($cases as $case): ?>
      <article class="rounded-2xl border border-surface-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-surface-800 dark:bg-surface-900">
        <span class="badge-soft"><?php echo htmlspecialchars($case['company']); ?></span>
        <h2 class="mt-3 text-lg font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($case['result']); ?></h2>
        <p class="mt-2 text-sm text-surface-600 dark:text-surface-300"><?php echo htmlspecialchars($case['desc']); ?></p>
        <div class="mt-4 flex gap-3 text-xs text-surface-500">
          <?php echo ui_button('Read story', 'link', ['href' => '#']); ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <div class="mt-10 rounded-2xl border border-dashed border-surface-300 bg-surface-100/60 p-6 text-center dark:border-surface-700 dark:bg-surface-900/70">
    <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Ready to add your story?</h2>
    <p class="mt-2 text-sm text-surface-600 dark:text-surface-300">We partner with Shari'ah boards, fintechs, and asset managers to showcase emblematic screening journeys.</p>
    <div class="mt-4 flex justify-center gap-3">
      <?php echo ui_button('Share your results', 'primary', ['href' => '/contact']); ?>
      <?php echo ui_button('Download media kit', 'ghost', ['href' => '#']); ?>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
