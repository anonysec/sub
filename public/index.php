<?php

declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
$news = fetchNewsItems(6);
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Blue Horizon Travel</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; background: #f7f8fa; color: #1f2937; }
    header { background: #0ea5e9; color: white; padding: 2rem; }
    main { max-width: 860px; margin: auto; padding: 2rem; }
    .card { background: white; border-radius: 10px; padding: 1rem 1.2rem; margin: 1rem 0; box-shadow: 0 1px 6px rgba(0,0,0,.08); }
  </style>
</head>
<body>
<header>
  <h1>Blue Horizon Travel</h1>
  <p>Custom tours, visa support, and family packages.</p>
</header>
<main>
  <div class="card"><h3>Turkey Spring Tour</h3><p>7-day guided package with hotel and airport transfer.</p></div>
  <div class="card"><h3>Malaysia Family Plan</h3><p>Affordable weekly departures and Arabic/Persian support.</p></div>
  <div class="card"><h3>Europe Visa Help</h3><p>Document checklist and embassy appointment support.</p></div>
  <?php if ($news): ?>
  <section class="card">
    <h3>Latest Iran News</h3>
    <ul>
      <?php foreach ($news as $n): ?>
        <li><a href="<?= htmlspecialchars((string)$n['link'], ENT_QUOTES) ?>" rel="noopener noreferrer" target="_blank"><?= htmlspecialchars((string)$n['title'], ENT_QUOTES) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>
  <div class="card"><h3>Need help?</h3><p>You can submit offline support request.</p><p><a href="/ticket.php">Open Ticket</a></p></div>
</main>
</body>
</html>
