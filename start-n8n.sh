#!/bin/sh

# Validate PORT environment variable and set N8N_PORT
if [ -z "$PORT" ] || [ "$PORT" = "undefined" ]; then
  export N8N_PORT=5678
  echo "PORT not defined or invalid, using default port 5678"
else
  export N8N_PORT=$PORT
  echo "Using PORT from environment: $N8N_PORT"
fi

# Configure n8n to work behind Railway proxy
export N8N_HOST=0.0.0.0
export N8N_PROTOCOL=https
export N8N_EDITOR_BASE_URL=https://n8n-production-9ab7.up.railway.app
export WEBHOOK_URL=https://n8n-production-9ab7.up.railway.app/

echo "Starting n8n on port $N8N_PORT"
echo "N8N_HOST: $N8N_HOST"
echo "N8N_PROTOCOL: $N8N_PROTOCOL"
echo "N8N_EDITOR_BASE_URL: $N8N_EDITOR_BASE_URL"

exec n8n start
