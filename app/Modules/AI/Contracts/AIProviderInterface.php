<?php

namespace App\Modules\AI\Contracts;

interface AIProviderInterface
{
    /**
     * Conduct a conversational requirements interview step.
     * @param array $context The conversation history.
     * @param string $userInput The latest user input.
     * @return array Contains 'reply' and updated 'context'.
     */
    public function conductRequirementsInterview(array $context, string $userInput): array;

    /**
     * Generate structured architecture from project requirements.
     * @param array $requirements The parsed requirements.
     * @return array The structured JSON response representing the architecture.
     */
    public function generateArchitecture(array $requirements): array;

    /**
     * Summarize a meeting transcript.
     * @param string $transcript The raw transcript text.
     * @return array The structured summary.
     */
    public function summarizeMeeting(string $transcript): array;

    /**
     * Send a single chat completion request.
     * This is the core primitive used by the AgentOrchestrator for the
     * multi-agent pipeline, allowing each provider to be called independently.
     *
     * @param array $messages The full message array [['role'=>'...', 'content'=>'...']].
     * @param bool $jsonMode If true, instructs the model to respond with valid JSON only.
     * @return string The raw text content of the model's reply.
     */
    public function chat(array $messages, bool $jsonMode = false): string;
}
