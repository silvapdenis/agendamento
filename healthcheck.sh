#!/bin/sh
# Simple healthcheck script for Railway

MAX_RETRIES=30
RETRY_INTERVAL=2

echo "Healthcheck: Waiting for n8n to be ready..."

for i in $(seq 1 $MAX_RETRIES); do
    if curl -f http://localhost:${N8N_PORT:-8080}/ > /dev/null 2>&1; then
        echo "✓ n8n is ready!"
        exit 0
    fi
    echo "Attempt $i/$MAX_RETRIES: n8n not ready yet, waiting ${RETRY_INTERVAL}s..."
    sleep $RETRY_INTERVAL
done

echo "✗ n8n failed to become ready after $MAX_RETRIES attempts"
exit 1
