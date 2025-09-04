<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
{
    // Use our custom neon pagination everywhere
    \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.neon');
    \Illuminate\Pagination\Paginator::defaultSimpleView('vendor.pagination.neon-simple');
}
}
