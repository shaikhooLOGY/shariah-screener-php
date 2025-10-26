<?php
$siteTitle = 'Shaikhoology Screener';
$pageTitle = isset($title) && $title !== '' ? $title . ' · ' . $siteTitle : $siteTitle;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <header>
        <?php include __DIR__ . '/partials/nav.php'; ?>
    </header>
    <main>
        <?php echo $content ?? ''; ?>
    </main>
    <footer>
        &copy; <?php echo date('Y'); ?> Shaikhoology · Building transparent Shari'ah finance insights.
    </footer>
</body>
</html>
