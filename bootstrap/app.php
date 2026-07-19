<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            // Register Customer / User API Routes
            Route::middleware('api')
                ->prefix('api')
                ->group(__DIR__ . '/../routes/api_user.php');

            // Register Admin API Routes
            Route::middleware('api')
                ->prefix('api/admin') // Automatically prefixes all admin routes with /api/admin
                ->group(__DIR__ . '/../routes/api_admin.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
