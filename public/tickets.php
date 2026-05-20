<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
requireRole('admin');

$store = new TicketStore();
$filterUser = trim((string)($_GET['user_id'] ?? ''));
$tickets = $filterUser !== '' ? $store->listForUser($filterUser, 300) : $store->listAll(500);
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/style.css"><title>Tickets</title></head>
<body><div class="container">
  <div class="card"><h2>Support Tickets</h2><p><a href="/admin.php">Back</a></p>
    <form method="get"><label>Filter by User ID <input name="user_id" value="<?= htmlspecialchars($filterUser, ENT_QUOTES) ?>"></label><button>Apply Filter</button></form>
  </div>
  <div class="card">
    <table>
      <tr><th>ID</th><th>User ID</th><th>Email</th><th>Subject</th><th>Status</th><th>Created</th><th>Body</th></tr>
      <?php foreach ($tickets as $t): ?>
      <tr>
        <td><?= htmlspecialchars((string)($t['id'] ?? ''), ENT_QUOTES) ?></td>
        <td><?= htmlspecialchars((string)($t['user_id'] ?? $filterUser), ENT_QUOTES) ?></td>
        <td><?= htmlspecialchars((string)($t['email'] ?? ''), ENT_QUOTES) ?></td>
        <td><?= htmlspecialchars((string)($t['subject'] ?? ''), ENT_QUOTES) ?></td>
        <td><?= htmlspecialchars((string)($t['status'] ?? ''), ENT_QUOTES) ?></td>
        <td><?= htmlspecialchars((string)($t['created_at'] ?? ''), ENT_QUOTES) ?></td>
        <td><?= nl2br(htmlspecialchars((string)($t['body'] ?? ''), ENT_QUOTES)) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div></body></html>
