#!/bin/bash

# Database Seeder Script for Expense Tracker
# This script will refresh the database and seed it with sample data

echo "🔄 Refreshing database and running seeders..."

# Navigate to backend directory
cd backend

# Refresh database and run seeders
php artisan migrate:fresh --seed

echo "✅ Database seeding completed!"
echo ""
echo "📊 Demo users have been created."
echo "ℹ️  Check backend/database/seeders/UserSeeder.php for login credentials."
echo "⚠️  Remember to change default passwords in production!"
echo ""
echo "🎯 Visit http://localhost:8000 to view the application"