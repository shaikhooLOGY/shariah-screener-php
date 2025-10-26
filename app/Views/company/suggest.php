<h1>Suggest Ratios — <?php echo htmlspecialchars($symbol ?? ''); ?></h1>
<div style="margin-bottom:12px;">
  <a href="/company/<?php echo urlencode($symbol); ?>">Compliance</a> |
  <a href="/company/<?php echo urlencode($symbol); ?>/discussion">Discussion</a> |
  <a href="/company/<?php echo urlencode($symbol); ?>/suggest">Suggest Ratios</a>
</div>

<?php if (!empty($_GET['ok'])): ?>
  <div style="padding:8px;background:#e7ffe7;border:1px solid #b2e6b2;border-radius:6px;">Thanks — submitted for review.</div>
<?php endif; ?>

<form method="post" action="">
  <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf ?? ''); ?>">
  <label>Period <input name="period" value="2025-Q2"></label><br><br>

  <label>Total Assets <input name="total_assets" type="number" step="0.01" required></label><br>
  <label>Total Debt <input name="total_debt" type="number" step="0.01" required></label><br>
  <label>Cash <input name="cash" type="number" step="0.01" required></label><br>
  <label>Receivables <input name="receivables" type="number" step="0.01" required></label><br>
  <label>Revenue <input name="revenue" type="number" step="0.01" required></label><br>
  <label>Interest Income <input name="interest_income" type="number" step="0.01" required></label><br>
  <label>Non-Shari'ah Income <input name="non_shariah_income" type="number" step="0.01" required></label><br><br>

  <label>Evidence URL (optional) <input name="evidence_url" style="width:60%;"></label><br>
  <label>Note (optional)</label><br>
  <textarea name="note" rows="4" style="width:100%;"></textarea><br><br>

  <button type="submit">Submit for Review</button>
</form>
