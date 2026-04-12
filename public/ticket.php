<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$store = new TicketStore();
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email = trim((string)($_POST['email'] ?? ''));
    $subject = trim((string)($_POST['subject'] ?? ''));
    $body = trim((string)($_POST['body'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $subject === '' || $body === '') {
        $msg = 'Please fill all fields correctly.';
    } else {
        $ticketId = 'T' . date('YmdHis') . '-' . substr(hash('sha256', $email . $subject . microtime(true)), 0, 8);
        $ok = $store->add([
            'id' => $ticketId,
            'email' => $email,
            'subject' => mb_substr($subject, 0, 120),
            'body' => mb_substr($body, 0, 2000),
            'status' => 'open',
            'created_at' => gmdate('c'),
        ]);
        $msg = $ok ? 'Ticket submitted. ID: ' . $ticketId : 'Could not save ticket.';
    }
}
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><title>Offline Ticket</title></head>
<body>
<h2>Offline Support Ticket</h2>
<p><a href="/">Home</a></p>
<?php if ($msg): ?><p><?= htmlspecialchars($msg, ENT_QUOTES) ?></p><?php endif; ?>
<form method="post">
  <label>Email <input type="email" name="email" required></label><br>
  <label>Subject <input name="subject" maxlength="120" required></label><br>
  <label>Message<br><textarea name="body" rows="8" cols="45" maxlength="2000" required></textarea></label><br>
  <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES) ?>">
  <button>Submit Ticket</button>
</form>
</body></html>
