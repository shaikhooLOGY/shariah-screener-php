<?php
$groups = [
    'Explore' => [
        '/' => 'Home',
        '/explore' => 'Explore',
        '/companies' => 'Companies',
        '/methodology' => 'Methodology',
        '/standards' => 'Standards',
        '/case-studies' => 'Case Studies',
        '/faq' => 'FAQ',
        '/glossary' => 'Glossary',
        '/about' => 'About',
        '/contact' => 'Contact',
    ],
    'Learn' => [
        '/learn' => 'Learn',
        '/articles' => 'Articles',
        '/terms' => 'Terms',
        '/privacy' => 'Privacy',
        '/disclaimer' => 'Disclaimer',
        '/purification' => 'Purification',
    ],
    'Scholars' => [
        '/scholars' => 'Scholar Board',
        '/scholar/example-scholar' => 'Sample Scholar',
    ],
    'Community' => [
        '/discussions' => 'Discussions',
        '/suggest-ratios' => 'Suggest Ratios',
        '/company/TCS' => 'Company Profile',
    ],
    'Dashboard' => [
        '/dashboard/ulama' => 'Ulama',
        '/dashboard/admin' => 'Admin',
        '/login' => 'Login',
        '/register' => 'Register',
    ],
];
$current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
?>
<div class="nav-container">
    <a class="brand" href="/">Shaikhoology</a>
    <nav class="nav-links">
        <?php foreach ($groups as $label => $links): ?>
            <div class="nav-group">
                <span><?php echo htmlspecialchars($label); ?></span>
                <div class="nav-group-links">
                    <?php foreach ($links as $href => $text): ?>
                        <?php $active = $current === $href ? 'style="font-weight:600;"' : ''; ?>
                        <a href="<?php echo htmlspecialchars($href); ?>" <?php echo $active; ?>>
                            <?php echo htmlspecialchars($text); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </nav>
</div>
