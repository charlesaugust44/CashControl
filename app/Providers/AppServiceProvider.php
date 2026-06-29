<?php

namespace App\Providers;

use App\Helpers\Formatter;
use App\Http\ViewComposers\NotificationsComposer;
use Illuminate\Support\Facades\View;
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
        view()->share('fmt', new Formatter());

        View::composer('components.header', NotificationsComposer::class);
    }
}
