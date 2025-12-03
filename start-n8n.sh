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
export DB_TYPE=sqlite

echo "==================================="
echo "Starting n8n on port $N8N_PORT"
echo "N8N_HOST: $N8N_HOST"
echo "N8N_PROTOCOL: $N8N_PROTOCOL"
echo "N8N_EDITOR_BASE_URL: $N8N_EDITOR_BASE_URL"
echo "DB_TYPE: $DB_TYPE"
echo "==================================="

# Start n8n in the background
n8n start &
N8N_PID=$!

# Wait for n8n to be ready
echo "Waiting for n8n to start..."
sleep 5

# Check if n8n process is still running
if ! kill -0 $N8N_PID 2>/dev/null; then
    echo "ERROR: n8n failed to start!"
    exit 1
fi

echo "n8n started successfully with PID $N8N_PID"

# Keep the container running
wait $N8N_PID
