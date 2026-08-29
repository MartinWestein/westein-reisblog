<?php

namespace App\Providers;

use App\Listeners\MarkEmailVerifiedAfterPasswordReset;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::before(function ($user, string $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        Paginator::useBootstrapFive();

        Event::listen(
            PasswordReset::class,
            MarkEmailVerifiedAfterPasswordReset::class,
        );
    }
}
