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
    protected ?string $systemPrompt = null;
    protected array $tools = [];

    public function __construct()
    {
        $apiKey = config('whatsapp.ai.openai.api_key');

        if (!$apiKey || str_contains($apiKey, 'your_openai_key_here')) {
            throw new AIServiceException('OpenAI API key not configured or using placeholder');
        }

        $this->apiKey = $apiKey;
        $this->model = config('whatsapp.ai.openai.model', 'gpt-4-turbo-preview');
        $this->maxTokens = (int) config('whatsapp.ai.openai.max_tokens', 500);
        $this->temperature = (float) config('whatsapp.ai.openai.temperature', 0.7);
    }

    public function withSystemPrompt(string $prompt): self
    {
        $this->systemPrompt = $prompt;
        return $this;
    }

    public function withTools(array $tools): self
    {
        $this->tools = $tools;
        return $this;
    }

    public function chat(string $message, array $history = []): string
    {
        $messages = [];

        if ($this->systemPrompt) {
            $messages[] = ['role' => 'system', 'content' => $this->systemPrompt];
        }

        $messages = array_merge($messages, $history, [
            ['role' => 'user', 'content' => $message],
        ]);

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
        ];

        if (!empty($this->tools)) {
            $payload['tools'] = $this->tools;
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', $payload);

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