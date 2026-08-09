<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL; // Idinagdag natin ito
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Schema::defaultStringLength(191);

        // Eto ang mag-force ng HTTPS kapag nasa production (Render)
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }
    }
}