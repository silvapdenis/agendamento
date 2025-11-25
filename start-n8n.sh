#!/bin/sh

# Validate PORT environment variable and set N8N_PORT
if [ -z "$PORT" ] || [ "$PORT" = "undefined" ]; then
  export N8N_PORT=5678
  echo "PORT not defined or invalid, using default port 5678"
else
  export N8N_PORT=$PORT
  echo "Using PORT from environment: $N8N_PORT"
fi

echo "Starting n8n on port $N8N_PORT"
exec n8n start

# Set environment variables
export N8N_HOST=0.0.0.0
export N8N_PORT=${PORT:-5678}
export N8N_PROTOCOL=https
export GENERIC_TIMEZONE=America/Sao_Paulo
export TZ=America/Sao_Paulo

# Security
export N8N_BASIC_AUTH_ACTIVE=true
export N8N_BASIC_AUTH_USER=admin
export N8N_BASIC_AUTH_PASSWORD=medico_bot_2025
export N8N_ENCRYPTION_KEY=n8n-encryption-key-medico-bot-2025

# Webhook configuration
export WEBHOOK_URL=https://${RAILWAY_STATIC_URL}/webhook
export N8N_EDITOR_BASE_URL=https://${RAILWAY_STATIC_URL}

# Logging
export N8N_LOG_LEVEL=debug

echo "✅ Environment configured"
echo "📡 Webhook URL: ${WEBHOOK_URL}"
echo "🌐 Editor URL: ${N8N_EDITOR_BASE_URL}"
echo "🔐 Auth: admin / medico_bot_2025"
echo "📁 User Folder: ${N8N_USER_FOLDER}"

# Start N8N with explicit configuration
echo "🔥 Starting N8N..."
n8n start --host=0.0.0.0 --port=${PORT}