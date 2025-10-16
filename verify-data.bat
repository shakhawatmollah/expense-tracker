@echo off
REM Database Data Verification Script (Windows)
echo 🔍 Verifying seeded data...

cd backend

echo.
echo 📊 Database Statistics:
echo ======================

REM Count users, categories, and expenses
php artisan tinker --execute="echo 'Users: ' . App\Models\User::count();"
php artisan tinker --execute="echo 'Categories: ' . App\Models\Category::count();"
php artisan tinker --execute="echo 'Expenses: ' . App\Models\Expense::count();"

echo.
echo ✅ Data verification completed!
echo 🌐 Frontend: http://localhost:3000
echo 🔧 Backend API: http://localhost:8000

pause