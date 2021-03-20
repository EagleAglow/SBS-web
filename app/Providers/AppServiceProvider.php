<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// fix big font problem
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // fix big font problem
        Paginator::useBootstrap();
    }
}
