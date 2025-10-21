# Expense Tracker - Local Development Setup (Windows)

Write-Host "🚀 Expense Tracker - Local Development Setup" -ForegroundColor Cyan
Write-Host "=============================================" -ForegroundColor Cyan
Write-Host ""

# Check if Composer is installed
if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Error: Composer is not installed. Please install from https://getcomposer.org" -ForegroundColor Red
    exit 1
}

# Check if Node.js is installed
if (-not (Get-Command node -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Error: Node.js is not installed. Please install from https://nodejs.org" -ForegroundColor Red
    exit 1
}

Write-Host "📦 Step 1: Installing Backend Dependencies..." -ForegroundColor Cyan
Set-Location backend
composer install
Write-Host "✓ Backend dependencies installed" -ForegroundColor Green

Write-Host ""
Write-Host "⚙️  Step 2: Setting up environment..." -ForegroundColor Cyan
if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "✓ Created .env file" -ForegroundColor Green
} else {
    Write-Host "⚠  .env file already exists" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "🔑 Step 3: Generating application key..." -ForegroundColor Cyan
php artisan key:generate
Write-Host "✓ Application key generated" -ForegroundColor Green

Write-Host ""
Write-Host "🗄️  Step 4: Setting up database..." -ForegroundColor Cyan
if (-not (Test-Path "database\database.sqlite")) {
    New-Item -Path "database\database.sqlite" -ItemType File | Out-Null
}
php artisan migrate
Write-Host "✓ Database migrated" -ForegroundColor Green

Write-Host ""
$seed = Read-Host "Do you want to seed the database with sample data? (y/n)"
if ($seed -eq 'y') {
    php artisan db:seed
    Write-Host "✓ Database seeded with sample data" -ForegroundColor Green
}

Write-Host ""
Write-Host "🔗 Step 5: Creating storage link..." -ForegroundColor Cyan
php artisan storage:link
Write-Host "✓ Storage link created" -ForegroundColor Green

Set-Location ..

Write-Host ""
Write-Host "📦 Step 6: Installing Frontend Dependencies..." -ForegroundColor Cyan
Set-Location frontend
npm install
Write-Host "✓ Frontend dependencies installed" -ForegroundColor Green

Write-Host ""
Write-Host "⚙️  Step 7: Setting up frontend environment..." -ForegroundColor Cyan
@"
VITE_API_BASE_URL=http://localhost:8000/api
VITE_API_VERSION=v1
VITE_APP_NAME="Expense Tracker"
"@ | Out-File -FilePath ".env" -Encoding UTF8
Write-Host "✓ Frontend environment configured" -ForegroundColor Green

Set-Location ..

Write-Host ""
Write-Host "✅ Setup Complete!" -ForegroundColor Green
Write-Host "==================" -ForegroundColor Green
Write-Host ""
Write-Host "🚀 To start development:" -ForegroundColor Cyan
Write-Host ""
Write-Host "Terminal 1 (Backend):" -ForegroundColor White
Write-Host "  cd backend; php artisan serve" -ForegroundColor Yellow
Write-Host ""
Write-Host "Terminal 2 (Frontend):" -ForegroundColor White
Write-Host "  cd frontend; npm run dev" -ForegroundColor Yellow
Write-Host ""
Write-Host "Terminal 3 (Queue Worker - Optional):" -ForegroundColor White
Write-Host "  cd backend; php artisan queue:work" -ForegroundColor Yellow
Write-Host ""
Write-Host "📖 Access the application:" -ForegroundColor Cyan
Write-Host "  Frontend: http://localhost:5173" -ForegroundColor White
Write-Host "  Backend API: http://localhost:8000/api" -ForegroundColor White
Write-Host "  Health Check: http://localhost:8000/api/health" -ForegroundColor White
Write-Host ""
Write-Host "🔑 Default credentials (if seeded):" -ForegroundColor Cyan
Write-Host "  Email: test@example.com" -ForegroundColor White
Write-Host "  Password: password" -ForegroundColor White
Write-Host ""
Write-Host "🎉 Happy coding!" -ForegroundColor Green
