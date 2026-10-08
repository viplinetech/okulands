<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
