<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers;

use App\Models\FaqItem;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Services\Gemini;
use App\Support\RichText;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /** The Help Center: the whole knowledge base, grouped by category, searchable on the page. */
    public function index()
    {
        $items = FaqItem::visible()->get();

        return view('pages.faq', [
            'settings' => SiteSetting::current(),
            'groups' => $items->groupBy('category'),
            'total' => $items->count(),
        ]);
    }

    /** "Was this helpful?" thumbs. Counters help the admin see which answers need work. */
    public function feedback(Request $request, FaqItem $faqItem): JsonResponse
    {
        $data = $request->validate(['helpful' => ['required', 'in:yes,no']]);

        $faqItem->increment($data['helpful'] === 'yes' ? 'helpful_yes' : 'helpful_no');

        return response()->json(['ok' => true]);
    }

    /**
     * OkuLands Smart AI: shown when a Help Center search finds nothing. Grounded strictly in the
     * admin's own FAQ answers and basic company facts, so it never invents prices, legal claims
     * or policies — it's told plainly to say so and point to WhatsApp/Contact when it doesn't know.
     */
    public function ask(Request $request, Gemini $gemini): JsonResponse
    {
        $data = $request->validate(['question' => ['required', 'string', 'max:300']]);

        $settings = SiteSetting::current();

        $knowledge = FaqItem::visible()->get()
            ->map(fn (FaqItem $f) => 'Q: '.$f->question."\nA: ".RichText::text($f->answer))
            ->implode("\n\n");

        $services = Service::visible()->pluck('title')->implode(', ');

        $prompt = <<<PROMPT
            You are "OkuLands Smart AI", a help assistant on the Oku Lands & Properties website
            (a real estate, construction, land and block-industry company based in Anambra State,
            Nigeria, serving Awka and Enugu, which also runs a realtor referral programme).

            Answer the visitor's question below using ONLY the knowledge provided. Rules:
            - Plain English, short and direct: 2-4 sentences, no markdown headers, a short list only if it genuinely helps.
            - Professional, warm Nigerian tone.
            - If the knowledge below does not actually answer the question (e.g. a specific price, a specific property, legal advice, or anything not covered), say honestly that you do not have that specific information, and suggest they contact the team via WhatsApp or the Contact page. Never invent a price, policy, timeline or legal claim that is not in the knowledge below.
            - Do not mention "the knowledge below" or that you are an AI reading a document; just answer naturally.

            Company services: {$services}

            Knowledge (existing Help Center answers):
            {$knowledge}

            Visitor's question: "{$data['question']}"
            PROMPT;

        try {
            $answer = $gemini->generate($prompt);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['answer' => $answer]);
    }
}
