<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\AxisResponse;
use App\Observers\AxisResponseObserver;

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
   public function boot()
{
AxisResponse::observe(AxisResponseObserver::class);
}
}
