<?php

namespace App\Providers;

use App\Contracts\RideMatcherInterface;
use App\Services\BoundingBoxMatcher;
use App\Services\NotificationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RideMatcherInterface::class, BoundingBoxMatcher::class);

        $this->app->singleton(NotificationService::class, function () {
            return new NotificationService(channels: []);
        });
    }

    public function boot(): void {}
}
