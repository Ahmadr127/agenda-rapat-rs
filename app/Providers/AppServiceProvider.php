<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\User;
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
        if (env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }

        // Gate didaftarkan dari katalog permission (identifier teknis).
        // Role apa pun yang memegang permission tersebut lolos — tidak ada
        // nama role yang disebut di sini.
        foreach (Permission::keys() as $key) {
            Gate::define($key, fn (User $user) => $user->hasPermission($key));
        }
    }
}
