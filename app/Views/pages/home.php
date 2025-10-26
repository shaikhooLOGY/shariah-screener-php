<?php $title = 'Home'; ob_start(); ?>
<h1>Shari'ah Screening Companion</h1>
<p>Track compliance, ratios, and scholar commentary for listed companies with a friendly workflow tailored for regional ulama and analysts.</p>
<div class="card-grid">
  <div class="card">
    <strong>Explore Companies</strong>
    <p>Review screening summaries, ratio thresholds, and audit notes for each ticker.</p>
  </div>
  <div class="card">
    <strong>Methodology First</strong>
    <p>Our ratios follow AAOIFI-inspired caps with a transparent evidence trail.</p>
  </div>
  <div class="card">
    <strong>Scholars Collaboration</strong>
    <p>Invite scholars to review filings, attach rulings, and keep an audit log.</p>
  </div>
</div>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
