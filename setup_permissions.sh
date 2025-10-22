#!/bin/bash
# Setup script für Flag 1 - LuzernerMoments CTF
# Dieser Script richtet die Verzeichnisse und Berechtigungen für www-data ein

echo "[+] Setting up directories for www-data..."

# Erstelle die notwendigen Verzeichnisse
mkdir -p /var/www/html/uploads
mkdir -p /var/www/html/data

echo "[+] Setting permissions for uploads directory..."
# uploads/ Verzeichnis - www-data muss hier schreiben können
chown -R www-data:www-data /var/www/html/uploads
chmod 755 /var/www/html/uploads

echo "[+] Setting permissions for data directory..."
# data/ Verzeichnis - www-data muss hier schreiben können
chown -R www-data:www-data /var/www/html/data
chmod 755 /var/www/html/data

echo "[+] Creating uploaded_files.txt if it doesn't exist..."
# Erstelle die TXT-Datei für uploads
touch /var/www/html/data/uploaded_files.txt
chown www-data:www-data /var/www/html/data/uploaded_files.txt
chmod 666 /var/www/html/data/uploaded_files.txt

echo "[+] Setting permissions for web files..."
# Alle PHP-Dateien sollten www-data gehören
chown www-data:www-data /var/www/html/*.php
chown -R www-data:www-data /var/www/html/assets

echo "[+] Setting up index.php in uploads to prevent directory listing..."
# Erstelle index.php in uploads um Directory Listing zu verhindern
cat > /var/www/html/uploads/index.php << 'EOF'
<?php
// Verhindere Directory Listing
header('HTTP/1.0 403 Forbidden');
echo '403 Forbidden';
exit;
EOF

chown www-data:www-data /var/www/html/uploads/index.php
chmod 644 /var/www/html/uploads/index.php

echo "[+] Done! Directories and permissions are configured for www-data."
echo ""
echo "Upload directory: /var/www/html/uploads/"
echo "Data file: /var/www/html/data/uploaded_files.txt"
echo ""
echo "[!] VULNERABILITY: uploads/ directory allows PHP execution!"
echo "[!] This is intentional for Flag 1 - msfvenom exploit"

