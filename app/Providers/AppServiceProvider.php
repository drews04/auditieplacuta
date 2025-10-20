<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Relations\Relation;

use App\Models\ThemePool;
use App\Models\ContestTheme;
use App\Models\Forum\Thread as ForumThread;
use App\Models\Forum\Reply as ForumReply;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Allow BOTH aliases and legacy FQCNs (prevents 500s)
        Relation::morphMap([
            // Theme Likes
            'contest'      => ContestTheme::class,
            'pool'         => ThemePool::class,

            // Forum Likes
            'forum_thread' => ForumThread::class,
            'forum_reply'  => ForumReply::class,

            // Legacy FQCNs already stored in DB
            'App\\Models\\Forum\\Thread' => ForumThread::class,
            'App\\Models\\Forum\\Reply'  => ForumReply::class,
        ]);

        // Neon pagination
        Paginator::defaultView('vendor.pagination.neon');
        Paginator::defaultSimpleView('vendor.pagination.neon-simple');
    }
}
