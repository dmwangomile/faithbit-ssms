#!/bin/bash

# FaithBit SSMS Development Start Script
# Quick start for development environment

set -e

echo "=== FaithBit SSMS Development Environment ==="
echo ""

# Create .env file if it doesn't exist
if [ ! -f ".env" ]; then
    echo "Creating development .env file..."
    cp .env.example .env
    
    # Set development defaults
    sed -i 's/APP_ENV=development/APP_ENV=dev/' .env
    sed -i 's/APP_DEBUG=true/APP_DEBUG=1/' .env
    
    echo "✅ Environment file created"
fi

# Start services
echo "Starting development services..."
docker-compose up -d mysql redis minio

echo "Waiting for database to be ready..."
sleep 15

# Check if database exists and has tables
echo "Setting up database..."
docker-compose exec mysql mysql -uroot -proot_password -e "CREATE DATABASE IF NOT EXISTS faithbit_ssms;"

# Import schema and seed data
echo "Importing database schema..."
docker-compose exec mysql mysql -ufaithbit_user -pfaithbit_pass faithbit_ssms < database/schema.sql

echo "Importing seed data..."
docker-compose exec mysql mysql -ufaithbit_user -pfaithbit_pass faithbit_ssms < database/seed.sql

echo ""
echo "=== Development Environment Ready ==="
echo ""
echo "Services running:"
echo "- MySQL: localhost:3306"
echo "- Redis: localhost:6379" 
echo "- MinIO: http://localhost:9000"
echo ""
echo "Database credentials:"
echo "- Host: localhost:3306"
echo "- Database: faithbit_ssms"
echo "- Username: faithbit_user"
echo "- Password: faithbit_pass"
echo ""
echo "MinIO credentials:"
echo "- Access Key: faithbit_access"
echo "- Secret Key: faithbit_secret"
echo ""
echo "To start backend API:"
echo "cd backend && composer install && php yii serve --host=0.0.0.0 --port=8080"
echo ""
echo "To start frontend:"
echo "cd frontend && npm install && npm run serve"
echo ""
echo "Admin login:"
echo "Username: admin"
echo "Password: admin123"