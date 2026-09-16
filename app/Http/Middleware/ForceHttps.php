<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects plain-HTTP requests to HTTPS in production, and forces the URL
 * generator to emit https:// links (so route()/asset() are correct behind a
 * TLS-terminating proxy — see the TRUSTED_PROXIES comment in bootstrap/app.php
 * for why $request->secure() needs that in place to detect HTTPS at all).
 *
 * Local/dev environments are left alone (php artisan serve has no TLS cert).
 */
class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('production')) {
            return $next($request);
        }

        URL::forceScheme('https');

        if (! $request->secure()) {
            $httpsUrl = 'https://'.$request->getHttpHost().$request->getRequestUri();

            return redirect()->to($httpsUrl, 301);
        }

        return $next($request);
    }
}
