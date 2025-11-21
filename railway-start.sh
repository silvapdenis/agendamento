#!/bin/bash

# Railway Laravel Initialization Script
set -e

echo "🚀 Initializing Laravel Application..."

# Add common PHP paths to PATH for Railway/Nixpacks
export PATH="/opt/php/bin:/usr/bin:/bin:/usr/local/bin:$PATH"

# Set default port
export PORT=${PORT:-8000}

# Debug environment
echo "📊 Environment Debug:"
echo "APP_KEY length: ${#APP_KEY}"
echo "PHP location: $(which php 2>/dev/null || echo 'PHP not found')"
echo "PHP version: $(php -v 2>/dev/null | head -1 || echo 'PHP not working')"

# Create .env file with correct APP_KEY
echo "📄 Setting up environment file..."
if [ -n "$APP_KEY" ]; then
    echo "APP_KEY=$APP_KEY" > .env
    echo "✅ APP_KEY set from Railway environment"
else
    echo "⚠️ No APP_KEY found in environment variables"
    echo "🔧 Generating new APP_KEY..."
    cp .env.example .env
    # Try to generate a new key
    if command -v php >/dev/null 2>&1; then
        NEW_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));" 2>/dev/null || echo "")
        if [ -n "$NEW_KEY" ]; then
            export APP_KEY="$NEW_KEY"
            echo "APP_KEY=$NEW_KEY" > .env
            echo "✅ Generated new APP_KEY: ${NEW_KEY:0:20}..."
        fi
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

# Clear all Laravel caches
echo "🧹 Clearing Laravel caches..."
if command -v php >/dev/null 2>&1; then
    php artisan config:clear 2>/dev/null || echo "⚠️ Config clear failed"
    php artisan cache:clear 2>/dev/null || echo "⚠️ Cache clear failed"
    php artisan route:clear 2>/dev/null || echo "⚠️ Route clear failed"
    php artisan view:clear 2>/dev/null || echo "⚠️ View clear failed"
else
    echo "❌ PHP not available, skipping cache clear"
fi

# Run database migrations
echo "🗄️ Running database migrations..."
if command -v php >/dev/null 2>&1; then
    php artisan migrate --force --no-interaction 2>/dev/null || echo "⚠️ Migration failed, continuing..."
    
    # Optimize Laravel
    echo "⚡ Optimizing Laravel..."
    php artisan config:cache 2>/dev/null || echo "⚠️ Config cache failed"
    php artisan route:cache 2>/dev/null || echo "⚠️ Route cache failed"
else
    echo "❌ PHP not available, skipping migrations and optimization"
fi

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