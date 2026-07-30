<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddCspHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $viteDevOrigins = '';

        if (app()->environment('local') && config('app.debug')) {
            $viteDevOrigins = ' http://127.0.0.1:5183 ws://127.0.0.1:5183';
        }

        $csp = "default-src 'self'; "
            . "script-src 'self' 'unsafe-inline'{$viteDevOrigins}; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com{$viteDevOrigins}; "
            . "font-src 'self' data: https://fonts.gstatic.com; "
            . "img-src 'self' data: https://*.googleusercontent.com; "
            . "connect-src 'self' https://fonts.googleapis.com https://fonts.gstatic.com{$viteDevOrigins}; "
            . "form-action 'self'; "
            . "base-uri 'self'; "
            . "frame-ancestors 'none'; "
            . "object-src 'none'";

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
