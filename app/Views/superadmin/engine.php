<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Superadmin · Screening engine';
ob_start();
?>
<section class="space-y-6" x-data="engineRunner(<?php echo $activeJobId ? (int)$activeJobId : 'null'; ?>)">
  <header class="space-y-2">
    <h1 class="text-2xl font-semibold text-surface-900 dark:text-white">Screening engine control</h1>
    <p class="text-sm text-surface-600 dark:text-surface-300">Abhi chalayein (Run Now) to recompute ratios. Dry run pehle dekh lein agar verify karna hai.</p>
  </header>

  <div class="grid gap-4 lg:grid-cols-3">
    <form method="post" action="/sa/engine/run" class="rounded-2xl border border-surface-200 bg-white p-5 shadow-sm dark:border-surface-800 dark:bg-surface-900">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
      <input type="hidden" name="scope" value="ticker">
      <h2 class="text-base font-semibold text-surface-900 dark:text-white">Single ticker</h2>
      <p class="mt-1 text-xs text-surface-500">Specific company ko dobara screen karein.</p>
      <input name="value" placeholder="e.g. TCS" class="mt-4 w-full rounded-xl border border-surface-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-800 dark:text-surface-100" />
      <label class="mt-3 flex items-center gap-2 text-xs text-surface-500">
        <input type="checkbox" name="dry" class="h-4 w-4" /> Dry run
      </label>
      <label class="mt-1 flex items-center gap-2 text-xs text-surface-500">
        <input type="checkbox" name="notify" class="h-4 w-4" checked /> Notify on completion
      </label>
      <?php echo ui_button('Abhi chalayein', 'primary', ['class' => 'mt-4 w-full']); ?>
    </form>

    <form method="post" action="/sa/engine/run" class="rounded-2xl border border-surface-200 bg-white p-5 shadow-sm dark:border-surface-800 dark:bg-surface-900">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
      <input type="hidden" name="scope" value="sector">
      <h2 class="text-base font-semibold text-surface-900 dark:text-white">Sector wise</h2>
      <p class="mt-1 text-xs text-surface-500">Pure sector ko dobara evaluate karein.</p>
      <input name="value" placeholder="e.g. IT" class="mt-4 w-full rounded-xl border border-surface-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-800 dark:text-surface-100" />
      <label class="mt-3 flex items-center gap-2 text-xs text-surface-500">
        <input type="checkbox" name="dry" class="h-4 w-4" /> Dry run
      </label>
      <label class="mt-1 flex items-center gap-2 text-xs text-surface-500">
        <input type="checkbox" name="notify" class="h-4 w-4" /> Notify on completion
      </label>
      <?php echo ui_button('Abhi chalayein', 'primary', ['class' => 'mt-4 w-full']); ?>
    </form>

    <form method="post" action="/sa/engine/run" class="rounded-2xl border border-surface-200 bg-white p-5 shadow-sm dark:border-surface-800 dark:bg-surface-900">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
      <input type="hidden" name="scope" value="all">
      <h2 class="text-base font-semibold text-surface-900 dark:text-white">Full recompute</h2>
      <p class="mt-1 text-xs text-surface-500">Saare tickers ko dobara chalao. Thoda waqt lag sakta hai.</p>
      <label class="mt-3 flex items-center gap-2 text-xs text-surface-500">
        <input type="checkbox" name="dry" class="h-4 w-4" /> Dry run only
      </label>
      <label class="mt-1 flex items-center gap-2 text-xs text-surface-500">
        <input type="checkbox" name="notify" class="h-4 w-4" checked /> Notify on completion
      </label>
      <?php echo ui_button('Full run start karain', 'danger', ['class' => 'mt-4 w-full']); ?>
    </form>
  </div>

  <section class="rounded-3xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900" x-init="initPolling()">
    <div class="flex items-center justify-between">
      <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Job history</h2>
      <template x-if="polling">
        <span class="text-xs text-indigo-500">Running…</span>
      </template>
    </div>
    <div class="mt-4 overflow-x-auto">
      <table class="min-w-full text-sm text-surface-600 dark:text-surface-200">
        <thead class="bg-surface-100 text-xs uppercase tracking-wide text-surface-500 dark:bg-surface-800 dark:text-surface-300">
          <tr>
            <th class="px-4 py-3 text-left">ID</th>
            <th class="px-4 py-3 text-left">Scope</th>
            <th class="px-4 py-3 text-left">Value</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Summary</th>
            <th class="px-4 py-3 text-left">Started</th>
            <th class="px-4 py-3 text-left">Finished</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-surface-200 dark:divide-surface-800">
          <?php foreach ($jobs as $job): ?>
            <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/70">
              <td class="px-4 py-3 text-xs font-semibold">#<?php echo (int)$job['id']; ?></td>
              <td class="px-4 py-3 text-xs text-surface-500"><?php echo htmlspecialchars($job['scope']); ?></td>
              <td class="px-4 py-3 text-xs text-surface-500"><?php echo htmlspecialchars($job['value'] ?? '-'); ?></td>
              <td class="px-4 py-3 text-xs"><?php echo ui_badge($job['status'], match($job['status']){ 'success'=>'success','failed'=>'danger','running'=>'warn', default=>'neutral'}); ?></td>
              <td class="px-4 py-3 text-xs text-surface-500">
                <?php $summary = $job['summary'] ?? []; echo htmlspecialchars($summary['message'] ?? '—'); ?>
              </td>
              <td class="px-4 py-3 text-xs text-surface-500"><?php echo htmlspecialchars($job['started_at'] ?? '-'); ?></td>
              <td class="px-4 py-3 text-xs text-surface-500"><?php echo htmlspecialchars($job['finished_at'] ?? '-'); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</section>

<script>
function engineRunner(activeJobId){
  return {
    polling: false,
    jobId: activeJobId,
    initPolling(){
      if (!this.jobId) return;
      this.polling = true;
      this.poll();
    },
    poll(){
      if (!this.jobId) return;
      fetch(`/dashboard/superadmin/engine?job=${this.jobId}&poll=1`)
        .then(res => res.json())
        .then(data => {
          if (data.status && data.status !== 'running') {
            this.polling = false;
            window.AppUI && window.AppUI.toast && window.AppUI.toast('info', 'Engine run completed');
          } else {
            setTimeout(() => this.poll(), 2000);
          }
        })
        .catch(() => {
          this.polling = false;
        });
    }
  };
}
</script>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
