<?php

namespace DoctorCode\SDK;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class DoctorCodeClient
{
    protected string $apiKey;
    protected string $apiUrl;
    protected Client $httpClient;

    public function __construct(string $apiKey, string $apiUrl = 'https://doctorcode.dev/api')
    {
        $this->apiKey = $apiKey;
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->httpClient = new Client([
            'base_uri' => $this->apiUrl,
            'timeout' => 30,
        ]);
    }

    /**
     * Report an error to DoctorCode
     *
     * @param \Throwable $exception
     * @param array $context Additional context about the error
     * @return array|null Response from DoctorCode API
     */
    public function reportError(\Throwable $exception, array $context = []): ?array
    {
        try {
            $response = $this->httpClient->post('/errors/report', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'error_message' => $exception->getMessage(),
                    'file_path' => $exception->getFile(),
                    'line_number' => $exception->getLine(),
                    'stack_trace' => $exception->getTraceAsString(),
                    'exception_class' => get_class($exception),
                    'code' => $exception->getCode(),
                    'context' => $context,
                    'environment' => config('app.env', 'production'),
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            Log::info('[DoctorCode] Error reported successfully', [
                'error_id' => $body['error_id'] ?? null,
                'status' => $body['status'] ?? null,
            ]);

            return $body;

        } catch (GuzzleException $e) {
            Log::error('[DoctorCode] Failed to report error', [
                'error' => $e->getMessage(),
                'original_error' => $exception->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get a fix suggestion for an error
     *
     * @param string $errorId The ID of the error from reportError()
     * @return array|null Fix suggestion from DoctorCode
     */
    public function getFixSuggestion(string $errorId): ?array
    {
        try {
            $response = $this->httpClient->get("/errors/{$errorId}/fix", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (GuzzleException $e) {
            Log::error('[DoctorCode] Failed to get fix suggestion', [
                'error' => $e->getMessage(),
                'error_id' => $errorId,
            ]);
            return null;
        }
    }

    /**
     * Get application statistics from DoctorCode
     *
     * @return array|null Application statistics
     */
    public function getStatistics(): ?array
    {
        try {
            $response = $this->httpClient->get('/statistics', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (GuzzleException $e) {
            Log::error('[DoctorCode] Failed to get statistics', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Test the API connection
     *
     * @return bool True if connection is successful
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->httpClient->get('/ping', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ],
            ]);

            return $response->getStatusCode() === 200;

        } catch (GuzzleException $e) {
            Log::error('[DoctorCode] Connection test failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
