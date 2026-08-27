<?php

namespace Tests\Feature;

use Tests\TestCase;

class SiteAnimationTest extends TestCase
{
    public function test_public_pages_load_the_shared_animation_bundle(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('resources/js/app.js');
    }

    public function test_animation_bundle_is_progressive_and_respects_reduced_motion(): void
    {
        $javascript = file_get_contents(resource_path('js/app.js'));
        $stylesheet = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString("'IntersectionObserver' in window", $javascript);
        $this->assertStringContainsString('prefers-reduced-motion: reduce', $javascript);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $stylesheet);
        $this->assertStringContainsString('[data-scroll-reveal-ready].is-revealed', $stylesheet);
    }
}
