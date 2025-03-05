#!/bin/bash
set -e

echo "Starting custom build process"

# Disable npm ci completely
export NPM_CONFIG_PREFER_INSTALL=install

# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Install Node.js dependencies with npm install instead of npm ci
echo "Installing Node.js dependencies..."
npm install --no-audit --no-fund --legacy-peer-deps

# Build frontend
echo "Building frontend..."
npm run build

# Clear Laravel caches and prepare for production
echo "Preparing Laravel for production..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan storage:link
php artisan migrate --force

echo "Build completed successfully!"