<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
ensureStorage();
$id = isset($_GET['id']) ? sanitizeText((string)$_GET['id'], 24) : '';
?>
<!doctype html>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Danke – #LuzernerMoments</title>
    <link rel="stylesheet" href="assets/style.css" />
  </head>
  <body>
    <header class="site-header">
      <div class="container">
        <h1>#LuzernerMoments</h1>
        <nav>
          <a href="gallery.php">Galerie</a>
          <a href="index.php">Einreichen</a>
        </nav>
      </div>
    </header>

    <main class="container">
      <section class="card">
        <h2>Vielen Dank für deine Einsendung!</h2>
        <?php if ($id !== ''): ?>
          <p>Deine Einreichungs-ID: <code><?php echo escape($id); ?></code></p>
        <?php endif; ?>
        <div class="actions">
          <a class="button" href="gallery.php">Zur Galerie</a>
          <a class="button secondary" href="index.php">Weitere Einsendung</a>
        </div>
      </section>
    </main>

    <footer class="site-footer">
      <div class="container">
        <p>© <?php echo date('Y'); ?> Luzern Moments</p>
      </div>
    </footer>
  </body>
  </html>


