<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(\App\Http\Middleware\ForceHttps::class);
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
        // Always JSON for api/*, and preserve Laravel's default
        // Accept-header-based detection for everything else — a couple of
        // public web.php routes (e.g. the background-investigation upload
        // form) are submitted via axios expecting JSON even though they
        // aren't under /api/*. Without the `|| $request->expectsJson()`
        // fallback, a validation failure there would render as an HTML
        // redirect instead of a JSON 422, which axios follows silently
        // (redirects are transparent to XHR) and the caller never sees
        // the error.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Serve the CSC-branded Error.vue page for standard HTTP error
        // statuses on web (non-API) requests once debug mode is off, so
        // applicants hitting a 404/419/429/500/etc. in production see a
        // branded, actionable page instead of Laravel's default output.
        // Left alone when APP_DEBUG is on so local/staging debugging still
        // gets the full diagnostic page.
        $exceptions->respond(function (SymfonyResponse $response, Throwable $e, Request $request) {
            if (config('app.debug') || $request->is('api/*')) {
                return $response;
            }

            $status = $response->getStatusCode();

            if (! in_array($status, [400, 403, 404, 419, 429, 500, 503], true)) {
                return $response;
            }

            return Inertia::render('Error', ['status' => $status])
                ->toResponse($request)
                ->setStatusCode($status);
        });
    })->create();
