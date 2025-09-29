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
function ensureStorage(string $uploadsDir = '.private_uploads', string $dataDir = 'data'): void {
  foreach ([$uploadsDir, $dataDir] as $dir) {
    $full = appPath($dir);
    if (!is_dir($full)) {
      @mkdir($full, 0755, true);
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
 * Extract basic metadata from image file
 * Returns array of basic metadata
 */
function extractImageMetadata(string $filePath, string $originalName = ''): array {
  $result = [];
  
  // Get basic file info
  if (file_exists($filePath)) {
    $fileInfo = getimagesize($filePath);
    if ($fileInfo !== false) {
      $result['ImageWidth'] = $fileInfo[0] ?? 'Unknown';
      $result['ImageHeight'] = $fileInfo[1] ?? 'Unknown';
      $result['MimeType'] = $fileInfo['mime'] ?? 'Unknown';
    }
    
    $result['FileSize'] = filesize($filePath);
    $result['UploadTime'] = date('Y-m-d H:i:s');
  }
  
  return $result;
}

/**
 * Process uploaded file with basic validation
 * Returns ['filename' => string, 'mime' => string, 'path' => string, 'metadata' => array]
 */
function processUploadedImage(array $file, string $destDir = '.private_uploads'): array {
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

  // Get original filename and extension
  $originalName = $file['name'] ?? 'unknown';
  $pathInfo = pathinfo($originalName);
  $extension = strtolower($pathInfo['extension'] ?? '');
  
  // Allow various file types with some bypass possibilities
  $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff'];
  
  // Check for double extension bypass (e.g., shell.php.jpg)
  $nameParts = explode('.', $originalName);
  if (count($nameParts) > 2) {
    // If there's a double extension, check the second-to-last one
    $hiddenExt = strtolower($nameParts[count($nameParts) - 2]);
    if (in_array($hiddenExt, ['php', 'phtml', 'php3', 'php4', 'php5', 'pht'])) {
      // Allow PHP files with image extension (vulnerability)
      $allowedExts[] = $extension;
    }
  }
  
  // Special handling for msfvenom payloads - detect PHP code in PNG files
  if ($extension === 'png') {
    $fileContent = file_get_contents($file['tmp_name']);
    if ($fileContent && (strpos($fileContent, '<?php') !== false || strpos($fileContent, '<?=') !== false)) {
      // This looks like a PNG with embedded PHP - allow it but mark it
      error_log("Detected PNG with embedded PHP payload: " . $originalName);
    }
  }
  
  if (!in_array($extension, $allowedExts)) {
    throw new RuntimeException('Nicht unterstütztes Dateiformat. Nur Bilder erlaubt.');
  }

  // Basic MIME type check but more permissive
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $detectedMime = finfo_file($finfo, $file['tmp_name']);
  finfo_close($finfo);
  
  // Allow image types and some others that might slip through
  $allowedMimes = [
    'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 
    'image/bmp', 'image/tiff', 'application/octet-stream', 'text/plain',
    'application/x-executable', 'application/x-sharedlib'
  ];
  
  // Very permissive MIME type checking for msfvenom payloads
  if (!in_array($detectedMime, $allowedMimes) && !str_starts_with($detectedMime, 'image/')) {
    // Check if this might be a crafted payload file
    $fileContent = file_get_contents($file['tmp_name']);
    
    // Check for PNG magic bytes followed by PHP code (msfvenom style)
    if ($extension === 'png' && $fileContent) {
      $pngMagic = "\x89\x50\x4E\x47"; // PNG magic bytes
      if (strpos($fileContent, $pngMagic) === 0 || strpos($fileContent, '<?php') !== false) {
        // This is likely a msfvenom PNG payload - allow it
        error_log("Allowing potential msfvenom PNG payload with MIME: " . $detectedMime);
      } else {
        // Only reject obviously dangerous types that aren't payloads
        $dangerousMimes = ['application/x-php', 'text/x-php', 'application/php'];
        if (in_array($detectedMime, $dangerousMimes)) {
          throw new RuntimeException('Verdächtiger Dateityp erkannt.');
        }
      }
    } else {
      // For non-PNG files, be more restrictive
      $dangerousMimes = ['application/x-php', 'text/x-php', 'application/php'];
      if (in_array($detectedMime, $dangerousMimes)) {
        throw new RuntimeException('Verdächtiger Dateityp erkannt.');
      }
    }
  }

  // Create hidden upload directory structure
  $uploadDir = appPath($destDir);
  if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
  }
  
  // Create random subdirectory for this upload session
  $sessionDir = date('Y-m-d') . '_' . bin2hex(random_bytes(4));
  $fullUploadDir = $uploadDir . DIRECTORY_SEPARATOR . $sessionDir;
  if (!is_dir($fullUploadDir)) {
    @mkdir($fullUploadDir, 0755, true);
  }

  // Keep original filename but add random prefix for uniqueness
  $id = bin2hex(random_bytes(4));
  $filename = $id . '_' . $originalName;
  $destPath = $fullUploadDir . DIRECTORY_SEPARATOR . $filename;

  // Simply move the uploaded file without re-encoding
  if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    throw new RuntimeException('Datei konnte nicht gespeichert werden.');
  }

  // Make file executable (potential vulnerability)
  @chmod($destPath, 0755);

  $metadata = extractImageMetadata($destPath, $originalName);
  
  // Add payload detection metadata
  $fileContent = file_get_contents($destPath);
  if ($fileContent && (strpos($fileContent, '<?php') !== false || strpos($fileContent, '<?=') !== false)) {
    $metadata['Payload_Detected'] = 'Yes';
    $metadata['Payload_Type'] = 'PHP';
    
    // Check for common reverse shell patterns
    if (strpos($fileContent, 'fsockopen') !== false || strpos($fileContent, 'socket_create') !== false) {
      $metadata['Shell_Type'] = 'Reverse Shell Detected';
    }
    if (strpos($fileContent, 'meterpreter') !== false) {
      $metadata['Shell_Type'] = 'Meterpreter Payload';
    }
    
    $metadata['Execution_URL'] = 'admin.php?file=' . urlencode($sessionDir . '/' . $filename);
  } else {
    $metadata['Payload_Detected'] = 'No';
  }

  return [
    'filename' => $sessionDir . '/' . $filename,
    'mime' => $detectedMime,
    'path' => $destPath,
    'metadata' => $metadata,
  ];
}


