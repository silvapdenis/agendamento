#!/bin/bash

# Laravel startup script for production deployment
echo "🚀 Starting Medical Appointment Bot..."

# Wait for database to be ready
echo "⏳ Waiting for database..."
sleep 5

# Ensure .env exists (for Docker builds)
if [ ! -f .env ]; then
    echo "📄 Creating .env from example..."
    cp .env.example .env
fi

# Run package discovery (skipped during build)
echo "📦 Discovering packages..."
php artisan package:discover --ansi

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Seed database if needed
if [ "$APP_ENV" = "production" ] && [ "$SEED_DATABASE" = "true" ]; then
    echo "🌱 Seeding database..."
    php artisan db:seed --force
fi

# Clear and cache config
echo "⚡ Optimizing application..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chmod -R 755 storage bootstrap/cache

echo "✅ Application ready!"

# Set default port if not provided
PORT=${PORT:-8000}

echo "🌐 Starting server on port $PORT..."

# Use PHP built-in server (more reliable than artisan serve in containers)
exec php -S 0.0.0.0:$PORT -t public