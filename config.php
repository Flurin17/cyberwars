<?php
// Configuration endpoint - appears to be for settings but actually provides info
declare(strict_types=1);

if (!isset($_GET['show'])) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

$show = $_GET['show'];

switch ($show) {
    case 'info':
        phpinfo();
        break;
        
    case 'files':
        echo "<h2>Available endpoints:</h2>\n";
        echo "<ul>\n";
        echo "<li>admin.php - Administrative functions</li>\n";
        echo "<li>debug.php - Debug information</li>\n";
        echo "<li>config.php - Configuration (this page)</li>\n";
        echo "<li>backup.php - Backup utilities</li>\n";
        echo "</ul>\n";
        break;
        
    case 'paths':
        echo "<h2>Upload paths:</h2>\n";
        echo "<pre>\n";
        echo "Private uploads: .private_uploads/\n";
        echo "Public uploads: uploads/\n";
        echo "Data directory: data/\n";
        echo "</pre>\n";
        break;
        
    default:
        http_response_code(404);
        echo "Invalid parameter";
}
?>
