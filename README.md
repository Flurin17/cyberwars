# #LuzernerMoments CTF - Flag 1: Initial Access

Willkommen zur **#LuzernerMoments** CTF Challenge! Dies ist Flag 1 des CTF "Luzerner Tourismusbüro".

## Übersicht

Dies ist eine absichtlich unsichere Webanwendung für ein Capture The Flag (CTF) Event. Die Website simuliert eine Tourismus-Kampagne der Stadt Luzern, bei der Benutzer Fotos hochladen können. Die Anwendung enthält eine **Remote Code Execution (RCE) Schwachstelle** in der Upload-Funktionalität.

⚠️ **WARNUNG:** Diese Anwendung ist ABSICHTLICH unsicher! Verwenden Sie sie NUR in einer isolierten CTF-Umgebung. NIEMALS in einer Produktionsumgebung einsetzen!

## Szenario

Die Stadt Luzern hat die Aktion **#LuzernerMoments** gestartet, bei der Touristen und Einheimische ihre schönsten Fotos aus Luzern hochladen können. Aufgrund des Zeitdrucks wurde die Webanwendung schnell entwickelt, wobei Sicherheitsaspekte vernachlässigt wurden.

**Ziel:** Finde die Schwachstelle, erlange Zugriff auf das System und finde das erste Flag.

## Installation

### Voraussetzungen

- Ubuntu Server 24.04.3 LTS
- Root-Zugriff

### Schnellinstallation

```bash
# 1. Repository klonen
git clone https://github.com/Flurin17/cyberwars.git
cd cyberwars

# 2. Installations-Script ausführen
sudo bash install.sh
```

Das Script installiert automatisch:
- Apache2 und PHP 8.3
- Alle Website-Dateien
- System-User `webflag`
- Flag-Datei in `/home/webflag/flag.txt`

### Manuelle Installation

Falls du die Installation manuell durchführen möchtest:

```bash
# Apache und PHP installieren
sudo apt update
sudo apt install -y apache2 php libapache2-mod-php php-cli

# Website-Dateien kopieren
sudo cp *.php /var/www/html/
sudo cp -r assets /var/www/html/
sudo cp robots.txt .htaccess /var/www/html/

# Verzeichnisse erstellen
sudo mkdir -p /var/www/html/uploads
sudo mkdir -p /var/www/html/data
sudo cp uploads/index.php /var/www/html/uploads/

# Berechtigungen setzen
sudo chown -R www-data:www-data /var/www/html
sudo chmod 755 /var/www/html/uploads
sudo chmod 755 /var/www/html/data

# System-User erstellen
sudo useradd -r -m -s /usr/sbin/nologin webflag

# Flag erstellen
echo "flag{initial_access_luzernermoments_83723}" | sudo tee /home/webflag/flag.txt
sudo chown webflag:webflag /home/webflag/flag.txt
sudo chmod 444 /home/webflag/flag.txt

# Apache konfigurieren und starten
sudo a2enmod rewrite
sudo sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
sudo systemctl restart apache2
```

## Zugriff

Nach der Installation ist die Website erreichbar unter:
- `http://<SERVER-IP>`
- `http://10.0.2.10` (bei NAT-Netzwerk Konfiguration)

## Hinweise für CTF-Teilnehmer

- Starte mit Web-Enumeration (dirb, gobuster, nikto, etc.)
- Untersuche die `robots.txt` Datei
- Teste die Upload-Funktionalität
- Achte auf die Dateivalidierung
- Denke an verschiedene Bypass-Techniken

## Schwachstellen (Spoiler!)

<details>
<summary>Klicke hier um die Schwachstellen zu sehen (SPOILER!)</summary>

Die Anwendung enthält folgende Schwachstellen:

1. **Unzureichende Dateivalidierung**
   - Nur Extension-Check mit `pathinfo()`
   - Keine Magic-Byte-Prüfung
   - Keine MIME-Type-Validierung

2. **Uploads in ausführbares Verzeichnis**
   - Dateien werden in `/uploads/` gespeichert
   - PHP-Code wird vom Webserver ausgeführt

3. **Double-Extension Bypass**
   - Dateien wie `shell.php.jpg` werden akzeptiert
   - Apache führt die Datei als PHP aus

</details>

## Komponenten

- `index.php` - Hauptseite mit Upload-Formular
- `upload.php` - Upload-Handler (enthält die Schwachstelle)
- `thanks.php` - Bestätigungsseite
- `uploads/index.php` - Übersicht aller Uploads
- `config.php` - Konfiguration
- `functions.php` - Hilfsfunktionen
- `assets/style.css` - Stylesheet
- `robots.txt` - Robots-Datei (Hinweis!)
- `.htaccess` - Apache-Konfiguration

## Dokumentation

Die vollständige Dokumentation findest du in `CTF.md`, inklusive:
- Detaillierte Beschreibung der Schwachstellen
- Lösungs-Walkthrough
- Technische Details zur Implementierung

## Support

Dies ist ein CTF-Projekt. Bei Fragen oder Problemen:
- Überprüfe die `CTF.md` Dokumentation
- Stelle sicher, dass alle Voraussetzungen erfüllt sind
- Kontaktiere den CTF-Organisator

## Lizenz

Dieses Projekt wurde für Bildungszwecke im Rahmen eines CTF erstellt.

---

**Viel Erfolg bei der Challenge! 🏔️**

#LuzernerMoments | Luzerner Tourismusbüro CTF 2025

