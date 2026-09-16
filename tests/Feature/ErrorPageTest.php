<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    public function test_web_404_renders_the_branded_error_page_when_debug_is_off(): void
    {
        config(['app.debug' => false]);

        $response = $this->get('/this-route-does-not-exist');

        $response->assertStatus(404);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Error')
            ->where('status', 404)
        );
    }

    public function test_api_404_still_renders_json_not_the_branded_page(): void
    {
        config(['app.debug' => false]);

        $response = $this->getJson('/api/this-route-does-not-exist');

        $response->assertStatus(404);
        $response->assertHeader('content-type', 'application/json');
    }

    public function test_debug_mode_bypasses_the_branded_error_page(): void
    {
        config(['app.debug' => true]);

        $response = $this->get('/this-route-does-not-exist');

        $response->assertStatus(404);
        // With debug on, Laravel's own diagnostic page is served — not Inertia.
        $response->assertHeader('content-type', 'text/html; charset=UTF-8');
    }
}
