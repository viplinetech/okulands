<?php

namespace Tests\Feature;

use App\Http\Controllers\ReferralController;
use App\Models\Lead;
use App\Models\Property;
use App\Models\ReferralVisit;
use App\Models\User;
use App\Notifications\NewLeadAlert;
use App\Notifications\NewReferralLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Someone arrives through a realtor's link and books an inspection:
 * the ADMIN gets every detail; the REALTOR sees the person's name and which property,
 * but never their phone, email or message.
 */
class RealtorPrivacyTest extends TestCase
{
    use RefreshDatabase;

    private function adminWith2fa(): User
    {
        return User::factory()->admin()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP', 'two_factor_recovery_codes' => ['aaaaa-bbbbb'], 'two_factor_confirmed_at' => now(),
        ]);
    }

    private function property(User $by): Property
    {
        return Property::create([
            'title' => 'Amansea Residential Plots', 'slug' => 'amansea-plots', 'sector' => 'real_estate', 'price' => 4_500_000,
            'location' => 'Amansea', 'status' => 'available', 'created_by' => $by->id,
        ]);
    }

    /** Visitor arrives via the realtor's link, then books an inspection for a property. */
    private function visitorBooks(User $realtor, Property $property): void
    {
        $this->get('/ref/'.$realtor->referral_code);
        $token = ReferralVisit::firstOrFail()->visitor_token;

        $this->withCookie(ReferralController::COOKIE, $token)->post('/contact', [
            'name' => 'Chidi Okonkwo', 'phone' => '8031234567', 'email' => 'chidi@example.com',
            'message' => 'I can come on Saturday morning.', 'interest' => 'inspection', 'property_id' => $property->id,
        ])->assertSessionHasNoErrors();
    }

    public function test_the_admin_receives_the_full_booking_with_who_referred_it(): void
    {
        $admin = $this->adminWith2fa();
        $realtor = User::factory()->create(['name' => 'Ngozi Realtor']);
        $property = $this->property($admin);

        $this->visitorBooks($realtor, $property);

        $lead = Lead::firstOrFail();
        $this->assertSame('inspection', $lead->type);
        $this->assertSame($realtor->id, $lead->referrer_id);
        $this->assertSame($property->id, $lead->property_id);

        // The admin notification says what, who, how to reach them and which realtor sent them.
        $data = $admin->notifications()->firstOrFail()->data;
        $this->assertSame('Inspection booked', $data['title']);
        foreach (['Chidi Okonkwo', 'Amansea Residential Plots', '8031234567', 'Ngozi Realtor'] as $detail) {
            $this->assertStringContainsString($detail, $data['body']);
        }

        // And the admin's lead page has everything.
        $this->actingAs($admin)->withSession(['two_factor_passed' => true])->get(route('admin.leads.show', $lead))
            ->assertOk()->assertSee('Chidi Okonkwo')->assertSee('8031234567')->assertSee('chidi@example.com')->assertSee('Saturday morning')->assertSee('Ngozi Realtor');
    }

    public function test_the_realtor_is_told_about_the_booking_but_never_who_made_it(): void
    {
        $admin = $this->adminWith2fa();
        $realtor = User::factory()->create();
        $property = $this->property($admin);

        $this->visitorBooks($realtor, $property);
        $lead = Lead::firstOrFail();

        // Their notification: person's name + which property — never phone, email or message.
        $data = $realtor->notifications()->firstOrFail()->data;
        $this->assertSame('Chidi Okonkwo booked an inspection through your link', $data['title']);
        $this->assertStringContainsString('Chidi Okonkwo', $data['body']);
        $this->assertStringContainsString('Amansea Residential Plots', $data['body']);
        foreach (['1234567', 'chidi@example.com', 'Saturday'] as $secret) {
            $this->assertStringNotContainsString($secret, json_encode($data));
        }

        // Their Booked inspections page: shows name + property, never phone/email/message.
        $this->actingAs($realtor);
        foreach (['/realtor/leads', '/realtor/leads?kind=inspections'] as $url) {
            $page = $this->get($url)->assertOk()->assertSee('Chidi Okonkwo')->assertSee('Amansea Residential Plots');
            foreach (['8031234567', 'chidi@example.com', 'Saturday morning', 'tel:', 'wa.me'] as $secret) {
                $page->assertDontSee($secret, false);
            }
        }
        // Dashboard shows bookings summary (no name, just type + property).
        $this->get('/realtor')->assertOk()->assertSee('Inspection booked')->assertSee('Amansea Residential Plots');
        foreach (['8031234567', 'chidi@example.com', 'Saturday morning'] as $secret) {
            $this->get('/realtor')->assertDontSee($secret, false);
        }
        $this->get('/realtor/leads')->assertSee('Ref #'.$lead->id);
        $this->get('/realtor/alerts')->assertOk()->assertSee('Chidi Okonkwo booked an inspection through your link');
    }

    public function test_the_realtor_query_exposes_name_only_never_contact_details(): void
    {
        $realtor = User::factory()->create();
        Lead::create(['name' => 'Secret Person', 'phone' => '08099999999', 'email' => 's@example.com', 'message' => 'private', 'type' => 'inspection', 'referrer_id' => $realtor->id]);

        $booking = $realtor->referredBookings()->firstOrFail();

        // Name is intentionally visible to the realtor; contact details are not loaded at all.
        $this->assertSame('Secret Person', $booking->name);
        $this->assertNull($booking->phone);
        $this->assertNull($booking->email);
        $this->assertNull($booking->message);
        $this->assertNotContains('phone', array_keys($booking->getAttributes()));
    }

    public function test_other_realtors_see_nothing_and_enquiries_are_kept_separate_from_inspections(): void
    {
        $admin = $this->adminWith2fa();
        $mine = User::factory()->create();
        $other = User::factory()->create();
        $property = $this->property($admin);

        Lead::create(['name' => 'Ada Okafor', 'phone' => '1', 'type' => 'inspection', 'property_id' => $property->id, 'referrer_id' => $mine->id]);
        Lead::create(['name' => 'Bode Salami', 'phone' => '2', 'type' => 'general', 'referrer_id' => $mine->id]);
        Lead::create(['name' => 'Caro Eze', 'phone' => '3', 'type' => 'inspection', 'property_id' => $property->id, 'referrer_id' => $other->id]);

        // Inspections tab shows Ada's name; enquiry tab shows Bode's name; each is separate.
        $this->actingAs($mine)->get('/realtor/leads?kind=inspections')->assertSee('Ada Okafor')->assertDontSee('Bode Salami');
        $this->get('/realtor/leads?kind=enquiries')->assertSee('Bode Salami')->assertDontSee('Ada Okafor');
        $this->get('/realtor/leads')->assertSee('Ref #'.Lead::where('referrer_id', $mine->id)->min('id'));

        // The other realtor only ever sees their own single booking (Caro), not mine.
        $this->actingAs($other)->get('/realtor/leads')->assertSee('Caro Eze')->assertDontSee('Ada Okafor')->assertDontSee('Bode Salami');
    }

    public function test_a_booking_without_a_referral_link_alerts_only_the_admin(): void
    {
        $admin = $this->adminWith2fa();
        $realtor = User::factory()->create();

        \Illuminate\Support\Facades\Notification::fake();
        $this->post('/contact', ['name' => 'Walk In', 'phone' => '08011112222', 'interest' => 'inspection'])->assertSessionHasNoErrors();

        \Illuminate\Support\Facades\Notification::assertSentTo($admin, NewLeadAlert::class);
        \Illuminate\Support\Facades\Notification::assertNotSentTo($realtor, NewReferralLead::class);
    }
}
