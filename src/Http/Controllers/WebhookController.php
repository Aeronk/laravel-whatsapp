<?php

namespace Katema\WhatsApp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Katema\WhatsApp\Services\WhatsAppService;
use Katema\WhatsApp\Services\WebhookHandler;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected WhatsAppService $whatsapp,
        protected WebhookHandler $handler
    ) {}

    public function verify(Request $request)
    {
        try {
            $mode = $request->query('hub_mode');
            $token = $request->query('hub_verify_token');
            $challenge = $request->query('hub_challenge');

            $result = $this->whatsapp->verifyWebhook($mode, $token, $challenge);
            
            return response($result, 200);
        } catch (\Exception $e) {
            Log::error('Webhook verification failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            
            return response('Forbidden', 403);
        }
    }

    public function handle(Request $request): JsonResponse
    {
        try {
            if (config('whatsapp.logging.enabled')) {
                Log::channel(config('whatsapp.logging.channel'))->info('Webhook received', [
                    'payload' => $request->all(),
                ]);
            }

            $this->handler->handle($request->all());

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Webhook handling failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}