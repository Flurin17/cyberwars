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

echo "[6/9] Erstelle Verzeichnisstruktur..."
# Erstelle uploads/ Verzeichnis
mkdir -p /var/www/html/uploads
cp "$TEMP_DIR/uploads/index.php" /var/www/html/uploads/

# Erstelle data/ Verzeichnis
mkdir -p /var/www/html/data

# Setze Berechtigungen
chown -R www-data:www-data /var/www/html
chmod 755 /var/www/html
chmod 755 /var/www/html/uploads
chmod 755 /var/www/html/data
chmod 755 /var/www/html/assets
chmod 644 /var/www/html/*.php
chmod 644 /var/www/html/.htaccess
chmod 644 /var/www/html/robots.txt
chmod 644 /var/www/html/assets/style.css

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
echo "       Flag wurde in /home/webflag/flag.txt gespeichert"

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
echo "Hinweise für CTF-Teilnehmer:"
echo "  - Starte mit Web-Enumeration (dirb, gobuster, etc.)"
echo "  - Teste die Upload-Funktionalität"
echo "  - Achte auf die Dateivalidierung"
echo "  - Denke an Double-Extensions (.php.jpg)"
echo ""
echo "================================================"

