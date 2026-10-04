#!/bin/bash
set -e

echo "=================================================="
echo "🚀 DEPLOYING FUZURRA ERP TO PRODUCTION"
echo "=================================================="

# 1. Maintenance Mode
echo "Step 1: Enabling maintenance mode..."
php artisan down || true

# 2. Pull Latest Code
echo "Step 2: Pulling latest changes from GitHub..."
git pull origin main

# 3. Dependencies
echo "Step 3: Installing optimized Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Database Migrations & Seeds
echo "Step 4: Running database migrations and seeding..."
php artisan migrate --force

# 5. Production Optimization Caches
echo "Step 5: Warming up production configuration and route caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Bring Application Live
echo "Step 6: Bringing application back online..."
php artisan up

echo "=================================================="
echo "✅ FUZURRA ERP IS LIVE AND RUNNING AT PEAK PERFORMANCE!"
echo "=================================================="
