<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        // inne middleware
    ];

    protected $middlewareGroups = [
        'web' => [
            // inne middleware
        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $routeMiddleware = [
        // inne middleware
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
    ];
}
