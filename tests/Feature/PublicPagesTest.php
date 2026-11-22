<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public static function pages(): array
    {
        return array_map(fn ($p) => [$p], [
            '/', '/destinations', '/study-in-germany', '/services', '/exam-prep', '/contact', '/b2b',
            '/study-in-finland', '/study-in-netherlands', '/study-in-lithuania', '/study-in-poland',
            '/study-in-latvia', '/study-in-spain', '/study-in-italy', '/study-in-austria',
            '/germany/studienkolleg', '/germany/bachelor', '/germany/master', '/germany/medicine',
            '/privacy-policy', '/terms-conditions', '/disclaimer', '/refund-policy',
        ]);
    }

    #[DataProvider('pages')]
    public function test_page_renders(string $path): void
    {
        $this->get($path)
            ->assertOk()
            ->assertSee('EUROPA', false)
            ->assertSee('data-enquiry="counselling"', false)
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_country_page_renders_its_content_blocks(): void
    {
        $this->get('/study-in-lithuania')
            ->assertOk()
            ->assertSee('Vilnius University')
            ->assertSee('Frequently Asked Questions')
            ->assertSee('Can I study in Lithuania entirely in English?');
    }

    public function test_legacy_urls_redirect_permanently(): void
    {
        $this->get('/about')->assertRedirect('/')->assertStatus(301);
        $this->get('/finland')->assertRedirect('/study-in-finland')->assertStatus(301);
        $this->get('/germany-master')->assertRedirect('/germany/master')->assertStatus(301);
        $this->get('/study-in-germany/bachelor')->assertRedirect('/germany/bachelor')->assertStatus(301);
        $this->get('/exams')->assertRedirect('/exam-prep')->assertStatus(301);
        $this->get('/terms')->assertRedirect('/terms-conditions')->assertStatus(301);
    }

    public function test_unknown_pages_return_404(): void
    {
        $this->get('/study-in-mars')->assertNotFound()->assertSee('Page not found');
        $this->get('/germany/pharmacy')->assertNotFound();
    }

    public function test_sitemap_and_robots(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml')->assertSee('/study-in-spain', false);
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin');
    }
}
