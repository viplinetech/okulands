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
 * the ADMIN gets every detail, the REALTOR is told it happened and for which property, but never who.
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

        // Their notification: what and which property, nothing about the person.
        $data = $realtor->notifications()->firstOrFail()->data;
        $this->assertSame('Inspection booked through your link', $data['title']);
        $this->assertStringContainsString('Amansea Residential Plots', $data['body']);
        foreach (['Chidi', 'Okonkwo', '0803', '1234567', 'chidi@example.com', 'Saturday'] as $secret) {
            $this->assertStringNotContainsString($secret, json_encode($data));
        }

        // Their Booked inspections page and dashboard.
        $this->actingAs($realtor);
        foreach (['/realtor/leads', '/realtor/leads?kind=inspections', '/realtor'] as $url) {
            $page = $this->get($url)->assertOk()->assertSee('Inspection booked')->assertSee('Amansea Residential Plots');
            foreach (['Chidi', 'Okonkwo', '8031234567', '1234567', 'chidi@example.com', 'Saturday morning', 'tel:', 'wa.me'] as $secret) {
                $page->assertDontSee($secret, false);
            }
        }
        $this->get('/realtor/leads')->assertSee('Ref #'.$lead->id);
        $this->get('/realtor/alerts')->assertOk()->assertSee('Inspection booked through your link')->assertDontSee('Chidi');
    }

    public function test_the_realtor_query_cannot_load_the_persons_details_at_all(): void
    {
        $realtor = User::factory()->create();
        Lead::create(['name' => 'Secret Person', 'phone' => '08099999999', 'email' => 's@example.com', 'message' => 'private', 'type' => 'inspection', 'referrer_id' => $realtor->id]);

        $booking = $realtor->referredBookings()->firstOrFail();

        // Even if a screen asked for them, the columns are simply not there.
        $this->assertNull($booking->name);
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

        Lead::create(['name' => 'A', 'phone' => '1', 'type' => 'inspection', 'property_id' => $property->id, 'referrer_id' => $mine->id]);
        Lead::create(['name' => 'B', 'phone' => '2', 'type' => 'general', 'referrer_id' => $mine->id]);
        Lead::create(['name' => 'C', 'phone' => '3', 'type' => 'inspection', 'property_id' => $property->id, 'referrer_id' => $other->id]);

        $this->actingAs($mine)->get('/realtor/leads?kind=inspections')->assertSee('Inspection booked')->assertDontSee('New enquiry');
        $this->get('/realtor/leads?kind=enquiries')->assertSee('New enquiry')->assertDontSee('Inspection booked');
        $this->get('/realtor/leads')->assertSee('Ref #'.Lead::where('referrer_id', $mine->id)->min('id'));

        // The other realtor only ever sees their own single booking.
        $this->actingAs($other)->get('/realtor/leads')->assertSee('Inspection booked')->assertDontSee('New enquiry');
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
