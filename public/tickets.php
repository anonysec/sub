<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
requireRole('admin');

$store = new TicketStore();
$tickets = $store->list(300);
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>Tickets</title></head>
<body>
<h2>Support Tickets</h2>
<p><a href="/admin.php">Back to admin</a></p>
<table border="1" cellpadding="6" cellspacing="0">
<tr><th>ID</th><th>Email</th><th>Subject</th><th>Status</th><th>Created</th><th>Body</th></tr>
<?php foreach ($tickets as $t): ?>
<tr>
  <td><?= htmlspecialchars((string)($t['id'] ?? ''), ENT_QUOTES) ?></td>
  <td><?= htmlspecialchars((string)($t['email'] ?? ''), ENT_QUOTES) ?></td>
  <td><?= htmlspecialchars((string)($t['subject'] ?? ''), ENT_QUOTES) ?></td>
  <td><?= htmlspecialchars((string)($t['status'] ?? ''), ENT_QUOTES) ?></td>
  <td><?= htmlspecialchars((string)($t['created_at'] ?? ''), ENT_QUOTES) ?></td>
  <td><?= nl2br(htmlspecialchars((string)($t['body'] ?? ''), ENT_QUOTES)) ?></td>
</tr>
<?php endforeach; ?>
</table>
</body></html>
