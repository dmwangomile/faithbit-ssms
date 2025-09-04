#!/bin/bash

# FaithBit SSMS Deployment Script
# Usage: ./scripts/deploy.sh [environment]

set -e

# Configuration
ENVIRONMENT=${1:-development}
PROJECT_NAME="faithbit-ssms"
COMPOSE_FILE="docker-compose.yml"

echo "=== FaithBit SSMS Deployment Script ==="
echo "Environment: $ENVIRONMENT"
echo "Project: $PROJECT_NAME"
echo ""

# Check if docker and docker-compose are installed
if ! command -v docker &> /dev/null; then
    echo "Error: Docker is not installed"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "Error: Docker Compose is not installed"
    exit 1
fi

# Create environment file if it doesn't exist
if [ ! -f ".env" ]; then
    echo "Creating .env file from example..."
    cp .env.example .env
    echo "Please edit .env file with your configuration before running again"
    exit 1
fi

# Load environment variables
source .env

echo "Step 1: Building Docker images..."
docker-compose build --no-cache

echo "Step 2: Starting services..."
docker-compose up -d

echo "Step 3: Waiting for services to be ready..."
sleep 30

echo "Step 4: Installing backend dependencies..."
docker-compose exec backend composer install --no-dev --optimize-autoloader

echo "Step 5: Running database migrations..."
docker-compose exec backend php yii migrate --interactive=0

echo "Step 6: Seeding database with initial data..."
docker-compose exec mysql mysql -u${DB_USER} -p${DB_PASSWORD} ${DB_NAME} < /docker-entrypoint-initdb.d/02-seed.sql

echo "Step 7: Building frontend..."
docker-compose exec frontend npm install
docker-compose exec frontend npm run build

echo "Step 8: Setting up MinIO buckets..."
docker-compose exec minio mc alias set myminio http://localhost:9000 ${MINIO_ACCESS_KEY} ${MINIO_SECRET_KEY}
docker-compose exec minio mc mb myminio/${MINIO_BUCKET} --ignore-existing

echo "Step 9: Setting permissions..."
docker-compose exec backend chown -R www-data:www-data /var/www/html/runtime
docker-compose exec backend chown -R www-data:www-data /var/www/html/web/assets

echo "Step 10: Restarting services for final configuration..."
docker-compose restart

echo ""
echo "=== Deployment Complete ==="
echo ""
echo "Services are now running:"
echo "- Backend API: http://localhost:8080"
echo "- Frontend Web: http://localhost:3000"
echo "- MySQL Database: localhost:3306"
echo "- Redis Cache: localhost:6379"
echo "- MinIO Storage: http://localhost:9000 (Console: http://localhost:9001)"
echo ""
echo "Default admin credentials:"
echo "Username: admin"
echo "Password: admin123"
echo "Email: admin@faithbit.com"
echo ""
echo "To check service status: docker-compose ps"
echo "To view logs: docker-compose logs -f [service-name]"
echo "To stop services: docker-compose down"
echo ""

# Health check
echo "Performing health check..."
sleep 5

if curl -f http://localhost:8080/api/health > /dev/null 2>&1; then
    echo "✅ Backend API is healthy"
else
    echo "❌ Backend API health check failed"
fi

if curl -f http://localhost:3000 > /dev/null 2>&1; then
    echo "✅ Frontend is healthy"
else
    echo "❌ Frontend health check failed"
fi

echo ""
echo "🎉 FaithBit SSMS is now running successfully!"
echo "Visit http://localhost:3000 to access the system"