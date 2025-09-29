<?php
// Backup utility endpoint - another common endpoint name
declare(strict_types=1);

if (!isset($_GET['action'])) {
    http_response_code(404);
    echo "Not Found";
    exit;
}

$action = $_GET['action'];

switch ($action) {
    case 'list':
        echo "<h2>Available backup operations:</h2>\n";
        echo "<ul>\n";
        echo "<li><a href='?action=files'>List files</a></li>\n";
        echo "<li><a href='?action=db'>Database info</a></li>\n";
        echo "<li><a href='?action=logs'>View logs</a></li>\n";
        echo "</ul>\n";
        break;
        
    case 'files':
        echo "<h2>File listing:</h2>\n";
        echo "<p>For file execution, use: <code>admin.php?file=&lt;path&gt;</code></p>\n";
        echo "<pre>\n";
        
        $uploadDir = __DIR__ . '/.private_uploads';
        if (is_dir($uploadDir)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploadDir));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $relativePath = str_replace($uploadDir . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $relativePath = str_replace('\\', '/', $relativePath);
                    echo $relativePath . "\n";
                }
            }
        }
        echo "</pre>\n";
        break;
        
    case 'db':
        echo "<h2>Database information:</h2>\n";
        echo "<p>Using file-based storage: data/submissions.jsonl</p>\n";
        $dataFile = __DIR__ . '/data/submissions.jsonl';
        if (file_exists($dataFile)) {
            echo "<p>Records: " . count(file($dataFile)) . "</p>\n";
        }
        break;
        
    case 'logs':
        echo "<h2>Recent log entries:</h2>\n";
        echo "<pre>\n";
        $logFile = __DIR__ . '/error.log';
        if (file_exists($logFile)) {
            $lines = file($logFile);
            $recent = array_slice($lines, -20);
            echo htmlspecialchars(implode('', $recent));
        } else {
            echo "No log file found\n";
        }
        echo "</pre>\n";
        break;
        
    default:
        http_response_code(404);
        echo "Invalid action";
}
?>
