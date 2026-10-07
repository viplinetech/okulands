<?php

namespace Tests\Feature;

use App\Models\NewsPost;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\RichText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The admin editor's output is printed unescaped on the public site, so it must be strictly sanitised. */
class RichTextTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create([
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP', 'two_factor_recovery_codes' => ['aaaaa-bbbbb'], 'two_factor_confirmed_at' => now(),
        ]);
    }

    public function test_formatting_is_kept(): void
    {
        $html = '<h2>Title</h2><p>Some <strong>bold</strong>, <em>italic</em> and <u>underlined</u> text.</p><ul><li>One</li><li>Two</li></ul><blockquote>Quote</blockquote><p class="ql-align-center">Centred</p>';

        $clean = RichText::clean($html);

        foreach (['<h2>Title</h2>', '<strong>bold</strong>', '<em>italic</em>', '<u>underlined</u>', '<ul>', '<blockquote>Quote</blockquote>', 'class="ql-align-center"'] as $keep) {
            $this->assertStringContainsString($keep, $clean);
        }
    }

    public function test_dangerous_markup_is_removed(): void
    {
        $clean = RichText::clean('<p onclick="steal()">Hi <script>alert(1)</script><a href="javascript:alert(2)">bad</a> <img src=x onerror=alert(3)><iframe src="https://evil.test"></iframe> <a href="https://ok.test" target="_blank">good</a></p><style>body{display:none}</style>');

        foreach (['<script', 'onclick', 'javascript:', '<img', 'onerror', '<iframe', '<style', 'display:none'] as $bad) {
            $this->assertStringNotContainsStringIgnoringCase($bad, $clean);
        }
        $this->assertStringContainsString('href="https://ok.test"', $clean);
        $this->assertStringContainsString('noopener', $clean);
    }

    public function test_an_empty_editor_is_null_and_padding_is_trimmed(): void
    {
        $this->assertNull(RichText::clean('<p><br></p>'));
        $this->assertNull(RichText::clean('   '));
        $this->assertSame('<p>Hello</p>', RichText::clean('<p><br></p><p>Hello</p><p><br></p>'));
    }

    public function test_older_plain_text_still_renders_with_paragraphs_and_line_breaks(): void
    {
        $html = RichText::html("First paragraph.\nSame paragraph, new line.\n\nSecond <b>paragraph</b>.");

        $this->assertStringContainsString('<p>First paragraph.<br>', $html);
        $this->assertStringContainsString('<p>Second &lt;b&gt;paragraph&lt;/b&gt;.</p>', $html);
        $this->assertSame('First Second', RichText::text('<p>First</p><p>Second</p>'));
    }

    public function test_saving_an_article_sanitises_it_and_the_public_page_shows_the_formatting(): void
    {
        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])
            ->post(route('admin.posts.store'), [
                'title' => 'Buying land safely', 'category' => 'Guides', 'status' => 'published', 'published_at' => now()->subDay()->format('Y-m-d H:i:s'),
                'body' => '<h2>Check the title</h2><p>Always <strong>verify</strong> it.<script>alert(1)</script></p>',
            ])->assertSessionHasNoErrors();

        $post = NewsPost::firstOrFail();
        $this->assertStringNotContainsString('<script', $post->body);

        $this->get(route('blog.show', $post->slug))->assertOk()->assertSee('<h2>Check the title</h2>', false)->assertSee('<strong>verify</strong>', false)->assertDontSee('alert(1)', false);
    }

    public function test_a_required_article_cannot_be_saved_with_only_blank_lines(): void
    {
        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true])
            ->post(route('admin.posts.store'), ['title' => 'Empty', 'category' => 'Guides', 'status' => 'published', 'body' => '<p><br></p>'])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('news_posts', 0);
    }

    public function test_the_editor_is_present_on_the_admin_content_forms(): void
    {
        $this->actingAs($this->admin())->withSession(['two_factor_passed' => true]);

        foreach (['/adminbackend/posts/create', '/adminbackend/properties/create', '/adminbackend/services/create', '/adminbackend/faqs/create'] as $url) {
            $this->get($url)->assertOk()->assertSee('data-rte', false);
        }
    }
}
