<?php

declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';

$error = null;
if (!verifyHiddenAccess()) { http_response_code(404); exit('Not found'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if (!checkRateLimit('login')) {
        $error = 'Too many attempts. Try later.';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $role = (string)($_POST['role'] ?? 'user');
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
}
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/style.css"><title>Customer Access</title></head>
<body><div class="container"><div class="card">
  <h2>Portal Login</h2>
  <?php if ($error): ?><p class="badge"><?= htmlspecialchars($error, ENT_QUOTES) ?></p><?php endif; ?>
  <form method="post">
    <input name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <select name="role"><option value="user">User</option><option value="admin">Admin</option></select>
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES) ?>">
    <button type="submit">Enter</button>
  </form>
</div></div></body></html>
