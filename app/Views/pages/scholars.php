<?php $title = 'Scholar Board'; ob_start(); ?>
<h1>Scholar Board</h1>
<p>See the panel of ulama reviewing filings, issuing guidance, and collaborating with compliance teams.</p>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
