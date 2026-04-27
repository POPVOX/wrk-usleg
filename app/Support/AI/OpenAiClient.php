<?php

namespace App\Support\AI;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiClient
{
    public const DEFAULT_TIMEOUT = 120;

    public static function request(): PendingRequest
    {
        $apiKey = config('services.openai.api_key') ?: env('OPENAI_API_KEY');
        $baseUrl = rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/');

        $headers = [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ];

        if ($organization = config('services.openai.organization')) {
            $headers['OpenAI-Organization'] = $organization;
        }

        return Http::baseUrl($baseUrl)
            ->withHeaders($headers)
            ->timeout((int) config('ai.timeout', self::DEFAULT_TIMEOUT))
            ->retry(2, 500);
    }

    public static function chat(array $payload): array
    {
        return self::send('/chat/completions', array_merge([
            'model' => config('services.openai.chat_model', 'gpt-5.4-mini-2026-03-17'),
        ], $payload));
    }

    public static function embedding(string $input, ?string $model = null): array
    {
        $response = self::send('/embeddings', [
            'model' => $model ?? config('services.openai.embedding_model', 'text-embedding-3-small'),
            'input' => $input,
        ]);

        return $response['data'][0]['embedding'] ?? [];
    }

    protected static function send(string $path, array $payload): array
    {
        if (!config('services.openai.api_key')) {
            return [
                'error' => true,
                'status' => 0,
                'body' => 'Missing OpenAI API key',
            ];
        }

        $baseKey = 'metrics:ai:openai';
        $start = microtime(true);
        $res = self::request()->post($path, $payload);
        $durationMs = (int) round((microtime(true) - $start) * 1000);

        if ($res->successful()) {
            $log = [
                'provider' => 'openai',
                'path' => $path,
                'status' => $res->status(),
                'duration_ms' => $durationMs,
            ];

            Log::info('OpenAI request success', $log);

            if (config('ai.metrics.enabled', true)) {
                Cache::increment("{$baseKey}:success");
                Cache::increment("{$baseKey}:count");
                Cache::increment("{$baseKey}:latency_ms_total", $durationMs);
                Cache::put("{$baseKey}:last_success_at", now()->toIso8601String(), 3600);
                Log::info('metric.ai_request', $log + ['outcome' => 'success']);
            }

            return $res->json();
        }

        $log = [
            'provider' => 'openai',
            'path' => $path,
            'status' => $res->status(),
            'duration_ms' => $durationMs,
            'body' => $res->body(),
        ];

        Log::error('OpenAI request failed', $log);

        if (config('ai.metrics.enabled', true)) {
            Cache::increment("{$baseKey}:error");
            Cache::increment("{$baseKey}:count");
            Cache::increment("{$baseKey}:latency_ms_total", $durationMs);
            Cache::put("{$baseKey}:last_error_at", now()->toIso8601String(), 3600);
            Cache::put("{$baseKey}:last_error_status", $res->status(), 3600);
            Cache::put("{$baseKey}:last_error_body", substr($res->body(), 0, 500), 3600);
            Log::warning('metric.ai_request', $log + ['outcome' => 'error']);
        }

        return [
            'error' => true,
            'status' => $res->status(),
            'body' => $res->body(),
        ];
    }
}
