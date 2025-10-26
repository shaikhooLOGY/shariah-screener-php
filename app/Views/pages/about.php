<?php $title = 'About'; ob_start(); ?>
<h1>About Shaikhoology</h1>
<p>We help Shari'ah boards and analysts collaborate on transparent, auditable compliance reviews for listed companies.</p>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
