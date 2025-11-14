# Guia de Configuração n8n para WhatsApp Medical Bot

Este guia explica como configurar o n8n para automatizar agendamentos médicos via WhatsApp.

## 📋 Pré-requisitos

- Node.js 20.19+ instalado (ou Docker)
- Laravel rodando (nosso sistema médico)
- WhatsApp Business API configurado
- n8n instalado

## ⚠️ Problemas Comuns

### Problema de Conectividade IPv6/IPv4

**IMPORTANTE**: No Windows, use sempre `127.0.0.1` em vez de `localhost` nas URLs do n8n. 

- ❌ **Errado**: `http://localhost:8000/api/n8n/specialties`
- ✅ **Correto**: `http://127.0.0.1:8000/api/n8n/specialties`

**Motivo**: O n8n pode ter problemas com resolução DNS localhost no Windows, causando erro "The service refused the connection".

### Problemas do Node.js

Se você tiver problemas com versões do Node.js:

1. **Use nvm-windows** para gerenciar versões:
   ```bash
   # Verificar versões disponíveis
   nvm list
   
   # Instalar versão compatível
   nvm install 20.19.5
   
   # Trocar para versão compatível
   nvm use 20.19.5
   ```

2. **Alternativa Docker** (mais confiável):
   Use a Opção 2 ou 3 de instalação abaixo.

## 🚀 Instalação do n8n

### Opção 1: Instalação Local (Recomendada para desenvolvimento)

```bash
# Instalar n8n globalmente
npm install n8n -g

# Ou usar npx (sem instalação global)
npx n8n
```

### Opção 2: Docker (Recomendada para produção)

```bash
# Criar pasta para dados do n8n
mkdir n8n-data

# Executar n8n via Docker
docker run -it --rm \
  --name n8n \
  -p 5678:5678 \
  -v n8n-data:/home/node/.n8n \
  n8nio/n8n
```

### Opção 3: Docker Compose (Mais completo)

Criar arquivo `docker-compose.yml`:

```yaml
version: '3.8'
services:
  n8n:
    image: n8nio/n8n
    ports:
      - "5678:5678"
    environment:
      - N8N_BASIC_AUTH_ACTIVE=true
      - N8N_BASIC_AUTH_USER=admin
      - N8N_BASIC_AUTH_PASSWORD=seu_password_aqui
      - WEBHOOK_URL=https://seu-dominio.com/
    volumes:
      - n8n_data:/home/node/.n8n
    restart: unless-stopped

volumes:
  n8n_data:
```

```bash
docker-compose up -d
```

## 🔧 Configuração Inicial

### 1. Iniciar n8n

**Opção A: Comando direto**
```bash
# Se instalou globalmente
n8n

# Ou se prefere usar npx
npx n8n
```

**Opção B: Se comando n8n não funciona (problema de PATH)**
```bash
# Encontrar onde n8n foi instalado
npm list -g n8n

# Ou usar npx (sempre funciona)
npx n8n

# Ou usar caminho completo (exemplo)
"C:\Users\[SEU_USUARIO]\AppData\Roaming\npm\n8n.cmd"
```

**Opção C: Docker (mais confiável)**
```bash
# Criar pasta para dados
mkdir n8n-data

# Executar n8n via Docker
docker run -it --rm --name n8n -p 5678:5678 -v n8n-data:/home/node/.n8n n8nio/n8n
```

**Aguarde até ver a mensagem:** `n8n ready on 0.0.0.0, port 5678`

### 2. Acessar n8n

Abra o navegador em: `http://localhost:5678`

### 3. Configurar Credenciais do WhatsApp

1. Vá em **Settings** → **Credentials**
2. Clique em **Add Credential**
3. Busque por "WhatsApp" ou "HTTP Request"
4. Configure:

```json
{
  "name": "WhatsApp Business API",
  "baseURL": "https://graph.facebook.com/v18.0",
  "headers": {
    "Authorization": "Bearer SEU_ACCESS_TOKEN_AQUI",
    "Content-Type": "application/json"
  }
}
```

### 4. Configurar Credenciais do Laravel

1. Adicione nova credencial "HTTP Request"
2. Configure:

```json
{
  "name": "Laravel Medical System",
  "baseURL": "http://127.0.0.1:8000/api",
  "headers": {
    "Content-Type": "application/json",
    "Accept": "application/json"
  }
}
```

## 📥 Importar Workflow

### 1. Importar o Workflow Base

1. No n8n, clique em **Import** (ícone de upload)
2. Selecione o arquivo `n8n-workflows/whatsapp-medical-bot.json`
3. Clique em **Import**

### 2. Configurar URLs nos Nós HTTP

Após importar, configure os seguintes nós:

#### Nó "Get Conversation State"
- URL: `http://127.0.0.1:8000/api/n8n/conversation-state`

#### Nó "Get Specialties"  
- URL: `http://127.0.0.1:8000/api/n8n/specialties`

#### Nó "Send WhatsApp Message"
- URL: `http://127.0.0.1:8000/api/n8n/send-whatsapp`

#### Nó "Update Conversation State"
- URL: `http://127.0.0.1:8000/api/n8n/conversation-state`

## 🔗 Configurar Webhook do WhatsApp

### 1. Obter URL do Webhook n8n

1. No workflow, clique no nó "WhatsApp Webhook"
2. Copie a "Webhook URL" (algo como: `http://localhost:5678/webhook/webhook-id`)

