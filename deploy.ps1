# Expense Tracker - Windows Deployment Script
# For use with WSL2 or Windows with Docker Desktop

Write-Host "🚀 Expense Tracker - Windows Deploy Script" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Check if Docker is running
try {
    docker ps | Out-Null
} catch {
    Write-Host "❌ Error: Docker is not running. Please start Docker Desktop." -ForegroundColor Red
    exit 1
}

# Get user inputs
$domain = Read-Host "Enter your domain name (e.g., expense.example.com)"
$email = Read-Host "Enter your email for SSL certificate"
$dbPassword = Read-Host "Enter MySQL password" -AsSecureString
$dbRootPassword = Read-Host "Enter MySQL root password" -AsSecureString

# Convert secure strings to plain text
$BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($dbPassword)
$dbPasswordPlain = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)

$BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($dbRootPassword)
$dbRootPasswordPlain = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)

Write-Host ""
Write-Host "📋 Configuration:" -ForegroundColor Yellow
Write-Host "Domain: $domain"
Write-Host "Email: $email"
Write-Host ""

$continue = Read-Host "Continue with deployment? (y/n)"
if ($continue -ne 'y') {
    exit 0
}

Write-Host ""
Write-Host "⚙️  Step 1: Configuring environment..." -ForegroundColor Cyan

# Create .env for docker-compose
@"
DB_PASSWORD=$dbPasswordPlain
DB_ROOT_PASSWORD=$dbRootPasswordPlain
"@ | Out-File -FilePath ".env" -Encoding UTF8

# Configure backend .env
if (-not (Test-Path "backend\.env")) {
    Copy-Item "backend\.env.example" "backend\.env"
}

# Update backend .env
$envContent = Get-Content "backend\.env" -Raw
$envContent = $envContent -replace 'APP_URL=.*', "APP_URL=https://$domain"
$envContent = $envContent -replace 'FRONTEND_URL=.*', "FRONTEND_URL=https://$domain"
$envContent = $envContent -replace 'SANCTUM_STATEFUL_DOMAINS=.*', "SANCTUM_STATEFUL_DOMAINS=$domain"
$envContent = $envContent -replace 'APP_ENV=.*', 'APP_ENV=production'
$envContent = $envContent -replace 'APP_DEBUG=.*', 'APP_DEBUG=false'
$envContent = $envContent -replace 'DB_CONNECTION=.*', 'DB_CONNECTION=mysql'
$envContent = $envContent -replace 'DB_HOST=.*', 'DB_HOST=db'
$envContent = $envContent -replace 'DB_DATABASE=.*', 'DB_DATABASE=expense_tracker'
$envContent = $envContent -replace 'DB_USERNAME=.*', 'DB_USERNAME=expense_user'
$envContent = $envContent -replace 'DB_PASSWORD=.*', "DB_PASSWORD=$dbPasswordPlain"
$envContent = $envContent -replace 'CACHE_STORE=.*', 'CACHE_STORE=redis'
$envContent = $envContent -replace 'SESSION_DRIVER=.*', 'SESSION_DRIVER=redis'
$envContent = $envContent -replace 'QUEUE_CONNECTION=.*', 'QUEUE_CONNECTION=redis'
$envContent = $envContent -replace 'REDIS_HOST=.*', 'REDIS_HOST=redis'
$envContent | Out-File -FilePath "backend\.env" -Encoding UTF8 -NoNewline

# Update frontend .env
@"
VITE_API_BASE_URL=https://$domain/api
VITE_API_VERSION=v1
VITE_APP_NAME="Expense Tracker"
"@ | Out-File -FilePath "frontend\.env" -Encoding UTF8

Write-Host "✓ Environment configured" -ForegroundColor Green

Write-Host ""
Write-Host "🏗️  Step 2: Building and starting containers..." -ForegroundColor Cyan
docker-compose -f docker-compose.prod.yml up -d --build

Write-Host ""
Write-Host "⏳ Waiting for containers to be ready..." -ForegroundColor Yellow
Start-Sleep -Seconds 10

Write-Host ""
Write-Host "🔑 Step 3: Initializing application..." -ForegroundColor Cyan

# Generate app key
docker exec expense-tracker-app php artisan key:generate --force

# Run migrations
docker exec expense-tracker-app php artisan migrate --force

# Create storage link
docker exec expense-tracker-app php artisan storage:link

# Optimize
docker exec expense-tracker-app php artisan config:cache
docker exec expense-tracker-app php artisan route:cache
docker exec expense-tracker-app php artisan view:cache

Write-Host "✓ Application initialized" -ForegroundColor Green

Write-Host ""
Write-Host "✅ Deployment Complete!" -ForegroundColor Green
Write-Host "=======================" -ForegroundColor Green
Write-Host ""
Write-Host "🌐 Your application is running at:" -ForegroundColor Cyan
Write-Host "http://localhost" -ForegroundColor Yellow
Write-Host ""
Write-Host "📊 Health Check:" -ForegroundColor Cyan
Write-Host "Invoke-WebRequest -Uri http://localhost/api/health | Select-Object -ExpandProperty Content" -ForegroundColor Yellow
Write-Host ""
Write-Host "🔑 Create your first user:" -ForegroundColor Cyan
Write-Host 'docker exec -it expense-tracker-app php artisan tinker' -ForegroundColor Yellow
Write-Host 'Then: $user = App\Models\User::create(["name"=>"Admin", "email"=>"admin@example.com", "password"=>bcrypt("password")]);' -ForegroundColor Yellow
Write-Host ""
Write-Host "📖 View logs:" -ForegroundColor Cyan
Write-Host "docker-compose -f docker-compose.prod.yml logs -f" -ForegroundColor Yellow
Write-Host ""
Write-Host "🔄 To update in the future:" -ForegroundColor Cyan
Write-Host "git pull; docker-compose -f docker-compose.prod.yml up -d --build" -ForegroundColor Yellow
Write-Host ""
Write-Host "⚠️  For production deployment:" -ForegroundColor Yellow
Write-Host "1. Point your domain DNS to your server IP" -ForegroundColor White
Write-Host "2. Setup SSL certificate (see DEPLOYMENT_GUIDE.md)" -ForegroundColor White
Write-Host "3. Configure firewall rules" -ForegroundColor White
Write-Host ""
Write-Host "🎉 Happy tracking!" -ForegroundColor Green
