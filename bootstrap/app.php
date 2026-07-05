<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\AddCspHeaders::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\HandleInertiaRequests::class);

        $middleware->alias([
            'role'           => \App\Http\Middleware\EnsureRole::class,
            'admin-access'   => \App\Http\Middleware\EnsureAdminAccess::class,
            'pipeline-stage' => \App\Http\Middleware\EnsurePipelineStageAccessible::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
