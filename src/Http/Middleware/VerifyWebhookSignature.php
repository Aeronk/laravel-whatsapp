<?php

namespace Katema\WhatsApp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Katema\WhatsApp\Exceptions\WhatsAppException;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Katema\WhatsApp\Exceptions\WhatsAppException
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('whatsapp.webhook.verify_signature')) {
            return $next($request);
        }

        if ($request->isMethod('GET')) {
            return $next($request);
        }

        $signature = $request->header('X-Hub-Signature-256');

        if (!$signature) {
            throw new WhatsAppException('Webhook signature missing', 403);
        }

        $appSecret = config('whatsapp.app_secret');

        if (!$appSecret) {
            throw new WhatsAppException('WhatsApp App Secret not configured', 500);
        }

        $expectedSignature = 'sha256=' . hash_hmac('sha256', $request->getContent(), $appSecret);

        if (!hash_equals($expectedSignature, $signature)) {
            throw new WhatsAppException('Invalid webhook signature', 403);
        }

        return $next($request);
    }
}
