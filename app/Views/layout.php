<?php
require_once __DIR__.'/partials/ui.php';

// Load UI configuration
$uiConfigPath = app_root() . '/config/ui.php';
$uiConfig = is_file($uiConfigPath) ? require $uiConfigPath : ['skin' => 'classic'];
$skin = $_GET['skin'] ?? $uiConfig['skin'];
$skin = in_array($skin, ['classic', 'aurora', 'noor']) ? $skin : 'classic';

$siteTitle = 'Shaikhoology Screener';
$pageTitle = isset($title) && $title !== '' ? $title . ' · ' . $siteTitle : $siteTitle;
$env = $_ENV['APP_ENV'] ?? 'development';
$role = $_SESSION['role'] ?? 'guest';
$user = $_SESSION['user'] ?? null;
$userName = $user['name'] ?? 'Guest';
$csrf = $_SESSION['csrf'] ?? '';
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

// Get pending counts for badges
$pendingApprovalsCount = 0;
$pendingTasksCount = 0;
$pendingSuggestionsCount = 0;

if ($role !== 'guest' && function_exists('db_pdo')) {
    try {
        $pdo = db_pdo();

        // Pending approvals for superadmin
        if ($role === 'superadmin') {
            $stmt = $pdo->query("SELECT COUNT(*) FROM approvals WHERE status = 'pending'");
            $pendingApprovalsCount = (int)$stmt->fetchColumn();
        }

        // Pending tasks for mufti/admin
        if (in_array($role, ['mufti', 'admin'])) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'open' AND assignee_id = " . ($user['id'] ?? 0));
            $pendingTasksCount = (int)$stmt->fetchColumn();
        }

        // Pending suggestions for mufti/admin
        if (in_array($role, ['mufti', 'admin'])) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM ratio_suggestions WHERE status = 'pending'");
            $pendingSuggestionsCount = (int)$stmt->fetchColumn();
        }
    } catch (\Throwable $e) {
        // Ignore errors
    }
}
?>
<!doctype html>
<html lang="en" class="h-full" x-data="themeDetector()" :class="theme === 'dark' ? 'theme-dark' : ''">
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
    <link rel="stylesheet" href="/assets/css/app.min.css?v=<?php echo htmlspecialchars(env('BUILD_TS', time())); ?>">
    <script defer src="https://unpkg.com/alpinejs@3.13.10/dist/cdn.min.js"></script>
    <script defer src="/assets/js/app.min.js?v=<?php echo htmlspecialchars(env('BUILD_TS', time())); ?>"></script>
    <script>
        window.APP = Object.assign({}, window.APP || {}, {
            csrf: <?php echo json_encode($csrf); ?>,
            role: <?php echo json_encode($role); ?>,
            env: <?php echo json_encode($env); ?>,
            skin: <?php echo json_encode($skin); ?>,
            user: <?php echo json_encode($user); ?>,
            pendingCounts: {
                approvals: <?php echo $pendingApprovalsCount; ?>,
                tasks: <?php echo $pendingTasksCount; ?>,
                suggestions: <?php echo $pendingSuggestionsCount; ?>
            }
        });

        // Apply skin CSS variables
        document.documentElement.style.cssText = '';
    </script>
</head>
<body class="min-h-full font-sans antialiased" x-data="appLayout()" x-on:keydown.window.prevent.cmd-k="toggleCommandPalette(true)" x-on:keydown.window.prevent.ctrl-k="toggleCommandPalette(true)" data-skin="<?php echo htmlspecialchars($skin); ?>">
<a href="#main" class="skip-to-content focus-visible:ring">Skip to content</a>

