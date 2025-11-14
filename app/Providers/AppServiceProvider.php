<?php

namespace App\Providers;

use App\Services\DirectWhatsAppService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Ajout de l'import URL


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
                $this->app->singleton(DirectWhatsAppService::class, function ($app) {
            return new DirectWhatsAppService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        if (config('app.env') === 'production') {
          URL::forceScheme('https');
   }
    }
}
