<?php

declare(strict_types=1);

namespace PaleoCRM\Service;

use PaleoCRM\Entity\DinoFossil;

/**
 * AIDescriptionService — Claude AI Integration
 *
 * Reads ANTHROPIC_API_KEY and CLAUDE_MODEL from environment variables.
 * Supports both full-response and SSE-streaming modes.
 */
final class AIDescriptionService
{
    private const API_ENDPOINT  = 'https://api.anthropic.com/v1/messages';
    private const DEFAULT_MODEL = 'claude-sonnet-4-20250514'; // ← updated model

    private readonly string $apiKey;
    private readonly string $model;

    public function __construct(
        string $apiKey  = '',
        string $model   = '',
    ) {
        // Prefer constructor args; fall back to env vars
        $this->apiKey = $apiKey  ?: ($_ENV['ANTHROPIC_API_KEY'] ?? '');
        $this->model  = $model   ?: ($_ENV['CLAUDE_MODEL'] ?? self::DEFAULT_MODEL);

        if (empty($this->apiKey)) {
            throw new \RuntimeException(
                'ANTHROPIC_API_KEY is required. Set it in your .env file or pass it to AIDescriptionService.'
            );
        }
    }

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Generate a complete AI description (blocking).
     *
     * @throws \RuntimeException on API failure
     */
    public function generateDescription(DinoFossil $fossil): string
    {
        $payload  = $this->buildPayload($fossil, stream: false);
        $response = $this->callAPI($payload);

        if (
            isset($response['content'][0]['type']) &&
            $response['content'][0]['type'] === 'text'
        ) {
            return $response['content'][0]['text'];
        }

        throw new \RuntimeException('Unexpected Claude API response structure');
    }

    /**
     * Stream an AI description via SSE.
     * Each chunk of raw SSE text is passed to $onChunk.
     *
     * @param callable(string):void $onChunk
     */
    public function generateDescriptionStream(DinoFossil $fossil, callable $onChunk): void
    {
        $payload = $this->buildPayload($fossil, stream: true);
        $this->callAPIStream($payload, $onChunk);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function buildPayload(DinoFossil $fossil, bool $stream): array
    {
        $data   = $fossil->toArray();
        $prompt = <<<PROMPT
Generate a detailed paleontological description for this fossil:

Species: {$data['species']}
Estimated Age: {$data['estimatedAge']} million years ago (Era: {$data['era']})
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
Keep the response between 100-300 words.
PROMPT;

        $payload = [
            'model'      => $this->model,
            'max_tokens' => 1024,
            'system'     => 'You are a paleontology expert. Generate engaging, scientifically accurate fossil descriptions.',
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ];

        if ($stream) {
            $payload['stream'] = true;
        }

        return $payload;
    }

    /** @return array Decoded JSON response */
    private function callAPI(array $payload): array
    {
        $ch = $this->initCurl();

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 30,
        ]);

        $raw      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("cURL error: {$error}");
        }
        if ($httpCode !== 200) {
            throw new \RuntimeException("Claude API error (HTTP {$httpCode}): {$raw}");
        }

        $decoded = json_decode($raw ?: '', true);
        if ($decoded === null) {
            throw new \RuntimeException('Failed to parse Claude API response');
        }

        return $decoded;
    }

    /** Stream response; each raw chunk of SSE text is forwarded to $onChunk */
    private function callAPIStream(array $payload, callable $onChunk): void
    {
        $ch = $this->initCurl();

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_WRITEFUNCTION  => static function ($curl, string $data) use ($onChunk): int {
                $onChunk($data);
                return strlen($data);
            },
        ]);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error || ($httpCode !== 200 && $httpCode !== 0)) {
            throw new \RuntimeException("Claude API stream error: {$error} (HTTP {$httpCode})");
        }
    }

    /** @return \CurlHandle */
    private function initCurl(): \CurlHandle
    {
        $ch = curl_init(self::API_ENDPOINT);

        if ($ch === false) {
            throw new \RuntimeException('Failed to initialize cURL');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST       => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: 2023-06-01',
            ],
        ]);

        return $ch;
    }
}