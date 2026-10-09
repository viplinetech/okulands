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

    private function respond(Gemini $gemini, string $prompt): JsonResponse
    {
        try {
            return response()->json(['html' => $this->toParagraphs($gemini->generate($prompt))]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
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
