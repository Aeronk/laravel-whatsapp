<?php

namespace Katema\WhatsApp\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Katema\WhatsApp\Exceptions\WhatsAppException;

class WhatsAppService
{
    protected string $accessToken;
    protected string $phoneNumberId;
    protected string $verifyToken;
    protected string $apiUrl;
    protected string $apiVersion;
    protected \Illuminate\Support\Collection $recordedMessages;

    public function __construct(string $accessToken, string $phoneNumberId, string $verifyToken)
    {
        $this->accessToken = $accessToken;
        $this->phoneNumberId = $phoneNumberId;
        $this->verifyToken = $verifyToken;
        $this->apiUrl = config('whatsapp.api_url');
        $this->apiVersion = config('whatsapp.api_version');
        $this->recordedMessages = collect();
    }

    public function to(string $to): \Katema\WhatsApp\Builders\MessageBuilder
    {
        return (new \Katema\WhatsApp\Builders\MessageBuilder($this))->to($to);
    }

    public function sendMessage(string $to, string $message, ?string $messageId = null): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $message],
        ];

        if ($messageId) {
            $payload['context'] = ['message_id' => $messageId];
        }

        return $this->makeRequest('messages', $payload);
    }

    public function sendTemplate(string $to, string $templateName, string $languageCode = 'en', array $components = []): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $languageCode],
            ],
        ];

        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        return $this->makeRequest('messages', $payload);
    }

    public function sendInteractive(string $to, array $interactive, ?string $messageId = null): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => $interactive,
        ];

        if ($messageId) {
            $payload['context'] = ['message_id' => $messageId];
        }

        return $this->makeRequest('messages', $payload);
    }

    public function sendImage(string $to, string $imageUrl, ?string $caption = null): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'image',
            'image' => ['link' => $imageUrl],
        ];

        if ($caption) {
            $payload['image']['caption'] = $caption;
        }

        return $this->makeRequest('messages', $payload);
    }

    public function sendDocument(string $to, string $documentUrl, ?string $filename = null, ?string $caption = null): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'document',
            'document' => ['link' => $documentUrl],
        ];

        if ($filename) {
            $payload['document']['filename'] = $filename;
        }

        if ($caption) {
            $payload['document']['caption'] = $caption;
        }

        return $this->makeRequest('messages', $payload);
    }

    public function sendAudio(string $to, string $audioUrl): array
    {
        return $this->makeRequest('messages', [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'audio',
            'audio' => ['link' => $audioUrl],
        ]);
    }

    public function sendVideo(string $to, string $videoUrl, ?string $caption = null): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'video',
            'video' => ['link' => $videoUrl],
        ];

        if ($caption) {
            $payload['video']['caption'] = $caption;
        }

        return $this->makeRequest('messages', $payload);
    }

    public function sendLocation(string $to, float $latitude, float $longitude, ?string $name = null, ?string $address = null): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'location',
            'location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
        ];

        if ($name) {
            $payload['location']['name'] = $name;
        }

        if ($address) {
            $payload['location']['address'] = $address;
        }

        return $this->makeRequest('messages', $payload);
    }

    public function sendFlow(string $to, string $flowId, array $flowData = [], string $mode = 'draft'): array
    {
        return $this->makeRequest('messages', [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'flow',
                'action' => [
                    'name' => 'flow',
                    'parameters' => [
                        'flow_id' => $flowId,
                        'flow_cta' => 'Open',
                        'flow_action' => 'navigate',
                        'flow_action_payload' => [
                            'screen' => 'WELCOME',
                            'data' => $flowData,
                        ],
                        'mode' => $mode,
                    ],
                ],
            ],
        ]);
    }

    public function markAsRead(string $messageId): array
    {
        return $this->makeRequest('messages', [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $messageId,
        ]);
    }

    public function downloadMedia(string $mediaId): string
    {
        $response = $this->makeRequest($mediaId, [], 'GET');
        $mediaUrl = $response['url'] ?? null;

        if (!$mediaUrl) {
            throw new WhatsAppException('Media URL not found');
        }

        $mediaResponse = Http::withToken($this->accessToken)->get($mediaUrl);

        if (!$mediaResponse->successful()) {
            throw new WhatsAppException('Failed to download media');
        }

        return $mediaResponse->body();
    }

    public function verifyWebhook(string $mode, string $token, string $challenge): string
    {
        if ($mode === 'subscribe' && $token === $this->verifyToken) {
            return $challenge;
        }

        throw new WhatsAppException('Webhook verification failed');
    }

    protected function makeRequest(string $endpoint, array $data = [], string $method = 'POST'): array
    {
        $url = "{$this->apiUrl}/{$this->apiVersion}/{$this->phoneNumberId}/{$endpoint}";

        $response = Http::withToken($this->accessToken)
                    ->asJson()
            ->{strtolower($method)}($url, $data);

        if (config('whatsapp.logging.enabled')) {
            Log::channel(config('whatsapp.logging.channel'))->info('WhatsApp API Request', [
                'endpoint' => $endpoint,
                'method' => $method,
                'data' => $data,
                'response' => $response->json(),
            ]);
        }

        if (!$response->successful()) {
            $error = $response->json();
            throw new WhatsAppException(
                $error['error']['message'] ?? 'WhatsApp API request failed',
                $response->status()
            );
        }

        return $response->json();
    }
}