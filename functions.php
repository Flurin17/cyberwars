<?php
// Hilfsfunktionen für die #LuzernerMoments Website

/**
 * Speichert eine Submission in der TXT-Datei
 */
function saveSubmission($submission) {
    $dataDirPath = __DIR__ . '/' . DATA_DIR;
    if (!is_dir($dataDirPath)) {
        mkdir($dataDirPath, 0777, true);
        chmod($dataDirPath, 0777);
    }

    $logFile = __DIR__ . '/' . SUBMISSIONS_FILE;

    $line = sprintf(
        "[%s] Uploaded: %s by %s (%s)\n",
        $submission['timestamp'],
        $submission['filename'],
        $submission['name'],
        $submission['email']
    );
    
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    chmod($logFile, 0666);
}

/**
 * Liest alle Submissions aus der TXT-Datei
 */
function getAllSubmissions() {
    $logFile = __DIR__ . '/' . SUBMISSIONS_FILE;

    if (!file_exists($logFile)) {
        return [];
    }

    $submissions = [];
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Parse format: [timestamp] Uploaded: filename by name (email)
        if (preg_match('/\[(.*?)\] Uploaded: (.*?) by (.*?) \((.*?)\)/', $line, $matches)) {
            $submissions[] = [
                'timestamp' => $matches[1],
                'filename' => $matches[2],
                'name' => $matches[3],
                'email' => $matches[4],
                'description' => '',
                'photo' => UPLOAD_DIR . $matches[2]
            ];
        }
    }

    // Neueste zuerst
    return array_reverse($submissions);
}

/**
 * Holt die letzten N Submissions
 */
function getRecentSubmissions($limit = 6) {
    $all = getAllSubmissions();
    return array_slice($all, 0, $limit);
}

/**
 * Bereinigt einen String für HTML-Ausgabe
 */
function cleanOutput($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
