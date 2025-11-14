#!/bin/bash
set -e

echo "🚀 Starting Medical Appointment Bot..."

# Set default port
PORT=${PORT:-8000}

# Ensure .env exists
if [ ! -f .env ]; then
    echo "📄 Creating .env from example..."
    cp .env.example .env
fi

# Wait for database
echo "⏳ Waiting for database..."
sleep 3

# Basic Laravel setup without problematic commands
echo "🔧 Setting up Laravel..."

# Generate app key if needed
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php -r "
    require_once 'vendor/autoload.php';
    \$app = require_once 'bootstrap/app.php';
    \$key = 'base64:' . base64_encode(random_bytes(32));
    file_put_contents('.env', str_replace('APP_KEY=', 'APP_KEY=' . \$key, file_get_contents('.env')));
    echo 'Key generated: ' . \$key . PHP_EOL;
    "
fi

# Run migrations safely
echo "🗄️ Running migrations..."
php artisan migrate --force 2>/dev/null || echo "Migration skipped"

# Set permissions
chmod -R 755 storage bootstrap/cache 2>/dev/null || true

echo "✅ Starting server on port $PORT..."

# Start PHP server directly
exec php -S 0.0.0.0:$PORT server.php