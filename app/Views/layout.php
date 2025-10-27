<?php
require_once __DIR__.'/partials/ui.php';

$siteTitle = 'Shaikhoology Screener';
$pageTitle = isset($title) && $title !== '' ? $title . ' · ' . $siteTitle : $siteTitle;
$env = $_ENV['APP_ENV'] ?? 'development';
$role = $_SESSION['role'] ?? 'guest';
$user = $_SESSION['user'] ?? null;
$userName = $user['name'] ?? 'Guest';
$csrf = $_SESSION['csrf'] ?? '';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// Get pending approvals count for superadmin badge
$pendingApprovalsCount = 0;
if ($role === 'superadmin' && function_exists('db_pdo')) {
    try {
        $pdo = db_pdo();
        $stmt = $pdo->query("SELECT COUNT(*) FROM approvals WHERE status = 'pending'");
        $pendingApprovalsCount = (int)$stmt->fetchColumn();
    } catch (\Throwable $e) {
        // Ignore errors
    }
}
?>
<!doctype html>
<html lang="en" class="h-full" x-data="themeDetector()" x-bind:class="theme">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <link rel="canonical" href="<?php echo htmlspecialchars($_ENV['APP_URL'] ?? ''); ?>">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
    <link rel="stylesheet" href="/assets/css/app.css">
    <script defer src="https://unpkg.com/alpinejs@3.13.10/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script defer src="https://unpkg.com/lucide@0.462.0/dist/umd/lucide.min.js"></script>
    <script defer src="/assets/js/app.js"></script>
    <script>
        window.APP = Object.assign({}, window.APP || {}, {
            csrf: <?php echo json_encode($csrf); ?>,
            role: <?php echo json_encode($role); ?>,
            env: <?php echo json_encode($env); ?>,
        });
    </script>
</head>
<body class="min-h-full bg-surface-50 font-sans text-surface-700 antialiased dark:bg-surface-950 dark:text-surface-200" x-data="appLayout()" x-on:keydown.window.prevent.cmd-k="toggleCommandPalette(true)" x-on:keydown.window.prevent.ctrl-k="toggleCommandPalette(true)">
<a href="#main" class="skip-to-content focus-visible:ring">Skip to content</a>

