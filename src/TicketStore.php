<?php

declare(strict_types=1);

final class TicketStore
{
    private string $file;

    public function __construct(?string $file = null)
    {
        $this->file = $file ?? (__DIR__ . '/../data/tickets.jsonl');
    }

    /** @param array<string,mixed> $ticket */
    public function add(array $ticket): bool
    {
        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $json = json_encode($ticket, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return false;
        }

        return file_put_contents($this->file, $json . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;
    }

    /** @return array<int,array<string,mixed>> */
    public function list(int $limit = 200): array
    {
        if (!file_exists($this->file)) {
            return [];
        }

        $lines = @file($this->file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return [];
        }

        $lines = array_slice($lines, -$limit);
        $out = [];
        foreach ($lines as $line) {
            $row = json_decode($line, true);
            if (is_array($row)) {
                $out[] = $row;
            }
        }

        return array_reverse($out);
    }
}
