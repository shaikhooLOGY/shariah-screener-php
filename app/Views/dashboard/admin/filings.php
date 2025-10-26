<?php $title = 'Admin Filings'; ob_start(); ?>
<h1>Filings Management</h1>
<p>Upload quarterly statements, map notes JSON, and trigger screening recalculations.</p>
<?php $content = ob_get_clean(); include __DIR__ . '/../../layout.php'; ?>