<!-- Top Header Navigation -->
<header class="app-header-top bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo and Brand -->
            <div class="flex items-center gap-4">
                <a href="/" class="flex items-center gap-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500 text-sm font-bold text-white">SS</span>
                    <div class="hidden sm:block">
                        <p class="text-sm font-semibold text-gray-900">Shaikhoology</p>
                        <p class="text-xs text-gray-500">Screening Console</p>
                    </div>
                </a>

                <!-- Role Badge -->
                <?php if ($role !== 'guest'): ?>
                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">
                        <?php echo htmlspecialchars(ucfirst($role)); ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Search Bar (centered on home) -->
            <?php if ($currentPath === '/'): ?>
                <div class="flex-1 max-w-md mx-8">
                    <form action="/search" method="get" class="relative">
                        <input type="search" name="q" placeholder="Search companies, filings, scholars…" class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">⌘K</span>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Right side actions -->
            <div class="flex items-center gap-3">
                <!-- Theme toggle -->
                <button class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" x-on:click="toggleTheme()" aria-label="Toggle dark mode">
                    <span x-show="theme === 'dark'">Light</span>
                    <span x-show="theme !== 'dark'">Dark</span>
                </button>

                <!-- Skin selector -->
                <select x-model="currentSkin" x-on:change="changeSkin($event.target.value)" class="rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <option value="classic">Classic</option>
                    <option value="aurora">Aurora</option>
                    <option value="noor">Noor</option>
                </select>

                <?php if ($role === 'guest'): ?>
                    <div class="flex items-center gap-2">
                        <a href="/login" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Sign in</a>
                        <a href="/register" class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Register</a>
                    </div>
                <?php else: ?>
                    <!-- User menu -->
                    <div class="relative" x-data="{open:false}" x-on:click.outside="open=false">
                        <button class="flex items-center gap-2 rounded-lg bg-gray-100 px-3 py-2 text-sm font-semibold hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" x-on:click="open=!open">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-500 text-white text-xs font-semibold">
                                <?php echo strtoupper(substr($userName, 0, 1)); ?>
                            </span>
                            <span class="hidden sm:block text-gray-700"><?php echo htmlspecialchars($userName); ?></span>
                        </button>
                        <div x-show="open" x-transition class="absolute right-0 mt-2 w-56 rounded-xl border border-gray-200 bg-white p-2 text-sm shadow-lg">
                            <div class="rounded-lg px-3 py-2 text-xs uppercase tracking-wide text-gray-400"><?php echo htmlspecialchars(ucfirst($role)); ?></div>
                            <a href="/profile" class="block rounded-lg px-3 py-2 text-gray-600 hover:bg-gray-100">Profile</a>
                            <a href="/settings" class="block rounded-lg px-3 py-2 text-gray-600 hover:bg-gray-100">Settings</a>
                            <hr class="my-2 border-gray-200">
                            <form action="/logout" method="post">
                                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                                <button class="w-full rounded-lg px-3 py-2 text-left text-red-600 hover:bg-red-50">Sign out</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Mobile menu button -->
                <button class="md:hidden rounded-lg p-2 text-gray-500 hover:bg-gray-100" x-on:click="$dispatch('toggle-mobile-menu')">
                    <span class="sr-only">Open menu</span>
                    ☰
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Main Navigation -->
<nav class="app-nav bg-white border-b border-gray-200 sticky top-16 z-40" x-data="mainNav()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-12">
            <!-- Primary Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <?php
                $navItems = [
                    ['href' => '/', 'label' => 'Home', 'icon' => 'home'],
                    ['href' => '/companies', 'label' => 'Companies', 'icon' => 'building'],
                ];

                foreach ($navItems as $item):
                    $isActive = $currentPath === $item['href'] || ($item['href'] !== '/' && str_starts_with($currentPath, $item['href']));
                ?>
                    <a href="<?php echo htmlspecialchars($item['href']); ?>" class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg transition-colors <?php echo $isActive ? 'text-indigo-600 bg-indigo-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'; ?>">
                        <i data-lucide="<?php echo $item['icon']; ?>" class="h-4 w-4"></i>
                        <?php echo htmlspecialchars($item['label']); ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Mobile Navigation Sheet -->
            <div class="md:hidden fixed inset-0 z-50 hidden" x-show="mobileMenuOpen" x-on:click.self="mobileMenuOpen = false">
                <div class="fixed inset-y-0 left-0 w-full bg-white shadow-xl">
                    <div class="flex items-center justify-between p-4 border-b border-gray-200">
                        <span class="text-lg font-semibold">Menu</span>
                        <button x-on:click="mobileMenuOpen = false" class="p-2 text-gray-400 hover:text-gray-600">×</button>
                    </div>
                    <div class="p-4 space-y-2">
                        <?php foreach ($navItems as $item): ?>
                            <a href="<?php echo htmlspecialchars($item['href']); ?>" class="flex items-center gap-3 px-4 py-3 text-base font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                                <i data-lucide="<?php echo $item['icon']; ?>" class="h-5 w-5"></i>
                                <?php echo htmlspecialchars($item['label']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Secondary Navigation (role-based) -->
            <div class="hidden md:flex items-center space-x-4">
                <?php if ($role !== 'guest'): ?>
                    <!-- Dashboard links based on role -->
                    <?php if ($role === 'superadmin'): ?>
                        <a href="/dashboard/superadmin/cmv" class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                            <i data-lucide="settings" class="h-4 w-4"></i>
                            CMV
                            <?php if ($pendingApprovalsCount > 0): ?>
                                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full"><?php echo $pendingApprovalsCount; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="/dashboard/superadmin/controversies" class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                            <i data-lucide="alert-triangle" class="h-4 w-4"></i>
                            Controversies
                        </a>
                        <a href="/dashboard/superadmin/sectors" class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                            <i data-lucide="grid-3x3" class="h-4 w-4"></i>
                            Sectors
                        </a>
                    <?php elseif ($role === 'admin'): ?>
                        <a href="/dashboard/admin" class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                            <i data-lucide="bar-chart" class="h-4 w-4"></i>
                            Dashboard
                        </a>
                        <a href="/dashboard/admin/tasks" class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                            <i data-lucide="check-square" class="h-4 w-4"></i>
                            Tasks
                            <?php if ($pendingTasksCount > 0): ?>
                                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-blue-500 rounded-full"><?php echo $pendingTasksCount; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="/dashboard/admin/suggestions" class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                            <i data-lucide="edit-3" class="h-4 w-4"></i>
                            Suggestions
                            <?php if ($pendingSuggestionsCount > 0): ?>
                                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-green-500 rounded-full"><?php echo $pendingSuggestionsCount; ?></span>
                            <?php endif; ?>
                        </a>
                    <?php elseif ($role === 'mufti'): ?>
                        <a href="/dashboard/ulama" class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                            <i data-lucide="users" class="h-4 w-4"></i>
                            Dashboard
                        </a>
                        <a href="/dashboard/ulama/tasks" class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                            <i data-lucide="check-square" class="h-4 w-4"></i>
                            Tasks
                            <?php if ($pendingTasksCount > 0): ?>
                                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-blue-500 rounded-full"><?php echo $pendingTasksCount; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="/dashboard/ulama/suggestions" class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                            <i data-lucide="edit-3" class="h-4 w-4"></i>
                            Suggestions
                            <?php if ($pendingSuggestionsCount > 0): ?>
                                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-green-500 rounded-full"><?php echo $pendingSuggestionsCount; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="/dashboard/ulama/controversies" class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                            <i data-lucide="alert-triangle" class="h-4 w-4"></i>
                            Controversies
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<main id="main" class="app-content flex-1">
    <?php if (!empty($breadcrumbs ?? [])): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            <?php echo ui_breadcrumbs($breadcrumbs); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flash ?? [])): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            <div class="space-y-3">
                <?php foreach ($flash as $tone => $message): ?>
                    <?php echo ui_alert($tone, $message); ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <?php echo $content ?? ''; ?>
    </div>
