<?php

declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
$news = fetchNewsItems(6);
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/style.css"><title>Blue Horizon Travel</title></head>
<body><div class="container">
  <div class="card"><h1>Blue Horizon Travel</h1><p class="muted">Custom tours, visa support, and family packages.</p></div>
  <div class="card"><h3>Turkey Spring Tour</h3><p>7-day guided package with hotel and airport transfer.</p></div>
  <div class="card"><h3>Malaysia Family Plan</h3><p>Affordable weekly departures and Arabic/Persian support.</p></div>
  <div class="card"><h3>Europe Visa Help</h3><p>Document checklist and embassy appointment support.</p></div>
  <?php if ($news): ?><div class="card"><h3>Latest Iran News</h3><ul><?php foreach ($news as $n): ?><li><a href="<?= htmlspecialchars((string)$n['link'], ENT_QUOTES) ?>" rel="noopener noreferrer" target="_blank"><?= htmlspecialchars((string)$n['title'], ENT_QUOTES) ?></a></li><?php endforeach; ?></ul></div><?php endif; ?>
  <div class="card"><h3>Need help?</h3><p>You can submit offline support request.</p><p><a href="/ticket.php">Open Ticket</a></p></div>
</div></body></html>
