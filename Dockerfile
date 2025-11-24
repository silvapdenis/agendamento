# Railway deployment using direct Docker approach
FROM n8nio/n8n:1.21.1

# Set Railway port
ENV N8N_PORT=$PORT
ENV N8N_HOST=0.0.0.0

# Basic auth
ENV N8N_BASIC_AUTH_ACTIVE=true  
ENV N8N_BASIC_AUTH_USER=admin
ENV N8N_BASIC_AUTH_PASSWORD=medico_bot_2025

# Storage
ENV N8N_USER_FOLDER=/tmp/.n8n

EXPOSE $PORT

CMD ["n8n", "start"]