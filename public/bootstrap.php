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


function rateLimitKey(string $scope): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    return hash('sha256', $scope . '|' . $ip);
}

function checkRateLimit(string $scope, int $max = 8, int $windowSec = 600): bool
{
    $key = rateLimitKey($scope);
    $file = __DIR__ . '/../tmp/rl_' . $key . '.json';
    $now = time();
    $state = ['count' => 0, 'start' => $now];

    if (file_exists($file)) {
        $raw = json_decode((string)file_get_contents($file), true);
        if (is_array($raw)) {
            $state['count'] = (int)($raw['count'] ?? 0);
            $state['start'] = (int)($raw['start'] ?? $now);
        }
    }

    if (($now - $state['start']) > $windowSec) {
        $state = ['count' => 0, 'start' => $now];
    }

    return $state['count'] < $max;
}

function bumpRateLimit(string $scope): void
{
    $key = rateLimitKey($scope);
    $file = __DIR__ . '/../tmp/rl_' . $key . '.json';
    $now = time();
    $state = ['count' => 0, 'start' => $now];

    if (file_exists($file)) {
        $raw = json_decode((string)file_get_contents($file), true);
        if (is_array($raw)) {
            $state['count'] = (int)($raw['count'] ?? 0);
            $state['start'] = (int)($raw['start'] ?? $now);
        }
    }

    $state['count']++;
    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0700, true);
    }
    file_put_contents($file, json_encode($state), LOCK_EX);
}

function verifyHiddenAccess(): bool
{
    $accessKey = cfg('ACCESS_KEY', '');
    if (!$accessKey) {
        return true;
    }
    $provided = (string)($_GET['k'] ?? '');
    return $provided !== '' && hash_equals($accessKey, $provided);
}

/** @return array<int, array<string,mixed>> */
function fetchNewsItems(int $limit = 5): array
{
    $feedsRaw = cfg('NEWS_RSS_FEEDS', '');
    if (!$feedsRaw) {
        return [];
    }

    $feeds = array_filter(array_map('trim', explode(',', $feedsRaw)));
    $items = [];

    foreach ($feeds as $feedUrl) {
        if (!str_starts_with($feedUrl, 'https://')) {
            continue;
        }

        $ch = curl_init($feedUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'PortalRSS/1.0',
        ]);
        $xmlRaw = curl_exec($ch);
        curl_close($ch);

        if (!is_string($xmlRaw) || $xmlRaw === '') {
            continue;
        }

        $xml = @simplexml_load_string($xmlRaw, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$xml || !isset($xml->channel->item)) {
            continue;
        }

        foreach ($xml->channel->item as $entry) {
            $items[] = [
                'title' => (string)($entry->title ?? ''),
                'link' => (string)($entry->link ?? ''),
                'pubDate' => (string)($entry->pubDate ?? ''),
            ];
            if (count($items) >= $limit) {
                break 2;
            }
        }
    }

    return $items;
}
