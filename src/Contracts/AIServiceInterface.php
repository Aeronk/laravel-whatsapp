<?php

namespace Katema\WhatsApp\Contracts;

interface AIServiceInterface
{
    /**
     * Send a chat message to the AI service.
     *
     * @param string $message
     * @param array $history
     * @return string
     */
    public function chat(string $message, array $history = []): string;

    /**
     * Get a completion for a prompt.
     *
     * @param string $prompt
     * @param array $options
     * @return string
     */
    public function completion(string $prompt, array $options = []): string;

    /**
     * Set the system prompt for the service.
     *
     * @param string $prompt
     * @return self
     */
    public function withSystemPrompt(string $prompt): self;

    /**
     * Set tools/functions for the service.
     *
     * @param array $tools
     * @return self
     */
    public function withTools(array $tools): self;
}
