<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

// Debug endpoint - should be removed in production but often forgotten
// Allows directory listing for "debugging" purposes

if (!isset($_GET['dir'])) {
    http_response_code(400);
    echo "Missing dir parameter";
    exit;
}

$dir = $_GET['dir'];
$basePath = __DIR__;

// Basic path traversal protection (but not perfect)
if (strpos($dir, '..') !== false) {
    http_response_code(403);
    echo "Path traversal not allowed";
    exit;
}

$fullPath = $basePath . DIRECTORY_SEPARATOR . $dir;

if (!is_dir($fullPath)) {
    http_response_code(404);
    echo "Directory not found";
    exit;
}

// List directory contents
echo "<h2>Directory listing: " . htmlspecialchars($dir) . "</h2>\n";
echo "<pre>\n";

$items = scandir($fullPath);
foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;
    
    $itemPath = $fullPath . DIRECTORY_SEPARATOR . $item;
    $relativePath = $dir . '/' . $item;
    
    if (is_dir($itemPath)) {
        echo "[DIR]  <a href='?dir=" . urlencode($relativePath) . "'>" . htmlspecialchars($item) . "/</a>\n";
    } else {
        $size = filesize($itemPath);
        $modified = date('Y-m-d H:i:s', filemtime($itemPath));
        echo "[FILE] <a href='" . htmlspecialchars($relativePath) . "'>" . htmlspecialchars($item) . "</a> ({$size} bytes, {$modified})\n";
    }
}

echo "</pre>\n";
?>
