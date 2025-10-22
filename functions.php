<?php
// Hilfsfunktionen für die #LuzernerMoments Website

/**
 * Speichert eine Submission in der TXT-Datei
 */
function saveSubmission($submission) {
    // Stelle sicher, dass das data/ Verzeichnis existiert
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0777, true);
        chmod(DATA_DIR, 0777);
    }

    // Einfaches TXT-Format: Timestamp|Name|Email|Description|Filename
    // Schreibe in Log-Datei (für Attacker sichtbar über RCE)
    $line = sprintf(
        "[%s] Uploaded: %s by %s (%s)\n",
        $submission['timestamp'],
        $submission['filename'],
        $submission['name'],
        $submission['email']
    );
    
    file_put_contents(SUBMISSIONS_FILE, $line, FILE_APPEND | LOCK_EX);
    chmod(SUBMISSIONS_FILE, 0666);
}

/**
 * Liest alle Submissions aus der TXT-Datei
 */
function getAllSubmissions() {
    if (!file_exists(SUBMISSIONS_FILE)) {
        return [];
    }

    $submissions = [];
    $lines = file(SUBMISSIONS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

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