</main>

<!-- Footer -->
<footer class="app-footer bg-gray-50 border-t border-gray-200 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-wrap items-center justify-between gap-3 text-xs text-gray-500">
            <span>&copy; <?php echo date('Y'); ?> Shaikhoology Screener • Build <?php echo htmlspecialchars($_ENV['APP_VERSION'] ?? 'dev-main'); ?></span>
            <nav class="flex items-center gap-3">
                <a href="/terms" class="hover:text-indigo-600">Terms</a>
                <a href="/privacy" class="hover:text-indigo-600">Privacy</a>
                <a href="/disclaimer" class="hover:text-indigo-600">Disclaimer</a>
            </nav>
        </div>
    </div>
</footer>

<!-- Toast Container -->
<div id="app-toasts" class="pointer-events-none fixed inset-x-0 top-20 z-50 mx-auto flex w-full max-w-sm flex-col gap-3 px-4 sm:right-4 sm:top-4 sm:left-auto sm:max-w-xs sm:px-0"></div>

<!-- Command Palette Modal -->
<?php
echo ui_modal(
    'command-palette',
    'Command Palette',
    '<p class="text-sm text-gray-500">Start typing to jump to a page.</p>
    <div class="relative mt-4">
        <input type="search" placeholder="Search…" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">↵</span>
    </div>',
    '<button class="rounded-lg border border-gray-300 px-3 py-1 text-xs font-semibold hover:bg-gray-50" x-on:click="open=false">Close</button>'
);
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Mobile menu handler
    document.addEventListener('toggle-mobile-menu', function() {
        Alpine.store('mobileMenu', { open: !Alpine.store('mobileMenu')?.open });
    });
});

