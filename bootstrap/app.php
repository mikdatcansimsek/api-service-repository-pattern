<?php

use App\Http\Middleware\ApiRateLimitMiddleware;
use App\Http\Middleware\ApiResponseMiddleware;
use App\Http\Middleware\CorsMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->api(prepend: [
            ApiRateLimitMiddleware::class,
            CorsMiddleware::class,
        ]);

        $middleware->alias([
            'cors' => CorsMiddleware::class,
            'api.rate.limit' => ApiRateLimitMiddleware::class,
            'api.response' => ApiResponseMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
