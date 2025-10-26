<?php $title = 'Article'; ob_start(); ?>
<h1>Article Placeholder</h1>
<p>Slug: <strong><?php echo htmlspecialchars($slug ?? ''); ?></strong>. Use this template to render a CMS-backed article later.</p>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
