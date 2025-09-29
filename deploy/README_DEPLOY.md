# Ubuntu Deployment

Run as root on the Ubuntu VM:

```bash
apt-get update -y && apt-get install -y rsync
bash deploy/ubuntu_deploy.sh
```

What it does:
- Installs Nginx, PHP-FPM and required PHP extensions
- Syncs this project to `/var/www/luzerner-moments`
- Sets ownership and permissions for `www-data`
- Configures an Nginx server on port 80
- Adjusts PHP upload limits
- Enables services on boot and opens port 80 (ufw if available)



