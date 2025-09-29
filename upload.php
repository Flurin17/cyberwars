<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
startAppSession();
ensureStorage();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  header('Location: index.php');
  exit;
}

$title = sanitizeText((string)($_POST['title'] ?? ''));
$description = sanitizeText((string)($_POST['description'] ?? ''), 400);

if ($title === '' || $description === '') {
  http_response_code(400);
  echo '<!doctype html><meta charset="utf-8"><p>Bitte Titel und Beschreibung ausfüllen. <a href="index.php">Zurück</a></p>';
  exit;
}

if (!isset($_FILES['photo'])) {
  http_response_code(400);
  echo '<!doctype html><meta charset="utf-8"><p>Kein Foto hochgeladen. <a href="index.php">Zurück</a></p>';
  exit;
}

try {
  $image = processUploadedImage($_FILES['photo']);
} catch (Throwable $e) {
  http_response_code(400);
  $msg = htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  echo '<!doctype html><meta charset="utf-8"><p>Fehler: ' . $msg . '. <a href="index.php">Zurück</a></p>';
  exit;
}

$record = [
  'id' => bin2hex(random_bytes(6)),
  'title' => $title,
  'description' => $description,
  'image' => $image['filename'],
  'metadata' => $image['metadata'] ?? [],
  'created_at' => date('c'),
];

try {
  saveSubmission($record);
} catch (Throwable $e) {
  http_response_code(500);
  echo '<!doctype html><meta charset="utf-8"><p>Interner Fehler beim Speichern. <a href="index.php">Zurück</a></p>';
  exit;
}

header('Location: thanks.php?id=' . urlencode($record['id']));
exit;


