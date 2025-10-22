<?php
require_once 'config.php';
require_once 'functions.php';

// Prüfe ob das Formular abgeschickt wurde
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Hole Form-Daten
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$description = $_POST['description'] ?? '';

// Validiere Textfelder
if (empty($name) || empty($email) || empty($description)) {
    die('Bitte fülle alle Felder aus.');
}

// Prüfe ob eine Datei hochgeladen wurde
if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    die('Fehler beim Hochladen der Datei.');
}

$file = $_FILES['photo'];
$originalName = $file['name'];
$tmpPath = $file['tmp_name'];
$fileSize = $file['size'];

// Maximale Dateigröße: 5MB
if ($fileSize > MAX_FILE_SIZE) {
    die('Die Datei ist zu groß. Maximale Größe: 5MB.');
}

// SCHWACHSTELLE: Nur Dateiendungs-Check, keine Magic-Byte-Prüfung!
// Dies ermöglicht Upload von PHP-Dateien mit Double-Extension (z.B. shell.php.jpg)
$fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($fileExtension, $allowedExtensions)) {
    die('Ungültiges Dateiformat. Erlaubt sind: JPG, JPEG, PNG, GIF');
}

// VULNERABLE: Speichere Datei mit Original-Namen!
// Dies erleichtert das Auffinden hochgeladener Dateien
$randomName = $originalName;
$uploadPath = UPLOAD_DIR . $randomName;

// Verschiebe die Datei ins Upload-Verzeichnis
if (!move_uploaded_file($tmpPath, $uploadPath)) {
    die('Fehler beim Speichern der Datei.');
}

// Setze Berechtigungen damit www-data darauf zugreifen kann
chmod($uploadPath, 0644);

// Speichere die Submission in der TXT-Datei
$submission = [
    'timestamp' => date('Y-m-d H:i:s'),
    'name' => $name,
    'email' => $email,
    'description' => $description,
    'photo' => $uploadPath,
    'filename' => $randomName
];

saveSubmission($submission);

// Weiterleitung zur Bestätigungsseite
header('Location: thanks.php?photo=' . urlencode($randomName));
exit;

