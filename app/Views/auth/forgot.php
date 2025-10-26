<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Forgot password';
$csrf = $_SESSION['csrf'] ?? '';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Forgot password', 'href' => '/forgot'],
];
ob_start();
?>
<section class="grid min-h-[480px] overflow-hidden rounded-3xl border border-surface-200 bg-white shadow-sm dark:border-surface-800 dark:bg-surface-900 lg:grid-cols-[1.2fr_0.8fr]">
  <div class="flex flex-col justify-center gap-4 border-r border-surface-200 bg-gradient-to-br from-indigo-500/10 via-white to-white px-8 py-12 dark:border-surface-800 dark:from-indigo-500/20 dark:via-surface-900 dark:to-surface-900">
    <span class="badge-soft">Reset access</span>
    <h1 class="text-3xl font-semibold text-surface-900 dark:text-white">We'll send a magic link</h1>
    <p class="text-sm text-surface-600 dark:text-surface-300">Enter the email associated with your account. We will email a secure link to reset your password.</p>
  </div>
  <form method="post" action="/forgot" class="flex flex-col gap-6 px-8 py-12">
    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
    <?php echo ui_input('email', 'Email address', 'email'); ?>
    <div class="flex flex-col gap-3">
      <?php echo ui_button('Send reset email'); ?>
      <?php echo ui_button('Back to sign in', 'ghost', ['href' => '/login']); ?>
    </div>
  </form>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
