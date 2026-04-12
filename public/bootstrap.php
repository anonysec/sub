<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/XuiClient.php';
require_once __DIR__ . '/../src/NodeBalancer.php';
require_once __DIR__ . '/../src/TicketStore.php';


header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self';");

session_name('portal_sess');
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => isset($_SERVER['HTTPS']),
    'cookie_samesite' => 'Strict',
]);

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function verifyCsrf(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(403);
        exit('Bad CSRF token');
    }
}

function xui(): XuiClient
{
    static $xui = null;
    if ($xui === null) {
        $xui = new XuiClient(
            cfg('XUI_BASE_URL', '') ?? '',
            cfg('XUI_USERNAME', '') ?? '',
            cfg('XUI_PASSWORD', '') ?? ''
        );
    }
    return $xui;
}

function requireRole(string $role): void
{
    if (($_SESSION['role'] ?? '') !== $role) {
        header('Location: ' . hiddenPath());
        exit;
    }
}


function verifyPasswordByRole(string $role, string $password): bool
{
    $hashKey = $role === 'admin' ? 'ADMIN_PASSWORD_HASH' : 'USER_PASSWORD_HASH';
    $plainKey = $role === 'admin' ? 'ADMIN_PASSWORD' : 'USER_PASSWORD';

    $hash = cfg($hashKey, '');
    if ($hash) {
        return password_verify($password, $hash);
    }

    $plain = cfg($plainKey, '');
    return $plain !== null && hash_equals($plain, $password);
}

