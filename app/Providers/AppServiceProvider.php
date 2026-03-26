<?php

namespace App\Providers;

use App\Models\Currency;
use App\Policies\Api\V1\CurrencyPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        \App\Models\Transaction::observe(\App\Observers\ProductObserver::class);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });
        Gate::policy(Currency::class, CurrencyPolicy::class);
    }
}
