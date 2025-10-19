<?php
require_once 'config.php';
require_once 'functions.php';

// Hole die letzten Submissions für die Galerie
$recentSubmissions = getRecentSubmissions(6);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>#LuzernerMoments - Dein Blick auf unsere Stadt</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1>🏔️ Luzerner Tourismusbüro</h1>
            </div>
            <nav>
                <a href="index.php">Home</a>
                <a href="gallery.php">Galerie</a>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h2>#LuzernerMoments</h2>
            <p class="tagline">Dein Blick auf unsere Stadt</p>
        </div>
    </section>

    <main class="container">
        <section class="intro">
            <h3>Teile deinen Luzerner Moment!</h3>
            <p>
                Die Stadt Luzern ruft zur Aktion <strong>#LuzernerMoments</strong> auf. Gesucht sind die 
                eindrücklichsten Augenblicke aus Luzern: das goldene Licht über dem Vierwaldstättersee, 
                die Kapellbrücke im Morgennebel, festliche Stimmung rund um das Luzerner Stadtfest oder 
                die stille Aussicht vom Pilatus.
            </p>
            <p>
                Touristinnen, Touristen und Einheimische laden ihr persönliches Highlight-Foto hoch und 
                beschreiben in wenigen Sätzen, warum gerade dieser Moment zählt. Jede Woche werden kleine 
                Preise vergeben – von Museumspässen bis Schifffahrt-Tickets.
            </p>
            <div class="prize-box">
                <h4>🏆 Hauptpreis</h4>
                <p>
                    Ein atemberaubendes Wochenende in Luzern mit Sonnenuntergangsfahrt auf dem 
                    Vierwaldstättersee, Fototour, Rooftop-Dinner und Hotelnacht!
                </p>
            </div>
        </section>

        <section class="upload-section">
            <h3>Lade dein Foto hoch</h3>
            <form action="upload.php" method="POST" enctype="multipart/form-data" class="upload-form">
                <div class="form-group">
                    <label for="name">Dein Name:</label>
                    <input type="text" id="name" name="name" required maxlength="100">
                </div>

                <div class="form-group">
                    <label for="email">E-Mail:</label>
                    <input type="email" id="email" name="email" required maxlength="100">
                </div>

                <div class="form-group">
                    <label for="description">Beschreibe deinen Moment:</label>
                    <textarea id="description" name="description" required maxlength="500" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label for="photo">Dein Foto (JPG, PNG, GIF):</label>
                    <input type="file" id="photo" name="photo" accept="image/*" required>
                </div>

                <button type="submit" class="btn-submit">Moment teilen</button>
            </form>
        </section>

        <?php if (!empty($recentSubmissions)): ?>
        <section class="recent-gallery">
            <h3>Aktuelle Luzerner Moments</h3>
            <div class="gallery-grid">
                <?php foreach ($recentSubmissions as $submission): ?>
                    <div class="gallery-item">
                        <img src="<?php echo htmlspecialchars($submission['photo']); ?>" 
                             alt="<?php echo htmlspecialchars($submission['name']); ?>">
                        <div class="gallery-info">
                            <p class="author">Von: <?php echo htmlspecialchars($submission['name']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="gallery-link">
                <a href="gallery.php" class="btn-secondary">Alle Moments ansehen →</a>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Luzerner Tourismusbüro. Eine Initiative zur Förderung des Luzerner Tourismus.</p>
            <p class="footer-note">Kampagne läuft während der Hochsaison und dem Luzerner Stadtfest.</p>
        </div>
    </footer>
</body>
</html>

