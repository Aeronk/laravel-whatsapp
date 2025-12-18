<?php

namespace Katema\WhatsApp\Services\AI;

use Illuminate\Support\Facades\Http;
use Katema\WhatsApp\Contracts\AIServiceInterface;
use Katema\WhatsApp\Exceptions\AIServiceException;

class OpenAIService implements AIServiceInterface
{
    protected string $apiKey;
    protected string $model;
    protected int $maxTokens;
    protected float $temperature;

    public function __construct()
    {
        $this->apiKey = config('whatsapp.ai.openai.api_key');
        $this->model = config('whatsapp.ai.openai.model');
        $this->maxTokens = config('whatsapp.ai.openai.max_tokens');
        $this->temperature = config('whatsapp.ai.openai.temperature');

        if (!$this->apiKey) {
            throw new AIServiceException('OpenAI API key not configured');
        }
    }

    public function chat(string $message, array $history = []): string
    {
        $messages = array_merge($history, [
            ['role' => 'user', 'content' => $message],
        ]);

        $response = Http::withToken($this->apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
            ]);

        if (!$response->successful()) {
            throw new AIServiceException('OpenAI API request failed: ' . $response->body());
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    public function completion(string $prompt, array $options = []): string
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $options['model'] ?? $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
                'temperature' => $options['temperature'] ?? $this->temperature,
            ]);

        if (!$response->successful()) {
            throw new AIServiceException('OpenAI API request failed: ' . $response->body());
        }

        return $response->json('choices.0.message.content') ?? '';
    }
}