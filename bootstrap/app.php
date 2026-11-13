<?php

use App\Http\Middleware\BindSessionToClient;
use App\Http\Middleware\DataGuard;
use App\Http\Middleware\EnsureHcmRole;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void
    {
        $middleware->append(SecurityHeaders::class);
        $middleware->appendToGroup('web', BindSessionToClient::class);

        $middleware->alias([
            'data.guard' => DataGuard::class,
            'role.hcm'   => EnsureHcmRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void
    {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
