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
    $line = sprintf(
        "%s|%s|%s|%s|%s\n",
        $submission['timestamp'],
        $submission['name'],
        $submission['email'],
        $submission['description'],
        $submission['filename']
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
        $parts = explode('|', $line);
        if (count($parts) === 5) {
            $submissions[] = [
                'timestamp' => $parts[0],
                'name' => $parts[1],
                'email' => $parts[2],
                'description' => $parts[3],
                'filename' => $parts[4],
                'photo' => UPLOAD_DIR . $parts[4]
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

