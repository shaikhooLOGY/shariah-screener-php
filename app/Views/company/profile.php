<h1>Company Profile: <?php echo htmlspecialchars($symbol ?? ''); ?></h1>
<div style="margin-bottom:12px;">
  <a href="/company/<?php echo urlencode($symbol); ?>">Compliance</a> |
  <a href="/company/<?php echo urlencode($symbol); ?>/discussion">Discussion</a> |
  <a href="/company/<?php echo urlencode($symbol); ?>/suggest">Suggest Ratios</a>
</div>

<?php if (!empty($company) && !empty($filing) && !empty($ratios)): ?>

  <?php if (!empty($verdict)): ?>
    <h2 style="margin-top:12px;">Verdict:
      <span style="padding:4px 8px;border-radius:6px;background:#eee;"><?php echo htmlspecialchars($verdict); ?></span>
    </h2>
    <p style="margin:6px 0 14px 0;">
      Caps: Debt≤<?php echo $caps['debt']*100; ?>%, Interest≤<?php echo $caps['interest']*100; ?>%,
      Liquid≤<?php echo $caps['liquid']*100; ?>%, Non-Sh≤<?php echo $caps['nonsh']*100; ?>%
    </p>
    <?php if (!empty($why)): ?>
      <ul><?php foreach ($why as $w) echo '<li>'.htmlspecialchars($w).'</li>'; ?></ul>
    <?php endif; ?>
  <?php endif; ?>

  <h2>Latest Filing (<?php echo htmlspecialchars($filing['period']); ?>)</h2>
  <table border="1" cellpadding="6" cellspacing="0">
    <tr><th align="left">Metric</th><th align="right">Value</th></tr>
    <tr><td>Total Assets</td><td align="right"><?php echo number_format((float)$filing['total_assets'],2); ?></td></tr>
    <tr><td>Total Debt</td><td align="right"><?php echo number_format((float)$filing['total_debt'],2); ?></td></tr>
    <tr><td>Cash</td><td align="right"><?php echo number_format((float)$filing['cash'],2); ?></td></tr>
    <tr><td>Receivables</td><td align="right"><?php echo number_format((float)$filing['receivables'],2); ?></td></tr>
    <tr><td>Revenue</td><td align="right"><?php echo number_format((float)$filing['revenue'],2); ?></td></tr>
    <tr><td>Interest Income</td><td align="right"><?php echo number_format((float)$filing['interest_income'],2); ?></td></tr>
    <tr><td>Non-Shari'ah Income</td><td align="right"><?php echo number_format((float)$filing['non_shariah_income'],2); ?></td></tr>
  </table>

  <h2>Screening Ratios</h2>
  <table border="1" cellpadding="6" cellspacing="0">
    <tr><th align="left">Ratio</th><th align="right">Percent</th></tr>
    <tr><td>Debt / Assets</td><td align="right"><?php echo number_format($ratios['debt_pct']*100,2).' %'; ?></td></tr>
    <tr><td>Interest Income / Revenue</td><td align="right"><?php echo number_format($ratios['interest_pct']*100,2).' %'; ?></td></tr>
    <tr><td>(Cash + Receivables) / Assets</td><td align="right"><?php echo number_format($ratios['liquid_pct']*100,2).' %'; ?></td></tr>
    <tr><td>Non-Shari'ah Income / Revenue</td><td align="right"><?php echo number_format($ratios['nonsh_pct']*100,2).' %'; ?></td></tr>
  </table>

<?php elseif (!empty($company)): ?>
  <p>No filings found yet for this company.</p>
<?php else: ?>
  <p>Company not found. Try <code>/company/TCS</code> after seeding.</p>
<?php endif; ?>
