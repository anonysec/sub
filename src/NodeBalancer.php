<?php

declare(strict_types=1);

require_once __DIR__ . '/XuiClient.php';

final class NodeBalancer
{
    /**
     * @param array<int, array{label:string,base_url:string,username:string,password:string,weight?:int}> $nodes
     * @return array{nodes: array<int, array<string, mixed>>, recommended: ?array<string,mixed>}
     */
    public function evaluate(array $nodes): array
    {
        $results = [];

        foreach ($nodes as $node) {
            $client = new XuiClient($node['base_url'], $node['username'], $node['password']);
            $label = $node['label'];
            $weight = (int)($node['weight'] ?? 1);

            if (!$client->login()) {
                $results[] = [
                    'label' => $label,
                    'ok' => false,
                    'weight' => $weight,
                    'score' => -1,
                    'reason' => 'login_failed',
                ];
                continue;
            }

            $status = $client->serverStatus();
            $online = $client->onlineClients();

            $cpu = (float)($status['obj']['cpu'] ?? 0);
            $memUsed = (float)($status['obj']['mem']['current'] ?? 0);
            $memTotal = (float)($status['obj']['mem']['total'] ?? 1);
            $memPercent = $memTotal > 0 ? ($memUsed / $memTotal) * 100 : 0;
            $onlineCount = is_array($online['obj'] ?? null) ? count($online['obj']) : 0;

            $score = max(0, 100 - $cpu - $memPercent - ($onlineCount * 1.2));
            $weightedScore = $score * $weight;

            $results[] = [
                'label' => $label,
                'ok' => true,
                'weight' => $weight,
                'cpu' => round($cpu, 2),
                'mem_percent' => round($memPercent, 2),
                'online_clients' => $onlineCount,
                'score' => round($weightedScore, 2),
            ];
        }

        usort($results, static fn(array $a, array $b): int => ($b['score'] <=> $a['score']));

        return [
            'nodes' => $results,
            'recommended' => $results[0] ?? null,
        ];
    }
}
