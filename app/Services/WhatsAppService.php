<?php

namespace App\Services;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Clinic;
use App\Models\Specialty;
use App\Models\Appointment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected $apiUrl;
    protected $accessToken;
    protected $conversationService;

    public function __construct(WhatsAppConversationService $conversationService)
    {
        $this->apiUrl = config('services.whatsapp.api_url');
        $this->accessToken = config('services.whatsapp.access_token');
        $this->conversationService = $conversationService;
    }

    /**
     * Processar mensagem recebida
     */
    public function processMessage($message, $fullPayload)
    {
        $phoneNumber = $message['from'];
        $messageText = $message['text']['body'] ?? '';
        $messageType = $message['type'];

        Log::info('Processando mensagem WhatsApp', [
            'phone' => $phoneNumber,
            'message' => $messageText,
            'type' => $messageType
        ]);

        // Apenas processar mensagens de texto por enquanto
        if ($messageType !== 'text') {
            $this->sendMessage($phoneNumber, 'Desculpe, no momento só consigo processar mensagens de texto. Como posso ajudá-lo?');
            return;
        }

        // Processar através do serviço de conversação
        $response = $this->conversationService->processUserMessage($phoneNumber, $messageText);
        
        if ($response) {
            $this->sendMessage($phoneNumber, $response);
        }
    }

    /**
     * Enviar mensagem via WhatsApp Business API
     */
    public function sendMessage($phoneNumber, $message, $type = 'text')
    {
        try {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $phoneNumber,
                'type' => $type,
                'text' => [
                    'body' => $message
                ]
            ];

            $response = Http::withToken($this->accessToken)
                ->post($this->apiUrl, $payload);

            if ($response->successful()) {
                Log::info('Mensagem WhatsApp enviada com sucesso', [
                    'phone' => $phoneNumber,
                    'message_preview' => substr($message, 0, 50) . '...'
                ]);
                return $response->json();
            } else {
                Log::error('Erro ao enviar mensagem WhatsApp', [
                    'phone' => $phoneNumber,
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);
                throw new \Exception('Falha ao enviar mensagem: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao enviar mensagem WhatsApp', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Enviar mensagem com botões interativos
     */
    public function sendInteractiveMessage($phoneNumber, $text, $buttons)
    {
        try {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $phoneNumber,
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => $text
                    ],
                    'action' => [
                        'buttons' => $buttons
                    ]
                ]
            ];

            $response = Http::withToken($this->accessToken)
                ->post($this->apiUrl, $payload);

            if ($response->successful()) {
                Log::info('Mensagem interativa WhatsApp enviada', [
                    'phone' => $phoneNumber,
                    'buttons_count' => count($buttons)
                ]);
                return $response->json();
            } else {
                Log::error('Erro ao enviar mensagem interativa', [
                    'phone' => $phoneNumber,
                    'response' => $response->body()
                ]);
                throw new \Exception('Falha ao enviar mensagem interativa');
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao enviar mensagem interativa', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Enviar lista de opções
     */
    public function sendListMessage($phoneNumber, $text, $buttonText, $sections)
    {
        try {
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $phoneNumber,
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'list',
                    'body' => [
                        'text' => $text
                    ],
                    'action' => [
                        'button' => $buttonText,
                        'sections' => $sections
                    ]
                ]
            ];

            $response = Http::withToken($this->accessToken)
                ->post($this->apiUrl, $payload);

            if ($response->successful()) {
                Log::info('Lista WhatsApp enviada', [
                    'phone' => $phoneNumber,
                    'sections_count' => count($sections)
                ]);
                return $response->json();
            } else {
                throw new \Exception('Falha ao enviar lista');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar lista WhatsApp', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}