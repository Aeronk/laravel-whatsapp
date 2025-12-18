<?php

namespace Katema\WhatsApp\Services\Chatbot;

use Katema\WhatsApp\Models\WhatsAppUser;
use Katema\WhatsApp\Models\WhatsAppSession;
use Katema\WhatsApp\Models\WhatsAppMessage;
use Katema\WhatsApp\Services\WhatsAppService;
use Katema\WhatsApp\Services\AI\AIServiceManager;
use Illuminate\Support\Collection;

class ChatbotEngine
{
    protected Collection $rules;
    protected bool $aiEnabled = false;

    public function __construct(
        protected WhatsAppService $whatsapp,
        protected AIServiceManager $aiManager
    ) {
        $this->rules = collect();
        $this->aiEnabled = config('whatsapp.ai.default') !== null;
    }

    public function addRule(callable $condition, callable $action, int $priority = 10): self
    {
        $this->rules->push([
            'condition' => $condition,
            'action' => $action,
            'priority' => $priority,
        ]);

        $this->rules = $this->rules->sortBy('priority');

        return $this;
    }

    public function process(WhatsAppMessage $message, WhatsAppUser $user): void
    {
        if ($user->is_blocked) {
            return;
        }

        $session = $this->getOrCreateSession($user);

        foreach ($this->rules as $rule) {
            if ($rule['condition']($message, $user, $session)) {
                $rule['action']($message, $user, $session, $this->whatsapp);
                return;
            }
        }

        if ($this->aiEnabled) {
            $this->handleWithAI($message, $user, $session);
        }
    }

    protected function getOrCreateSession(WhatsAppUser $user): WhatsAppSession
    {
        $session = $user->activeSession()->first();

        if (!$session || $session->isExpired()) {
            $session?->end();

            $timeout = config('whatsapp.chatbot.session_timeout');
            $session = WhatsAppSession::create([
                'whatsapp_user_id' => $user->id,
                'status' => 'active',
                'started_at' => now(),
                'expires_at' => now()->addSeconds($timeout),
            ]);
        } else {
            $session->extend();
        }

        return $session;
    }

    protected function handleWithAI(WhatsAppMessage $message, WhatsAppUser $user, WhatsAppSession $session): void
    {
        $conversationHistory = $this->getConversationHistory($session);
        
        $response = $this->aiManager->chat(
            $message->content['body'] ?? '',
            $conversationHistory
        );

        if ($response) {
            $this->whatsapp->sendMessage($user->phone_number, $response);
        }
    }

    protected function getConversationHistory(WhatsAppSession $session, int $limit = 10): array
    {
        return $session->messages()
            ->latest()
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(function ($msg) {
                return [
                    'role' => $msg->direction === 'incoming' ? 'user' : 'assistant',
                    'content' => $msg->content['body'] ?? json_encode($msg->content),
                ];
            })
            ->toArray();
    }

    public function clearRules(): self
    {
        $this->rules = collect();
        return $this;
    }
}