#!/bin/bash

# N8N Initialization Script for Railway

echo "🚀 Starting N8N for Medical Appointment System..."

# Set environment variables
export N8N_HOST=0.0.0.0
export N8N_PORT=${PORT:-5678}
export N8N_PROTOCOL=https
export GENERIC_TIMEZONE=America/Sao_Paulo
export TZ=America/Sao_Paulo

# Database configuration
export DB_TYPE=mysqldb
export DB_MYSQLDB_HOST=${MYSQLHOST}
export DB_MYSQLDB_PORT=${MYSQLPORT}
export DB_MYSQLDB_DATABASE=${MYSQLDATABASE}
export DB_MYSQLDB_USER=${MYSQLUSER}
export DB_MYSQLDB_PASSWORD=${MYSQLPASSWORD}

# Security
export N8N_BASIC_AUTH_ACTIVE=true
export N8N_BASIC_AUTH_USER=admin
export N8N_BASIC_AUTH_PASSWORD=medico_bot_2025

# Webhook configuration
export N8N_WEBHOOK_URL=https://${RAILWAY_STATIC_URL}/webhook
export N8N_EDITOR_BASE_URL=https://${RAILWAY_STATIC_URL}

echo "✅ Environment configured"
echo "📡 Webhook URL: ${N8N_WEBHOOK_URL}"
echo "🌐 Editor URL: ${N8N_EDITOR_BASE_URL}"
echo "🔐 Auth: admin / medico_bot_2025"

# Start N8N
echo "🔥 Starting N8N..."
n8n start