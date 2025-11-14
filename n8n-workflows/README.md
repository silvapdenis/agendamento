# Configuração dos Workflows n8n

## Fluxo de Notificações WhatsApp

Este workflow automatiza o envio de notificações via WhatsApp para os pacientes em diferentes momentos:

### 1. Confirmação de Agendamento
- Enviado quando uma consulta é confirmada
- Inclui detalhes da consulta (data, médico, clínica, endereço)
- Instruções para o paciente

### 2. Lembrete de Consulta
- Enviado 24h antes da consulta
- Lembra o paciente sobre a consulta no dia seguinte
- Inclui instruções sobre documentos

### 3. Cancelamento de Consulta
- Enviado quando uma consulta é cancelada
- Informa o motivo do cancelamento
- Oferece opção para reagendamento

## Como Configurar

### 1. Instalar n8n
```bash
npm install n8n -g
```

### 2. Configurar Webhook no Laravel
Adicione esta rota no seu `routes/api.php`:

```php
Route::post('/webhook/n8n/notifications', function(Request $request) {
    // Processar webhook do n8n
    return response()->json(['status' => 'received']);
});
```

### 3. Configurar WhatsApp Business API
- Obter credenciais da API do WhatsApp Business
- Configurar webhook URL: `http://your-domain.com/webhook/appointment-notification`

### 4. Importar Workflow
1. Copie o conteúdo do arquivo `whatsapp-notifications.json`
2. Acesse o n8n em `http://localhost:5678`
3. Importe o workflow
4. Configure as credenciais do WhatsApp/Telegram

### 5. Integrar com Laravel

Crie um serviço para enviar notificações:

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class N8nNotificationService
{
    protected $webhookUrl;

    public function __construct()
    {
        $this->webhookUrl = config('services.n8n.webhook_url');
    }

    public function sendAppointmentNotification($appointment, $type)
    {
        $data = [
            'notification_type' => $type,
            'notification_id' => uniqid(),
            'patient_phone' => $appointment->patient->phone,
            'patient_name' => $appointment->patient->name,
            'doctor_name' => $appointment->doctor->user->name,
            'clinic_name' => $appointment->clinic->name,
            'clinic_address' => $appointment->clinic->address,
            'appointment_date' => $appointment->appointment_date->format('d/m/Y H:i'),
            'cancellation_reason' => $appointment->cancellation_reason ?? '',
            'webhook_response_url' => route('webhook.n8n.response')
        ];

        return Http::post($this->webhookUrl, $data);
    }
}
```

### 6. Usar no Controller
```php
use App\Services\N8nNotificationService;

class AppointmentController extends Controller
{
    public function store(Request $request, N8nNotificationService $notificationService)
    {
        // ... criar agendamento ...

        // Enviar notificação
        $notificationService->sendAppointmentNotification(
            $appointment, 
            'appointment_confirmed'
        );

        return response()->json($appointment);
    }
}
```

## Variáveis de Ambiente

Adicione no `.env`:

```env
N8N_WEBHOOK_URL=http://localhost:5678/webhook/appointment-notification
WHATSAPP_API_TOKEN=your-whatsapp-token
WHATSAPP_PHONE_NUMBER_ID=your-phone-number-id
```