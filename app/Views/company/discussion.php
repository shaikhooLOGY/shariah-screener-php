<h1>Discussion — <?php echo htmlspecialchars($symbol ?? ''); ?></h1>
<div style="margin-bottom:12px;">
  <a href="/company/<?php echo urlencode($symbol); ?>">Compliance</a> |
  <a href="/company/<?php echo urlencode($symbol); ?>/discussion">Discussion</a> |
  <a href="/company/<?php echo urlencode($symbol); ?>/suggest">Suggest Ratios</a>
</div>

<?php if (!empty($_GET['ok'])): ?>
  <div style="padding:8px;background:#e7ffe7;border:1px solid #b2e6b2;border-radius:6px;">Posted.</div>
<?php endif; ?>

<h2>New post</h2>
<form method="post" action="">
  <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf ?? ''); ?>">
  <textarea name="body" rows="4" style="width:100%;" placeholder="Share sources, page numbers, logic…"></textarea>
  <br><button type="submit">Post</button>
</form>

<h2>Recent</h2>
<?php if (!empty($posts)): ?>
  <ul>
  <?php foreach ($posts as $p): ?>
    <li><pre style="white-space:pre-wrap;background:#fafafa;border:1px solid #eee;padding:8px;border-radius:6px;"><?php echo htmlspecialchars($p['body_md']); ?></pre>
    <small><?php echo htmlspecialchars($p['created_at']); ?></small></li>
  <?php endforeach; ?>
  </ul>
<?php else: ?>
  <p>No posts yet.</p>
<?php endif; ?>
