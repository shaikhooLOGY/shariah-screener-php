<?php $title = 'Discussions'; ob_start(); ?>
<h1>Discussions Hub</h1>
<p>Coordinate public notes, scholar feedback, and investor Q&amp;A around each screened company.</p>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
