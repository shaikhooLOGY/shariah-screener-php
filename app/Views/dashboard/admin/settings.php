<?php $title = 'Admin Settings'; ob_start(); ?>
<h1>Platform Settings</h1>
<p>Configure screening caps, notification channels, and evidence retention policies.</p>
<?php $content = ob_get_clean(); include __DIR__ . '/../../layout.php'; ?>
