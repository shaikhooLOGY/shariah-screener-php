<?php $title = 'Scholar Profile'; ob_start(); ?>
<h1>Scholar Profile</h1>
<p>Profile slug: <strong><?php echo htmlspecialchars($slug ?? ''); ?></strong>. Populate with biography, qualifications, and recent rulings.</p>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
