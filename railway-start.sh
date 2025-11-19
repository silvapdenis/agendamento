#!/bin/bash

# Railway Laravel Initialization Script
set -e

echo "🚀 Initializing Laravel Application..."

# Add common paths for Railway environment
export PATH="/opt/php/bin:/usr/bin:/bin:/usr/local/bin:$PATH"

# Set default port
export PORT=${PORT:-8000}

# Debug environment
echo "📊 Environment Debug:"
echo "PHP location: $(which php || echo 'PHP not found')"
echo "APP_KEY exists: $( [ -n "$APP_KEY" ] && echo 'YES' || echo 'NO' )"
echo "APP_KEY length: ${#APP_KEY}"

# Create .env file with correct APP_KEY
echo "📄 Setting up environment file..."
if [ -n "$APP_KEY" ]; then
    echo "APP_KEY=$APP_KEY" > .env
    echo "✅ APP_KEY set from Railway environment"
else
    echo "⚠️ No APP_KEY found in environment variables"
    echo "🔧 Generating new APP_KEY..."
    # Try to generate a new key using PHP if available
    if command -v php > /dev/null 2>&1; then
        NEW_KEY="base64:$(openssl rand -base64 32)"
        echo "APP_KEY=$NEW_KEY" > .env
        echo "✅ Generated new APP_KEY"
    else
        echo "❌ PHP not available, copying .env.example"
        cp .env.example .env
    fi
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

# Clear all Laravel caches (only if PHP is available)
if command -v php > /dev/null 2>&1; then
    echo "🧹 Clearing Laravel caches..."
    php artisan config:clear || echo "⚠️ Config clear failed"
    php artisan cache:clear || echo "⚠️ Cache clear failed"
    php artisan route:clear || echo "⚠️ Route clear failed"
    php artisan view:clear || echo "⚠️ View clear failed"

    # Run database migrations
    echo "🗄️ Running database migrations..."
    php artisan migrate --force --no-interaction 2>/dev/null || echo "⚠️ Migration failed, continuing..."

    # Optimize Laravel
    echo "⚡ Optimizing Laravel..."
    php artisan config:cache || echo "⚠️ Config cache failed"
else
    echo "⚠️ PHP not available, skipping Laravel commands"
fi
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