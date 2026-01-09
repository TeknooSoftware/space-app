# Installation Guide

## Overview

This guide provides step-by-step instructions for installing Space on your infrastructure. Before proceeding,
ensure all [system requirements](requirements.md) are met.

## Installation Methods

Space can be installed using several methods:

1. **Docker Compose** (recommended for development and testing)
2. **Manual Installation** (recommended for production)
3. **Kubernetes Deployment** (Enterprise Edition with Helm charts)

## Prerequisites

Before installation, ensure you have:

- All [system requirements](requirements.md) satisfied
- Root or sudo access to the server
- Network connectivity to required services
- SSL/TLS certificates (for production)

## Method 1: Docker Compose Installation (Development)

### 1.1. Clone the Repository

```bash
git clone https://github.com/TeknooSoftware/space-app.git
cd space-app
```

### 1.2. Configure Environment

Run the command and follow instructions to configure:

- Docker compose
- Database connection
- RabbitMQ connection
- Kubernetes cluster
- Email settings
- Security settings

```bash
./space.sh config
```

### 1.3. Build Docker Images

```bash
./space.sh build
```

This command builds all necessary Docker images including:

- PHP-FPM for web server
- PHP-CLI for workers
- PHP-Buildah for image building
- Apache HTTP server
- MongoDB
- RabbitMQ
- Mercure (optional)

### 1.4. Start Services

```bash
./space.sh start
```

This starts all containers defined in `compose.yml`.

### 1.5. Install Dependencies

```bash
./space.sh install
```

This command:

- Installs PHP dependencies via Composer
- Builds Symfony application
- Warms up caches

### 1.6 Create Administrator

```bash
./space.sh create-admin email=admin@example.com password=SecurePassword123
```

### 1.7 Access Space

Open your browser and navigate to:

- **Web UI**: http://localhost
- **RabbitMQ Management**: http://localhost:15672 (guest/guest)

The Docker Compose setup is now complete. For production deployment, use Method 2.

## Method 2: Manual Installation (Production)

### 2.1. Install System Dependencies

**Ubuntu/Debian:**

```bash
# Update package list
sudo apt-get update

# Install PHP and extensions
sudo apt-get install -y \
    php8.4-cli php8.4-fpm \
    php8.4-mongodb php8.4-curl php8.4-mbstring \
    php8.4-xml php8.4-zip php8.4-gd \
    php8.4-intl php8.4-bcmath

# Install system tools
sudo apt-get install -y \
    git curl wget unzip \
    nginx \
    buildah

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

**RHEL/Rocky Linux:**

```bash
# Enable EPEL and Remi repositories
sudo dnf install -y epel-release
sudo dnf install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm

# Enable PHP 8.4
sudo dnf module enable php:remi-8.4

# Install PHP and extensions
sudo dnf install -y \
    php-cli php-fpm \
    php-mongodb php-mbstring php-xml \
    php-gd php-intl php-bcmath php-sodium

# Install system tools
sudo dnf install -y \
    git curl wget unzip \
    nginx \
    buildah

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2.2. Install and Configure MongoDB

**Install MongoDB:**

```bash
# Ubuntu/Debian
sudo apt-get install gnupg curl
# Ajouter la clé GPG de MongoDB (méthode moderne)
curl -fsSL https://www.mongodb.org/static/pgp/server-7.0.asc | \
   sudo gpg --dearmor -o /usr/share/keyrings/mongodb-server-7.0.gpg
# Ajouter le dépôt MongoDB (Debian 12 Bookworm est compatible)
echo "deb [ arch=amd64,arm64 signed-by=/usr/share/keyrings/mongodb-server-7.0.gpg ] https://repo.mongodb.org/apt/debian bookworm/mongodb-org/7.0 main" | \
   sudo tee /etc/apt/sources.list.d/mongodb-org-7.0.list
sudo apt-get update
sudo apt-get install -y mongodb-org

# RHEL/Rocky
sudo tee /etc/yum.repos.d/mongodb-org-7.0.repo << EOF
[mongodb-org-7.0]
name=MongoDB Repository
baseurl=https://repo.mongodb.org/yum/redhat/9/mongodb-org/7.0/x86_64/
gpgcheck=1
enabled=1
gpgkey=https://www.mongodb.org/static/pgp/server-7.0.asc
EOF
sudo dnf install -y mongodb-org
```

**Start MongoDB:**

