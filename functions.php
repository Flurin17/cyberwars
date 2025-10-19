<?php
// Hilfsfunktionen für die #LuzernerMoments Website

/**
 * Speichert eine Submission in der JSONL-Datei
 */
function saveSubmission($submission) {
    // Stelle sicher, dass das data/ Verzeichnis existiert
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }

    // Füge die Submission als neue Zeile hinzu (JSONL-Format)
    $jsonLine = json_encode($submission, JSON_UNESCAPED_UNICODE) . "\n";
    file_put_contents(SUBMISSIONS_FILE, $jsonLine, FILE_APPEND | LOCK_EX);
}

/**
 * Liest alle Submissions aus der JSONL-Datei
 */
function getAllSubmissions() {
    if (!file_exists(SUBMISSIONS_FILE)) {
        return [];
    }

    $submissions = [];
    $lines = file(SUBMISSIONS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $submission = json_decode($line, true);
        if ($submission) {
            $submissions[] = $submission;
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

