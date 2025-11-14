#!/bin/bash

# Deploy Script for Medical Appointment Bot
# Automated deployment to production

echo "🚀 Starting Medical Appointment Bot Deployment..."

# 1. Environment Setup
echo "📋 Setting up environment..."
cp .env.production .env

# Generate secure APP_KEY
php artisan key:generate --force

# 2. Database Setup
echo "🗄️ Setting up database..."
php artisan migrate --force
php artisan db:seed --force

# 3. Cache Optimization
echo "⚡ Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload --optimize

# 4. Import n8n Workflows
echo "🔄 Setting up n8n workflows..."
if [ -d "n8n-workflows" ]; then
    echo "n8n workflows found, importing..."
    # Workflows will be mounted in n8n container
fi

# 5. Start Services
echo "🐳 Starting Docker services..."
docker-compose -f docker-compose.prod.yml up -d

# 6. Health Check
echo "🏥 Running health checks..."
sleep 30

# Check Laravel API
if curl -f http://localhost:8000/api/health; then
    echo "✅ Laravel API is healthy"
else
    echo "❌ Laravel API health check failed"
fi

# Check n8n
if curl -f http://localhost:5678/healthz; then
    echo "✅ n8n is healthy"
else
    echo "❌ n8n health check failed"
fi

echo "🎉 Deployment complete!"
echo "📝 Next steps:"
echo "   1. Configure domain DNS"
echo "   2. Set up SSL certificates"
echo "   3. Update WhatsApp webhook URL"
echo "   4. Test WhatsApp Business integration"

# Display important URLs
echo ""
echo "🌐 Important URLs:"
echo "   Laravel API: https://your-domain.com/api"
echo "   n8n Interface: https://n8n.your-domain.com"
echo "   WhatsApp Webhook: https://your-domain.com/api/whatsapp/webhook"