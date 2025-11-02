<?php
require_once '../config.php';
require_once '../functions.php';

$submissions = getAllSubmissions();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uploads - #LuzernerMoments</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1>🏔️ Luzerner Tourismusbüro</h1>
            </div>
            <nav>
                <a href="../index.php">Home</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <section class="gallery-section">
            <h2>Alle eingereichten Momente</h2>
            <p class="gallery-intro">
                Hier findest du sämtliche Uploads aus der Kampagne. Die Dateien werden im Originalnamen gespeichert.
            </p>

            <?php if (empty($submissions)): ?>
                <div class="empty-gallery">
                    <p>Noch keine Momente vorhanden. Sei die erste Person und teile deinen Blick auf Luzern!</p>
                </div>
            <?php else: ?>
                <div class="gallery-grid">
                    <?php foreach ($submissions as $submission): ?>
                        <?php $filename = basename($submission['filename']); ?>
                        <div class="gallery-item">
                            <div class="gallery-meta">
                                <h3><?php echo cleanOutput($submission['name']); ?></h3>
                                <p class="gallery-timestamp"><?php echo cleanOutput($submission['timestamp']); ?></p>
                                <p class="gallery-email"><?php echo cleanOutput($submission['email']); ?></p>
                            </div>
                            <div class="gallery-preview">
                                <img src="<?php echo htmlspecialchars($filename); ?>" alt="Upload">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Luzerner Tourismusbüro. Eine Initiative zur Förderung des Luzerner Tourismus.</p>
        </div>
    </footer>
</body>
</html>

