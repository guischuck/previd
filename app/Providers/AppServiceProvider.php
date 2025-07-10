<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Removed GoogleDocumentAiService singleton - now using only Python extractor
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inertia::share([
            'sidebarOpen' => true,
        ]);

        // Gates para controle de acesso
        Gate::define('manage-companies', function ($user) {
            return $user->isSuperAdmin();
        });

        Gate::define('manage-users', function ($user) {
            return $user->isAdmin();
        });
    }
}
