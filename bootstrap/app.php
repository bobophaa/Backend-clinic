<?php

use Illuminate\Foundation\Application;
// use Illuminate\Foundation\Http\Middleware\HandleCors;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;

return Application::configure(basePath: dirname(__DIR__))
    // ->withMiddleware(function (Middleware $middleware) {
    //     $middleware->append(HandleCors::class);
    // })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->create();
