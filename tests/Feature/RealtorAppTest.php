<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\Lead;
use App\Models\Property;
use App\Models\PropertyView;
use App\Models\ReferralVisit;
use App\Models\User;
use App\Notifications\HotLead;
use App\Notifications\NewReferralLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class RealtorAppTest extends TestCase
{
    use RefreshDatabase;

    private function property(User $owner): Property
    {
        return Property::create([
            'title' => 'Amansea Plot', 'slug' => 'amansea-plot', 'sector' => 'real_estate', 'price' => 5_000_000,
            'location' => 'Amansea', 'status' => 'available', 'created_by' => $owner->id,
        ]);
    }

    public function test_every_realtor_page_renders_with_data(): void
    {
        $user = User::factory()->create();
        $member = User::factory()->create();
        $member->forceFill(['referred_by' => $user->id])->save();
        $this->property($user);
        Lead::create(['name' => 'A Lead', 'phone' => '08011112222', 'referrer_id' => $user->id]);
        $this->actingAs($user);

        foreach (['/realtor', '/realtor/leads', '/realtor/team', '/realtor/earnings', '/realtor/share', '/realtor/listings', '/realtor/alerts', '/realtor/profile', '/realtor/security'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get('/realtor/share')->assertSee('<svg', false)->assertSee($user->referral_code);
        $this->get('/realtor/listings')->assertSee('Amansea Plot')->assertSee('/ref/'.$user->referral_code, false);
    }

    public function test_the_mobile_shell_has_the_bottom_bar_a_more_sheet_and_the_centre_action(): void
    {
        $this->actingAs(User::factory()->create())->get('/realtor')
            ->assertOk()
            ->assertSee('class="bottom-nav"', false)
            ->assertSee('data-sheet-panel', false)
            ->assertSee('bn-fab', false)
            ->assertSee('min-[600px]:flex', false);
    }

    public function test_profile_update_validates_the_payout_account_and_encrypts_it(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $base = ['name' => 'Chinedu Okafor', 'phone' => '08031234567'];

        $this->put(route('realtor.profile.update'), $base + ['bank_name' => 'Zenith Bank', 'account_number' => '123', 'account_name' => 'Chinedu'])
            ->assertSessionHasErrors('account_number');
        $this->put(route('realtor.profile.update'), $base + ['bank_name' => 'Fake Bank', 'account_number' => '0123456789', 'account_name' => 'Chinedu'])
            ->assertSessionHasErrors('bank_name');
        $this->put(route('realtor.profile.update'), $base + ['bank_name' => 'Zenith Bank'])
            ->assertSessionHasErrors(['account_number', 'account_name']);

        $this->put(route('realtor.profile.update'), $base + ['bank_name' => 'Zenith Bank', 'account_number' => '0123456789', 'account_name' => 'Chinedu Okafor'])
            ->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('0123456789', $user->account_number);
        $this->assertNotSame('0123456789', \DB::table('users')->where('id', $user->id)->value('account_number'));
        $this->assertSame('•••• 6789', $user->maskedAccountNumber());
        $this->assertDatabaseHas('activity_logs', ['action' => 'profile.bank_updated']);
    }

    public function test_the_profile_form_cannot_change_role_status_or_sponsor(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->put(route('realtor.profile.update'), [
            'name' => 'New Name', 'phone' => '08031234567', 'role' => 'admin', 'status' => 'suspended', 'referred_by' => 1, 'commission_rate_override' => 90,
        ]);

        $user->refresh();
        $this->assertSame('New Name', $user->name);
        $this->assertSame('realtor', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertNull($user->referred_by);
        $this->assertNull($user->commission_rate_override);
    }

    public function test_avatars_are_resized_to_webp_and_disguised_files_are_refused(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->actingAs($user);
        $base = ['name' => 'Chinedu Okafor', 'phone' => '08031234567'];

        $this->put(route('realtor.profile.update'), $base + ['avatar' => UploadedFile::fake()->image('me.jpg', 2400, 1800)])->assertSessionHasNoErrors();
        $path = $user->fresh()->avatar;
        $this->assertStringStartsWith('uploads/avatars/', $path);
        $this->assertStringEndsWith('.webp', $path);
        Storage::disk('public')->assertExists($path);
        [$w] = getimagesizefromstring(Storage::disk('public')->get($path));
        $this->assertLessThanOrEqual(600, $w);

        // A script renamed to .jpg is not an image and must never be stored.
        $fake = UploadedFile::fake()->createWithContent('shell.jpg', '<?php echo "pwned";');
        $this->put(route('realtor.profile.update'), $base + ['avatar' => $fake])->assertSessionHasErrors('avatar');
        $this->assertSame($path, $user->fresh()->avatar);
    }

    public function test_a_lead_via_a_referral_link_alerts_the_realtor_and_is_credited_to_them(): void
    {
        $realtor = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $visit = ReferralVisit::create(['visitor_token' => (string) Str::uuid(), 'referrer_id' => $realtor->id]);

        $this->withCookie('oku_ref', $visit->visitor_token)
            ->post('/contact', ['name' => 'Buyer One', 'phone' => '08055556666', 'message' => 'Hello'])
            ->assertRedirect();

        $lead = Lead::firstOrFail();
        $this->assertSame($realtor->id, $lead->referrer_id);
        $this->assertSame(1, $realtor->notifications()->where('type', NewReferralLead::class)->count());
        $this->assertSame(1, $admin->unreadNotifications()->count());
    }

    public function test_a_visitor_who_keeps_viewing_a_property_triggers_one_hot_lead_alert(): void
    {
        $realtor = User::factory()->create();
        $property = $this->property($realtor);
        $visit = ReferralVisit::create(['visitor_token' => (string) Str::uuid(), 'referrer_id' => $realtor->id]);

        $this->withCookie('oku_ref', $visit->visitor_token);
        foreach (range(1, 5) as $i) {
            $this->get('/properties/'.$property->slug)->assertOk();
        }

        $this->assertSame(5, PropertyView::firstOrFail()->views);
        $this->assertSame(1, $realtor->notifications()->where('type', HotLead::class)->count());
    }

    public function test_browser_prefetching_does_not_count_as_a_view(): void
    {
        $realtor = User::factory()->create();
        $property = $this->property($realtor);
        $visit = ReferralVisit::create(['visitor_token' => (string) Str::uuid(), 'referrer_id' => $realtor->id]);

        $this->withCookie('oku_ref', $visit->visitor_token)
            ->get('/properties/'.$property->slug, ['Sec-Purpose' => 'prefetch;prerender'])->assertOk();

        $this->assertSame(0, PropertyView::count());
    }

    public function test_notifications_are_marked_read_when_the_page_is_opened(): void
    {
        $realtor = User::factory()->create();
        $realtor->notify(new NewReferralLead(Lead::create(['name' => 'X', 'phone' => '08000000000', 'referrer_id' => $realtor->id])));
        $this->assertSame(1, $realtor->unreadNotifications()->count());

        $this->actingAs($realtor)->get('/realtor/alerts')->assertOk()->assertSee('New enquiry through your link');

        $this->assertSame(0, $realtor->fresh()->unreadNotifications()->count());
    }

    public function test_earnings_only_show_the_realtors_own_commission(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $property = $this->property($me);
        $sale = new \App\Models\Sale(['property_id' => $property->id, 'realtor_id' => $me->id, 'buyer_name' => 'B', 'amount' => 1_000_000]);
        $sale->status = 'approved';
        $sale->save();
        Commission::create(['sale_id' => $sale->id, 'user_id' => $me->id, 'tier' => 1, 'rate' => 5, 'amount' => 123_456, 'status' => 'pending']);
        Commission::create(['sale_id' => $sale->id, 'user_id' => $other->id, 'tier' => 2, 'rate' => 2, 'amount' => 654_321, 'status' => 'pending']);

        $this->actingAs($me)->get('/realtor/earnings')->assertOk()->assertSee('123,456')->assertDontSee('654,321');
    }
}
