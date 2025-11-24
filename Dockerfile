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

# Create n8n user and directory
RUN addgroup -g 1000 n8n && \
    adduser -D -s /bin/sh -u 1000 -G n8n n8n && \
    mkdir -p /home/n8n/.n8n && \
    chown -R n8n:n8n /home/n8n

# Switch to n8n user
USER n8n

# Set environment variables
ENV N8N_HOST=0.0.0.0
ENV N8N_PORT=5678
ENV N8N_BASIC_AUTH_ACTIVE=true
ENV N8N_BASIC_AUTH_USER=admin
ENV N8N_BASIC_AUTH_PASSWORD=medico_bot_2025
ENV N8N_USER_FOLDER=/home/n8n/.n8n
ENV N8N_ENCRYPTION_KEY=railway-custom-key

# Set working directory
WORKDIR /home/n8n

# Expose port
EXPOSE 5678

# Start n8n
CMD ["n8n", "start"]