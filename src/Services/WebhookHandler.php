<?php

namespace Katema\WhatsApp\Services;

use Katema\WhatsApp\Models\WhatsAppUser;
use Katema\WhatsApp\Models\WhatsAppMessage;
use Katema\WhatsApp\Events\MessageReceived;
use Katema\WhatsApp\Events\MessageStatusUpdated;
use Katema\WhatsApp\Events\FlowResponseReceived;

class WebhookHandler
{
    public function handle(array $payload): void
    {
        $entry = $payload['entry'][0] ?? null;
        if (!$entry) {
            return;
        }

        $changes = $entry['changes'][0] ?? null;
        if (!$changes) {
            return;
        }

        $value = $changes['value'] ?? null;
        if (!$value) {
            return;
        }

        if (isset($value['messages'])) {
            foreach ($value['messages'] as $message) {
                $this->handleMessage($message, $value['metadata'] ?? []);
            }
        }

        if (isset($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                $this->handleStatus($status);
            }
        }
    }

    protected function handleMessage(array $message, array $metadata): void
    {
        $from = $message['from'];
        $messageId = $message['id'];
        $type = $message['type'];
        $timestamp = $message['timestamp'];

        $user = WhatsAppUser::firstOrCreate(
            ['phone_number' => $from],
            [
                'profile_name' => $metadata['display_phone_number'] ?? null,
                'last_interaction_at' => now(),
            ]
        );

        $user->update(['last_interaction_at' => now()]);

        $content = $this->extractContent($message, $type);

        $whatsappMessage = WhatsAppMessage::firstOrCreate(
            ['message_id' => $messageId],
            [
                'whatsapp_user_id' => $user->id,
                'type' => $type,
                'direction' => 'incoming',
                'status' => 'received',
                'content' => $content,
                'metadata' => $message,
                'sent_at' => now()->setTimestamp($timestamp),
            ]
        );

        event(new MessageReceived($whatsappMessage, $user));
    }

    protected function handleStatus(array $status): void
    {
        $messageId = $status['id'];
        $statusType = $status['status'];

        $message = WhatsAppMessage::where('message_id', $messageId)->first();

        if (!$message) {
            return;
        }

        $updates = ['status' => $statusType];

        match ($statusType) {
            'sent' => $updates['sent_at'] = now(),
            'delivered' => $updates['delivered_at'] = now(),
            'read' => $updates['read_at'] = now(),
            'failed' => [
                $updates['failed_at'] = now(),
                $updates['error_message'] = $status['errors'][0]['title'] ?? 'Unknown error',
            ],
            'deleted' => $updates['status'] = 'deleted',
            default => null,
        };

        $message->update($updates);

        event(new MessageStatusUpdated($message, $statusType));
    }

    protected function extractContent(array $message, string $type): array
    {
        return match ($type) {
            'text' => [
                'body' => $message['text']['body'] ?? '',
            ],
            'image', 'video', 'audio', 'document', 'sticker' => [
                'id' => $message[$type]['id'] ?? null,
                'mime_type' => $message[$type]['mime_type'] ?? null,
                'caption' => $message[$type]['caption'] ?? null,
            ],
            'location' => [
                'latitude' => $message['location']['latitude'] ?? null,
                'longitude' => $message['location']['longitude'] ?? null,
                'name' => $message['location']['name'] ?? null,
                'address' => $message['location']['address'] ?? null,
            ],
            'interactive' => [
                'type' => $message['interactive']['type'] ?? null,
                'button_reply' => $message['interactive']['button_reply'] ?? null,
                'list_reply' => $message['interactive']['list_reply'] ?? null,
                'nfm_reply' => $message['interactive']['nfm_reply'] ?? null,
            ],
            'button' => [
                'payload' => $message['button']['payload'] ?? null,
                'text' => $message['button']['text'] ?? null,
            ],
            default => $message,
        };
    }
}