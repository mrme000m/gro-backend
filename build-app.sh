#!/bin/bash
# Railway build script for GroFresh Laravel application
# Make sure this file has executable permissions: chmod +x build-app.sh

set -e

echo "=== Building GroFresh Application for Railway ==="

# Install Node.js dependencies
echo "Installing Node.js dependencies..."
npm install

# Build frontend assets (with error handling)
echo "Building frontend assets..."
if NODE_OPTIONS="--openssl-legacy-provider" npm run production; then
    echo "Frontend assets built successfully"
else
    echo "Warning: Frontend asset build failed, continuing with backend-only deployment"
fi

# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Clear any existing caches
echo "Clearing Laravel caches..."
php artisan optimize:clear

# Cache Laravel configurations for production
echo "Caching Laravel configurations..."
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

# Create storage link
echo "Creating storage link..."
php artisan storage:link

echo "=== Build Complete ==="
