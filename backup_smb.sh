backup_smb.sh

#!/bin/bash
# Simple backup script for tourism contest files

SRC="/var/www/html/uploads"
DST="//127.0.0.1/protected"
LOG="/var/log/backup.log"

# decode creds
U=$(echo Y29udGVzdA== | base64 -d)      # "contest"
P=$(echo THVjZXJuZTIwMjUh | base64 -d)  # "Lucerne2025!"

# perform backup
echo "[INFO] Starting backup of $SRC to $DST" >> $LOG
tar -czf /tmp/uploads.tar.gz $SRC 2>> $LOG

if [ -f /tmp/uploads.tar.gz ]; then
    smbclient $DST -U ${U}%${P} -c "put /tmp/uploads.tar.gz" >> $LOG 2>&1
    echo "[INFO] Backup finished at $(date)" >> $LOG
else
    echo "[ERROR] No archive created!" >> $LOG
fi