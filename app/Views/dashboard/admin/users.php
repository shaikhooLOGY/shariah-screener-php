<?php
require_once __DIR__.'/../../partials/ui.php';
$title = 'Manage users';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Admin dashboard', 'href' => '/dashboard/admin'],
    ['label' => 'Users', 'href' => '/dashboard/admin/users'],
];
$columns = [
    ['label' => 'Name', 'key' => 'name', 'sortable' => true],
    ['label' => 'Role', 'key' => 'role', 'sortable' => true],
    ['label' => 'Status', 'key' => 'status'],
];
$rows = [
    ['name' => 'Aaliyah Khan', 'role' => ui_badge('Admin', 'info'), 'status' => ui_badge('Active', 'success')],
    ['name' => 'Sheikh Yusuf', 'role' => ui_badge('Ulama', 'warn'), 'status' => ui_badge('Pending', 'warn')],
    ['name' => 'Sara Malik', 'role' => ui_badge('Analyst', 'neutral'), 'status' => ui_badge('Active', 'success')],
];
ob_start();
?>
<section class="space-y-6">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <h1 class="text-2xl font-semibold text-surface-900 dark:text-white">Users</h1>
      <p class="text-sm text-surface-600 dark:text-surface-300">Invite analysts, scholars, and superadmins. Control access and permissions.</p>
    </div>
    <div class="flex gap-2">
      <?php echo ui_button('Invite user'); ?>
      <?php echo ui_button('Sync SSO', 'ghost'); ?>
    </div>
  </div>
  <?php echo ui_table($columns, $rows, ['id' => 'admin_user_table']); ?>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../../layout.php';
