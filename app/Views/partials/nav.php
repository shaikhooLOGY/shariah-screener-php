<?php
require_once __DIR__.'/ui.php';

$role = $_SESSION['role'] ?? 'guest';
$user = $_SESSION['user'] ?? null;
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isActive = function (string $href) use ($currentPath): bool {
    if ($href === '/') {
        return $currentPath === '/';
    }
    return str_starts_with($currentPath, $href);
};

$sections = [
    'Public' => [
        ['href' => '/', 'label' => 'Home'],
        ['href' => '/explore', 'label' => 'Explore'],
        ['href' => '/companies', 'label' => 'Companies'],
        ['href' => '/methodology', 'label' => 'Methodology'],
        ['href' => '/standards', 'label' => 'Standards'],
        ['href' => '/case-studies', 'label' => 'Case Studies'],
        ['href' => '/faq', 'label' => 'FAQ'],
        ['href' => '/glossary', 'label' => 'Glossary'],
        ['href' => '/about', 'label' => 'About'],
        ['href' => '/contact', 'label' => 'Contact'],
    ],
    'Community' => [
        ['href' => '/discussions', 'label' => 'Discussions'],
        ['href' => '/suggest-ratios', 'label' => 'Suggest Ratios'],
    ],
];

$authLinks = [
    ['href' => '/login', 'label' => 'Login'],
    ['href' => '/register', 'label' => 'Register'],
];

$ulamaSection = [
    'Ulama' => [
        ['href' => '/dashboard/ulama', 'label' => 'Dashboard'],
        ['href' => '/dashboard/ulama/reviews', 'label' => 'Reviews'],
    ],
];

$adminSection = [
    'Admin' => [
        ['href' => '/dashboard/admin', 'label' => 'Dashboard'],
        ['href' => '/dashboard/admin/companies', 'label' => 'Companies'],
        ['href' => '/dashboard/admin/filings', 'label' => 'Filings'],
        ['href' => '/dashboard/admin/users', 'label' => 'Users'],
        ['href' => '/dashboard/admin/settings', 'label' => 'Settings'],
    ],
];

$superSection = [
    'Superadmin' => [
        ['href' => '/dashboard/superadmin/users', 'label' => 'Users'],
        ['href' => '/dashboard/superadmin/buckets', 'label' => 'Buckets'],
        ['href' => '/dashboard/superadmin/engine', 'label' => 'Engine'],
        ['href' => '/dashboard/superadmin/audit', 'label' => 'Audit Log'],
        ['href' => '/dashboard/superadmin/system', 'label' => 'System'],
        ['href' => '/dashboard/superadmin/approvals', 'label' => 'Approvals', 'ability' => 'role.approve_request'],
    ],
];

if ($role === 'guest') {
    $sections['Account'] = $authLinks;
}

if (in_array($role, ['ulama', 'admin', 'superadmin'], true)) {
    $sections = array_merge($sections, $ulamaSection);
}

if (in_array($role, ['admin', 'superadmin'], true)) {
    $sections = array_merge($sections, $adminSection);
}

if ($role === 'superadmin') {
    $sections = array_merge($sections, $superSection);
}
?>
<?php foreach ($sections as $section => $links): ?>
    <div class="app-sidebar-section">
        <p class="app-sidebar-section__title"><?php echo htmlspecialchars($section); ?></p>
        <ul class="app-sidebar-section__menu">
            <?php foreach ($links as $link): ?>
                <?php
                $active = $isActive($link['href']);
                $ability = $link['ability'] ?? null;
                $show = true;
                if ($ability && $user) {
                    $show = function_exists('user_can') ? \user_can($user, $ability) : false;
                }
                if (!$show) continue;
                ?>
                <li>
                    <a href="<?php echo htmlspecialchars($link['href']); ?>" class="app-sidebar-link<?php echo $active ? ' app-sidebar-link--active' : ''; ?>">
                        <span><?php echo htmlspecialchars($link['label']); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endforeach; ?>
