<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
// add this:
use App\Http\Middleware\MarkForumSeenAt;

class Kernel extends HttpKernel
{
    protected $middlewareGroups = [
        'web' => [
            // Laravel defaults…
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

            // 👇 add this line anywhere in the web group:
            MarkForumSeenAt::class,
        ],

        'api' => [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    // (optional) give it an alias if you ever want route-level usage:
    protected $middlewareAliases = [
        // …
        'forum.seen' => MarkForumSeenAt::class,
    ];
}