<div class="app-shell" x-bind:class="{'app-shell--collapsed': sidebarCollapsed}">
    <aside class="app-sidebar" x-data="drawer()" x-bind:class="{'translate-x-0': open, '-translate-x-full': !open}">
        <div class="flex items-center justify-between border-b border-surface-200 px-6 py-5 dark:border-surface-800">
            <a href="/" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-500 text-base font-semibold text-white">SS</span>
                <div>
                    <p class="text-sm font-semibold text-surface-900 dark:text-white">Shaikhoology</p>
                    <p class="text-xs text-surface-500">Screening Console</p>
                </div>
            </a>
            <button class="md:hidden rounded-full p-2 text-surface-500 hover:bg-surface-100 dark:text-surface-300 dark:hover:bg-surface-800" x-on:click="toggle()">
                <span class="sr-only">Close menu</span>×
            </button>
        </div>
        <nav class="app-sidebar-nav">
            <?php include __DIR__ . '/partials/nav.php'; ?>
        </nav>
        <div class="mt-auto border-t border-surface-200 px-6 py-5 text-xs text-surface-500 dark:border-surface-800">
            <?php if ($role === 'guest'): ?>
                <p>Aap guest mode me dekh rahe hain.</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="/login" class="inline-flex items-center rounded-full border border-surface-300 px-3 py-1 font-semibold hover:bg-surface-100 dark:border-surface-700 dark:hover:bg-surface-800">Login</a>
                    <a href="/register" class="inline-flex items-center rounded-full border border-surface-300 px-3 py-1 font-semibold hover:bg-surface-100 dark:border-surface-700 dark:hover:bg-surface-800">Register</a>
                </div>
            <?php else: ?>
                <p>Signed in as <strong><?php echo htmlspecialchars($userName); ?></strong> · <?php echo htmlspecialchars(ucfirst($role)); ?></p>
                <p class="mt-2 flex flex-wrap items-center gap-2">
                    <button class="rounded-full border border-surface-300 px-3 py-1 text-xs font-semibold hover:bg-surface-100 dark:border-surface-700 dark:hover:bg-surface-800" x-on:click="$dispatch('open-modal', 'command-palette')">Command Palette</button>
                    <button class="rounded-full border border-surface-300 px-3 py-1 text-xs font-semibold hover:bg-surface-100 dark:border-surface-700 dark:hover:bg-surface-800" x-on:click="toggleTheme()">Toggle Theme</button>
                </p>
            <?php endif; ?>
        </div>
    </aside>

    <div class="app-main">
        <header class="app-header">
            <div class="flex items-center gap-3">
                <button class="md:hidden rounded-full p-2 text-surface-500 hover:bg-surface-100 dark:text-surface-300 dark:hover:bg-surface-800" x-on:click="$dispatch('toggle-drawer')">
                    <span class="sr-only">Open sidebar</span>
                    ☰
                </button>
                <form action="/search" method="get" class="relative hidden w-full max-w-sm md:block">
                    <input type="search" name="q" placeholder="Search companies, filings, scholars…" class="w-full rounded-full border border-surface-200 bg-white pl-10 pr-4 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-900 dark:text-surface-100" />
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-surface-400">⌘K</span>
                </form>
            </div>
            <div class="flex items-center gap-3">
                <button class="rounded-full border border-surface-200 px-3 py-1 text-xs font-semibold hover:bg-surface-100 dark:border-surface-700 dark:hover:bg-surface-800 md:hidden" x-on:click="toggleCommandPalette(true)">Search</button>
                <?php if ($role === 'superadmin' && $pendingApprovalsCount > 0): ?>
                    <a href="/dashboard/superadmin/approvals" class="relative rounded-full border border-red-200 bg-red-50 px-3 py-1 text-xs font-semibold text-red-700 hover:bg-red-100 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30">
                        Approvals
                        <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white"><?php echo $pendingApprovalsCount; ?></span>
                    </a>
                <?php endif; ?>
                <button class="rounded-full border border-surface-200 px-3 py-1 text-xs font-semibold hover:bg-surface-100 dark:border-surface-700 dark:hover:bg-surface-800" x-on:click="toggleTheme()" aria-label="Toggle dark mode">
                    <span x-show="theme === 'dark'">Light</span>
                    <span x-show="theme !== 'dark'">Dark</span>
                </button>
                <?php if ($role === 'guest'): ?>
                    <div class="flex items-center gap-2">
                        <a href="/login" class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Sign in</a>
                        <a href="/register" class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold text-surface-600 hover:bg-surface-200 dark:text-surface-100 dark:hover:bg-surface-800">Register</a>
                    </div>
                <?php else: ?>
                    <div class="relative" x-data="{open:false}" x-on:click.outside="open=false">
                        <button class="flex items-center gap-2 rounded-full bg-surface-100 px-3 py-2 text-sm font-semibold hover:bg-surface-200 dark:bg-surface-800 dark:hover:bg-surface-700" x-on:click="open=!open">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-500 text-white font-semibold">
                                <?php echo strtoupper(substr($userName, 0, 1)); ?>
                            </span>
            <span class="hidden sm:block text-surface-700 dark:text-surface-100"><?php echo htmlspecialchars($userName); ?></span>
                        </button>
                        <div x-show="open" x-transition class="absolute right-0 mt-2 w-56 rounded-2xl border border-surface-200 bg-white p-2 text-sm shadow-lg dark:border-surface-700 dark:bg-surface-900">
                            <div class="rounded-xl px-3 py-2 text-xs uppercase tracking-wide text-surface-400 dark:text-surface-500"><?php echo htmlspecialchars(ucfirst($role)); ?></div>
                            <a href="/profile" class="block rounded-xl px-3 py-2 text-surface-600 hover:bg-surface-100 dark:text-surface-200 dark:hover:bg-surface-800">Profile</a>
                            <a href="/settings" class="block rounded-xl px-3 py-2 text-surface-600 hover:bg-surface-100 dark:text-surface-200 dark:hover:bg-surface-800">Settings</a>
                            <hr class="my-2 border-surface-200 dark:border-surface-700">
                            <form action="/logout" method="post">
                                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                                <button class="w-full rounded-xl px-3 py-2 text-left text-rose-600 hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-500/10">Sign out</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <main id="main" class="app-content">
            <?php if (!empty($breadcrumbs ?? [])): ?>
                <div class="mb-4 md:mb-6">
                    <?php echo ui_breadcrumbs($breadcrumbs); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($flash ?? [])): ?>
                <div class="space-y-3">
                    <?php foreach ($flash as $tone => $message): ?>
                        <?php echo ui_alert($tone, $message); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php echo $content ?? ''; ?>
        </main>

        <footer class="app-footer">
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs text-surface-500 dark:text-surface-400">
                <span>&copy; <?php echo date('Y'); ?> Shaikhoology Screener • Build <?php echo htmlspecialchars($_ENV['APP_VERSION'] ?? 'dev-main'); ?></span>
                <nav class="flex items-center gap-3">
                    <a href="/terms" class="hover:text-indigo-600 dark:hover:text-indigo-300">Terms</a>
                    <a href="/privacy" class="hover:text-indigo-600 dark:hover:text-indigo-300">Privacy</a>
                    <a href="/disclaimer" class="hover:text-indigo-600 dark:hover:text-indigo-300">Disclaimer</a>
                </nav>
            </div>
        </footer>
    </div>
</div>

<div id="app-toasts" class="pointer-events-none fixed inset-x-0 top-4 z-50 mx-auto flex w-full max-w-sm flex-col gap-3 px-4 sm:right-4 sm:top-4 sm:left-auto sm:max-w-xs sm:px-0"></div>

<?php
echo ui_modal(
    'command-palette',
    'Command Palette',
    '<p class="text-sm text-surface-500">Start typing to jump to a page.</p>
    <div class="relative mt-4">
        <input type="search" placeholder="Search…" class="w-full rounded-xl border border-surface-200 bg-white px-4 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-900 dark:text-surface-100">
        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-surface-400">↵</span>
    </div>',
    '<button class="rounded-full border border-surface-300 px-3 py-1 text-xs font-semibold hover:bg-surface-100 dark:border-surface-700 dark:hover:bg-surface-800" x-on:click="open=false">Close</button>'
);
?>
</body>
</html>
