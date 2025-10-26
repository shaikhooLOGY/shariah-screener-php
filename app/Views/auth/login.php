<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Login';
$csrf = $_SESSION['csrf'] ?? '';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Login', 'href' => '/login'],
];
ob_start();
?>
<section class="grid min-h-[560px] overflow-hidden rounded-3xl border border-surface-200 bg-white shadow-sm dark:border-surface-800 dark:bg-surface-900 lg:grid-cols-[1.1fr_0.9fr]">
  <div class="flex items-center border-r border-surface-200 bg-gradient-to-br from-indigo-500/10 via-white to-white px-8 py-12 dark:border-surface-800 dark:from-indigo-500/20 dark:via-surface-900 dark:to-surface-900">
    <div class="space-y-4">
      <span class="badge-soft">Welcome back</span>
      <h1 class="text-3xl font-semibold text-surface-900 dark:text-white">Sign in to continue screening</h1>
      <p class="text-sm text-surface-600 dark:text-surface-300">Stay aligned with your Shari'ah board. Access filings, verdicts, and discussions from one console.</p>
      <ul class="space-y-2 text-sm text-surface-600 dark:text-surface-300">
        <li>• Email + password or SSO</li>
        <li>• Fine-grained role permissions</li>
        <li>• 2FA supported</li>
      </ul>
    </div>
  </div>
  <form method="post" action="/login" class="flex flex-col gap-6 px-8 py-12">
    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
    <?php echo ui_input('email', 'Email address', 'email'); ?>
    <?php echo ui_input('password', 'Password', 'password'); ?>
    <div class="flex items-center justify-between text-xs">
      <?php echo ui_checkbox('remember', 'Remember me'); ?>
      <a class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-300" href="/forgot">Forgot password?</a>
    </div>
    <div class="flex flex-col gap-3">
      <?php echo ui_button('Sign in'); ?>
      <?php echo ui_button('Continue with SSO', 'soft', ['href' => '#']); ?>
    </div>
    <p class="text-xs text-surface-500 dark:text-surface-400">New here? <a href="/register" class="text-indigo-600 dark:text-indigo-300">Create an account</a>.</p>
  </form>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
