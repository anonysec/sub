<?php

declare(strict_types=1);

function loadEnv(string $path): array
{
    $vars = [];
    if (!file_exists($path)) {
        return $vars;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }
        [$key, $value] = array_pad(explode('=', $trimmed, 2), 2, '');
        $vars[trim($key)] = trim($value);
    }

    return $vars;
}

function cfg(string $key, ?string $default = null): ?string
{
    static $env = null;
    if ($env === null) {
        $env = loadEnv(__DIR__ . '/../.env');
    }

    return $env[$key] ?? $default;
}

function appSecret(): string
{
    return cfg('APP_SECRET', 'dev-secret-change-me') ?? 'dev-secret-change-me';
}

function hiddenPath(): string
{
    return cfg('HIDDEN_PATH', '/portal-7f9a') ?? '/portal-7f9a';
}

/**
 * @return array<int, array{label:string,base_url:string,username:string,password:string,weight?:int}>
 */
function xuiNodes(): array
{
    $raw = cfg('XUI_NODES', '');
    if (!$raw) {
        return [[
            'label' => cfg('XUI_LABEL', 'primary') ?? 'primary',
            'base_url' => cfg('XUI_BASE_URL', '') ?? '',
            'username' => cfg('XUI_USERNAME', '') ?? '',
            'password' => cfg('XUI_PASSWORD', '') ?? '',
            'weight' => (int)(cfg('XUI_WEIGHT', '1') ?? '1'),
        ]];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return [];
    }

    $valid = [];
    foreach ($decoded as $node) {
        if (!is_array($node)) {
            continue;
        }
        $valid[] = [
            'label' => (string)($node['label'] ?? 'node'),
            'base_url' => (string)($node['base_url'] ?? ''),
            'username' => (string)($node['username'] ?? ''),
            'password' => (string)($node['password'] ?? ''),
            'weight' => (int)($node['weight'] ?? 1),
        ];
    }

    return $valid;
}
