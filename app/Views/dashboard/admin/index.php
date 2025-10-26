<?php $title = 'Admin Dashboard'; ob_start(); ?>
<h1>Admin Dashboard</h1>
<p>Coordinate company imports, scholar assignments, and community moderation from one console.</p>
<?php $content = ob_get_clean(); include __DIR__ . '/../../layout.php'; ?>
