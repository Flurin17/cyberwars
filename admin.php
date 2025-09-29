<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

// Payload execution endpoint - allows executing uploaded PHP payloads
// This is intentionally vulnerable for CTF/pentest purposes

if (!isset($_GET['file'])) {
    http_response_code(400);
    echo "Missing file parameter";
    exit;
}

$file = $_GET['file'];
$basePath = __DIR__ . DIRECTORY_SEPARATOR . '.private_uploads';

// Basic path traversal protection (but not perfect)
if (strpos($file, '..') !== false) {
    http_response_code(403);
    echo "Path traversal not allowed";
    exit;
}

$fullPath = $basePath . DIRECTORY_SEPARATOR . $file;

if (!file_exists($fullPath)) {
    http_response_code(404);
    echo "File not found: " . htmlspecialchars($file);
    exit;
}

// Check if file contains PHP code
$fileContent = file_get_contents($fullPath);
if (!$fileContent) {
    http_response_code(500);
    echo "Could not read file";
    exit;
}

// Look for PHP code in the file
if (strpos($fileContent, '<?php') !== false || strpos($fileContent, '<?=') !== false) {
    // Execute the PHP code found in the file
    // This is the vulnerability that allows reverse shells
    
    // Set headers to prevent output buffering
    header('Content-Type: text/plain');
    
    // Log the execution attempt
    error_log("Executing payload from file: " . $file);
    
    // Execute the file - this will run any PHP code including reverse shells
    include $fullPath;
    
} else {
    // If no PHP code detected, try to serve as image
    $pathInfo = pathinfo($fullPath);
    $extension = strtolower($pathInfo['extension'] ?? '');
    
    switch ($extension) {
        case 'png':
            header('Content-Type: image/png');
            break;
        case 'jpg':
        case 'jpeg':
            header('Content-Type: image/jpeg');
            break;
        case 'gif':
            header('Content-Type: image/gif');
            break;
        default:
            header('Content-Type: application/octet-stream');
    }
    
    readfile($fullPath);
}
?>
