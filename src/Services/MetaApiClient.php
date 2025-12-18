<?php
namespace Katema\WhatsApp\Services;

use Illuminate\Support\Facades\Http;

class MetaApiClient
{
    public function sendMessage(array $payload)
    {
        return Http::withToken(config('whatsapp.meta.token'))
            ->post(
                "https://graph.facebook.com/v24.0/"
                .config('whatsapp.meta.phone_number_id')
                ."/messages",
                $payload
            )
            ->json();
    }
}
