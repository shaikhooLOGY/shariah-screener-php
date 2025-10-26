<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Standards comparison';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Methodology', 'href' => '/methodology'],
    ['label' => 'Standards', 'href' => '/standards'],
];
$standards = [
    ['name' => 'AAOIFI', 'jurisdiction' => 'Global', 'focus' => 'Classical caps and purification workflows'],
    ['name' => 'OJK Indonesia', 'jurisdiction' => 'Indonesia', 'focus' => 'Sector-specific revenue thresholds'],
    ['name' => 'Malaysia SC', 'jurisdiction' => 'Malaysia', 'focus' => 'Qualitative + quantitative blend'],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <header class="space-y-3">
    <h1 class="text-3xl font-semibold tracking-tight text-surface-900 dark:text-white">Compare screening standards</h1>
    <p class="max-w-3xl text-sm text-surface-600 dark:text-surface-300">Toggle between AAOIFI, OJK, Malaysia SC, and custom regional frameworks. Each preset updates ratio caps, purification guidance, and recommended disclosures.</p>
  </header>

  <div class="mt-8 overflow-hidden rounded-2xl border border-surface-200 shadow-sm dark:border-surface-800">
    <table class="min-w-full text-sm text-surface-600 dark:text-surface-200">
      <thead class="bg-surface-100 text-xs uppercase tracking-wide text-surface-500 dark:bg-surface-800 dark:text-surface-300">
        <tr>
          <th class="px-4 py-3 text-left">Standard</th>
          <th class="px-4 py-3 text-left">Jurisdiction</th>
          <th class="px-4 py-3 text-left">Focus</th>
          <th class="px-4 py-3 text-left">Caps</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-surface-200 bg-white dark:divide-surface-800 dark:bg-surface-900">
        <?php foreach ($standards as $standard): ?>
        <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/60">
          <td class="px-4 py-3 font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($standard['name']); ?></td>
          <td class="px-4 py-3"><?php echo htmlspecialchars($standard['jurisdiction']); ?></td>
          <td class="px-4 py-3 text-surface-500 dark:text-surface-400"><?php echo htmlspecialchars($standard['focus']); ?></td>
          <td class="px-4 py-3">
            <?php echo ui_badge('View caps', 'info'); ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="mt-8 rounded-2xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
    <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Need a bespoke configuration?</h2>
    <p class="mt-2 text-sm text-surface-600 dark:text-surface-300">Use feature flags to enable experimental caps or upcoming regulatory drafts. Superadmins can manage variants from the System tab.</p>
    <div class="mt-4 flex gap-2">
      <?php echo ui_button('Open settings', 'primary', ['href' => '/dashboard/admin/settings']); ?>
      <?php echo ui_button('Contact us', 'ghost', ['href' => '/contact']); ?>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