```bash
sudo systemctl enable mongod
sudo systemctl start mongod
```

**Create Database and User:**

```bash
mongosh
```

```mongosh
use admin
db.createUser({
  user: "space_admin",
  pwd: "SecurePassword123",
  roles: [ { role: "userAdminAnyDatabase", db: "admin" }, "readWriteAnyDatabase" ]
})

use space
db.createUser({
  user: "space_user",
  pwd: "SecurePassword456",
  roles: [ { role: "readWrite", db: "space" } ]
})

exit
```

### 2.3. Install and Configure RabbitMQ

**Install RabbitMQ:**

```bash
# Ubuntu/Debian
sudo apt-get install -y rabbitmq-server

# RHEL/Rocky
sudo dnf install -y rabbitmq-server
```

**Start RabbitMQ:**

```bash
sudo systemctl enable rabbitmq-server
sudo systemctl start rabbitmq-server
```

**Enable Management Plugin:**

```bash
sudo rabbitmq-plugins enable rabbitmq_management
```

**Create User:**

```bash
sudo rabbitmqctl add_user space_user SecurePassword789
sudo rabbitmqctl set_permissions -p / space_user ".*" ".*" ".*"
sudo rabbitmqctl set_user_tags space_user administrator
```

### 2.4. Clone Space Repository

```bash
# Create application directory
sudo mkdir -p /opt/space
sudo chown $USER:$USER /opt/space
cd /opt/space

# Clone repository
git clone https://github.com/TeknooSoftware/space-app.git .
```

### 2.4. Configure

```bash
cd /opt/space/appliance
./space.sh configure
```

### 2.5. Install Space Dependencies

```bash
cd /opt/space
./space.sh install
```

This installs all PHP dependencies via Composer.

### 2.6. Configure Web Server

#### Nginx Configuration

Create `/etc/nginx/sites-available/space`:

```nginx
server {
    listen 80;
    server_name space.example.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name space.example.com;
    root /opt/space/appliance/public;

    ssl_certificate /etc/ssl/certs/space.crt;
    ssl_certificate_key /etc/ssl/private/space.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        internal;
    }

    location ~ \.php$ {
        return 404;
    }

    error_log /var/log/nginx/space_error.log;
    access_log /var/log/nginx/space_access.log;
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/space /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### Apache Configuration

Create `/etc/apache2/sites-available/space.conf`:

```apache
<VirtualHost *:80>
    ServerName space.example.com
    Redirect permanent / https://space.example.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName space.example.com
    DocumentRoot /opt/space/appliance/public

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/space.crt
    SSLCertificateKeyFile /etc/ssl/private/space.key

    <Directory /opt/space/appliance/public>
        AllowOverride All
        Require all granted
        FallbackResource /index.php
    </Directory>

    <Directory /opt/space/appliance/public/bundles>
        FallbackResource disabled
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/space_error.log
    CustomLog ${APACHE_LOG_DIR}/space_access.log combined
</VirtualHost>
```

Enable the site:

```bash
sudo a2enmod rewrite ssl
sudo a2ensite space
sudo apache2ctl configtest
sudo systemctl reload apache2
```

### 2.9. Set Permissions

```bash
sudo chown -R www-data:www-data /opt/space/appliance/var
sudo chmod -R 775 /opt/space/appliance/var
```

### 2.10. Warm Up Cache

```bash
cd /opt/space
./space.sh warmup
```

### 2.11. Create Administrator User

```bash
./space.sh create-admin email=admin@example.com password=SecurePassword123
```

### 2.12. Configure Workers

Create systemd service files for workers.

**New Job Worker** (`/etc/systemd/system/space-worker-new-job.service`):

```ini
[Unit]
Description = Space New Job Worker
After = network.target rabbitmq-server.service mongodb.service

[Service]
Type = simple
User = www-data
WorkingDirectory = /opt/space/appliance
ExecStart = /usr/bin/php /opt/space/appliance/bin/console messenger:consume new_job
Restart = always
RestartSec = 10

[Install]
WantedBy = multi-user.target
```

**Execute Job Worker** (`/etc/systemd/system/space-worker-execute-job.service`):

```ini
[Unit]
Description = Space Execute Job Worker
After = network.target rabbitmq-server.service mongodb.service

[Service]
Type = simple
User = www-data
WorkingDirectory = /opt/space/appliance
ExecStart = /usr/bin/php /opt/space/appliance/bin/console messenger:consume execute_job
Restart = always
RestartSec = 10

