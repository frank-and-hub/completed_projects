<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class OpenAIService
{
    protected $apiKey;
    protected $endpoint = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.open_ai.key');
    }

    public function generateText($prompt, $model = 'gpt-4o-mini', $temperature = 0.7)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->endpoint, [
                    'model' => $model,
                    'store' => true,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a helpful content writer specialized in SEO and location-based descriptions.'
                        ],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => $temperature,
                ]);

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        throw new \Exception("OpenAI API error: " . $response->body());
    }
}
