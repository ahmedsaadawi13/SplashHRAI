# SplashHRAI - Deployment Guide

This guide covers deploying SplashHRAI to production environments.

## Server Requirements

### Minimum Specifications
- **CPU:** 2 cores
- **RAM:** 2 GB
- **Storage:** 20 GB SSD
- **OS:** Ubuntu 20.04 LTS or CentOS 8

### Recommended Specifications
- **CPU:** 4 cores
- **RAM:** 4 GB
- **Storage:** 50 GB SSD
- **OS:** Ubuntu 22.04 LTS

## Software Requirements

### PHP 7.0+
```bash
sudo apt update
sudo apt install php php-cli php-fpm php-mysql php-curl php-mbstring php-xml php-zip
```

### MySQL 5.7+
```bash
sudo apt install mysql-server
sudo mysql_secure_installation
```

### Web Server (Apache or Nginx)

**Apache:**
```bash
sudo apt install apache2
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**Nginx:**
```bash
sudo apt install nginx
sudo systemctl start nginx
```

## Deployment Steps

### 1. Upload Files

```bash
# Via Git
git clone <your-repo-url> /var/www/splashhr
cd /var/www/splashhr

# Or upload via SFTP/SCP
scp -r SplashHRAI/ user@server:/var/www/splashhr
```

### 2. Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/splashhr
sudo chmod -R 755 /var/www/splashhr
sudo chmod -R 775 /var/www/splashhr/storage
sudo chmod -R 775 /var/www/splashhr/storage/uploads
sudo chmod -R 775 /var/www/splashhr/storage/logs
sudo chmod -R 775 /var/www/splashhr/storage/cache
```

### 3. Configure Environment

```bash
cp .env.example .env
nano .env
```

Set production values:
```
APP_ENV=production
APP_URL=https://yourdomain.com

DB_HOST=localhost
DB_NAME=splashhr_ai
DB_USER=splashhr_user
DB_PASS=strong_password_here

AI_API_KEY=your_production_api_key
```

### 4. Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE splashhr_ai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'splashhr_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON splashhr_ai.* TO 'splashhr_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Import Database

```bash
mysql -u splashhr_user -p splashhr_ai < /var/www/splashhr/database.sql
```

### 6. Configure Web Server

#### Apache Virtual Host

Create `/etc/apache2/sites-available/splashhr.conf`:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/splashhr/public

    <Directory /var/www/splashhr/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <Directory /var/www/splashhr/storage>
        Require all denied
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/splashhr_error.log
    CustomLog ${APACHE_LOG_DIR}/splashhr_access.log combined
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite splashhr
sudo systemctl reload apache2
```

#### Nginx Configuration

Create `/etc/nginx/sites-available/splashhr`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/splashhr/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location /storage {
        deny all;
        return 403;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/splashhr /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 7. SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-apache  # For Apache
# OR
sudo apt install certbot python3-certbot-nginx   # For Nginx

# Apache
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

## PHP Configuration

Edit `/etc/php/7.4/fpm/php.ini` or `/etc/php/7.4/apache2/php.ini`:

```ini
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 300
memory_limit = 256M
session.gc_maxlifetime = 7200

# Enable OpCache for performance
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

Restart PHP-FPM:
```bash
sudo systemctl restart php7.4-fpm  # For Nginx
# OR
sudo systemctl restart apache2     # For Apache
```

## MySQL Optimization

Edit `/etc/mysql/mysql.conf.d/mysqld.cnf`:

```ini
[mysqld]
max_connections = 200
innodb_buffer_pool_size = 512M
innodb_log_file_size = 128M
query_cache_size = 64M
query_cache_type = 1
```

Restart MySQL:
```bash
sudo systemctl restart mysql
```

## Firewall Configuration

```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable
```

## Cron Jobs

Edit crontab:
```bash
sudo crontab -e
```

Add scheduled tasks:
```cron
# Daily attendance summary at midnight
0 0 * * * cd /var/www/splashhr && php scripts/attendance_daily.php >> storage/logs/cron.log 2>&1

# Monthly payroll reminder on 1st at 9 AM
0 9 1 * * cd /var/www/splashhr && php scripts/payroll_reminder.php >> storage/logs/cron.log 2>&1

# Clean old logs weekly
0 3 * * 0 find /var/www/splashhr/storage/logs -name "*.log" -mtime +30 -delete

# Backup database daily at 2 AM
0 2 * * * mysqldump -u splashhr_user -p'password' splashhr_ai | gzip > /var/backups/splashhr_$(date +\%Y\%m\%d).sql.gz

# Clean old backups (keep 30 days)
0 4 * * * find /var/backups -name "splashhr_*.sql.gz" -mtime +30 -delete
```

## Monitoring & Logging

### Log Rotation

Create `/etc/logrotate.d/splashhr`:

```
/var/www/splashhr/storage/logs/*.log {
    daily
    rotate 30
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
    sharedscripts
}
```

### Monitor Application

Install monitoring tools:
```bash
# Install htop for system monitoring
sudo apt install htop

# Install mytop for MySQL monitoring
sudo apt install mytop

# Check application logs
tail -f /var/www/splashhr/storage/logs/app.log
tail -f /var/www/splashhr/storage/logs/security.log
```

## Performance Optimization

### Enable Gzip Compression (Nginx)

