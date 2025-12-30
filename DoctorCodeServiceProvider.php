<?php

namespace DoctorCode\SDK;

use DoctorCode\SDK\Exceptions\DoctorCodeExceptionHandler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;

class DoctorCodeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/doctorcode.php', 'doctorcode'
        );

        // Bind the DoctorCode client
        $this->app->singleton(DoctorCodeClient::class, function ($app) {
            return new DoctorCodeClient(
                config('doctorcode.api_key'),
                config('doctorcode.api_url', 'https://doctorcode.dev/api')
            );
        });

        // Register exception handler integration
        if (config('doctorcode.enabled', true)) {
            $this->app->extend(ExceptionHandler::class, function ($handler, $app) {
                return new DoctorCodeExceptionHandler($handler, $app[DoctorCodeClient::class]);
            });
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/doctorcode.php' => config_path('doctorcode.php'),
            ], 'doctorcode-config');
        }
    }
}
