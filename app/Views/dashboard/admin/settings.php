<?php
require_once __DIR__.'/../../partials/ui.php';
$title = 'Platform settings';
$role = $_SESSION['role'] ?? 'admin';
$active = $_GET['tab'] ?? 'general';
$config = require dirname(__DIR__, 3) . '/config/screening.php';
$caps = $config['caps'] ?? ['debt' => 0.33, 'interest' => 0.05, 'liquid' => 0.7, 'nonsh' => 0.05];
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Admin dashboard', 'href' => '/dashboard/admin'],
    ['label' => 'Settings', 'href' => '/dashboard/admin/settings'],
];
$tabs = [
    ['id' => 'general', 'label' => 'General', 'href' => '/dashboard/admin/settings?tab=general'],
    ['id' => 'notifications', 'label' => 'Notifications', 'href' => '/dashboard/admin/settings?tab=notifications'],
];
if ($role === 'superadmin') {
    $tabs[] = ['id' => 'system', 'label' => 'System', 'href' => '/dashboard/admin/settings?tab=system'];
}
$csrf = $_SESSION['csrf'] ?? '';
ob_start();
?>
<section class="space-y-6">
  <header class="space-y-2">
    <h1 class="text-2xl font-semibold text-surface-900 dark:text-white">Settings</h1>
    <p class="text-sm text-surface-600 dark:text-surface-300">Configure screening defaults, notifications, and system operations.</p>
  </header>

  <?php echo ui_tabs($tabs, $active); ?>

  <?php if ($active === 'general'): ?>
    <form method="post" action="/dashboard/admin/settings" class="grid gap-6 rounded-3xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
      <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Screening defaults</h2>
      <div class="grid gap-4 md:grid-cols-2">
        <?php echo ui_input('cap_debt', 'Debt cap (%)', 'number', (string)(($caps['debt'] ?? 0.33) * 100)); ?>
        <?php echo ui_input('cap_interest', 'Interest cap (%)', 'number', (string)(($caps['interest'] ?? 0.05) * 100)); ?>
        <?php echo ui_input('cap_liquid', 'Liquidity cap (%)', 'number', (string)(($caps['liquid'] ?? 0.7) * 100)); ?>
        <?php echo ui_input('cap_nonsh', 'Non-Shari\'ah cap (%)', 'number', (string)(($caps['nonsh'] ?? 0.05) * 100)); ?>
      </div>
      <div class="flex gap-3">
        <?php echo ui_button('Save settings'); ?>
        <?php echo ui_button('Reset', 'ghost'); ?>
      </div>
    </form>
  <?php elseif ($active === 'notifications'): ?>
    <form method="post" action="/dashboard/admin/settings?tab=notifications" class="space-y-4 rounded-3xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
      <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Notifications</h2>
      <?php echo ui_toggle('notify_filings', 'Email me when new filings arrive', true); ?>
      <?php echo ui_toggle('notify_discussions', 'Notify me when scholars reply', false); ?>
      <?php echo ui_toggle('notify_health', 'System health alerts', true); ?>
      <div class="flex gap-3">
        <?php echo ui_button('Save preferences'); ?>
        <?php echo ui_button('Cancel', 'ghost'); ?>
      </div>
    </form>
  <?php elseif ($active === 'system' && $role === 'superadmin'): ?>
    <div class="space-y-6">
      <div class="rounded-3xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
        <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Feature flags</h2>
        <div class="mt-4 space-y-3 text-sm text-surface-600 dark:text-surface-300">
          <?php echo ui_toggle('flag_new_caps', 'Enable next-gen cap engine', false); ?>
          <?php echo ui_toggle('flag_api_v2', 'Expose API v2 beta', false); ?>
        </div>
      </div>
      <div class="rounded-3xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
        <h2 class="text-lg font-semibold text-surface-900 dark:text-white">System health</h2>
        <ul class="mt-3 space-y-2 text-sm text-surface-600 dark:text-surface-300">
          <li>• PHP <?php echo PHP_VERSION; ?></li>
          <li>• APP_ENV: <?php echo htmlspecialchars($_ENV['APP_ENV'] ?? 'n/a'); ?></li>
          <li>• Health endpoint: <a class="text-indigo-600" href="/health">/health</a></li>
        </ul>
      </div>
    </div>
  <?php endif; ?>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../../layout.php';
