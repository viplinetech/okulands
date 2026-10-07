<?php

namespace Tests\Feature;

use App\Models\NewsPost;
use App\Models\Property;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\RichText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Public-site polish: legal pages, contextual call-to-action buttons, listing cards and tidy blog excerpts. */
class SiteCleanupTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP', 'two_factor_recovery_codes' => ['aaaaa-bbbbb'], 'two_factor_confirmed_at' => now(),
        ]);
    }

    private function property(User $by, string $status = 'available'): Property
    {
        return Property::create([
            'title' => 'Amansea Plot', 'slug' => 'amansea-plot', 'sector' => 'real_estate', 'price' => 5_000_000,
            'location' => 'Amansea', 'status' => $status, 'created_by' => $by->id,
        ]);
    }

    public function test_the_privacy_policy_and_terms_pages_exist_and_are_linked_from_every_footer(): void
    {
        $this->get('/privacy-policy')->assertOk()->assertSee('Privacy Policy')->assertSee('Nigeria Data Protection Act')->assertSee('never your name', false)->assertSee('Last updated');
        $this->get('/terms')->assertOk()->assertSee('Terms of Use')->assertSee('realtor programme');
        $this->get('/not-a-legal-page')->assertNotFound();

        foreach (['/', '/about', '/contact', '/properties'] as $url) {
            $this->get($url)->assertSee(route('legal', 'privacy-policy'), false)->assertSee(route('legal', 'terms'), false);
        }

        // The contact form tells people what happens to their details.
        $this->get('/contact')->assertSee('Your details are used only to reply to you');
    }

    public function test_every_listing_card_has_its_own_book_an_inspection_button(): void
    {
        $admin = $this->admin();
        $property = $this->property($admin);

        $this->get('/properties')->assertSee('Book an inspection')->assertSee(route('properties.show', $property->slug).'#book', false);

        // The property page has the booking form the button jumps to.
        $this->get('/properties/'.$property->slug)->assertSee('id="book"', false)->assertSee('Request inspection');

        // Sold listings offer a different next step.
        $property->update(['status' => 'sold']);
        $this->get('/properties')->assertSee('Find similar')->assertDontSee('Book an inspection');
    }

    public function test_closing_panels_offer_the_next_step_that_fits_the_page(): void
    {
        $this->get('/properties')->assertSee('Can’t find the right plot?')->assertSee('Contact Oku Lands')->assertSee('Become a realtor')->assertSee(route('contact', ['interest' => 'buying']), false);
        $this->get('/services')->assertSee('Contact Oku Lands')->assertSee('Book a free inspection');
        $this->get('/blog')->assertSee('Contact Oku Lands')->assertSee('Browse properties');
        $this->get('/gallery')->assertSee('Book a free inspection')->assertSee('Contact Oku Lands');
        $this->get('/about')->assertSee('Ready to secure your future?')->assertSee('Book a free inspection')->assertSee('Become a realtor');
    }

    public function test_repeated_and_decorative_sections_are_gone(): void
    {
        $home = $this->get('/')->assertOk();
        $home->assertDontSee('marquee-track', false)->assertDontSee('OKU LANDS', false)->assertDontSee('Read about Oku Lands', false);

        // The FAQ lives on Home and the Help Center, not on Services and Contact as well.
        $this->get('/services')->assertDontSee('Browse the full Help Center');
        $this->get('/contact')->assertDontSee('Browse the full Help Center')->assertDontSee('Quick answers');
    }

    public function test_blog_excerpts_use_the_opening_paragraph_not_headings_and_list_items_run_together(): void
    {
        $post = NewsPost::create([
            'title' => 'Verify titles', 'slug' => 'verify-titles', 'published_at' => now()->subDay(), 'author_id' => $this->admin()->id,
            'body' => '<p>Most disputes begin with a skipped check. Here is our short list.</p><h2>1. Ask for the survey plan</h2><p>It proves the boundaries.</p><ul><li>Survey plan</li><li>Deed</li></ul>',
        ]);

        $this->assertSame('Most disputes begin with a skipped check. Here is our short list.', $post->summary(200));
        $this->assertStringNotContainsString('1. Ask', $post->summary(200));
        // Plain text written before the editor existed still works.
        $this->assertSame('First para.', RichText::lead("First para.\n\nSecond para."));
        // And a post that opens with a list falls back to its text.
        $this->assertSame('One Two', RichText::lead('<ul><li>One</li><li>Two</li></ul>'));
    }
}
