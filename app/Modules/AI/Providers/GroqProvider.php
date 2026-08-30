<?php

namespace App\Modules\AI\Providers;

use App\Modules\AI\Contracts\AIProviderInterface;
use Illuminate\Support\Facades\Http;

class GroqProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.groq.com/openai/v1';
    protected string $model = 'openai/gpt-oss-120b';

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key', '');
    }

    /**
     * Core primitive: single chat completion call.
     * Used by AgentOrchestrator to call this provider for specific agent turns.
     */
    public function chat(array $messages, bool $jsonMode = false): string
    {
        return $this->sendChatRequest($messages, $jsonMode);
    }

    public function conductRequirementsInterview(array $context, string $userInput): array
    {
        $messages = $context;
        $messages[] = ['role' => 'user', 'content' => $userInput];

        $response = $this->sendChatRequest($messages);

        $messages[] = ['role' => 'assistant', 'content' => $response];

        return [
            'reply' => $response,
            'context' => $messages,
        ];
    }

    public function generateArchitecture(array $requirements): array
    {
        $prompt = "Based on these requirements, generate a JSON object representing the system architecture (components, data flow, tech stack).\n\nRequirements:\n" . json_encode($requirements);

        $messages = [
            ['role' => 'system', 'content' => 'You are an expert Software Architect. Always reply with raw JSON.'],
            ['role' => 'user', 'content' => $prompt],
        ];

        $response = $this->sendChatRequest($messages, true);

        return json_decode($response, true) ?? ['error' => 'Failed to parse JSON architecture'];
    }

    public function summarizeMeeting(string $transcript): array
    {
        $prompt = "Summarize the following meeting transcript into key points and action items in JSON format:\n\n" . $transcript;

        $messages = [
            ['role' => 'system', 'content' => 'You are an AI meeting assistant. Reply in raw JSON with keys: "summary" and "action_items".'],
            ['role' => 'user', 'content' => $prompt],
        ];

        $response = $this->sendChatRequest($messages, true);

        return json_decode($response, true) ?? ['error' => 'Failed to parse JSON summary'];
    }

    protected function sendChatRequest(array $messages, bool $jsonMode = false): string
    {
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.3,
            'max_tokens' => 2048,
        ];

        if ($jsonMode) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(120)
            ->post("{$this->baseUrl}/chat/completions", $payload);

        if ($response->failed()) {
            return json_encode(['error' => 'Groq API Request Failed', 'details' => $response->body()]);
        }

        return $response->json('choices.0.message.content', '');
    }
}
