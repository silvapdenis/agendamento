# Railway deployment using official n8n Docker image  
FROM n8nio/n8n:1.21.1

# Set user as root to install dependencies and setup
USER root

# Install required packages and create directories
RUN apk add --no-cache bash \
    && mkdir -p /home/node/.n8n \
    && chown -R node:node /home/node/.n8n

# Create startup script
RUN echo '#!/bin/bash' > /start.sh \
    && echo 'export N8N_HOST=0.0.0.0' >> /start.sh \
    && echo 'export N8N_PORT=${PORT:-5678}' >> /start.sh \
    && echo 'export N8N_BASIC_AUTH_ACTIVE=true' >> /start.sh \
    && echo 'export N8N_BASIC_AUTH_USER=admin' >> /start.sh \
    && echo 'export N8N_BASIC_AUTH_PASSWORD=medico_bot_2025' >> /start.sh \
    && echo 'export N8N_USER_FOLDER=/home/node/.n8n' >> /start.sh \
    && echo 'export N8N_ENCRYPTION_KEY=railway-n8n-key-2025' >> /start.sh \
    && echo 'echo "Starting n8n on 0.0.0.0:${PORT}"' >> /start.sh \
    && echo 'exec n8n start' >> /start.sh \
    && chmod +x /start.sh

# Switch back to node user
USER node

# Set working directory
WORKDIR /home/node

# Expose port
EXPOSE 5678

# Use the startup script
CMD ["/start.sh"]