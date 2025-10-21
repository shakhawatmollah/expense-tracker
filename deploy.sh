#!/bin/bash

#############################################
# Expense Tracker - Quick Deploy Script
# For Ubuntu 22.04 LTS
#############################################

set -e

echo "🚀 Expense Tracker - Quick Deploy Script"
echo "========================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   echo -e "${RED}Error: This script should not be run as root${NC}" 
   exit 1
fi

# Get user inputs
read -p "Enter your domain name (e.g., expense.example.com): " DOMAIN
read -p "Enter your email for SSL certificate: " EMAIL
read -sp "Enter MySQL password: " DB_PASSWORD
echo ""
read -sp "Enter MySQL root password: " DB_ROOT_PASSWORD
echo ""

echo ""
echo "📋 Configuration:"
echo "Domain: $DOMAIN"
echo "Email: $EMAIL"
echo ""

read -p "Continue with deployment? (y/n) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    exit 1
fi

echo ""
echo "🔧 Step 1: Updating system..."
sudo apt update && sudo apt upgrade -y

echo ""
echo "🐳 Step 2: Installing Docker..."
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    sudo usermod -aG docker $USER
    rm get-docker.sh
    echo -e "${GREEN}✓ Docker installed${NC}"
else
    echo -e "${YELLOW}Docker already installed${NC}"
fi

echo ""
echo "📦 Step 3: Installing Docker Compose..."
if ! command -v docker-compose &> /dev/null; then
    sudo apt install docker-compose -y
    echo -e "${GREEN}✓ Docker Compose installed${NC}"
else
    echo -e "${YELLOW}Docker Compose already installed${NC}"
fi

echo ""
echo "🔒 Step 4: Installing Certbot for SSL..."
sudo apt install certbot python3-certbot-nginx -y

echo ""
echo "📥 Step 5: Cloning repository..."
if [ ! -d "expense-tracker" ]; then
    git clone https://github.com/shakhawatmollah/expense-tracker.git
    cd expense-tracker
else
    cd expense-tracker
    git pull origin main
fi

echo ""
echo "⚙️  Step 6: Configuring environment..."

# Create .env for docker-compose
cat > .env << EOF
DB_PASSWORD=$DB_PASSWORD
DB_ROOT_PASSWORD=$DB_ROOT_PASSWORD
EOF

# Configure backend .env
if [ ! -f "backend/.env" ]; then
    cp backend/.env.example backend/.env
fi

# Update backend .env with domain
sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN|g" backend/.env
sed -i "s|FRONTEND_URL=.*|FRONTEND_URL=https://$DOMAIN|g" backend/.env
sed -i "s|SANCTUM_STATEFUL_DOMAINS=.*|SANCTUM_STATEFUL_DOMAINS=$DOMAIN|g" backend/.env
sed -i "s|APP_ENV=.*|APP_ENV=production|g" backend/.env
sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|g" backend/.env
sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=mysql|g" backend/.env
sed -i "s|DB_HOST=.*|DB_HOST=db|g" backend/.env
sed -i "s|DB_DATABASE=.*|DB_DATABASE=expense_tracker|g" backend/.env
sed -i "s|DB_USERNAME=.*|DB_USERNAME=expense_user|g" backend/.env
sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|g" backend/.env
sed -i "s|CACHE_STORE=.*|CACHE_STORE=redis|g" backend/.env
sed -i "s|SESSION_DRIVER=.*|SESSION_DRIVER=redis|g" backend/.env
sed -i "s|QUEUE_CONNECTION=.*|QUEUE_CONNECTION=redis|g" backend/.env
sed -i "s|REDIS_HOST=.*|REDIS_HOST=redis|g" backend/.env

# Update frontend .env
cat > frontend/.env << EOF
VITE_API_BASE_URL=https://$DOMAIN/api
VITE_API_VERSION=v1
VITE_APP_NAME="Expense Tracker"
EOF

echo -e "${GREEN}✓ Environment configured${NC}"

echo ""
echo "🏗️  Step 7: Building and starting containers..."
docker-compose -f docker-compose.prod.yml up -d --build

echo ""
echo "⏳ Waiting for containers to be ready..."
sleep 10

echo ""
echo "🔑 Step 8: Initializing application..."

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

echo -e "${GREEN}✓ Application initialized${NC}"

echo ""
echo "🔐 Step 9: Setting up SSL certificate..."
sudo certbot --nginx -d $DOMAIN --non-interactive --agree-tos --email $EMAIL

echo ""
echo "🔥 Step 10: Configuring firewall..."
if command -v ufw &> /dev/null; then
    sudo ufw allow 80/tcp
    sudo ufw allow 443/tcp
    sudo ufw allow 22/tcp
    sudo ufw --force enable
    echo -e "${GREEN}✓ Firewall configured${NC}"
fi

echo ""
echo "✅ Deployment Complete!"
echo "======================="
echo ""
echo "🌐 Your application is now live at:"
echo -e "${GREEN}https://$DOMAIN${NC}"
echo ""
echo "📊 Health Check:"
echo "curl https://$DOMAIN/api/health"
echo ""
echo "🔑 Create your first user:"
echo "docker exec -it expense-tracker-app php artisan tinker"
echo 'Then run: $user = App\Models\User::create(["name"=>"Admin", "email"=>"admin@example.com", "password"=>bcrypt("password")]);'
echo ""
echo "📖 View logs:"
echo "docker-compose -f docker-compose.prod.yml logs -f"
echo ""
echo "🔄 To update in the future:"
echo "git pull && docker-compose -f docker-compose.prod.yml up -d --build"
echo ""
echo -e "${YELLOW}⚠️  Important: Save these credentials securely:${NC}"
echo "DB Password: $DB_PASSWORD"
echo "DB Root Password: $DB_ROOT_PASSWORD"
echo ""
echo "🎉 Happy tracking!"
