<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
ensureStorage();
$items = loadSubmissions();
?>
<!doctype html>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Galerie – #LuzernerMoments</title>
    <link rel="stylesheet" href="assets/style.css" />
  </head>
  <body>
    <header class="site-header">
      <div class="container">
        <h1>#LuzernerMoments</h1>
        <nav>
          <a href="gallery.php" aria-current="page">Galerie</a>
          <a href="index.php">Einreichen</a>
        </nav>
      </div>
    </header>

    <main class="container">
      <h2>Galerie</h2>
      <?php if (empty($items)): ?>
        <p>Es wurden noch keine Bilder eingereicht. <a href="index.php">Sei die/der Erste!</a></p>
      <?php else: ?>
        <div class="grid">
          <?php foreach ($items as $entry): ?>
            <figure class="card">
              <img loading="lazy" src="<?php echo 'uploads/' . escape($entry['image']); ?>" alt="<?php echo escape($entry['title']); ?>" />
              <figcaption>
                <h3><?php echo escape($entry['title']); ?></h3>
                <p><?php echo escape($entry['description']); ?></p>
                <p class="meta">Eingereicht am <?php echo escape(date('d.m.Y, H:i', strtotime((string)$entry['created_at']))); ?></p>
                <?php if (!empty($entry['metadata'])): ?>
                  <details class="metadata">
                    <summary>Bildinformationen</summary>
                    <dl>
                      <?php foreach ($entry['metadata'] as $key => $value): ?>
                        <dt><?php echo escape($key); ?></dt>
                        <dd><?php echo escape(is_array($value) ? json_encode($value) : (string)$value); ?></dd>
                      <?php endforeach; ?>
                    </dl>
                  </details>
                <?php endif; ?>
              </figcaption>
            </figure>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </main>

    <footer class="site-footer">
      <div class="container">
        <p>© <?php echo date('Y'); ?> Luzern Moments</p>
      </div>
    </footer>
  </body>
  </html>


