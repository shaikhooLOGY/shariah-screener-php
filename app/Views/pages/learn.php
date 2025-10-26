<?php $title = 'Learn'; ob_start(); ?>
<h1>Learning Library</h1>
<p>Curated explainers, webinars, and worksheet templates to onboard teams into Shari'ah screening workflows.</p>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
