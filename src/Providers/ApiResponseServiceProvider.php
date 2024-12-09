<?php

declare(strict_types=1);

namespace Aliziodev\ApiResponse\Providers;

use Aliziodev\ApiResponse\Response\ApiResponse;
use Illuminate\Support\ServiceProvider;

class ApiResponseServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->singleton('api-response', function ($app) {
            return new ApiResponse();
        });

    }
}
