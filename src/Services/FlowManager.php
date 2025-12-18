<?php

use Katema\WhatsApp\Services\MetaApiClient;

class FlowManager
{
    public function sendFlow(string $to, array $flowJson)
    {
        return app(MetaApiClient::class)->sendMessage([
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'flow'
            ]
        ]);
    }
}
