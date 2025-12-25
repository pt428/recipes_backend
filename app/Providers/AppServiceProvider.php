<?php

namespace App\Providers;

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
        // ✅ FORCE česká locale
        // app()->setLocale('cs');
        // config(['app.locale' => 'cs']);
        // config(['app.fallback_locale' => 'cs']);
    }
}
