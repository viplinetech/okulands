<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsPost;
use App\Services\Gemini;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** "Generate using OkuLands Smart AI" buttons scattered through the admin's writing fields. */
class AiController extends Controller
{
    public function propertyDescription(Request $request, Gemini $gemini): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'location' => ['nullable', 'string', 'max:200'],
            'sector' => ['nullable', 'string', 'max:60'],
            'type' => ['nullable', 'string', 'max:80'],
            'price' => ['nullable', 'numeric'],
            'size' => ['nullable', 'string', 'max:40'],
            'bedrooms' => ['nullable', 'string', 'max:10'],
            'bathrooms' => ['nullable', 'string', 'max:10'],
        ]);

        $facts = collect([
            'Listing title' => $data['title'],
            'Location' => $data['location'] ?? null,
            'Service/sector' => $data['sector'] ?? null,
            'Property type' => $data['type'] ?? null,
            'Price (NGN)' => isset($data['price']) ? number_format((float) $data['price']) : null,
            'Size' => $data['size'] ?? null,
            'Bedrooms' => $data['bedrooms'] ?? null,
            'Bathrooms' => $data['bathrooms'] ?? null,
        ])->filter()->map(fn ($v, $k) => "{$k}: {$v}")->implode("\n");

        $prompt = <<<PROMPT
            You are writing a property listing description for Oku Lands & Properties, a real estate,
            construction and land company based in Anambra State, Nigeria.

            Write a short, clean property description using the facts below. Rules:
            - Plain English, in a warm, trustworthy Nigerian tone (Anambra/South-East context where relevant). No slang, no exaggeration, no "dear esteemed buyer" style filler.
            - Short: 2 short paragraphs, about 60-100 words total. A busy buyer should read it in seconds.
            - Naturally include the location and property type somewhere in the text (helps the page rank in Google), without keyword-stuffing.
            - Do not invent facts (no claims about schools, security, amenities, titles etc. unless given below).
            - Output format: plain HTML using only <p> tags for paragraphs. No headings, no markdown, no lists, no emoji.

            Facts:
            {$facts}
            PROMPT;

        return $this->respond($gemini, $prompt);
    }

    public function blogPost(Request $request, Gemini $gemini): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category' => ['nullable', 'string', 'max:60'],
        ]);

        $category = $data['category'] ?? null;

        $prompt = <<<PROMPT
            You are writing a blog article for the Oku Lands & Properties website. Oku Lands is a
            Nigerian company (based in Anambra State, serving Awka and Enugu) that handles bulk land
            sales, general contracting/construction, block production, and real estate, and also runs
            a network of independent realtors who earn commission referring buyers.

            Write a complete, useful blog article for this title: "{$data['title']}"
            {$this->categoryLine($category)}

            Rules:
            - Plain English, professional and trustworthy Nigerian tone. Practical ideas, advice, or
              explanations a Nigerian land/property buyer or someone curious about construction would
              genuinely find useful. No filler, no exaggerated claims, no slang.
            - Length: about 350-550 words.
            - Structure: a short opening paragraph, 2-4 short sections (each may have a short <h3>
              subheading if it helps readability), and a brief closing paragraph. Use <ul>/<li> for any
              list of tips or steps.
            - Do not invent specific prices, legal claims, or named locations beyond Awka/Enugu/Anambra
              unless the title itself names somewhere else.
            - Output format: plain HTML using only these tags: <p>, <h3>, <ul>, <li>, <strong>, <em>.
              No markdown, no emoji, no code fences, no title repeated as a heading (the title is shown
              separately on the page already).
            PROMPT;

        return $this->respond($gemini, $prompt);
    }

    /**
     * One click, nothing typed first: the AI picks its own topic and writes the whole post
     * (title, category, short summary and the article itself) in one go.
     */
    public function blogPostFull(Gemini $gemini): JsonResponse
    {
        $categories = ['Buying guide', 'Construction', 'Agriculture', 'Company news', 'Market insight'];
        $avoid = NewsPost::query()->latest()->limit(8)->pluck('title')->implode('; ') ?: 'none yet';

        $prompt = <<<PROMPT
            You are writing a brand new blog article for the Oku Lands & Properties website. Oku Lands
            is a Nigerian company based in Anambra State, serving Awka and Enugu, that handles bulk
            land sales, general contracting/construction, block production, and real estate, and also
            runs a network of independent realtors who earn commission referring buyers.

            Pick your own useful, specific topic (ideas, advice or an explanation related to buying
            land, real estate, construction, block production, or the realtor programme). Do not pick
            a generic "welcome to our blog" topic, and do not repeat any of these recent titles:
            {$avoid}

            Rules:
            - Plain English, professional and trustworthy Nigerian tone. No slang, no exaggerated
              claims, no filler, and never use an em dash (—) anywhere.
            - Do not invent specific prices, legal claims, or named locations beyond Awka/Enugu/Anambra.
            - The article body: 350-550 words. A short opening paragraph, 2-4 short sections (a short
              <h3> subheading each, if it helps), a brief closing paragraph. <ul>/<li> for any list of
              tips or steps. Only these HTML tags: <p>, <h3>, <ul>, <li>, <strong>, <em>. No markdown,
              no emoji, no code fences, no title repeated as a heading inside the body.
            - The short summary: 1-2 plain sentences, no HTML, under 200 characters.
            - The category must be exactly one of: {$this->list($categories)}.

            Respond with ONLY a single JSON object, no markdown fences, no extra text, in exactly this
            shape: {"title": "...", "category": "...", "excerpt": "...", "body": "..."}
            PROMPT;

        try {
            $parsed = $this->parseJson($gemini->generate($prompt));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'title' => (string) ($parsed['title'] ?? ''),
            'category' => in_array($parsed['category'] ?? null, $categories, true) ? $parsed['category'] : $categories[0],
            'excerpt' => (string) ($parsed['excerpt'] ?? ''),
            'body' => $this->toParagraphs((string) ($parsed['body'] ?? '')),
        ]);
    }

    private function list(array $items): string
    {
        return implode(', ', $items);
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

    private function respond(Gemini $gemini, string $prompt): JsonResponse
    {
        try {
            return response()->json(['html' => $this->toParagraphs($gemini->generate($prompt))]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function categoryLine(?string $category): string
    {
        return $category ? "It belongs to the \"{$category}\" category on the blog." : '';
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
