<?php

namespace DoctorCode;

use Throwable;

/**
 * DoctorCode PHP SDK
 *
 * Dead simple error monitoring and AI-powered fixes
 *
 * Usage:
 *   DoctorCode\Client::init('your-api-key');
 *   DoctorCode\Client::captureException($exception);
 */
class Client
{
    private static $apiKey = null;
    private static $apiUrl = 'https://doctorcode.dev/api/v1/errors';
    private static $enabled = true;
    private static $environment = 'production';
    private static $timeout = 5;
    private static $context = [];

    /**
     * Initialize DoctorCode with your API key
     *
     * @param string|array $config API key or configuration array
     * @return void
     */
    public static function init($config)
    {
        if (is_string($config)) {
            self::$apiKey = $config;
        } elseif (is_array($config)) {
            self::$apiKey = $config['api_key'] ?? null;
            self::$enabled = $config['enabled'] ?? true;
            self::$environment = $config['environment'] ?? 'production';
            self::$timeout = $config['timeout'] ?? 5;
            self::$context = $config['context'] ?? [];

            if (isset($config['api_url'])) {
                self::$apiUrl = rtrim($config['api_url'], '/') . '/api/v1/errors';
            }
        }

        if (!self::$apiKey) {
            error_log('[DoctorCode] Warning: No API key provided');
        }
    }

    /**
     * Capture an exception and send to DoctorCode
     *
     * @param Throwable $exception The exception to capture
     * @param array $context Additional context
     * @return bool Success status
     */
    public static function captureException(Throwable $exception, array $context = [])
    {
        if (!self::$enabled || !self::$apiKey) {
            return false;
        }

        $payload = [
            'error_message' => $exception->getMessage(),
            'file_path' => $exception->getFile(),
            'line_number' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'context' => array_merge(self::$context, $context, [
                'environment' => self::$environment,
                'php_version' => PHP_VERSION,
                'exception_type' => get_class($exception),
                'timestamp' => date('c'),
            ]),
        ];

        return self::send($payload);
    }

    /**
     * Capture a manual error
     *
     * @param string $message Error message
     * @param array $context Additional context
     * @return bool Success status
     */
    public static function captureError($message, array $context = [])
    {
        if (!self::$enabled || !self::$apiKey) {
            return false;
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $caller = $backtrace[0] ?? [];

        $payload = [
            'error_message' => $message,
            'file_path' => $caller['file'] ?? 'unknown',
            'line_number' => $caller['line'] ?? 0,
            'stack_trace' => self::formatBacktrace($backtrace),
            'context' => array_merge(self::$context, $context, [
                'environment' => self::$environment,
                'php_version' => PHP_VERSION,
                'timestamp' => date('c'),
            ]),
        ];

        return self::send($payload);
    }

    /**
     * Set global context that will be sent with all errors
     *
     * @param array $context Context data
     * @return void
     */
    public static function setContext(array $context)
    {
        self::$context = array_merge(self::$context, $context);
    }

    /**
     * Send payload to DoctorCode API
     *
     * @param array $payload
     * @return bool
     */
    private static function send(array $payload)
    {
        // Use non-blocking async request if possible
        if (function_exists('fastcgi_finish_request')) {
            register_shutdown_function(function () use ($payload) {
                self::sendRequest($payload);
            });
            return true;
        }

        // Otherwise send immediately
        return self::sendRequest($payload);
    }

    /**
     * Actually send the HTTP request
     *
     * @param array $payload
     * @return bool
     */
    private static function sendRequest(array $payload)
    {
        $json = json_encode($payload);

        $ch = curl_init(self::$apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => [
                'X-App-Key: ' . self::$apiKey,
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json),
            ],
            CURLOPT_TIMEOUT => self::$timeout,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("[DoctorCode] Failed to send error: $error");
            return false;
        }

        if ($httpCode !== 200) {
            error_log("[DoctorCode] API returned status $httpCode: $response");
            return false;
        }

        return true;
    }

    /**
     * Format backtrace as string
     *
     * @param array $backtrace
     * @return string
     */
    private static function formatBacktrace(array $backtrace)
    {
        $trace = [];
        foreach ($backtrace as $i => $frame) {
            $file = $frame['file'] ?? 'unknown';
            $line = $frame['line'] ?? 0;
            $function = $frame['function'] ?? '';
            $class = $frame['class'] ?? '';
            $type = $frame['type'] ?? '';

            $call = $class ? "$class$type$function" : $function;
            $trace[] = "#$i $file($line): $call()";
        }

        return implode("\n", $trace);
    }
}
