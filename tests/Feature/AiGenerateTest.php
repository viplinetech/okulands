<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiGenerateTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP', 'two_factor_recovery_codes' => [], 'two_factor_confirmed_at' => now(),
        ]);
    }

    public function test_without_an_api_key_it_fails_gracefully_instead_of_crashing(): void
    {
        config(['services.gemini.key' => null]);

        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])
            ->postJson(route('admin.ai.property-description'), ['title' => 'Prime Plot, Amansea'])
            ->assertStatus(422)
            ->assertJsonStructure(['message']);
    }

    public function test_it_generates_a_property_description_from_the_gemini_api(): void
    {
        config(['services.gemini.key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => '<p>A fine plot of land in Amansea, Awka.</p>']]]]],
            ], 200),
        ]);

        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])
            ->postJson(route('admin.ai.property-description'), [
                'title' => 'Prime Residential Plot',
                'location' => 'Amansea, Awka',
                'sector' => 'Real Estate',
                'price' => 8500000,
            ])
            ->assertOk()
            ->assertJson(['html' => '<p>A fine plot of land in Amansea, Awka.</p>']);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'generativelanguage.googleapis.com')
            && str_contains(json_encode($request->data()), 'Prime Residential Plot'));
    }

    public function test_a_plain_text_ai_reply_is_wrapped_into_paragraphs(): void
    {
        config(['services.gemini.key' => 'test-key']);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => "First paragraph.\n\nSecond paragraph."]]]]],
            ], 200),
        ]);

        $response = $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])
            ->postJson(route('admin.ai.property-description'), ['title' => 'Test Plot'])
            ->assertOk();

        $this->assertSame('<p>First paragraph.</p><p>Second paragraph.</p>', $response->json('html'));
    }

    public function test_a_transient_503_is_retried_and_recovers(): void
    {
        config(['services.gemini.key' => 'test-key']);

        Http::fakeSequence('generativelanguage.googleapis.com/*')
            ->push(['error' => ['code' => 503, 'message' => 'busy']], 503)
            ->push(['candidates' => [['content' => ['parts' => [['text' => '<p>Recovered after a retry.</p>']]]]]], 200);

        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])
            ->postJson(route('admin.ai.property-description'), ['title' => 'Test Plot'])
            ->assertOk()
            ->assertJson(['html' => '<p>Recovered after a retry.</p>']);
    }

    public function test_repeated_503s_fail_with_a_clear_busy_message(): void
    {
        config(['services.gemini.key' => 'test-key']);

        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['code' => 503]], 503)]);

        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])
            ->postJson(route('admin.ai.property-description'), ['title' => 'Test Plot'])
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'OkuLands Smart AI is unusually busy right now. Please try again in a moment.']);
    }

    public function test_the_title_is_required(): void
    {
        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])
            ->postJson(route('admin.ai.property-description'), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    public function test_a_realtor_cannot_reach_the_admin_ai_endpoints(): void
    {
        $realtor = User::factory()->create();

        $this->actingAs($realtor)
            ->postJson(route('admin.ai.property-description'), ['title' => 'Test'])
            ->assertStatus(404);
    }
}
