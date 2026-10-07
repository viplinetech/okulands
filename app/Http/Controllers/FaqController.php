<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers;

use App\Models\FaqItem;
use App\Models\SiteSetting;
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
}
