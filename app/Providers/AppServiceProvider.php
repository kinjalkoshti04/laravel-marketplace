<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        // Strong passwords for register, reset password and change password
        // (all of them use Password::defaults()). The same rules are checked
        // in the browser by resources/js/auth-validation.js.
        Password::defaults(fn () => Password::min(8)
            ->max(64)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols());
    }
}
