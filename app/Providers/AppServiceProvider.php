<?php

namespace App\Providers;

use App\Helpers\Formatter;
use App\Http\ViewComposers\NotificationsComposer;
use App\Support\UnityContext;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(UnityContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $view->with('fmt', new Formatter());
        });

        View::composer('components.header', NotificationsComposer::class);
    }
}
