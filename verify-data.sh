#!/bin/bash

# Database Data Verification Script
echo "🔍 Verifying seeded data..."

cd backend

echo ""
echo "📊 Database Statistics:"
echo "======================"

# Count users
USER_COUNT=$(php artisan tinker --execute="echo App\Models\User::count();")
echo "👥 Users: $USER_COUNT"

# Count categories
CATEGORY_COUNT=$(php artisan tinker --execute="echo App\Models\Category::count();")
echo "📁 Categories: $CATEGORY_COUNT"

# Count expenses
EXPENSE_COUNT=$(php artisan tinker --execute="echo App\Models\Expense::count();")
echo "💰 Expenses: $EXPENSE_COUNT"

echo ""
echo "✅ Data verification completed!"
echo "🌐 Frontend: http://localhost:3000"
echo "🔧 Backend API: http://localhost:8000"