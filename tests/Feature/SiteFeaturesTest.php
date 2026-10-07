<?php

namespace Tests\Feature;

use App\Models\FaqItem;
use App\Models\Lead;
use App\Models\ReferralVisit;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private function realtor(string $name): User
    {
        return User::create([
            'name' => $name,
            'email' => strtolower($name).'@example.test',
            'password' => 'password',
            'role' => 'realtor',
            'status' => 'active',
        ])->fresh();
    }

    public function test_every_public_page_renders(): void
    {
        foreach (['/', '/about', '/properties', '/services', '/gallery', '/blog', '/contact', '/faqs', '/login', '/register'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_help_center_lists_active_questions_and_hides_inactive_ones(): void
    {
        FaqItem::create(['category' => 'Payments', 'question' => 'Can I pay in instalments?', 'answer' => 'Yes, on select properties.']);
        FaqItem::create(['category' => 'Payments', 'question' => 'Hidden question?', 'answer' => 'Nope.', 'is_active' => false]);

        $this->get('/faqs')
            ->assertOk()
            ->assertSee('Can I pay in instalments?')
            ->assertSee('FAQPage', false)
            ->assertDontSee('Hidden question?');
    }

    public function test_faq_feedback_is_counted(): void
    {
        $faq = FaqItem::create(['category' => 'General', 'question' => 'Q?', 'answer' => 'A.']);

        $this->postJson(route('faq.feedback', $faq), ['helpful' => 'yes'])->assertOk();
        $this->postJson(route('faq.feedback', $faq), ['helpful' => 'no'])->assertOk();
        $this->postJson(route('faq.feedback', $faq), ['helpful' => 'maybe'])->assertStatus(422);

        $faq->refresh();
        $this->assertSame(1, $faq->helpful_yes);
        $this->assertSame(1, $faq->helpful_no);
    }

    public function test_referral_link_tags_visitor_and_credits_later_enquiries(): void
    {
        $john = $this->realtor('John');

        $this->get('/ref/'.$john->referral_code)
            ->assertRedirect('/')
            ->assertCookie(\App\Http\Controllers\ReferralController::COOKIE);

        $visit = ReferralVisit::firstOrFail();
        $this->assertSame($john->id, $visit->referrer_id);

        $this->withCookie(\App\Http\Controllers\ReferralController::COOKIE, $visit->visitor_token)
            ->post('/contact', ['name' => 'Buyer', 'phone' => '0800000000', 'message' => 'Interested'])
            ->assertRedirect();

        $this->assertSame($john->id, Lead::firstOrFail()->referrer_id);
    }

    public function test_the_most_recent_referrer_takes_over_the_credit(): void
    {
        $john = $this->realtor('John');
        $mary = $this->realtor('Mary');

        $this->get('/ref/'.$john->referral_code);
        $token = ReferralVisit::firstOrFail()->visitor_token;

        // The same visitor later arrives through Mary's link: she takes over the credit (last-touch).
        $this->withCookie(\App\Http\Controllers\ReferralController::COOKIE, $token)->get('/ref/'.$mary->referral_code);

        $this->assertSame(1, ReferralVisit::count());
        $this->assertSame($mary->id, ReferralVisit::firstOrFail()->referrer_id);
    }

    public function test_referral_link_rejects_unknown_codes_and_external_redirects(): void
    {
        $john = $this->realtor('John');

        $this->get('/ref/does-not-exist')->assertRedirect('/');
        $this->assertSame(0, ReferralVisit::count());

        $this->get('/ref/'.$john->referral_code.'?to=https://evil.example')->assertRedirect('/');
        $this->get('/ref/'.$john->referral_code.'?to=//evil.example')->assertRedirect('/');
        $this->get('/ref/'.$john->referral_code.'?to=/properties')->assertRedirect('/properties');
    }

    public function test_only_approved_reviews_show_and_there_is_no_public_review_form(): void
    {
        Testimonial::create(['client_name' => 'Ada', 'rating' => 5, 'content' => 'Smooth process from inspection to documents.', 'approved' => false]);
        Testimonial::create(['client_name' => 'Bola', 'rating' => 5, 'content' => 'Honest people and clear paperwork.', 'approved' => true]);

        $about = $this->get('/about')->assertOk();
        $about->assertSee('Honest people and clear paperwork.')->assertDontSee('Smooth process from inspection')->assertDontSee('Tell us how it went');

        // Reviews are entered by the admin only; the old public submission endpoint is gone.
        $this->post('/testimonials', ['client_name' => 'Eve', 'rating' => 5, 'content' => 'Spam spam spam spam.'])->assertNotFound();
        $this->assertSame(2, Testimonial::count());
    }

    public function test_the_ceo_section_is_always_shown_with_the_fixed_letter(): void
    {
        $this->get('/about')->assertSee('Message from our CEO')->assertSee(\App\Support\StaticText::ceoName());
        $this->get('/')->assertSee('A word from our CEO');
    }
}
