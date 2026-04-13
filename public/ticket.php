<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$store = new TicketStore();
$msg = null;
$userId = trim((string)($_POST['user_id'] ?? $_GET['user_id'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if (!checkRateLimit('ticket_submit', 10, 3600)) {
        $msg = 'Too many tickets from your IP. Try later.';
    } else {
        $email = trim((string)($_POST['email'] ?? ''));
        $subject = trim((string)($_POST['subject'] ?? ''));
        $body = trim((string)($_POST['body'] ?? ''));

        if ($userId === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $subject === '' || $body === '') {
            $msg = 'Please fill all fields correctly.';
        } else {
            $ticketId = 'T' . date('YmdHis') . '-' . substr(hash('sha256', $email . $subject . microtime(true)), 0, 8);
            $ok = $store->addForUser($userId, [
                'id' => $ticketId,
                'user_id' => $userId,
                'email' => $email,
                'subject' => mb_substr($subject, 0, 120),
                'body' => mb_substr($body, 0, 2000),
                'status' => 'open',
                'created_at' => gmdate('c'),
                'telegram' => 'https://t.me/imKoris',
            ]);
            if (!$ok) {
                $msg = 'Could not save ticket.';
            } else {
                bumpRateLimit('ticket_submit');
                $msg = 'Ticket submitted. ID: ' . $ticketId;
            }
        }
    }
}
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/style.css"><title>Offline Ticket</title></head>
<body><div class="container">
  <div class="card"><h2>Offline Support Ticket</h2><p class="muted">Need urgent help? Telegram: <a href="https://t.me/imKoris" target="_blank" rel="noopener noreferrer">@imKoris</a></p></div>
  <div class="card">
    <?php if ($msg): ?><p class="badge"><?= htmlspecialchars($msg, ENT_QUOTES) ?></p><?php endif; ?>
    <form method="post">
      <label>User ID <input name="user_id" value="<?= htmlspecialchars($userId, ENT_QUOTES) ?>" placeholder="example: user123" required></label>
      <label>Email <input type="email" name="email" required></label>
      <label>Subject <input name="subject" maxlength="120" required></label>
      <label>Message<textarea name="body" rows="8" maxlength="2000" required></textarea></label>
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES) ?>">
      <button>Submit Ticket</button>
    </form>
  </div>
</div></body></html>
