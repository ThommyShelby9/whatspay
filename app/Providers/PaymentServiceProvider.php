
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PaymentService;
use App\Services\WalletService;
use App\Services\CampaignBudgetService;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService($app->make(WalletService::class));
        });

        $this->app->singleton(CampaignBudgetService::class, function ($app) {
            return new CampaignBudgetService(
                $app->make(WalletService::class),
                $app->make(PaymentService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function bootstrap(): void
    {
        //
    }
}