### 2. Configurar no WhatsApp Business

1. Acesse o Meta for Developers
2. Vá para seu app WhatsApp Business
3. Em **Webhooks**, configure:
   - **Callback URL**: `https://seu-dominio.com/webhook/webhook-id`
   - **Verify Token**: `seu_verify_token`
   - **Fields**: `messages`

## 🧪 Teste da Configuração

### 1. Testar Endpoints Laravel

```bash
# Testar se as APIs estão funcionando
curl http://127.0.0.1:8000/api/n8n/specialties
curl "http://127.0.0.1:8000/api/n8n/conversation-state?phone=5511999999999"
```

### 2. Testar Webhook n8n

```bash
# Simular webhook do WhatsApp
curl -X POST http://localhost:5678/webhook/seu-webhook-id \
  -H "Content-Type: application/json" \
  -d '{
    "entry": [{
      "changes": [{
        "value": {
          "messages": [{
            "from": "5511999999999",
            "type": "text",
            "text": {"body": "agendar consulta"},
            "id": "test123",
            "timestamp": "1634567890"
          }],
          "contacts": [{
            "profile": {"name": "Teste"}
          }]
        }
      }]
    }]
  }'
```

### 3. Verificar Logs

No n8n:
1. Vá em **Executions**
2. Verifique se há execuções bem-sucedidas
3. Clique em uma execução para ver detalhes

## 🔄 Fluxo Completo no n8n

### Estrutura do Workflow

```
WhatsApp Webhook
    ↓
Verificar se há mensagens
    ↓
Extrair dados da mensagem
    ↓
Verificar se é mensagem de texto
    ↓
Buscar estado da conversa (Laravel API)
    ↓
Processar baseado no estado atual
    ↓
Buscar dados necessários (Laravel API)
    ↓
Enviar resposta (WhatsApp API)
    ↓
Atualizar estado da conversa (Laravel API)
```

### Estados da Conversa

1. **initial**: Detectar intenção de agendamento
2. **waiting_specialty**: Processar seleção de especialidade
3. **waiting_doctor**: Processar seleção de médico
4. **waiting_clinic**: Processar seleção de clínica
5. **waiting_date**: Processar seleção de data
6. **waiting_time**: Processar seleção de horário
7. **waiting_patient_info**: Processar informações do paciente

## 📊 Monitoramento

### 1. Logs do n8n

- Acessível em: `http://localhost:5678/workflows/executions`
- Mostra todas as execuções e erros

### 2. Logs do Laravel

```bash
tail -f storage/logs/laravel.log | grep -i whatsapp
```

### 3. Webhook do WhatsApp

- Verificar no Meta for Developers se os webhooks estão sendo entregues

## 🔒 Segurança

### 1. Autenticação Básica n8n (Produção)

```bash
# Variáveis de ambiente
N8N_BASIC_AUTH_ACTIVE=true
N8N_BASIC_AUTH_USER=admin
N8N_BASIC_AUTH_PASSWORD=senha_forte_aqui
```

### 2. HTTPS (Produção)

- Configure SSL/TLS para n8n
- Use proxy reverso (Nginx/Apache)
- WhatsApp Business API requer HTTPS

### 3. Rate Limiting

- Configure rate limiting no Laravel
- Use middleware para proteger APIs n8n

## 🚀 Deploy para Produção

### 1. n8n em Servidor

```bash
# PM2 para gerenciar processo
npm install pm2 -g
pm2 start n8n --name "n8n-medical-bot"
pm2 startup
pm2 save
```

### 2. Variáveis de Ambiente

```bash
# .env do n8n
N8N_HOST=0.0.0.0
N8N_PORT=5678
N8N_PROTOCOL=https
WEBHOOK_URL=https://seu-dominio.com/
DB_TYPE=postgresdb  # Para produção
DB_POSTGRESDB_HOST=localhost
DB_POSTGRESDB_PORT=5432
DB_POSTGRESDB_DATABASE=n8n
DB_POSTGRESDB_USER=n8n
DB_POSTGRESDB_PASSWORD=password
```

## 🐛 Troubleshooting

### Problemas Comuns

1. **Webhook não recebe mensagens**
   - Verificar URL do webhook
   - Confirmar que n8n está acessível externamente
   - Verificar logs do WhatsApp Business

2. **Erro ao chamar APIs Laravel**
   - Verificar se Laravel está rodando
   - Confirmar URLs nas configurações n8n
   - Verificar logs do Laravel

3. **Mensagens não são enviadas**
   - Verificar token do WhatsApp Business
   - Confirmar phone number ID
   - Verificar quotas da API

### Comandos de Debug

```bash
# Testar conectividade
curl -I http://127.0.0.1:8000/api/n8n/specialties

# Verificar logs n8n
docker logs n8n

# Testar webhook
ngrok http 5678  # Para expor n8n publicamente para testes
```

## 📚 Próximos Passos

1. **Expandir Estados**: Adicionar cancelamento, reagendamento
2. **Lembretes**: Criar workflow para lembretes automáticos
3. **Analytics**: Adicionar tracking de conversas
4. **Templates**: Usar WhatsApp Message Templates
5. **Fallback**: Transferir para atendente humano

## 🔗 Links Úteis

- [Documentação n8n](https://docs.n8n.io/)
- [WhatsApp Business API](https://developers.facebook.com/docs/whatsapp)
- [n8n Community](https://community.n8n.io/)
- [Templates n8n](https://n8n.io/workflows/)