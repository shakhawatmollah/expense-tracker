#!/bin/bash

#############################################
# Expense Tracker - Local Development Setup
#############################################

set -e

echo "🚀 Expense Tracker - Local Development Setup"
echo "============================================="
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

echo -e "${CYAN}📦 Step 1: Installing Backend Dependencies...${NC}"
cd backend
composer install
echo -e "${GREEN}✓ Backend dependencies installed${NC}"

echo ""
echo -e "${CYAN}⚙️  Step 2: Setting up environment...${NC}"
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo -e "${GREEN}✓ Created .env file${NC}"
else
    echo -e "${YELLOW}⚠  .env file already exists${NC}"
fi

echo ""
echo -e "${CYAN}🔑 Step 3: Generating application key...${NC}"
php artisan key:generate
echo -e "${GREEN}✓ Application key generated${NC}"

echo ""
echo -e "${CYAN}🗄️  Step 4: Setting up database...${NC}"
touch database/database.sqlite
php artisan migrate
echo -e "${GREEN}✓ Database migrated${NC}"

echo ""
read -p "Do you want to seed the database with sample data? (y/n) " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan db:seed
    echo -e "${GREEN}✓ Database seeded with sample data${NC}"
fi

echo ""
echo -e "${CYAN}🔗 Step 5: Creating storage link...${NC}"
php artisan storage:link
echo -e "${GREEN}✓ Storage link created${NC}"

cd ..

echo ""
echo -e "${CYAN}📦 Step 6: Installing Frontend Dependencies...${NC}"
cd frontend
npm install
echo -e "${GREEN}✓ Frontend dependencies installed${NC}"

echo ""
echo -e "${CYAN}⚙️  Step 7: Setting up frontend environment...${NC}"
cat > .env << EOF
VITE_API_BASE_URL=http://localhost:8000/api
VITE_API_VERSION=v1
VITE_APP_NAME="Expense Tracker"
EOF
echo -e "${GREEN}✓ Frontend environment configured${NC}"

cd ..

echo ""
echo -e "${GREEN}✅ Setup Complete!${NC}"
echo "=================="
echo ""
echo -e "${CYAN}🚀 To start development:${NC}"
echo ""
echo "Terminal 1 (Backend):"
echo -e "${YELLOW}  cd backend && php artisan serve${NC}"
echo ""
echo "Terminal 2 (Frontend):"
echo -e "${YELLOW}  cd frontend && npm run dev${NC}"
echo ""
echo "Terminal 3 (Queue Worker - Optional):"
echo -e "${YELLOW}  cd backend && php artisan queue:work${NC}"
echo ""
echo -e "${CYAN}📖 Access the application:${NC}"
echo "  Frontend: http://localhost:5173"
echo "  Backend API: http://localhost:8000/api"
echo "  Health Check: http://localhost:8000/api/health"
echo ""
echo -e "${CYAN}🔑 Default credentials (if seeded):${NC}"
echo "  Email: test@example.com"
echo "  Password: password"
echo ""
echo -e "${GREEN}🎉 Happy coding!${NC}"
