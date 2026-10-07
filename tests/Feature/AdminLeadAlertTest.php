<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use App\Notifications\NewLeadAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** Contact messages and booked inspections must reach every active admin, in-app and by email. */
class AdminLeadAlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_contact_message_alerts_active_admins_only(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $suspended = User::factory()->admin()->suspended()->create();
        $realtor = User::factory()->create();

        $this->post('/contact', ['name' => 'Ada Obi', 'phone' => '8031234567', 'message' => 'Do you have plots in Lekki?', 'interest' => 'other'])
            ->assertSessionHas('status');

        Notification::assertSentTo($admin, NewLeadAlert::class, fn ($n, $channels) => in_array('mail', $channels) && in_array('database', $channels));
        Notification::assertNotSentTo($suspended, NewLeadAlert::class);
        Notification::assertNotSentTo($realtor, NewLeadAlert::class);
    }

    public function test_the_admin_sees_a_booked_inspection_with_the_property_in_their_notifications(): void
    {
        $admin = User::factory()->admin()->create();
        $property = Property::create([
            'title' => 'Amansea Plot', 'slug' => 'amansea-plot', 'sector' => 'real_estate', 'price' => 5_000_000,
            'location' => 'Amansea', 'status' => 'available', 'created_by' => $admin->id,
        ]);

        $this->post('/contact', ['name' => 'Chidi Eze', 'phone' => '08099998888', 'property_id' => $property->id, 'interest' => 'inspection']);

        $data = $admin->notifications()->firstOrFail()->data;
        $this->assertSame('Inspection booked', $data['title']);
        $this->assertStringContainsString($property->title, $data['body']);
        $this->assertStringContainsString('8099998888', $data['body']);
    }

    public function test_a_plain_contact_message_is_labelled_as_a_message(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/contact', ['name' => 'Ngozi', 'email' => 'ngozi@example.com', 'phone' => '8011112222', 'message' => 'Hello there', 'interest' => 'other']);

        $this->assertSame('New contact message', $admin->notifications()->firstOrFail()->data['title']);
    }
}
