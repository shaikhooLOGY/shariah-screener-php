<?php $title = 'Login'; ob_start(); ?>
<h1>Login</h1>
<p>Authenticate to access dashboards, scholar reviews, and screening evidence.</p>
<?php $content = ob_get_clean(); include __DIR__ . '/../layout.php'; ?>
