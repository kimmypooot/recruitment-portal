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

        // Without this, $request->secure() can resolve false behind a
        // reverse proxy/load balancer that terminates TLS (Nginx, an ARR/
        // CDN front-end, etc.), which silently disables the HSTS header in
        // AddCspHeaders and can misbehave with secure-cookie logic. Off by
        // default (empty TRUSTED_PROXIES) so a direct, un-proxied deployment
        // is unaffected; set TRUSTED_PROXIES to the proxy's IP/CIDR (or '*'
        // only if every request genuinely arrives through a trusted proxy
        // layer you control) once one is in front of the app.
        if ($proxies = trim((string) env('TRUSTED_PROXIES', ''))) {
            $middleware->trustProxies(
                at: $proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)),
                headers: Request::HEADER_X_FORWARDED_FOR
                    | Request::HEADER_X_FORWARDED_HOST
                    | Request::HEADER_X_FORWARDED_PORT
                    | Request::HEADER_X_FORWARDED_PROTO
                    | Request::HEADER_X_FORWARDED_AWS_ELB,
            );
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
