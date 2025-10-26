<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Superadmin · Buckets';
ob_start();
?>
<section class="space-y-6" x-data="bucketManager('<?php echo htmlspecialchars($csrf); ?>')">
  <header class="space-y-2">
    <h1 class="text-2xl font-semibold text-surface-900 dark:text-white">Tri-bucket view</h1>
    <p class="text-sm text-surface-600 dark:text-surface-300">Pass / Watchlist / Fail ko ek jagah dekhein. Override karte waqt chhota sa reason zaroor likhein.</p>
  </header>

  <div class="flex flex-wrap items-center justify-between gap-3">
    <div class="flex gap-2 rounded-full bg-surface-200 p-1 dark:bg-surface-800/70">
      <?php foreach (['pass' => 'Pass', 'watch' => 'Watchlist', 'fail' => 'Fail'] as $key => $label): ?>
        <a href="/dashboard/superadmin/buckets?bucket=<?php echo $key; ?>" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold <?php echo $bucket === $key ? 'bg-white text-indigo-600 shadow-sm dark:bg-surface-900 dark:text-indigo-300' : 'text-surface-600 hover:text-indigo-600 dark:text-surface-300 dark:hover:text-indigo-300'; ?>">
          <?php echo htmlspecialchars($label); ?>
          <span class="text-xs font-semibold text-surface-500 dark:text-surface-400"><?php echo $counts[$key] ?? 0; ?></span>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="flex gap-2">
      <?php echo ui_button('CSV export', 'ghost', ['href' => '/sa/buckets/export?bucket='.$bucket]); ?>
      <?php echo ui_button('Engine kholain', 'soft', ['href' => '/dashboard/superadmin/engine']); ?>
    </div>
  </div>

  <div class="overflow-hidden rounded-3xl border border-surface-200 bg-white shadow-sm dark:border-surface-800 dark:bg-surface-900">
    <table class="min-w-full text-sm text-surface-600 dark:text-surface-200">
      <thead class="bg-surface-100 text-xs uppercase tracking-wide text-surface-500 dark:bg-surface-800 dark:text-surface-300">
        <tr>
          <th class="px-4 py-3 text-left">Ticker</th>
          <th class="px-4 py-3 text-left">Ratios</th>
          <th class="px-4 py-3 text-left">Reason</th>
          <th class="px-4 py-3 text-left">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-surface-200 dark:divide-surface-800">
        <?php if (!$companies): ?>
          <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-surface-500">Yahan abhi khaali hai — baad me wapas aayein.</td></tr>
        <?php endif; ?>
        <?php foreach ($companies as $company): ?>
          <?php $ratios = $company['ratios']; $companyId = (int)$company['company_id']; $tickerJs = json_encode($company['ticker']); $bucketJs = json_encode($company['current_bucket']); ?>
          <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/70">
            <td class="px-4 py-4 align-top">
              <p class="font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($company['ticker']); ?></p>
              <p class="text-xs text-surface-500"><?php echo htmlspecialchars($company['name']); ?></p>
              <p class="mt-1 text-xs text-surface-400">Latest: <?php echo htmlspecialchars($company['period'] ?? '—'); ?></p>
              <p class="mt-1 text-xs text-surface-400">Natural verdict: <?php echo strtoupper($company['derived_bucket']); ?></p>
            </td>
            <td class="px-4 py-4 align-top">
              <div class="grid gap-1 text-xs">
                <span>Debt/Assets: <strong><?php echo percent($ratios['debt_pct'] ?? 0); ?></strong></span>
                <span>Interest/Revenue: <strong><?php echo percent($ratios['interest_pct'] ?? 0); ?></strong></span>
                <span>Liquid/Assets: <strong><?php echo percent($ratios['liquid_pct'] ?? 0); ?></strong></span>
                <span>Non-Sh/Revenue: <strong><?php echo percent($ratios['nonsh_pct'] ?? 0); ?></strong></span>
              </div>
            </td>
            <td class="px-4 py-4 align-top text-xs text-surface-500 dark:text-surface-300">
              <p><?php echo htmlspecialchars($company['override_reason']); ?></p>
              <?php if (!empty($company['updated_by_name'])): ?>
                <p class="mt-1 text-[11px] text-surface-400">Override by <?php echo htmlspecialchars($company['updated_by_name']); ?> (<?php echo htmlspecialchars($company['updated_at']); ?>)</p>
              <?php endif; ?>
            </td>
            <td class="px-4 py-4 align-top text-xs">
              <div class="flex flex-col gap-2">
                <?php echo ui_button('Move bucket', 'soft', [
                    'type' => 'button',
                    'x-on:click' => "openMove($companyId, $tickerJs, $bucketJs)",
                ]); ?>
                <?php echo ui_button('Recompute', 'link', ['href' => '/dashboard/superadmin/engine?prefill='.$company['ticker']]); ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php
$modalBody = '<form method="post" action="" class="space-y-4" x-ref="moveForm">
    <input type="hidden" name="csrf" value="'.htmlspecialchars($csrf).'">
    <input type="hidden" name="redirect" value="/dashboard/superadmin/buckets?bucket='.$bucket.'">
    <input type="hidden" name="bucket" x-model="form.bucket">
    <p class="text-sm text-surface-600 dark:text-surface-300">Ticker <strong x-text="form.ticker"></strong> ko kis bucket me lana hai?</p>
    <select class="w-full rounded-full border border-surface-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-800 dark:text-surface-100" x-model="form.bucket">
        <option value="pass">Pass</option>
        <option value="watch">Watchlist</option>
        <option value="fail">Fail</option>
    </select>
    '.ui_textarea('reason', 'Reason', '', 'Chhota sa sabab likhe bina override mat karein.', [], ['x-model' => 'form.reason']).' 
    <div class="flex justify-end gap-2">
        '.ui_button('Band karo', 'ghost', ['type' => 'button', 'x-on:click' => 'open=false']).'
        '.ui_button('Submit karo', 'primary').'
    </div>
</form>';

echo ui_modal('move-bucket', 'Bucket change', $modalBody, '');
?>

<script>
function bucketManager(csrf){
  return {
    form: { companyId: null, ticker: '', bucket: 'pass', reason: '' },
    openMove(id, ticker, current){
      this.form.companyId = id;
      this.form.ticker = ticker;
      this.form.bucket = current;
      this.form.reason = '';
      const modal = document.querySelector('[x-ref="moveForm"]');
      if (modal) {
        modal.setAttribute('action', `/sa/buckets/${id}/move`);
      }
      window.dispatchEvent(new CustomEvent('open-modal', { detail: 'move-bucket' }));
    }
  };
}
</script>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
