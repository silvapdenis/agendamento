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
    freetype-dev \
    netcat-openbsd \
    curl

# Create app directory
WORKDIR /app

# Install n8n globally
RUN npm install -g n8n@1.21.1

# Create n8n user and directory (using existing node user)
RUN mkdir -p /home/node/.n8n && \
    chown -R node:node /home/node/.n8n

# Copy and set up startup script (as root before switching users)
COPY start-n8n.sh /home/node/start-n8n.sh
RUN chmod +x /home/node/start-n8n.sh && \
    chown node:node /home/node/start-n8n.sh

# Switch to node user
USER node

# Set environment variables
ENV N8N_HOST=0.0.0.0
ENV N8N_PORT=5678
ENV N8N_PROTOCOL=https
ENV N8N_PATH=/
ENV WEBHOOK_URL=https://n8n-production-9ab7.up.railway.app/
ENV N8N_BASIC_AUTH_ACTIVE=false
ENV N8N_USER_FOLDER=/home/node/.n8n
ENV N8N_ENCRYPTION_KEY=railway-custom-key
ENV DB_TYPE=sqlite
ENV N8N_DIAGNOSTICS_ENABLED=false
ENV N8N_LOG_LEVEL=info

# Set working directory
WORKDIR /home/node

# Expose port
EXPOSE 5678

# Start n8n with port validation
CMD ["/home/node/start-n8n.sh"]