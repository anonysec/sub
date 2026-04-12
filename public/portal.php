<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$error = null;
if (!verifyHiddenAccess()) {
    http_response_code(404);
    exit('Not found');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!checkRateLimit('login')) {
        http_response_code(429);
        $error = 'Too many attempts. Try later.';
    }
    verifyCsrf();

    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $role = (string) ($_POST['role'] ?? 'user');

    if ($username === '' || $password === '' || !verifyPasswordByRole($role, $password)) {
        bumpRateLimit('login');
        $error = 'Invalid credentials';
    } else {
        session_regenerate_id(true);
        $_SESSION['role'] = $role;
        $_SESSION['username'] = $username;
        header('Location: ' . ($role === 'admin' ? '/admin.php' : '/user.php'));
        exit;
    }
}
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Customer Access</title>
  <style>
    body { font-family: Arial, sans-serif; background: #111827; color: #f9fafb; display:grid; place-items:center; min-height:100vh; }
    .box { width: 320px; background:#1f2937; padding: 1.2rem; border-radius: 10px; }
    input, select, button { width:100%; margin:.5rem 0; padding:.6rem; border-radius:6px; border:1px solid #374151; }
    button { background:#0ea5e9; color:white; border:0; cursor:pointer; }
    .e { color: #fca5a5; }
  </style>
</head>
<body>
  <form method="post" class="box">
    <h3>Customer Portal</h3>
    <?php if ($error): ?><p class="e"><?= htmlspecialchars($error, ENT_QUOTES) ?></p><?php endif; ?>
    <input name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <select name="role"><option value="user">User</option><option value="admin">Admin</option></select>
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES) ?>">
    <button type="submit">Enter</button>
  </form>
</body>
</html>
