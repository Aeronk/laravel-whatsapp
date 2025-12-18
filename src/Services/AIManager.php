<?php

class AIManager
{
    public function reply($message, $user)
    {
        return match(config('whatsapp.ai.default')) {
            'gemini' => app(GeminiClient::class)->reply($message),
            default => app(OpenAIClient::class)->reply($message),
        };
    }
}
