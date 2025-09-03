<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Forum\Post;
use App\Models\Forum\Thread;
use App\Policies\Forum\PostPolicy;
use App\Policies\Forum\ThreadPolicy;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Thread::class => ThreadPolicy::class,
        Post::class   => PostPolicy::class,
    ];
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
        //
    }
}
