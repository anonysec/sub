<?php

declare(strict_types=1);

final class TicketStore
{
    private string $baseDir;

    public function __construct(?string $baseDir = null)
    {
        $this->baseDir = $baseDir ?? (__DIR__ . '/../data/tickets');
    }

    private function sanitizeUserId(string $userId): string
    {
        $id = strtolower(trim($userId));
        $id = preg_replace('/[^a-z0-9._-]/', '-', $id) ?? 'unknown';
        return trim($id, '-_.') ?: 'unknown';
    }

    private function pathForUser(string $userId): string
    {
        return $this->baseDir . '/' . $this->sanitizeUserId($userId) . '.jsonl';
    }

    /** @param array<string,mixed> $ticket */
    public function addForUser(string $userId, array $ticket): bool
    {
        $file = $this->pathForUser($userId);
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0700, true);
        }

        $json = json_encode($ticket, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return false;
        }

        return file_put_contents($file, $json . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;
    }

    /** @return array<int,array<string,mixed>> */
    public function listForUser(string $userId, int $limit = 200): array
    {
        return $this->readFile($this->pathForUser($userId), $limit);
    }

    /** @return array<int,array<string,mixed>> */
    public function listAll(int $limit = 500): array
    {
        if (!is_dir($this->baseDir)) {
            return [];
        }

        $all = [];
        foreach (glob($this->baseDir . '/*.jsonl') ?: [] as $file) {
            $userId = basename($file, '.jsonl');
            foreach ($this->readFile($file, $limit) as $row) {
                $row['user_id'] = $row['user_id'] ?? $userId;
                $all[] = $row;
            }
        }

        usort($all, static fn(array $a, array $b): int => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
        return array_slice($all, 0, $limit);
    }

    /** @return array<int,array<string,mixed>> */
    private function readFile(string $file, int $limit): array
    {
        if (!file_exists($file)) {
            return [];
        }
        $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return [];
        }

        $rows = [];
        foreach (array_slice($lines, -$limit) as $line) {
            $data = json_decode($line, true);
            if (is_array($data)) {
                $rows[] = $data;
            }
        }
        return array_reverse($rows);
    }
}
