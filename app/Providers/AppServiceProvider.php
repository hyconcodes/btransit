<?php

namespace App\Providers;

use App\Models\Ride;
use App\Observers\RideObserver;
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
        Ride::observe(RideObserver::class);
    }
}
