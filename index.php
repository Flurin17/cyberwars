<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
startAppSession();
ensureStorage();
?>
<!doctype html>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>#LuzernerMoments – Luzern CTF Galerie</title>
    <link rel="stylesheet" href="assets/style.css" />
  </head>
  <body>
    <header class="site-header">
      <div class="container">
        <h1>#LuzernerMoments</h1>
        <nav>
          <a href="gallery.php">Galerie</a>
          <a href="index.php" aria-current="page">Einreichen</a>
        </nav>
      </div>
    </header>

    <section class="hero">
      <div class="container">
        <h2>Zeig deinen schönsten Luzern-Moment</h2>
        <p>
          Die Stadt Luzern ruft zur Aktion <strong>#LuzernerMoments</strong> auf. Gesucht sind die eindrücklichsten Augenblicke aus Luzern: das goldene Licht über dem Vierwaldstättersee, die Kapellbrücke im Morgennebel, festliche Stimmung rund um das Luzerner Stadtfest oder die stille Aussicht vom Pilatus.
        </p>
        <p>
          Touristinnen, Touristen und Einheimische laden ihr persönliches Highlight-Foto hoch und beschreiben in wenigen Sätzen, warum gerade dieser Moment zählt. Jede Woche werden kleine Preise vergeben. Am Ende kürt eine Jury das kreativste und stimmungsvollste Bild der Saison und vergibt den Hauptpreis.
        </p>
      </div>
    </section>

    <main class="container">
      <section class="card">
        <h3>Dein Beitrag</h3>
        <form action="upload.php" method="post" enctype="multipart/form-data" class="upload-form">

          <label for="title">Titel</label>
          <input type="text" id="title" name="title" maxlength="80" required placeholder="z.B. Morgenstimmung an der Kapellbrücke" />

          <label for="description">Warum zählt dieser Moment?</label>
          <textarea id="description" name="description" rows="4" maxlength="400" required placeholder="Kurze Beschreibung (max. 400 Zeichen)"></textarea>

          <label for="photo">Foto hochladen</label>
          <input type="file" id="photo" name="photo" accept="image/*" required />
          <p class="help">Erlaubt: JPG, PNG, WEBP, GIF – bis 10&nbsp;MB. Bilder werden automatisch in JPG umgewandelt.</p>

          <div class="actions">
            <button type="submit">Einreichen</button>
            <a class="button secondary" href="gallery.php">Galerie ansehen</a>
          </div>
        </form>
      </section>
    </main>

    <footer class="site-footer">
      <div class="container">
        <p>© <?php echo date('Y'); ?> Luzern Moments</p>
      </div>
    </footer>
  </body>
  </html>


