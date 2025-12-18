<?php

namespace Katema\WhatsApp\Listeners;

use Katema\WhatsApp\Events\MessageReceived;
use Katema\WhatsApp\Services\Chatbot\ChatbotEngine;

class ProcessIncomingMessage
{
    public function __construct(
        protected ChatbotEngine $chatbot
    ) {}

    public function handle(MessageReceived $event): void
    {
        if (!config('whatsapp.chatbot.enabled')) {
            return;
        }

        $this->chatbot->process($event->message, $event->user);
    }
}