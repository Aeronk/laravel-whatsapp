<?php

namespace Katema\WhatsApp\Builders;

use Katema\WhatsApp\Facades\WhatsApp;
use Katema\WhatsApp\Services\WhatsAppService;

class MessageBuilder
{
    protected string $to;
    protected string $type = 'text';
    protected ?string $body = null;
    protected array $buttons = [];
    protected array $sections = [];
    protected array $metadata = [];
    protected ?string $contextMessageId = null;

    public function __construct(protected WhatsAppService $service)
    {
    }

    public function to(string $to): self
    {
        $this->to = $to;
        return $this;
    }

    public function text(string $body): self
    {
        $this->type = 'text';
        $this->body = $body;
        return $this;
    }

    public function context(string $messageId): self
    {
        $this->contextMessageId = $messageId;
        return $this;
    }

    public function buttons(array $buttons): self
    {
        $this->type = 'interactive_button';
        $this->buttons = $buttons;
        return $this;
    }

    public function list(string $buttonText, array $sections): self
    {
        $this->type = 'interactive_list';
        $this->metadata['button_text'] = $buttonText;
        $this->sections = $sections;
        return $this;
    }

    public function send(): array
    {
        if ($this->type === 'text') {
            return $this->service->sendMessage($this->to, $this->body, $this->contextMessageId);
        }

        if ($this->type === 'interactive_button') {
            return $this->service->sendInteractive($this->to, [
                'type' => 'button',
                'body' => ['text' => $this->body],
                'action' => [
                    'buttons' => collect($this->buttons)->map(fn($text, $id) => [
                        'type' => 'reply',
                        'reply' => ['id' => is_numeric($id) ? $text : $id, 'title' => $text]
                    ])->values()->all()
                ]
            ], $this->contextMessageId);
        }

        // Additional types can be implemented here...

        return [];
    }
}
