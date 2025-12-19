<?php

namespace Katema\WhatsApp\Testing;

use Katema\WhatsApp\Services\WhatsAppService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert;

class WhatsAppFake extends WhatsAppService
{
    protected Collection $recordedMessages;

    public function __construct()
    {
        $this->recordedMessages = collect();
    }

    public function sendMessage(string $to, string $message, ?string $messageId = null): array
    {
        $this->recordedMessages->push([
            'to' => $to,
            'message' => $message,
            'type' => 'text',
            'context_message_id' => $messageId,
        ]);

        return ['messaging_product' => 'whatsapp', 'contacts' => [['input' => $to, 'wa_id' => $to]], 'messages' => [['id' => 'fake_id_' . uniqid()]]];
    }

    public function assertSentTo(string $to, callable $callback = null): void
    {
        $messages = $this->recordedMessages->where('to', $to);

        Assert::assertTrue(
            $messages->count() > 0,
            "No messages recorded for {$to}"
        );

        if ($callback) {
            Assert::assertTrue(
                $messages->filter($callback)->count() > 0,
                "The expected message was not sent to {$to}"
            );
        }
    }

    public function assertNothingSent(): void
    {
        Assert::assertTrue(
            $this->recordedMessages->isEmpty(),
            "Messages were sent unexpectedly"
        );
    }
}
