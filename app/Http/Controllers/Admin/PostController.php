<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Models\NewsPost;
use App\Services\Gemini;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends ResourceController
{
    private const CATEGORIES = ['Buying guide', 'Construction', 'Agriculture', 'Company news', 'Market insight'];

    protected function config(): array
    {
        return [
            'model' => NewsPost::class,
            'title' => 'Blog posts',
            'singular' => 'post',
            'subtitle' => 'Articles and company news. A post goes live at its publish date; leave it empty to keep a draft.',
            'icon' => 'newspaper',
            'route' => 'admin.posts',
            // Asks up front, on the list page, before anything is typed: let OkuLands Smart AI
            // write a complete draft, or start a blank post to write by hand.
            'generateAi' => ['route' => 'admin.posts.generate-ai', 'label' => 'Generate with OkuLands Smart AI'],
            'search' => ['title', 'category', 'excerpt'],
            'order' => ['published_at', 'desc'],
            'view' => fn (NewsPost $p) => $p->published_at && $p->published_at->isPast() ? route('blog.show', $p->slug) : null,
            'columns' => [
                ['label' => 'Cover', 'type' => 'image', 'value' => fn (NewsPost $p) => $p->coverUrl()],
                ['label' => 'Title', 'key' => 'title', 'main' => true, 'sub' => fn (NewsPost $p) => $p->category],
                ['label' => 'Status', 'value' => fn (NewsPost $p) => ! $p->published_at ? 'draft' : ($p->published_at->isFuture() ? 'scheduled' : 'published'), 'type' => 'badge'],
                ['label' => 'Publish date', 'key' => 'published_at', 'type' => 'date'],
            ],
            'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text', 'rules' => ['required', 'string', 'max:200'], 'full' => true],
                ['name' => 'category', 'label' => 'Category', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:60'], 'list' => ['Buying guide', 'Agriculture', 'Construction', 'Company news', 'Market insight']],
                ['name' => 'published_at', 'label' => 'Publish date & time', 'type' => 'datetime', 'rules' => ['nullable', 'date'], 'help' => 'Empty = draft (not shown on the website).'],
                ['name' => 'excerpt', 'label' => 'Short summary', 'type' => 'textarea', 'rows' => 3, 'rules' => ['nullable', 'string', 'max:300'], 'help' => 'Shown on the blog list and in Google results.'],
                ['name' => 'body', 'label' => 'Article', 'type' => 'richtext', 'rules' => ['required', 'string', 'max:100000'], 'help' => 'Use the toolbar for headings, bold, lists and links. Press Enter for a new paragraph.'],
                ['name' => 'cover_image', 'label' => 'Cover photo', 'type' => 'image', 'folder' => 'blog'],
            ],
        ];
    }

    protected function prepare(array $data, ?Model $model, Request $request): array
    {
        if (! $model) {
            $data['slug'] = $this->uniqueSlug($data['title']);
            $data['author_id'] = $request->user()->id;
        }

        return $data;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;
        for ($i = 2; NewsPost::where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }

    /**
     * The "Generate with OkuLands Smart AI" choice on the list page: fully hands-off. Picks its
     * own topic, writes and self-proofreads a complete article with SEO keywords worked in
     * naturally, designs and attaches its own cover image (the title set as real text on an
     * on-brand gradient), and publishes it immediately. The "Add post" choice next to it is the
     * normal, entirely blank, manual form for a human-written post.
     */
    public function generateAi(Request $request, Gemini $gemini): RedirectResponse
    {
        $avoid = NewsPost::query()->latest()->limit(8)->pluck('title')->implode('; ') ?: 'none yet';
        $categories = implode(', ', self::CATEGORIES);

        $prompt = <<<PROMPT
            You are writing a brand new blog article for the Oku Lands & Properties website. Oku Lands
            is a Nigerian company based in Anambra State, serving Awka and Enugu, that handles bulk
            land sales, general contracting/construction, block production, and real estate, and also
            runs a network of independent realtors who earn commission referring buyers. This article
            will be published immediately with no human review, so it must be ready to publish as-is.

            Pick your own useful, specific topic (ideas, advice or an explanation related to buying
            land, real estate, construction, block production, or the realtor programme). Do not pick
            a generic "welcome to our blog" topic, and do not repeat any of these recent titles:
            {$avoid}

            Rules:
            - Plain English, professional and trustworthy Nigerian tone, clearly relevant to Oku
              Lands' own business. No slang, no exaggerated claims, no filler, and never use an em
              dash (—) anywhere.
            - SEO: naturally work in 2-4 relevant search phrases a Nigerian land/property buyer might
              actually search for. Keep the topic general (real estate, construction, land buying in
              Nigeria broadly) rather than tied to any specific city or state. Only name Awka, Enugu
              or Anambra if the topic itself genuinely calls for a local example; do not mention them
              by default or force them in. Never force any phrase in unnaturally.
            - Proofread yourself before answering: the final text must have correct grammar, spelling,
              punctuation and natural sentence flow throughout, as if professionally edited. Do not
              submit a first draft.
            - Do not invent specific prices, legal claims, or named locations.
            - The article body: 350-550 words. A short opening paragraph, 2-4 short sections (a short
              <h3> subheading each, if it helps), a brief closing paragraph. <ul>/<li> for any list of
              tips or steps. Only these HTML tags: <p>, <h3>, <ul>, <li>, <strong>, <em>. No markdown,
              no emoji, no code fences, no title repeated as a heading inside the body.
            - The short summary: 1-2 plain sentences, no HTML, under 200 characters, written to make
              someone want to click (and usable as-is as a search engine result snippet).
            - The category must be exactly one of: {$categories}.

            Respond with ONLY a single JSON object, no markdown fences, no extra text, in exactly this
            shape: {"title": "...", "category": "...", "excerpt": "...", "body": "..."}
            PROMPT;

        try {
            $parsed = $this->parseJson($gemini->generate($prompt));
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.posts.index')->with('error', $e->getMessage());
        }

        $title = (string) ($parsed['title'] ?? '');
        if ($title === '') {
            return redirect()->route('admin.posts.index')->with('error', 'OkuLands Smart AI could not generate a post this time. Please try again.');
        }

        $slug = $this->uniqueSlug($title);

        $post = NewsPost::create([
            'title' => $title,
            'slug' => $slug,
            'category' => in_array($parsed['category'] ?? null, self::CATEGORIES, true) ? $parsed['category'] : self::CATEGORIES[0],
            'excerpt' => (string) ($parsed['excerpt'] ?? ''),
            'body' => $this->toParagraphs((string) ($parsed['body'] ?? '')),
            'cover_image' => \App\Support\CoverArt::generateFor($title, $slug),
            'author_id' => $request->user()->id,
            // A few seconds in the past, not exactly now: avoids any clock/precision edge case
            // where the "published_at <= now()" check a moment later (on the very next request,
            // the redirect to the live page) could miss by a hair.
            'published_at' => now()->subSeconds(5),
        ]);

        // Stays on the admin list, not the live article or the edit screen: just a confirmation
        // that it was written and published successfully.
        return redirect()->route('admin.posts.index')
            ->with('success', 'OkuLands Smart AI generated and published "'.$title.'".');
    }

    /** @return array<string, mixed> */
    private function parseJson(string $text): array
    {
        $text = trim(preg_replace('/^```(?:json)?\s*|\s*```$/', '', trim($text)) ?? $text);

        $decoded = json_decode($text, true);

        if (! is_array($decoded)) {
            // The model sometimes wraps the JSON in a sentence; pull out the {...} block and retry.
            if (preg_match('/\{.*\}/s', $text, $m)) {
                $decoded = json_decode($m[0], true);
            }
        }

        if (! is_array($decoded) || empty($decoded['body'])) {
            throw new \RuntimeException('OkuLands Smart AI could not generate a post this time. Please try again.');
        }

        return $decoded;
    }

    /** Strip a stray ```html fence if the model adds one, and make sure we hand back real HTML. */
    private function toParagraphs(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/^```(?:html)?\s*|\s*```$/', '', $text) ?? $text;

        if (! preg_match('/<[a-z][^>]*>/i', trim($text))) {
            $text = collect(preg_split('/\R{2,}/', $text))
                ->map(fn ($p) => '<p>'.e(trim($p)).'</p>')
                ->implode('');
        }

        return trim($text);
    }
}
