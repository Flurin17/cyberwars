<?php
require_once 'config.php';

$photoName = $_GET['photo'] ?? '';
$photoPath = '';

if (!empty($photoName)) {
    $photoPath = UPLOAD_DIR . basename($photoName);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danke - #LuzernerMoments</title>
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

    <main class="container">
        <section class="thanks-section">
            <div class="success-icon">✓</div>
            <h2>Vielen Dank!</h2>
            <p class="success-message">
                Dein Luzerner Moment wurde erfolgreich hochgeladen und nimmt jetzt an der Aktion teil.
            </p>

            <?php if (!empty($photoPath) && file_exists($photoPath)): ?>
                <div class="uploaded-preview">
                    <h3>Dein hochgeladenes Foto:</h3>
                    <img src="<?php echo htmlspecialchars($photoPath); ?>" alt="Dein Upload">
                </div>
            <?php endif; ?>

            <div class="next-steps">
                <h3>Wie geht es weiter?</h3>
                <ul>
                    <li>📧 Du erhältst eine Bestätigungs-E-Mail</li>
                    <li>🏆 Jede Woche werden Gewinner ausgelost</li>
                    <li>📸 Am Ende der Kampagne kürt eine Jury das beste Foto</li>
                    <li>🎁 Der Hauptgewinn: Ein unvergessliches Wochenende in Luzern</li>
                </ul>
            </div>

            <div class="cta-buttons">
                <a href="gallery.php" class="btn-secondary">Zur Galerie</a>
                <a href="index.php" class="btn-secondary">Zur Startseite</a>
            </div>

            <div class="share-reminder">
                <p>💙 Teile deinen Moment auf Social Media mit <strong>#LuzernerMoments</strong></p>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Luzerner Tourismusbüro. Eine Initiative zur Förderung des Luzerner Tourismus.</p>
        </div>
    </footer>
</body>
</html>

