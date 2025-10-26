<?php
if (($_ENV['APP_ENV'] ?? 'production') === 'production') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$checks = [
    'CWD' => getcwd(),
    'Root' => $root,
    'PHP' => PHP_VERSION,
    'APP_ENV' => $_ENV['APP_ENV'] ?? 'n/a',
    'APP_URL' => $_ENV['APP_URL'] ?? 'n/a',
    'public/index.php' => file_exists($root.'/public/index.php') ? 'yes' : 'no',
    'core/Router.php' => file_exists($root.'/core/Router.php') ? 'yes' : 'no',
    '\\Core\\dispatch' => function_exists('\\Core\\dispatch') ? 'yes' : 'no',
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Shaikhoology diagnostics</title>
  <link rel="stylesheet" href="/assets/css/app.css">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-surface-50 py-16 text-surface-700">
  <main class="mx-auto w-full max-w-2xl rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
    <h1 class="text-2xl font-semibold text-surface-900 dark:text-white">Diagnostics</h1>
    <p class="mt-2 text-sm text-surface-600 dark:text-surface-300">Use this page during debugging or deployment checks. Remove or protect in production.</p>
    <dl class="mt-6 space-y-3 text-sm">
      <?php foreach ($checks as $key => $value): ?>
        <div class="rounded-2xl border border-surface-200 bg-white p-3 shadow-sm dark:border-surface-800 dark:bg-surface-900/80">
          <dt class="text-xs uppercase tracking-wide text-surface-500 dark:text-surface-400"><?php echo htmlspecialchars($key); ?></dt>
          <dd class="mt-1 font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($value); ?></dd>
        </div>
      <?php endforeach; ?>
    </dl>
    <div class="mt-6 flex gap-3 text-sm">
      <a href="/health" class="rounded-full border border-surface-200 px-4 py-2 hover:bg-surface-100 dark:border-surface-700 dark:hover:bg-surface-800">Open /health</a>
      <a href="/prod_health_direct.php" class="rounded-full border border-surface-200 px-4 py-2 hover:bg-surface-100 dark:border-surface-700 dark:hover:bg-surface-800">Direct health check</a>
    </div>
  </main>
</body>
</html>
