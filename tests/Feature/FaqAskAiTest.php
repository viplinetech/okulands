<?php

namespace Tests\Feature;

use App\Models\FaqItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FaqAskAiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_answers_a_question_grounded_in_the_existing_faqs(): void
    {
        FaqItem::create(['category' => 'Payments', 'question' => 'Do you offer payment plans?', 'answer' => 'Yes, instalments are available on select properties.', 'is_active' => true]);

        config(['services.gemini.key' => 'test-key']);
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'Yes, Oku Lands offers payment plans on select properties.']]]]],
            ], 200),
        ]);

        $this->postJson(route('faq.ask'), ['question' => 'Can I pay in instalments?'])
            ->assertOk()
            ->assertJson(['answer' => 'Yes, Oku Lands offers payment plans on select properties.']);

        Http::assertSent(fn ($request) => str_contains(json_encode($request->data()), 'Do you offer payment plans?')
            && str_contains(json_encode($request->data()), 'Can I pay in instalments?'));
    }

    public function test_it_fails_gracefully_without_an_api_key(): void
    {
        config(['services.gemini.key' => null]);

        $this->postJson(route('faq.ask'), ['question' => 'Do you build houses?'])
            ->assertStatus(422)
            ->assertJsonStructure(['message']);
    }

    public function test_the_question_is_required(): void
    {
        $this->postJson(route('faq.ask'), [])->assertStatus(422)->assertJsonValidationErrors('question');
    }

    public function test_it_is_rate_limited(): void
    {
        config(['services.gemini.key' => 'test-key']);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'An answer.']]]]],
        ], 200)]);

        for ($i = 0; $i < 8; $i++) {
            $this->postJson(route('faq.ask'), ['question' => 'A question '.$i]);
        }

        $this->postJson(route('faq.ask'), ['question' => 'One too many'])->assertStatus(429);
    }
}
