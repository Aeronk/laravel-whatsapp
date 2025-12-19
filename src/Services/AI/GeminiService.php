<?php

namespace Katema\WhatsApp\Services\AI;

use Illuminate\Support\Facades\Http;
use Katema\WhatsApp\Contracts\AIServiceInterface;
use Katema\WhatsApp\Exceptions\AIServiceException;

class GeminiService implements AIServiceInterface
{
    protected string $apiKey;
    protected string $model;
    protected int $maxTokens;
    protected float $temperature;
    protected ?string $systemPrompt = null;

    public function __construct()
    {
        $apiKey = config('whatsapp.ai.gemini.api_key');

        if (!$apiKey) {
            throw new AIServiceException('Gemini API key not configured');
        }

        $this->apiKey = $apiKey;
        $this->model = config('whatsapp.ai.gemini.model', 'gemini-pro');
        $this->maxTokens = (int) config('whatsapp.ai.gemini.max_tokens', 500);
        $this->temperature = (float) config('whatsapp.ai.gemini.temperature', 0.7);
    }

    public function withSystemPrompt(string $prompt): self
    {
        $this->systemPrompt = $prompt;
        return $this;
    }

    public function withTools(array $tools): self
    {
        // Gemini tool calling requires a slightly different payload, 
        // to be implemented in a future update if requested.
        return $this;
    }

    public function chat(string $message, array $history = []): string
    {
        $contents = $this->formatHistory($history);
        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'maxOutputTokens' => $this->maxTokens,
                'temperature' => $this->temperature,
            ],
        ];

        if ($this->systemPrompt) {
            $payload['system_instruction'] = [
                'parts' => [['text' => $this->systemPrompt]]
            ];
        }

        $response = Http::timeout(30)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent", $payload, [
                'key' => $this->apiKey,
            ]);

        if (!$response->successful()) {
            throw new AIServiceException('Gemini API request failed: ' . $response->body());
        }

        return $response->json('candidates.0.content.parts.0.text') ?? '';
    }

    public function completion(string $prompt, array $options = []): string
    {
        $model = $options['model'] ?? $this->model;

        $response = Http::timeout(30)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'contents' => [
                    ['role' => 'user', 'parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => $options['max_tokens'] ?? $this->maxTokens,
                    'temperature' => $options['temperature'] ?? $this->temperature,
                ],
            ], [
                'key' => $this->apiKey,
            ]);

        if (!$response->successful()) {
            throw new AIServiceException('Gemini API request failed: ' . $response->body());
        }

        return $response->json('candidates.0.content.parts.0.text') ?? '';
    }

    protected function formatHistory(array $history): array
    {
        return collect($history)->map(function ($msg) {
            $role = $msg['role'] === 'assistant' ? 'model' : 'user';
            return [
                'role' => $role,
                'parts' => [['text' => $msg['content']]],
            ];
        })->toArray();
    }
}