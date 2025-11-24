# Railway deployment using official n8n Docker image
FROM n8nio/n8n:1.21.1

# Set user as root temporarily to ensure permissions
USER root

# Create n8n directory with proper permissions
RUN mkdir -p /tmp/.n8n && chown -R node:node /tmp/.n8n

# Switch back to node user
USER node

# Set Railway environment variables
ENV N8N_HOST=0.0.0.0
ENV N8N_BASIC_AUTH_ACTIVE=true
ENV N8N_BASIC_AUTH_USER=admin
ENV N8N_BASIC_AUTH_PASSWORD=medico_bot_2025
ENV N8N_USER_FOLDER=/tmp/.n8n
ENV N8N_ENCRYPTION_KEY=railway-n8n-key

# Expose port (Railway will set this dynamically)
EXPOSE 5678

# Start n8n with Railway port
CMD sh -c "N8N_PORT=${PORT:-5678} n8n start"