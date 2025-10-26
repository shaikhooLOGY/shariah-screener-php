<?php $title = 'FAQ'; ob_start(); ?>
<h1>Frequently Asked Questions</h1>
<p>Answers to the most common questions about screening ratios, evidence workflows, and scholar reviews.</p>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
