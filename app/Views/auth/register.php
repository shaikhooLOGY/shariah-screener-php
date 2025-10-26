<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Register';
$csrf = $_SESSION['csrf'] ?? '';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Register', 'href' => '/register'],
];
ob_start();
?>
<section class="grid min-h-[560px] overflow-hidden rounded-3xl border border-surface-200 bg-white shadow-sm dark:border-surface-800 dark:bg-surface-900 lg:grid-cols-[1.1fr_0.9fr]">
  <div class="flex items-center border-r border-surface-200 bg-gradient-to-br from-emerald-500/10 via-white to-white px-8 py-12 dark:border-surface-800 dark:from-emerald-500/20 dark:via-surface-900 dark:to-surface-900">
    <div class="space-y-4">
      <span class="badge-soft">Create account</span>
      <h1 class="text-3xl font-semibold text-surface-900 dark:text-white">Onboard your Shari'ah team</h1>
      <p class="text-sm text-surface-600 dark:text-surface-300">Invite scholars, analysts, and investor relations to collaborate in a unified workspace.</p>
      <ul class="space-y-2 text-sm text-surface-600 dark:text-surface-300">
        <li>• Granular access control per role</li>
        <li>• SQLite for local testing, MySQL in production</li>
        <li>• Exportable audit trails</li>
      </ul>
    </div>
  </div>
  <form method="post" action="/register" class="flex flex-col gap-6 px-8 py-12">
    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
    <?php echo ui_input('name', 'Full name'); ?>
    <?php echo ui_input('email', 'Work email', 'email'); ?>
    <?php echo ui_select('role', 'Role', ['user' => 'Analyst', 'ulama' => 'Ulama', 'admin' => 'Admin', 'superadmin' => 'Superadmin']); ?>
    <?php echo ui_input('password', 'Password', 'password'); ?>
    <?php echo ui_input('password_confirmation', 'Confirm password', 'password'); ?>
    <div>
      <?php echo ui_checkbox('terms', 'I agree to the Terms'); ?>
    </div>
    <div class="flex flex-col gap-3">
      <?php echo ui_button('Create account'); ?>
      <?php echo ui_button('Use corporate SSO', 'soft', ['href' => '#']); ?>
    </div>
    <p class="text-xs text-surface-500 dark:text-surface-400">Already on Shaikhoology? <a href="/login" class="text-indigo-600 dark:text-indigo-300">Sign in</a>.</p>
  </form>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
