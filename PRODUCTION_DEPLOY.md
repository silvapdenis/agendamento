# Laravel Medical Appointment System with n8n Integration

## Deploy Instructions

### Prerequisites
- Docker & Docker Compose
- PostgreSQL database
- Domain with SSL

### Environment Variables
```env
APP_NAME="Medical Appointment Bot"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=medical_appointments
DB_USERNAME=postgres
DB_PASSWORD=your_secure_password

N8N_HOST=0.0.0.0
N8N_PORT=5678
N8N_PROTOCOL=https
N8N_DOMAIN=your-domain.com
WEBHOOK_URL=https://your-domain.com
```

### Services Architecture
1. **Laravel API** - Port 8000
2. **n8n Workflow** - Port 5678  
3. **PostgreSQL** - Port 5432
4. **Nginx Proxy** - Port 80/443

### URLs Structure
- Laravel API: `https://your-domain.com/api`
- n8n Interface: `https://n8n.your-domain.com`
- WhatsApp Webhook: `https://your-domain.com/api/whatsapp/webhook`