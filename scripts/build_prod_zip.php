<?php
declare(strict_types=1);

$root = dirname(__DIR__);
chdir($root);

$cmd = 'composer install --no-dev --optimize-autoloader';
passthru($cmd, $status);
if ($status !== 0) {
    fwrite(STDERR, "Composer install failed with status {$status}\n");
    exit($status);
}

if (!is_dir($root . '/vendor')) {
    fwrite(STDERR, "vendor/ directory missing after composer install\n");
    exit(1);
}

$distDir = $root . '/dist';
if (!is_dir($distDir)) {
    mkdir($distDir, 0777, true);
}

$zipName = 'shaikhoology_build_' . date('Ymd_His') . '.zip';
$zipPath = $distDir . '/' . $zipName;

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Unable to create archive at {$zipPath}\n");
    exit(1);
}

$include = [
    'app',
    'core',
    'public',
    'routes',
    'config',
    'database',
    'scripts',
    'vendor',
    'storage',
    '.htaccess',
    '.env.production.example',
    'RUNBOOK_HOSTINGER.md',
    'prod_health_direct.php',
];

$excludeFragments = ['/.git/', '/tests/', '/node_modules/', '/dist/', '.DS_Store', '/storage/shaikhoology.sqlite'];
$rootLength = strlen($root) + 1;

$addPath = function (string $path) use ($zip, $root, $excludeFragments, $rootLength): void {
    $full = $root . '/' . $path;
    if (is_dir($full)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($full, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $fileInfo) {
            $relPath = substr($fileInfo->getPathname(), $rootLength);
            foreach ($excludeFragments as $fragment) {
                if (str_contains($relPath, $fragment)) {
                    continue 2;
                }
            }
            if ($fileInfo->isDir()) {
                $zip->addEmptyDir($relPath);
            } else {
                $zip->addFile($fileInfo->getPathname(), $relPath);
            }
        }
    } elseif (is_file($full)) {
        $zip->addFile($full, $path);
    }
};

foreach ($include as $path) {
    $addPath($path);
}

$zip->close();

echo "Build archive created: {$zipPath}\n";
echo "Included top-level: " . implode(', ', $include) . "\n";
