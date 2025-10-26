<?php $title = 'Articles'; ob_start(); ?>
<h1>Articles &amp; Insights</h1>
<p>Read commentary from scholars, analysts, and community contributors about modern screening challenges.</p>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
