# Railway deployment using official n8n Docker image
FROM n8nio/n8n:1.21.1

# Set environment variables directly
ENV N8N_HOST=0.0.0.0
ENV N8N_BASIC_AUTH_ACTIVE=true
ENV N8N_BASIC_AUTH_USER=admin  
ENV N8N_BASIC_AUTH_PASSWORD=medico_bot_2025
ENV N8N_USER_FOLDER=/home/node/.n8n
ENV N8N_ENCRYPTION_KEY=railway-simple-key

# Set user as root temporarily
USER root

# Create n8n directory and set permissions
RUN mkdir -p /home/node/.n8n && chown -R node:node /home/node/.n8n

# Switch back to node user  
USER node

# Set working directory
WORKDIR /home/node

# Expose port
EXPOSE 5678

# Start n8n directly with port from environment
CMD ["sh", "-c", "N8N_PORT=${PORT:-5678} n8n start"]