<?php

namespace DoctorCode\Laravel;

use DoctorCode\Client;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Merge default config
        $this->mergeConfigFrom(__DIR__ . '/config.php', 'doctorcode');

        // Initialize DoctorCode client
        $this->app->singleton('doctorcode', function ($app) {
            return $this->initializeClient();
        });
    }

    /**
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/config.php' => config_path('doctorcode.php'),
        ], 'doctorcode-config');

        // Auto-register exception handler if enabled
        if (config('doctorcode.enabled', false) && config('doctorcode.auto_capture', true)) {
            $this->registerExceptionHandler();
        }
    }

    /**
     * Initialize the DoctorCode client
     *
     * @return void
     */
    protected function initializeClient()
    {
        Client::init([
            'api_key' => config('doctorcode.api_key') ?? env('DOCTORCODE_API_KEY'),
            'enabled' => config('doctorcode.enabled', env('DOCTORCODE_ENABLED', true)),
            'environment' => config('doctorcode.environment', env('DOCTORCODE_ENVIRONMENT', app()->environment())),
            'timeout' => config('doctorcode.timeout', 5),
        ]);
    }

    /**
     * Register the exception handler
     *
     * @return void
     */
    protected function registerExceptionHandler()
    {
        $handler = $this->app->make(ExceptionHandler::class);

        if (method_exists($handler, 'reportable')) {
            $handler->reportable(function (\Throwable $e) {
                Client::captureException($e);
            });
        }
    }
}
