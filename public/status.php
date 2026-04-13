<?php

declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
requireRole('admin');

$balancer = new NodeBalancer();
$report = $balancer->evaluate(xuiNodes());
$recommended = $report['recommended'];
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/style.css"><title>Node Status</title></head>
<body><div class="container"><div class="card">
<h2>Outbound/Node Status</h2>
<p><a href="/admin.php">Back to admin</a> | <a href="/logout.php">Logout</a></p>
<?php if ($recommended && ($recommended['ok'] ?? false)): ?>
  <p class="badge">Recommended: <?= htmlspecialchars((string)$recommended['label'], ENT_QUOTES) ?> (score <?= htmlspecialchars((string)$recommended['score'], ENT_QUOTES) ?>)</p>
<?php else: ?><p>No healthy node found.</p><?php endif; ?>
<table>
  <tr><th>Label</th><th>Status</th><th>Weight</th><th>CPU%</th><th>Mem%</th><th>Online Clients</th><th>Score</th></tr>
  <?php foreach ($report['nodes'] as $node): ?>
    <tr><td><?= htmlspecialchars((string)($node['label'] ?? ''), ENT_QUOTES) ?></td><td><?= !empty($node['ok']) ? 'OK' : 'DOWN' ?></td><td><?= (int)($node['weight'] ?? 1) ?></td><td><?= htmlspecialchars((string)($node['cpu'] ?? '-'), ENT_QUOTES) ?></td><td><?= htmlspecialchars((string)($node['mem_percent'] ?? '-'), ENT_QUOTES) ?></td><td><?= htmlspecialchars((string)($node['online_clients'] ?? '-'), ENT_QUOTES) ?></td><td><?= htmlspecialchars((string)($node['score'] ?? '-'), ENT_QUOTES) ?></td></tr>
  <?php endforeach; ?>
</table>
</div></div></body></html>
