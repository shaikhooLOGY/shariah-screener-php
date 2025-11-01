<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Superadmin · System';
ob_start();
?>
<section class="space-y-6">
  <header class="space-y-2">
    <h1 class="text-2xl font-semibold text-surface-900 dark:text-white">System controls</h1>
    <p class="text-sm text-surface-600 dark:text-surface-300">Feature flags toggle karein, health monitor karein, aur logs ko jaldi se check karein.</p>
  </header>

  <div class="grid gap-6 lg:grid-cols-2">
  <div class="space-y-4 rounded-3xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Feature flags</h2>
      <span class="text-xs text-surface-500">Toggle karo aur turant effect dekho</span>
    </div>
    <div class="space-y-3 text-sm">
      <?php if (!$flags): ?>
        <p class="text-xs text-surface-500">No flags configured.</p>
      <?php endif; ?>
      <?php foreach ($flags as $flag): ?>
        <form method="post" action="/sa/flags/<?php echo htmlspecialchars($flag['key']); ?>" class="flex items-center justify-between gap-4 rounded-2xl border border-surface-200 px-4 py-3 dark:border-surface-700">
          <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
          <input type="hidden" name="label" value="<?php echo htmlspecialchars($flag['label']); ?>">
          <input type="hidden" name="value" value="<?php echo $flag['value'] ? 0 : 1; ?>">
          <div>
            <p class="font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($flag['label']); ?></p>
            <p class="text-xs text-surface-500">Key: <?php echo htmlspecialchars($flag['key']); ?></p>
          </div>
          <?php echo ui_button($flag['value'] ? 'Band karo' : 'Enable karo', $flag['value'] ? 'ghost' : 'primary'); ?>
        </form>
      <?php endforeach; ?>
    </div>
    <div class="border-t border-surface-200 pt-4 dark:border-surface-700">
      <h3 class="text-sm font-semibold text-surface-900 dark:text-white mb-3">UI Skin</h3>
      <form method="post" action="/sa/flags/ui.skin" class="flex items-center justify-between gap-4">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="label" value="UI Skin">
        <select name="skin_value" class="flex-1 rounded-lg border border-surface-300 px-3 py-2 text-sm">
          <option value="classic" <?php echo (env('UI_SKIN', 'classic') === 'classic') ? 'selected' : ''; ?>>Classic</option>
          <option value="aurora" <?php echo (env('UI_SKIN', 'classic') === 'aurora') ? 'selected' : ''; ?>>Aurora</option>
          <option value="noor" <?php echo (env('UI_SKIN', 'classic') === 'noor') ? 'selected' : ''; ?>>Noor</option>
        </select>
        <?php echo ui_button('Update Skin', 'primary'); ?>
      </form>
    </div>
  </div>

    <div class="space-y-4 rounded-3xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
      <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Health</h2>
      <div class="grid gap-3 text-sm text-surface-600 dark:text-surface-300">
        <div class="flex items-center justify-between">
          <span>Status</span>
          <?php echo ui_badge(strtoupper($health['status']), $health['status'] === 'ok' ? 'success' : 'danger'); ?>
        </div>
        <div class="flex items-center justify-between"><span>DB connect time</span><span><?php echo $health['db_time']; ?> ms</span></div>
        <div class="flex items-center justify-between"><span>Environment</span><span><?php echo htmlspecialchars($health['env']); ?></span></div>
        <?php if (!empty($health['error'])): ?>
          <p class="text-xs text-rose-500">Error: <?php echo htmlspecialchars($health['error']); ?></p>
        <?php endif; ?>
      </div>
      <div class="flex gap-2">
        <?php echo ui_button('Open /health', 'ghost', ['href' => '/health']); ?>
        <?php echo ui_button('Smoketest (diag)', 'soft', ['href' => '/diag.php', 'target' => '_blank']); ?>
      </div>
    </div>
  </div>

  <div class="rounded-3xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
    <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Recent audit logs</h2>
    <p class="text-xs text-surface-500">Last few actions. Pura history dekhne ke liye audit screen use karein.</p>
    <ul class="mt-4 space-y-2 text-sm text-surface-600 dark:text-surface-300">
      <?php if (!$logs): ?>
        <li>No recent entries.</li>
      <?php endif; ?>
      <?php foreach ($logs as $log): ?>
        <li class="rounded-2xl border border-surface-200 px-4 py-3 dark:border-surface-700">
          <span class="text-surface-900 dark:text-white"><?php echo htmlspecialchars($log['actor_name'] ?? 'System'); ?></span>
          ne <strong><?php echo htmlspecialchars($log['action']); ?></strong> (<?php echo htmlspecialchars($log['entity']); ?>#<?php echo htmlspecialchars($log['entity_id']); ?>) perform kiya · <span class="text-xs text-surface-500"><?php echo htmlspecialchars($log['created_at']); ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class="mt-4">
      <?php echo ui_button('Audit log dekho', 'link', ['href' => '/dashboard/superadmin/audit']); ?>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
