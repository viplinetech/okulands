<?php

namespace Tests\Feature;

use App\Models\NewsPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** The "Generate with OkuLands Smart AI" choice on the Blog posts list page: fully hands-off. */
class BlogAiGenerateTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP', 'two_factor_recovery_codes' => [], 'two_factor_confirmed_at' => now(),
        ]);
    }

    private function fakeAi(array $payload): void
    {
        config(['services.gemini.key' => 'test-key']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => json_encode($payload)]]]]],
        ], 200)]);
    }

    public function test_it_writes_designs_a_cover_for_and_publishes_a_post_immediately(): void
    {
        Storage::fake('public');

        $this->fakeAi([
            'title' => 'Five Things to Check Before Buying Land in Anambra',
            'category' => 'Buying guide',
            'excerpt' => 'A quick checklist for first-time land buyers in Anambra.',
            'body' => '<p>Buying land is a big step.</p><h3>Check the title</h3><p>Always verify it first.</p>',
        ]);

        $response = $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])
            ->post(route('admin.posts.generate-ai'));

        $post = NewsPost::firstOrFail();
        $response->assertRedirect(route('admin.posts.index'))->assertSessionHas('success');

        $this->assertSame('Five Things to Check Before Buying Land in Anambra', $post->title);
        $this->assertSame('Buying guide', $post->category);
        $this->assertStringContainsString('Check the title', $post->body);

        // Published immediately, not left as a draft.
        $this->assertNotNull($post->published_at);
        $this->assertTrue($post->published_at->lessThanOrEqualTo(now()));

        // A real, designed cover image was generated and attached (not the generic placeholder).
        $this->assertNotNull($post->cover_image);
        Storage::disk('public')->assertExists($post->cover_image);
        $svg = Storage::disk('public')->get($post->cover_image);
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('Five Things to', $svg); // the title, wrapped onto the cover
    }

    public function test_it_fails_gracefully_and_creates_nothing_when_the_ai_reply_cannot_be_parsed(): void
    {
        config(['services.gemini.key' => 'test-key']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Sorry, I cannot help with that.']]]]],
        ], 200)]);

        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])
            ->post(route('admin.posts.generate-ai'))
            ->assertRedirect(route('admin.posts.index'))
            ->assertSessionHas('error');

        $this->assertSame(0, NewsPost::count());
    }

    public function test_an_invalid_category_from_the_ai_falls_back_to_the_first_option(): void
    {
        Storage::fake('public');
        $this->fakeAi(['title' => 'A Title', 'category' => 'Not A Real Category', 'excerpt' => 'An excerpt.', 'body' => '<p>Body.</p>']);

        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])
            ->post(route('admin.posts.generate-ai'));

        $this->assertSame('Buying guide', NewsPost::firstOrFail()->category);
    }

    public function test_a_realtor_cannot_trigger_blog_generation(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.posts.generate-ai'))
            ->assertStatus(404);

        $this->assertSame(0, NewsPost::count());
    }

    public function test_the_manual_add_post_form_no_longer_shows_any_ai_button(): void
    {
        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])
            ->get(route('admin.posts.create'))
            ->assertOk()
            ->assertDontSee('OkuLands Smart AI');
    }

    public function test_the_list_page_shows_the_generate_choice_next_to_add_post(): void
    {
        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])
            ->get(route('admin.posts.index'))
            ->assertOk()
            ->assertSee('Generate with OkuLands Smart AI')
            ->assertSee('Add post');
    }
}
