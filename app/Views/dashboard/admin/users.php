<?php $title = 'Admin Users'; ob_start(); ?>
<h1>User Management</h1>
<p>Manage analyst accounts, scholar access, and community moderators.</p>
<?php $content = ob_get_clean(); include __DIR__ . '/../../layout.php'; ?>
