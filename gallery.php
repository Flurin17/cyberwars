<?php
require_once 'config.php';
require_once 'functions.php';

// Hole alle Submissions
$allSubmissions = getAllSubmissions();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galerie - #LuzernerMoments</title>
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
                <a href="gallery.php" class="active">Galerie</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="page-header">
            <h2>#LuzernerMoments Galerie</h2>
            <p>Entdecke die schönsten Momente aus Luzern</p>
        </section>

        <?php if (empty($allSubmissions)): ?>
            <div class="empty-state">
                <p>Noch keine Moments vorhanden. Sei der Erste und teile deinen Luzerner Moment!</p>
                <a href="index.php" class="btn-secondary">Jetzt hochladen</a>
            </div>
        <?php else: ?>
            <div class="gallery-grid full">
                <?php foreach ($allSubmissions as $submission): ?>
                    <div class="gallery-item detailed">
                        <img src="<?php echo htmlspecialchars($submission['photo']); ?>" 
                             alt="<?php echo htmlspecialchars($submission['name']); ?>">
                        <div class="gallery-details">
                            <p class="author"><strong><?php echo htmlspecialchars($submission['name']); ?></strong></p>
                            <p class="description"><?php echo htmlspecialchars($submission['description']); ?></p>
                            <p class="timestamp"><?php echo htmlspecialchars($submission['timestamp']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="back-link">
            <a href="index.php" class="btn-secondary">← Zurück zur Startseite</a>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Luzerner Tourismusbüro. Eine Initiative zur Förderung des Luzerner Tourismus.</p>
        </div>
    </footer>
</body>
</html>

