<?php
// Konfigurationsdatei für #LuzernerMoments Website

// Pfade
define('UPLOAD_DIR', 'uploads/');
define('DATA_DIR', 'data/');
define('SUBMISSIONS_FILE', DATA_DIR . 'uploaded_files.txt');

// Upload-Einstellungen
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Zeitzone
date_default_timezone_set('Europe/Zurich');

// Error Reporting (für Entwicklung)
// In Produktion sollte dies ausgeschaltet sein
error_reporting(E_ALL);
ini_set('display_errors', 1);

