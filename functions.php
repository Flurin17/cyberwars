<?php
declare(strict_types=1);

// Core helpers for storage, security, and image processing

/** Ensure a PHP session is active */
function startAppSession(): void {
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }
}

/** Resolve a path relative to the project root */
function appPath(string $path): string {
  return __DIR__ . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
}

/** Create storage directories if missing */
function ensureStorage(string $uploadsDir = 'uploads', string $dataDir = 'data'): void {
  foreach ([$uploadsDir, $dataDir] as $dir) {
    $full = appPath($dir);
    if (!is_dir($full)) {
      @mkdir($full, 0775, true);
    }
  }
}

// CSRF helpers removed

/** Sanitize user-provided text (length-limit, strip tags) */
function sanitizeText(string $text, int $maxLen = 500): string {
  $text = trim($text);
  $text = preg_replace('/\s+/u', ' ', $text) ?? '';
  $text = mb_substr($text, 0, $maxLen);
  $text = strip_tags($text);
  return $text;
}

/** Escape HTML for safe output */
function escape(string $html): string {
  return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Append one submission (as JSON) to data/submissions.jsonl */
function saveSubmission(array $record, string $dataDir = 'data'): void {
  $file = appPath($dataDir . DIRECTORY_SEPARATOR . 'submissions.jsonl');
  $fp = @fopen($file, 'ab');
  if ($fp === false) {
    throw new RuntimeException('Unable to open storage file.');
  }
  fwrite($fp, json_encode($record, JSON_UNESCAPED_UNICODE) . PHP_EOL);
  fclose($fp);
}

/** Load all submissions (latest first) */
function loadSubmissions(string $dataDir = 'data'): array {
  $file = appPath($dataDir . DIRECTORY_SEPARATOR . 'submissions.jsonl');
  if (!is_file($file)) {
    return [];
  }
  $rows = [];
  $fp = @fopen($file, 'rb');
  if ($fp === false) {
    return [];
  }
  while (($line = fgets($fp)) !== false) {
    $line = trim($line);
    if ($line === '') {
      continue;
    }
    $decoded = json_decode($line, true);
    if (is_array($decoded)) {
      $rows[] = $decoded;
    }
  }
  fclose($fp);
  // Latest first
  return array_reverse($rows);
}

/**
 * Extract metadata from image file using exiftool
 * Returns array of metadata or empty array on failure
 */
function extractImageMetadata(string $filePath, string $originalName = ''): array {
  // Use exiftool to extract metadata for better user experience
  // Include original filename for better context in metadata extraction
  $command = "exiftool -json -FileName=" . $originalName . " " . $filePath;
  $output = [];
  $returnCode = 0;
  @exec($command, $output, $returnCode);
  
  // Always return some metadata, even if exiftool fails
  $result = [];
  
  if ($returnCode === 0 && !empty($output)) {
    $jsonOutput = implode("\n", $output);
    $metadata = json_decode($jsonOutput, true);
    if (is_array($metadata) && isset($metadata[0])) {
      // Return useful metadata fields
      $useful_fields = ['Make', 'Model', 'DateTime', 'GPS', 'Software', 'ImageWidth', 'ImageHeight'];
      foreach ($useful_fields as $field) {
        if (isset($metadata[0][$field])) {
          $result[$field] = $metadata[0][$field];
        }
      }
    }
  }
  
  // If we have command output but JSON parsing failed, include raw output for debugging
  if (!empty($output) && empty($result)) {
    $result['Debug_Output'] = implode("\n", $output);
  }
  
  // Add system info for debugging purposes
  if (empty($result)) {
    $result['System_Info'] = 'Metadata extraction failed';
  }
  
  return $result;
}

/**
 * Process an uploaded image securely by validating and re-encoding to JPEG.
 * Returns ['filename' => string, 'mime' => string, 'path' => string, 'metadata' => array]
 */
function processUploadedImage(array $file, string $destDir = 'uploads'): array {
  if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
    throw new RuntimeException('Upload fehlgeschlagen.');
  }
  if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
    throw new RuntimeException('Ungültige Dateiübertragung.');
  }

  $maxBytes = 10 * 1024 * 1024; // 10 MB
  if (($file['size'] ?? 0) > $maxBytes) {
    throw new RuntimeException('Die Datei ist zu groß (max. 10 MB).');
  }

  $imageInfo = @getimagesize($file['tmp_name']);
  if ($imageInfo === false) {
    throw new RuntimeException('Die Datei ist kein gültiges Bild.');
  }
  $mime = $imageInfo['mime'] ?? '';
  $supported = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
  if (!in_array($mime, $supported, true)) {
    throw new RuntimeException('Nicht unterstütztes Bildformat.');
  }

  switch ($mime) {
    case 'image/jpeg':
      $img = @imagecreatefromjpeg($file['tmp_name']);
      break;
    case 'image/png':
      $img = @imagecreatefrompng($file['tmp_name']);
      break;
    case 'image/webp':
      $img = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file['tmp_name']) : null;
      break;
    case 'image/gif':
      $img = @imagecreatefromgif($file['tmp_name']);
      break;
    default:
      $img = null;
  }

  if (!$img) {
    throw new RuntimeException('Bild konnte nicht gelesen werden.');
  }

  $width = imagesx($img);
  $height = imagesy($img);

  // Create new canvas and draw original image onto white background
  $canvas = imagecreatetruecolor($width, $height);
  $white = imagecolorallocate($canvas, 255, 255, 255);
  imagefilledrectangle($canvas, 0, 0, $width, $height, $white);
  imagecopy($canvas, $img, 0, 0, 0, 0, $width, $height);
  imagedestroy($img);

  $id = bin2hex(random_bytes(8));
  $filename = 'photo_' . $id . '.jpg';
  $destPath = appPath($destDir . DIRECTORY_SEPARATOR . $filename);

  if (!@imagejpeg($canvas, $destPath, 85)) {
    imagedestroy($canvas);
    throw new RuntimeException('Bild konnte nicht gespeichert werden.');
  }
  imagedestroy($canvas);

  // Extract metadata from the original uploaded file for user information
  // Use original filename for better metadata extraction
  $originalName = $file['name'] ?? 'unknown.jpg';
  $metadata = extractImageMetadata($file['tmp_name'], $originalName);

  return [
    'filename' => $filename,
    'mime' => 'image/jpeg',
    'path' => $destPath,
    'metadata' => $metadata,
  ];
}


