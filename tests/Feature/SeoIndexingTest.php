<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoIndexingTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP', 'two_factor_recovery_codes' => [], 'two_factor_confirmed_at' => now(),
        ]);
    }

    public function test_by_default_the_site_is_indexable(): void
    {
        $this->get('/')->assertSee('<meta name="robots" content="index, follow">', false);
        $body = $this->get('/robots.txt')->getContent();
        $this->assertStringNotContainsString("Disallow: /\n", $body);
        $this->assertStringContainsString('Sitemap:', $body);
    }

    public function test_turning_indexing_off_blocks_every_crawler_site_wide(): void
    {
        SiteSetting::current()->update(['seo_indexing_enabled' => false]);

        $this->get('/')->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        $this->get('/about')->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->get('/robots.txt')->assertSee("User-agent: *\nDisallow: /", false);
    }

    public function test_an_admin_can_toggle_indexing_from_settings(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->withSession(['two_factor_passed' => true, 'step_up_at' => time()])
            ->put(route('admin.settings.update'), [
                'site_name' => 'Oku Lands', 'default_theme' => 'light',
                'seo_indexing_enabled' => '0',
            ])->assertRedirect();

        $this->assertFalse(SiteSetting::current()->indexingEnabled());

        $this->get('/')->assertSee('noindex', false);
        $this->assertDatabaseHas('activity_logs', ['action' => 'admin.seo.indexing_toggled']);
    }

    public function test_meta_title_falls_back_to_the_admin_default_on_pages_without_their_own_title(): void
    {
        // The home page sets no explicit <x-layouts.public title="…">, so it uses this fallback.
        SiteSetting::current()->update(['seo_meta_title' => 'Custom Default Title']);

        $this->get('/')->assertSee('<title>Custom Default Title</title>', false);
    }

    public function test_the_seo_settings_tab_renders_for_an_admin(): void
    {
        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true, 'step_up_at' => time()])
            ->get(route('admin.settings'))
            ->assertOk()
            ->assertSee('Search engine indexing')
            ->assertSee('View /robots.txt', false)
            ->assertSee('View /sitemap.xml', false);
    }

    public function test_sitemap_is_empty_while_indexing_is_off(): void
    {
        SiteSetting::current()->update(['seo_indexing_enabled' => false]);

        $this->get('/sitemap.xml')->assertDontSee('<url>', false);
    }
}
