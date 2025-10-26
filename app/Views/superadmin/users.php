<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Superadmin · Users';
ob_start();
?>
<section class="space-y-6" x-data="{ bulkOpen: false }">
  <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-2xl font-semibold text-surface-900 dark:text-white">Manage users</h1>
      <p class="text-sm text-surface-600 dark:text-surface-300">Promote, demote, or deactivate accounts. Password default <code>secret</code> for demo users.</p>
    </div>
    <div class="flex gap-2">
      <?php echo ui_button('Bulk promote/demote', 'soft', ['x-on:click' => "$dispatch('open-modal','bulk-role')"]); ?>
    </div>
  </header>

  <form method="get" class="flex flex-wrap items-center gap-3 rounded-2xl border border-surface-200 bg-white p-4 shadow-sm dark:border-surface-800 dark:bg-surface-900">
    <div class="relative flex-1 min-w-[200px]">
      <input type="search" name="q" value="<?php echo htmlspecialchars($filters['q']); ?>" placeholder="Naam ya email se search" class="w-full rounded-full border border-surface-200 bg-white px-10 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-800 dark:text-surface-100" />
      <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-surface-400">🔍</span>
    </div>
    <select name="role" class="rounded-full border border-surface-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-800 dark:text-surface-100">
      <option value="">All roles</option>
      <?php foreach (['user','mufti','admin','superadmin'] as $roleOption): ?>
        <option value="<?php echo $roleOption; ?>" <?php echo $filters['role']===$roleOption?'selected':''; ?>><?php echo ucfirst($roleOption); ?></option>
      <?php endforeach; ?>
    </select>
    <button class="rounded-full border border-surface-200 px-4 py-2 text-sm font-semibold hover:bg-surface-100 dark:border-surface-700 dark:hover:bg-surface-800">Filter</button>
  </form>

  <?php
  $columns = [
      ['label' => 'Name', 'key' => 'name', 'sortable' => true],
      ['label' => 'Email', 'key' => 'email'],
      ['label' => 'Role', 'key' => 'role'],
      ['label' => 'Status', 'key' => 'status'],
      ['label' => 'Actions', 'key' => 'actions'],
  ];
  $rows = [];
  $redirectQuery = http_build_query(array_merge($filters, ['page' => $pagination['page']]));
  $redirect = '/dashboard/superadmin/users'.($redirectQuery ? '?'.$redirectQuery : '');
  foreach ($users as $user) {
      $statusChip = $user['active'] ? ui_badge('Active', 'success') : ui_badge('Inactive', 'warn');
      $roleTone = $user['role']==='superadmin' ? 'danger' : ($user['role']==='admin' ? 'info' : 'neutral');

      $actions = '<div class="flex flex-wrap gap-2 text-xs">';
      if ($user['role'] !== 'superadmin') {
          foreach (['admin' => 'Admin', 'mufti' => 'Mufti', 'user' => 'User'] as $roleKey => $roleLabel) {
            if ($roleKey === $user['role']) {
                continue;
            }
            $label = match ($roleKey) {
                'admin' => 'Admin banao',
                'mufti' => 'Mufti banao',
                default => 'User bana do',
            };
            $actions .= '<form method="post" action="/sa/users/'.(int)$user['id'].'/role" class="inline">
                <input type="hidden" name="csrf" value="'.htmlspecialchars($csrf).'">
                <input type="hidden" name="role" value="'.$roleKey.'">
                <input type="hidden" name="redirect" value="'.htmlspecialchars($redirect).'">
                '.ui_button($label, 'ghost').'
            </form>';
        }
      }
      $toggleActive = $user['active'] ? 0 : 1;
      $actions .= '<form method="post" action="/sa/users/'.(int)$user['id'].'/status" class="inline">
          <input type="hidden" name="csrf" value="'.htmlspecialchars($csrf).'">
          <input type="hidden" name="active" value="'.$toggleActive.'">
          <input type="hidden" name="redirect" value="'.htmlspecialchars($redirect).'">
          '.ui_button($user['active'] ? 'Deactivate kar dein' : 'Fir se chalao', $user['active'] ? 'ghost' : 'soft').'
      </form>';
      $actions .= '</div>';

      $rows[] = [
          'name' => '<div><p class="font-semibold text-surface-900 dark:text-white">'.htmlspecialchars($user['name']).'</p><p class="text-xs text-surface-500">ID '.$user['id'].'</p></div>',
          'email' => '<span class="text-sm">'.htmlspecialchars($user['email']).'</span>',
          'role' => ui_badge(ucfirst($user['role']), $roleTone),
          'status' => $statusChip,
          'actions' => $actions,
      ];
  }
  echo ui_table($columns, $rows, ['id' => 'superadmin_users']);
  ?>

  <div class="flex items-center justify-between text-xs text-surface-500">
    <span><?php echo $pagination['total']; ?> users</span>
    <div class="flex gap-2">
      <?php if ($pagination['page'] > 1): ?>
        <a class="rounded-full border border-surface-200 px-3 py-1 hover:bg-surface-100 dark:border-surface-700 dark:hover:bg-surface-800" href="?<?php echo http_build_query(array_merge($filters, ['page' => $pagination['page']-1])); ?>">Prev</a>
      <?php endif; ?>
      <?php if ($pagination['page'] < $pagination['pages']): ?>
        <a class="rounded-full border border-surface-200 px-3 py-1 hover:bg-surface-100 dark:border-surface-700 dark:hover:bg-surface-800" href="?<?php echo http_build_query(array_merge($filters, ['page' => $pagination['page']+1])); ?>">Next</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php
$modalBody = '<p class="text-sm text-surface-600 dark:text-surface-300">IDs ya emails newline/comma separated. Is user ko kis role me lana hai?</p>
<form method="post" action="/sa/users/bulk-role" class="mt-4 space-y-3">
  <input type="hidden" name="csrf" value="'.htmlspecialchars($csrf).'">
  <textarea name="identifiers" rows="4" class="w-full rounded-xl border border-surface-200 bg-white px-4 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-800 dark:text-surface-100" placeholder="user1@example.com, user2@example.com"></textarea>
  <select name="role" class="w-full rounded-full border border-surface-200 bg-white px-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-surface-700 dark:bg-surface-800 dark:text-surface-100">
    <option value="user">User</option>
    <option value="mufti">Mufti</option>
    <option value="admin">Admin</option>
  </select>
  <div class="flex justify-end gap-2">
    '.ui_button('Band karo', 'ghost', ['type' => 'button', 'x-on:click' => 'open=false']).'
    '.ui_button('Apply karo', 'primary').'
  </div>
</form>';

echo ui_modal('bulk-role', 'Bulk promote/demote', $modalBody, '');
?>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
