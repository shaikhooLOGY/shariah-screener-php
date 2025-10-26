<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Contact';
$csrf = $_SESSION['csrf'] ?? '';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Contact', 'href' => '/contact'],
];
ob_start();
?>
<section class="grid gap-8 rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900 lg:grid-cols-2">
  <div class="space-y-4">
    <span class="badge-soft">Say salaam 👋</span>
    <h1 class="text-3xl font-semibold tracking-tight text-surface-900 dark:text-white">We would love to hear from you</h1>
    <p class="text-sm text-surface-600 dark:text-surface-300">Schedule a walkthrough, request a feature, or explore how Shaikhoology can support your Shari'ah governance programme. We respond within one business day.</p>
    <div class="rounded-2xl border border-surface-200 bg-white p-5 text-sm shadow-sm dark:border-surface-800 dark:bg-surface-900">
      <p class="font-semibold text-surface-900 dark:text-white">Support channels</p>
      <p class="mt-2 text-surface-600 dark:text-surface-300">Email <a href="mailto:salaam@shaikhoology.com">salaam@shaikhoology.com</a> or call +971-4-000-0001</p>
      <p class="mt-4 text-xs text-surface-500">Office hours: Sunday – Thursday, 9am to 6pm GST</p>
    </div>
  </div>
  <form method="post" action="/contact" class="space-y-4">
    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
    <?php echo ui_input('name', 'Full name'); ?>
    <?php echo ui_input('email', 'Business email', 'email'); ?>
    <?php echo ui_select('topic', 'Topic', ['Demo' => 'Book a demo', 'Support' => 'Support', 'Partnership' => 'Partnership']); ?>
    <?php echo ui_textarea('message', 'How can we help?', '', 'Include context, timelines, and target markets.'); ?>
    <div class="flex gap-2">
      <?php echo ui_button('Send message'); ?>
      <?php echo ui_button('Schedule call', 'ghost', ['href' => '#']); ?>
    </div>
  </form>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
