# 🚀 Deployment Guide - Expense Tracker

Complete guide for deploying the Expense Tracker application to production.

---

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [Deployment Options](#deployment-options)
3. [Option 1: Docker Deployment](#option-1-docker-deployment-recommended)
4. [Option 2: VPS Deployment](#option-2-vps-deployment)
5. [Option 3: Cloud Platform Deployment](#option-3-cloud-platforms)
6. [Post-Deployment](#post-deployment)
7. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Required Software
- **Docker** (v20.10+) & **Docker Compose** (v2.0+)
- **Git** for version control
- **Node.js** (v18+) for building frontend
- **Composer** for PHP dependencies

### Required Accounts (Optional)
- Domain name provider (Namecheap, GoDaddy, etc.)
- SSL certificate (Let's Encrypt - free)
- Cloud provider (DigitalOcean, AWS, Azure, etc.)

### Environment Requirements
- **Minimum Server**: 2GB RAM, 2 CPU cores, 20GB storage
- **Recommended**: 4GB RAM, 2 CPU cores, 40GB storage
- **OS**: Ubuntu 22.04 LTS (recommended) or any Linux distro

---

## Deployment Options

### Choose Your Deployment Method:

| Method | Best For | Complexity | Cost |
|--------|----------|------------|------|
| **Docker** | Modern deployments, scalability | Easy | Low |
| **VPS** | Full control, custom setup | Medium | Medium |
| **Cloud** | Enterprise, auto-scaling | Medium-Hard | Variable |

---

## Option 1: Docker Deployment (Recommended)

### Step 1: Prepare Your Server

```bash
# SSH into your server
ssh user@your-server-ip

# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo apt install docker-compose -y

# Add user to docker group
sudo usermod -aG docker $USER
newgrp docker
```

### Step 2: Clone Your Repository

```bash
# Clone the project
git clone https://github.com/shakhawatmollah/expense-tracker.git
cd expense-tracker

# Or upload your files via SCP
scp -r ./expense-tracker user@your-server-ip:/home/user/
```

### Step 3: Configure Environment

```bash
# Copy environment file
cp backend/.env.example backend/.env

# Edit environment variables
nano backend/.env
```

**Required Changes in `.env`:**

```env
# Application
APP_NAME="Expense Tracker"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Generate app key (run: php artisan key:generate)
APP_KEY=base64:YOUR_GENERATED_KEY_HERE

# Database
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=expense_tracker
DB_USERNAME=expense_user
DB_PASSWORD=your_strong_password_here

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache & Session
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Security
SECURE_COOKIES=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

# Frontend
FRONTEND_URL=https://your-domain.com
SANCTUM_STATEFUL_DOMAINS=your-domain.com

# Mail (Optional - for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Step 4: Create Docker Environment File

```bash
# Create .env file for docker-compose
nano .env
```

Add:
```env
DB_PASSWORD=your_strong_db_password
DB_ROOT_PASSWORD=your_strong_root_password
```

### Step 5: Build and Deploy

```bash
# Build and start containers
docker-compose -f docker-compose.prod.yml up -d --build

# Check if containers are running
docker-compose -f docker-compose.prod.yml ps

# View logs
docker-compose -f docker-compose.prod.yml logs -f app
```

### Step 6: Initialize Application

```bash
# Enter the application container
docker exec -it expense-tracker-app sh

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Seed database (optional - for demo data)
php artisan db:seed --force

# Create storage link
php artisan storage:link

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Exit container
exit
```

### Step 7: Setup SSL (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Get SSL certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

**Update nginx config for SSL:**

```bash
# Edit docker/nginx.conf
nano docker/nginx.conf
```

Add SSL configuration (see example in troubleshooting section).

**Restart containers:**
```bash
docker-compose -f docker-compose.prod.yml restart
```

---

## Option 2: VPS Deployment

### Step 1: Setup Server

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring \
    php8.2-curl php8.2-zip php8.2-gd php8.2-redis php8.2-cli

# Install MySQL
sudo apt install mysql-server -y
sudo mysql_secure_installation

# Install Redis
sudo apt install redis-server -y

# Install Nginx
sudo apt install nginx -y

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Step 2: Setup Database

```bash
# Login to MySQL
sudo mysql -u root -p

# Create database and user
CREATE DATABASE expense_tracker;
CREATE USER 'expense_user'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON expense_tracker.* TO 'expense_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 3: Deploy Application

```bash
# Create directory
sudo mkdir -p /var/www/expense-tracker
sudo chown -R $USER:$USER /var/www/expense-tracker

# Clone repository
cd /var/www/expense-tracker
git clone https://github.com/shakhawatmollah/expense-tracker.git .

# Setup backend
cd backend
cp .env.example .env
nano .env  # Edit with your settings

# Install dependencies
composer install --no-dev --optimize-autoloader

# Generate key and migrate
php artisan key:generate
php artisan migrate --force
php artisan storage:link

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
sudo chown -R www-data:www-data /var/www/expense-tracker
sudo chmod -R 755 /var/www/expense-tracker
sudo chmod -R 775 /var/www/expense-tracker/backend/storage
sudo chmod -R 775 /var/www/expense-tracker/backend/bootstrap/cache
```

### Step 4: Build Frontend

```bash
# Build frontend
cd /var/www/expense-tracker/frontend
npm ci
npm run build

# Copy to backend public
cp -r dist/* ../backend/public/
```

### Step 5: Configure Nginx

```bash
# Create Nginx config
sudo nano /etc/nginx/sites-available/expense-tracker
```

Add:
```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/expense-tracker/backend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/expense-tracker /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Step 6: Setup Queue Worker

```bash
# Create supervisor config
sudo nano /etc/supervisor/conf.d/expense-tracker.conf
```

Add:
```ini
[program:expense-tracker-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/expense-tracker/backend/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasflags=TERM
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/expense-tracker/backend/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Start supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start expense-tracker-worker:*
```

### Step 7: Setup Cron for Laravel Scheduler

```bash
sudo crontab -e -u www-data
```

Add:
```cron
* * * * * cd /var/www/expense-tracker/backend && php artisan schedule:run >> /dev/null 2>&1
```

---

## Option 3: Cloud Platforms

### A. DigitalOcean App Platform

1. **Create App:**
   - Go to DigitalOcean App Platform
   - Click "Create App"
   - Connect your GitHub repository

2. **Configure:**
   - **Backend**: Laravel (PHP 8.2)
   - **Database**: Managed MySQL
   - **Redis**: Managed Redis
   - Set environment variables

3. **Deploy:**
   - App Platform handles everything automatically

### B. AWS Elastic Beanstalk

1. **Install EB CLI:**
```bash
pip install awsebcli
```

2. **Initialize:**
```bash
eb init -p php-8.2 expense-tracker
```

3. **Deploy:**
```bash
eb create production
eb deploy
```

### C. Heroku

1. **Install Heroku CLI:**
```bash
curl https://cli-assets.heroku.com/install.sh | sh
```

2. **Deploy:**
```bash
heroku login
heroku create expense-tracker
heroku addons:create heroku-postgresql:mini
heroku addons:create heroku-redis:mini
git push heroku main
heroku run php artisan migrate --force
```

---

## Post-Deployment

### 1. Test Application

```bash
# Check health endpoint
curl https://your-domain.com/api/health

# Expected response:
# {"status":"healthy","checks":{...}}
```

### 2. Setup Monitoring

**Using UptimeRobot (Free):**
1. Go to uptimerobot.com
2. Add new monitor
3. URL: https://your-domain.com/api/health
4. Interval: 5 minutes

**Using Sentry (Error Tracking):**
```bash
# Install Sentry SDK
composer require sentry/sentry-laravel

# Add to .env
SENTRY_LARAVEL_DSN=your_sentry_dsn_here
```

### 3. Setup Backups

```bash
# Test backup
docker exec expense-tracker-app php artisan backup:database

# Check backup was created
docker exec expense-tracker-app ls -la storage/app/backups/

# Backups run automatically daily at 2 AM (configured in Kernel.php)
```

### 4. Configure Domain DNS

**Add A Records:**
```
Type    Name    Value           TTL
A       @       your-server-ip  3600
A       www     your-server-ip  3600
```

### 5. Create Admin User

```bash
# SSH into server
docker exec -it expense-tracker-app sh

# Create user via tinker
php artisan tinker

>>> $user = new App\Models\User();
>>> $user->name = 'Admin';
>>> $user->email = 'admin@yourdomain.com';
>>> $user->password = bcrypt('SecurePassword123!');
>>> $user->save();
>>> exit
```

---

## Troubleshooting

### Issue: Containers not starting

```bash
# Check logs
docker-compose -f docker-compose.prod.yml logs

# Rebuild
docker-compose -f docker-compose.prod.yml down
docker-compose -f docker-compose.prod.yml up -d --build --force-recreate
```

### Issue: Permission denied errors

```bash
# Fix storage permissions
docker exec expense-tracker-app chown -R www-data:www-data /var/www/html/backend/storage
docker exec expense-tracker-app chmod -R 775 /var/www/html/backend/storage
```

### Issue: Database connection failed

```bash
# Check database is running
docker-compose -f docker-compose.prod.yml ps db

# Test connection
docker exec expense-tracker-app php artisan tinker
>>> DB::connection()->getPdo();
```

### Issue: 502 Bad Gateway

```bash
# Check nginx config
docker exec expense-tracker-app nginx -t

# Restart nginx
docker-compose -f docker-compose.prod.yml restart app
```

### Issue: Frontend shows white screen

```bash
# Check if frontend was built
docker exec expense-tracker-app ls -la backend/public/assets

# Rebuild frontend
cd frontend
npm run build
docker cp dist/. expense-tracker-app:/var/www/html/backend/public/
```

### Issue: API returns 404

**Check `.env` file:**
```env
# Make sure these are set correctly
APP_URL=https://your-domain.com
FRONTEND_URL=https://your-domain.com
SANCTUM_STATEFUL_DOMAINS=your-domain.com
```

---

## Performance Optimization

### 1. Enable OPcache

```bash
# Edit php.ini
docker exec -it expense-tracker-app sh
vi /usr/local/etc/php/php.ini
```

Add:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### 2. Configure Redis

```bash
# Increase Redis max memory
docker exec expense-tracker-redis redis-cli CONFIG SET maxmemory 256mb
docker exec expense-tracker-redis redis-cli CONFIG SET maxmemory-policy allkeys-lru
```

### 3. Optimize Database

```sql
-- Add indexes (already included in migrations)
-- But check with:
SHOW INDEX FROM expenses;
SHOW INDEX FROM categories;
SHOW INDEX FROM budgets;
```

---

## Security Checklist

- [ ] APP_DEBUG=false in production
- [ ] Strong passwords for database
- [ ] SSL certificate installed
- [ ] Firewall configured (ufw/iptables)
- [ ] Only necessary ports open (80, 443, 22)
- [ ] Regular backups enabled
- [ ] Monitoring setup
- [ ] Logs rotation configured
- [ ] Security headers enabled in nginx
- [ ] Rate limiting configured
- [ ] CORS properly configured

---

## Maintenance

### Update Application

```bash
# Pull latest changes
git pull origin main

# Update dependencies
docker exec expense-tracker-app composer install --no-dev --optimize-autoloader

# Run migrations
docker exec expense-tracker-app php artisan migrate --force

# Clear caches
docker exec expense-tracker-app php artisan cache:clear
docker exec expense-tracker-app php artisan config:cache
docker exec expense-tracker-app php artisan route:cache
docker exec expense-tracker-app php artisan view:cache

# Restart
docker-compose -f docker-compose.prod.yml restart
```

### Scale Workers

```bash
# Edit docker-compose.prod.yml
# Add worker service:
  worker:
    build:
      context: .
      dockerfile: Dockerfile.production
    command: php artisan queue:work redis --sleep=3 --tries=3
    restart: unless-stopped
    depends_on:
      - db
      - redis

# Scale workers
docker-compose -f docker-compose.prod.yml up -d --scale worker=3
```

---

## Support

For issues:
1. Check logs: `docker-compose -f docker-compose.prod.yml logs -f`
2. Check health: `curl https://your-domain.com/api/health`
3. GitHub Issues: [Create Issue](https://github.com/shakhawatmollah/expense-tracker/issues)

---

**Congratulations! Your Expense Tracker is now live! 🎉**

Access at: https://your-domain.com
