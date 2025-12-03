#!/bin/sh

echo "==================================="
echo "DEBUG: Environment Variables"
echo "==================================="
echo "PORT: $PORT"
echo "RAILWAY_ENVIRONMENT: $RAILWAY_ENVIRONMENT"
echo "RAILWAY_STATIC_URL: $RAILWAY_STATIC_URL"

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
export N8N_LOG_LEVEL=debug
export N8N_LOG_OUTPUT=console

echo "==================================="
echo "Starting n8n on port $N8N_PORT"
echo "N8N_HOST: $N8N_HOST"
echo "N8N_PROTOCOL: $N8N_PROTOCOL"
echo "N8N_EDITOR_BASE_URL: $N8N_EDITOR_BASE_URL"
echo "DB_TYPE: $DB_TYPE"
echo "==================================="

# Test if we can bind to the port
echo "Testing port binding..."
nc -l -p $N8N_PORT &
NC_PID=$!
sleep 1
if kill -0 $NC_PID 2>/dev/null; then
    echo "✓ Port $N8N_PORT is available"
    kill $NC_PID 2>/dev/null
else
    echo "✗ Cannot bind to port $N8N_PORT"
fi

echo "Starting n8n process..."

# Start n8n in background
n8n start &
N8N_PID=$!

# Wait for n8n to be ready
echo "Waiting for n8n to respond on port $N8N_PORT..."
MAX_WAIT=60
WAITED=0

while [ $WAITED -lt $MAX_WAIT ]; do
    if curl -f http://localhost:$N8N_PORT/ > /dev/null 2>&1; then
        echo "✓ n8n is responding on port $N8N_PORT!"
        break
    fi
    sleep 2
    WAITED=$((WAITED + 2))
    echo "Waited ${WAITED}s for n8n..."
done

if [ $WAITED -ge $MAX_WAIT ]; then
    echo "✗ n8n did not become ready in time"
    exit 1
fi

echo "✓ n8n startup complete, container is ready"

# Keep container alive
wait $N8N_PID
