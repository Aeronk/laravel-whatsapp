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
        try {
            $provider = config('whatsapp.ai.default');

            if (!$provider) {
                return;
            }

            $this->service = match ($provider) {
                'openai' => new OpenAIService(),
                'gemini' => new GeminiService(),
                default => null,
            };
        } catch (\Exception $e) {
            // Silently fail to keep the package optional
            $this->service = null;
        }
    }

    public function chat(string $message, array $history = []): ?string
    {
        if (!$this->service) {
            return null;
        }

        try {
            return $this->service->chat($message, $history);
        } catch (\Exception $e) {
            // Log or handle error if needed, but return null to prevent crashing the flow
            return null;
        }
    }

    public function completion(string $prompt, array $options = []): ?string
    {
        if (!$this->service) {
            return null;
        }

        try {
            return $this->service->completion($prompt, $options);
        } catch (\Exception $e) {
            return null;
        }
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