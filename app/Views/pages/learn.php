<?php
require_once __DIR__.'/../partials/ui.php';
$title = 'Learn';
$breadcrumbs = [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Learn', 'href' => '/learn'],
];
$courses = [
    ['title' => 'Foundations of Shari\'ah screening', 'duration' => '45 mins', 'level' => 'Intro', 'cta' => '/articles'],
    ['title' => 'Advanced ratio troubleshooting', 'duration' => '60 mins', 'level' => 'Intermediate', 'cta' => '/articles'],
    ['title' => 'Working with scholars remotely', 'duration' => '35 mins', 'level' => 'Practitioner', 'cta' => '/discussions'],
];
ob_start();
?>
<section class="rounded-3xl border border-surface-200 bg-white px-6 py-10 shadow-sm dark:border-surface-800 dark:bg-surface-900">
  <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <div>
      <h1 class="text-3xl font-semibold tracking-tight text-surface-900 dark:text-white">Learning library</h1>
      <p class="mt-2 max-w-2xl text-sm text-surface-600 dark:text-surface-300">Self-paced courses and guides to upskill compliance teams, scholars, and investor relations.</p>
    </div>
    <div class="flex gap-2">
      <?php echo ui_button('View articles', 'primary', ['href' => '/articles']); ?>
      <?php echo ui_button('Join live workshop', 'ghost', ['href' => '#']); ?>
    </div>
  </header>

  <div class="mt-8 grid gap-6 lg:grid-cols-3">
    <?php foreach ($courses as $course): ?>
      <article class="rounded-2xl border border-surface-200 bg-white p-6 shadow-sm dark:border-surface-800 dark:bg-surface-900">
        <div class="flex items-center justify-between text-xs text-surface-500 dark:text-surface-400">
          <span><?php echo htmlspecialchars($course['duration']); ?></span>
          <span class="rounded-full border border-surface-200 px-3 py-1 dark:border-surface-700"><?php echo htmlspecialchars($course['level']); ?></span>
        </div>
        <h2 class="mt-3 text-lg font-semibold text-surface-900 dark:text-white"><?php echo htmlspecialchars($course['title']); ?></h2>
        <div class="mt-4 flex gap-2">
          <?php echo ui_button('Start lesson', 'soft', ['href' => $course['cta']]); ?>
          <?php echo ui_button('Save', 'link', ['href' => '#']); ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php
$content = ob_get_clean();
include __DIR__.'/../layout.php';
