<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Verificação do webhook do WhatsApp
     */
    public function verify(Request $request)
    {
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        // Verificar se o token está correto
        if ($mode === 'subscribe' && $token === config('services.whatsapp.verify_token')) {
            Log::info('WhatsApp webhook verificado com sucesso');
            return response($challenge, 200);
        }

        Log::warning('Falha na verificação do webhook WhatsApp', [
            'mode' => $mode,
            'token' => $token
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Receber mensagens do WhatsApp
     */
    public function webhook(Request $request)
    {
        Log::info('Mensagem WhatsApp recebida', ['payload' => $request->all()]);

        try {
            $body = $request->all();

            // Verificar se há mensagens
            if (isset($body['entry'][0]['changes'][0]['value']['messages'])) {
                $messages = $body['entry'][0]['changes'][0]['value']['messages'];
                
                foreach ($messages as $message) {
                    $this->whatsAppService->processMessage($message, $body);
                }
            }

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao processar mensagem WhatsApp', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Enviar mensagem via WhatsApp (para testes)
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string'
        ]);

        try {
            $result = $this->whatsAppService->sendMessage(
                $request->phone,
                $request->message
            );

            return response()->json(['status' => 'sent', 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}