<?php
// Simple file server for uploaded images
// This serves files from the .private_uploads directory

$requestedFile = $_GET['file'] ?? '';

if (empty($requestedFile)) {
    http_response_code(404);
    exit;
}

// Basic security check
if (strpos($requestedFile, '..') !== false) {
    http_response_code(403);
    exit;
}

$privatePath = __DIR__ . '/../.private_uploads/' . $requestedFile;

if (!file_exists($privatePath)) {
    http_response_code(404);
    exit;
}

// Determine content type
$pathInfo = pathinfo($privatePath);
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
    case 'webp':
        header('Content-Type: image/webp');
        break;
    default:
        header('Content-Type: application/octet-stream');
}

readfile($privatePath);
?>
