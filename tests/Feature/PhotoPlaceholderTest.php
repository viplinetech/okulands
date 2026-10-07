<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Support\Placeholder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Photos: a branded placeholder until one is uploaded, and a confirmed "Remove photo" action. */
class PhotoPlaceholderTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP', 'two_factor_recovery_codes' => ['aaaaa-bbbbb'], 'two_factor_confirmed_at' => now(),
        ]);
    }

    public function test_the_placeholder_names_okulands_and_the_section(): void
    {
        $svg = base64_decode(substr(Placeholder::url('Home · Hero photo'), strlen('data:image/svg+xml;base64,')));

        $this->assertStringContainsString('OkuLands', $svg);
        $this->assertStringContainsString('Home · Hero photo', $svg);
    }

    public function test_sections_without_a_photo_show_the_placeholder_instead_of_a_stock_photo(): void
    {
        $this->get('/')->assertOk()->assertSee('data:image/svg+xml;base64,', false)->assertDontSee('/images/ng/', false);
        $this->get('/about')->assertOk()->assertSee('data:image/svg+xml;base64,', false);
        $this->get('/services')->assertOk()->assertDontSee('/images/ng/', false);
    }

    public function test_uploaded_photos_are_shown_instead_of_the_placeholder(): void
    {
        SiteSetting::current()->update(['about_image' => 'uploads/about/photo.webp']);

        $this->assertStringContainsString('uploads/about/photo.webp', SiteSetting::current()->aboutImageUrl());
    }

    public function test_every_uploaded_photo_has_a_remove_button_with_a_confirmation_popup(): void
    {
        SiteSetting::current()->update(['about_image' => 'uploads/about/photo.webp', 'hero_images' => ['uploads/hero/a.webp']]);

        $html = $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])
            ->get('/adminbackend/settings')->assertOk()->assertSee('data-remove-photo', false)->assertSee('Remove photo')->getContent();

        $this->assertStringContainsString('name="remove_about_image"', $html);
        $this->assertStringContainsString('name="remove_hero_images[]"', $html);
        // The popup itself is on every admin page.
        $this->assertStringContainsString('id="confirm-modal"', $html);
    }

    public function test_removing_a_photo_on_save_deletes_it_and_the_placeholder_returns(): void
    {
        SiteSetting::current()->update(['about_image' => 'uploads/about/photo.webp']);

        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])->put(route('admin.settings.update'), [
            'site_name' => 'Oku Lands', 'default_theme' => 'light', 'remove_about_image' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertNull(SiteSetting::current()->fresh()->about_image);
        $this->assertStringContainsString('data:image/svg+xml', SiteSetting::current()->aboutImageUrl());
    }
}
