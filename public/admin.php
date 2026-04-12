<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
requireRole('admin');

$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if (!xui()->login()) {
        $msg = 'Failed to login to x-ui panel.';
    } else {
        $payload = [
            'id' => (int)($_POST['inbound_id'] ?? 0),
            'settings' => json_encode([
                'clients' => [[
                    'id' => $_POST['uuid'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'limitIp' => 0,
                    'totalGB' => (int)($_POST['total_gb'] ?? 0),
                    'expiryTime' => (int)($_POST['expiry_ms'] ?? 0),
                    'enable' => true,
                ]],
            ], JSON_THROW_ON_ERROR),
        ];
        $resp = xui()->addClient($payload);
        $msg = ($resp['success'] ?? false) ? 'Client added.' : ('API error: ' . ($resp['msg'] ?? 'unknown'));
    }
}

$inbounds = [];
if (xui()->login()) {
    $resp = xui()->listInbounds();
    $inbounds = $resp['obj'] ?? [];
}
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>Admin</title></head>
<body>
<h2>Admin Panel</h2>
<p><a href="/status.php">Node Status</a> | <a href="/tickets.php">Tickets</a> | <a href="/logout.php">Logout</a></p>
<?php if ($msg): ?><p><?= htmlspecialchars($msg, ENT_QUOTES) ?></p><?php endif; ?>
<form method="post">
  <label>Inbound ID <input name="inbound_id" required></label><br>
  <label>Email <input name="email" required></label><br>
  <label>UUID <input name="uuid" required></label><br>
  <label>Total GB <input name="total_gb" value="50"></label><br>
  <label>Expiry ms unix <input name="expiry_ms" value="0"></label><br>
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES) ?>">
  <button>Add Client</button>
</form>

<h3>Inbounds</h3>
<ul>
  <?php foreach ($inbounds as $in): ?>
    <li>#<?= (int)($in['id'] ?? 0) ?> - <?= htmlspecialchars((string)($in['remark'] ?? 'no-remark'), ENT_QUOTES) ?></li>
  <?php endforeach; ?>
</ul>
</body></html>
