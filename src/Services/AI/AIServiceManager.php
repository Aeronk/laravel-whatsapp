<?php

namespace Katema\WhatsApp\Services\AI;

use Katema\WhatsApp\Contracts\AIServiceInterface;
use Katema\WhatsApp\Exceptions\AIServiceException;

class AIServiceManager
{
    protected ?AIServiceInterface $service = null;

    public function __construct()
    {
        $this->bootService();
    }

    protected function bootService(): void
    {
        $provider = config('whatsapp.ai.default');

        if (!$provider) {
            return;
        }

        $this->service = match($provider) {
            'openai' => new OpenAIService(),
            'gemini' => new GeminiService(),
            default => throw new AIServiceException("Unsupported AI provider: {$provider}"),
        };
    }

    public function chat(string $message, array $history = []): ?string
    {
        if (!$this->service) {
            return null;
        }

        return $this->service->chat($message, $history);
    }

    public function completion(string $prompt, array $options = []): ?string
    {
        if (!$this->service) {
            return null;
        }

        return $this->service->completion($prompt, $options);
    }

    public function isEnabled(): bool
    {
        return $this->service !== null;
    }

    public function getProvider(): ?string
    {
        return config('whatsapp.ai.default');
    }
}