<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Vacancy;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    /**
     * Served as a route (not a static public/robots.txt file) so the
     * Sitemap: directive can always be an absolute, environment-correct
     * URL instead of a hardcoded domain.
     */
    public function robots(): Response
    {
        $disallowed = [
            '/applicant', '/admin', '/hrmpsb', '/appointing-authority',
            '/forgot-password', '/reset-password', '/email/verify',
            '/background-investigation/upload', '/auth/google',
            '/privacy-policy', '/terms-of-service',
        ];

        $lines = ['User-agent: *'];
        foreach ($disallowed as $path) {
            $lines[] = "Disallow: {$path}";
        }
        $lines[] = '';
        $lines[] = 'Sitemap: '.url('/sitemap.xml');

        return response(implode("\n", $lines), 200)
            ->header('Content-Type', 'text/plain');
    }

    public function sitemap(): Response
    {
        $urls = Cache::remember('sitemap.xml', now()->addHour(), function () {
            $staticPaths = [
                ['loc' => '/', 'priority' => '1.0'],
                ['loc' => '/how-to-apply', 'priority' => '0.6'],
                ['loc' => '/help', 'priority' => '0.5'],
                ['loc' => '/about', 'priority' => '0.5'],
                ['loc' => '/login', 'priority' => '0.4'],
                ['loc' => '/register', 'priority' => '0.4'],
                ['loc' => '/track-application', 'priority' => '0.4'],
            ];

            $vacancyPaths = Vacancy::open()
                ->orderByDesc('published_at')
                ->limit(500)
                ->get(['id', 'updated_at'])
                ->map(fn (Vacancy $vacancy) => [
                    'loc' => "/vacancies/{$vacancy->id}/apply",
                    'priority' => '0.8',
                    'lastmod' => $vacancy->updated_at?->toAtomString(),
                ]);

            return collect($staticPaths)->concat($vacancyPaths);
        });

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
