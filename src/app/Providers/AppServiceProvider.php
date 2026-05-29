<?php

namespace App\Providers;

use App\Contracts\RideMatcherInterface;
use App\Services\AllDriversMatcher;
use App\Services\BoundingBoxMatcher;
use App\Services\NotificationService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $matcher = config('app.ride_matcher') === 'all'
            ? AllDriversMatcher::class
            : BoundingBoxMatcher::class;

        $this->app->bind(RideMatcherInterface::class, $matcher);

        $this->app->singleton(NotificationService::class, function () {
            return new NotificationService(channels: []);
        });
    }

    public function boot(): void
    {
        // Garante que rotas geradas (route(), redirect()) usem o host real da
        // requisição — necessário quando acessado por IP local (ex: celular na rede).
        if (!$this->app->runningInConsole()) {
            URL::forceRootUrl(request()->schemeAndHttpHost());
        }
    }
}
