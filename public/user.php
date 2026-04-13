<?php

declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
requireRole('user');

$data = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if (xui()->login()) {
        $email = trim((string)($_POST['email'] ?? ''));
        if ($email !== '') {
            $data = xui()->getClientTraffics($email);
        }
    }
}
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/style.css"><title>User</title></head>
<body><div class="container">
  <div class="card"><h2>User Subscription</h2><p><a href="/ticket.php?user_id=<?= urlencode((string)($_SESSION['username'] ?? '')) ?>">Open Ticket</a> | <a href="/logout.php">Logout</a></p>
    <form method="post">
      <label>Your Email <input name="email" required></label>
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES) ?>">
      <button>Check Usage</button>
    </form>
  </div>
  <?php if ($data): ?><div class="card"><pre><?= htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT), ENT_QUOTES) ?></pre></div><?php endif; ?>
</div></body></html>
