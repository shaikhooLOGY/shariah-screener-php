<?php $title = 'Admin Companies'; ob_start(); ?>
<h1>Manage Companies</h1>
<p>Add new tickers, adjust sector mappings, and oversee compliance status flags.</p>
<?php $content = ob_get_clean(); include __DIR__ . '/../../layout.php'; ?>
