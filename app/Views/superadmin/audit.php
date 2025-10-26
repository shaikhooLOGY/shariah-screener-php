<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Superadmin · Audit log';
ob_start();
?>
<section class="space-y-6">
  <header class="space-y-2">
    <h1 class="text-2xl font-semibold text-surface-900 dark:text-white">Audit log</h1>
    <p class="text-sm text-surface-600 dark:text-surface-300">Har promotion, flag toggle, ya engine run yahan capture hota hai.</p>
  </header>

  <form method="get" class="grid gap-4 rounded-3xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900 md:grid-cols-2 lg:grid-cols-3">
    <div>
      <label class="text-xs font-semibold text-surface-500">Actor</label>
      <input type="text" name="actor" value="<?php echo htmlspecialchars($filters['actor']); ?>" placeholder="ID ya naam" class="mt-1 w-full rounded-full border border-surface-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-800 dark:text-surface-100" />
    </div>
    <div>
      <label class="text-xs font-semibold text-surface-500">Action</label>
      <input type="text" name="action" value="<?php echo htmlspecialchars($filters['action']); ?>" placeholder="user.role" class="mt-1 w-full rounded-full border border-surface-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-800 dark:text-surface-100" />
    </div>
    <div>
      <label class="text-xs font-semibold text-surface-500">Entity</label>
      <input type="text" name="entity" value="<?php echo htmlspecialchars($filters['entity']); ?>" placeholder="company" class="mt-1 w-full rounded-full border border-surface-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-800 dark:text-surface-100" />
    </div>
    <div>
      <label class="text-xs font-semibold text-surface-500">From (date)</label>
      <input type="date" name="from" value="<?php echo htmlspecialchars($filters['from']); ?>" class="mt-1 w-full rounded-full border border-surface-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-800 dark:text-surface-100" />
    </div>
    <div>
      <label class="text-xs font-semibold text-surface-500">To (date)</label>
      <input type="date" name="to" value="<?php echo htmlspecialchars($filters['to']); ?>" class="mt-1 w-full rounded-full border border-surface-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-800 dark:text-surface-100" />
    </div>
    <div class="flex items-end gap-2">
      <?php echo ui_button('Filter karo'); ?>
      <?php echo ui_button('Reset', 'ghost', ['href' => '/dashboard/superadmin/audit']); ?>
      <?php echo ui_button('Export CSV', 'soft', ['href' => '/sa/audit/export']); ?>
    </div>
  </form>

  <div class="overflow-hidden rounded-3xl border border-surface-200 bg-white shadow-sm dark:border-surface-800 dark:bg-surface-900">
    <table class="min-w-full text-sm text-surface-600 dark:text-surface-200">
      <thead class="bg-surface-100 text-xs uppercase tracking-wide text-surface-500 dark:bg-surface-800 dark:text-surface-300">
        <tr>
          <th class="px-4 py-3 text-left">Time</th>
          <th class="px-4 py-3 text-left">Actor</th>
          <th class="px-4 py-3 text-left">Action</th>
          <th class="px-4 py-3 text-left">Entity</th>
          <th class="px-4 py-3 text-left">Meta</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-surface-200 dark:divide-surface-800">
        <?php if (!$logs): ?>
          <tr><td colspan="5" class="px-4 py-6 text-center text-xs text-surface-500">No entries found.</td></tr>
        <?php endif; ?>
        <?php foreach ($logs as $log): ?>
          <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/70">
            <td class="px-4 py-3 text-xs text-surface-500"><?php echo htmlspecialchars($log['created_at']); ?></td>
            <td class="px-4 py-3 text-xs text-surface-500"><?php echo htmlspecialchars($log['actor_name'] ?? 'System'); ?></td>
            <td class="px-4 py-3 text-xs font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($log['action']); ?></td>
            <td class="px-4 py-3 text-xs text-surface-500"><?php echo htmlspecialchars($log['entity']); ?> #<?php echo htmlspecialchars($log['entity_id']); ?></td>
            <td class="px-4 py-3 text-xs text-surface-500">
              <?php if ($log['meta']): ?>
                <code class="rounded-md bg-surface-100 px-2 py-1 text-[11px] dark:bg-surface-800">
                  <?php echo htmlspecialchars(json_encode($log['meta'], JSON_UNESCAPED_UNICODE)); ?>
                </code>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
