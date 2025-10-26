<?php $title = 'Companies'; ob_start(); ?>
<h1>Companies Directory</h1>
<p>Find issuers by ticker, sector, or compliance classification. Screening data syncs from our SQLite or MySQL stores.</p>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