Add to nginx config:
```nginx
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;
```

### Enable Gzip Compression (Apache)

```bash
sudo a2enmod deflate
```

Add to `.htaccess`:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>
```

### Browser Caching

Add to nginx config:
```nginx
location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

## Security Hardening

### 1. Hide PHP Version

In `php.ini`:
```ini
expose_php = Off
```

### 2. Disable Directory Listing

Apache `.htaccess`:
```apache
Options -Indexes
```

Nginx config:
```nginx
autoindex off;
```

### 3. Protect Sensitive Files

Create `/var/www/splashhr/.htaccess`:
```apache
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

<FilesMatch "\.(sql|log|env)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### 4. Enable Security Headers

Nginx:
```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
```

Apache `.htaccess`:
```apache
Header set X-Frame-Options "SAMEORIGIN"
Header set X-Content-Type-Options "nosniff"
Header set X-XSS-Protection "1; mode=block"
```

### 5. Fail2Ban (Brute Force Protection)

Install:
```bash
sudo apt install fail2ban
```

Create `/etc/fail2ban/jail.local`:
```ini
[splashhr]
enabled = true
filter = splashhr
logpath = /var/www/splashhr/storage/logs/security.log
maxretry = 5
bantime = 3600
```

Create `/etc/fail2ban/filter.d/splashhr.conf`:
```ini
[Definition]
failregex = ^.*Failed login attempt for:.*$
ignoreregex =
```

Restart fail2ban:
```bash
sudo systemctl restart fail2ban
```

## Backup Strategy

### Automated Backup Script

Create `/root/backup_splashhr.sh`:

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/splashhr"
DATE=$(date +%Y%m%d_%H%M%S)
DB_USER="splashhr_user"
DB_PASS="your_password"
DB_NAME="splashhr_ai"

mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup uploads
tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz -C /var/www/splashhr/storage uploads/

# Remove backups older than 30 days
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completed: $DATE"
```

Make executable:
```bash
chmod +x /root/backup_splashhr.sh
```

Add to cron:
```bash
0 2 * * * /root/backup_splashhr.sh >> /var/log/splashhr_backup.log 2>&1
```

## Troubleshooting

### Check Error Logs

```bash
# Application logs
tail -f /var/www/splashhr/storage/logs/app.log

# Apache logs
tail -f /var/log/apache2/splashhr_error.log

# Nginx logs
tail -f /var/log/nginx/error.log

# PHP-FPM logs
tail -f /var/log/php7.4-fpm.log
```

### Common Issues

**500 Internal Server Error**
- Check file permissions
- Review Apache/Nginx error logs
- Verify PHP syntax: `php -l /var/www/splashhr/public/index.php`

**Database Connection Error**
- Verify MySQL is running: `sudo systemctl status mysql`
- Check credentials in `.env`
- Test connection: `mysql -u splashhr_user -p -h localhost splashhr_ai`

**File Upload Fails**
- Check `upload_max_filesize` in `php.ini`
- Verify storage directory permissions
- Review PHP error log

**Session Issues**
- Check session directory permissions: `ls -la /var/lib/php/sessions`
- Verify `session.save_path` in `php.ini`

## Health Checks

Create a health check endpoint at `/var/www/splashhr/public/health.php`:

```php
<?php
header('Content-Type: application/json');

$health = array(
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'checks' => array()
);

// Check database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=splashhr_ai', 'splashhr_user', 'password');
    $health['checks']['database'] = 'OK';
} catch (Exception $e) {
    $health['checks']['database'] = 'FAILED';
    $health['status'] = 'unhealthy';
}

// Check storage writable
$health['checks']['storage'] = is_writable(__DIR__ . '/../storage') ? 'OK' : 'FAILED';

// Check uploads directory
$health['checks']['uploads'] = is_writable(__DIR__ . '/../storage/uploads') ? 'OK' : 'FAILED';

http_response_code($health['status'] === 'healthy' ? 200 : 503);
echo json_encode($health, JSON_PRETTY_PRINT);
```

## Scaling Considerations

### Load Balancing

Use multiple application servers behind a load balancer (HAProxy, Nginx):

```nginx
upstream splashhr_backend {
    server 10.0.1.10:80;
    server 10.0.1.11:80;
    server 10.0.1.12:80;
}

server {
    listen 80;
    server_name yourdomain.com;

    location / {
        proxy_pass http://splashhr_backend;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

### Database Replication

Set up MySQL master-slave replication for read scaling.

### CDN Integration

Use CloudFlare or AWS CloudFront for static assets.

### Session Management

For multi-server setups, use Redis or database sessions:

In `php.ini`:
```ini
session.save_handler = redis
session.save_path = "tcp://127.0.0.1:6379"
```

## Maintenance Mode

Create `/var/www/splashhr/public/maintenance.php`:

```php
<!DOCTYPE html>
<html>
<head>
    <title>Maintenance - SplashHRAI</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 50px; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <h1>System Maintenance</h1>
    <p>We're performing scheduled maintenance. We'll be back shortly.</p>
</body>
</html>
```

Enable maintenance mode in `.htaccess`:
```apache
# Maintenance mode
RewriteCond %{REQUEST_URI} !^/maintenance\.php$
RewriteRule ^(.*)$ /maintenance.php [R=503,L]
```

---

**For support or questions, refer to the main README.md**
