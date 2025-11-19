#!/bin/bash

# Railway Laravel Initialization Script
set -e

echo "🚀 Initializing Laravel Application..."

# Set default port
export PORT=${PORT:-8000}

# Create .env file with correct APP_KEY
echo "📄 Setting up environment file..."
if [ -n "$APP_KEY" ]; then
    echo "APP_KEY=$APP_KEY" > .env
    echo "✅ APP_KEY set from Railway environment"
else
    echo "⚠️ No APP_KEY found in environment variables"
    cp .env.example .env
fi

# Append other environment variables from .env.example (excluding APP_KEY)
grep -v "^APP_KEY=" .env.example >> .env 2>/dev/null || true

# Override with Railway environment variables
cat << EOF >> .env
APP_ENV=production
APP_DEBUG=\${APP_DEBUG:-true}
APP_URL=\${RAILWAY_STATIC_URL:-http://localhost:$PORT}

# Database (Railway PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=\${PGHOST:-127.0.0.1}
DB_PORT=\${PGPORT:-5432}
DB_DATABASE=\${PGDATABASE:-laravel}
DB_USERNAME=\${PGUSER:-laravel}
DB_PASSWORD=\${PGPASSWORD:-}

# WhatsApp Configuration
WHATSAPP_VERIFY_TOKEN=medico_bot_2025
N8N_WEBHOOK_URL=\${N8N_WEBHOOK_URL:-http://localhost:5678/webhook/appointment-notification}
EOF

# Clear all Laravel caches
echo "🧹 Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force --no-interaction 2>/dev/null || echo "⚠️ Migration failed, continuing..."

# Optimize Laravel
echo "⚡ Optimizing Laravel..."
php artisan config:cache
php artisan route:cache

# Verify APP_KEY
APP_KEY_CHECK=$(php artisan tinker --execute="echo config('app.key');" 2>/dev/null || echo "ERROR")
if [[ "$APP_KEY_CHECK" == *"base64:"* ]]; then
    echo "✅ APP_KEY verified: ${APP_KEY_CHECK:0:20}..."
else
    echo "❌ APP_KEY verification failed: $APP_KEY_CHECK"
fi

# Set permissions
chmod -R 755 storage bootstrap/cache

echo "✅ Laravel initialized successfully!"
echo "🌐 Starting server on port $PORT..."

# Start PHP server
exec php -S 0.0.0.0:$PORT -t public