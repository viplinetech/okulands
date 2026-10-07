<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Support\StaticText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The written content of the website is fixed; the admin manages photos, contact details and figures only. */
class StaticWebsiteTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP', 'two_factor_recovery_codes' => ['aaaaa-bbbbb'], 'two_factor_confirmed_at' => now(),
        ]);
    }

    public function test_the_admin_has_no_page_text_editor(): void
    {
        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])
            ->get('/adminbackend/content')->assertNotFound();

        $labels = collect(\App\Support\Nav::admin())->flatten(1)->pluck('label')->all();
        $this->assertNotContains('Page content', $labels);
    }

    public function test_the_written_wording_is_always_the_fixed_text_even_if_old_values_are_in_the_database(): void
    {
        SiteSetting::current()->update(['hero_headline' => 'Old headline from the database', 'ceo_message' => 'Old CEO letter']);

        $this->get('/')->assertOk()->assertSee('Homes built')->assertDontSee('Old headline from the database');
        $this->get('/about')->assertDontSee('Old CEO letter');
    }

    public function test_the_admin_settings_page_manages_photos_and_contact_details_not_writing(): void
    {
        $html = $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])
            ->get('/adminbackend/settings')->assertOk()->getContent();

        foreach (['name="hero_headline"', 'name="about_body"', 'name="ceo_message"', 'name="mission"', 'name="values', 'name="commitments'] as $removed) {
            $this->assertStringNotContainsString($removed, $html);
        }
        foreach (['name="about_image"', 'name="about_image_2"', 'name="ceo_photo"', 'name="phone"', 'name="email"', 'hero_images'] as $kept) {
            $this->assertStringContainsString($kept, $html);
        }
    }

    public function test_saving_settings_keeps_the_fixed_wording_and_only_updates_photos_and_contacts(): void
    {
        SiteSetting::current()->update(['about_headline' => 'Stored headline']);

        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])->put(route('admin.settings.update'), [
            'site_name' => 'Oku Lands', 'default_theme' => 'light', 'phone' => '08000000000',
        ])->assertSessionHasNoErrors();

        $this->assertSame('08000000000', SiteSetting::current()->phone);
        $this->get('/about')->assertSee(StaticText::aboutHeadline(), false);
    }

    public function test_the_ceo_section_and_static_wording_are_shown_without_any_setup(): void
    {
        $this->get('/about')->assertSee('Message from our CEO')->assertSee(StaticText::ceoName());
        $this->get('/')->assertSee('A word from our CEO');
    }
}
