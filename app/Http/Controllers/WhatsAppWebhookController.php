<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Handle GET request for Meta Business webhook verification
     */
    public function verify(Request $request)
    {
        // Meta Business verification parameters
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');
        
        Log::info('Meta Business webhook verification', [
            'mode' => $mode,
            'token' => $token,
            'challenge' => $challenge
        ]);
        
        // Verify the webhook
        if ($mode === 'subscribe' && $token === 'medico_bot_2025') {
            Log::info('Webhook verification successful');
            return response($challenge, 200);
        }
        
        Log::error('Webhook verification failed');
        return response('Forbidden', 403);
    }
    
    /**
     * Handle POST request for WhatsApp messages
     */
    public function webhook(Request $request)
    {
        Log::info('WhatsApp webhook received', $request->all());
        
        try {
            // Forward the request to n8n
            $n8nUrl = 'http://localhost:5678/webhook-test/whatsapp-webhook';
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($n8nUrl, $request->all());
                
            Log::info('n8n response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return response()->json(['status' => 'forwarded'], 200);
            
        } catch (\Exception $e) {
            Log::error('Error forwarding to n8n', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);
            
            return response()->json(['error' => 'Internal error'], 500);
        }
    }
}