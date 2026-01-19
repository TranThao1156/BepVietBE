<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Services\AIChatService;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        parent::boot();

        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));

        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }

    public function register(): void
    {
        $this->app->singleton(AIChatService::class, function ($app) {
            return new AIChatService();
        });
    }
}
