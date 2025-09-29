<?php
// Test endpoint - common in development environments
declare(strict_types=1);

echo "<h1>Test Environment</h1>\n";
echo "<p>This is a test environment for the photo upload system.</p>\n";

if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    echo "<h2>Debug Information:</h2>\n";
    echo "<ul>\n";
    echo "<li>PHP Version: " . PHP_VERSION . "</li>\n";
    echo "<li>Upload Max Filesize: " . ini_get('upload_max_filesize') . "</li>\n";
    echo "<li>Post Max Size: " . ini_get('post_max_size') . "</li>\n";
    echo "<li>Memory Limit: " . ini_get('memory_limit') . "</li>\n";
    echo "</ul>\n";
    
    echo "<h3>Available endpoints:</h3>\n";
    echo "<ul>\n";
    echo "<li><code>admin.php</code> - Administrative interface</li>\n";
    echo "<li><code>debug.php</code> - Debug directory listing</li>\n";
    echo "<li><code>config.php</code> - Configuration viewer</li>\n";
    echo "<li><code>backup.php</code> - Backup utilities</li>\n";
    echo "</ul>\n";
    
    echo "<h3>Usage example:</h3>\n";
    echo "<p>To execute uploaded payloads: <code>admin.php?file=2024-XX-XX_XXXX/XXXX_filename.png</code></p>\n";
}

if (isset($_GET['phpinfo'])) {
    phpinfo();
}
?>
