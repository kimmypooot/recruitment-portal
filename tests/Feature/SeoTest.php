<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_disallows_authenticated_areas_and_references_sitemap(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('Disallow: /admin');
        $response->assertSee('Disallow: /hrmpsb');
        $response->assertSee('Disallow: /applicant');
        $response->assertSee('Disallow: /appointing-authority');
        $response->assertSee('Sitemap: '.url('/sitemap.xml'));
    }

    public function test_sitemap_xml_includes_static_pages_and_open_vacancies(): void
    {
        $vacancy = Vacancy::factory()->create([
            'status' => 'published',
            'deadline_at' => now()->addWeek(),
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee('<urlset', false);
        $response->assertSee(url('/'), false);
        $response->assertSee(url("/vacancies/{$vacancy->id}/apply"), false);
    }
}
