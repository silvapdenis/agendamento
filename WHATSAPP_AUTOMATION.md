# Sistema de Agendamento via WhatsApp

Este sistema permite que pacientes agendem consultas médicas através do WhatsApp Business API de forma automatizada e conversacional.

## Características

- ✅ Fluxo conversacional intuitivo
- ✅ Integração com agendas específicas por clínica
- ✅ Verificação de disponibilidade em tempo real
- ✅ Confirmação automática de agendamentos
- ✅ Suporte a múltiplas especialidades e clínicas
- ✅ Gerenciamento de estado de conversas
- ✅ Limpeza automática de conversas antigas

## Fluxo de Conversa

1. **Inicialização**: Paciente digita palavras-chave como "agendar", "consulta", "médico"
2. **Seleção de Especialidade**: Lista numerada com especialidades disponíveis
3. **Seleção de Médico**: Lista de médicos da especialidade escolhida
4. **Seleção de Clínica**: Clínicas onde o médico atende
5. **Seleção de Data**: Próximas datas disponíveis (até 30 dias)
6. **Seleção de Horário**: Horários livres baseados na agenda específica
7. **Informações do Paciente**: Nome completo
8. **Confirmação**: Agendamento criado com número de protocolo

## Configuração

### 1. Variáveis de Ambiente

Adicione as seguintes variáveis ao seu arquivo `.env`:

```env
# WhatsApp Business API
WHATSAPP_API_URL=https://graph.facebook.com/v18.0/SEU_PHONE_NUMBER_ID/messages
WHATSAPP_ACCESS_TOKEN=seu_token_de_acesso
WHATSAPP_VERIFY_TOKEN=seu_token_de_verificacao_customizado
WHATSAPP_PHONE_NUMBER_ID=seu_phone_number_id
```

### 2. Configuração do Webhook

Configure o webhook do WhatsApp Business API para apontar para:
- **URL de Verificação**: `GET https://seu-dominio.com/api/whatsapp/webhook`
- **URL de Recebimento**: `POST https://seu-dominio.com/api/whatsapp/webhook`
- **Token de Verificação**: O valor definido em `WHATSAPP_VERIFY_TOKEN`

### 3. Permissões Necessárias

Certifique-se de que sua aplicação WhatsApp Business tenha as seguintes permissões:
- `whatsapp_business_messaging`
- `whatsapp_business_management`

## Endpoints da API

### Webhook do WhatsApp
- `GET /api/whatsapp/webhook` - Verificação do webhook
- `POST /api/whatsapp/webhook` - Recebimento de mensagens

### Envio de Mensagens (Protegido por Autenticação)
- `POST /api/whatsapp/send-message` - Enviar mensagem manual

## Estrutura do Banco de Dados

### Tabela: whats_app_conversations
```sql
- id: ID único da conversa
- phone_number: Número de telefone do usuário (único)
- state: Estado atual da conversa (enum)
- context: Dados temporários da conversa (JSON)
- last_message_at: Timestamp da última mensagem
- created_at/updated_at: Timestamps de controle
```

### Estados de Conversa
- `initial`: Estado inicial/boas-vindas
- `waiting_specialty`: Aguardando seleção de especialidade
- `waiting_doctor`: Aguardando seleção de médico
- `waiting_clinic`: Aguardando seleção de clínica
- `waiting_date`: Aguardando seleção de data
- `waiting_time`: Aguardando seleção de horário
- `waiting_patient_info`: Aguardando informações do paciente
- `waiting_confirmation`: Aguardando confirmação final

## Comandos Artisan

### Limpeza de Conversas
```bash
# Limpar conversas antigas (padrão: 7 dias)
php artisan whatsapp:cleanup

# Limpar conversas antigas (personalizado: 30 dias)
php artisan whatsapp:cleanup --days=30
```

## Integração com o Sistema

O sistema utiliza:
- **DoctorSchedule**: Para verificar agendas por clínica/dia
- **Doctor/Specialty/Clinic**: Para listar opções disponíveis
- **Appointment**: Para criar agendamentos
- **User**: Para criar/buscar pacientes

## Exemplo de Conversa

```
👤 Usuário: agendar consulta
🤖 Bot: 🏥 Agendamento de Consulta

Para agendar sua consulta, preciso de algumas informações.

Especialidades disponíveis:
1. Cardiologia
2. Dermatologia
3. Pediatria

📝 Digite o número da especialidade desejada:

👤 Usuário: 1
🤖 Bot: 👨‍⚕️ Médicos disponíveis em Cardiologia:

1. Dr(a). João Silva
2. Dr(a). Maria Santos

📝 Digite o número do médico desejado:

👤 Usuário: 1
🤖 Bot: 🏥 Clínicas onde Dr(a). João Silva atende:

1. Clínica Central
   📍 Rua Principal, 123, São Paulo

2. Clínica Norte  
   📍 Av. Norte, 456, São Paulo

📝 Digite o número da clínica desejada:

👤 Usuário: 1
🤖 Bot: 📅 Datas disponíveis:

1. 17/10/2025 (Quinta-feira)
2. 18/10/2025 (Sexta-feira)
3. 21/10/2025 (Segunda-feira)

📝 Digite o número da data desejada:

👤 Usuário: 1
🤖 Bot: 🕐 Horários disponíveis para 17/10/2025:

1. 08:00
2. 09:00
3. 10:00
4. 14:00

📝 Digite o número do horário desejado:

👤 Usuário: 2
🤖 Bot: 📋 Resumo do agendamento:

👨‍⚕️ Médico: Dr(a). João Silva
🏥 Especialidade: Cardiologia
📍 Clínica: Clínica Central
📅 Data: 17/10/2025
🕐 Horário: 09:00

Para confirmar o agendamento, preciso do seu nome completo:

👤 Usuário: Maria da Silva
🤖 Bot: ✅ Agendamento confirmado!

📋 Número do agendamento: #123
👤 Paciente: Maria da Silva
👨‍⚕️ Médico: Dr(a). João Silva
📍 Clínica: Clínica Central
📅 Data: 17/10/2025 09:00

🔔 Você receberá um lembrete 24 horas antes da consulta.

Para fazer um novo agendamento, digite agendar consulta.
```

## Monitoramento e Logs

Todos os eventos importantes são registrados nos logs do Laravel:
- Mensagens recebidas
- Erros de processamento
- Agendamentos criados
- Falhas de comunicação com WhatsApp API

## Segurança

- Webhook protegido por token de verificação
- Validação de payload do WhatsApp
- Rate limiting implícito via WhatsApp Business API
- Logs detalhados para auditoria

## Próximas Funcionalidades

- [ ] Lembretes automáticos 24h antes da consulta
- [ ] Cancelamento/reagendamento via WhatsApp
- [ ] Consulta de agendamentos existentes
- [ ] Integração com sistema de pagamentos
- [ ] Templates de mensagem personalizáveis
- [ ] Dashboard de analytics das conversas