function ui_alert(tone, message) {
    const colors = {
        success: 'bg-green-50 border-green-200 text-green-800',
        danger: 'bg-red-50 border-red-200 text-red-800',
        warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
        info: 'bg-blue-50 border-blue-200 text-blue-800'
    };
    return `<div class="rounded-lg border p-4 ${colors[tone] || colors.info}">${message}</div>`;
}

function ui_breadcrumbs(crumbs) {
    let html = '<nav class="flex" aria-label="Breadcrumb"><ol class="flex items-center space-x-2">';
    crumbs.forEach((crumb, index) => {
        if (index > 0) html += '<li><span class="text-gray-400">/</span></li>';
        html += `<li><a href="${crumb.href}" class="text-gray-500 hover:text-gray-700">${crumb.label}</a></li>`;
    });
    html += '</ol></nav>';
    return html;
}

function ui_modal(id, title, content, footer) {
    return `
    <div x-show="$store.modals.${id}" x-on:click.self="$store.modals.${id} = false" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
            <div class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900">${title}</h3>
                    <div class="mt-4">${content}</div>
                </div>
                <div class="mt-5 sm:mt-6">${footer}</div>
            </div>
        </div>
    </div>`;
}
</script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('themeDetector', () => ({
        theme: localStorage.getItem('theme') || 'light',
        init() {
            this.applyTheme();
        },
        toggleTheme() {
            this.theme = this.theme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme', this.theme);
            this.applyTheme();
        },
        applyTheme() {
            document.documentElement.classList.toggle('dark', this.theme === 'dark');
            document.documentElement.classList.toggle('theme-dark', this.theme === 'dark');
        }
    }));

    Alpine.data('appLayout', () => ({
        currentSkin: '<?php echo $skin; ?>',
        changeSkin(skin) {
            localStorage.setItem('skin', skin);
            window.location.reload();
        }
    }));

    Alpine.data('mainNav', () => ({
        mobileMenuOpen: false,
        init() {
            document.addEventListener('toggle-mobile-menu', () => {
                this.mobileMenuOpen = !this.mobileMenuOpen;
            });
        }
    }));

    Alpine.store('modals', {});
    Alpine.store('mobileMenu', { open: false });
});
</script>
</body>
</html>
