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
echo "📊 Demo Login Credentials:"
echo "   Email: demo@example.com | Password: demo123"
echo "   Email: shakhawat@example.com | Password: password123"
echo "   Email: tawfiq@example.com | Password: password123"
echo "   Email: admin@example.com | Password: admin123"
echo ""
echo "🎯 Visit http://localhost:8000 to view the application"