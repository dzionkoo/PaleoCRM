<?php

declare(strict_types=1);

namespace PaleoCRM\Service;

use PaleoCRM\Entity\DinoFossil;

/**
 * AIDescriptionService - Claude AI Integration
 * 
 * Handles communication with Claude API for intelligent fossil descriptions
 * Demonstrates dependency injection and external service abstraction
 */
final class AIDescriptionService
{
    private const API_ENDPOINT = 'https://api.anthropic.com/v1/messages';
    private const MODEL = 'claude-3-5-sonnet-20241022';

    public function __construct(
        private readonly string $apiKey,
    ) {}

    /**
     * Generate AI description for a fossil using Claude API
     * 
     * @param DinoFossil $fossil Fossil to describe
     * @return string Generated description
     * @throws \RuntimeException if API call fails
     */
    public function generateDescription(DinoFossil $fossil): string
    {
        $prompt = $this->buildPrompt($fossil);

        $payload = [
            'model' => self::MODEL,
            'max_tokens' => 1024,
            'system' => 'You are a paleontology expert. Generate engaging, scientifically accurate descriptions of fossils. Keep descriptions concise but informative (100-300 words).',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ]
            ]
        ];

        $response = $this->callClaudeAPI($payload);

        // Extract text from Claude response
        if (
            isset($response['content'][0]['type']) &&
            $response['content'][0]['type'] === 'text'
        ) {
            return $response['content'][0]['text'];
        }

        throw new \RuntimeException('Unexpected Claude API response structure');
    }

    /**
     * Build prompt for Claude based on fossil characteristics
     * 
     * @param DinoFossil $fossil Fossil entity
     * @return string Formatted prompt for Claude
     */
    private function buildPrompt(DinoFossil $fossil): string
    {
        $data = $fossil->toArray();

        return <<<PROMPT
Generate a detailed paleontological description for this fossil:

Species: {$data['species']}
Estimated Age: {$data['estimatedAge']} million years ago ({$data['era']})
Weight: {$data['weight']} kg
Discovery Location: {$data['discoveryLocation']}
Collection: {$data['collectionName']}
Theoretical Velocity: {$data['theoreticalVelocity']}

Include:
1. Physical characteristics and adaptations
2. Ecological role and habitat
3. Evolutionary significance
4. Preservation quality assessment

Write in an engaging, scientifically accurate tone suitable for museum display.
PROMPT;
    }

    /**
     * Call Claude API with proper error handling
     * 
     * @param array<string, mixed> $payload API request payload
     * @return array<string, mixed> API response
     * @throws \RuntimeException on API error
     */
    private function callClaudeAPI(array $payload): array
    {
        $ch = curl_init(self::API_ENDPOINT);

        if ($ch === false) {
            throw new \RuntimeException('Failed to initialize cURL');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("cURL error: {$error}");
        }

        if ($httpCode !== 200) {
            throw new \RuntimeException(
                "Claude API error (HTTP {$httpCode}): {$response}"
            );
        }

        $decoded = json_decode($response ?? '', true);

        if ($decoded === null) {
            throw new \RuntimeException('Failed to parse Claude API response');
        }

        return $decoded;
    }
}
