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

# Enable debug for troubleshooting
echo "🐛 Enabling Laravel debug mode..."
sed -i 's/APP_DEBUG=false/APP_DEBUG=true/g' .env
sed -i 's/LOG_LEVEL=error/LOG_LEVEL=debug/g' .env

# Wait for database
echo "⏳ Waiting for database..."
sleep 3

# Basic Laravel setup without problematic commands
echo "🔧 Setting up Laravel..."

# Generate app key - always ensure it exists
echo "🔑 Ensuring application key..."
if grep -q "APP_KEY=$" .env || [ -z "$APP_KEY" ]; then
    echo "🔑 Generating new application key..."
    php -r "
    \$key = 'base64:' . base64_encode(random_bytes(32));
    \$env = file_get_contents('.env');
    \$env = preg_replace('/APP_KEY=.*/', 'APP_KEY=' . \$key, \$env);
    file_put_contents('.env', \$env);
    echo 'Key generated: ' . \$key . PHP_EOL;
    "
else
    echo "🔑 Application key already set"
fi

# Run migrations safely
echo "🗄️ Running migrations..."
php artisan migrate --force 2>/dev/null || echo "Migration skipped"

# Set permissions
chmod -R 755 storage bootstrap/cache 2>/dev/null || true

echo "✅ Starting server on port $PORT..."

# Start PHP server directly
exec php -S 0.0.0.0:$PORT server.php