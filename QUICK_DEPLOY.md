# 🚀 Quick Deployment Reference

## Choose Your Deployment Method

### 1️⃣ **Quick Deploy (Recommended for Beginners)**

#### On Linux Server:
```bash
chmod +x deploy.sh
./deploy.sh
```

#### On Windows (PowerShell):
```powershell
.\deploy.ps1
```

**What it does:**
- ✅ Installs Docker & Docker Compose
- ✅ Configures environment
- ✅ Builds & starts containers
- ✅ Sets up SSL certificate
- ✅ Configures firewall

**Time:** ~15 minutes

---

### 2️⃣ **Manual Docker Deployment**

```bash
# 1. Clone repository
git clone https://github.com/shakhawatmollah/expense-tracker.git
cd expense-tracker

# 2. Setup environment
cp backend/.env.example backend/.env
nano backend/.env  # Edit with your settings

# 3. Create docker .env
echo "DB_PASSWORD=your_password" > .env
echo "DB_ROOT_PASSWORD=your_root_password" >> .env

# 4. Build and start
docker-compose -f docker-compose.prod.yml up -d --build

# 5. Initialize
docker exec expense-tracker-app php artisan key:generate --force
docker exec expense-tracker-app php artisan migrate --force
docker exec expense-tracker-app php artisan config:cache
```

**Time:** ~10 minutes

---

### 3️⃣ **Local Development Setup**

#### On Linux/Mac:
```bash
chmod +x setup-dev.sh
./setup-dev.sh
```

#### On Windows (PowerShell):
```powershell
.\setup-dev.ps1
```

**Then start servers:**
```bash
# Terminal 1 - Backend
cd backend && php artisan serve

# Terminal 2 - Frontend
cd frontend && npm run dev
```

**Access:** http://localhost:5173

---

## 📋 Post-Deployment Checklist

After deployment, complete these tasks:

### Essential (Must Do):
- [ ] Test health endpoint: `curl https://your-domain.com/api/health`
- [ ] Create admin user (see command below)
- [ ] Update DNS A records
- [ ] Test login/registration
- [ ] Verify email settings (if configured)

### Recommended (Should Do):
- [ ] Setup monitoring (UptimeRobot, Pingdom)
- [ ] Configure backups
- [ ] Setup Sentry for error tracking
- [ ] Test API endpoints
- [ ] Review logs for errors

### Optional (Nice to Have):
- [ ] Configure CDN
- [ ] Setup staging environment
- [ ] Enable analytics
- [ ] Configure custom domain email

---

## 🔑 Create Admin User

```bash
# SSH into your server
ssh user@your-server-ip

# For Docker deployment:
docker exec -it expense-tracker-app php artisan tinker

# For VPS deployment:
cd /var/www/expense-tracker/backend
php artisan tinker

# Then in tinker:
>>> $user = new App\Models\User();
>>> $user->name = 'Admin';
>>> $user->email = 'admin@yourdomain.com';
>>> $user->password = bcrypt('YourSecurePassword123!');
>>> $user->save();
>>> exit
```

---

## 🔍 Common Commands

### Docker Deployment:

```bash
# View logs
docker-compose -f docker-compose.prod.yml logs -f

# Restart containers
docker-compose -f docker-compose.prod.yml restart

# Stop containers
docker-compose -f docker-compose.prod.yml down

# Update application
git pull
docker-compose -f docker-compose.prod.yml up -d --build

# Run artisan commands
docker exec expense-tracker-app php artisan [command]

# Enter container shell
docker exec -it expense-tracker-app sh

# Check health
curl https://your-domain.com/api/health

# View database
docker exec -it expense-tracker-db mysql -u expense_user -p expense_tracker
```

### VPS Deployment:

```bash
# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm

# View logs
tail -f /var/www/expense-tracker/backend/storage/logs/laravel.log
tail -f /var/log/nginx/error.log

# Run artisan commands
cd /var/www/expense-tracker/backend
php artisan [command]

# Check status
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
sudo systemctl status redis

# Update application
cd /var/www/expense-tracker
git pull
cd backend
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
cd ../frontend
npm ci && npm run build
cp -r dist/* ../backend/public/
```

---

## 🔧 Troubleshooting Quick Fixes

### Container won't start:
```bash
docker-compose -f docker-compose.prod.yml down
docker-compose -f docker-compose.prod.yml up -d --build --force-recreate
```

### Permission errors:
```bash
docker exec expense-tracker-app chown -R www-data:www-data /var/www/html/backend/storage
docker exec expense-tracker-app chmod -R 775 /var/www/html/backend/storage
```

### Clear all caches:
```bash
docker exec expense-tracker-app php artisan cache:clear
docker exec expense-tracker-app php artisan config:clear
docker exec expense-tracker-app php artisan route:clear
docker exec expense-tracker-app php artisan view:clear
docker exec expense-tracker-app php artisan config:cache
docker exec expense-tracker-app php artisan route:cache
```

### Database connection failed:
```bash
# Check database is running
docker-compose -f docker-compose.prod.yml ps db

# Restart database
docker-compose -f docker-compose.prod.yml restart db

# Check .env settings match docker-compose
cat backend/.env | grep DB_
```

### Frontend shows white screen:
```bash
# Check if files exist
docker exec expense-tracker-app ls -la backend/public/assets

# Rebuild frontend locally and copy
cd frontend
npm run build
docker cp dist/. expense-tracker-app:/var/www/html/backend/public/
```

---

## 📊 Monitoring URLs

Add these to your monitoring service:

- **Main:** https://your-domain.com
- **Health Check:** https://your-domain.com/api/health
- **API Status:** https://your-domain.com/api/v1/auth/login (expect 405)

Expected responses:
- **Health:** 200 OK with JSON
- **Main:** 200 OK with HTML
- **API:** 405 Method Not Allowed (normal for GET on login)

---

## 🔐 Security Checklist

Before going live:

- [ ] `APP_DEBUG=false` in production
- [ ] `APP_ENV=production` 
- [ ] Strong database passwords (min 16 chars)
- [ ] SSL certificate installed
- [ ] Firewall enabled (ports 80, 443, 22 only)
- [ ] `SECURE_COOKIES=true`
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] Rate limiting enabled
- [ ] CORS properly configured
- [ ] Regular backups scheduled
- [ ] Monitoring setup
- [ ] Error tracking (Sentry) configured

---

## 🆘 Get Help

1. **Read full guide:** `DEPLOYMENT_GUIDE.md`
2. **Check health:** `curl https://your-domain.com/api/health`
3. **View logs:** `docker-compose -f docker-compose.prod.yml logs -f`
4. **GitHub Issues:** https://github.com/shakhawatmollah/expense-tracker/issues

---

## 📱 Quick Test After Deployment

```bash
# 1. Health check
curl https://your-domain.com/api/health

# 2. Register test user
curl -X POST https://your-domain.com/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test@test.com","password":"password","password_confirmation":"password"}'

# 3. Login
curl -X POST https://your-domain.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"password"}'

# Expected: 200 OK with token
```

---

**⚡ That's it! Your Expense Tracker is live! 🎉**

For detailed instructions, see: [DEPLOYMENT_GUIDE.md](./DEPLOYMENT_GUIDE.md)
