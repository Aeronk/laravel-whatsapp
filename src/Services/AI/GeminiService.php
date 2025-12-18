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

    public function __construct()
    {
        $this->apiKey = config('whatsapp.ai.gemini.api_key');
        $this->model = config('whatsapp.ai.gemini.model');
        $this->maxTokens = config('whatsapp.ai.gemini.max_tokens');
        $this->temperature = config('whatsapp.ai.gemini.temperature');

        if (!$this->apiKey) {
            throw new AIServiceException('Gemini API key not configured');
        }
    }

    public function chat(string $message, array $history = []): string
    {
        $contents = $this->formatHistory($history);
        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

        $response = Http::timeout(30)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent", [
                'contents' => $contents,
                'generationConfig' => [
                    'maxOutputTokens' => $this->maxTokens,
                    'temperature' => $this->temperature,
                ],
            ], [
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