[Install]
WantedBy = multi-user.target
```

**History Worker** (`/etc/systemd/system/space-worker-history.service`):

```ini
[Unit]
Description = Space History Worker
After = network.target rabbitmq-server.service mongodb.service

[Service]
Type = simple
User = www-data
WorkingDirectory = /opt/space/appliance
ExecStart = /usr/bin/php /opt/space/appliance/bin/console messenger:consume history_sent
Restart = always
RestartSec = 10

[Install]
WantedBy = multi-user.target
```

**Job Done Worker** (`/etc/systemd/system/space-worker-job-done.service`):

```ini
[Unit]
Description = Space Job Done Worker
After = network.target rabbitmq-server.service mongodb.service

[Service]
Type = simple
User = www-data
WorkingDirectory = /opt/space/appliance
ExecStart = /usr/bin/php /opt/space/appliance/bin/console messenger:consume job_done
Restart = always
RestartSec = 10

[Install]
WantedBy = multi-user.target
```

Enable and start workers:

```bash
sudo systemctl daemon-reload
sudo systemctl enable space-worker-new-job space-worker-execute-job space-worker-history space-worker-job-done
sudo systemctl start space-worker-new-job space-worker-execute-job space-worker-history space-worker-job-done
```

Check worker status:

```bash
sudo systemctl status space-worker-*
```

### 2.13. Verify Installation

Access your Space instance:

```
https://space.example.com
```

Log in with the administrator credentials created in step 2.11.

## Post-Installation

### Security Hardening

1. **Firewall Configuration**:
   ```bash
   sudo ufw allow 22/tcp
   sudo ufw allow 80/tcp
   sudo ufw allow 443/tcp
   sudo ufw enable
   ```

2. **Secure MongoDB**:
    - Enable authentication
    - Bind to localhost only (if on same server)
    - Use TLS connections

3. **Secure RabbitMQ**:
    - Change default passwords
    - Restrict management UI access
    - Enable TLS

4. **File Permissions**:
    - Ensure sensitive files are not publicly accessible
    - Set proper ownership and permissions

### Backup Configuration

Set up regular backups for:

1. **MongoDB Database**:
   ```bash
   mongodump --uri="mongodb://space_user:password@localhost:27017/space" --out=/backup/mongodb/$(date +%Y%m%d)
   ```

2. **Configuration Files**:
   ```bash
   tar -czf /backup/config/space-config-$(date +%Y%m%d).tar.gz /opt/space/appliance/.env.local /opt/space/config/
   ```

3. **Encryption Keys**:
   ```bash
   cp -a /opt/space/config/secrets /backup/secrets/$(date +%Y%m%d)/
   ```

### Monitoring Setup

Configure monitoring for:

- Web server health
- Worker processes
- Database connections
- Queue depths
- Disk space
- CPU and memory usage

See [worker.md](worker.md) for worker monitoring details.

### SSL Certificate Renewal

If using Let's Encrypt:

```bash
sudo certbot renew --nginx
```

Set up automatic renewal:

```bash
sudo crontab -e
# Add:
0 3 * * * certbot renew --quiet --nginx
```

## Troubleshooting

### Common Issues

**Issue: Cannot connect to MongoDB**

- Check MongoDB is running: `sudo systemctl status mongod`
- Verify connection string in `.env.local`
- Check firewall rules

**Issue: Workers not processing jobs**

- Check worker status: `sudo systemctl status space-worker-*`
- View worker logs: `sudo journalctl -u space-worker-execute-job -f`
- Verify RabbitMQ connection

**Issue: 500 Internal Server Error**

- Check application logs: `tail -f /opt/space/appliance/var/log/prod.log`
- Verify file permissions
- Clear cache: `./space.sh warmup`

**Issue: Cannot build images**

- Ensure Buildah is installed
- Check Buildah configuration
- Verify worker has proper permissions

### Getting Help

- **Documentation**: Check other documentation files
- **GitHub Issues**: https://github.com/TeknooSoftware/space-app/issues
- **Community Support**: GitHub Discussions
- **Commercial Support**: contact@teknoo.software

## Next Steps

After installation:

1. Review [configuration.md](configuration.md) for detailed configuration options
2. Set up your first project and deployment
3. Configure subscription plans (if needed)
4. Set up monitoring and alerting

