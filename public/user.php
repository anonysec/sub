<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
requireRole('user');

$data = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if (xui()->login()) {
        $email = trim($_POST['email'] ?? '');
        $data = xui()->getClientTraffics($email);
    }
}
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>User</title></head>
<body>
<h2>User Subscription</h2>
<p><a href="/logout.php">Logout</a></p>
<form method="post">
  <label>Your Email <input name="email" required></label>
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES) ?>">
  <button>Check Usage</button>
</form>
<?php if ($data): ?><pre><?= htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT), ENT_QUOTES) ?></pre><?php endif; ?>
</body></html>
