<?php

namespace Katema\WhatsApp\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Katema\WhatsApp\Models\WhatsAppMessage;
use Katema\WhatsApp\Models\WhatsAppUser;

class MessageReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public WhatsAppMessage $message,
        public WhatsAppUser $user
    ) {}
}

class MessageStatusUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public WhatsAppMessage $message,
        public string $status
    ) {}
}

class FlowResponseReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public array $flowData,
        public WhatsAppUser $user
    ) {}
}