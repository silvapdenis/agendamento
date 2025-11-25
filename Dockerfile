# Build n8n from scratch using Node.js
FROM node:18-alpine

# Install required packages
RUN apk add --no-cache \
    python3 \
    py3-pip \
    build-base \
    cairo-dev \
    jpeg-dev \
    pango-dev \
    musl-dev \
    giflib-dev \
    pixman-dev \
    pangomm-dev \
    libjpeg-turbo-dev \
    freetype-dev

# Create app directory
WORKDIR /app

# Install n8n globally
RUN npm install -g n8n@1.21.1

# Create n8n user and directory (using existing node user)
RUN mkdir -p /home/node/.n8n && \
    chown -R node:node /home/node/.n8n

# Switch to node user
USER node

# Set environment variables
ENV N8N_HOST=0.0.0.0
ENV N8N_PORT=5678
ENV N8N_BASIC_AUTH_ACTIVE=true
ENV N8N_BASIC_AUTH_USER=admin
ENV N8N_BASIC_AUTH_PASSWORD=medico_bot_2025
ENV N8N_USER_FOLDER=/home/node/.n8n
ENV N8N_ENCRYPTION_KEY=railway-custom-key

# Create a startup script to handle port validation
RUN echo '#!/bin/sh' > /start.sh && \
    echo 'if [ -z "$PORT" ] || [ "$PORT" = "undefined" ]; then' >> /start.sh && \
    echo '  export N8N_PORT=5678' >> /start.sh && \
    echo 'else' >> /start.sh && \
    echo '  export N8N_PORT=$PORT' >> /start.sh && \
    echo 'fi' >> /start.sh && \
    echo 'echo "Starting n8n on port $N8N_PORT"' >> /start.sh && \
    echo 'exec n8n start' >> /start.sh && \
    chmod +x /start.sh

# Set working directory
WORKDIR /home/node

# Expose port
EXPOSE 5678

# Start n8n with port validation
CMD ["/start.sh"]